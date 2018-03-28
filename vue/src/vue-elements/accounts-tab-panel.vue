<template>
	<div class="tab-view">
		<div class="panel-body">
			<h3>Accounts</h3>
			<div class="columns">
				<div class="column col-sm-12 col-md-12 col-xl-12 col-12 text-center">
					<b>New Service</b><br/>
					<i>Select a service and sign in with an account for that service.</i>
				</div>
				<div class="column col-sm-12 col-md-12 col-xl-6 col-4 text-center centered">
					<sign-in-btn></sign-in-btn>
				</div>
			</div>
			
			<div class="container">
				<div class="columns" v-if="checkLicense">
					<div class="column col-12 text-left">
						<h5><i class="fa fa-lock "></i> Extend</h5>
						<p>You are allowed to add a maximum 1 account for Twitter and 1 account for Facebook. For using
							more
							accounts and networks, you need to check the <strong>FULL</strong> version.
						</p>
					</div>
				</div>
				<div class="columns" :class="'rop-tab-state-'+is_loading">
					<div class="column col-sm-12 col-md-12 col-lg-12 text-left rop-available-accounts">
						
						<h5>Accounts</h5>
						<div class="empty" v-if="accountsCount === 0">
							<div class="empty-icon">
								<i class="fa fa-3x fa-user-circle-o"></i>
							</div>
							<p class="empty-title h5">No accounts!</p>
							<p class="empty-subtitle">Sign in and add your social accounts.</p>
						</div>
						<div class="account-container" v-for="( account, id ) in accounts">
							<service-user-tile :account_data="account" :account_id="id"></service-user-tile>
							<div class="divider"></div>
						</div>
					</div>
				</div>
			</div>
			<div class="columns">
				<div class="column col-12">
					<h4><i class="fa fa-info-circle"></i> Info</h4>
					<p>Authenticate a new service (eg. Facebook, Twitter etc. ), select the accounts you want to add
						from that service and <b>activate</b> them. Only the active accounts will be used for
						sharing.</p>
				</div>
			</div>
			<div class="panel-footer" v-if="accountsCount > 0">
				
				<button class="btn btn-secondary" @click="resetAccountData()">
					<i class="fa fa-ban" v-if="!this.is_loading"></i>
					<i class="fa fa-spinner fa-spin" v-else></i>
					Remove all accounts
				</button>
			</div>
		</div>
	
	</div>
</template>

<script>
	import SignInBtn from './sign-in-btn.vue'
	import ServiceUserTile from './service-user-tile.vue'

	module.exports = {
		name: 'account-view',
		data: function () {
			return {
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
			/**
			 * Check if we have a pro license.
			 * @returns {boolean}
			 */
			checkLicense: function () {
				return (this.$store.state.licence < 1);
			}

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
			ServiceUserTile
		}
	}
</script>