import { test, expect } from '../../fixtures';

const DOCS = 'https://docs.revive.social/';

test.describe( 'Docs links', () => {
	test.beforeEach( async ( { page, admin, ropUtils } ) => {
		await ropUtils.reset();
		await ropUtils.seedAccount();
		await admin.visitAdminPage( '/admin.php?page=TweetOldPost' );
		await page.waitForSelector( '.tab-view[type="accounts"]' );
	} );

	test( 'sidebar Docs button opens the docs with UTMs', async ( { page } ) => {
		await expect(
			page.getByRole( 'link', { name: 'Documentation', exact: true } )
		).toHaveAttribute(
			'href',
			DOCS +
				'?utm_source=wpadmin&utm_medium=dashboard&utm_campaign=documentation&utm_content=docsrevivesocial'
		);
	} );

	test( 'General Settings links open the current docs pages with UTMs', async ( {
		page,
	} ) => {
		await page.getByText( 'General Settings' ).click();

		const postTypesLink = page
			.locator( 'a', { hasText: 'Learn more' } )
			.and(
				page.locator(
					'a[href*="how-to-share-different-wordpress-post-types"]'
				)
			);
		await expect( postTypesLink ).toHaveAttribute(
			'href',
			DOCS +
				'general-guides/how-to-share-different-wordpress-post-types-to-social-media-w-revive-old-posts?utm_source=wpadmin&utm_medium=general-settings&utm_campaign=post-types&utm_content=docsrevivesocialgeneral-guideshow-to-share-different-wordpress-post-types-to-social-media-w-revive-old-posts'
		);

		// No link on the tab goes through an old /article/ redirect, which drops the UTMs.
		const docsLinks = page.locator( 'a[href^="https://docs.revive.social/"]' );
		await expect( docsLinks.first() ).toBeVisible();
		for ( const href of await docsLinks.evaluateAll( ( links ) =>
			links.map( ( link ) => link.getAttribute( 'href' ) )
		) ) {
			expect( href ).not.toContain( '/article/' );
			expect( href ).toContain( 'utm_source=wpadmin' );
		}
	} );
} );
