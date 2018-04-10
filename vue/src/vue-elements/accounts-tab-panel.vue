<template>
	<div class="tab-view">
		<div class="panel-body">
			<div class="container">
				<div class="columns" :class="'rop-tab-state-'+is_loading">
					<div class="column col-sm-12 col-md-12 col-lg-12 text-left rop-available-accounts">
						<div class="empty" v-if="accountsCount === 0">
							<div class="empty-icon">
								<i class="fa fa-3x fa-user-circle-o"></i>
							</div>
							<p class="empty-title h5">No accounts!</p>
							<p class="empty-subtitle">Sign in and add your social accounts.</p>
						</div>
						<div class="account-container" v-for="( account, id ) in accounts">
							<service-user-tile :account_data="account" :account_id="id"></service-user-tile>
							<span class="divider"></span>
						</div>
						<div class="add-accounts">
							<add-account-tile></add-account-tile>
							<span class="divider"></span>
						</div>
					</div>
				</div>
			</div>
			<div class="panel-footer" v-if="accountsCount > 0">
				<div class="columns">
					<div class="column col-12">
						<p class="text-gray"><i class="fa fa-info-circle"></i> Authenticate a new service (eg. Facebook, Twitter etc. ), select the accounts you want to add from that service and <b>activate</b> them. Only the active accounts will be used for sharing.</p>
					</div>
				</div>
				<div class="column col-12 text-right">
				<button class="btn btn-secondary" @click="resetAccountData()">
					<i class="fa fa-ban" v-if="!this.is_loading"></i>
					<i class="fa fa-spinner fa-spin" v-else></i>
					Remove all accounts
				</button>
				</div>
			</div>
		</div>
	
	</div>
</template>

<script>
	import SignInBtn from './sign-in-btn.vue'
	import ServiceUserTile from './service-user-tile.vue'
	import AddAccountTile from './reusables/add-account-tile.vue'

	module.exports = {
		name: 'account-view',
		data: function () {
			return {
				addAccountActive: false,
				accountsCount: 0,
				is_loading: false
			}
		},
		computed: {
			/**
			 * Get all the available/active accounts.
			 */
			accounts: function () {
				const all_accounts = {};
				const services = this.$store.state.authenticatedServices;
				for (const key in services) {
					if (!services.hasOwnProperty(key)) {
						continue;
					}
					const service = services[key];

					for (const account_id in service.available_accounts) {
						if (!service.available_accounts.hasOwnProperty(account_id)) {
							continue;
						}
						all_accounts[account_id] = service.available_accounts[account_id];

					}
				}
				this.$log.info('All accounts : ', all_accounts);
				this.accountsCount = Object.keys(all_accounts).length;
				return all_accounts;
			},
		},

		methods: {
			resetAccountData: function () {
				if (this.is_loading) {
					this.$log.warn('Request in progress...Bail');
					return;
				}
				this.is_loading = true;
				this.$store.dispatch('fetchAJAXPromise', {
					req: 'reset_accounts',
					data: {}
				}).then(response => {
					this.is_loading = false;
				}, error => {
					this.is_loading = false;
					Vue.$log.error('Got nothing from server. Prompt user to check internet connection and try again', error)
				})
			}
		},
		components: {
			SignInBtn,
			ServiceUserTile,
			AddAccountTile
		}
	}
</script>
<style scoped>
	#rop_core .columns.py-2 .text-gray {
		margin: 0;
		line-height: normal;
	}
	#rop_core .input-group {
		width: 100%;
	}
	b {
		margin-bottom :5px;
		display: block;
	}
	#rop_core .text-gray b {
		display: inline;
	}
	#rop_core .input-group .input-group-addon {
		padding: 3px 5px;
	}
	#rop_core .rop-available-accounts h5 {
		margin-bottom: 15px;
	}
	@media( max-width: 600px ) {
		#rop_core .panel-body .text-gray {
			margin-bottom: 10px;
		}
		#rop_core .text-right {
			text-align: left;
		}
	}
</style>