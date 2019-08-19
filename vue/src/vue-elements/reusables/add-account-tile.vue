<template>
	<div>
		<div class="tile-content">
			<div class="tile-title"><span v-html="labels.add_account"></span></div>
		</div>
		<div class="rop-add-account">
					<sign-in-btn></sign-in-btn>
		</div>
	</div>
</template>

<script>
	import SignInBtn from '../sign-in-btn.vue';

	module.exports = {
		name: "add-account-tile",
		data: function () {
			return {
				addAccountActive: false,
				labels: this.$store.state.labels.accounts,
				upsell_link: ropApiSettings.upsell_link,
			}
		},
		computed: {
		},
		components: {
			SignInBtn,
		}
	}
</script>

<style scoped>
	.icon_box {
		background: #efefef;
		padding: 0;
		transition: .3s ease;
	}

	.icon_box.close .fa {
		line-height: 1.6em;
	}

	.icon_box.open .fa {
		line-height: 1.7em;
		width: 20px;
		transform: rotate(-135deg);
		-webkit-transform: rotate(-135deg);
	}

	.fa {
		transition: all .3s cubic-bezier(.34, 1.61, .7, 1);
	}
</style>
