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
				
				<div class="columns">
					<div class="column col-sm-12 col-md-12 col-lg-12 text-left rop-available-accounts">
						<h5>Accounts</h5>
						<div class="empty" v-if="accounts.length == 0">
							<div class="empty-icon">
								<i class="fa fa-3x fa-user-circle-o"></i>
							</div>
							<p class="empty-title h5">No accounts!</p>
							<p class="empty-subtitle">Add one from the <b>"Authenticated Services"</b> section.</p>
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
					<p><i>Authenticate a new service (eg. Facebook, Twitter etc. ), select the accounts you want to add
						from that service and <b>activate</b> them. Only the accounts displayed in the <b>"Active
							accounts"</b> section will be used.</i></p>
				</div>
			</div>
		</div>
		<div class="panel-footer">
			<cron-button></cron-button>
		</div>
	</div>
</template>

<script>
	import SignInBtn from './sign-in-btn.vue'
	import ServiceTile from './service-tile.vue'
	import ServiceUserTile from './service-user-tile.vue'
	import CronButton from './reusables/cron-button.vue'

	module.exports = {
		name: 'account-view',
		computed: {
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
				return all_accounts;
			},
			is_loading: function () {
				return true;
			}
		},
		components: {
			SignInBtn,
			CronButton,
			ServiceTile,
			ServiceUserTile
		}
	}
</script>
<style type="text/css">
	
	.rop-available-accounts {
		padding-top: 35px;
	}

</style>