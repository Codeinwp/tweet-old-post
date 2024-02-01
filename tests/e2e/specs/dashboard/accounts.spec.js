/**
 * WordPress dependencies
 */
import { test, expect } from '@wordpress/e2e-test-utils-playwright';

test.describe( 'Accounts', () => {

    test.beforeEach( async ( { page, admin } ) => {
        await admin.visitAdminPage( '/admin.php?page=TweetOldPost' );

        // Wait for the accounts tab to load.
        await page.waitForSelector( '.tab-view[type="accounts"]' );
    } );

	test( 'Social Accounts', async ( { admin, page }) => {
        await expect( page.getByRole('button', { name: 'Facebook' }) ).toBeVisible();
        await expect( page.getByRole('button', { name: 'Twitter' }) ).toBeVisible();
        await expect( page.getByRole('button', { name: 'LinkedIn' }) ).toBeVisible();
        await expect( page.getByRole('button', { name: 'Tumblr' }) ).toBeVisible();
        await expect( page.getByRole('button', { name: 'GMB' }) ).toBeVisible();
        await expect( page.getByRole('button', { name: 'Vk' }) ).toBeVisible();
	} );
} );