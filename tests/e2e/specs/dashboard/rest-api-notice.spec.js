/**
 * WordPress dependencies
 */
import { test, expect } from '@wordpress/e2e-test-utils-playwright';

const NOTICE = '.rop-api-not-available';

/**
 * Match the plugin's REST endpoint on both pretty and plain permalinks.
 *
 * @param {URL} url The request URL.
 * @returns {boolean} Whether the request targets the plugin API.
 */
const isRopApiRequest = ( url ) =>
	decodeURIComponent( url.href ).includes( 'tweet-old-post/v8/api' );

/**
 * Fail the named plugin API requests, let every other one through.
 *
 * @param {import('@playwright/test').Page} page  The page object.
 * @param {Object}                          plan  Request name => delay in ms before failing it.
 *                                                The name `*` fails every plugin API request,
 *                                                which is what a REST API outage looks like.
 * @returns {Promise<Object>} Promise per request name, resolved once it failed.
 */
async function failRequests( page, plan ) {
	const settled = {};
	const resolvers = {};

	for ( const req of Object.keys( plan ) ) {
		settled[ req ] = new Promise( ( resolve ) => {
			resolvers[ req ] = resolve;
		} );
	}

	await page.route(
		( url ) => isRopApiRequest( url ),
		async ( route ) => {
			const req = new URL( route.request().url() ).searchParams.get( 'req' );
			const target = req in plan ? req : '*';

			if ( ! ( target in plan ) ) {
				await route.continue();
				return;
			}

			if ( plan[ target ] > 0 ) {
				await new Promise( ( resolve ) => setTimeout( resolve, plan[ target ] ) );
			}

			await route.abort();
			resolvers[ target ]();
		}
	);

	return settled;
}

test.describe( 'REST API notice', () => {

	test( 'is not shown when another start-up request succeeds afterwards', async ( { page, admin } ) => {
		// `get_available_services` fails immediately, so the failure settles
		// before the two requests that go through.
		const failed = await failRequests( page, { get_available_services: 0 } );

		await admin.visitAdminPage( '/admin.php?page=TweetOldPost' );
		await failed.get_available_services;

		// The successful requests still render the dashboard.
		await page.waitForSelector( '.tab-view[type="accounts"]' );
		await expect( page.locator( NOTICE ) ).toBeHidden();
	} );

	test( 'is not shown when a start-up request fails after a success', async ( { page, admin } ) => {
		// Reverse completion order: the failure is held back so it settles
		// last, after the other two requests have already succeeded.
		const failed = await failRequests( page, { get_active_accounts: 2000 } );

		await admin.visitAdminPage( '/admin.php?page=TweetOldPost' );
		await page.waitForSelector( '.tab-view[type="accounts"]' );
		await failed.get_active_accounts;

		await expect( page.locator( NOTICE ) ).toBeHidden();
	} );

	test( 'is shown when the REST API is unreachable', async ( { page, admin } ) => {
		// A real outage takes down every plugin API request, not just the
		// three dispatched from the dashboard's `created()` hook.
		const failed = await failRequests( page, { '*': 0 } );

		await admin.visitAdminPage( '/admin.php?page=TweetOldPost' );
		await failed[ '*' ];

		await expect( page.locator( NOTICE ) ).toBeVisible();
		await expect( page.locator( NOTICE ) ).toContainText( 'core REST API functionality is not available' );
	} );
} );
