import { test, expect } from '../../fixtures';
import { tryCloseTourModal } from '../../utils';

const DAY = 24 * 60 * 60;

/**
 * Regression coverage for #1102.
 *
 * A site whose cron stalled for months accumulates `queued` instant-share
 * entries. When cron recovered the drain ran oldest-modified first with no
 * cutoff, flooding the accounts with archive content while the post the author
 * just published waited behind the backlog.
 *
 * The queue can be drained either by the explicit `/publish-now` trigger or by
 * a wp-cron run, so these assertions look at what reached the mocked API
 * rather than at who drained it.
 */
test.describe('Publish Now backlog', () => {
	test.beforeEach(async ({ ropUtils }) => {
		await ropUtils.reset();
		await ropUtils.seedAccount();
	});

	/**
	 * Wait for a post to leave the mocked API as a share request.
	 *
	 * @param {Object} ropUtils The ROP fixture.
	 * @param {string} title    Post title expected in the payload.
	 */
	const waitForShare = async (ropUtils, title) => {
		await expect
			.poll(
				async () => {
					const requests = await ropUtils.getRequests();

					return requests.some(
						(request) =>
							request.url.endsWith('/post-on-x') &&
							JSON.stringify(request.body).includes(title)
					);
				},
				{ timeout: 30000 }
			)
			.toBe(true);
	};

	test('drops the stale backlog and shares only the fresh post', async ({
		page,
		admin,
		ropUtils,
	}, testInfo) => {
		const backlogIds = [];
		for (const [ index, age ] of [ 60 * DAY, 45 * DAY, 30 * DAY ].entries()) {
			backlogIds.push(
				await ropUtils.seedQueuedPost(`Backlog Post ${index + 1}`, age)
			);
		}

		await admin.createNewPost({ title: 'Breaking News' });
		await tryCloseTourModal(page);

		await page.getByRole('button', { name: 'Revive Social' }).click();
		await expect(
			page.getByRole('checkbox', { name: 'Share Immediately' })
		).toBeChecked();

		await page
			.getByRole('button', { name: 'Publish', exact: true })
			.click();
		const publishPanel = page.getByLabel('Editor publish');
		await publishPanel
			.getByRole('button', { name: 'Publish', exact: true })
			.click();

		const freshId = await page.evaluate(() =>
			wp.data.select('core/editor').getCurrentPostId()
		);

		// The share runs server-side; wait for the plugin to have picked the
		// post up before draining, so the trigger cannot outrun the save.
		await expect
			.poll(
				async () =>
					(await ropUtils.getPublishNowState(freshId)).status,
				{ timeout: 20000 }
			)
			.not.toBe('pending');

		await ropUtils.runPublishNow(freshId);
		await waitForShare(ropUtils, 'Breaking News');

		const requests = await ropUtils.getRequests();
		await testInfo.attach('rop-social-requests', {
			body: JSON.stringify(requests, null, 2),
			contentType: 'application/json',
		});

		const shareRequests = requests.filter((request) =>
			request.url.endsWith('/post-on-x')
		);
		expect(shareRequests).toHaveLength(1);

		for (const request of requests) {
			expect(JSON.stringify(request.body)).not.toContain('Backlog Post');
		}

		// The backlog is retired rather than left queued for the next drain.
		for (const postId of backlogIds) {
			const state = await ropUtils.getPublishNowState(postId);
			expect(state.status).not.toBe('queued');
			expect(state.history[0].status).toBe('expired');
		}
	});

	test('still shares an entry queued moments ago', async ({ ropUtils }) => {
		const postId = await ropUtils.seedQueuedPost('Just Queued', 30);

		await ropUtils.runPublishNow(postId);
		await waitForShare(ropUtils, 'Just Queued');

		const requests = await ropUtils.getRequests();
		const shareRequests = requests.filter((request) =>
			request.url.endsWith('/post-on-x')
		);
		expect(shareRequests).toHaveLength(1);
	});
});
