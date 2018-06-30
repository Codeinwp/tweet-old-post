<template>
	<div>
		<div class="tile tile-centered rop-add-account">
			<div class="tile-content">
				<strong class="tile-title">{{labels.add_account}}</strong>
			</div>
				<div class="tile-action">
					<sign-in-btn></sign-in-btn>
				</div>
		</div>
		<transition name="fade">
			<div class="columns my-2" v-if="checkLicense">
				<div class="column col-12 text-center">
					<p class="upsell">
						<i class="fa fa-lock "></i> <span v-html="labels.upsell_accounts"></span>
					</p>
				</div>
			</div>
		</transition>
	</div>
</template>

<script>
	import SignInBtn from '../sign-in-btn.vue';

	module.exports = {
		name: "add-account-tile",
		data: function () {
			return {
				labels: this.$store.state.labels.accounts,
				upsell_link: ropApiSettings.upsell_link,
			}
		},
		computed: {
			/**
			 * Check if we have a pro license.
			 * @returns {boolean}
			 */
			checkLicense: function () {
				return (this.$store.state.licence < 1);
			}
		},
		components: {
			SignInBtn,
		}
	}
</script>