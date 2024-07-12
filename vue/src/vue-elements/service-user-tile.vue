<template>
  <div
    class="tile tile-centered rop-account"
    :class="'rop-'+type+'-account'"
  >
    <div class="tile-icon">
      <div
        class="icon_box"
        :class="service"
      >
        <img
          v-if="img"
          class="service_account_image"
          :src="img"
        >
        <i
          class="fa  "
          :class="icon"
          aria-hidden="true"
        />
      </div>
    </div>
    <div class="tile-content">
      <div class="tile-title">
        <a
          :href="link"
          target="_blank"
        >{{ user }}</a>
      </div>
      <div class="tile-subtitle text-gray">
        {{ serviceInfo }}
      </div>
    </div>
    <div class="tile-action">
      <div
        v-if="'webhook' === account_data?.service"
        class="tile-icon rop-edit-account tooltip tooltip-right"
        :data-tooltip="account_labels.edit_account"
        @click="openEditPopup()" 
      >
        <i
          v-if=" ! is_loading"
          class="fa fa-edit"
        />
      </div>
      <div class="form-group">
        <label class="form-switch">
          <div class="ajax-loader "><i
            v-show="is_loading"
            class="fa fa-spinner fa-spin"
          /></div>
          <input
            v-model="account_data.active"
            :disabled="checkDisabled"
            type="checkbox"
            @change="startToggleAccount( account_id, type )"
          >
          <i class="form-icon" />
        </label>
      </div>
     
      <div
        v-if=" ! account_data.active"
        class="tile-icon rop-remove-account tooltip tooltip-right"
        :data-tooltip="account_labels.remove_account"
        @click="removeAccount(account_id) "
      >
        <i
          v-if=" ! is_loading"
          class="fa fa-trash"
        />
      </div>
      <a
        href="https://revive.social/plugins/revive-old-post/?utm_source=wpadmin&utm_medium=accounts&utm_campaign=more-accounts"
        target="_blank"
      ><p v-if="informFbProProducts">{{ all_labels.generic.only_pro_suffix }}</p></a>
    </div>
  </div>
</template>

<script>

	import Vue from 'vue'

	export default {
		name: 'ServiceUserTile',
		components: {},
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
                return (this.$store.state.license > 0);
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
					serviceIcon = serviceIcon.concat('fb-n');
				}else if(this.account_data.account_type === 'instagram_account'){
					serviceIcon = serviceIcon.concat('instagram-n');
				}else if(this.account_data.account_type === 'facebook_group'){
					serviceIcon = serviceIcon.concat('users');
				}

				if (this.account_data.service === 'twitter') serviceIcon = serviceIcon.concat('twitter-x');
				if (this.account_data.service === 'linkedin') serviceIcon = serviceIcon.concat('linkedin-n');
				if (this.account_data.service === 'tumblr') serviceIcon = serviceIcon.concat('tumblr-n');
				if (this.account_data.service === 'pinterest') serviceIcon = serviceIcon.concat('pinterest');
				if (this.account_data.service === 'vk') serviceIcon = serviceIcon.concat('vk-n')
				if (this.account_data.service === 'gmb') serviceIcon = serviceIcon.concat('google-n')

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

			},
			openEditPopup() {
				this.$store.commit( 'setEditPopup', {
					accountId: this.account_id,
					canShow: true,
				})
			}
		}
	}
</script>
<style scoped>
	.rop-remove-account,
	.rop-edit-account
	{
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
  .fa-twitter-x{
    background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--! Font Awesome Free 6.5.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free (Icons: CC BY 4.0, Fonts: SIL OFL 1.1, Code: MIT License) Copyright 2024 Fonticons, Inc. --><path d="M389.2 48h70.6L305.6 224.2 487 464H345L233.7 318.6 106.5 464H35.8L200.7 275.5 26.8 48H172.4L272.9 180.9 389.2 48zM364.4 421.8h39.1L151.1 88h-42L364.4 421.8z"/></svg>');

  }
  .fa-instagram-n{
    background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><!--! Font Awesome Free 6.5.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free (Icons: CC BY 4.0, Fonts: SIL OFL 1.1, Code: MIT License) Copyright 2024 Fonticons, Inc. --><path d="M224.1 141c-63.6 0-114.9 51.3-114.9 114.9s51.3 114.9 114.9 114.9S339 319.5 339 255.9 287.7 141 224.1 141zm0 189.6c-41.1 0-74.7-33.5-74.7-74.7s33.5-74.7 74.7-74.7 74.7 33.5 74.7 74.7-33.6 74.7-74.7 74.7zm146.4-194.3c0 14.9-12 26.8-26.8 26.8-14.9 0-26.8-12-26.8-26.8s12-26.8 26.8-26.8 26.8 12 26.8 26.8zm76.1 27.2c-1.7-35.9-9.9-67.7-36.2-93.9-26.2-26.2-58-34.4-93.9-36.2-37-2.1-147.9-2.1-184.9 0-35.8 1.7-67.6 9.9-93.9 36.1s-34.4 58-36.2 93.9c-2.1 37-2.1 147.9 0 184.9 1.7 35.9 9.9 67.7 36.2 93.9s58 34.4 93.9 36.2c37 2.1 147.9 2.1 184.9 0 35.9-1.7 67.7-9.9 93.9-36.2 26.2-26.2 34.4-58 36.2-93.9 2.1-37 2.1-147.8 0-184.8zM398.8 388c-7.8 19.6-22.9 34.7-42.6 42.6-29.5 11.7-99.5 9-132.1 9s-102.7 2.6-132.1-9c-19.6-7.8-34.7-22.9-42.6-42.6-11.7-29.5-9-99.5-9-132.1s-2.6-102.7 9-132.1c7.8-19.6 22.9-34.7 42.6-42.6 29.5-11.7 99.5-9 132.1-9s102.7-2.6 132.1 9c19.6 7.8 34.7 22.9 42.6 42.6 11.7 29.5 9 99.5 9 132.1s2.7 102.7-9 132.1z"/></svg>');
    background-repeat:no-repeat;
  }
  .fa-fb-n{
    background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--! Font Awesome Free 6.5.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free (Icons: CC BY 4.0, Fonts: SIL OFL 1.1, Code: MIT License) Copyright 2024 Fonticons, Inc. --><path d="M512 256C512 114.6 397.4 0 256 0S0 114.6 0 256C0 376 82.7 476.8 194.2 504.5V334.2H141.4V256h52.8V222.3c0-87.1 39.4-127.5 125-127.5c16.2 0 44.2 3.2 55.7 6.4V172c-6-.6-16.5-1-29.6-1c-42 0-58.2 15.9-58.2 57.2V256h83.6l-14.4 78.2H287V510.1C413.8 494.8 512 386.9 512 256h0z"/></svg>');
    background-repeat:no-repeat;
  }
  .fa-linkedin-n{
    background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><!--! Font Awesome Free 6.5.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free (Icons: CC BY 4.0, Fonts: SIL OFL 1.1, Code: MIT License) Copyright 2024 Fonticons, Inc. --><path d="M416 32H31.9C14.3 32 0 46.5 0 64.3v383.4C0 465.5 14.3 480 31.9 480H416c17.6 0 32-14.5 32-32.3V64.3c0-17.8-14.4-32.3-32-32.3zM135.4 416H69V202.2h66.5V416zm-33.2-243c-21.3 0-38.5-17.3-38.5-38.5S80.9 96 102.2 96c21.2 0 38.5 17.3 38.5 38.5 0 21.3-17.2 38.5-38.5 38.5zm282.1 243h-66.4V312c0-24.8-.5-56.7-34.5-56.7-34.6 0-39.9 27-39.9 54.9V416h-66.4V202.2h63.7v29.2h.9c8.9-16.8 30.6-34.5 62.9-34.5 67.2 0 79.7 44.3 79.7 101.9V416z"/></svg>');
    background-repeat:no-repeat;
  }
  .fa-thumblr-n{
    background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512"><!--! Font Awesome Free 6.5.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free (Icons: CC BY 4.0, Fonts: SIL OFL 1.1, Code: MIT License) Copyright 2024 Fonticons, Inc. --><path d="M309.8 480.3c-13.6 14.5-50 31.7-97.4 31.7-120.8 0-147-88.8-147-140.6v-144H17.9c-5.5 0-10-4.5-10-10v-68c0-7.2 4.5-13.6 11.3-16 62-21.8 81.5-76 84.3-117.1.8-11 6.5-16.3 16.1-16.3h70.9c5.5 0 10 4.5 10 10v115.2h83c5.5 0 10 4.4 10 9.9v81.7c0 5.5-4.5 10-10 10h-83.4V360c0 34.2 23.7 53.6 68 35.8 4.8-1.9 9-3.2 12.7-2.2 3.5.9 5.8 3.4 7.4 7.9l22 64.3c1.8 5 3.3 10.6-.4 14.5z"/></svg>');
    background-repeat:no-repeat;
  }
  .fa-google-n{
    background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 488 512"><!--! Font Awesome Free 6.5.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free (Icons: CC BY 4.0, Fonts: SIL OFL 1.1, Code: MIT License) Copyright 2024 Fonticons, Inc. --><path d="M488 261.8C488 403.3 391.1 504 248 504 110.8 504 0 393.2 0 256S110.8 8 248 8c66.8 0 123 24.5 166.3 64.9l-67.5 64.9C258.5 52.6 94.3 116.6 94.3 256c0 86.5 69.1 156.6 153.7 156.6 98.2 0 135-70.4 140.8-106.9H248v-85.3h236.1c2.3 12.7 3.9 24.9 3.9 41.4z"/></svg>');
    background-repeat:no-repeat;
  }
  .fa-vk-n{
    background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><!--! Font Awesome Free 6.5.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free (Icons: CC BY 4.0, Fonts: SIL OFL 1.1, Code: MIT License) Copyright 2024 Fonticons, Inc. --><path d="M31.4907 63.4907C0 94.9813 0 145.671 0 247.04V264.96C0 366.329 0 417.019 31.4907 448.509C62.9813 480 113.671 480 215.04 480H232.96C334.329 480 385.019 480 416.509 448.509C448 417.019 448 366.329 448 264.96V247.04C448 145.671 448 94.9813 416.509 63.4907C385.019 32 334.329 32 232.96 32H215.04C113.671 32 62.9813 32 31.4907 63.4907ZM75.6 168.267H126.747C128.427 253.76 166.133 289.973 196 297.44V168.267H244.16V242C273.653 238.827 304.64 205.227 315.093 168.267H363.253C359.313 187.435 351.46 205.583 340.186 221.579C328.913 237.574 314.461 251.071 297.733 261.227C316.41 270.499 332.907 283.63 346.132 299.751C359.357 315.873 369.01 334.618 374.453 354.747H321.44C316.555 337.262 306.614 321.61 292.865 309.754C279.117 297.899 262.173 290.368 244.16 288.107V354.747H238.373C136.267 354.747 78.0267 284.747 75.6 168.267Z"/></svg>');
    background-repeat:no-repeat;
  }
  .icon_box.twitter{
    background:none;
  }
  .icon_box.facebook{
    background:none;
  }

	.rop-edit-account { 
		margin-right: 10px;
		margin-top: 2px;
	}
</style>
