# Printshop

Laravel-based printshop portfolio and order management system with 3D model previews.

## Requirements

- PHP 8.2+
- Composer
- Node.js + npm
- SQLite or another supported database

## Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan storage:link
npm install
npm run build
```

## Development

```bash
php artisan serve
npm run dev
```

## Testing

```bash
php artisan test
```

## Configuration

| Variable | Description | Default |
| --- | --- | --- |
| `PORTFOLIO_MATERIALS_CACHE_TTL` | Cache TTL (seconds) for the materials guide page. | `86400` |
