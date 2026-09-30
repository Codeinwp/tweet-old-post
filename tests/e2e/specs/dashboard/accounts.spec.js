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

    test( 'Opening an account connection keeps available networks unlocked', async ( { page } ) => {
        const facebook = page.getByRole( 'button', { name: 'Facebook' } ).first();
        const linkedIn = page.getByRole( 'button', { name: 'LinkedIn' } );

        await expect( facebook ).toBeVisible();
        await expect( facebook.getByRole( 'img' ) ).toHaveCount( 0 );
        await expect( linkedIn.getByRole( 'img' ) ).toHaveCount( 1 );

        await facebook.click();

        await expect( page.getByRole( 'button', { name: /sign in to facebook/i } ) ).toBeVisible();
        await expect( facebook.getByRole( 'img' ) ).toHaveCount( 0 );
        await expect( linkedIn.getByRole( 'img' ) ).toHaveCount( 1 );
    } );

	test( 'Social Accounts', async ( { admin, page }) => {
        await expect( page.getByRole('button', { name: 'Facebook' }) ).toBeVisible();
        await expect( page.getByRole('button', { name: 'Twitter' }) ).toBeVisible();
        await expect( page.getByRole('button', { name: 'LinkedIn' }) ).toBeVisible();
        await expect( page.getByRole('button', { name: 'Tumblr' }) ).toBeVisible();
        await expect( page.getByRole('button', { name: 'GMB' }) ).toBeVisible();
        await expect( page.getByRole('button', { name: 'Vk' }) ).toBeVisible();
        await expect( page.getByRole('button', { name: 'Webhook' }) ).toBeVisible();
	} );
} );