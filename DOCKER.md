# Docker — CMS Bypur

## Arsitektur

```
Browser (HTTPS)
    │
    ▼ :443
Nginx HOST (VPS)          ← docker/nginx/vps-host.conf
(SSL termination, gzip, security headers)
    │
    ▼ :8080 (127.0.0.1)
Nginx CONTAINER           ← docker/nginx/production.conf
(static assets, fastcgi)
    │
    ▼ :9000
PHP-FPM (app)             ← Dockerfile stage: app-production
    ├──► PostgreSQL :5432
    └──► Redis :6379

Queue Worker              ← Dockerfile stage: app-production
    └──► Redis :6379
```

---

## Compose Profiles

Satu file `docker-compose.yml` dengan dua profile:

| Profile | Command | Digunakan untuk |
|---|---|---|
| `dev` | `docker compose --profile dev up -d` | Development lokal |
| `prod` | `docker compose --profile prod up -d` | Production di VPS |

`postgres` dan `redis` tidak memiliki profile — selalu aktif di kedua environment.

---

## Services

| Service | Profile | Image | Container |
|---|---|---|---|
| `postgres` | keduanya | `postgres:16-alpine` | `cms_bypur_postgres` |
| `redis` | keduanya | `redis:7.4-alpine` | `cms_bypur_redis` |
| `app` | dev | PHP-FPM (stage: `app-local`) | `cms_bypur_app` |
| `nginx` | dev | `nginx:1.27-alpine` | `cms_bypur_nginx` |
| `node` | dev | `node:20-alpine` | `cms_bypur_node` |
| `queue` | dev | PHP-FPM (stage: `app-local`) | `cms_bypur_queue` |
| `app-prod` | prod | PHP-FPM (stage: `app-production`) | `cms_bypur_app` |
| `nginx-prod` | prod | Nginx (stage: `nginx-production`) | `cms_bypur_nginx` |
| `queue-prod` | prod | PHP-FPM (stage: `app-production`) | `cms_bypur_queue` |

---

## Dockerfile Stages

| Stage | Base | Output |
|---|---|---|
| `deps-php` | `php:8.3-fpm-alpine` | PHP + extensions |
| `deps-composer` | `deps-php` | `/var/www/html/vendor` |
| `deps-node` | `node:20-alpine` | `public/build/` (Vite assets) |
| `app-local` | `deps-php` | Dev image (bind mount) |
| `app-production` | `deps-php` | Immutable prod image |
| `nginx-production` | `nginx:1.27-alpine` | Nginx + public assets baked-in |

---

## Development

```bash
# Start
docker compose --profile dev up -d

# Rebuild setelah perubahan Dockerfile
docker compose --profile dev up -d --build

# Logs
docker compose logs -f app
docker compose logs -f node

# Artisan
docker compose exec app php artisan migrate
docker compose exec app php artisan migrate:fresh --seed
docker compose exec app php artisan cache:clear

# Shell
docker compose exec app sh
docker compose exec postgres psql -U portfolio -d cms_bypur
docker compose exec redis redis-cli

# Stop
docker compose --profile dev down
```

---

## Deploy Production (VPS Ubuntu)

### 1. Persiapan awal (sekali saja)

```bash
# Install Docker
curl -fsSL https://get.docker.com | sh
sudo usermod -aG docker $USER
newgrp docker

# Install Nginx & Certbot di host
sudo apt install -y nginx certbot python3-certbot-nginx

# Setup Nginx host config
sudo cp docker/nginx/vps-host.conf /etc/nginx/sites-available/cms.bypur.my.id
sudo ln -s /etc/nginx/sites-available/cms.bypur.my.id /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl reload nginx

# Issue SSL certificate
sudo certbot --nginx -d cms.bypur.my.id
```

### 2. Deploy pertama kali

```bash
git clone <repository-url> ~/cms-bypur
cd ~/cms-bypur

cp .env.example .env
nano .env
# Set: APP_ENV=production, APP_KEY, APP_URL=https://cms.bypur.my.id
#      DB_PASSWORD, REDIS_PASSWORD (opsional)
#      DB_HOST=postgres, REDIS_HOST=redis

docker compose --profile prod up -d --build
```

### 3. Deploy update (setelah push ke repo)

```bash
cd ~/cms-bypur
git pull
docker compose --profile prod up -d --build
# Entrypoint otomatis menjalankan migrate
```

### 4. Generate APP_KEY (jika belum ada di .env)

```bash
docker compose --profile prod exec app-prod php artisan key:generate --force
docker compose --profile prod restart app-prod
```

### 5. Cek status

```bash
docker compose ps
docker compose logs -f app-prod
```

---

## Environment Variables

| Variable | Deskripsi | Dev | Prod |
|---|---|---|---|
| `APP_ENV` | Environment | `local` | `production` |
| `APP_URL` | URL aplikasi | `http://localhost:8080` | `https://cms.bypur.my.id` |
| `NGINX_PORT` | Port host Nginx | `8080` | `8080` |
| `DB_HOST` | Host PostgreSQL | `postgres` | `postgres` |
| `DB_DATABASE` | Nama database | `cms_bypur` | `cms_bypur` |
| `DB_USERNAME` | User database | `portfolio` | `portfolio` |
| `DB_PASSWORD` | Password database | `secret` | **set kuat** |
| `REDIS_HOST` | Host Redis | `redis` | `redis` |
| `REDIS_PASSWORD` | Password Redis | _(kosong)_ | **set kuat** |

> `DB_HOST` dan `REDIS_HOST` harus menggunakan nama service Docker (`postgres`, `redis`).

---

## Entrypoint (`docker/php/entrypoint.sh`)

Dijalankan otomatis setiap container `app`/`app-prod` dan `queue`/`queue-prod` start:

```
1. chown storage/ & bootstrap/cache/ → www-data:www-data 775
2. Tunggu PostgreSQL (nc loop)
3. Tunggu Redis (nc loop)
4. php artisan optimize:clear && cache:clear
5. php artisan migrate --force
6. Seed jika tabel roles masih kosong
7. Jika APP_ENV=production:
   └─ config:cache, route:cache, view:cache
8. exec php-fpm (atau command lain)
```

---

## Troubleshooting

```bash
# Container tidak start
docker compose logs app-prod

# PostgreSQL tidak ready
docker compose ps postgres
docker compose logs postgres

# Redis tidak ready
docker compose exec redis redis-cli ping

# Reset database (HATI-HATI: data hilang)
docker compose --profile prod down -v
docker compose --profile prod up -d --build

# Rebuild image dari awal
docker compose --profile prod build --no-cache
```

---

## File Structure

```
portfolio-admin/
├── Dockerfile                 # 6 stages
├── docker-compose.yml         # dev + prod (profiles)
├── .dockerignore
└── docker/
    ├── nginx/
    │   ├── default.conf           # Nginx dev
    │   ├── production.conf        # Nginx prod (container)
    │   ├── vps-host.conf          # Nginx host VPS (SSL + proxy)
    │   └── errors/
    │       ├── 404.html
    │       └── 50x.html
    └── php/
        ├── local.ini              # PHP INI overrides
        └── entrypoint.sh          # Bootstrap script
```
