# E2E Testing

## Config

The configuration is similar to Gutenberg Project. Check [this](https://github.com/WordPress/gutenberg/tree/trunk/test/e2e) repo for more details.

To start creating new tests you need to do the following:

1. Run `npm run wp-env start`. This will create a Docker test instance. The file `.wp-env.json` is used to override some settings. [Read more here](https://developer.wordpress.org/block-editor/reference-guides/packages/packages-env/). [Changelog to check new features.](https://github.com/WordPress/gutenberg/blob/b9f2514f9e37099f6481046c4ba20fa46c2d7171/packages/env/CHANGELOG.md)
2. Run `npx install playwright`. This will the install the browser packages for Playwright to use it.
3. Run `npm run test:e2e:playwright:ui`. This will lunch Playwright in UI mode. _Recommended when developing_. You can use `npm run test:e2e:playwright` to run without UI.

If the Playwright-bundled Chromium crashes on launch (SIGTRAP, common on newer macOS with older Playwright versions), run against system Chrome instead: `PLAYWRIGHT_CHANNEL=chrome npm run test:e2e:playwright`.

## Mocks & Fixtures

No test talks to real social APIs. The pieces:

- `mu-plugins/rop-e2e-bootstrap.php` — loaded into wp-env as an mu-plugin (see `mappings` in `.wp-env.json`). It mocks the Revive Social server (`post-on-x` and `logs` endpoints) via `pre_http_request`, records every intercepted request, and registers REST endpoints under `rop-e2e/v1`:
  - `POST /reset` — clears mock state, seeded accounts, and any leftover publish-now queue (run it in `beforeEach` for isolation across retries)
  - `POST /account` — seeds an authenticated X account (`@testaccount`, token `rop-e2e-token`)
  - `POST /publish-now` — triggers the publish-now cron job for a post (wp-env runs with `DISABLE_WP_CRON`, so this is the only share trigger)
  - `POST /requests` — returns the intercepted API requests for payload assertions
- `fixtures/index.js` — extends the WordPress Playwright test with a `ropUtils` fixture wrapping those endpoints (`reset`, `seedAccount`, `runPublishNow`, `getRequests`). Import `test`/`expect` from here instead of `@wordpress/e2e-test-utils-playwright` when a spec needs plugin state.

See `specs/dashboard/publish-now.spec.js` for the full pattern: seed → publish → trigger → assert on captured payloads.

## Useful Resources

- [Playwright Tests Docs](https://playwright.dev/docs/writing-tests)
- [WordPress E2E Repo](https://github.com/WordPress/wordpress-develop/tree/trunk/tests/e2e)
- [Gutenberg E2E Repo](https://github.com/WordPress/gutenberg/tree/trunk/test/e2e)
- [Otter E2E Repo](https://github.com/Codeinwp/otter-blocks/tree/master/src/blocks/test/e2e)
