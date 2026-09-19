import { test, expect } from '../../fixtures';

// base64( serialize( 'urn:li:person:E2ETEST' ) )
const VALID_ID = 'czoyMToidXJuOmxpOnBlcnNvbjpFMkVURVNUIjs=';
// base64( serialize( [ account array, [ 'notify_user_at' => 4102444800 ] ] ) )
const VALID_PAGES = 'YToyOntpOjA7YTo2OntzOjI6ImlkIjtzOjIxOiJ1cm46bGk6cGVyc29uOkUyRVRFU1QiO3M6MzoiaW1nIjtzOjA6IiI7czo3OiJhY2NvdW50IjtzOjEzOiJFMkUgVGVzdCBVc2VyIjtzOjEwOiJpc19jb21wYW55IjtiOjA7czo0OiJ1c2VyIjtzOjEzOiJFMkUgVGVzdCBVc2VyIjtzOjEyOiJhY2Nlc3NfdG9rZW4iO3M6MTQ6ImUyZS10ZXN0LXRva2VuIjt9aToxO2E6MTp7czoxNDoibm90aWZ5X3VzZXJfYXQiO2k6NDEwMjQ0NDgwMDt9fQ==';
// Same as VALID_PAGES but the last entry is not the notify date.
const PAGES_WITHOUT_NOTIFY = 'YToyOntpOjA7YTo2OntzOjI6ImlkIjtzOjIxOiJ1cm46bGk6cGVyc29uOkUyRVRFU1QiO3M6MzoiaW1nIjtzOjA6IiI7czo3OiJhY2NvdW50IjtzOjEzOiJFMkUgVGVzdCBVc2VyIjtzOjEwOiJpc19jb21wYW55IjtiOjA7czo0OiJ1c2VyIjtzOjEzOiJFMkUgVGVzdCBVc2VyIjtzOjEyOiJhY2Nlc3NfdG9rZW4iO3M6MTQ6ImUyZS10ZXN0LXRva2VuIjt9aToxO2E6MTp7czoxMDoidW5leHBlY3RlZCI7aToxO319';
// base64( serialize( [ [ 'notify_user_at' => ... ] ] ) ) — notify entry only, no accounts.
const PAGES_ONLY_NOTIFY = 'YToxOntpOjA7YToxOntzOjE0OiJub3RpZnlfdXNlcl9hdCI7aTo0MTAyNDQ0ODAwO319';
// base64( serialize( [ 'bad-account', [ 'notify_user_at' => 4102444800 ] ] ) ) — account entry is a string, not an array.
const PAGES_WITH_STRING_ACCOUNT = 'YToyOntpOjA7czoxMToiYmFkLWFjY291bnQiO2k6MTthOjE6e3M6MTQ6Im5vdGlmeV91c2VyX2F0IjtpOjQxMDI0NDQ4MDA7fX0=';
// base64( serialize( [ 'urn:li:person:E2ETEST' ] ) ) — id decodes to an array instead of a string.
const ARRAY_ID = 'YToxOntpOjA7czoyMToidXJuOmxpOnBlcnNvbjpFMkVURVNUIjt9';

/**
 * Call a plugin API endpoint from the dashboard page.
 *
 * @param {import('@playwright/test').Page} page The page object.
 * @param {string} req  The API method to call.
 * @param {Object} body The request payload.
 */
async function callRopApi( page, req, body ) {
	return await page.evaluate( async ( { req, body } ) => {
		// `root` carries a query string only on plain permalinks; let URL sort
		// out `?` vs `&` the way the plugin's own `params` option does.
		const url = new URL( window.ropApiSettings.root, window.location.href );
		url.searchParams.set( 'req', req );

		const response = await fetch( url.toString(), {
			method: 'POST',
			body: JSON.stringify( body ),
			headers: {
				'Content-Type': 'application/json',
				'X-WP-Nonce': window.ropApiSettings.nonce,
			},
		} );

		const text = await response.text();
		let json = null;
		try {
			json = JSON.parse( text );
		} catch ( e ) {
			// Non-JSON response, e.g. the plugin's ROP_DEBUG output.
		}

		return { status: response.status, body: json, text };
	}, { req, body } );
}

test.describe( 'LinkedIn error handling (issue #1098)', () => {

	test.beforeEach( async ( { page, admin, ropUtils } ) => {
		// Start every scenario with no service registered. The happy-path test
		// adds a LinkedIn account and its own cleanup does not run when it
		// fails, so a retry would otherwise inherit that account.
		await ropUtils.reset();
		await admin.visitAdminPage( '/admin.php?page=TweetOldPost' );
		await page.waitForSelector( '.tab-view[type="accounts"]' );
	} );

	test( 'error payload without pages is rejected without a fatal error', async ( { page } ) => {
		const response = await callRopApi( page, 'add_account_li', { id: VALID_ID } );

		// Before the fix this fataled (array_pop on bool) and surfaced as a
		// WordPress critical-error response. After the fix the payload is
		// rejected by validation: with ROP_DEBUG on the plugin answers with
		// its debug text, in production with a JSON code 400 response.
		expect( response.text ).not.toContain( 'critical error' );
		if ( response.body ) {
			expect( response.body.code ).toBe( '400' );
		} else {
			expect( response.text ).toContain( 'Value not set' );
		}

		// The dashboard must survive the failed attempt.
		await page.reload();
		await page.waitForSelector( '.tab-view[type="accounts"]' );
		await expect( page.getByRole( 'button', { name: 'LinkedIn' } ) ).toBeVisible();
	} );

	test( 'array-valued id and pages are rejected without a fatal error', async ( { page } ) => {
		// `is_set_not_empty()` accepts arrays, so these reach `base64_decode()`
		// and used to raise a PHP 8 TypeError before the string guard.
		const response = await callRopApi( page, 'add_account_li', {
			id: [ VALID_ID ],
			pages: [ VALID_PAGES ],
		} );

		expect( response.text ).not.toContain( 'critical error' );
		expect( response.status ).toBe( 200 );
		expect( response.body.code ).toBe( '400' );
	} );

	test( 'garbled pages payload is rejected without a fatal error', async ( { page } ) => {
		const response = await callRopApi( page, 'add_account_li', {
			id: VALID_ID,
			pages: btoa( 'linkedin-error-string-not-account-data' ),
		} );

		expect( response.status ).toBe( 200 );
		expect( response.body.code ).toBe( '400' );

		// No LinkedIn service must have been registered from the bad payload.
		const services = await callRopApi( page, 'get_authenticated_services', {} );
		const serviceNames = Object.values( services.body.data || {} ).map( ( s ) => s.service );
		expect( serviceNames ).not.toContain( 'linkedin' );
	} );

	test( 'empty payload is rejected without a fatal error', async ( { page } ) => {
		const response = await callRopApi( page, 'add_account_li', {} );

		expect( response.text ).not.toContain( 'critical error' );
		if ( response.body ) {
			expect( response.body.code ).toBe( '400' );
		} else {
			expect( response.text ).toContain( 'Value not set' );
		}
	} );

	test( 'pages without a notify entry are rejected', async ( { page } ) => {
		const response = await callRopApi( page, 'add_account_li', {
			id: VALID_ID,
			pages: PAGES_WITHOUT_NOTIFY,
		} );

		expect( response.status ).toBe( 200 );
		expect( response.body.code ).toBe( '400' );
	} );

	test( 'pages with only a notify entry and no accounts are rejected', async ( { page } ) => {
		const response = await callRopApi( page, 'add_account_li', {
			id: VALID_ID,
			pages: PAGES_ONLY_NOTIFY,
		} );

		expect( response.status ).toBe( 200 );
		expect( response.body.code ).toBe( '400' );

		const services = await callRopApi( page, 'get_authenticated_services', {} );
		const serviceNames = Object.values( services.body.data || {} ).map( ( s ) => s.service );
		expect( serviceNames ).not.toContain( 'linkedin' );
	} );

	test( 'account entry that is not an array is rejected', async ( { page } ) => {
		const response = await callRopApi( page, 'add_account_li', {
			id: VALID_ID,
			pages: PAGES_WITH_STRING_ACCOUNT,
		} );

		expect( response.status ).toBe( 200 );
		expect( response.body.code ).toBe( '400' );
	} );

	test( 'id that does not decode to a string is rejected', async ( { page } ) => {
		const response = await callRopApi( page, 'add_account_li', {
			id: ARRAY_ID,
			pages: VALID_PAGES,
		} );

		expect( response.status ).toBe( 200 );
		expect( response.body.code ).toBe( '400' );
	} );

	test( 'rejected payload leaves the LinkedIn error in the plugin log', async ( { page } ) => {
		// Earlier tests in this spec log the same entry — clear the log first
		// so the assertion can only be satisfied by this request.
		await callRopApi( page, 'get_log', { force: true } );

		await callRopApi( page, 'add_account_li', {
			id: VALID_ID,
			pages: btoa( 'linkedin-error-string-not-account-data' ),
		} );

		const log = await callRopApi( page, 'get_log', {} );
		expect( JSON.stringify( log.body.data ) ).toContain( 'Linkedin Error' );
	} );

	test( 'valid payload still adds the account', async ( { page } ) => {
		const response = await callRopApi( page, 'add_account_li', {
			id: VALID_ID,
			pages: VALID_PAGES,
		} );

		expect( response.status ).toBe( 200 );
		expect( response.body.code ).toBe( '200' );

		// The service must actually be registered, with the account exposed.
		const services = await callRopApi( page, 'get_authenticated_services', {} );
		const linkedin = Object.values( services.body.data || {} ).find(
			( s ) => s.service === 'linkedin'
		);
		expect( linkedin ).toBeTruthy();
		expect( JSON.stringify( linkedin.available_accounts ) ).toContain( 'E2E Test User' );

		// Clean up so other specs start from a pristine accounts state.
		const reset = await callRopApi( page, 'reset_accounts', {} );
		expect( reset.body.code ).toBe( '200' );
	} );
} );
