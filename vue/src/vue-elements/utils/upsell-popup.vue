<template>
	<div id="rop-twitter-upsell-popup" v-if="twitterDismissLink">
		<popup ref="popup" @closed="deactivateTwitterNotice" :close-when-clicked-outside=false>
			<snackbar-notice>
				<h6>
					<strong>
						Important X (Twitter) changes that impact Revice Social
					</strong>
				</h6>
			</snackbar-notice>
			<br>
			<p>X (formerly known as Twitter) is making changes that will impact daily post limit of Revive Social on X (Twitter)</p>
			<p>Twitter's recent policy update on posting limits has led to changes in our service. We've implemented new user limits that are tailored to each plan level. Our commitment is to ensure a seamless experience as we adapt to these changes, and we thank you for your understanding and patience during this period of transition.</p>
			<ul>
				<li><strong>Free users </strong> have a limit of 1 post per day.</li>
				<li><strong>Personal plan </strong> users have a limit of 4 posts per day.</li>
				<li><strong>Business plan </strong> suers have a limit of 10 posts per day.</li>
				<li><strong>Marketer plan </strong> users have a limit of 24 posts per day.</li>
			</ul>
			<p>
				If you're considering an upgrade due to these changes, we're offering a special <strong> 10% discount </strong> with the coupon code <strong>“TWITTERIMPACT” </strong> valid for the next <strong> 48 hours </strong> only.
			</p>
			<div class="actions">
				<a class="action" :href="upsellLink" target="_blank" rel="noopener noreferrer">
					Upgrade Now
				</a>
				<button class="action primary" @click="closePopup">
					I Acknowledge
				</button>
			</div>
		</popup>
	</div>
</template>

<script>
/**
 * Display up-sell popup.
 *
 * Implement up-sell popup for new Twitter posting limit.
 */

import Popup from '../reusables/popup.vue';
import SnackbarNotice from "../reusables/snackbar-notice.vue";

export default {
	components: {
		'snackbar-notice': SnackbarNotice,
		'popup': Popup
	},
	data() {
		return {
			upsellLink: 'https://revive.social/plugins/revive-old-post/',
			twitterDismissLink: this.$store.state.notifications.twitter_limit_promotion_close_url,
		}
	},
	methods: {
		showPopup() {
			this.$refs.popup.openPopup();
		},
		closePopup() {
			this.$refs.popup.closePopup();
			this.deactivateTwitterNotice();
		},
		deactivateTwitterNotice() {
			fetch( this.twitterDismissLink, { method: 'GET' });
			this.$store.commit( 'updateState', { requestName: 'close_twitter_limit_promotion' } )
		}
	},
	mounted() {
		if ( this.twitterDismissLink ) {
			this.showPopup();
		}
	}
};
</script>

<style scoped>

#rop-twitter-upsell-popup h6 {
	margin: 0;
}

#rop-twitter-upsell-popup :is(p, li) {
	font-size: 14px;
}

#rop-twitter-upsell-popup ul {
	padding: 0;
	margin-left: 0;
	list-style: none;
}

.actions {
	display: flex;
	justify-content: flex-end;
	gap: 30px;
}

.action {
	border-radius: 4px;
	border: 1px solid #4268CF;
	background: #FFF;
	color: #4268CF;
	padding: 10px 15px;
	cursor: pointer;
	text-decoration: unset;
}

.action.primary {
	background: #4268CF;
	color: #FFF;
}

</style>
