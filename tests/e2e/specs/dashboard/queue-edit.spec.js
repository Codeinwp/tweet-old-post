import { test, expect } from '../../fixtures';
import { tryCloseTourModal } from '../../utils';

/**
 * @typedef {Object} QueueItem
 * @property {number} time
 * @property {number} post_id
 * @property {{time: number, account_id: string, date: string, post_id: number, content: QueueContent}} post_data
 */

/**
 * @typedef {Object} QueueContent
 * @property {string}           content
 * @property {''}               hashtags
 * @property {false}            post_with_image
 * @property {''}               post_image
 * @property {''}               post_url
 * @property {''}               short_url_service
 * @property {{type: ''}}       mimetype
 */

/**
 * @typedef {Object} QueueUpdate
 * @property {string}                               account_id
 * @property {number}                               post_id
 * @property {{text?: string, image?: string|null}} custom_data
 */

/**
 * Queue editing, skipping and the Business plan gate all live in the Pro
 * add-on, so the dashboard runs against a mocked queue API and a rewritten
 * license type. What is asserted is the payload the dashboard sends.
 */
test.describe('Sharing Queue edit', () => {
	/** @type {string} */
	let accountId;

	test.beforeEach(async ({ ropUtils }) => {
		await ropUtils.reset();
		accountId = (await ropUtils.seedAccount()).accountId;
	});

	// Every queue response triggers a cron status fetch that can outlive the test.
	test.afterEach(async ({ page }) => {
		await page.unrouteAll({ behavior: 'ignoreErrors' });
	});

	/**
	 * Build a queue entry for the seeded account.
	 *
	 * @param {number} postId  Post ID.
	 * @param {string} content Card content.
	 * @param {number} time    Scheduled timestamp.
	 * @return {QueueItem} Queue entry.
	 */
	const queueItem = (postId, content, time) => ({
		time,
		post_id: postId,
		post_data: {
			time,
			account_id: accountId,
			date: 'Tomorrow',
			post_id: postId,
			content: {
				content,
				hashtags: '',
				post_with_image: false,
				post_image: '',
				post_url: '',
				short_url_service: '',
				mimetype: { type: '' },
			},
		},
	});

	test('saving an untouched card after a skip keeps the previous caption off the next post', async ({
		page,
		admin,
	}) => {
		/** @type {QueueItem[]} */
		let queue = [
			queueItem(101, 'Content of post A', 2000000000),
			queueItem(102, 'Content of post B', 2000003600),
			queueItem(103, 'Content of post C', 2000007200),
		];
		/** @type {QueueUpdate[]} */
		const updates = [];

		await page.route(
			(url) => url.searchParams.get('page') === 'TweetOldPost',
			async (route) => {
				const response = await route.fetch();
				const body = (await response.text()).replace(
					/"license_type":"-?\d+"/,
					'"license_type":"3"'
				);
				await route.fulfill({ response, body });
			}
		);

		await page.route(
			(url) =>
				decodeURIComponent(url.href).includes('tweet-old-post/v8/api'),
			async (route) => {
				const request = route.request();
				const req = new URL(request.url()).searchParams.get('req');

				if (req === 'manage_cron') {
					const response = await route.fetch();
					const json = await response.json();
					json.data = { ...json.data, current_status: true };
					return route.fulfill({ response, json });
				}

				if (req === 'skip_queue_event') {
					const { post_id: postId } = request.postDataJSON();
					queue = queue.filter((item) => item.post_id !== postId);
				} else if (req === 'update_queue_event') {
					const data = request.postDataJSON();
					updates.push(data);
					const item = queue.find(
						(entry) => entry.post_id === data.post_id
					);
					if (typeof data.custom_data.text === 'string') {
						item.post_data.content.content = data.custom_data.text;
					}
				} else if (req !== 'get_queue') {
					return route.continue();
				}

				return route.fulfill({ json: { code: '201', data: queue } });
			}
		);

		await admin.visitAdminPage('/admin.php?page=TweetOldPost');
		await tryCloseTourModal(page);
		await page.getByText('Sharing Queue', { exact: true }).click();

		const firstCard = page.locator('.tab-view .card').first();
		await expect(firstCard).toContainText('Content of post A');

		await firstCard.getByRole('button', { name: 'Edit' }).click();
		const caption = firstCard.locator('textarea');
		await caption.clear();
		await caption.pressSequentially('Caption for post A');
		await firstCard.getByRole('button', { name: 'Save' }).click();
		await expect(firstCard).toContainText('Caption for post A');

		await firstCard.getByRole('button', { name: 'Skip' }).click();
		await expect(firstCard).toContainText('Content of post B');

		await firstCard.getByRole('button', { name: 'Edit' }).click();
		await firstCard.getByRole('button', { name: 'Save' }).click();

		await expect.poll(() => updates.length).toBe(2);
		expect(updates[0]).toMatchObject({
			post_id: 101,
			custom_data: { text: 'Caption for post A' },
		});
		expect(updates[1].post_id).toBe(102);
		expect(updates[1].custom_data).toEqual({});
		await expect(firstCard).toContainText('Content of post B');
	});
});
