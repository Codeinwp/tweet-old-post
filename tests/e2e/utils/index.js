
/**
 * Add a fake Twitter account to the plugin. Must be used in the plugin dashboard page.
 * 
 * @param {import("@playwright/test").Page} page The page object. 
 */
export async function addFakeTwitterAccount( page ) {
    return await page.evaluate( async () => {
        const response = await fetch( `${window.ropApiSettings.root}&req=add_account_tw`, {
            method: 'POST',
            body: JSON.stringify({
                id: 'aToxMzEwNTQ2NjkzMDU4OTczNj==',
                pages: {
                    id: '13105466930589737',
                    name: 'Test Account',
                    screen_name: 'testaccount',
                    profile_image_url_https: 'https://pbs.twimg.com/profile_images/1331227579035119618/UnO-tehU_normal.jpg',
                    credentials: {
                        rop_auth_token: '13105466930'
                    },
                    activate_account: true,
                }
            }),
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': window.ropApiSettings.nonce,
            },
        } );

        return response.ok;
    } );
}

/**
 * Close the tour modal if it is visible.
 *
 * @param {import('playwright').Page} page The page object.
 */
export async function tryCloseTourModal( page ) {
	if (await page.getByRole('button', { name: 'Skip' }).isVisible()) {
		await page.getByRole('button', { name: 'Skip' }).click();
		await page.waitForTimeout(500);
	}
}
