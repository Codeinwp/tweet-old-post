# Agent Workflow

## Project Overview

**Revive Old Posts (Revive Social)** — a WordPress plugin that automatically shares WordPress posts to social networks (Facebook, X/Twitter, LinkedIn, Instagram, Telegram, Mastodon, BlueSky, VK, Pinterest, TikTok, etc.) with scheduling and automation.

- **Text Domain:** `tweet-old-post`
- **Main Plugin File:** `tweet-old-post.php`
- **Pro Companion Plugin:** `tweet-old-post-pro` (separate repo)

## Build & Development Commands

```bash
# Install dependencies
npm ci
composer install

# Build assets (Vue dashboard + React sharing panel)
npm run build              # Production webpack build (Vue)
npm run sharing            # Production wp-scripts build (React)

# Development with watch
npm run dev                # Webpack watch mode (Vue)
npm run sharing-dev        # wp-scripts dev mode (React)

# Linting
composer run lint          # PHPCS (WordPress-Core standard)
composer run format        # Auto-fix PHP code style
npm run lint               # ESLint for Vue + React files
npm run format             # ESLint auto-fix

# Testing
composer run test                    # PHPUnit (all suites)
./vendor/bin/phpunit tests/test-content.php  # Single test file
composer run phpstan                 # Static analysis (level 6)

# E2E Testing (requires wp-env)
npm run wp-env start                 # Start WordPress environment
npm run test:e2e:playwright          # Run Playwright E2E tests
npm run test:e2e:playwright:ui       # E2E with Playwright UI mode

# Distribution
npm run dist               # Create distribution ZIP archive
```

## Architecture

### Entry Point & Autoloading

`tweet-old-post.php` defines constants (prefixed `ROP_`), registers activation/deactivation hooks, sets up a custom autoloader (`class-rop-autoloader.php`), and calls `run_rop()` which instantiates the `Rop` core class.

The autoloader scans `includes/` recursively, matching classes by the `Rop` namespace prefix. Class files follow the convention `class-{slug-case-name}.php`.

### Core Class Hierarchy

- **`Rop`** (`includes/class-rop.php`) — Core plugin class. Loads dependencies, sets up i18n, defines admin hooks.
- **`Rop_Loader`** (`includes/class-rop-loader.php`) — Central hook registration system. Actions/filters are queued then bulk-registered.
- **`Rop_Admin`** (`includes/admin/class-rop-admin.php`) — Admin UI, script/style enqueuing, menu registration. ~2,000 lines.
- **`Rop_Rest_Api`** (`includes/admin/class-rop-rest-api.php`) — REST endpoints at `tweet-old-post/v8/api` and `tweet-old-post/v8/share/{id}`. Requires `manage_options` capability. ~1,700 lines.

### Service Layer (Social Networks)

`includes/admin/services/` — Each social network has a service class extending `Rop_Services_Abstract` (Strategy pattern):
- Key methods: `get_service_credentials()`, `login()`, `publish()`, `get_account()`
- Services: Twitter, Facebook, LinkedIn, Mastodon, BlueSky, Telegram, VK, Pinterest, Tumblr, GMB, Webhook

### Models (Data Layer)

`includes/admin/models/` — Settings, services, queue, scheduler, post format, post selector, URL shorteners. Models store data in WordPress options.

### Helpers

`includes/admin/helpers/` — Content manipulation, post formatting, cron scheduling, DB migrations, logging, custom API clients (Telegram, BlueSky).

### URL Shorteners

`includes/admin/shortners/` — Each shortener extends `Rop_Url_Shortner_Abstract` (Bitly, Firebase, Rebrandly, is.gd, ow.ly, rviv.ly).

### Frontend (Two Systems)

1. **Vue 2 Dashboard** (legacy) — Built via `webpack.config.js`. Entry points in `vue/src/` → output to `assets/js/build/`. Uses Vuex for state, vue-resource for HTTP.
2. **React Components** (new) — Built via `webpack.sharing.config.js` using `@wordpress/scripts`. Source in `src/` → output to `assets/js/react/build/`. Used for instant/manual sharing in the block editor.

### Cron System

`cron-system/` — Alternative remote cron implementation (`RopCronSystem\Rop_Cron_Core`). Activated when `ROP_CRON_ALTERNATIVE` is true (controlled by `rop_use_remote_cron` option).

### External Auth

Social network authentication goes through `ROP_AUTH_APP_URL` (`https://app.revive.social`) with per-service paths (`/fb_auth`, `/tw_auth`, `/li_auth`, etc.).

## Coding Standards

- **PHP:** WordPress-Core via PHPCS (`phpcs.xml`) with many naming convention rules relaxed — camelCase variables/methods are allowed throughout the codebase
- **JS (Vue):** `plugin:vue/recommended` with babel-eslint parser
- **JS (React):** `@wordpress/eslint-plugin/recommended` with text domain enforced as `tweet-old-post`
- **Static Analysis:** PHPStan level 6 with WordPress extension and baseline (`phpstan-baseline.neon`)

## Testing Structure

PHPUnit test suites are defined in `phpunit.xml` with individual files in `tests/`:
- `test-plugin.php`, `test-accounts.php`, `test-content.php`, `test-logger.php`, `test-post-format.php`, `test-queue.php`, `test-scheduler.php`, `test-selector.php`

E2E tests use Playwright with `@wordpress/e2e-test-utils-playwright`. Specs live in `tests/e2e/specs/`. Config at `tests/e2e/playwright.config.js`.

PHPUnit bootstrap (`tests/bootstrap.php`) requires WordPress test suite via `WP_TESTS_DIR` env var.
