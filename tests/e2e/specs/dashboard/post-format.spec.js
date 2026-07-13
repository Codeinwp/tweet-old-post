import { test, expect } from '../../fixtures';

test.describe('Post Format', () => {
	test.beforeEach(async ({ ropUtils }) => {
		await ropUtils.reset();
		await ropUtils.seedAccount();
	});

	test('shows the available post content options', async ({
		page,
		admin,
	}) => {
		await admin.visitAdminPage('/admin.php?page=TweetOldPost');

		// Go to Post Format tab.
		await page.getByText('Post Format').click();

		const shareContent = page.locator(
			'select:has(option[value="custom_content"])'
		);
		await expect(
			shareContent.locator('option[value="custom_content"]')
		).toHaveText('Custom Content (Pro)');

		await expect(shareContent).toHaveValue('post_title');
		await expect(
			page.getByText('Additional Text', { exact: true })
		).toBeVisible();
		await expect(page.getByText('Choose where you want the')).toBeVisible();
	});
});
