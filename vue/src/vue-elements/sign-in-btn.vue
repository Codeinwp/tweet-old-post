<template>
  <div id="rop-sign-in-area">
    <div class="input-group text-right buttons-wrap">
      <button
        v-for="( service, network ) in services"
        :key="network"
        :title="getTooltip( service, network )"
        class="btn input-group-btn"
        :class="'btn-' + network + ' ' + ( checkDisabled( service, network ) ? 'rop-disabled' : '' )"
        @click="requestAuthorization( network )"
      >
        <i
          v-if="! [ 'gmb', 'twitter'].includes( network )"
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
        {{ displayName( service.name, false, true ) }}<span
          v-if="checkDisabled( service, network )"
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
            {{ labels.sign_in_btn }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import Tooltip from './reusables/popover.vue'

export default {
  name: 'SignInBtn',
  components: {Tooltip},
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
      showBtn: false
    }
  },
  computed: {
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
        'active': this.modal.isOpen === true
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

    isAllowedTumblr: function () {
      let showButton = true;
      if (!this.showTmblrAppBtn) {
        showButton = false;
      }
      return showButton;
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
        this.upsellModal.title = wp.i18n.sprintf( this.labels.upsell_service_title, networkName);
        this.upsellModal.body = wp.i18n.sprintf( this.labels.upsell_service_body,  networkName);
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

      if (!valid) {
        this.$forceUpdate()
        return;
      }

      this.activePopup = this.selected_network
      this.getUrlAndGo(credentials)
      this.modal.isOpen = false
    },
    cancelModal: function () {
      this.$store.state.auth_in_progress = false
      this.showAdvanceConfig = false
      this.modal.isOpen = false
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
.rop-disabled{
  opacity: 0.6
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
</style>
