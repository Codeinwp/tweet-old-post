<template>
	<div>

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