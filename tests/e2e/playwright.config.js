/**
 * External dependencies
 */
import fs from 'fs';
import os from 'os';
import path from 'path';
import { fileURLToPath } from 'url';
import { defineConfig, devices } from '@playwright/test';

/**
 * WordPress dependencies
 */
const baseConfig = require( '@wordpress/scripts/config/playwright.config' );

// Port precedence: WP_BASE_URL > WP_ENV_PORT > .wp-env.override.json > 8889.
// wp-env maps testsPort to the Playwright target (see @wordpress/scripts default).
const getOverridePort = () => {
	try {
		const override = JSON.parse(
			fs.readFileSync(
				path.join( process.cwd(), '.wp-env.override.json' ),
				'utf8'
			)
		);

		return (
			parseInt( override.testsPort, 10 ) ||
			parseInt( override.port, 10 ) ||
			undefined
		);
	} catch ( e ) {
		return undefined;
	}
};

const WP_ENV_PORT =
	parseInt( process.env.WP_ENV_PORT || '', 10 ) ||
	getOverridePort() ||
	8889;
const WP_BASE_URL =
	process.env.WP_BASE_URL || `http://localhost:${ WP_ENV_PORT }`;

process.env.WP_BASE_URL = WP_BASE_URL;

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
	webServer: {
		...baseConfig.webServer,
		port: WP_ENV_PORT,
		reuseExistingServer: true,
	},
	use: {
		...baseConfig.use,
		baseURL: WP_BASE_URL,
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