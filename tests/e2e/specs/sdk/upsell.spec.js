/**
 * WordPress dependencies
 */
const { test, expect } = require( '@wordpress/e2e-test-utils-playwright' );

test.describe( 'Upsell', () => {
    test.beforeEach( async ( { admin, requestUtils, page } ) => {
        page.setDefaultTimeout( 5000 );
    } );

    test( 'featured tab in Install Plugin', async ( { admin, page } ) => {
        await admin.visitAdminPage( 'plugin-install.php?tab=featured' );

        // Those should be visible only when a PRO product is installed.
        await expect( page.getByText('Image Optimization by Optimole') ).toBeHidden();
        await expect( page.locator('#the-list div').filter({ hasText: 'Otter Blocks' }).nth(1) ).toBeHidden();

        await expect( page.getByLabel('Install Image Optimization by') ).toBeHidden();
        await expect( page.getByLabel('Install Otter Blocks') ).toBeHidden();
    });
} );