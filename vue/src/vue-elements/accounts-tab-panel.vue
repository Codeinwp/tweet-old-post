<template>
  <div class="tab-view">
    <div class="panel-body">
      <div
        v-if="twitter_warning"
        class="toast  toast-warning"
        v-html="labels.twitter_warning"
      />
      <div class="container">
        <div
          class="columns"
          :class="'rop-tab-state-'+is_loading"
        >
          <div class="column col-sm-12 col-md-12 col-lg-12 text-left rop-available-accounts mt-2">
            <vue_spinner
              v-if="is_preloading === 0"
              ref="Preloader"
              :preloader_message="labels.preloader_message_accounts"
            />
            <div
              v-if="accountsCount === 0 && is_preloading > 0"
              class="empty mb-2"
            >
              <div class="empty-icon">
                <i class="fa fa-3x fa-user-circle-o" />
              </div>
              <p class="empty-title h5">
                {{ labels.no_accounts }}
              </p>
              <p class="empty-subtitle">
                {{ labels.no_accounts_desc }}
              </p>
            </div>
            <template v-if="is_preloading > 0">
              <div
                v-for="( account, id ) in accounts"
                :key="id"
                
                class="account-container"
              >
                <service-user-tile
                  :account_data="account"
                  :account_id="id"
                />
                <span class="divider" />
              </div>
            </template>
            <div
              v-if="is_preloading > 0"
              id="rop-add-account-button"
              class="add-accounts"
            >
              <add-account-tile />
              <span class="divider" />
            </div>
          </div>
        </div>
      </div>
      <div
        v-if="is_preloading > 0"
        class="panel-footer"
      >
        <div
          v-if="checkLicense && pro_installed"
          class="columns my-2"
        >
          <div class="column col-12">
            <i class="fa fa-info-circle " /> <span v-html="labels.activate_license" />
          </div>
        </div>
        <div
          v-if="hasActiveAccountsLimitation"
          class="columns my-2"
        >
          <div class="column col-12">
            <p class="upsell">
              <i class="fa fa-info-circle " /> <span v-html="labels.upsell_accounts" />
            </p>
          </div>
        </div>
        <div class="column col-12 text-right">
          <button
            class="btn btn-secondary"
            @click="resetAccountData()"
          >
            <i
              v-if="!is_loading"
              class="fa fa-ban"
            />
            <i
              v-else
              class="fa fa-spinner fa-spin"
            />
            {{ labels.remove_all_cta }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
    import SignInBtn from './sign-in-btn.vue'
    import ServiceUserTile from './service-user-tile.vue'
    import AddAccountTile from './reusables/add-account-tile.vue'
    import vue_spinner from './reusables/vue-spinner.vue'
    import WebhookAccountModal from './reusables/webhook-account-modal.vue'

    export default {
        name: 'AccountView',
        components: {
            SignInBtn,
            ServiceUserTile,
            AddAccountTile,
            WebhookAccountModal,
            'vue_spinner': vue_spinner
        },
        data: function () {
            return {
                addAccountActive: false,
                accountsCount: 0,
                is_loading: false,
                twitter_warning: false,
                labels: this.$store.state.labels.accounts,
                upsell_link: ropApiSettings.upsell_link,
                pro_installed: ropApiSettings.pro_installed
            }
        },
        computed: {
            /**
             * Get all the available/active accounts.
             */
            accounts: function () {
                const all_accounts = {};
                let twitter = 0;
                const services = this.$store.state.authenticatedServices;
                for (const key in services) {
                    if (!services.hasOwnProperty(key)) {
                        continue;
                    }
                    const service = services[key];
                    for (const account_id in service.available_accounts) {
                        if (!service.available_accounts.hasOwnProperty(account_id)) {
                            continue;
                        }
                        all_accounts[account_id] = service.available_accounts[account_id];
                        if (service.service === 'twitter') {
                            twitter += Object.keys(service.available_accounts).length;
                        }
                    }
                }
                this.twitter_warning = twitter > 1;
                this.$log.info('All accounts: ', all_accounts);
                this.$log.debug('Preloading: ', this.$store.state.hide_preloading);
                this.accountsCount = Object.keys(all_accounts).length;
                return all_accounts;
            },
            /**
             * Check if we have a pro license.
             * @returns {boolean}
             */
            checkLicense: function () {
                return (this.$store.state.license < 1);
            },
            is_preloading: function () {
                return this.$store.state.hide_preloading;
            },
            hasActiveAccountsLimitation: function () {
              return !this.pro_installed && this.accountsCount >= 2 && this.checkLicense ;
            }
        },
        mounted: function () {
            if (0 === this.is_preloading) {
                this.page_loader_module_display();
            }
        },

        methods: {
            page_loader_module_display() {
                // Display the preloader until accounts are loaded.
                //this.$refs.Preloader.preloader_message = this.$store.state.labels.accounts.preloader_message;
                this.$refs.Preloader.show();
            },
            resetAccountData: function () {
                if (this.is_loading) {
                    this.$log.warn('Request in progress...Bail');
                    return;
                }
                this.is_loading = true;
                this.$store.dispatch('fetchAJAXPromise', {
                    req: 'reset_accounts',
                    data: {}
                }).then(response => {
                    this.is_loading = false;
                    if (this.$parent.start_status === true) {
                        // Stop sharing process if enabled.
                        this.$parent.togglePosting();
                    }
                    this.$store.dispatch('fetchAJAXPromise', {
                        req: 'get_available_services'
                    }).then(response => {
                        this.is_loading = false;
                    })
                }, error => {
                    this.is_loading = false;
                    Vue.$log.error('Got nothing from server. Prompt user to check internet connection and try again', error)
                })
            }
        }
    }
</script>
<style scoped>
    #rop_core .columns.py-2 .text-gray {
        margin: 0;
        line-height: normal;
    }

    #rop_core .input-group {
        width: 100%;
    }

    b {
        margin-bottom: 5px;
        display: block;
    }

    #rop_core .text-gray b {
        display: inline;
    }

    #rop_core .input-group .input-group-addon {
        padding: 3px 5px;
    }

    #rop_core .rop-available-accounts h5 {
        margin-bottom: 15px;
    }

    @media ( max-width: 600px ) {
        #rop_core .panel-body .text-gray {
            margin-bottom: 10px;
        }

        #rop_core .text-right {
            text-align: left;
        }
    }
</style>
