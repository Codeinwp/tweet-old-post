<template>
  <div id="rop-sign-in-area">
    <div class="input-group text-right buttons-wrap">
      <button
        v-for="( service, network ) in services"
        :key="network"
        :title="getTooltip( service, network )"
        class="btn input-group-btn"
        :class="getButtonClass( service, network )"
        :data-tooltip="canShowProPluginUpgradeWebhookNotice ? labels.get_latest_pro_version : ''"
        @click="requestAuthorization( network )"
      >
        <i
          v-if="! [ 'gmb', 'twitter', 'webhook', 'mastodon'].includes( network )"
          class="fa fa-fw"
          :class="'fa-' + network"
        />
        <i
          v-if="network === 'gmb'"
          class="fa fa-fw fa-google"
        />
        <i
          v-if="network === 'twitter'"
          class="fa fa-fw"
        >
          <svg
            xmlns="http://www.w3.org/2000/svg"
            height="14"
            width="16"
            viewBox="0 0 512 512"
            fill="currentColor"
          >
            <!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2023 Fonticons, Inc. -->
            <path d="M389.2 48h70.6L305.6 224.2 487 464H345L233.7 318.6 106.5 464H35.8L200.7 275.5 26.8 48H172.4L272.9 180.9 389.2 48zM364.4 421.8h39.1L151.1 88h-42L364.4 421.8z" />
          </svg>
        </i>
        <i
          v-if="network === 'webhook'"
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
              fill="#fff"
              d="M482 226h-1l-10 2q-33 4 -64.5 18.5t-55.5 38.5q-41 37 -57 91q-9 30 -8 63t12 63q17 45 52 78l13 12l-83 135q-26 -1 -45 7q-30 13 -45 40q-7 15 -9 31t2 32q8 30 33 48q15 10 33 14.5t36 2t34.5 -12.5t27.5 -25q12 -17 14.5 -39t-5.5 -41q-1 -5 -7 -14l-3 -6l118 -192
q6 -9 8 -14l-10 -3q-9 -2 -13 -4q-23 -10 -41.5 -27.5t-28.5 -39.5q-17 -36 -9 -75q4 -23 17 -43t31 -34q37 -27 82 -27q27 -1 52.5 9.5t44.5 30.5q17 16 26.5 38.5t10.5 45.5q0 17 -6 42l70 19l8 1q14 -43 7 -86q-4 -33 -19.5 -63.5t-39.5 -53.5q-42 -42 -103 -56
q-6 -2 -18 -4l-14 -2h-37zM500 350q-17 0 -34 7t-30.5 20.5t-19.5 31.5q-8 20 -4 44q3 18 14 34t28 25q24 15 56 13q3 4 5 8l112 191q3 6 6 9q27 -26 58.5 -35.5t65 -3.5t58.5 26q32 25 43.5 61.5t0.5 73.5q-8 28 -28.5 50t-48.5 33q-31 13 -66.5 8.5t-63.5 -24.5
q-4 -3 -13 -10l-5 -6q-4 3 -11 10l-47 46q23 23 52 38.5t61 21.5l22 4h39l28 -5q64 -13 110 -60q22 -22 36.5 -50.5t19.5 -59.5q5 -36 -2 -71.5t-25 -64.5t-44 -51t-57 -35q-34 -14 -70.5 -16t-71.5 7l-17 5l-81 -137q13 -19 16 -37q5 -32 -13 -60q-16 -25 -44 -35
q-17 -6 -35 -6zM218 614q-58 13 -100 53q-47 44 -61 105l-4 24v37l2 11q2 13 4 20q7 31 24.5 59t42.5 49q50 41 115 49q38 4 76 -4.5t70 -28.5q53 -34 78 -91q7 -17 14 -45q6 -1 18 0l125 2q14 0 20 1q11 20 25 31t31.5 16t35.5 4q28 -3 50 -20q27 -21 32 -54
q2 -17 -1.5 -33t-13.5 -30q-16 -22 -41 -32q-17 -7 -35.5 -6.5t-35.5 7.5q-28 12 -43 37l-3 6q-14 0 -42 -1l-113 -1q-15 -1 -43 -1l-50 -1l3 17q8 43 -13 81q-14 27 -40 45t-57 22q-35 6 -70 -7.5t-57 -42.5q-28 -35 -27 -79q1 -37 23 -69q13 -19 32 -32t41 -19l9 -3z"
            />
          </svg>
        </i>
        <i
          v-if="network === 'mastodon'"
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
          fill="#fff"
          d="M73.7014 17.9592C72.5616 9.62034 65.1774 3.04876 56.424 1.77536C54.9472 1.56019 49.3517 0.7771 36.3901 0.7771H36.2933C23.3281 0.7771 20.5465 1.56019 19.0697 1.77536C10.56 3.01348 2.78877 8.91838 0.903306 17.356C-0.00357857 21.5113 -0.100361 26.1181 0.068112 30.3439C0.308275 36.404 0.354874 42.4535 0.91406 48.489C1.30064 52.498 1.97502 56.4751 2.93215 60.3905C4.72441 67.6217 11.9795 73.6395 19.0876 76.0945C26.6979 78.6548 34.8821 79.0799 42.724 77.3221C43.5866 77.1245 44.4398 76.8953 45.2833 76.6342C47.1867 76.0381 49.4199 75.3714 51.0616 74.2003C51.0841 74.1839 51.1026 74.1627 51.1156 74.1382C51.1286 74.1138 51.1359 74.0868 51.1368 74.0592V68.2108C51.1364 68.185 51.1302 68.1596 51.1185 68.1365C51.1069 68.1134 51.0902 68.0932 51.0695 68.0773C51.0489 68.0614 51.0249 68.0503 50.9994 68.0447C50.9738 68.0391 50.9473 68.0392 50.9218 68.045C45.8976 69.226 40.7491 69.818 35.5836 69.8087C26.694 69.8087 24.3031 65.6569 23.6184 63.9285C23.0681 62.4347 22.7186 60.8764 22.5789 59.2934C22.5775 59.2669 22.5825 59.2403 22.5934 59.216C22.6043 59.1916 22.621 59.1702 22.6419 59.1533C22.6629 59.1365 22.6876 59.1248 22.714 59.1191C22.7404 59.1134 22.7678 59.1139 22.794 59.1206C27.7345 60.2936 32.799 60.8856 37.8813 60.8843C39.1036 60.8843 40.3223 60.8843 41.5447 60.8526C46.6562 60.7115 52.0437 60.454 57.0728 59.4874C57.1983 59.4628 57.3237 59.4416 57.4313 59.4098C65.3638 57.9107 72.9128 53.2051 73.6799 41.2895C73.7086 40.8204 73.7803 36.3758 73.7803 35.889C73.7839 34.2347 74.3216 24.1533 73.7014 17.9592ZM61.4925 47.6918H53.1514V27.5855C53.1514 23.3526 51.3591 21.1938 47.7136 21.1938C43.7061 21.1938 41.6988 23.7476 41.6988 28.7919V39.7974H33.4078V28.7919C33.4078 23.7476 31.3969 21.1938 27.3894 21.1938C23.7654 21.1938 21.9552 23.3526 21.9516 27.5855V47.6918H13.6176V26.9752C13.6176 22.7423 14.7157 19.3795 16.9118 16.8868C19.1772 14.4 22.1488 13.1231 25.8373 13.1231C30.1064 13.1231 33.3325 14.7386 35.4832 17.9662L37.5587 21.3949L39.6377 17.9662C41.7884 14.7386 45.0145 13.1231 49.2765 13.1231C52.9614 13.1231 55.9329 14.4 58.2055 16.8868C60.4017 19.3772 61.4997 22.74 61.4997 26.9752L61.4925 47.6918Z"
        >
        </path>
			  </svg>
        </i>
        {{ displayName( service.name, false, true ) }}
        <span
          v-if="checkDisabled( service, network ) || ('webhook' === network && canShowProPluginUpgradeWebhookNotice)"
          style="font-size:13px;line-height: 20px"
          class="dashicons dashicons-lock"
        />
      </button>
    </div>

    <div
      class="modal rop-upsell-modal"
      :class="upsellModalActiveClass"
    >
      <div class="modal-overlay" />
      <div class="modal-container">
        <div class="modal-header">
          <button
            class="btn btn-clear float-right"
            @click="closeUpsellModal()"
          />
          <div class="modal-title h3">
            <span class="dashicons dashicons-lock" />
          </div>
          <div class="modal-title h5">
            {{ upsellModal.title }}
          </div>
        </div>
        <div class="modal-body">
          {{ upsellModal.body }}
        </div>
        <div class="modal-footer">
          <a
            :href="upsellModal.link"
            class="btn  btn-success"
            target="_blank"
          >{{ labels.upsell_upgrade_now }}</a>
        </div>
      </div>
    </div>

    <div
      class="modal"
      :class="modalActiveClass"
    >
      <div class="modal-overlay" />
      <div class="modal-container">
        <div class="modal-header">
          <button
            class="btn btn-clear float-right"
            @click="cancelModal()"
          />
          <div class="modal-title h5">
            {{ displayName( modal.serviceName, true ) }} {{ labels.service_popup_title }}
          </div>
        </div>
        <div class="modal-body">
          <div class="content">
            <div
              v-if="isFacebook"
              class="auth-app"
            >
              <button
                class="btn btn-primary big-btn"
                @click="openPopupFB()"
              >
                {{ labels.fb_app_signin_btn }}
              </button>
              <div v-if="!hideOwnAppOption">
                <span class="text-center">{{ labels.app_option_signin }}</span>
              </div>
            </div>
            <div
              v-if="isLinkedIn"
              class="auth-app"
            >
              <button
                class="btn btn-primary big-btn"
                @click="openPopupLI()"
              >
                {{ labels.li_app_signin_btn }}
              </button>
              <div v-if="!hideOwnAppOption">
                <span class="text-center">{{ labels.app_option_signin }}</span>
              </div>
            </div>
            <div
              v-if="isTumblr && isAllowedTumblr"
              class="auth-app"
            >
              <button
                class="btn btn-primary big-btn"
                @click="openPopupTumblr()"
              >
                {{ labels.tumblr_app_signin_btn }}
              </button>
              <div v-if="!hideOwnAppOption">
                <span class="text-center">{{ labels.app_option_signin }}</span>
              </div>
            </div>
            <div
              v-if="isGmb"
              class="auth-app"
            >
              <button
                id="gmb-btn"
                class="btn btn-primary big-btn"
                @click="openPopupGmb()"
              >
                {{ labels.gmb_app_signin_btn }}
              </button>
            </div>

            <div
              v-if="isVk"
              class="auth-app"
            >
              <button
                id="vk-btn"
                class="btn btn-primary big-btn"
                @click="openPopupVk()"
              >
                {{ labels.vk_app_signin_btn }}
              </button>
            </div>

            <div v-if="!hideOwnAppOption || isTwitter">
              <div
                v-if="isFacebook || isLinkedIn || (isTumblr && isAllowedTumblr)"
                id="rop-advanced-config"
              >
                <button
                  class="btn btn-primary"
                  @click="showAdvanceConfig = !showAdvanceConfig"
                >
                  {{ labels.show_advance_config }}
                </button>
              </div>

              <div
                v-if="showAdvanceConfig && (isFacebook || isLinkedIn || (isTumblr && isAllowedTumblr) )"
              >
                <div
                  v-for="( field, id ) in modal.data"
                  :key="field.id"
                  class="form-group"
                >
                  <label
                    class="form-label"
                    :for="field.id"
                  >{{ field.name }}</label>
                  <input
                    :id="field.id"
                    v-model="field.value"
                    :class="[ 'form-input', field.error ? ' is-error' : '' ]"
                    type="text"
                    :placeholder="field.name"
                  >
                  <small
                    v-if="field.error"
                    class="text-error"
                  >{{ labels.field_required }}</small>
                  <p class="text-gray uppercase">
                    {{ field.description }}
                  </p>
                </div>
              </div>
            </div>
            <div
              v-if="(!isFacebook && !isLinkedIn && !isGmb && !isTumblr && !isVk) || (isTumblr && !isAllowedTumblr)"
            >
              <div
                v-for="( field, id ) in modal.data"
                :key="id"
                class="form-group"
              >
                <label
                  class="form-label"
                  :for="field.id"
                >{{ field.name }}</label>
                <input
                  :id="field.id"
                  v-model="field.value"
                  :class="[ 'form-input', field.error ? ' is-error' : '' ]"
                  type="text"
                  :placeholder="field.name"
                >
                <small
                  v-if="field.error"
                  class="text-error"
                >{{ labels.field_required }}</small>
                <p class="text-gray uppercase">
                  {{ field.description }}
                </p>
              </div>
            </div>
            <WebhookHeaders
              v-if="isWebhook && showHeaders"
              :headers.sync="webhooksHeaders"
            />
            <div
              v-if="isWebhook"
            >
              <button
                v-if="!showHeaders"
                class="btn btn-primary"
                @click="showHeaders = true"
              >
                {{ labels.edit_headers }}
              </button>
              <button
                v-if="showHeaders"
                class="btn btn-secondary"
                @click="showHeaders = false"
              >
                {{ labels.hide }}
              </button>
            </div>
          </div>
        </div>

        <div
          v-if="showAdvanceConfig && (isFacebook || isLinkedIn || isTumblr) || isTwitter || isMastodon"
          class="modal-footer"
        >
          <div
            class="text-left pull-left mr-2"
            v-html="modal.description"
          />
          <button
            class="btn btn-primary"
            @click="closeModal()"
          >
            {{ labels.sign_in_btn }}
          </button>
        </div>
        <div
          v-if="(!isTwitter && !isFacebook && !isLinkedIn && !isGmb && !isTumblr && !isVk && !isMastodon) || (isTumblr && !isAllowedTumblr)"
          class="modal-footer"
        >
          <div
            class="text-left pull-left mr-2"
            v-html="modal.description"
          />
          <button
            class="btn btn-primary"
            @click="closeModal()"
          >
            {{ isOpenToEdit ? labels.save_selector_btn : labels.sign_in_btn }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import Tooltip from './reusables/popover.vue';
import WebhookHeaders from './reusables/webhook-headers.vue';

export default {
  name: 'SignInBtn',
  components: {Tooltip, WebhookHeaders},
  data: function () {
    return {
      modal: {
        isOpen: false,
        serviceName: '',
        description: '',
        data: {}
      },
      upsellModal: {
        isOpen: false,
        title: '',
        body: '',
        link: ropApiSettings.upsell_link
      },
      showAdvanceConfig: false,
      labels: this.$store.state.labels.accounts,
      upsell_link: ropApiSettings.upsell_link,
      activePopup: '',
      appOrigin: ropAuthAppData.authAppUrl,
      appPathFB: ropAuthAppData.authAppFacebookPath,
      appPathTW: ropAuthAppData.authAppTwitterPath,
      appPathLI: ropAuthAppData.authAppLinkedInPath,
      appPathTumblr: ropAuthAppData.authAppTumblrPath,
      appPathGmb: ropAuthAppData.authAppGmbPath,
      appPathVk: ropAuthAppData.authAppVkPath,
      appAdminEmail: ropAuthAppData.adminEmail,
      siteAdminUrl: ropAuthAppData.adminUrl,
      appUniqueId: ropAuthAppData.authToken,
      appSignature: ropAuthAppData.authSignature,
      pluginVersion: ropAuthAppData.pluginVersion,
      windowParameters: 'top=20,left=100,width=560,height=670',
      authPopupWindow: null,
      showLiAppBtn: ropApiSettings.show_li_app_btn,
      showTmblrAppBtn: ropApiSettings.show_tmblr_app_btn,
      hideOwnAppOption: ropApiSettings.hide_own_app_option,
      currentWebhookHeader: '',
      webhooksHeaders: [],
      showBtn: false,
      showHeaders: false,
      canShowProPluginUpgradeWebhookNotice: 'valid' === ropApiSettings?.license_data_view?.license && ! ropApiSettings?.webhook_pro_available // Notice the user to upgrade to the last version of the plugin to use the webhook feature.
    }
  },
  computed: {
    isOpenToEdit() {
      return this.$store.state.editPopup?.canShow;
    },
    selected_service: function () {
      return this.services[this.selected_network]
    },
    selected_network: {
      get: function () {
        let defaultNetwork = this.modal.serviceName
        if (Object.keys(this.services)[0] && defaultNetwork === '') {
          defaultNetwork = Object.keys(this.services)[0]
        }
        return defaultNetwork.toLowerCase()
      },
      set: function (newNetwork) {
        this.modal.serviceName = newNetwork
      }
    },
    services: function () {
      return this.$store.state.availableServices
    },
    modalActiveClass: function () {
      return {
        'active': this.modal?.isOpen === true
      }
    },
    upsellModalActiveClass: function () {
      return {
        'active': this.upsellModal.isOpen === true
      }
    },
    serviceId: function () {
      return 'service-' + this.modal.serviceName.toLowerCase()
    },
    isFacebook() {
      return this.modal.serviceName === 'Facebook' ||  this.modal.serviceName === 'Instagram';
    },
    // will return true if the current service actions are for Twitter.
    isTwitter() {
      return this.modal.serviceName === 'Twitter';
    },
    // will return true if the current service actions are for LinkedIn.
    isLinkedIn() {
      return this.modal.serviceName === 'LinkedIn';
    },
    // will return true if the current service actions are for Tumblr.
    isTumblr() {
      return this.modal.serviceName === 'Tumblr';
    },
    // will return true if the current service actions are for Google My Business.
    isGmb() {
      return this.modal.serviceName === 'Gmb';
    },
    // will return true if the current service actions are for Vk.
    isVk() {
      return this.modal.serviceName === 'Vk';
    },
    // will return true if the current service actions are for Pinterest.
    isPinterest() {
      return this.modal.serviceName === 'Pinterest';
    },

    isWebhook() {
      return this.modal.serviceName === 'Webhook';
    },

    isTelegram() {
      return this.modal.serviceName === 'Telegram';
    },

    isMastodon() {
      return this.modal.serviceName === 'Mastodon';
    },

    isAllowedTumblr: function () {
      let showButton = true;
      if (!this.showTmblrAppBtn) {
        showButton = false;
      }
      return showButton;
    },
  },
  watch: {
    isOpenToEdit( canShow) {
      if ( ! canShow ) {
        return;
      }

      this.openEditPopup();
    }
  },
  created() {
    if ( this.isOpenToEdit ) {
      this.openEditPopup();
    }
  },
  methods: {
    /**
     * Get display name for the service.
     *
     * @param serviceName
     * @returns {*}
     */
    displayName( serviceName, short = false, justNote = false ) {
      if ( 'Twitter' === serviceName ) {
        if ( short === true ) {
          return 'X';
        }
        if ( justNote === true ) {
          return this.labels.tw_new_name.replace( 'X ', '');
        }
        return this.labels.tw_new_name;
      }
      return serviceName;
    },

    /**
     * Get tooltip for the service.
     *
     *
     * @param service
     * @param network
     * @returns {string}
     */
    getTooltip(service, network) {
      if (service !== undefined && service.active === false) {
        return this.labels.only_in_pro
      }
      let countAuthServices = 0
      for (let authService in this.$store.state.authenticatedServices) {
        if (this.$store.state.authenticatedServices[authService].service === network) {
          countAuthServices++
        }
      }

      let countActiveAccounts = 0
      for (let activeAccount in this.$store.state.activeAccounts) {
        if (this.$store.state.activeAccounts[activeAccount].service === network) {
          countActiveAccounts++
        }
      }

      if (service !== undefined && (service.allowed_accounts <= countAuthServices || service.allowed_accounts <= countActiveAccounts)) {
        return this.labels.limit_reached
      }
      return ''
    },
    /**
     * Check status for the service.
     *
     *
     * @param service
     * @param network
     * @returns {boolean}
     */
    checkDisabled(service, network) {
      if (service !== undefined && service.active === false) {
        return true
      }

      let countAuthServices = 0
      for (let authService in this.$store.state.authenticatedServices) {
        if (this.$store.state.authenticatedServices[authService].service === network) {
          countAuthServices++
        }
      }

      let countActiveAccounts = 0
      for (let activeAccount in this.$store.state.activeAccounts) {
        if (this.$store.state.activeAccounts[activeAccount].service === network) {
          countActiveAccounts++
        }
      }

      if (service !== undefined && (service.allowed_accounts <= countAuthServices || service.allowed_accounts <= countActiveAccounts)) {
        return true
      }

      return this.$store.state.auth_in_progress
    },
    openUpsellModal(){
      this.upsellModal.isOpen = true;

    },
    closeUpsellModal(){
      this.upsellModal.isOpen = false;
    },
    /**
     * Request authorization popup.
     */
    requestAuthorization: function (network) {

      this.selected_network = network;
      if (this.checkDisabled(this.services[network], network)) {
        let networkName = this.$store.state.availableServices[network].fullname || this.$store.state.availableServices[network].name;
        let featureName = wp.i18n.sprintf( this.labels.upsell_extra_network.toLowerCase(), networkName);
        if(network === 'twitter' || network === 'facebook'){
          featureName = wp.i18n.sprintf( this.labels.upsell_extra_account.toLowerCase(), networkName);
        }
        this.upsellModal.title = wp.i18n.sprintf( this.labels.upsell_service_title, featureName.charAt(0).toUpperCase()
            + featureName.slice(1));
        this.upsellModal.body = wp.i18n.sprintf( network === 'telegram' ? this.labels.upsell_bz_service_body : this.labels.upsell_service_body,  featureName);
        this.upsellModal.link = wp.url.addQueryArgs(this.upsell_link, {
          utm_source: 'wp-admin',
          utm_medium: 'add_account',
          utm_campaign: networkName
        });
        this.openUpsellModal();
        return
      }
      this.$store.state.auth_in_progress = true
      if (this.$store.state.availableServices[this.selected_network].two_step_sign_in) {
        this.modal.serviceName = this.$store.state.availableServices[this.selected_network].name
        this.modal.description = this.$store.state.availableServices[this.selected_network].description
        this.modal.data = this.$store.state.availableServices[this.selected_network].credentials

        this.showHeaders = false;
        this.openModal()
      } else {
        this.activePopup = this.selected_network
        this.getUrlAndGo([])
      }
    },
    /**
     * Open popup to specific url.
     * @param url
     */
    openPopup(url) {
      this.$log.debug('Opening popup for url ', url)
      this.$store.commit('logMessage', ['Trying to open popup for url:' + url, 'notice'])
      window.open(url, '_self')
    },
    openEditPopup() {
      const [ serviceName, id, _ ] = this.$store.state.editPopup?.accountId.split( '_' );
      const accountToEdit = `${serviceName}_${id}`;
      if ( 'webhook' === serviceName ) {

        // Prepare fields.
        const serviceSchema = this.$store.state?.availableServices?.[serviceName];
        const fieldData = Object.keys( serviceSchema?.credentials )
          .reduce( ( fields, fieldId ) => {
            fields[fieldId] = { ...serviceSchema?.credentials[fieldId] };
            fields[fieldId].value = this.$store.state?.authenticatedServices?.[accountToEdit]?.credentials?.[fieldId];
            return fields;
          }, {} );

        // Prepare modal.
        this.modal.serviceName = serviceSchema.name;
        this.modal.description = '';
        this.modal.data = fieldData;
        this.webhooksHeaders = this.$store.state?.authenticatedServices?.[accountToEdit]?.credentials?.headers;

        this.openModal();
      }
    },
    /**
     * Get signin url. Used for authentication of the user who is using their own app.
     * @param credentials
     */
    getUrlAndGo(credentials) {
      this.$store.dispatch('fetchAJAXPromise', {
        req: 'get_service_sign_in_url',
        updateState: false,
        data: {service: this.selected_network, credentials: credentials}
      }).then(response => {
        //  console.log( 'Got some data, now lets show something in this component', response )
        if (!response.url || response.url == '') {
          this.cancelModal()
          alert('Could not authenticate, please make sure you entered the correct credentials.');
        } else {
          this.openPopup(response.url)
        }
      }, error => {
        Vue.$log.error('Got nothing from server. Prompt user to check internet connection and try again', error)
      })
    },
    requestAuthentication() {
      this.$store.dispatch('fetchAJAX', {req: 'authenticate_service', data: {service: this.selected_network}})
    },
    getButtonClass( service, network )  {
      let cssClasses = 'btn-' + network + ' ' + ( this.checkDisabled( service, network ) ? 'rop-disabled' : '' );
      
      if ( 'webhook' === network && this.canShowProPluginUpgradeWebhookNotice ) {
        cssClasses = cssClasses + ' tooltip tooltip-top';
      }
      return cssClasses;
    },
    /**
     * Open the modal.
     */
    openModal: function () {
      this.modal.isOpen = true
    },
    closeModal: function () {
      let credentials = {}
      let valid = true;
      for (const index of Object.keys(this.modal.data)) {
        credentials[index] = ''
        if ('value' in this.modal.data[index] && '' !== this.modal.data[index].value) {
          credentials[index] = this.modal.data[index].value
          this.modal.data[index].error = false
        } else {
          this.modal.data[index].error = true
          valid = false;
        }
      }

      if ( ! valid ) {
        this.$forceUpdate()
        return;
      }

      this.activePopup = this.selected_network

      if( this.isWebhook ) {
        credentials['headers'] = this.webhooksHeaders;
        if( this.isOpenToEdit ) {
          this.editAccountWebhook( credentials );
        } else {
          this.addAccountWebhook( credentials );
        }

        this.webhooksHeaders = [];
      } else if ( this.isTelegram ) {
        this.addAccountTelegram(credentials);
      } else {
        this.getUrlAndGo(credentials)
      }
      this.modal.isOpen = false;

      this.$store.commit( 'setEditPopupShowPermission', false );
    },
    cancelModal: function () {
      this.$store.state.auth_in_progress = false
      this.showAdvanceConfig = false
      this.modal.isOpen = false

      this.$store.commit( 'setEditPopupShowPermission', false );
    },
    /**
     * Add Facebook account.
     *
     * @param data Data.
     */
    addAccountFB(data) {
      this.$store.dispatch('fetchAJAXPromise', {
        req: 'add_account_fb',
        updateState: false,
        data: data
      }).then(response => {
        window.removeEventListener("message", event => this.getChildWindowMessage(event));
      }, error => {
        this.is_loading = false;
        Vue.$log.error('Got nothing from server. Prompt user to check internet connection and try again', error)
      });
    },
    /**
     * Add Twitter account.
     *
     * @param data Data.
     */
    addAccountTW(data) {
      this.$store.dispatch('fetchAJAXPromise', {
        req: 'add_account_tw',
        updateState: false,
        data: data
      }).then(() => {
        window.removeEventListener("message", this.getChildWindowMessage );
      }, error => {
        this.is_loading = false;
        Vue.$log.error('Got nothing from server. Prompt user to check internet connection and try again', error)
      });
    },
    /**
     * Add LinkedIn account.
     *
     * @param data Data.
     */
    addAccountLI(data) {
      this.$store.dispatch('fetchAJAXPromise', {
        req: 'add_account_li',
        updateState: false,
        data: data
      }).then(() => {
        window.removeEventListener("message", this.getChildWindowMessage );
      }, error => {
        this.is_loading = false;
        Vue.$log.error('Got nothing from server. Prompt user to check internet connection and try again', error)
      });
    },
    /**
     * Add Tumblr account.
     *
     * @param data Data.
     */
    addAccountTumblr(data) {
      this.$store.dispatch('fetchAJAXPromise', {
        req: 'add_account_tumblr',
        updateState: false,
        data: data
      }).then(() => {
        window.removeEventListener("message", this.getChildWindowMessage );
      }, error => {
        this.is_loading = false;
        Vue.$log.error('Got nothing from server. Prompt user to check internet connection and try again', error)
      });
    },
    /**
     * Add Google My Business account.
     *
     * @param data Data.
     */
    addAccountGmb(data) {
      this.$store.dispatch('fetchAJAXPromise', {
        req: 'add_account_gmb',
        updateState: false,
        data: data
      }).then(() => {
        window.removeEventListener("message", this.getChildWindowMessage );
      }, error => {
        this.is_loading = false;
        Vue.$log.error('Got nothing from server. Prompt user to check internet connection and try again', error)
      });
    },
    /**
     * Add VK account.
     *
     * @param data Data.
     */
    addAccountVk(data) {
      this.$store.dispatch('fetchAJAXPromise', {
        req: 'add_account_vk',
        updateState: false,
        data: data
      }).then(() => {
        window.removeEventListener("message", this.getChildWindowMessage );
      }, error => {
        this.is_loading = false;
        Vue.$log.error('Got nothing from server. Prompt user to check internet connection and try again', error)
      });
    },
    addAccountWebhook(data) {
      this.$store.dispatch('fetchAJAXPromise', {
        req: 'add_account_webhook',
        updateState: false,
        data: data
      }).then(() => {
        window.removeEventListener("message", this.getChildWindowMessage );
        window.location.reload();
      }, error => {
        this.is_loading = false;
        Vue.$log.error('Got nothing from server. Prompt user to check internet connection and try again', error)
      });
    },
    editAccountWebhook( data ) {
      const [ serviceName, id, _ ] = this.$store.state.editPopup?.accountId.split( '_' );
      data['id'] = id;
      data['service_id'] = `${serviceName}_${id}`;
      data['full_id'] = this.$store.state.editPopup?.accountId;
      data['active'] = Boolean( this.$store.state?.activeAccounts?.[this.$store.state.editPopup?.accountId] );

      this.$store.dispatch('fetchAJAXPromise', {
        req: 'edit_account_webhook',
        updateState: false,
        data: data
      }).then(() => {
        window.removeEventListener("message", this.getChildWindowMessage );
        window.location.reload();
      }, error => {
        this.is_loading = false;
        Vue.$log.error('Got nothing from server. Prompt user to check internet connection and try again', error)
      });
    },
    /**
     * Get message from child window.
     * @param {MessageEvent<any>} event Event.
     */
    getChildWindowMessage: async function (event) {

      if ( ! event.origin.includes( this.appOrigin ) ) {
        return;
      }

      const accountData = JSON.parse(event.data);

      if ('Twitter' === this.modal.serviceName) {
        this.addAccountTW( accountData );
      } else if ('Facebook' === this.modal.serviceName || 'Instagram' === this.modal.serviceName) {
        this.addAccountFB( accountData );
      } else if ('LinkedIn' === this.modal.serviceName) {
        this.addAccountLI( accountData );
      } else if ('Tumblr' === this.modal.serviceName) {
        this.addAccountTumblr( accountData );
      } else if ('Gmb' === this.modal.serviceName) {
        this.addAccountGmb( accountData );
      } else if ('Vk' === this.modal.serviceName) {
        this.addAccountVk( accountData );
      } else if ('Webhook' === this.modal.serviceName) {
        this.addAccountWebhook( accountData );
      } else if ('Telegram' === this.modal.serviceName) {
        this.addAccountTelegram( accountData );
      }

      try {
        window?.tiTrk?.with('tweet')?.add({
          feature: 'add-account',
          featureComponent: 'sign-in-btn',
          featureValue: this.modal.serviceName?.toLowerCase(),
        });
        await window?.tiTrk?.uploadEvents();
      } catch (e) {
        console.warn( e );
      }

      window.location.reload();
    },
    addWebhookHeader() {
      if ( ! this.currentWebhookHeader ) {
        return;
      }

      this.webhooksHeaders.push( this.currentWebhookHeader );
    },
    openPopupFB: function () {
      let loginUrl = this.appOrigin + this.appPathFB + '?callback_url=' + this.siteAdminUrl + '&token=' + this.appUniqueId + '&signature=' + this.appSignature + '&data=' + this.appAdminEmail;
      try {
        this.authPopupWindow.close();
      } catch (e) {
        // nothing to do
      } finally {
        this.authPopupWindow = window.open(loginUrl, 'authFB', this.windowParameters);
        this.cancelModal();
      }
      window.addEventListener("message", this.getChildWindowMessage );
    },
    openPopupTW: function () { // Open the popup specific for Twitter
      let loginUrl = this.appOrigin + this.appPathTW + '?callback_url=' + this.siteAdminUrl + '&token=' + this.appUniqueId + '&signature=' + this.appSignature + '&data=' + this.appAdminEmail + '&plugin_version=' + this.pluginVersion;

      try {
        this.authPopupWindow.close();
      } catch (e) {
        // nothing to do
      } finally {
        this.authPopupWindow = window.open(loginUrl, 'authTW', this.windowParameters);
        this.cancelModal();
      }
      window.addEventListener("message", this.getChildWindowMessage );
    },
    openPopupLI: function () { // Open the popup specific for LinkedIn
      let loginUrl = this.appOrigin + this.appPathLI + '?callback_url=' + this.siteAdminUrl + '&token=' + this.appUniqueId + '&signature=' + this.appSignature + '&data=' + this.appAdminEmail;
      try {
        this.authPopupWindow.close();
      } catch (e) {
        // nothing to do
      } finally {
        this.authPopupWindow = window.open(loginUrl, 'authLI', this.windowParameters);
        this.cancelModal();
      }
      window.addEventListener("message", this.getChildWindowMessage );
    },
    openPopupTumblr: function () { // Open the popup specific for Tumblr
      let loginUrl = this.appOrigin + this.appPathTumblr + '?callback_url=' + this.siteAdminUrl + '&token=' + this.appUniqueId + '&signature=' + this.appSignature + '&data=' + this.appAdminEmail;
      try {
        this.authPopupWindow.close();
      } catch (e) {
        // nothing to do
      } finally {
        this.authPopupWindow = window.open(loginUrl, 'authTmblr', this.windowParameters);
        this.cancelModal();
      }
      window.addEventListener("message", this.getChildWindowMessage );
    },
    openPopupGmb: function () { // Open the popup specific for Google My Business
      let loginUrl = this.appOrigin + this.appPathGmb + '?callback_url=' + this.siteAdminUrl + '&token=' + this.appUniqueId + '&signature=' + this.appSignature + '&data=' + this.appAdminEmail;
      try {
        this.authPopupWindow.close();
      } catch (e) {
        // nothing to do
      } finally {
        this.authPopupWindow = window.open(loginUrl, 'authGmb', this.windowParameters);
        this.cancelModal();
      }
      window.addEventListener("message", this.getChildWindowMessage );
    },
    openPopupVk: function () { // Open the popup specific for VK
      let loginUrl = this.appOrigin + this.appPathVk + '?callback_url=' + this.siteAdminUrl + '&token=' + this.appUniqueId + '&signature=' + this.appSignature + '&data=' + this.appAdminEmail;
      try {
        this.authPopupWindow.close();
      } catch (e) {
        // nothing to do
      } finally {
        this.authPopupWindow = window.open(loginUrl, 'authVk', this.windowParameters);
        this.cancelModal();
      }
      window.addEventListener("message", this.getChildWindowMessage );
    },
    addAccountTelegram(data) {
      this.$store.dispatch('fetchAJAXPromise', {
        req: 'add_account_telegram',
        updateState: false,
        data: data
      }).then(() => {
        window.removeEventListener("message", this.getChildWindowMessage );
        window.location.reload();
      }, error => {
        this.is_loading = false;
        Vue.$log.error('Got nothing from server. Prompt user to check internet connection and try again', error)
      });
    },
  }
}
</script>
<style scoped>
#rop-sign-in-area .btn[disabled] {
  cursor: not-allowed;
  pointer-events: auto;
  opacity: 0.3;
}

.big-btn#gmb-btn {
  padding: 0 35px 0 14px;
}

.btn-gmb {
  text-transform: uppercase;
}

.rop-disabled{
  opacity: 0.6
}
#rop-sign-in-area .btn:not( .btn-secondary ) {
  border:none;
}

#rop_core .rop-upsell-modal .modal-container{
  max-width: 500px;
  padding: 25px;
  .dashicons{
    font-size: 2rem;
  }
  .modal-title, .modal-footer{
    text-align: center;
  }
  .h3{
    min-height: 30px;
  }
  .h5.modal-title{
    padding:30px 20px 20px 20px;
  }
  .modal-header{
    padding: 0px;
  }
  .btn-success{
    border:none;
    background-color:#00a32a;
    color: #fff;
    padding: 0.5rem 1rem;
    height: auto;
    display: inline;
  }
  .btn-success:hover{
    background-color:#009528;
  }
  .modal-body{
    font-size: 0.7rem;
    margin: 10px 30px;
    padding: 0px;
  }
}

@media (min-width: 768px) {
  .content:has(.webhook-headers) {
    display: grid;
    grid-template-columns: auto auto;
    gap: 10px;
  }

  .content:has(.webhook-headers) .auth-app {
    min-width: 200px;
  }
}

</style>
