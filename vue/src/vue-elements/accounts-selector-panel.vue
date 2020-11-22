<template>
	<div class="tab-view">
		<div class="panel-body">
			<div class="d-inline-block mt-2 column col-12">
				<p class="text-gray"><i class="fa fa-info-circle"></i> <span v-html="labels.accounts_selector"></span>
				</p>
			</div>
			<empty-active-accounts v-if="accountsCount === 0"></empty-active-accounts>
			<div class="container" v-if="accountsCount > 0">
				<div class="columns">
					<div class="column col-3 col-sm-12 col-md-12 col-xl-3 col-lg-3 col-xs-12 col-rop-selector-accounts">
						<span class="divider"></span>
						<div v-for="( account, id ) in active_accounts">
							<div class="rop-selector-account-container" :class="{active: selected_account===id}"
							     @click="setActiveAccount(id)">
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
							<span class="divider"></span>
						</div>
					</div>
					<div class="column col-9 col-sm-12  col-md-12  col-xl-9 col-lg-9 col-xs-12"
					     :class="'rop-tab-state-'+is_loading">
						<component :is="type" :account_id="selected_account" :license="license"></component>
					</div>
				</div>
			</div>
		</div>
		<div class="panel-footer" v-if="accountsCount > 0">
			<div class="panel-actions text-right" v-if="allow_footer">
				<button class="btn btn-secondary" @click="resetAccountData()"><i class="fa fa-ban"
				                                                                 v-if="!this.is_loading"></i> <i
						class="fa fa-spinner fa-spin" v-else></i> {{labels.reset_selector_btn}} {{component_label}}
					{{labels.for}}
					<b>{{active_account_name}}</b>
				</button>
				<button class="btn btn-primary" @click="saveAccountData()"><i class="fa fa-check"
				                                                              v-if="!this.is_loading"></i> <i
						class="fa fa-spinner fa-spin" v-else></i> {{labels.save_selector_btn}} {{component_label}}
				</button>
			</div>
		</div>
	</div>
</template>

<script>
	import EmptyActiveAccounts from './reusables/empty-active-accounts.vue'
	import PostFormat from './post-format.vue'
	import AccountSchedule from './account-schedule.vue'
	import MultipleSelect from './reusables/multiple-select.vue'

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
			let key = null;
			if (Object.keys(this.$store.state.activeAccounts)[0] !== undefined) key = Object.keys(this.$store.state.activeAccounts)[0];

			return {
				selected_account: key,
				component_label: '',
				allow_footer: true,
				license: this.$store.state.licence,
				action: '',
				labels: this.$store.state.labels.accounts,
				upsell_link: ropApiSettings.upsell_link,
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
			active_accounts: {
				get: function () {
					const active_accounts = this.$store.state.activeAccounts;

					const normalized_accounts = {};
					for (const key in active_accounts) {
						if (!active_accounts.hasOwnProperty(key)) {
							continue;
						}
						normalized_accounts[key] = active_accounts[key];

					}
					this.$log.info('Available accounts', normalized_accounts)
					return normalized_accounts;
				},
				set: function (value) {
					this.setupData();
				}
			},
			active_account_name: function () {

				return this.active_accounts[this.selected_account].user;

			},
		},
		watch: {
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
					this.allow_footer = true;
				}
				if (this.type === 'schedule') {
					label = 'schedule';
					/**
					 * Allow footer if we have a valid license.
					 */
					this.allow_footer = (this.license > 1);
				}
				this.action = action;
				this.component_label = label;
				this.checkActiveData();
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
				}, error => {

					this.is_loading = false;
					Vue.$log.error('Got nothing from server. Prompt user to check internet connection and try again', error)
				})
			},
			getIcon(account) {

				let serviceIcon = 'fa-'
				if (account.service === 'facebook') serviceIcon = serviceIcon.concat('facebook')
				if (account.service === 'twitter') serviceIcon = serviceIcon.concat('twitter')
				if (account.service === 'linkedin') serviceIcon = serviceIcon.concat('linkedin')
				if (account.service === 'tumblr') serviceIcon = serviceIcon.concat('tumblr')
				if (account.service === 'pinterest') serviceIcon = serviceIcon.concat('pinterest')
				if (service === 'vk') serviceIcon = serviceIcon.concat('vk')
				if (service === 'gmb') serviceIcon = serviceIcon.concat('google')

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
				}, error => {
					this.is_loading = false;
					Vue.$log.error('Got nothing from server. Prompt user to check internet connection and try again', error)
				})
				this.$forceUpdate()
			},
			checkActiveData() {
				if (typeof  this.active_data[this.selected_account] === 'undefined') {
					this.getAccountData();
				}
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
				/**
				 * When a new account is added and we don't have any data for it.
				 */
				this.checkActiveData();
			}
		},
		components: {
			'empty-active-accounts': EmptyActiveAccounts,
			'post-format': PostFormat,
			'schedule': AccountSchedule,
			'MultipleSelect': MultipleSelect
		}
	}
</script>

<style scoped>
	.icon_box {
		width: 30px;
		height: 30px;
		padding: 5px;
	}

	.icon_box.no-image {
		padding: 0;
	}

	.icon_box.has_image > .fa {
		width: 15px;
		height: 15px;
		padding: 0;
		line-height: 15px;
	}

	.icon_box.no-image > .fa {
		font-size: 20px;
		background: transparent;
		line-height: 30px;
	}
</style>
