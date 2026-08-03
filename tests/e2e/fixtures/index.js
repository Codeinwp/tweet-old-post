/**
 * WordPress dependencies
 */
import { test as base, expect } from '@wordpress/e2e-test-utils-playwright';

export const test = base.extend({
	ropUtils: async ({ requestUtils }, use) => {
		const call = (path, data) =>
			requestUtils.rest({
				method: 'POST',
				path: `/rop-e2e/v1/${path}`,
				data,
			});

		await use({
			reset: () => call('reset'),
			seedAccount: () => call('account'),
			runPublishNow: (postId) => call('publish-now', { postId }),
			getRequests: () =>
				call('requests').then((result) => result.requests),
		});
	},
});

export { expect };
