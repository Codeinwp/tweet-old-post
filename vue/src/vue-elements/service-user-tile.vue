<template>
	<div class="tile tile-centered rop-account" :class="'rop-'+type+'-account'">

		<div class="tile-icon">
			<div class="icon_box" :class="service">
				<img class="service_account_image" :src="img" v-if="img"/>
				<i class="fa  " :class="icon" aria-hidden="true"></i>
			</div>
		</div>
		<div class="tile-content">
			<div class="tile-title"><a :href="link" target="_blank">{{ user }}</a></div>
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

   		<div class="tile-icon rop-remove-account tooltip tooltip-right" @click="removeAccount(account_id) "  :data-tooltip="account_labels.remove_account" v-if=" ! account_data.active">
			<i class="fa fa-trash" v-if=" ! is_loading"></i>
			<i class="fa fa-spinner fa-spin" v-else></i>
		</div>
			<a href="https://revive.social/plugins/revive-old-post/?utm_source=rop&utm_medium=dashboard&utm_campaign=upsell" target="_blank"><p v-if="informFbProProducts">{{ all_labels.generic.only_pro_suffix }}</p></a>
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
				account_labels: this.$store.state.labels.accounts,
				all_labels: this.$store.state.labels,
				upsell_link: ropApiSettings.upsell_link,
			}
		},
		computed: {
			/**
			 * Inform the user that Instagram and Facebook Groups are available in Pro version
			 * @returns {boolean}
			 */
			informFbProProducts: function(){
				
				if( (this.account_data.account_type === 'instagram_account' || this.account_data.account_type === 'facebook_group') &&  !this.isPro ){
					return true;
				}

				// Backwards compatibilty < v8.7.0 we weren't storing 'account_type' for Facebook groups yet.
				// If is free version disable Facebook groups
				if(this.account_data.service === 'facebook'){
					if( this.user.includes('Facebook Group:') && !this.isPro ){
						return true;
					}
				}

			},
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

				// Backwards compatibilty < v8.7.0 we weren't storing 'account_type' for Facebook groups yet.
				// If is free version disable Facebook groups
				if(this.account_data.service === 'facebook'){
					if( this.user.includes('Facebook Group:') && !this.isPro ){
						return true;
					}
				}

				// If is free version disable Instagram
				if( (this.account_data.account_type === 'instagram_account' || this.account_data.account_type === 'facebook_group' ) && !this.isPro ){
				return true;
				}

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
             * Check if we have a pro license.
             * @returns {boolean}
             */
			isPro: function () {
                return (this.$store.state.licence > 0);
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

				if( this.account_data.service === 'facebook' &&  this.account_data.account_type !== 'instagram_account' && this.account_data.account_type !== 'facebook_group' ){
					serviceIcon = serviceIcon.concat('facebook');
				}else if(this.account_data.account_type === 'instagram_account'){
					serviceIcon = serviceIcon.concat('instagram');
				}else if(this.account_data.account_type === 'facebook_group'){
					serviceIcon = serviceIcon.concat('users');
				}

				if (this.account_data.service === 'twitter') serviceIcon = serviceIcon.concat('twitter');
				if (this.account_data.service === 'linkedin') serviceIcon = serviceIcon.concat('linkedin');
				if (this.account_data.service === 'tumblr') serviceIcon = serviceIcon.concat('tumblr');
				if (this.account_data.service === 'pinterest') serviceIcon = serviceIcon.concat('pinterest');
				if (this.account_data.service === 'vk') serviceIcon = serviceIcon.concat('vk')
				if (this.account_data.service === 'gmb') serviceIcon = serviceIcon.concat('google')

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
			 * Return account link.
			 */
			link: function () {
				return this.account_data.link
			},
			/**
			 * Return account info details.
			 * @returns {T[]}
			 */
			serviceInfo: function () {

				return this.account_data.account.concat(' ' + this.account_labels.at + ': ').concat(this.account_data.created)
			}
		},
		methods: {
		    /**
			 * Remove inactivate account.
			 *
			 * @param id Account to remove.
			 */
		    removeAccount(id){
                Vue.$log.info('Remove account', id);
                if (this.is_loading) {
                    Vue.$log.warn('Request in progress...Bail...', id);
                    return;
                }
                this.is_loading = true;
                this.$store.dispatch('fetchAJAXPromise', {
                    req: 'remove_account',
                    data: {account_id: id}
                }).then(response => {
                    this.$store.dispatch('fetchAJAXPromise', {req: 'get_authenticated_services'}).then(response =>{
                        this.is_loading = false;
                    },error => {
                        this.is_loading = false;
                    });
                    // This needs to be run to reset the available services to make the social media auth buttons available again.
					this.$store.dispatch('fetchAJAXPromise', {req: 'get_available_services'}).then(response =>{

					},error => {
						Vue.$log.error('service-user-tile.vue => fetchAJAXPromise::get_available_services issue: ', error)
					});
                    // get_available_services
                }, error => {
                    this.is_loading = false;
                    Vue.$log.error('Got nothing from server. Prompt user to check internet connection and try again', error)
                });
			},
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
					this.$store.dispatch('fetchAJAXPromise', {req: 'get_authenticated_services'}).then(response =>{
                        this.is_loading = false;
					},error => {
					    this.is_loading = false;
					});
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
<style scoped>
	.rop-remove-account{
		width:15px;
		text-align: center;
		cursor: pointer;
		height: 100%;
		-ms-flex: 0 0 auto;
		line-height: 40px;
		opacity: 1;
		margin-left:0;
		transition-timing-function: ease-in;
		transition: 1s;
		z-index:9999;
	}

</style>
