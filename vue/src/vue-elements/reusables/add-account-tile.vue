<template>
	<div>
		<div class="tile tile-centered rop-add-account">
			<a class="tile-icon c-hand" @click="addAccountActive = !addAccountActive">
				<div class="icon_box" :class="(addAccountActive) ? 'close bg-error' : 'open bg-success'">
					<i class="fa fa-2x fa-close" aria-hidden="true"></i>
				</div>
			</a>
			<div class="tile-content">
				<div class="tile-title">{{labels.add_account}}</div>
			</div>
			<transition name="fade">
				<div class="tile-action" v-if="addAccountActive">
					<sign-in-btn></sign-in-btn>
				</div>
			</transition>
		</div>
		<transition name="fade">
			<div class="columns my-2" v-if="checkLicense && addAccountActive">
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
				addAccountActive: false,
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