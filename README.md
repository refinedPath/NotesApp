# NotesApp

**Live demo:** https://notesapp.refinedpath.dev

A simple notes management web application built with vanilla PHP 8.3 and
MySQL/MariaDB, with a Bootstrap 5 frontend. Notes can be color-coded,
pinned, and (in active development) tagged.

This is a portfolio project focused on demonstrating clean architecture
patterns in a small codebase: REST API conventions, dependency-injected
PDO models, server-side validation, strict typing, and progressive
frontend enhancement without a framework.

## Status

Active development. Tag UI is in progress; the backend tag API
and join-table support are complete and the frontend rendering work is
underway.

## Features

- Create, read, update, and delete notes with title, content, color,
  and pinned state
- Server-side validation with length and format constraints
- Color-coded notes via per-card accent border
- Pinned notes float to the top of the grid
- Search and sort
- Tag CRUD and assignment endpoints (frontend integration in progress)
- Soft 204 No Content response on successful deletion
- Inline validation error display in modal forms

## Tech stack

- **Backend:** PHP 8.3, MySQL/MariaDB, PDO (no ORM)
- **Frontend:** Vanilla JavaScript (no framework), Bootstrap 5.3
- **Tooling (not required to run the app):** Composer, PHPStan level 6, php-cs-fixer (PSR-12 + 2-space)

## Architecture

- `src/api/` — REST endpoints grouped by resource (`notes/`, `tags/`,
  `note_tags/`). Each file handles one HTTP verb on its resource.
- `src/models/` — PDO data-access classes. Models do data access only;
  validation and HTTP concerns live in the API layer.
- `src/lib/` — Shared helpers (`Response`, `Validator`, polyfills).
- `src/config/` — Database connection, env loading, schema, seed data.
- `public/` — Frontend (HTML, CSS, JS).

## Setup

Requires PHP 8.3+, MySQL 8 or MariaDB 10+, and a web server. Composer for development.

```bash
# Clone
git clone git@github.com:refinedPath/NotesApp.git
cd NotesApp

# Install dependencies for development. Not required to run the app.
composer install

# Create the database and load the schema
mysql -u root -p < src/config/database.sql

# (Optional) Load seed data — note this is destructive by default
mysql -u root -p NotesApp < src/config/seed.sql

# Configure environment
cp .env.example .env
# Edit .env with your DB credentials
```

## Development conventions

- `declare(strict_types=1)` in every PHP file
- PHPStan level 6 must pass: `vendor/bin/phpstan analyze`
- `php-cs-fixer` enforces style: `vendor/bin/php-cs-fixer fix`
- Database constructor throws on connection failure; endpoints handle
  via a single try/catch wrapping all DB-touching code
- Models return `rowCount()` from mutations, not bool
- Centralized `Response::success` / `Response::error` / `Response::noContent`
  for all HTTP responses; never `echo json_encode` from endpoints
- `Config::getBool('APP_DEBUG')` controls whether DB error messages
  leak into responses

## License

MIT — see [LICENSE](LICENSE).
