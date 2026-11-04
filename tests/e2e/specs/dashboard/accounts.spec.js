import { test, expect } from '../../fixtures';

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

test.describe( 'Webhook account editing', () => {
	test.beforeEach( async ( { ropUtils } ) => {
		await ropUtils.reset();
		await ropUtils.seedWebhookAccount( 'Webhook A', 'https://a.example.test/hook' );
		await ropUtils.seedWebhookAccount( 'Webhook B', 'https://b.example.test/hook' );
	} );

	test( 'an unadded header typed for one webhook is gone when editing another', async ( { admin, page } ) => {
		await admin.visitAdminPage( '/admin.php?page=TweetOldPost' );
		await page.waitForSelector( '.tab-view[type="accounts"]' );

		const modal = page.locator( '.modal.active' );
		const newHeader = modal.locator( '.webhook-headers input' ).last();

		await page.locator( '.rop-account', { hasText: 'Webhook A' } ).locator( '.rop-edit-account' ).click();
		await modal.getByRole( 'button', { name: 'Edit Headers' } ).click();
		await newHeader.fill( 'X-Draft: from-A' );
		await modal.locator( '.modal-header .btn-clear' ).click();
		await expect( modal ).toHaveCount( 0 );

		await page.locator( '.rop-account', { hasText: 'Webhook B' } ).locator( '.rop-edit-account' ).click();
		const editHeaders = modal.getByRole( 'button', { name: 'Edit Headers' } );
		await expect( modal.getByRole( 'button', { name: /^(Edit Headers|Hide)$/ } ) ).toBeVisible();
		if ( await editHeaders.isVisible() ) {
			await editHeaders.click();
		}
		await expect( newHeader ).toHaveValue( '' );
	} );
} );
