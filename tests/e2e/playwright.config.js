/**
 * External dependencies
 */
import os from 'os';
import path from 'path';
import { fileURLToPath } from 'url';
import { defineConfig, devices } from '@playwright/test';

/**
 * WordPress dependencies
 */
const baseConfig = require( '@wordpress/scripts/config/playwright.config' );

const config = defineConfig( {
	...baseConfig,
	forbidOnly: Boolean( process.env.CI ),
	retries: process.env.CI ? 2 : 0,
	outputDir: path.resolve( 'test-results' ),
	reporter: process.env.CI
		? [
				[ 'list' ],
				[ './config/flaky-tests-reporter.js' ],
				[ 'html', { outputFolder: 'playwright-report', open: 'never' } ],
			]
		: 'list',
	workers: 1,
	use: {
		...baseConfig.use,
		channel: process.env.PLAYWRIGHT_CHANNEL,
		trace: 'retain-on-failure',
		screenshot: 'only-on-failure',
		video: 'on-first-retry',
	},
	globalSetup: fileURLToPath(
		new URL( './config/global-setup.js', 'file:' + __filename ).href
	),
	projects: [
		{
			name: 'chromium',
			use: { ...devices[ 'Desktop Chrome' ] },
			grepInvert: /-chromium/,
		},
	],
} );

export default config;