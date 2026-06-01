# Portfolio Admin

Private admin panel for managing portfolio website content. Built with **Laravel 13**, **Vue 3 + Inertia.js**, and **Tailwind CSS v4**.

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

### Without Docker
- PHP >= 8.3 with extensions: `pdo_pgsql`, `pgsql`, `redis`, `gd`, `zip`, `intl`, `pcntl`, `bcmath`, `opcache`
- Composer 2
- Node.js >= 20 + npm
- PostgreSQL >= 16
- Redis >= 7

### With Docker
- Docker >= 24
- Docker Compose >= 2.20

## Quick Start (Docker)

```bash
# 1. Clone and enter project
git clone <repository-url> portfolio-admin
cd portfolio-admin

# 2. Copy environment file
cp .env.example .env

# 3. Start all services
docker compose up -d

# 4. Generate application key
docker compose exec app php artisan key:generate

# 5. Build frontend assets (on host)
npm install
npm run build
```

App will be available at **http://localhost:8080**.

See [DOCKER.md](DOCKER.md) for full Docker documentation.

## Quick Start (Local)

```bash
# 1. Install PHP dependencies
composer install

# 2. Install Node dependencies
npm install

# 3. Copy and configure environment
cp .env.example .env
# Edit .env — set DB_HOST, DB_PASSWORD, REDIS_HOST etc.

# 4. Generate key and run migrations
php artisan key:generate
php artisan migrate

# 5. Start dev server
php artisan serve &
npm run dev
```

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
