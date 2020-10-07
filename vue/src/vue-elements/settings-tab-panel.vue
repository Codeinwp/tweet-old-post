<template>
    <div class="tab-view">
        <div class="panel-body">
            <div class="container" :class="'rop-tab-state-'+is_loading">
                <div class="columns py-2" v-if="this.apply_exclude_limit_cron" >
                    <div class="column col-6 col-sm-12 vertical-align rop-control">
                        <b>{{labels.cron_type_label}}</b>
                        <p class="text-gray"><span v-html="labels.cron_type_label_desc"></span></p>
                    </div>
                    <div class="column col-6 col-sm-12 vertical-align text-left rop-control">
                        <div class="form-group">
                            <!-- @category New Cron System -->
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
                        </div>
                    </div>
                </div>
                <span class="divider" v-if="this.apply_exclude_limit_cron && ! isBiz" ></span>

                <div class="columns py-2" v-if="! isBiz">
                    <div class="column col-6 col-sm-12 vertical-align">
                        <b>{{labels.min_interval_title}}</b>
                        <p class="text-gray">{{labels.min_interval_desc}}</p>
                    </div>
                    <div class="column col-6 col-sm-12 vertical-align">
                        <counter-input id="default_interval" :min-val="generalSettings.min_interval" :step-val="generalSettings.step_interval"
                                       :value.sync="generalSettings.default_interval"></counter-input>
                    </div>
                </div>
                <span class="divider"></span>
                <div class="columns py-2">
                    <div class="column col-6 col-sm-12 vertical-align">
                        <b>{{labels.min_days_title}}</b>
                        <p class="text-gray">{{labels.min_days_desc}}</p>
                    </div>
                    <div class="column col-6 col-sm-12 vertical-align">
                        <counter-Input id="min_post_age" :max-val="365"
                                       :value.sync="generalSettings.minimum_post_age"></counter-Input>
                    </div>
                </div>
                <!-- Max Post Age -->
                <div class="columns py-2">
                    <div class="column col-6 col-sm-12 vertical-align">
                        <b>{{labels.max_days_title}}</b>
                        <p class="text-gray">{{labels.max_days_desc}}</p>
                    </div>
                    <div class="column col-6 col-sm-12 vertical-align">
                        <counter-input id="max_post_age" :max-val="365"
                                       :value.sync="generalSettings.maximum_post_age"></counter-input>
                    </div>
                </div>

                <span class="divider"></span>

                <div class="columns py-2">
                    <div class="column col-6 col-sm-12 vertical-align">
                        <b>{{labels.no_posts_title}}</b>
                        <p class="text-gray">{{labels.no_posts_desc}}</p>
                    </div>
                    <div class="column col-6 col-sm-12 vertical-align">
                        <counter-input id="no_of_posts" :value.sync="generalSettings.number_of_posts"></counter-input>
                    </div>
                </div>
                <span class="divider"></span>

                <!-- Share more than once -->
                <div class="columns py-2">
                    <div class="column col-6 col-sm-12 vertical-align">
                        <b>{{labels.share_once_title}}</b>
                        <p class="text-gray">{{labels.share_once_desc}}</p>
                    </div>
                    <div class="column col-6 col-sm-12 vertical-align text-left">
                        <div class="form-group">
                            <label class="form-checkbox" id="share_more_than_once">
                                <input type="checkbox" v-model="generalSettings.more_than_once"/>
                                <i class="form-icon"></i> {{labels.share_once_yes}}
                            </label>
                        </div>
                    </div>
                </div>
                <span class="divider"></span>
                <div class="columns py-2" :class="'rop-control-container-'+isPro">
                    <div class="column col-6 col-sm-12 vertical-align rop-control">
                        <b>{{labels.post_types_title}}</b>
                        <p class="text-gray"><span v-html="labels.post_types_desc"></span></p>
                    </div>
                    <div class="column col-6 col-sm-12 vertical-align text-left rop-control">
                        <multiple-select id="rop_post_types" :options="postTypes" :disabled="isPro"
                                         :selected="generalSettings.selected_post_types"
                                         :changed-selection="updatedPostTypes"></multiple-select>

                        <p class="text-primary rop-post-type-badge" v-if="checkMediaPostType " v-html="labels.post_types_attachament_info"></p>
                    </div>
                </div>

                <div class="columns " v-if="!isPro">
                    <div class="column text-center">
                        <p class="upsell"><i class="fa fa-lock"></i> {{labels.post_types_upsell}}</p>
                    </div>
                </div>

				<span class="divider" v-if="!isPro"></span>

                <!-- Taxonomies -->
				<div class="columns py-2" v-if="!isPro">
                    <div class="column col-6 col-sm-12 vertical-align">
                        <b>{{labels.taxonomies_title}}</b>
                        <p class="text-gray"><span v-html="labels.taxonomies_desc"></span></p>
                    </div>
                    <div id="rop_taxonomies" class="column col-6 col-sm-12 vertical-align text-left">
                        <div class="input-group">
                            <multiple-select :options="taxonomies"
                                             :selected="generalSettings.selected_taxonomies"
                                             :changed-selection="updatedTaxonomies"
                                             :is_pro_version="isPro" :apply_limit="isTaxLimit" v-on:display-limiter-notice="displayProMessage"></multiple-select>
                            <span class="input-group-addon vertical-align">
								<label class="form-checkbox">
									<input type="checkbox" v-model="generalSettings.exclude_taxonomies"/>
									<i class="form-icon"></i>{{labels.taxonomies_exclude}}
								</label>
							</span>
                        </div>
                        <p class="text-primary rop-post-type-badge" v-if="is_taxonomy_message" v-html="labels.post_types_taxonomy_limit"></p>
                    </div>
                </div>

                <span class="divider"></span>

                <!-- Google Analytics -->
                <div class="columns py-2">
                    <div class="column col-6 col-sm-12 vertical-align">
                        <b>{{labels.ga_title}}</b>
                        <p class="text-gray">{{labels.ga_desc}}</p>
                    </div>
                    <div class="column col-6 col-sm-12 vertical-align text-left">
                        <div class="form-group">
                            <label class="form-checkbox">
                                <input type="checkbox" v-model="generalSettings.ga_tracking"/>
                                <i class="form-icon"></i>{{labels.ga_yes}}
                            </label>
                        </div>
                    </div>
                </div>

                <span class="divider"></span>

                <div class="columns py-2">
                    <div class="column col-6 col-sm-12 vertical-align rop-control">
                        <b>{{labels.instant_share_title}}</b>
                        <p class="text-gray"><span v-html="labels.instant_share_desc"></span></p>
                    </div>
                    <div class="column col-6 col-sm-12 vertical-align text-left rop-control">
                        <div class="form-group">
                            <label id="rop_instant_share" class="form-checkbox">
                                <input type="checkbox" v-model="generalSettings.instant_share"/>
                                <i class="form-icon"></i>{{labels.instant_share_yes}}
                            </label>
                        </div>
                    </div>
                </div>

                <span class="divider"></span>

                <div class="columns py-2" v-if="isInstantShare">
                    <div class="column col-6 col-sm-12 vertical-align rop-control">
                        <b>{{labels.true_instant_share_title}}</b>
                        <p class="text-gray"><span v-html="labels.true_instant_share_desc"></span></p>
                    </div>
                    <div class="column col-6 col-sm-12 vertical-align text-left rop-control">
                        <div class="form-group">
                            <label class="form-checkbox">
                                <input type="checkbox" v-model="generalSettings.true_instant_share"/>
                                <i class="form-icon"></i>{{labels.true_instant_share_yes}}
                            </label>
                        </div>
                    </div>
                </div>

                <span class="divider" v-if="isInstantShare"></span>

                <div class="columns py-2" v-if="isInstantShare">
                  <div class="column col-6 col-sm-12 vertical-align rop-control">
                    <b>{{labels.instant_share_default_title}}</b>
                    <p class="text-gray">{{labels.instant_share_default_desc}}</p>
                  </div>
                  <div class="column col-6 col-sm-12 vertical-align text-left rop-control">
                    <div class="form-group">
                      <label class="form-checkbox">
                        <input type="checkbox" v-model="generalSettings.instant_share_default"/>
                        <i class="form-icon"></i>{{labels.instant_share_default_yes}}
                      </label>
                    </div>
                  </div>
                </div>

                <span class="divider" v-if="isInstantShare"></span>

                <div class="columns py-2" v-if="isInstantShare" :class="'rop-control-container-'+isPro">
                    <div class="column col-6 col-sm-12 vertical-align rop-control">
                        <b>{{labels.instant_share_future_scheduled_title}}</b>
                        <p class="text-gray"><span v-html="labels.instant_share_future_scheduled_desc"></span></p>
                    </div>
                    <div class="column col-6 col-sm-12 vertical-align text-left rop-control">
                        <div class="form-group">
                            <label class="form-checkbox">
                                <input type="checkbox" v-model="generalSettings.instant_share_future_scheduled"/>
                                <i class="form-icon"></i>{{labels.instant_share_future_scheduled_yes}}
                            </label>
                        </div>
                    </div>
                </div>
                <!-- Upsell -->
                <div class="columns " v-if="!isPro && isInstantShare">
                    <div class="column text-center">
                        <p class="upsell"><i class="fa fa-lock"></i> {{labels.instant_share_future_scheduled_upsell}}</p>
                    </div>
                </div>
                <span class="divider" v-if="isInstantShare"></span>

                <div class="columns py-2" :class="'rop-control-container-'+isPro">
                    <div class="column col-6 col-sm-12 vertical-align rop-control">
                        <b>{{labels.custom_share_title}}</b>
                        <p class="text-gray"><span v-html="labels.custom_share_desc"></span></p>
                    </div>
                    <div class="column col-6 col-sm-12 vertical-align text-left rop-control">
                        <div class="form-group">
                            <label id="rop_custom_share_msg" class="form-checkbox">
                                <input type="checkbox" :disabled="!isPro" v-model="generalSettings.custom_messages"/>
                                <i class="form-icon"></i>{{labels.custom_share_yes}}
                            </label>
                        </div>
                    </div>
                </div>
                <span class="divider"></span>


                <div class="columns py-2" :class="'rop-control-container-'+isPro" v-if="isCustomMsgs">
                    <div class="column col-6 col-sm-12 vertical-align rop-control">
                        <b>{{labels.custom_share_order_title}}</b>
                        <p class="text-gray">{{labels.custom_share_order_desc}}</p>
                    </div>
                    <div class="column col-6 col-sm-12 vertical-align text-left rop-control">
                        <div class="form-group">
                            <label id="rop_custom_share_msg" class="form-checkbox">
                                <input type="checkbox" :disabled="!isPro" v-model="generalSettings.custom_messages_share_order"/>
                                <i class="form-icon"></i>{{labels.custom_share_order_yes}}
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Upsell -->
                <div class="columns " v-if="!isPro">
                    <div class="column text-center">
                        <p class="upsell"><i class="fa fa-lock"></i> {{labels.custom_share_upsell}}</p>
                    </div>
                </div>
                <span class="divider" v-if="isCustomMsgs"></span>

                <div class="columns py-2">
                    <div class="column col-6 col-sm-12 vertical-align rop-control">
                        <b>{{labels.housekeeping}}</b>
                        <p class="text-gray">{{labels.housekeeping_desc}}</p>
                    </div>
                    <div class="column col-6 col-sm-12 vertical-align text-left rop-control">
                        <div class="form-group">
                            <label class="form-checkbox">
                                <input type="checkbox" v-model="generalSettings.housekeeping"/>
                                <i class="form-icon"></i>{{labels.housekeeping_yes}}
                            </label>
                        </div>
                    </div>
                </div>
                <span class="divider"></span>

            </div>
        </div>
        <div class="panel-footer text-right">
            <button class="btn btn-primary" @click="saveGeneralSettings()"><i class="fa fa-check"
                                                                              v-if="!this.is_loading"></i> <i
                    class="fa fa-spinner fa-spin" v-else></i> {{labels.save}}
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

    module.exports = {
        name: 'settings-view',
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
            isTaxLimit: function () {
                if (ropApiSettings.tax_apply_limit > 0) {
                    return true;
                }
                return false;
            },
            isBiz: function () {
                return (this.$store.state.licence > 1);
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
				this.$store.dispatch('fetchAJAXPromise', {
					req: 'save_general_settings',
					updateState: false,
					data: {
						available_taxonomies: this.generalSettings.available_taxonomies,
						default_interval: this.generalSettings.default_interval,
						minimum_post_age: this.generalSettings.minimum_post_age,
						maximum_post_age: this.generalSettings.maximum_post_age,
						number_of_posts: this.generalSettings.number_of_posts,
						more_than_once: this.generalSettings.more_than_once,
						selected_post_types: postTypesSelected,
						selected_taxonomies: taxonomiesSelected,
						exclude_taxonomies: excludeTaxonomies,
						ga_tracking: this.generalSettings.ga_tracking,
						custom_messages: this.generalSettings.custom_messages,
						custom_messages_share_order: this.generalSettings.custom_messages_share_order,
						instant_share: this.generalSettings.instant_share,
						true_instant_share: this.generalSettings.true_instant_share,
						instant_share_default: this.generalSettings.instant_share_default,
						instant_share_future_scheduled: this.generalSettings.instant_share_future_scheduled,
						housekeeping: this.generalSettings.housekeeping,
					}
				}).then(response => {
					this.is_loading = false;
					this.$log.info('Successfully saved general settings.');
				}, error => {

					this.$log.error('Successfully saved general settings.');
					this.is_loading = false;
					Vue.$log.error('Got nothing from server. Prompt user to check internet connection and try again', error)
				})
			}
		},
		components: {
			counterInput,
			MultipleSelect
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
