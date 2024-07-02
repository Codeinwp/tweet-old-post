/**
 * WordPress dependencies
 */
import { test, expect } from '@wordpress/e2e-test-utils-playwright';
import { addFakeTwitterAccount } from '../../utils';

test.describe( 'Accounts', () => {

    test.beforeEach( async ( { page, admin } ) => {
        await admin.visitAdminPage( '/admin.php?page=TweetOldPost' );

        // Wait for the accounts tab to load.
        await page.waitForSelector( '.tab-view[type="accounts"]' );

        const accountAdded = await addFakeTwitterAccount( page );

        expect( accountAdded ).toBe( true );
    } );

    test( 'check custom content post message', async ( { page, admin } ) => {
        await admin.visitAdminPage( '/admin.php?page=TweetOldPost' );

        // Go to Post Format tab.
        await page.getByText('Post Format').click();

        // Activate Custom Content for the post message.
        // Note: The real user can not select the option since it is disabled, but the automated test can.
        await page.getByRole('combobox').first().selectOption( 'Custom Content (Pro)' );

        // Check UI elements.
        await expect( page.getByText('Message Content') ).toBeVisible();
        await expect( page.getByPlaceholder('{title} with {content}') ).toBeVisible();
        await expect( page.getByText('Choose where you want the') ).toBeHidden();
    } );
	
} );