# How to contribute to the project.

## Development Setup

This projects requires you to have Node.js (with npm) and Composer.

- You can run `npm ci` & `composer install` to install dependencies.
- Once done, you can run `npm run build` to generate build files.
- You can also use `npm run dev` to generate dev build if you are working on the files.

> [!NOTE]  
> The minimum Node version is 16. The version recommended is 18 or higher (tested up to 21). For PHP, use 7.4.

## E2E Testing

We use Playwright to write the E2E together with WordPress tool set.

Use `npm run wp-env start` to start the testing environment.

> [!NOTE]  
> The testing instance has a port that need to be free to work with setup (usually the port 8889).

Install Playwright with `npm install -g playwright-cli` and `npx playwright install`.

Use `npm run test:e2e:playwright:ui` to lunch it in UI mode for easy development.
