<template>
  <div id="rop-sign-in-area">
    <div class="input-group text-right buttons-wrap">
      <button
        v-for="( service, network ) in services"
        :key="network"
        :disabled="checkDisabled( service, network )"
        :title="getTooltip( service, network )"
        class="btn input-group-btn"
        :class="'btn-' + network"
        @click="requestAuthorization( network )"
      >
        <i
          v-if="! [ 'gmb', 'twitter', 'webhook'].includes( network )"
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
        {{ displayName( service.name, false, true ) }}
      </button>
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
              v-if="isTwitter"
              class="auth-app"
            >
              <button
                class="btn btn-primary big-btn"
                @click="showAdvanceConfig = !showAdvanceConfig"
              >
                {{ labels.show_own_keys_config }}
              </button>
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
                v-if="isTwitter && ! showAdvanceConfig"
                id="rop-advanced-config"
                class="tw-signin-advanced-config"
              >
                <button
                  class="btn btn-secondary"
                  @click="openPopupTW()"
                >
                  {{ labels.tw_app_signin_btn }}
                </button>
                <div v-if="!hideOwnAppOption">
                  <span class="text-center">{{ labels.app_option_signin }}</span>
                </div>
                <tooltip
                  placement="bottom-right"
                  mode="hover"
                  :nowrap="false"
                  :min-width="260"
                  :max-width="350"
                >
                  <div slot="outlet">
                    <button
                      class="btn btn-sm input-group-btn circle"
                    >
                      <i
                        class="fa fa-exclamation-circle"
                        aria-hidden="true"
                      />
                    </button>
                  </div>
                  <div
                    slot="tooltip"
                    v-html="labels.tw_app_signin_tooltip"
                  />
                </tooltip>
              </div>

              <div
                v-if="showAdvanceConfig && (isFacebook || isTwitter || isLinkedIn || (isTumblr && isAllowedTumblr) )"
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
              v-if="(!isTwitter && !isFacebook && !isLinkedIn && !isGmb && !isTumblr && !isVk) || (isTumblr && !isAllowedTumblr)"
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
              v-if="isWebhook"
              :headers.sync="webhooksHeaders"
            />
          </div>
        </div>

        <div
          v-if="showAdvanceConfig && (isFacebook || isTwitter || isLinkedIn || isTumblr)"
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
          v-if="(!isTwitter && !isFacebook && !isLinkedIn && !isGmb && !isTumblr && !isVk) || (isTumblr && !isAllowedTumblr)"
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
    serviceId: function () {
      return 'service-' + this.modal.serviceName.toLowerCase()
    },
    isFacebook() {
      return this.modal.serviceName === 'Facebook';
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

    isAllowedTumblr: function () {
      let showButton = true;
      if (!this.showTmblrAppBtn) {
        showButton = false;
      }
      return showButton;
    }
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
    /**
     * Request authorization popup.
     */
    requestAuthorization: function (network) {
      this.selected_network = network;
      this.$store.state.auth_in_progress = true
      if (this.$store.state.availableServices[this.selected_network].two_step_sign_in) {
        this.modal.serviceName = this.$store.state.availableServices[this.selected_network].name
        this.modal.description = this.$store.state.availableServices[this.selected_network].description
        this.modal.data = this.$store.state.availableServices[this.selected_network].credentials
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
      const accountToEdit = this.$store.state.editPopup?.accountId?.split( '_' )?.slice( 0, 2 ).join('_');
      const serviceName = this.$store.state?.authenticatedServices?.[accountToEdit]?.service;

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
          credentials['service_id'] = this.$store.state.editPopup?.accountId;
          credentials['active'] = Boolean( this.$store.state?.activeAccounts?.[this.$store.state.editPopup?.accountId] );
          this.editAccountWebhook( credentials );
        } else {
          this.addAccountWebhook( credentials );
        }

        this.webhooksHeaders = [];
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
      } else if ('Facebook' === this.modal.serviceName) {
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
