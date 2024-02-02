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
          :checked="share_on_update_by_default"
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
              class=" fa "
              :class="getServiceClass(account.service)"
            /> {{ account.user }}
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
				license: this.$store.state.licence,
				labels: this.$store.state.labels.publish_now,
				accounts: this.$store.state.publish_now.accounts,
				share_on_update_enabled: this.$store.state.publish_now.instant_share_enabled,
				share_on_update_by_default: this.$store.state.publish_now.instant_share_by_default,
				choose_accounts_manually: this.$store.state.publish_now.choose_accounts_manually,
				showField: fields,
				toggle_accounts: this.$store.state.publish_now.instant_share_by_default,
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
				
				return true;
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
