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
        await page.waitForSelector( '#default_interval' );
        await page.fill( '#default_interval', '5' );

        await expect( page.getByText('Minimum Interval Between') ).toBeVisible();
        await expect( page.$eval('#default_interval', el => el.value) ).resolves.toBe('5');

        /**
         * Check Share More Than Once toggle.
         */
        await page.locator('#share_more_than_once').first().uncheck();
       
        await expect( page.getByText('Share More Than Once?') ).toBeVisible();
        await expect( page.locator('#share_more_than_once').first().isChecked() ).resolves.toBe(false);
	} );
} );