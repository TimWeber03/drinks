<p align="center">
  <img src="https://img.shields.io/badge/PHP-8.5-777bb4" alt="PHP 8.5">
  <img src="https://img.shields.io/badge/Laravel-13-ff2d20" alt="Laravel 13">
  <img src="https://img.shields.io/badge/Livewire-3-4e56a6" alt="Livewire 3">
  <img src="https://img.shields.io/badge/license-MIT-blue" alt="MIT License">
</p>

# Drinks

A self-hosted, honesty-based drinks tally for offices, hackerspaces, and other shared spaces. People keep a running balance, tap a drink to deduct its price, and top up whenever they like — no cash register, no per-purchase login.

It's built around a fast "tap your name, tap a drink" kiosk flow, with a real admin login, per-drink stock tracking, and a one-way import tool for migrating data from a compatible legacy system.

## Features

**Kiosk** (public, no login required)
- Tap your name, then tap a drink — balance updates instantly.
- Deposit money with quick-amount buttons or a custom value.
- Per-drink stock tracking: items sell out, get greyed out automatically, and can be hidden entirely with one toggle.
- Per-person purchase history and a site-wide recent activity feed.
- Light/dark mode, responsive from phone to kiosk tablet to laptop.

**Admin** (behind a real login)
- Manage drinks (price, image, active/inactive, stock) and drinkers (active/inactive, avatar, manual balance corrections).
- Manage other admin accounts — no public self-registration.
- Full transaction log, filterable by person, drink, type, and date range.

**Migration**
- `mete:import` connects directly to an existing mete database (Postgres or SQLite) and imports drinkers, drinks, and full transaction history — including reconstructing historical running balances from mete's raw audit log. Idempotent and supports `--dry-run`.

## Tech stack

- [Laravel 13](https://laravel.com) + [Livewire 3](https://livewire.laravel.com) — no separate frontend build/SPA
- [Tailwind CSS](https://tailwindcss.com)
- PostgreSQL
- [Laravel Sail](https://laravel.com/docs/sail) for local development

## Getting started

You'll need [Docker](https://www.docker.com/) installed. If you also have PHP and Composer locally, this is the fastest path:

```bash
git clone <this-repo> drinks
cd drinks
composer install
cp .env.example .env
php artisan key:generate
./vendor/bin/sail up -d
./vendor/bin/sail artisan migrate --seed
```

No PHP locally? Bootstrap `composer install` with a throwaway container instead:

```bash
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php84-composer:latest \
    composer install --ignore-platform-reqs
```

Then visit:

- **`http://localhost`** — the kiosk
- **`http://localhost/login`** — admin login

`migrate --seed` creates a demo admin at `admin@example.com` / `password` plus a few sample drinkers and drinks. For a real admin account, use the dedicated command instead of the seeder:

```bash
./vendor/bin/sail artisan app:create-admin --name="Jane Doe" --email="jane@example.com" --password="a-strong-password"
```

(omit the options to be prompted interactively).

## Importing data from mete

If you're moving from an existing [mete](https://github.com/chaosdorf/mete) installation, point the importer at its database via a few extra `.env` variables (see `.env.example` for the full list — mete supports both Postgres and SQLite):

```bash
METE_DB_CONNECTION=pgsql
METE_DB_HOST=127.0.0.1
METE_DB_DATABASE=mete
METE_DB_USERNAME=mete
METE_DB_PASSWORD=secret
```

Always dry-run first:

```bash
./vendor/bin/sail artisan mete:import --dry-run
```

Then run it for real:

```bash
./vendor/bin/sail artisan mete:import
```

The import is idempotent — re-running it upserts by mete's original IDs instead of creating duplicates. Admin accounts are never imported (mete has none); create them with `app:create-admin` as above.

## Running tests

```bash
./vendor/bin/sail artisan test
```

## Code style

This project uses [Laravel Pint](https://laravel.com/docs/pint):

```bash
./vendor/bin/sail bin pint
```

## Contributing

Issues and pull requests are welcome. Before opening a PR, please make sure `sail artisan test` and `sail bin pint` are both clean.

## License

Licensed under the [MIT License](LICENSE).
