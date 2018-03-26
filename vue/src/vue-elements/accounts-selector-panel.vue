<template>
	<div class="tab-view">
		<div class="panel-body">
			<h3>Post Format</h3>
			<div class="d-inline-block">
				<h4><i class="fa fa-info-circle"></i> Info</h4>
				<p><i>Each <b>account</b> can have it's own <b>Post Format</b> for sharing, on the left you can see the
					current selected account and network, bellow are the <b>Post Format</b> options for the account.
					Don't forget to save after each change and remember, you can always reset an account to the network
					defaults.
				</i></p>
			</div>
			<empty-active-accounts v-if="accountsCount === 0"></empty-active-accounts>
			<div class="container" v-if="accountsCount > 0">
				
				<div class="columns">
					<div class="column col-2 rop-selector-accounts">
						<div v-for="( account, id ) in active_accounts">
							<div class="rop-selector-account-container" v-bind:class="{active: selected_account===id}"
							     @click="setActiveAccount(id)">
								<div class="columns">
									<div class="tile tile-centered rop-account">
										<div class="tile-icon">
											<div class="icon_box"
											     :class=" (account.img ? 'has_image' : 'no-image' ) + ' ' +account.service ">
												<img class="service_account_image" :src="account.img"
												     v-if="account.img"/>
												<i class="fa  " :class="getIcon(account)" aria-hidden="true"></i>
											</div>
										</div>
										<div class="tile-content">
											<p class="rop-account-name">{{account.user}}</p>
											<strong class="rop-service-name">{{account.service}}</strong>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="column col-10" :class="'rop-tab-state-'+is_loading">
						<component :is="type" :account_id="selected_account"></component>
					</div>
				</div>
			</div>
		</div>
		<div class="panel-footer" v-if="accountsCount > 0">
			<button class="btn btn-primary" @click="saveAccountData()"><i class="fa fa-check"
			                                                              v-if="!this.is_loading"></i> <i
					class="fa fa-spinner fa-spin" v-else></i> Save {{component_label}}
			</button>
			<button class="btn btn-secondary" @click="resetAccountData()"><i class="fa fa-ban"
			                                                                 v-if="!this.is_loading"></i> <i
					class="fa fa-spinner fa-spin" v-else></i> Reset {{component_label}} for
				<b>{{active_account_name}}</b>
			</button>
		</div>
	</div>
</template>

<script>
	import EmptyActiveAccounts from './reusables/empty-active-accounts.vue'
	import PostFormat from './post-format.vue'
	import AccountSchedule from './account-schedule.vue'

	module.exports = {
		name: 'account-selector-view',
		props: {
			type: {
				default: function () {
					return '';
				},
				type: String
			}
		},
		data: function () {
			let key = null
			if (Object.keys(this.$store.state.activeAccounts)[0] !== undefined) key = Object.keys(this.$store.state.activeAccounts)[0];
			return {
				selected_account: key,
				component_label: '',
				action: '',
				is_loading: false
			}
		},
		mounted: function () {
			this.setupData();
		},
		filters: {
			capitalize: function (value) {
				if (!value) return ''
				value = value.toString()
				return value.charAt(0).toUpperCase() + value.slice(1)
			}
		},
		computed: {
			has_pro: function () {
				return this.$store.state.has_pro
			},
			active_data: function () {
				if (this.type === 'post-format') {
					return this.$store.state.activePostFormat;
				}
				if (this.type === 'schedule') {
					return this.$store.state.activeSchedule;
				}
				return [];
			},
			accountsCount: function () {
				return Object.keys(this.$store.state.activeAccounts).length
			},
			active_accounts: function () {
				return this.$store.state.activeAccounts
			},
			active_account_name: function () {
				return this.active_accounts[this.selected_account].user;
			},
		},
		watch: {
			active_accounts: function () {
				if (Object.keys(this.$store.state.activeAccounts)[0] && this.selected_account === null) {
					this.selected_account = Object.keys(this.$store.state.activeAccounts)[0];
					this.getAccountData();
				}
			},
			type: function () {
				this.setupData();
			}
		},
		methods: {
			setupData() {
				let action = this.type.replace('-', '_');
				let label = '';
				if (this.type === 'post-format') {
					label = 'post format'
				}
				if (this.type === 'schedule') {
					label = 'schedule'
				}
				this.action = action;
				this.component_label = label;
				if (this.active_data.length === 0) {
					this.getAccountData();
				}
			},
			getAccountData() {
				if (this.is_loading) {
					this.$log.warn('Request in progress...Bail');
					return;
				}
				if (this.active_accounts[this.selected_account] !== undefined) {
					this.is_loading = true;
					this.$store.dispatch('fetchAJAXPromise', {
						req: 'get_' + this.action,
						data: {}
					}).then(response => {
						this.$log.info('Successfully fetched account data', this.type, this.selected_account);
						this.$store.dispatch('fetchAJAX', {req: 'get_queue'});
						this.is_loading = false;
					}, error => {
						Vue.$log.error('Got nothing from server. Prompt user to check internet connection and try again', error)

						this.is_loading = false;
					})
				}
			},
			saveAccountData() {
				if (this.is_loading) {
					this.$log.warn('Request in progress...Bail');
					return;
				}
				this.is_loading = true;
				this.$store.dispatch('fetchAJAXPromise', {
					req: 'save_' + this.action,
					data: {
						service: this.active_accounts[this.selected_account].service,
						account_id: this.selected_account,
						data: this.active_data[this.selected_account]
					}
				}).then(response => {
					this.is_loading = false;
					this.$store.dispatch('fetchAJAX', {req: 'get_queue'})
				}, error => {

					this.is_loading = false;
					Vue.$log.error('Got nothing from server. Prompt user to check internet connection and try again', error)
				})
			},
			getIcon(account) {

				let serviceIcon = 'fa-'
				if (account.service === 'facebook') serviceIcon = serviceIcon.concat('facebook-official')
				if (account.service === 'twitter') serviceIcon = serviceIcon.concat('twitter')
				if (account.service === 'linkedin') serviceIcon = serviceIcon.concat('linkedin')
				if (account.service === 'tumblr') serviceIcon = serviceIcon.concat('tumblr')

				return serviceIcon;
			},
			resetAccountData() {
				if (this.is_loading) {
					this.$log.warn('Request in progress...Bail');
					return;
				}
				this.is_loading = true;
				this.$store.dispatch('fetchAJAXPromise', {
					req: 'reset_' + this.action,
					data: {
						service: this.active_accounts[this.selected_account].service,
						account_id: this.selected_account
					}
				}).then(response => {
					this.is_loading = false;
					this.$log.info('Succesfully reseted account', this.type,);
					this.$store.dispatch('fetchAJAX', {req: 'get_queue'})
				}, error => {
					this.is_loading = false;
					Vue.$log.error('Got nothing from server. Prompt user to check internet connection and try again', error)
				})
				this.$forceUpdate()
			},
			setActiveAccount(id) {
				if (this.is_loading) {
					this.$log.warn("Request in progress...Bail");
					return;
				}
				if (this.selected_account === id) {
					this.$log.info("Account already active");
					return;
				}

				this.$log.info('Switched account data  ', this.type, id);
				this.selected_account = id;
			}
		},
		components: {
			'empty-active-accounts': EmptyActiveAccounts,
			'post-format': PostFormat,
			'schedule': AccountSchedule,
		}
	}
</script>
