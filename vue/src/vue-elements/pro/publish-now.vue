<template>
  <div class="rop-control-container">
    <p v-if="Object.keys(accounts).length < 1">
      {{ labels.add_account_to_use_instant_share }}
    </p>
    <!-- Share on publish/update -->
    <fieldset v-if="Object.keys(accounts).length > 0">
      <label class="form-checkbox">
        <input
          type="checkbox"
          :checked="toggle_accounts"
          name="publish_now"
          value="1"
          @click="toggle_accounts = !toggle_accounts"
        >
        <span v-html=" labels.share_on_update" />
      </label>
      <template v-if="toggle_accounts">
        <div
          v-for="(account, key) in accounts"
          :id="key"
          :key="key"
          class="form-group rop-publish-now-accounts-wrapper"
        >
          <label
            :id="key"
            class="form-checkbox rop-publish-now-account"
          >
            <input
              type="checkbox"
              :checked="isActiveAccount(key)"
              :value="key"
              name="publish_now_accounts[]"
              class="rop-account-names"
              @click="toggleServices($event, key)"
            >
            <i
              v-if="account.service !== 'webhook' || account.service !== 'mastodon'"
              class=" fa "
              :class="getServiceClass(account.service)"
            />
            <i
              v-if="account.service === 'webhook'"
              class="fa fa-fw"
            >
              <svg
                height="14"
                width="16"
                viewBox="0 0 74 79"
                xmlns="http://www.w3.org/2000/svg"
                xmlns:xlink="http://www.w3.org/1999/xlink"
                version="1.1"
              >
                <path
                  d="M73.7014 17.9592C72.5616 9.62034 65.1774 3.04876 56.424 1.77536C54.9472 1.56019 49.3517 0.7771 36.3901 0.7771H36.2933C23.3281 0.7771 20.5465 1.56019 19.0697 1.77536C10.56 3.01348 2.78877 8.91838 0.903306 17.356C-0.00357857 21.5113 -0.100361 26.1181 0.068112 30.3439C0.308275 36.404 0.354874 42.4535 0.91406 48.489C1.30064 52.498 1.97502 56.4751 2.93215 60.3905C4.72441 67.6217 11.9795 73.6395 19.0876 76.0945C26.6979 78.6548 34.8821 79.0799 42.724 77.3221C43.5866 77.1245 44.4398 76.8953 45.2833 76.6342C47.1867 76.0381 49.4199 75.3714 51.0616 74.2003C51.0841 74.1839 51.1026 74.1627 51.1156 74.1382C51.1286 74.1138 51.1359 74.0868 51.1368 74.0592V68.2108C51.1364 68.185 51.1302 68.1596 51.1185 68.1365C51.1069 68.1134 51.0902 68.0932 51.0695 68.0773C51.0489 68.0614 51.0249 68.0503 50.9994 68.0447C50.9738 68.0391 50.9473 68.0392 50.9218 68.045C45.8976 69.226 40.7491 69.818 35.5836 69.8087C26.694 69.8087 24.3031 65.6569 23.6184 63.9285C23.0681 62.4347 22.7186 60.8764 22.5789 59.2934C22.5775 59.2669 22.5825 59.2403 22.5934 59.216C22.6043 59.1916 22.621 59.1702 22.6419 59.1533C22.6629 59.1365 22.6876 59.1248 22.714 59.1191C22.7404 59.1134 22.7678 59.1139 22.794 59.1206C27.7345 60.2936 32.799 60.8856 37.8813 60.8843C39.1036 60.8843 40.3223 60.8843 41.5447 60.8526C46.6562 60.7115 52.0437 60.454 57.0728 59.4874C57.1983 59.4628 57.3237 59.4416 57.4313 59.4098C65.3638 57.9107 72.9128 53.2051 73.6799 41.2895C73.7086 40.8204 73.7803 36.3758 73.7803 35.889C73.7839 34.2347 74.3216 24.1533 73.7014 17.9592ZM61.4925 47.6918H53.1514V27.5855C53.1514 23.3526 51.3591 21.1938 47.7136 21.1938C43.7061 21.1938 41.6988 23.7476 41.6988 28.7919V39.7974H33.4078V28.7919C33.4078 23.7476 31.3969 21.1938 27.3894 21.1938C23.7654 21.1938 21.9552 23.3526 21.9516 27.5855V47.6918H13.6176V26.9752C13.6176 22.7423 14.7157 19.3795 16.9118 16.8868C19.1772 14.4 22.1488 13.1231 25.8373 13.1231C30.1064 13.1231 33.3325 14.7386 35.4832 17.9662L37.5587 21.3949L39.6377 17.9662C41.7884 14.7386 45.0145 13.1231 49.2765 13.1231C52.9614 13.1231 55.9329 14.4 58.2055 16.8868C60.4017 19.3772 61.4997 22.74 61.4997 26.9752L61.4925 47.6918Z"
                />
              </svg>
            </i>
            <i
              v-if="account.service === 'mastodon'"
              class="fa fa-fw"
            >
              <!-- Uploaded to: SVG Repo, www.svgrepo.com, Generator: SVG Repo Mixer Tools -->
              <svg
                height="14"
                width="16"
                viewBox="0 0 14 14"
                xmlns="http://www.w3.org/2000/svg"
                xmlns:xlink="http://www.w3.org/1999/xlink"
                version="1.1"
              >
                <path
                  d="m 6.9823069,0.99999827 c -1.534141,0.0125 -3.0098524,0.17863003 -3.8698611,0.57359003 0,0 -1.705616,0.76301 -1.705616,3.36618 0,0.5961 -0.011605,1.30889 0.00727,2.06474 0.061937,2.5457596 0.466727,5.0547197 2.8204691,5.6777097 1.0852583,0.28725 2.0170112,0.34734 2.7674335,0.30609 1.3608723,-0.0754 2.1248476,-0.4856 2.1248476,-0.4856 l -0.044954,-0.98737 c 0,0 -0.9724634,0.30659 -2.0646395,0.26922 -1.0820905,-0.0371 -2.2244047,-0.11664 -2.3994193,-1.44519 -0.016163,-0.1167 -0.024245,-0.24151 -0.024245,-0.3725601 0,0 1.0622137,0.2596701 2.4084096,0.3213401 0.8231567,0.0378 1.5950724,-0.0483 2.3791143,-0.14183 1.5035609,-0.1795401 2.8127109,-1.1059101 2.9772509,-1.9524097 0.259257,-1.33345 0.237902,-3.25414 0.237902,-3.25414 0,-2.60317 -1.705515,-3.36618 -1.705515,-3.36618 -0.859944,-0.39496 -2.3366293,-0.56105 -3.8707705,-0.57359003 l -0.037681,0 z M 5.2460822,3.0340283 c 0.6390261,0 1.1228982,0.24565 1.4428639,0.73694 l 0.3110395,0.52136 0.3111408,-0.52136 c 0.319901,-0.49129 0.8037728,-0.73694 1.4428636,-0.73694 0.5522624,0 0.9972822,0.19415 1.337096,0.57288 0.329405,0.37874 0.493381,0.8906 0.493381,1.5348 l 0,3.15201 -1.2488059,0 0,-3.05928 c 0,-0.64491 -0.271258,-0.97231 -0.8140164,-0.97231 -0.6001053,0 -0.9008935,0.38835 -0.9008935,1.15617 l 0,1.6745 -1.2414304,0 0,-1.6745 c 0,-0.76782 -0.3007881,-1.15617 -0.9008934,-1.15617 -0.5427585,0 -0.8141175,0.3274 -0.8141175,0.97231 l 0,3.05928 -1.2488051,0 0,-3.15201 c 0,-0.6442 0.1640115,-1.15606 0.4934811,-1.5348 0.3397494,-0.37873 0.7847692,-0.57288 1.3370963,-0.57288 z"
                />
              </svg>
            </i>
            {{ account.user }}
          </label>
          <span
            :id="key"
            class="rop-edit-custom-instant-share-message-text"
            @click="togglefields(key)"
          >{{ showField[key] ? 'done' : 'edit message' }}</span>
          <p
            v-show="showField[key]"
            class="rop-custom-instant-share-message-text"
          >
            Custom share message:
          </p>
          <textarea
            v-show="showField[key]"
            :name="key"
            :disabled="!isPro"
            class="rop-custom-instant-share-message-area"
          />
          <p
            v-if="!isPro && showField[key]"
            class="custom-instant-share-upsell"
            v-html="labels.custom_instant_share_messages_upsell"
          />
        </div>
      </template>
    </fieldset>
  </div>
</template>

<script>
	/**
	 * This component is responsible for rendering the "Publish Now" section of the Pro version of the plugin.
	 * 
	 * The section is located in the "Publish" metabox of the post/page editor.
	 */
	import ButtonCheckbox from '../reusables/button-checkbox.vue'
	export default {
		name: 'PublishNow',
		components: {
			ButtonCheckbox
		},
		data: function () {
			var fields = {};
			Object.keys( this.$store.state.publish_now.accounts ).forEach( e => {
				fields[e] = false;
			} );
			return {
                rop_is_edit_post_screen: ropApiSettings.rop_is_edit_post_screen,
				license: this.$store.state.license,
				labels: this.$store.state.labels.publish_now,
				accounts: this.$store.state.publish_now.accounts,
				share_on_update_enabled: this.$store.state.publish_now.instant_share_enabled,
				toggle_accounts: this.$store.state.publish_now.instant_share_by_default ? this.$store.state.publish_now.action : false,
				choose_accounts_manually: this.$store.state.publish_now.choose_accounts_manually,
				showField: fields,
				page_active_accounts: this.$store.state.publish_now.page_active_accounts
			}
		},
		computed: {
		},
		computed: {
			isPro: function () {
					return (this.license > 0);
			},
		},
		created() {
		},
		methods: {
			/**
			 * Get the class for the Font Awesome icon of a social media service.
			 * 
			 * @param {string} service - The slug of the social media service to get the icon for. 
			 */
			getServiceClass: function (service) {
				let serviceIcon = 'fa-'
				if (service === 'facebook') serviceIcon = serviceIcon.concat('facebook')
				if (service === 'twitter') serviceIcon = serviceIcon.concat('twitter')
				if (service === 'linkedin') serviceIcon = serviceIcon.concat('linkedin')
				if (service === 'tumblr') serviceIcon = serviceIcon.concat('tumblr')
				if (service === 'pinterest') serviceIcon = serviceIcon.concat('pinterest')
				if (service === 'vk') serviceIcon = serviceIcon.concat('vk')
				if (service === 'gmb') serviceIcon = serviceIcon.concat('google')
				if (service === 'telegram') serviceIcon = serviceIcon.concat('telegram')
				return serviceIcon;
			},
			/**
			 * Toggle the sharing feature for a specific account.
			 * 
			 * @param {Event} event - The toggle event.
			 * @param {string} account_id - The ID of the account to toggle the services for.
			 */
			toggleServices: function(event, account_id){
				var self = this;
				if( event.target.checked ) {
					return;
				}
				return self.showField[account_id] = false;
			},
			/**
			 * Toggle the custom share message field for a specific account.
			 * 
			 * @param {string} account_id - The ID of the account to toggle the custom share message field for.
			 */
			togglefields: function(account_id) {
				var self = this;
				return self.showField[account_id] = ! self.showField[account_id];
			},
		
			/**
			 * 
			 * Check if the account is active for the page.
			 * 
			 * @param {string} account_id - The ID of the account to check the active state for.
			 */
			isActiveAccount: function( account_id ) {
				
				// If the active accounts for the page are set, check if the account is active for the page.
				if ( this.page_active_accounts ) {
					return  {...(this.page_active_accounts ?? {})}?.hasOwnProperty(account_id);
				}
				
				return ! this.choose_accounts_manually;
			},
		}
	}
</script>
<style>
	.rop-publish-now-branding{
		text-align: right;
		width:100%;
		float:right;
	}
	.rop-edit-custom-instant-share-message-text{
		text-decoration: underline;
		color: #0073aa;
		font-size: 12px;
		font-style:italic;
		cursor: pointer;
	}
	.rop-publish-now-account, .rop-custom-instant-share-message-area{
		margin: 0 0 0 16px;
	}
	.custom-instant-share-upsell{
		color: #808080;
		margin: 0 0 12px 16px;
	}
	.rop-custom-instant-share-message-text{
		margin: 5px 0 5px 16px;
		font-style: italic;
	}
	.rop-publish-now-accounts-wrapper{
		margin-top:10px;
	}
</style>