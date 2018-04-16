<template>
	<div class="tile tile-centered rop-account" :class="'rop-'+type+'-account'">
		<div class="tile-icon">
			<div class="icon_box" :class="service">
				<img class="service_account_image" :src="img" v-if="img"/>
				<i class="fa  " :class="icon" aria-hidden="true"></i>
			</div>
		</div>
		<div class="tile-content">
			<div class="tile-title">{{ user }}</div>
			<div class="tile-subtitle text-gray">{{ serviceInfo }}</div>
		</div>
		<div class="tile-action">
			<div class="form-group">
				<label class="form-switch">
					<div class="ajax-loader "><i class="fa fa-spinner fa-spin" v-show="is_loading"></i></div>
					<input :disabled="checkDisabled" type="checkbox" v-model="account_data.active"
					       @change="startToggleAccount( account_id, type )"/>
					<i class="form-icon"></i>
				</label>
			</div>
		</div>
	</div>
</template>

<script>

	import Vue from 'vue'

	module.exports = {
		name: 'service-user-tile',
		props: ['account_data', 'account_id'],
		data: function () {
			return {
				/**
				 * Loading state used for showing animations.
				 */
				is_loading: false,
				labels: this.$store.state.labels.accounts,
				upsell_link: ropApiSettings.upsell_link,
			}
		},
		computed: {
			/**
			 * Check if the account is allowed to be activate.
			 * @returns {boolean}
			 */
			checkDisabled: function () {
				if (this.account_data.active) {
					return false;
				}
				let available_services = this.$store.state.availableServices;
				if (typeof available_services[this.account_data.service] === 'undefined') {
					this.$log.info('No available service ', this.account_data.service);
					return true;
				}
				if (available_services[this.account_data.service].active === false) {
					this.$log.info('Service is not allowed', this.account_data.service);
					return true;
				}
				let service_limit = available_services[this.account_data.service].allowed_accounts;


				let countActiveAccounts = 0
				for (let activeAccount in this.$store.state.activeAccounts) {
					if (this.$store.state.activeAccounts[activeAccount].service === this.account_data.service) {
						countActiveAccounts++
					}
				}
				this.$log.info('Service limit details ', this.account_data.service, service_limit, countActiveAccounts);
				return (service_limit <= countActiveAccounts);


			},
			/**
			 * Returns account type.
			 * @returns {string}
			 */
			type: function () {
				return this.account_data.active === true ? 'active' : 'inactive';
			},
			/**
			 * Service class if we have an avatar image or not.
			 * @returns {module.exports.computed.service|module.exports.props.service|{type, required}|*}
			 */
			service: function () {
				let iconClass = this.account_data.service;
				if (this.img !== '') {
					iconClass = iconClass.concat(' ').concat('has_image')
				} else {
					iconClass = iconClass.concat(' ').concat('no-image')
				}
				return iconClass
			},
			/**
			 * Get service icon class.
			 * @returns {string}
			 */
			icon: function () {
				let serviceIcon = 'fa-';
				if (this.account_data.service === 'facebook') serviceIcon = serviceIcon.concat('facebook');
				if (this.account_data.service === 'twitter') serviceIcon = serviceIcon.concat('twitter');
				if (this.account_data.service === 'linkedin') serviceIcon = serviceIcon.concat('linkedin');
				if (this.account_data.service === 'tumblr') serviceIcon = serviceIcon.concat('tumblr');
				return serviceIcon
			},
			/**
			 * Setup img to be used.
			 * @returns {string}
			 */
			img: function () {
				let img = '';
				if (this.account_data.img !== '' && this.account_data.img !== undefined) {
					img = this.account_data.img
				}
				return img
			},
			/**
			 * Return account username.
			 */
			user: function () {
				return this.account_data.user
			},
			/**
			 * Return account info details.
			 * @returns {T[]}
			 */
			serviceInfo: function () {
				return this.account_data.account.concat(' ' + this.labels.at + ': ').concat(this.account_data.created)
			}
		},
		methods: {
			/**
			 * Toggle account state.
			 *
			 * @param id
			 * @param type
			 */
			toggleAccount: function (id, type) {
				let parts = id.split('_');
				if (parts.length !== 3) {
					Vue.$log.error('Invalid id format for active account ', id);
					return;
				}
				let service_id = parts[0] + '_' + parts[1];

				this.$store.state.authenticatedServices[service_id].available_accounts[id].active = (type !== 'inactive');
				this.$log.info("Before toggle ", this.$store.state.activeAccounts);
				if (type === 'inactive') {
					Vue.delete(this.$store.state.activeAccounts, id);
				} else {
					Vue.set(this.$store.state.activeAccounts, id, this.$store.state.authenticatedServices[service_id].available_accounts[id]);
				}
				this.$store.dispatch('fetchAJAXPromise', {
					req: 'toggle_account',
					data: {account_id: id, state: type}
				}).then(response => {
					this.is_loading = false;
					this.$store.dispatch('fetchAJAX', {req: 'get_authenticated_services'})
				}, error => {
					this.is_loading = false;
					Vue.$log.error('Got nothing from server. Prompt user to check internet connection and try again', error)
				})
			},
			/**
			 * Start toggle action.
			 * @param id
			 * @param type
			 */
			startToggleAccount(id, type) {
				Vue.$log.info('Toggle account', id, type);
				if (this.is_loading) {
					Vue.$log.warn('Request in progress...Bail...', id, type);
					return;
				}
				this.is_loading = true;
				this.toggleAccount(id, type)

			}
		}
	}
</script>
