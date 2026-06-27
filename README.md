# Kirada

Kirada is a Laravel property management app for landlords, tenants, admins, and maintenance teams.

## Features

- Role-based dashboards for admin, landlord, tenant, and maintenance users
- Property, unit, tenant, lease, invoice, and rent payment management
- Maintenance request workflow
- Tenant invitations
- Secure document management
- In-app messaging
- AI assistant
- Multi-language UI
- PWA assets and offline fallback

## Tech Stack

- PHP 8.3
- Laravel 13
- Livewire 3, Volt, Flux, and Blaze
- Laravel Fortify authentication
- Spatie Laravel Permission
- Tailwind CSS 4
- Vite

## Local Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install
npm run build
php artisan serve
```

For development with Vite:

```bash
npm run dev
```

## Tests

```bash
php artisan test
```

The current verified test suite passes with 43 tests and 10 skipped.

## Repository

```text
https://github.com/Abdallah-Tah/kirada
```
