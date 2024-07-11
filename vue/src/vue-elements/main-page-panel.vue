<template>
  <div>
    <div class="columns panel-header">
      <div
        v-if="is_preloading_over > 0"
        class="column header-logo vertical-align"
      >
        <div>
          <img
            :src="plugin_logo"
            class="plugin-logo avatar avatar-lg"
          >
          <h1 class="plugin-title d-inline-block">
            Revive Social
          </h1>
        </div>
      </div>
      <toast />
      <div
        v-if=" is_rest_api_error "
        class="toast toast-error rop-api-not-available"
        v-html="labels.api_not_available"
      />
      <div
        v-if=" is_fb_domain_notice "
        class="toast toast-primary"
      >
        <button
          class="btn btn-clear float-right"
          @click="close_fb_domain_notice()"
        />
        <div v-html="labels.rop_facebook_domain_toast" />
      </div>
      <div class="sidebar sidebar-top card rop-container-start">
        <!-- Next post count down -->
        <countdown :current_time="current_time" />
        <!--  -->

        <button
          class="btn btn-sm"
          :class="btn_class"
          :data-tooltip="labels.active_account_warning"
          :disabled="!haveAccountsActive"
          @click="togglePosting()"
        >
          <i
            v-if="!is_loading && !start_status"
            class="fa fa-play"
          />
          <i
            v-else-if="!is_loading && start_status"
            class="fa fa-stop"
          />
          <i
            v-else
            class="fa fa-spinner fa-spin"
          />
          {{ ( start_status ? labels.stop : labels.start ) }} {{ labels.sharing }}
        </button>
      </div>
    </div>

    <div class="columns">
      <div class="panel column col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
        <div
          v-if="is_preloading_over > 0"
          class="panel-nav"
          style="padding: 8px;"
        >
          <ul class="tab ">
            <li
              v-for="tab in displayTabs"
              :id="tab.name.replace(' ', '').toLowerCase()"
              :key="tab.slug"
              class="tab-item c-hand"
              :class="{ active: tab.isActive }"
            >
              <a
                :class=" ( tab.slug === 'logs' && logs_no > 0 ) ? ' badge-logs badge' : '' "
                :data-badge="logs_no"
                @click="switchTab( tab.slug )"
              >{{ tab.name }}</a>
            </li>
          </ul>
        </div>
        <component
          :is="page.template"
          :type="page.view"
        />
      </div>

      <div
        v-if="is_preloading_over > 0"
        class="sidebar column col-3 col-xs-12 col-sm-12  col-md-12 col-lg-12"
        :class="'rop-license-plan-'+license"
      >
        <div class="card rop-container-start">
          <StatusBox
            :status-color-class="status_color_class"
            :label="status_label_display"
          />

          <countdown :current_time="current_time" />
          
          <button
            id="rop_start_stop_btn"
            class="btn"
            :class="btn_class"
            :data-tooltip="labels.active_account_warning"
            :disabled="!haveAccountsActive"
            @click="togglePosting()"
          >
            <i
              v-if="!is_loading && !start_status"
              class="fa fa-play"
            />
            <i
              v-else-if="!is_loading && start_status"
              class="fa fa-stop"
            />
            <i
              v-else
              class="fa fa-spinner fa-spin"
            />
            {{ labels.click }} {{ labels.to }} {{ ( start_status ? labels.stop : labels.start ) }} {{ labels.sharing }}
          </button>


          <div
            v-if="staging"
            id="staging-status"
            v-html="labels.staging_status"
          />
          <div
            v-if="!haveAccounts"
            class="rop-spacer"
          />
          <div v-if="haveAccounts">
            <upsell-sidebar />
          </div>
          <a
            v-if="license >= 1 && labels.rop_support_url !== ''"
            :href="labels.rop_support_url"
            target="_blank"
            class="btn rop-sidebar-action-btns"
          >{{ labels.rop_support }}</a>
          <a
            v-if="haveAccounts"
            href="https://docs.revive.social/"
            target="_blank"
            class="btn rop-sidebar-action-btns"
          >{{ labels.rop_docs }}</a>
          <a
            v-if="haveAccounts"
            href="https://wordpress.org/support/plugin/tweet-old-post/reviews/?rate=5#new-post"
            target="_blank"
            class="btn rop-sidebar-action-btns"
          >{{ labels.review_it }}</a>
          <div
            v-if="license_data_view.installed"
            class="license-container"
          >
            <h6 class="license-title">
              {{ license_field_title }}
            </h6>
            <p class="license-description text-gray">
              {{ labels.license_help }}
              <a
                href="https://store.themeisle.com/"
                rel="nofollow"
                target="_blank"
                class="text-gray"
              >{{ labels.purchase_history }}</a>
            </p>
            <input
              v-model="generalSettings.license_key"
              type="password"
              class="form-input"
              :placeholder="password_mask"
              :disabled="is_license_valid"
            >
            <span
              v-if="license_data_view.expires"
              class="text-gray expires-on"
            >
              {{ license_data_view.expires }}
            </span>
            <button
              v-if="! is_license_valid"
              class="btn btn-primary activate"
              @click="activateLicense()"
            >
              {{ labels.activate }}
            </button>
            <button
              v-if="is_license_valid"
              class="btn btn-secondary deactivate"
              @click="disableLicense()"
            >
              {{ labels.deactivate }}
            </button>
            <p
              v-if="license_error"
              class="text-error"
            >
              {{ license_error }}
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
    /* global ROP_ASSETS_URL */
    import AccountsTab from './accounts-tab-panel.vue'
    import SettingsTab from './settings-tab-panel.vue'
    import AccountsSelectorTab from './accounts-selector-panel.vue'
    import QueueTab from './queue-tab-panel.vue'
    import LogsTab from './logs-tab-panel.vue'
    import Toast from './reusables/toast.vue'
    import CountDown from './reusables/countdown.vue'
    import upsellSidebar from './upsell-sidebar.vue'
    import StatusBox from './reusables/status-box.vue'

    export default {
        name: 'MainPagePanel',
        components: {
            'accounts': AccountsTab,
            'settings': SettingsTab,
            'accounts-selector': AccountsSelectorTab,
            'queue': QueueTab,
            'logs': LogsTab,
            'upsell-sidebar': upsellSidebar,
            'toast': Toast,
            'countdown': CountDown,
            StatusBox
        },
        data: function () {
            return {
                to_pro_upsell: ROP_ASSETS_URL + 'img/to_pro.png',
                to_business_upsell: ROP_ASSETS_URL + 'img/to_business.png',
                plugin_logo: ROP_ASSETS_URL + 'img/logo_rop.png',
                license: this.$store.state.licence,
                labels: this.$store.state.labels.general,
                upsell_link: ropApiSettings.upsell_link,
                staging: ropApiSettings.staging,
                is_loading: false,
                is_loading_logs: false,
                status_is_error_display: false,
                license_data_view: this.$store.state.licenseDataView,
                license_field_title: window.wp.i18n.sprintf(this.$store.state.labels.general.license_product, 'Revive Old Posts Pro Add-on'),
                license_error: false,
                password_mask: this.$store.state.licenseDataView?.passwordMask
            }
        },
        computed: {
            is_license_valid() {
              return this.$store.state.licenseDataView.license === 'valid';
            },
            is_preloading_over: function () {
                return this.$store.state.hide_preloading;
            },
            /**
             * Display the clicked tab.
             *
             * @returns {module.exports.computed.displayTabs|*[]}
             */
            displayTabs: function () {
                return this.$store.state.displayTabs
            },
            /**
             * Get active page.
             */
            page: function () {
                return this.$store.state.page
            },
            /**
             * Check if rest api is available.
             */
            is_rest_api_error: function () {
                return this.$store.state.api_not_available
            },
            /**
             * Check if rest api is available.
             */
            is_fb_domain_notice: function () {
                return this.$store.state.fb_exception_toast
            },
            current_time: {
                get: function () {
                    return this.$store.state.cron_status.current_time;
                },
                set: function (value) {
                    this.$store.state.cron_status.current_time = value;
                }
            },
            date_format: function () {

                return this.$store.state.cron_status.date_format;
            },
            logs_no: function () {

                return this.$store.state.cron_status.logs_number;
            },
            /**
             * Get btn start class.
             */
            btn_class: function () {
                let btn_class = ('btn-' + (this.start_status ? 'danger' : 'success'));
                if (!this.haveAccountsActive) {
                    btn_class += ' tooltip button-disabled ';
                }
                return btn_class;
            },
            /**
             * Status label.
             */
            status_color_class: function () {
                let status_color_class = ('sharing-status-' + (this.start_status ? 'sharing' : 'notsharing'));
                if (!this.haveAccountsActive) {
                    status_color_class = ' sharing-status-notsharing  ';
                }
                if (this.status_is_error_display) {
                    return ' sharing-status-error ';
                }
                return status_color_class;
            },
            status_label_display: function () {
                let labels = this.$store.state.labels.general;
                let status_label_display = (this.start_status ? labels.sharing_to_account : labels.sharing_not_started);
                if (!this.haveAccountsActive) {
                    status_label_display = labels.sharing_not_started;
                }

                if (this.status_is_error_display) {
                    return labels.status + ': ' + labels.error_check_log;
                }
                return labels.status + ': ' + status_label_display;
            },
            /**
             * Check if we have accounts connected.
             *
             * @returns {boolean}
             */
            haveAccounts: function () {
                return (Object.keys(this.$store.state.authenticatedServices).length > 0);
            },
            /**
             * Check if we have accounts active.
             *
             * @returns {boolean}
             */
            haveAccountsActive: function () {
                return (Object.keys(this.$store.state.activeAccounts).length > 0);
            },
            /*
            * Check if the sharing is started.
            */
            start_status: function () {
                return this.$store.state.cron_status.current_status
            },

            /**
             * Get general settings.
             * @returns {module.exports.computed.generalSettings|Array|*}
             */
            generalSettings: function () {
                return this.$store.state.generalSettings
            },
        },
        mounted: function () {
            setInterval(() => {
                //this.$log.info('counting');
                if (this.current_time > 0) {
                    this.current_time += 1;
                }
            }, 1000);

            this.get_toast_message(false);
        },
        created() {
            this.$root.$refs.main_page = this;
        },
        methods: {
            /**
             *
             * */
            close_fb_domain_notice() {
                if (this.is_loading) {
                    this.$log.warn('Request in progress...Bail');
                    return;
                }

                this.$store.dispatch('fetchAJAXPromise', {
                    req: 'fb_exception_toast',
                    data: {action: 'hide'}
                }).then(response => {
                    this.$log.info('Succesfully closed facebook domain toast.');
                    this.is_loading = false;
                }, error => {
                    this.is_loading = false;
                    Vue.$log.error('Got nothing from server. Prompt user to check internet connection and try again', error)
                })
            },
            /**
             * Toggle sharing.
             *
             * @category New Cron System - adapted
             */
            togglePosting(force_status) {
                if (this.is_loading) {
                    this.$log.warn('Request in progress...Bail');
                    return;
                }

                let new_status = false;

                if (typeof force_status === 'undefined') {
                    new_status = this.start_status === false ? 'start' : 'stop';
                } else {
                    new_status = force_status === false ? 'start' : 'stop';
                }

                this.is_loading = true;

                this.$store.dispatch('fetchAJAXPromise', {
                    req: 'manage_cron',
                    data: {
                        'action': new_status
                    }
                }).then(response => {
                    this.is_loading = false;
                }, error => {
                    this.is_loading = false;
                    Vue.$log.error('Got nothing from server. Prompt user to check internet connection and try again', error)
                })
            },
            /**
             * Toggle tab active.
             * @param slug
             */
            switchTab(slug) {
                this.$store.commit('setTabView', slug)
            },

            get_toast_message(force) {
                if (this.is_loading_logs) {
                    this.$log.warn('Request in progress...Bail');
                    return;
                }
                this.is_loading_logs = true;
                this.$store.dispatch('fetchAJAXPromise', {
                    req: 'get_toast',
                    data: {force: force}
                }).then(response => {
                    this.$log.info('Succesfully fetched toast log.');
                    this.is_loading_logs = false;
                    this.$store.dispatch('fetchAJAX', {req: 'manage_cron', data: {action: 'status'}});

                    // Toast message code start
                    if (response.length) {
                        for (let index_error in response) {
                            if ('error' === response[index_error].type) {
                                let toast = {
                                    type: response[index_error].type,
                                    show: true,
                                    title: 'Error encountered',
                                    message: response[index_error].message
                                };
                                this.$store.commit('updateState', {stateData: toast, requestName: 'update_toast'});
                            } else if ('status_error' === response[index_error].type) {
                                this.$log.warn('Status is error check logs, global admin notice will be displayed');
                                this.status_is_error_display = true;
                            }
                        }
                    }
                    // Toast message code end

                }, error => {
                    Vue.$log.error('Got nothing from server. Prompt user to check internet connection and try again', error)
                    this.is_loading_logs = false;
                })
            },
            activateLicense() {
              this.uploadLicense( 'activate' );
            },
            disableLicense() {
              this.generalSettings.license_key = '';
              this.uploadLicense( 'deactivate' );
            },
            uploadLicense( action ) {
              this.$store.dispatch( 'fetchAJAXPromise', {
                req: 'set_license',
                updateState: false,
                data: {
                  license_key: this.generalSettings.license_key,
                  action
                }
              }).then( response => {
                if ( response?.success ) {
                  window.location.reload();
                } else {
                  this.license_error = response?.message;
                }
              }, error => {
                this.license_error = this.labels.could_not_send;
                Vue.$log.error('Got nothing from server. Prompt user to check internet connection and try again', error)
              })
            }
        }
    }
</script>

<style>
    #rop_core .badge[data-badge]::after {
        position: absolute;
        bottom: -16px;
        right: 0px;
    }

    #rop_core .rop-api-not-available {
        margin: 10px 0px 10px 0px;
    }

    #rop_core .badge.badge-logs::after {
        right: auto;
        top: 0px;
    }

    #rop_core .badge.badge-logs {
        padding-right: 10px;
    }

    #rop_core .license-container {
      margin-top: 20px;
      display: flex;
      flex-direction: column;
      gap: 15px;
    }

    #rop_core .license-title {
      font-size: 14px;
      font-weight: bold;
      line-height: 0;
      color: black;
    }

    #rop_core .license-description {
      font-size: 13px;
      line-height: 1.2em;
      margin: 0;
    }

    .expires-on {
      font-size: 13px;
      line-height: 1.2em;
    }
</style>
