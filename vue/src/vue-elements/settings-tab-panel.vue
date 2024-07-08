<template>
  <div class="tab-view">
    <div class="panel-body">
      <div
        class="container"
        :class="'rop-tab-state-'+is_loading"
      >
        <!-- Disabled Remote Cron feature and left code commented out -->
        <!-- <div class="columns py-2" v-if="this.apply_exclude_limit_cron" >
                    <div class="column col-6 col-sm-12 vertical-align rop-control">
                        <b>{{labels.cron_type_label}}</b>
                        <p class="text-gray"><span v-html="labels.cron_type_label_desc"></span></p>
                    </div>
                    <div class="column col-6 col-sm-12 vertical-align text-left rop-control">
                        <div class="form-group">
                          
                            <div style="padding: 10px; text-align: left;">
                                <toggle-button
                                        :value="rop_cron_remote"
                                        :labels="{checked: 'Remote Cron', unchecked: 'Local Cron'}"
                                        :width="110"
                                        :height="28"
                                        :switch-color="{checked: '#FFFFFF', unchecked: '#FFFFFF'}"
                                        :color="{checked: '#7DCE94', unchecked: '#82C7EB'}"
                                        @change="rop_cron_remote = $event.value; update_cron_type_action()"
                                        :disabled="!rop_cron_remote_agreement"
                                        :sync="true"
                                />
                            </div>
                          <input
                              type="checkbox"
                              :checked="rop_cron_remote_agreement"
                              :disabled="rop_cron_remote_agreement"
                              @change="update_agreement_checkbox()"
                          /> <span v-html="labels.cron_type_label_desc_terms"></span>
                          <br>
                          <br>
                          <small v-html="labels.cron_type_notice"></small>
                        </div>
                    </div>
                </div> -->
        <!-- <span class="divider" v-if="this.apply_exclude_limit_cron && ! isBiz" ></span> -->
                
        <!-- Minimum interval between shares -->

        <div
          v-if="! isBiz"
          class="columns py-2"
        >
          <div class="column col-6 col-sm-12 vertical-align">
            <b>{{ labels.min_interval_title }}</b>
            <p class="text-gray">
              {{ labels.min_interval_desc }}
            </p>
          </div>
          <div class="column col-6 col-sm-12 vertical-align">
            <counter-input
              id="default_interval"
              :min-val="generalSettings.min_interval"
              :step-val="generalSettings.step_interval"
              :value.sync="generalSettings.default_interval"
            />
          </div>
        </div>
        
        <div
          v-if="!isPro && generalSettings.default_interval < 12"
          class="columns "
        >
          <div class="column text-center">
            <p class="upsell">
              <i class="fa fa-info-circle" /> {{ labels.min_interval_upsell }}
            </p>
          </div>
        </div>

        <span
          v-if="! isBiz"
          class="divider"
        />
                
        <!-- Min Post Age -->
               
        <div class="columns py-2">
          <div class="column col-6 col-sm-12 vertical-align">
            <b>{{ labels.min_days_title }}</b>
            <p class="text-gray">
              {{ labels.min_days_desc }}
            </p>
          </div>
          <div class="column col-6 col-sm-12 vertical-align">
            <counter-Input
              id="min_post_age"
              :value.sync="generalSettings.minimum_post_age"
            />
          </div>
        </div>

        <span class="divider" />

        <!-- Max Post Age -->
        <div
          class="columns py-2"
          :class="'rop-control-container-'+isPro"
        >
          <div class="column col-6 col-sm-12 vertical-align rop-control">
            <b>{{ labels.max_days_title }}</b>
            <p class="text-gray">
              {{ labels.max_days_desc }}
            </p>
          </div>
          <div class="column col-6 col-sm-12 vertical-align">
            <counter-input
              id="max_post_age" 
              :value.sync="generalSettings.maximum_post_age"
              :disabled="!isPro"
            />
          </div>
        </div>
                
        <div
          v-if="!isPro"
          class="columns "
        >
          <div class="column text-center">
            <p class="upsell">
              <i class="fa fa-info-circle" /> {{ labels.available_in_pro }}
            </p>
          </div>
        </div>
        <span class="divider" />
              
        <!-- Number of Posts -->
        <div
          class="columns py-2"
          :class="'rop-control-container-'+isPro"
        >
          <div class="column col-6 col-sm-12 vertical-align rop-control">
            <b>{{ labels.no_posts_title }}</b>
            <p class="text-gray">
              {{ labels.no_posts_desc }}
            </p>
          </div>
          <div class="column col-6 col-sm-12 vertical-align rop-control">
            <counter-input
              id="no_of_posts"
              :value.sync="generalSettings.number_of_posts"
              :disabled="!isPro"
            />
          </div>
        </div>
        <div
          v-if="!isPro"
          class="columns "
        >
          <div class="column text-center">
            <p class="upsell">
              <i class="fa fa-info-circle" /> {{ labels.available_in_pro }}
            </p>
          </div>
        </div>
        <span class="divider" />

        <!-- Share more than once -->
        <div class="columns py-2">
          <div class="column col-6 col-sm-12 vertical-align">
            <b>{{ labels.share_once_title }}</b>
            <p class="text-gray">
              {{ labels.share_once_desc }}
            </p>
          </div>
          <div class="column col-6 col-sm-12 vertical-align text-left">
            <div class="form-group">
              <label
                id="share_more_than_once"
                class="form-checkbox"
              >
                <input
                  v-model="generalSettings.more_than_once"
                  type="checkbox"
                >
                <i class="form-icon" /> {{ labels.yes_text }}
              </label>
            </div>
          </div>
        </div>
        <span class="divider" />
               
        <!-- Post Types -->
        <div
          class="columns py-2"
          :class="'rop-control-container-'+isPro"
        >
          <div class="column col-6 col-sm-12 vertical-align rop-control">
            <b>{{ labels.post_types_title }}</b>
            <p class="text-gray">
              <span v-html="labels.post_types_desc" />
            </p>
          </div>
          <div class="column col-6 col-sm-12 vertical-align text-left rop-control">
            <multiple-select
              id="rop_post_types"
              :options="postTypes"
              :disabled="isPro"
              :selected="generalSettings.selected_post_types"
              :changed-selection="updatedPostTypes"
            />

            <p
              v-if="checkMediaPostType "
              class="text-primary rop-post-type-badge"
              v-html="labels.post_types_attachament_info"
            />
          </div>
        </div>

        <div
          v-if="!isPro"
          class="columns "
        >
          <div class="column text-center">
            <p class="upsell">
              <i class="fa fa-info-circle" /> {{ labels.post_types_upsell }}
            </p>
          </div>
        </div>

        <span
          v-if="!isPro || license_price_id === 7"
          class="divider"
        />

        <!-- Taxonomies -->
        <!-- Price ID 7 is Starter Plan -->
        <div
          v-if="!isPro || license_price_id === 7"
          class="columns py-2"
        >
          <div class="column col-6 col-sm-12 vertical-align">
            <b>{{ labels.taxonomies_title }}</b>
            <p class="text-gray">
              <span v-html="labels.taxonomies_desc" />
            </p>
          </div>
          <div
            id="rop_taxonomies"
            class="column col-6 col-sm-12 vertical-align text-left"
          >
            <div class="input-group">
              <multiple-select
                :options="taxonomies"
                :selected="generalSettings.selected_taxonomies"
                :changed-selection="updatedTaxonomies"
                :is_pro_version="isPro"
                :apply_limit="isTaxLimit"
                @display-limiter-notice="displayProMessage"
              />
              <span class="input-group-addon vertical-align">
                <label class="form-checkbox">
                  <input
                    v-model="generalSettings.exclude_taxonomies"
                    type="checkbox"
                  >
                  <i class="form-icon" />{{ labels.taxonomies_exclude }}
                </label>
              </span>
            </div>
            <p
              v-if="is_taxonomy_message"
              class="text-primary rop-post-type-badge"
              v-html="labels.post_types_taxonomy_limit"
            />
          </div>
        </div>

        <span class="divider" />

        <!-- Update publish date -->
        <div
          class="columns py-2"
          :class="'rop-control-container-'+isPro"
        >
          <div class="column col-6 col-sm-12 vertical-align rop-control">
            <b>{{ labels.update_post_published_date_title }}</b>
            <p class="text-gray">
              <span v-html="labels.update_post_published_date_desc" />
            </p>
          </div>
          <div class="column col-6 col-sm-12 vertical-align text-left rop-control">
            <div class="form-group">
              <label
                id="share_more_than_once"
                class="form-checkbox"
              >
                <input
                  v-model="generalSettings.update_post_published_date"
                  type="checkbox"
                  :disabled="!isPro"
                >
                <i class="form-icon" /> {{ labels.yes_text }}
              </label>
            </div>
          </div>
        </div>
        <div
          v-if="!isPro"
          class="columns "
        >
          <div class="column text-center">
            <p class="upsell">
              <i class="fa fa-info-circle" /> {{ labels.available_in_pro }}
            </p>
          </div>
        </div>
        <span class="divider" />

        <!-- Google Analytics -->
        <div class="columns py-2">
          <div class="column col-6 col-sm-12 vertical-align">
            <b>{{ labels.ga_title }}</b>
            <p class="text-gray">
              {{ labels.ga_desc }}
            </p>
          </div>
          <div class="column col-6 col-sm-12 vertical-align text-left">
            <div class="form-group">
              <label class="form-checkbox">
                <input
                  v-model="generalSettings.ga_tracking"
                  type="checkbox"
                >
                <i class="form-icon" />{{ labels.yes_text }}
              </label>
            </div>
          </div>
        </div>
        <span class="divider" />

        <!-- Enable Instant Sharing Feature (Post on Publish) -->
        <div class="columns py-2">
          <div class="column col-6 col-sm-12 vertical-align rop-control">
            <b>{{ labels.instant_share_title }}</b>
            <p class="text-gray">
              <span v-html="labels.instant_share_desc" />
            </p>
          </div>
          <div class="column col-6 col-sm-12 vertical-align text-left rop-control">
            <div class="form-group">
              <label
                id="rop_instant_share"
                class="form-checkbox"
              >
                <input
                  v-model="generalSettings.instant_share"
                  type="checkbox"
                >
                <i class="form-icon" />{{ labels.yes_text }}
              </label>
            </div>
          </div>
        </div>

        <span class="divider" />

        <!-- Use True Instant Share -->
        <div
          v-if="isInstantShare"
          class="columns py-2"
        >
          <div class="column col-6 col-sm-12 vertical-align rop-control">
            <b>{{ labels.true_instant_share_title }}</b>
            <p class="text-gray">
              <span v-html="labels.true_instant_share_desc" />
            </p>
          </div>
          <div class="column col-6 col-sm-12 vertical-align text-left rop-control">
            <div class="form-group">
              <label class="form-checkbox">
                <input
                  v-model="generalSettings.true_instant_share"
                  type="checkbox"
                >
                <i class="form-icon" />{{ labels.yes_text }}
              </label>
            </div>
          </div>
        </div>

        <span
          v-if="isInstantShare"
          class="divider"
        />

        <!-- Enable Instant Sharing By Default -->
        <div
          v-if="isInstantShare"
          class="columns py-2"
        >
          <div class="column col-6 col-sm-12 vertical-align rop-control">
            <b>{{ labels.instant_share_default_title }}</b>
            <p class="text-gray">
              {{ labels.instant_share_default_desc }}
            </p>
          </div>
          <div class="column col-6 col-sm-12 vertical-align text-left rop-control">
            <div class="form-group">
              <label class="form-checkbox">
                <input
                  v-model="generalSettings.instant_share_default"
                  type="checkbox"
                >
                <i class="form-icon" />{{ labels.yes_text }}
              </label>
            </div>
          </div>
        </div>
        <span
          v-if="isInstantShare && isInstantShareByDefault"
          class="divider"
        />
                
        <!-- Choose Accounts Manually -->
        <div
          v-if="isInstantShare && isInstantShareByDefault"
          class="columns py-2"
        >
          <div class="column col-6 col-sm-12 vertical-align rop-control">
            <b>{{ labels.instant_share_choose_accounts_manually_title }}</b>
            <p class="text-gray">
              {{ labels.instant_share_choose_accounts_manually_desc }}
            </p>
          </div>
          <div class="column col-6 col-sm-12 vertical-align text-left rop-control">
            <div class="form-group">
              <label class="form-checkbox">
                <input
                  v-model="generalSettings.instant_share_choose_accounts_manually"
                  type="checkbox"
                >
                <i class="form-icon" />{{ labels.yes_text }}
              </label>
            </div>
          </div>
        </div>
        <span
          v-if="isInstantShare"
          class="divider"
        />
                
        <!-- Share Scheduled Posts to Social Media On Publish -->
        <div
          v-if="isInstantShare"
          class="columns py-2"
          :class="'rop-control-container-'+isPro"
        >
          <div class="column col-6 col-sm-12 vertical-align rop-control">
            <b>{{ labels.instant_share_future_scheduled_title }}</b>
            <p class="text-gray">
              <span v-html="labels.instant_share_future_scheduled_desc" />
            </p>
          </div>
          <div class="column col-6 col-sm-12 vertical-align text-left rop-control">
            <div class="form-group">
              <label class="form-checkbox">
                <input
                  v-model="generalSettings.instant_share_future_scheduled"
                  type="checkbox"
                  :disabled="!isPro"
                >
                <i class="form-icon" />{{ labels.yes_text }}
              </label>
            </div>
          </div>
        </div>
        <!-- Upsell -->
        <div
          v-if="!isPro && isInstantShare"
          class="columns "
        >
          <div class="column text-center">
            <p class="upsell">
              <i class="fa fa-info-circle" /> {{ labels.available_in_pro }}
            </p>
          </div>
        </div>
        <span
          v-if="isInstantShare"
          class="divider"
        />

        <!-- Enable Share Content Variations -->
        <div
          class="columns py-2"
          :class="'rop-control-container-'+isPro"
        >
          <div class="column col-6 col-sm-12 vertical-align rop-control">
            <b>{{ labels.custom_share_title }}</b>
            <p class="text-gray">
              <span v-html="labels.custom_share_desc" />
            </p>
          </div>
          <div class="column col-6 col-sm-12 vertical-align text-left rop-control">
            <div class="form-group">
              <label
                id="rop_custom_share_msg"
                class="form-checkbox"
              >
                <input
                  v-model="generalSettings.custom_messages"
                  type="checkbox"
                  :disabled="!isPro"
                >
                <i class="form-icon" />{{ labels.yes_text }}
              </label>
            </div>
          </div>
        </div>
        <!-- Upsell -->
        <div
          v-if="!isPro"
          class="columns "
        >
          <div class="column text-center">
            <p class="upsell">
              <i class="fa fa-info-circle" /> {{ labels.available_in_pro }}
            </p>
          </div>
        </div>
        <span class="divider" />

        <!-- Share Message Variations In the Order They Are Added -->
        <div
          v-if="isCustomMsgs"
          class="columns py-2"
          :class="'rop-control-container-'+isPro"
        >
          <div class="column col-6 col-sm-12 vertical-align rop-control">
            <b>{{ labels.custom_share_order_title }}</b>
            <p class="text-gray">
              {{ labels.custom_share_order_desc }}
            </p>
          </div>
          <div class="column col-6 col-sm-12 vertical-align text-left rop-control">
            <div class="form-group">
              <label
                id="rop_custom_share_msg"
                class="form-checkbox"
              >
                <input
                  v-model="generalSettings.custom_messages_share_order"
                  type="checkbox"
                  :disabled="!isPro"
                >
                <i class="form-icon" />{{ labels.yes_text }}
              </label>
            </div>
          </div>
        </div>
        <span
          v-if="isCustomMsgs"
          class="divider"
        />

        <!-- Housekeeping -->
        <div class="columns py-2">
          <div class="column col-6 col-sm-12 vertical-align rop-control">
            <b>{{ labels.housekeeping }}</b>
            <p class="text-gray">
              {{ labels.housekeeping_desc }}
            </p>
          </div>
          <div class="column col-6 col-sm-12 vertical-align text-left rop-control">
            <div class="form-group">
              <label class="form-checkbox">
                <input
                  v-model="generalSettings.housekeeping"
                  type="checkbox"
                >
                <i class="form-icon" />{{ labels.yes_text }}
              </label>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="panel-footer text-right">
      <button
        class="btn btn-primary"
        @click="saveGeneralSettings()"
      >
        <i
          v-if="!is_loading"
          class="fa fa-check"
        /> <i
          v-else
          class="fa fa-spinner fa-spin"
        /> {{ labels.save }}
      </button>
    </div>
  </div>
</template>

<script>
    import Vue from 'vue'
    import counterInput from './reusables/counter-input.vue'
    import MultipleSelect from './reusables/multiple-select.vue'
    import ToggleButton from 'vue-js-toggle-button'

    Vue.use(ToggleButton);

    export default {
        name: 'SettingsView',
		components: {
			counterInput,
			MultipleSelect
		},
        data: function () {
            return {
                searchQuery: '',
                postTimeout: '',
                labels: this.$store.state.labels.settings,
                upsell_link: ropApiSettings.upsell_link,
                is_loading: false,
                is_taxonomy_message: false,
                /**
                 * @category New Cron System
                 */
                rop_cron_remote: Boolean(ropApiSettings.rop_cron_remote),
                rop_cron_remote_agreement: Boolean(ropApiSettings.rop_cron_remote_agreement),
                is_cron_btn_active: false
            }
        },
        computed: {
            generalSettings: function () {
                return this.$store.state.generalSettings
            },
            isPro: function () {
                return (this.$store.state.licence >= 1);
            },
            license_price_id: function () {
                return this.$store.state.licence;
            },
            isTaxLimit: function () {
                if (ropApiSettings.tax_apply_limit > 0) {
                    return true;
                }
                return false;
            },
            isBiz: function () {
                return (this.$store.state.licence > 1 && this.$store.state.licence !== 7 );
            },
            postTypes: function () {
                return this.$store.state.generalSettings.available_post_types;
            },
            taxonomies: function () {
                return this.$store.state.generalSettings.available_taxonomies
            },
            checkMediaPostType() {
                let post_type = this.$store.state.generalSettings.selected_post_types;

                if (post_type === undefined || post_type === null) {
                    return false;
                }

                if (post_type.length < 0) {
                    return false;
                }

                var result = post_type.map(a => a.value);
                return (result.indexOf('attachment') > -1);
            },
            isInstantShare: function () {
                return this.$store.state.generalSettings.instant_share;
            },
            isInstantShareByDefault: function () {
                return this.$store.state.generalSettings.instant_share_default;
            },
            isCustomMsgs: function () {
                return this.$store.state.generalSettings.custom_messages;
            },
            apply_exclude_limit_cron: function () {
              return ropApiSettings.remote_cron_type_limit > 0;
            }
        },
        mounted: function () {
            this.$log.info('In General Settings state ');
            this.getGeneralSettings();
        },
        methods: {
            /**
             * Will update settings related to Cron
             * true = Will use remote true Cron Job System
             * false = Will use local WordPress Cron Job System.
             *
             * @category New Cron System
             */
            update_cron_type_action() {

                this.is_cron_btn_active = true;
                Vue.$log.info('#! Use Remote Cron : ' + this.rop_cron_remote);

                this.$store.dispatch('fetchAJAXPromise', {
                    req: 'update_cron_type',
                    data: {
                        'action': this.rop_cron_remote
                    }
                }).then(response => {
                    this.is_cron_btn_active = false;
                    this.$root.$refs.main_page.togglePosting(true);
                    ropApiSettings.rop_cron_remote = this.rop_cron_remote;
                    //this.$emit( 'togglePosting', true);
                    //this.togglePosting(true);
                }, error => {
                    this.is_cron_btn_active = false;
                    Vue.$log.error('Got nothing from server. Prompt user to check internet connection and try again', error)
                })
            },
            update_agreement_checkbox(){

              this.rop_cron_remote_agreement = true;
              Vue.$log.info('#! User agreement : ' + this.rop_cron_remote_agreement);

              this.$store.dispatch('fetchAJAXPromise', {
                req: 'update_cron_type_agreement',
                data: {
                  'action': this.rop_cron_remote_agreement
                }
              }).then(response => {

                this.is_cron_btn_active = false;
                ropApiSettings.rop_cron_remote_agreement = this.rop_cron_remote_agreement;

              }, error => {
                this.rop_cron_remote_agreement = false;
                Vue.$log.error('Got nothing from server. Prompt user to check internet connection and try again', error)
              })

          },
            displayProMessage(data) {
                if (!this.isPro && data >= 4 ) {
                    if (true === this.isTaxLimit) {
                        this.is_taxonomy_message = true;
                    } else {
                        this.is_taxonomy_message = false;
                    }
                }
            },
            getGeneralSettings() {

                if (this.$store.state.generalSettings.length === 0) {
                    this.is_loading = true;
                    this.$log.info('Fetching general settings.');
                    this.$store.dispatch('fetchAJAXPromise', {req: 'get_general_settings'}).then(response => {
                        this.is_loading = false;
                        this.$log.debug('Succesfully fetched.');
                    }, error => {
                        this.is_loading = false;
                        this.$log.error('Can not fetch the general settings.')
                    })
                }

            },
            searchUpdate(newQuery) {
                this.searchQuery = newQuery
            },
            updatedPostTypes(data) {
                let postTypes = []
                for (let index in data) {
                    postTypes.push(data[index].value)
                }

				this.$store.commit('updateState', {stateData: data, requestName: 'update_selected_post_types'})
				this.$store.dispatch('fetchAJAX', {req: 'get_taxonomies', data: {post_types: postTypes}})
			},
            updatedTaxonomies(data) {
                let taxonomies = [];

                if (this.isPro || false === this.isTaxLimit) {
                    this.is_taxonomy_message = false;
                    for (let index in data) {
                        taxonomies.push(data[index].value)
                    }
                    this.$store.commit('updateState', {stateData: data, requestName: 'update_selected_taxonomies'})
                } else {

                    if (data.length > 3) {
                        this.is_taxonomy_message = true;
                    } else {
                        this.is_taxonomy_message = false;
                        for (let index in data) {
                            taxonomies.push(data[index].value)
                        }
                        this.$store.commit('updateState', {stateData: data, requestName: 'update_selected_taxonomies'})
                    }
                }


			},
			saveGeneralSettings() {
				let postTypesSelected = this.$store.state.generalSettings.selected_post_types
				let taxonomiesSelected = this.$store.state.generalSettings.selected_taxonomies
				let excludeTaxonomies = this.generalSettings.exclude_taxonomies
				let postsSelected = this.generalSettings.selected_posts
				this.is_loading = true;
				this.$log.info('Sending request for saving general settings..');
        
        const savedSettings = {
          available_taxonomies: this.generalSettings.available_taxonomies,
          default_interval: this.generalSettings.default_interval,
          minimum_post_age: this.generalSettings.minimum_post_age,
          maximum_post_age: this.generalSettings.maximum_post_age,
          number_of_posts: this.generalSettings.number_of_posts,
          more_than_once: this.generalSettings.more_than_once,
          selected_post_types: postTypesSelected,
          selected_taxonomies: taxonomiesSelected,
          exclude_taxonomies: excludeTaxonomies,
          update_post_published_date: this.generalSettings.update_post_published_date,
          ga_tracking: this.generalSettings.ga_tracking,
          custom_messages: this.generalSettings.custom_messages,
          custom_messages_share_order: this.generalSettings.custom_messages_share_order,
          instant_share: this.generalSettings.instant_share,
          true_instant_share: this.generalSettings.true_instant_share,
          instant_share_default: this.generalSettings.instant_share_default,
          instant_share_future_scheduled: this.generalSettings.instant_share_future_scheduled,
          instant_share_choose_accounts_manually: this.generalSettings.instant_share_choose_accounts_manually,
          housekeeping: this.generalSettings.housekeeping,
        };

				this.$store.dispatch('fetchAJAXPromise', {
					req: 'save_general_settings',
					updateState: false,
					data: savedSettings
				}).then(response => {
					this.is_loading = false;
					this.$log.info('Successfully saved general settings.');

          const ignoredKeys = [
            'available_post_types',
            'available_taxonomies',
            'selected_posts',
            'exclude_taxonomies',
            'selected_taxonomies',
          ];

          const trackingPayload = Object.entries(savedSettings)
            .map(([key, value]) => {
              if( 'selected_post_types' === key ) {
                value = value.map( postType => postType.value ).join( ',' );
              }
              
              return [key, value];
            })
            .filter(
              ([key, value]) => (
              !ignoredKeys.includes(key) && 
              ! ( value === undefined || value === null || value === '' ) &&
              ! Array.isArray(value) &&
              typeof value !== 'object'
            ) )
            .reduce((acc, [key, value]) => {
              acc[key] = value;
              return acc;
            }, {});
         
          window?.tiTrk?.with('tweet').add({
            feature: 'general-settings',
            featureComponent: 'saved-settings',
            featureData: trackingPayload,
          });

          window?.tiTrk?.uploadEvents();
				}, error => {

					this.$log.error('Successfully saved general settings.');
					this.is_loading = false;
					Vue.$log.error('Got nothing from server. Prompt user to check internet connection and try again', error)
				})
			}
		}
	}
</script>

<style scoped>
	#rop_core .panel-body .text-gray {
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

	#rop_core .input-group .input-group-addon {
		padding: 3px 5px;
	}

	@media ( max-width: 600px ) {
		#rop_core .panel-body .text-gray {
			margin-bottom: 10px;
		}

		#rop_core .text-right {
			text-align: left;
		}
	}

	.rop-post-type-badge{
		text-align: center;

	}
</style>
