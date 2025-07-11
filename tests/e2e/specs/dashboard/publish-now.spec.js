/**
 * WordPress dependencies
 */
import { test, expect } from '@wordpress/e2e-test-utils-playwright';
import {
    tryCloseTourModal,
    addFakeTwitterAccount
} from '../../utils';

test.describe( 'Publish Now', () => {

    test.beforeEach( async ( { page, admin } ) => {
        await admin.visitAdminPage( '/admin.php?page=TweetOldPost' );

        // Wait for the accounts tab to load.
        await page.waitForSelector( '.tab-view[type="accounts"]' );
        
        const accountAdded = await addFakeTwitterAccount( page );
        
        expect( accountAdded ).toBe( true );

        await admin.createNewPost({
            title: 'Test Post'
        });
        await tryCloseTourModal( page );
    } );

	test( 'Instant Share', async ( { admin, page }) => {
        await page.getByRole( 'button', { name: 'Revive Social' } ).click();

        // Make sure Instant Share button is visible and checked by default.
        const shareImmediatelyCheckbox = page.getByRole( 'checkbox', { name: 'Share Immediately' } );
        await expect( shareImmediatelyCheckbox ).toBeVisible();
        await expect( shareImmediatelyCheckbox ).toBeChecked();

        // Make sure Twitter account is selected by default.
        const twitterCheckbox = page.getByRole( 'checkbox', { name: ' @testaccount' } );
        await expect( twitterCheckbox ).toBeVisible();
        await expect( twitterCheckbox ).toBeChecked();

        const publishButton = page.getByRole( 'button', { name: 'Publish', exact: true } );
        await expect( publishButton ).toBeVisible();
        await publishButton.click();

        // Make sure Pre Publish modal is visible.
        await expect( page.getByLabel( 'Editor publish' ).getByRole( 'button', { name: 'Instant Sharing' } ) ).toBeVisible();

        await page.getByLabel( 'Editor publish' ).getByRole( 'button', { name: 'Publish', exact: true }).click();

        await page.waitForTimeout( 5000 );

        // We make sure post-publish status is visible and manual sharing is visible.
        await expect( page.locator('div').filter({ hasText: /^Posting to social media…$/ }).first() ).toBeVisible();
        await expect( page.getByLabel( 'Editor publish' ).getByRole( 'button', { name: 'Manual Sharing' } ) ).toBeVisible();
	} );
} );