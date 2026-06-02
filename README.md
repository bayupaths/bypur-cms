# CMS Bypur

Admin panel untuk mengelola konten website portfolio `bypur.my.id`. Dibangun dengan **Laravel 13**, **Vue 3 + Inertia.js**, dan **Tailwind CSS v4**.

URL Production: **https://cms.bypur.my.id**

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | PHP 8.3, Laravel 13 |
| Frontend | Vue 3, Inertia.js, TypeScript |
| Styling | Tailwind CSS v4 |
| UI Components | Reka UI, Lucide Icons |
| Database | PostgreSQL 16 |
| Cache / Queue / Session | Redis 7 |
| Web Server | Nginx 1.27 + PHP-FPM 8.3 |
| Build Tool | Vite 8 |
| Container | Docker + Docker Compose |

## Requirements

- Docker >= 24
- Docker Compose >= 2.20

## Quick Start — Development

```bash
# 1. Clone
git clone <repository-url> portfolio-admin
cd portfolio-admin

# 2. Copy environment
cp .env.example .env
# Edit .env: set APP_KEY, DB_PASSWORD, dll

# 3. Generate app key
docker compose --profile dev run --rm app php artisan key:generate

# 4. Jalankan semua services
docker compose --profile dev up -d
```

App tersedia di **http://localhost:8080** · Vite dev server di **http://localhost:5173**.

Lihat [DOCKER.md](DOCKER.md) untuk dokumentasi lengkap termasuk deploy production ke VPS.

## Features

### Profile & CV Management
- **Profile** — personal info, bio, social links, availability status
- **Experience** — work history with tech stack, drag-to-reorder
- **Education** — academic background, drag-to-reorder
- **Skills** — skill catalog with category, level, drag-to-reorder
- **Certificates** — certifications with issuer and credential URL
- **Services** — offered services with toggle active/inactive

### User & Access Management
- **Users** — create/edit users
- **Roles & Permissions** — RBAC role management

### Menu Management
- **Menu Groups** — sidebar navigation grouping
- **Menu Items** — navigation items with ordering

## Project Structure

```
app/
├── Http/Controllers/Web/   # Inertia controllers
│   ├── Auth/               # Login, user, role, permission
│   ├── Profile/            # Profile, experience, education...
│   └── Menu/               # Menu group & items
├── Models/                 # Eloquent models
├── Services/               # Business logic
│   └── Profile/            # Profile domain services (with Redis cache)
└── Support/
    └── CacheKeys.php       # Centralized Redis cache key definitions
resources/js/
├── pages/                  # Inertia Vue pages
├── components/             # Shared Vue components
├── composables/            # Vue composables
├── schemas/                # Zod validation schemas
└── types/                  # TypeScript type definitions
```

## Environment Variables

| Variable | Description | Default |
|---|---|---|
| `APP_URL` | Application URL | `http://localhost:8080` |
| `NGINX_PORT` | Host port untuk Nginx | `8080` |
| `DB_HOST` | PostgreSQL host | `postgres` |
| `DB_DATABASE` | Nama database | `cms_bypur` |
| `DB_USERNAME` | Database user | `portfolio` |
| `REDIS_HOST` | Redis host | `redis` |
| `CACHE_STORE` | Cache driver | `redis` |
| `QUEUE_CONNECTION` | Queue driver | `redis` |
| `SESSION_DRIVER` | Session driver | `redis` |

## License

Private — All rights reserved.


## Features

### Profile & CV Management
- **Profile** — personal info, bio, social links, availability status
- **Experience** — work history with tech stack, drag-to-reorder
- **Education** — academic background, drag-to-reorder
- **Skills** — skill catalog with category, level, drag-to-reorder
- **Certificates** — certifications with issuer and credential URL
- **Services** — offered services with toggle active/inactive

### User & Access Management
- **Users** — create/edit users
- **Roles & Permissions** — RBAC role management

### Menu Management
- **Menu Groups** — sidebar navigation grouping
- **Menu Items** — navigation items with ordering

## Project Structure

```
app/
├── Http/Controllers/Web/   # Inertia controllers
│   ├── Auth/               # Login, user, role, permission
│   ├── Profile/            # Profile, experience, education...
│   └── Menu/               # Menu group & items
├── Models/                 # Eloquent models
├── Services/               # Business logic
│   └── Profile/            # Profile domain services (with Redis cache)
└── Support/
    └── CacheKeys.php       # Centralized Redis cache key definitions
resources/js/
├── pages/                  # Inertia Vue pages
│   ├── auth/               # Login, user management
│   └── Profile/            # Profile management pages
├── components/             # Shared Vue components
├── composables/            # Vue composables
├── schemas/                # Zod validation schemas
└── types/                  # TypeScript type definitions
```

## Available Commands

```bash
# Development
npm run dev                          # Start Vite dev server
php artisan serve                    # Start PHP dev server

# Database
php artisan migrate                  # Run migrations
php artisan migrate:fresh --seed     # Fresh migration with seeders

# Cache
php artisan cache:clear              # Clear all cache
php artisan config:cache             # Cache config (production)
php artisan route:cache              # Cache routes (production)

# Queue
php artisan queue:work               # Start queue worker

# Docker shortcuts
docker compose exec app php artisan <command>
docker compose exec app composer <command>
```

## Environment Variables

Key variables in `.env`:

| Variable | Description | Default |
|---|---|---|
| `APP_URL` | Application URL | `http://localhost:8080` |
| `NGINX_PORT` | Host port for Nginx | `8080` |
| `DB_HOST` | PostgreSQL host | `postgres` (Docker) |
| `DB_DATABASE` | Database name | `portfolio_admin` |
| `DB_USERNAME` | Database user | `portfolio` |
| `REDIS_HOST` | Redis host | `redis` (Docker) |
| `CACHE_STORE` | Cache driver | `redis` |
| `QUEUE_CONNECTION` | Queue driver | `redis` |
| `SESSION_DRIVER` | Session driver | `redis` |

## License

Private — all rights reserved.
