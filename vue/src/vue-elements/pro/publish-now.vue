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
              v-if="account.service !== 'webhook'"
              class=" fa "
              :class="getServiceClass(account.service)"
            />
            <i
              v-if="account.service === 'webhook'"
              class="fa fa-fw"
            >
              <!-- Uploaded to: SVG Repo, www.svgrepo.com, Generator: SVG Repo Mixer Tools -->
              <svg
                height="14"
                width="16"
                viewBox="-10 -5 1034 1034"
                xmlns="http://www.w3.org/2000/svg"
                xmlns:xlink="http://www.w3.org/1999/xlink"
                version="1.1"
              >
                <path
                  fill="#000"
                  d="M482 226h-1l-10 2q-33 4 -64.5 18.5t-55.5 38.5q-41 37 -57 91q-9 30 -8 63t12 63q17 45 52 78l13 12l-83 135q-26 -1 -45 7q-30 13 -45 40q-7 15 -9 31t2 32q8 30 33 48q15 10 33 14.5t36 2t34.5 -12.5t27.5 -25q12 -17 14.5 -39t-5.5 -41q-1 -5 -7 -14l-3 -6l118 -192
q6 -9 8 -14l-10 -3q-9 -2 -13 -4q-23 -10 -41.5 -27.5t-28.5 -39.5q-17 -36 -9 -75q4 -23 17 -43t31 -34q37 -27 82 -27q27 -1 52.5 9.5t44.5 30.5q17 16 26.5 38.5t10.5 45.5q0 17 -6 42l70 19l8 1q14 -43 7 -86q-4 -33 -19.5 -63.5t-39.5 -53.5q-42 -42 -103 -56
q-6 -2 -18 -4l-14 -2h-37zM500 350q-17 0 -34 7t-30.5 20.5t-19.5 31.5q-8 20 -4 44q3 18 14 34t28 25q24 15 56 13q3 4 5 8l112 191q3 6 6 9q27 -26 58.5 -35.5t65 -3.5t58.5 26q32 25 43.5 61.5t0.5 73.5q-8 28 -28.5 50t-48.5 33q-31 13 -66.5 8.5t-63.5 -24.5
q-4 -3 -13 -10l-5 -6q-4 3 -11 10l-47 46q23 23 52 38.5t61 21.5l22 4h39l28 -5q64 -13 110 -60q22 -22 36.5 -50.5t19.5 -59.5q5 -36 -2 -71.5t-25 -64.5t-44 -51t-57 -35q-34 -14 -70.5 -16t-71.5 7l-17 5l-81 -137q13 -19 16 -37q5 -32 -13 -60q-16 -25 -44 -35
q-17 -6 -35 -6zM218 614q-58 13 -100 53q-47 44 -61 105l-4 24v37l2 11q2 13 4 20q7 31 24.5 59t42.5 49q50 41 115 49q38 4 76 -4.5t70 -28.5q53 -34 78 -91q7 -17 14 -45q6 -1 18 0l125 2q14 0 20 1q11 20 25 31t31.5 16t35.5 4q28 -3 50 -20q27 -21 32 -54
q2 -17 -1.5 -33t-13.5 -30q-16 -22 -41 -32q-17 -7 -35.5 -6.5t-35.5 7.5q-28 12 -43 37l-3 6q-14 0 -42 -1l-113 -1q-15 -1 -43 -1l-50 -1l3 17q8 43 -13 81q-14 27 -40 45t-57 22q-35 6 -70 -7.5t-57 -42.5q-28 -35 -27 -79q1 -37 23 -69q13 -19 32 -32t41 -19l9 -3z"
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
