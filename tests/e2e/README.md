# E2E Testing

## Config

The configuration is similar to Gutenberg Project. Check [this](https://github.com/WordPress/gutenberg/tree/trunk/test/e2e) repo for more details.

To start creating new tests you need to do the following:

1. Run `npm run wp-env start`. This will create a Docker test instance. The file `.wp-env.json` is used to override some settings. [Read more here](https://developer.wordpress.org/block-editor/reference-guides/packages/packages-env/). [Changelog to check new features.](https://github.com/WordPress/gutenberg/blob/b9f2514f9e37099f6481046c4ba20fa46c2d7171/packages/env/CHANGELOG.md)
2. Run `npx install playwright`. This will the install the browser packages for Playwright to use it.
3. Run `npm run test:e2e:playwright:ui`. This will lunch Playwright in UI mode. _Recommended when developing_. You can use `npm run test:e2e:playwright` to run without UI.

## Useful Resources

- [Playwright Tests Docs](https://playwright.dev/docs/writing-tests)
- [WordPress E2E Repo](https://github.com/WordPress/wordpress-develop/tree/trunk/tests/e2e)
- [Gutenberg E2E Repo](https://github.com/WordPress/gutenberg/tree/trunk/test/e2e)
- [Otter E2E Repo](https://github.com/Codeinwp/otter-blocks/tree/master/src/blocks/test/e2e)
