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
