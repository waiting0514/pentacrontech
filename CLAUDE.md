# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Development Environment

This project uses **Laravel Sail** (Docker). All commands must run through Sail:

```bash
# Start containers
./vendor/bin/sail up -d

# Frontend with hot reload (run in separate terminal)
./vendor/bin/sail npm run dev
```

## Common Commands

```bash
# Composer
./vendor/bin/sail composer install
./vendor/bin/sail composer require package-name

# NPM
./vendor/bin/sail npm install
./vendor/bin/sail npm run build

# Database
./vendor/bin/sail artisan migrate
./vendor/bin/sail artisan db:seed

# Testing
./vendor/bin/sail artisan test
./vendor/bin/sail artisan test --filter=TestName

# Code formatting
./vendor/bin/sail pint

# Interactive shell
./vendor/bin/sail artisan tinker

# View logs
./vendor/bin/sail artisan pail
```

## Architecture Overview

**Single-tenant CMS with AI features** - One Laravel app for a single company. Site-wide configuration (SEO, contact, SMTP, captcha, maintenance, AI budget) lives in one `site_settings` row, accessed via `SiteSetting::current()`.

### Tech Stack
- **Backend**: Laravel 12, PHP 8.2+, MySQL 8.4, Redis, Meilisearch
- **Frontend**: Vue 3 (Composition API), Vite 7, Tailwind CSS 4, Element Plus
- **Auth**: Laravel Sanctum (Bearer tokens)
- **RBAC**: Spatie Permission (roles: super-admin, admin)

### Key Directories
```
app/
├── Http/Controllers/Api/     # REST API controllers
├── Services/                 # Business logic (AI, Image, RAG)
├── Models/                   # Eloquent models
└── Traits/ApiResponse.php    # Standardized API responses

resources/js/
├── admin/                    # Admin SPA (Vue 3)
│   ├── views/               # Page components
│   ├── store/               # Pinia stores
│   └── router/              # Vue Router with permission guards
├── services/                # API service layer (axios)
└── components/              # Shared Vue components
```

### API Response Format
All API responses use `ApiResponse` trait:
```json
{"success": true, "code": 200, "message": "...", "data": {...}}
```

### Multi-language Fields
Products and News store `name` and `description` as JSON arrays:
```php
$casts = ['name' => 'array', 'description' => 'array'];
// Usage: $product->name['zh'], $product->name['en']
```

### Image Path Handling
- Storage: relative paths in DB (`uploads/products/thumb/xxx.jpg`)
- API output: Use `asset('storage/' . $path)` for full URLs
- See `ProductResource.php` for image URL transformation pattern

### AI Budget Control
The site has a yearly TWD AI budget (`site_settings.yearly_budget_twd`). `AiCostControlService` checks budget before API calls and records usage to `ai_usage_logs` table.

## Testing

```bash
# Run all tests
./vendor/bin/sail artisan test

# Run specific test
./vendor/bin/sail artisan test --filter=ProductInquiryTest
```

Test database configured in `phpunit.xml`.

## Test Accounts

After running `db:seed`:
- Admin: `admin` / `admin123`
- Test: `test` / `password` (not created when APP_ENV=production)
