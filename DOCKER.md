# Docker Setup — Portfolio Admin

Dokumentasi lengkap infrastruktur Docker untuk project Portfolio Admin.

---

## Arsitektur

```
         Browser
            │
            ▼
    ┌───────────────┐
    │  Nginx :8080  │  (reverse proxy, static assets)
    └───────┬───────┘
            │ fastcgi :9000
            ▼
    ┌───────────────┐        ┌─────────────────┐
    │  PHP-FPM app  │───────▶│  PostgreSQL 5432│
    └───────┬───────┘        └─────────────────┘
            │
            ▼
    ┌───────────────┐
    │  Redis 6379   │  (cache, session, queue)
    └───────────────┘
            ▲
            │
    ┌───────────────┐
    │ Queue Worker  │  (same image as app)
    └───────────────┘
```

---

## Services

| Service | Image | Port | Container Name |
|---|---|---|---|
| `app` | Custom PHP-FPM 8.3 | — | `portfolio_admin_app` |
| `nginx` | `nginx:1.27-alpine` | `8080:80` | `portfolio_admin_nginx` |
| `postgres` | `postgres:16-alpine` | `5432:5432` | `portfolio_admin_postgres` |
| `redis` | `redis:7.4-alpine` | `6379:6379` | `portfolio_admin_redis` |
| `queue` | Custom PHP-FPM 8.3 | — | `portfolio_admin_queue` |

---

## Multi-Stage Dockerfile

Dockerfile menggunakan 5 stage untuk memisahkan concerns dan meminimalkan ukuran image:

### Stage 1 — `deps-php`
```
Base: php:8.3-fpm-alpine
```
- Install OS libraries: `postgresql-dev`, `libpng-dev`, `libzip-dev`, dll.
- Install PHP extensions: `pdo_pgsql`, `pgsql`, `gd`, `zip`, `intl`, `pcntl`, `bcmath`, `opcache`
- Install PECL extension: `redis`
- Copy `docker/php/local.ini`

### Stage 2 — `deps-composer`
```
Base: deps-php
```
- Copy `composer.json` + `composer.lock`
- Jalankan `composer install --no-dev --no-scripts --no-autoloader`
- Hasil: `/var/www/html/vendor` (tanpa app code)

### Stage 3 — `deps-node`
```
Base: node:20-alpine
```
- Install npm dependencies
- Jalankan `npm run build`
- Hasil: `public/build/` (compiled frontend assets)

### Stage 4 — `app-local` _(Development)_
```
Base: deps-php
```
- Copy Composer binary
- Copy vendor dari `deps-composer`
- Source code di-**bind mount** saat runtime (tidak di-copy ke image)
- Cocok untuk hot reload saat development

### Stage 5 — `app-production` _(Production)_
```
Base: deps-php
```
- Copy vendor dari `deps-composer`
- Copy **seluruh source code** ke dalam image (immutable)
- Copy `public/build/` dari `deps-node`
- Jalankan `composer dump-autoload --optimize`
- Set permissions `www-data` untuk storage
- Image bersifat immutable — tidak butuh bind mount

---

## Volumes

| Volume | Service | Deskripsi |
|---|---|---|
| `postgres_data` | postgres | Data PostgreSQL, persisten di host |
| `redis_data` | redis | Data Redis (append-only), persisten di host |
| `vendor_cache` | app, queue | Composer vendor cache (dev only) |
| `node_modules_cache` | app | Node modules cache (dev only) |

---

## Entrypoint (`docker/php/entrypoint.sh`)

Script yang dijalankan setiap container `app` dan `queue` start:

```
1. Set permissions: storage/ dan bootstrap/cache/ → www-data:www-data 775
2. Tunggu PostgreSQL: nc -z $DB_HOST $DB_PORT (loop setiap 2 detik)
3. Tunggu Redis:      nc -z $REDIS_HOST $REDIS_PORT (loop setiap 2 detik)
4. php artisan optimize:clear
5. php artisan migrate --force --no-interaction
6. Jika APP_ENV=production:
   └─ php artisan config:cache
   └─ php artisan route:cache
   └─ php artisan view:cache
7. exec "$@"  → menjalankan php-fpm atau command lain
```

---

## Environment Variables Docker

Buat file `.env` dari template:
```bash
cp .env.example .env
```

Variable yang relevan untuk Docker:

| Variable | Deskripsi | Default |
|---|---|---|
| `NGINX_PORT` | Port host untuk Nginx | `8080` |
| `DB_CONNECTION` | Driver database | `pgsql` |
| `DB_HOST` | Hostname PostgreSQL (nama service) | `postgres` |
| `DB_PORT` | Port PostgreSQL | `5432` |
| `DB_DATABASE` | Nama database | `portfolio_admin` |
| `DB_USERNAME` | User PostgreSQL | `portfolio` |
| `DB_PASSWORD` | Password PostgreSQL | `secret` |
| `REDIS_CLIENT` | PHP Redis client | `phpredis` |
| `REDIS_HOST` | Hostname Redis (nama service) | `redis` |
| `REDIS_PORT` | Port Redis | `6379` |
| `REDIS_PASSWORD` | Password Redis (opsional) | _(kosong)_ |
| `CACHE_STORE` | Cache driver | `redis` |
| `SESSION_DRIVER` | Session driver | `redis` |
| `QUEUE_CONNECTION` | Queue driver | `redis` |

> **Penting:** `DB_HOST` dan `REDIS_HOST` harus menggunakan **nama service Docker** (`postgres`, `redis`), bukan `127.0.0.1`.

---

## Perintah Umum

### Development

```bash
# Start semua services (build otomatis jika belum ada)
docker compose up -d

# Start dan rebuild image
docker compose up -d --build

# Lihat status services
docker compose ps

# Lihat logs semua service
docker compose logs -f

# Lihat logs service tertentu
docker compose logs -f app
docker compose logs -f nginx
```

### Artisan Commands

```bash
# Jalankan artisan di container app
docker compose exec app php artisan <command>

# Contoh
docker compose exec app php artisan migrate
docker compose exec app php artisan migrate:fresh --seed
docker compose exec app php artisan cache:clear
docker compose exec app php artisan queue:work

# Generate app key (pertama kali setup)
docker compose exec app php artisan key:generate
```

### Composer

```bash
docker compose exec app composer install
docker compose exec app composer require <package>
docker compose exec app composer update
```

### Shell Access

```bash
# Masuk ke container app
docker compose exec app sh

# Masuk ke container PostgreSQL
docker compose exec postgres psql -U portfolio -d portfolio_admin

# Masuk ke container Redis CLI
docker compose exec redis redis-cli
```

### Stop & Cleanup

```bash
# Stop semua service (data tetap tersimpan)
docker compose down

# Stop dan hapus volumes (HATI-HATI: data database hilang)
docker compose down -v

# Rebuild image dari awal (tanpa cache)
docker compose build --no-cache
```

---

## Production

Untuk production, gunakan override file `docker-compose.prod.yml`:

```bash
# Build production image
docker compose -f docker-compose.yml -f docker-compose.prod.yml build

# Start production
docker compose -f docker-compose.yml -f docker-compose.prod.yml up -d
```

Perbedaan production:
- `app` dan `queue` menggunakan target `app-production` (immutable image)
- Source code tidak di-bind mount
- Frontend assets sudah di-bake ke dalam image
- Entrypoint otomatis menjalankan `config:cache`, `route:cache`, `view:cache`

---

## File Structure Docker

```
portfolio-admin/
├── Dockerfile                     # Multi-stage build (5 stages)
├── docker-compose.yml             # Development orchestration
├── docker-compose.prod.yml        # Production overrides
├── .dockerignore                  # Build context exclusions
└── docker/
    ├── nginx/
    │   └── default.conf           # Nginx server block config
    └── php/
        ├── local.ini              # PHP INI overrides
        └── entrypoint.sh          # Container bootstrap script
```

---

## Troubleshooting

### Container app tidak start

```bash
# Cek logs entrypoint
docker compose logs app

# Pastikan .env sudah ada dan DB_HOST=postgres
cat .env | grep DB_HOST
```

### PostgreSQL connection refused

```bash
# Cek apakah postgres service healthy
docker compose ps postgres

# Cek logs postgres
docker compose logs postgres
```

### Redis connection refused

```bash
# Cek apakah redis service healthy
docker compose ps redis

# Test koneksi manual
docker compose exec redis redis-cli ping
# Expected: PONG
```

### Vite assets tidak ditemukan

```bash
# Build assets di host (development)
npm install
npm run dev

# Atau di dalam container
docker compose exec app npm run build
```

### Permission error pada storage

```bash
# Jalankan ulang entrypoint secara manual
docker compose exec app chown -R www-data:www-data storage bootstrap/cache
docker compose exec app chmod -R 775 storage bootstrap/cache
```
