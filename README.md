<p align="center">
  <img src="https://img.shields.io/badge/PHP-8.5-777bb4" alt="PHP 8.5">
  <img src="https://img.shields.io/badge/Laravel-13-ff2d20" alt="Laravel 13">
  <img src="https://img.shields.io/badge/Livewire-3-4e56a6" alt="Livewire 3">
  <img src="https://img.shields.io/badge/license-MIT-blue" alt="MIT License">
</p>

# Drinks

A self-hosted, honesty-based drinks tally for offices, hackerspaces, and other shared spaces. People keep a running balance, tap a drink to deduct its price, and top up whenever they like — no cash register, no per-purchase login.

It's built around a fast "tap your name, tap a drink" kiosk flow, with an open management view, per-drink stock tracking, and a one-way import tool for migrating data from a compatible legacy system.

## Features

**Kiosk** (public, no login required)
- Tap your name, then tap a drink — balance updates instantly.
- Deposit money with quick-amount buttons or a custom value.
- Per-drink stock tracking: items sell out, get greyed out automatically, and can be hidden entirely with one toggle.
- Per-person purchase history and a site-wide recent activity feed.
- A **Management** link in the header opens the management view directly — no login step.
- Light/dark mode, responsive from phone to kiosk tablet to laptop.

**Management** (open, like the kiosk)
- Manage drinks (price, image, active/inactive, stock) and drinkers (active/inactive, avatar, manual balance corrections).
- Manage admin accounts.
- Full transaction log, filterable by person, drink, type, and date range.
- No login is required to reach any of this, so the whole app — kiosk, management view and API alike — belongs on a trusted network. Accounts and the login page still exist and are used for the profile page.

**API**
- Implements the [Space-Market API v3](https://space-market.github.io/API/swagger.json) under `/v3` and the older [v1](https://github.com/Space-Market/API/blob/v1/spec/swagger.yaml) (mete-compatible) API at the root, so existing space-market clients (vending frontends, barcode scanners, dashboards) can talk to this installation.

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
- **`http://localhost/dashboard`** — the management view
- **`http://localhost/login`** — admin login

`migrate --seed` creates a demo admin at `admin@example.com` / `password` plus a few sample drinkers and drinks. The management view doesn't require that account, but for a real one use the dedicated command instead of the seeder:

```bash
./vendor/bin/sail artisan app:create-admin --name="Jane Doe" --email="jane@example.com" --password="a-strong-password"
```

(omit the options to be prompted interactively).

## Space-Market API

The app serves two versions of the [Space-Market API](https://github.com/Space-Market/API): the current **v3** under `/v3`, and the older mete-compatible **v1** at the root. Both are backed by the same drinks, drinkers and transactions, so a v1 barcode scanner and a v3 dashboard can run side by side.

### v3

[Specification](https://space-market.github.io/API/swagger.json) — drinks are *products*, drinkers are *users*, and all amounts are integer cents.

```bash
curl http://localhost/v3/info/
curl http://localhost/v3/products/
curl -X POST http://localhost/v3/users/1/buy/ -H 'Content-Type: application/json' -d '{"product": 2}'
```

| Resource | Endpoints |
| --- | --- |
| Server | `GET /v3/info/` |
| Products | `GET` + `POST /v3/products/`, `GET` + `PATCH` + `DELETE /v3/products/{id}/` |
| Users | `GET` + `POST /v3/users/`, `GET` + `PATCH` + `DELETE /v3/users/{id}/`, `GET /v3/users/stats/`, `GET /v3/users/barcode/{barcode}/` |
| Transactions | `POST /v3/users/{id}/deposit/`, `.../spend/`, `.../buy/`, `.../buy/barcode/`, `.../transfer/` |
| Audits | `GET /v3/audits/?start=YYYY-MM-DD` |
| Images | `GET` + `POST /v3/images/`, `GET /v3/images/{id}`, `GET /v3/images/{id}/img` |
| Barcodes | `POST /v3/barcodes/`, `GET` + `PATCH` + `DELETE /v3/barcodes/{id}/` |
| Denominations | `GET` + `POST /v3/denominations/`, `GET` + `PATCH` + `DELETE /v3/denominations/{id}/` |

Endpoints that take a single value (`deposit`, `spend`, `buy`, `buy/barcode`) accept both the bare JSON scalar the specification shows (`150`) and the object form clients commonly send (`{"amount": 150}`).

Purchases go through the same code path as the kiosk, so stock tracking, balance locking, and the transaction log all apply. Per-user audits are only served for drinkers who have `audit` enabled; everyone else gets a `401`.

### v1 (mete-compatible)

[Specification](https://github.com/Space-Market/API/blob/v1/spec/swagger.yaml) — v1 defines no base path, so its endpoints sit at the root with a `.json` suffix. Amounts are decimal euros, drinks stay *drinks*, and the balance-changing calls are `GET` requests with query parameters, exactly as mete had them.

```bash
curl http://localhost/drinks.json
curl "http://localhost/users/1/deposit.json?amount=2.50"
curl "http://localhost/users/1/buy.json?drink=2"
```

| Resource | Endpoints |
| --- | --- |
| Drinks | `GET` + `POST /drinks.json`, `GET /drinks/new.json`, `GET` + `PATCH` + `DELETE /drinks/{id}.json` |
| Users | `GET` + `POST /users.json`, `GET /users/new.json`, `GET /users/stats.json`, `GET` + `PATCH` + `DELETE /users/{id}.json` |
| Transactions | `GET /users/{id}/deposit.json?amount=`, `.../payment.json?amount=`, `.../buy.json?drink=`, `POST /users/{id}/buy_barcode.json` |
| Barcodes | `GET` + `POST /barcodes.json`, `GET /barcodes/new.json`, `DELETE /barcodes/{barcode}.json` |
| Audits | `GET /audits.json?start_date[year]=…&start_date[month]=…&start_date[day]=…` |

A drink's logo is uploaded as a `logo` file alongside the other fields and served back through `logo_url`; v1 barcodes only ever link to drinks. `donation_recommendation` is accepted and returned as the deprecated alias for `price`. Audits default to the current month when no range is given, and v1 has no per-user audit filter — use v3 for that.

What the server reports in `GET /v3/info/` — currency, decimal separator, energy unit, credit limit, and the defaults for new products — is configured in `config/spacemarket.php` and the matching `SPACEMARKET_*` variables in `.env`.

### Authentication

Neither specification defines authentication, and neither does this implementation: anyone who can reach the app can read and write through the API, including creating and deleting drinks. Keep the installation on a trusted network, or put access control in front of it at the reverse proxy.

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
