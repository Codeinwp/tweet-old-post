import { test, expect } from '../../fixtures';
import { tryCloseTourModal } from '../../utils';

test.describe('Publish Now', () => {
	test.beforeEach(async ({ page, admin, ropUtils }) => {
		await ropUtils.reset();
		await ropUtils.seedAccount();
		await admin.createNewPost({ title: 'Test Post' });
		await tryCloseTourModal(page);
	});

	test('shares a published post through the mocked X process', async ({
		page,
		ropUtils,
		requestUtils,
	}, testInfo) => {
		await page.getByRole('button', { name: 'Revive Social' }).click();

		const shareImmediately = page.getByRole('checkbox', {
			name: 'Share Immediately',
		});
		await expect(shareImmediately).toBeChecked();
		await expect(
			page.getByRole('checkbox', { name: /@testaccount/ })
		).toBeChecked();

		await page
			.getByRole('button', { name: 'Publish', exact: true })
			.click();
		const publishPanel = page.getByLabel('Editor publish');
		await expect(
			publishPanel.getByRole('button', { name: 'Instant Sharing' })
		).toBeVisible();
		await publishPanel
			.getByRole('button', { name: 'Publish', exact: true })
			.click();

		await expect(page.getByText('Posting to social media…')).toBeVisible();
		const postId = await page.evaluate(() =>
			wp.data.select('core/editor').getCurrentPostId()
		);
		await ropUtils.runPublishNow(postId);

		const viewHistory = publishPanel.getByRole('button', {
			name: 'View History',
		});
		await expect(viewHistory).toBeVisible({ timeout: 15000 });
		await expect(page.getByText('Posting to social media…')).toBeHidden();
		await viewHistory.click();
		const history = page.getByRole('dialog', { name: 'Sharing History' });
		await expect(history.getByText('@testaccount')).toBeVisible();
		await expect(history.getByText('Success')).toBeVisible();

		const requests = await ropUtils.getRequests();
		await testInfo.attach('rop-social-requests', {
			body: JSON.stringify(requests, null, 2),
			contentType: 'application/json',
		});
		const shareRequest = requests.find((request) =>
			request.url.endsWith('/post-on-x')
		);
		const logRequest = requests.find((request) =>
			request.url.endsWith('/logs')
		);
		expect(requests).toHaveLength(2);
		expect(shareRequest).toBeDefined();
		expect(logRequest).toBeDefined();
		expect(shareRequest.body).toMatchObject({
			sharing_type: 'tw',
			rop_auth_token: 'rop-e2e-token',
		});
		expect(shareRequest.body.post_data.text).toContain('Test Post');
		expect(logRequest.body).toMatchObject({
			network: 'twitter',
			handle: '@testaccount',
			content: 'Test Post',
		});
		const permalink = new URL(
			await requestUtils.rest({ path: `/wp/v2/posts/${postId}` }).then((post) => post.link)
		);
		expect(logRequest.body.link).toContain(permalink.pathname);
	});
});
