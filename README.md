# Notes App — Symfony

A server-rendered Notes web application built with Symfony 7.2, reproducing the functionality of [notes-app-python](https://github.com/mgrandusky/notes-app-python).

## Stack

- **Symfony 7.2** — PHP framework
- **Doctrine ORM + Migrations** — database layer (SQLite for dev, configurable)
- **Twig** — server-rendered templates with Bootstrap 5
- **Symfony Security** — session-based form login + CSRF protection
- **KnpU OAuth2 Client Bundle** — OAuth login via Google and GitHub
- **TinyMCE** (CDN) — rich text editor for note content
- **Symfony HtmlSanitizer** — server-side HTML sanitization

## Requirements

- PHP 8.2+
- Composer 2.x
- SQLite extension (`php-sqlite3`) for local development, or a MySQL/PostgreSQL server

## Local Setup

### 1. Clone and install dependencies

```bash
git clone https://github.com/mgrandusky/notes-app-symfony.git
cd notes-app-symfony
composer install
```

### 2. Configure environment

Copy `.env` and create a `.env.local` for your local overrides:

```bash
cp .env .env.local
```

Edit `.env.local`:

```dotenv
APP_ENV=dev
APP_SECRET=your_random_32_char_secret

# SQLite (default for dev – no server required)
DATABASE_URL="sqlite:///%kernel.project_dir%/var/data_dev.db"

# Google OAuth  (get credentials from https://console.cloud.google.com)
GOOGLE_CLIENT_ID=your_google_client_id
GOOGLE_CLIENT_SECRET=your_google_client_secret

# GitHub OAuth  (get credentials from https://github.com/settings/developers)
GITHUB_CLIENT_ID=your_github_client_id
GITHUB_CLIENT_SECRET=your_github_client_secret
```

> **OAuth callback URLs** to register with the providers:
> - Google: `http://localhost:8000/connect/google/check`
> - GitHub: `http://localhost:8000/connect/github/check`

### 3. Create the database and run migrations

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

### 4. Start the development server

```bash
php -S localhost:8000 -t public/
```

Or with the Symfony CLI:

```bash
symfony server:start
```

Open <http://localhost:8000> in your browser.

---

## Features

### Authentication
- Register with email + password
- Login with email + password (form login, CSRF-protected)
- Login with **Google** or **GitHub** OAuth — existing accounts matched by email
- Logout

### Notes
- **Create / edit / view** notes with a TinyMCE rich text editor
- **Soft delete** — notes are flagged `isDeleted=true`, not removed from the database
- **Archive / unarchive** toggle
- **Tags** — comma-separated string, filterable on the index page

### Notes Index (`/notes/`)
Supports query-parameter filtering and sorting, matching the Flask app:

| Param      | Description                                    | Default       |
|------------|------------------------------------------------|---------------|
| `search`   | Full-text search across title, content, tags   | —             |
| `tag`      | Filter by tag (LIKE match)                     | —             |
| `sort`     | `updatedAt` \| `createdAt` \| `title`          | `updatedAt`   |
| `order`    | `asc` \| `desc`                                | `desc`        |
| `archived` | `0` (active) \| `1` (archived) \| empty (all) | empty (all)   |

### Security
- All note routes require `IS_AUTHENTICATED_FULLY`
- CSRF tokens on every state-changing form (login, register, create/edit note, archive, delete, logout)
- Note ownership enforced — users can only see/modify their own notes
- Rich text content is sanitized with Symfony HtmlSanitizer before storage

### Allowed HTML in note content
`p`, `br`, `strong`, `em`, `u`, `h1`–`h6`, `ul`, `ol`, `li`, `blockquote`, `a` (href, title), `code`, `pre`, `span` (style), `div` (style)

---

## Running Tests

```bash
php bin/phpunit
```

Tests use an in-memory SQLite database (configured in `phpunit.dist.xml`) and cover:

- Unauthenticated redirect to `/login`
- Authenticated note creation
- Note repository filtering (user isolation, soft-delete, search)

---

## Production Notes

- Set `APP_ENV=prod` and `APP_SECRET` to a strong random value.
- Replace the TinyMCE `no-api-key` CDN URL with a real API key from <https://www.tiny.cloud>.
- Run `composer dump-env prod` to compile environment variables.
- Use a proper database (MySQL/PostgreSQL) in production.
