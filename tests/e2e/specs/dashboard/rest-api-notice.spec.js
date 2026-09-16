import { test, expect } from '../../fixtures';

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
 * Take control of the plugin's REST requests.
 *
 * Aborting a request resolves on the test side before the browser has run the
 * store's rejection handler, so nothing here is used as a synchronisation
 * point — each test waits on an observable effect instead.
 *
 * @param {import('@playwright/test').Page} page          The page object.
 * @param {Object}                          plan          What to do with each request.
 * @param {string[]}                        plan.fail     Request names to abort on arrival, or `*` for every one.
 * @param {string[]}                        plan.hold     Request names to park until `release()` is called, or `*` for every one.
 * @returns {Promise<Object>} Controller exposing `release()`.
 */
async function controlRopApi( page, { fail = [], hold = [] } = {} ) {
	const parked = [];
	let released = false;

	const listed = ( list, req ) => list.includes( '*' ) || list.includes( req );

	await page.route(
		( url ) => isRopApiRequest( url ),
		async ( route ) => {
			const req = new URL( route.request().url() ).searchParams.get( 'req' );

			if ( listed( fail, req ) ) {
				await route.abort();
				return;
			}

			if ( ! released && listed( hold, req ) ) {
				parked.push( route );
				return;
			}

			await route.continue();
		}
	);

	return {
		/**
		 * Let the parked requests through, or fail them.
		 *
		 * @param {string} how Either `continue` or `abort`.
		 */
		release: async ( how = 'continue' ) => {
			released = true;
			for ( const route of parked.splice( 0 ) ) {
				await ( 'abort' === how ? route.abort() : route.continue() );
			}
		},
	};
}

/**
 * Resolve once the dashboard has finished handling a failed start-up request.
 *
 * `rop_main.js` logs this from the `.catch()` on the dispatch, which runs after
 * the store has committed the failure — so it is the first moment at which the
 * notice would be visible if the failure still raised it.
 *
 * @param {import('@playwright/test').Page} page The page object.
 * @param {string}                          req  Name of the failing request.
 * @returns {Promise} Promise resolved once the failure was handled.
 */
const startupFailureHandled = ( page, req ) =>
	page.waitForEvent( 'console', ( message ) =>
		message.text().includes( `Could not load ${ req } when starting the dashboard` )
	);

test.describe( 'REST API notice', () => {

	test( 'is cleared when a start-up request succeeds after a failure', async ( { page, admin } ) => {
		// Park every request so the failure is the first outcome the dashboard
		// sees, and the notice is provably on before any success arrives.
		const api = await controlRopApi( page, {
			fail: [ 'get_available_services' ],
			hold: [ '*' ],
		} );

		await admin.visitAdminPage( '/admin.php?page=TweetOldPost' );
		await expect( page.locator( NOTICE ) ).toBeVisible();

		// Now let the successes through: the recovery must remove the notice.
		await api.release();
		await page.waitForSelector( '.tab-view[type="accounts"]' );
		await expect( page.locator( NOTICE ) ).toBeHidden();
	} );

	test( 'is not raised when a start-up request fails after a success', async ( { page, admin } ) => {
		// Reverse completion order: the failing request is parked until the
		// other two have succeeded and rendered the dashboard.
		const api = await controlRopApi( page, { hold: [ 'get_active_accounts' ] } );

		await admin.visitAdminPage( '/admin.php?page=TweetOldPost' );
		await page.waitForSelector( '.tab-view[type="accounts"]' );
		await expect( page.locator( NOTICE ) ).toBeHidden();

		const handled = startupFailureHandled( page, 'get_active_accounts' );
		await api.release( 'abort' );
		await handled;

		await expect( page.locator( NOTICE ) ).toBeHidden();
	} );

	test( 'is raised when the REST API goes down after the dashboard loaded', async ( { page, admin, ropUtils } ) => {
		// An active account is what enables the start/stop sharing button.
		await ropUtils.reset();
		await ropUtils.seedAccount();

		await admin.visitAdminPage( '/admin.php?page=TweetOldPost' );
		await page.waitForSelector( '.tab-view[type="accounts"]' );
		await expect( page.locator( NOTICE ) ).toBeHidden();

		// The API goes down only now, after start-up has already succeeded.
		await controlRopApi( page, { fail: [ '*' ] } );
		await page.locator( '#rop_start_stop_btn' ).click();

		await expect( page.locator( NOTICE ) ).toBeVisible();

		await ropUtils.reset();
	} );

	test( 'is shown when the REST API is unreachable', async ( { page, admin } ) => {
		// A real outage takes down every plugin API request, not just the
		// three dispatched from the dashboard's `created()` hook.
		await controlRopApi( page, { fail: [ '*' ] } );

		await admin.visitAdminPage( '/admin.php?page=TweetOldPost' );

		await expect( page.locator( NOTICE ) ).toBeVisible();
		await expect( page.locator( NOTICE ) ).toContainText( 'core REST API functionality is not available' );
	} );
} );
