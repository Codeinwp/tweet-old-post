<template>
	<snackbar-notice variation="warning" v-if="limit" :close-notice="closeNotice">
		<span class="notice-msg">{{ message }} <a class="notice-link" :href="upsellLink">Upgrade Now</a> </span>
	</snackbar-notice>
</template>

<script>

/**
 * Display a notice to the user when they have reached their daily limit for Twitter posts.
 */

import SnackbarNotice from "../reusables/snackbar-notice.vue";

export default {
	name: 'twitter-notice',
	data() {
		const maxLimit = this.$store.state.notifications.twitter_limit;

		return {
			message: `Daily limit for X (Twitter) posts on your current plan is ${maxLimit} post per day. To post more,`,
			upsellLink: ''
		}
	},
	computed: {
		limit() {
			return this.$store.state.notifications.twitter_limit;
		}
	},
	methods: {
		closeNotice() {
			const closeUrl = this.$store.state.notifications.twitter_limit_close_url;
			if ( closeUrl ) {
				fetch( closeUrl, { method: 'GET' })
					.then( () => {
						this.$store.commit( 'updateState', { requestName: 'close_twitter_limit_notification' } )
					});
			}
		}
	},
	components: {
		'snackbar-notice': SnackbarNotice
	}
}
</script>

<style scoped>
	div {
		--text-color: #050505;
		--link-color: var(--text-color);

		--text-size: 16px;
		--link-font-weight: 700;
	}

	:is(.notice-msg, .notice-link) {
		color: var(--text-color);
		font-family: sans-serif;
		font-size: var(--text-size);
		font-style: normal;
		font-weight: 400;
		line-height: normal;

		margin: 0;
		padding: 0;
	}

	.notice-link {
		font-weight: var(--link-font-weight);
		text-decoration-line: underline;
	}
</style>
