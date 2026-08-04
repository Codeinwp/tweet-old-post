/**
 * WordPress dependencies
 */
import { test, expect } from '@wordpress/e2e-test-utils-playwright';

test.describe( 'Accounts', () => {

    test.beforeEach( async ( { page, admin } ) => {
        await admin.visitAdminPage( '/admin.php?page=TweetOldPost' );

        // Wait for the accounts tab to load.
        await page.waitForSelector( '.tab-view[type="accounts"]' );

        await page.getByText('General Settings').click();
    } );

	test( 'Can change inputs', async ( { page }) => {

        /**
         * Check Minimum Interval Between Shares input field.
         */
        const interval = page.locator( '#default_interval' );
        await interval.waitFor();
        // Wait for Vue to bind the saved value before editing.
        await expect( interval ).not.toHaveValue( '' );
        await interval.fill( '20' );

        await expect( page.getByText('Minimum Interval Between') ).toBeVisible();
        await expect( interval ).toHaveValue( '20' );

        /**
         * Check Share More Than Once toggle.
         */
        await page.locator('#share_more_than_once').first().uncheck();
       
        await expect( page.getByText('Share More Than Once?') ).toBeVisible();
        await expect( page.locator('#share_more_than_once').first().isChecked() ).resolves.toBe(false);
	} );
} );