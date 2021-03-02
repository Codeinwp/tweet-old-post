<template>
    <div>
        <div class="columns py-2" v-if="wpml_active_status">
            <div class="column col-6 col-sm-12 vertical-align">
                <b>{{labels.language_title}}</b>
                <p class="text-gray">{{labels.language_title_desc}}</p>
            </div>
            <div class="column col-6 col-sm-12 vertical-align">
                <div class="form-group">
                    <select id="wpml-language-selector" class="form-select" v-model="post_format.wpml_language" :disabled="!isPro" v-on:change="refresh_language_taxonomies">
                        <option v-for="(lang, index) in wpml_languages" :value="lang.code" v-bind:selected="index == 0 || lang.code == post_format.wpml_language ? true : false">{{lang.label}}</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="columns " v-if="!isPro && wpml_active_status">
            <div class="column text-center">
                <p class="upsell"><i class="fa fa-info-circle"></i> <span v-html="labels.full_wpml_support_upsell"></span></p>
            </div>
        </div>
        <span class="divider"></span>

        <div class="columns py-2">
            <div class="column col-6 col-sm-12 vertical-align">
                <b>{{labels.post_content_title}}</b>
                <p class="text-gray">{{labels.post_content_desc}}</p>
            </div>
            <div class="column col-6 col-sm-12 vertical-align">
                <div class="form-group">
                    <select class="form-select" v-model="post_format.post_content">
                        <option value="post_title">{{labels.post_content_option_title}}</option>
                        <option value="post_content">{{labels.post_content_option_content}}</option>
                        <option value="post_title_content">{{labels.post_content_option_title_content}}</option>
                        <option value="custom_field">{{labels.post_content_option_custom_field}}</option>
                    </select>
                </div>
            </div>
        </div>

                <div class="columns py-2" v-if="post_format.post_content === 'custom_field'">
                    <div class="column col-6 col-sm-12 vertical-align">
                        <b>{{labels.custom_meta_title}}</b>
                        <p class="text-gray">{{labels.custom_meta_desc}}</p>
                    </div>
                    <div class="column col-6 col-sm-12 vertical-align">
                        <div class="form-group">
                            <input class="form-input" type="text" v-model="post_format.custom_meta_field"
                                   value="" placeholder=""/>
                        </div>
                    </div>
                </div>

        <span class="divider"></span>

        <div class="columns py-2">
            <div class="column col-6 col-sm-12 vertical-align">
                <b>{{labels.max_char_title}}</b>
                <p class="text-gray">{{labels.max_char_desc}}</p>
            </div>
            <div class="column col-6 col-sm-12 vertical-align">
                <div class="form-group">
                    <input class="form-input" type="number" v-model="post_format.maximum_length"
                           value="" placeholder="" />
                </div>
            </div>
        </div>
        <span class="divider"></span>

        <div class="columns py-2">
            <div class="column col-6 col-sm-12 vertical-align">
                <b>{{labels.add_char_title}}</b>
                <p class="text-gray"><span v-html="labels.add_char_desc"></span></p>
            </div>
            <div class="column col-6 col-sm-12 vertical-align">
                <div class="form-group">
					<textarea class="form-input" v-model="post_format.custom_text"
                              v-bind:placeholder="labels.add_char_placeholder">{{post_format.custom_text}}</textarea>
                </div>
            </div>
        </div>

        <div class="columns py-2">
            <div class="column col-6 col-sm-12 vertical-align">
                <p class="text-gray">{{labels.add_pos_title}}</p>
            </div>
            <div class="column col-6 col-sm-12 vertical-align">
                <div class="form-group">
                    <select class="form-select" v-model="post_format.custom_text_pos">
                        <option value="beginning">{{labels.add_pos_option_start}}</option>
                        <option value="end">{{labels.add_pos_option_end}}</option>
                    </select>
                </div>
            </div>
        </div>
        <span class="divider"></span>
        <div class="columns py-2">
            <div class="column col-6 col-sm-12 vertical-align">
                <b>{{labels.add_link_title}}</b>
                <p class="text-gray">{{labels.add_link_desc}}</p>
            </div>
            <div class="column col-6 col-sm-12 vertical-align">
                <div class="input-group">
                    <label class="form-checkbox">
                        <input type="checkbox" v-model="post_format.include_link"/>
                        <i class="form-icon"></i> {{labels.add_link_yes}}
                    </label>
                </div>
            </div>
        </div>
        <span class="divider"></span>
        <div class="columns py-2">
            <div class="column col-6 col-sm-12 vertical-align">
                <b>{{labels.meta_link_title}}</b>
                <p class="text-gray">{{labels.meta_link_desc}}</p>
            </div>
            <div class="column col-6 col-sm-12 vertical-align">
                <div class="input-group">
                    <label class="form-checkbox">
                        <input type="checkbox" v-model="post_format.url_from_meta"/>
                        <i class="form-icon"></i> {{labels.meta_link_yes}}
                    </label>
                </div>
            </div>
        </div>

        <!-- Custom Field -->
        <div class="columns py-2" v-if="post_format.url_from_meta">
            <div class="column col-6 col-sm-12 vertical-align">
                <b>{{labels.meta_link_name_title}}</b>
                <p class="text-gray">{{labels.meta_link_name_desc}}</p>
            </div>
            <div class="column col-6 col-sm-12 vertical-align">
                <div class="form-group">
                    <input class="form-input" type="text" v-model="post_format.url_meta_key" value=""
                           placeholder=""/>
                </div>
            </div>
        </div>
        <span class="divider"></span>
        <div class="columns py-2" :class="'rop-control-container-'+(isPro && (license_price_id !== 7))">
            <div class="column col-6 col-sm-12 vertical-align rop-control">
                <b>{{labels_settings.taxonomies_title}}</b>
                <p class="text-gray"><span v-html="labels_settings.taxonomies_desc"></span></p>
            </div>
            <div class="column col-6 col-sm-12 vertical-align">
                <div class="input-group">
                    <multiple-select :disabled="!!isPro && (license_price_id !== 7)" :options="taxonomy" :selected="taxonomy_filter" :name="post_format.taxonomy_filter" :changed-selection="updated_tax_filter" :key="this.account_id"></multiple-select>
                    <span class="input-group-addon vertical-align">
                        <label class="form-checkbox">
						    <input :disabled="!isPro || (license_price_id === 7)" type="checkbox" v-model="post_format.exclude_taxonomies"/>
							<i class="form-icon"></i>{{labels_settings.taxonomies_exclude}}
						</label>
					</span>
                </div>
            </div>
        </div>
        <div class="columns " v-if="!isPro || (license_price_id === 7)">
            <div class="column text-center">
                <p class="upsell"><i class="fa fa-info-circle"></i> {{labels.taxonomy_based_sharing_upsell}}</p>
            </div>
        </div>
        <span class="divider"></span>
        <div class="columns py-2">
            <div class="column col-6 col-sm-12 vertical-align">
                <b>{{labels.use_shortner_title}}</b>
                <p class="text-gray">{{labels.use_shortner_desc}}</p>
            </div>
            <div class="column col-6 col-sm-12 vertical-align">
                <div class="input-group">
                    <label class="form-checkbox">
                        <input type="checkbox" v-model="post_format.short_url"/>
                        <i class="form-icon"></i> {{labels.use_shortner_yes}}
                    </label>
                </div>
            </div>
        </div>
        <div class="columns py-2" v-if="post_format.short_url">
            <div class="column col-6 col-sm-12 vertical-align">
                <b>{{labels.shortner_title}}</b>
                <p class="text-gray">{{labels.shortner_desc}}</p>
            </div>
            <div class="column col-6 col-sm-12 vertical-align">
                <div class="form-group">
                    <select class="form-select" v-model="post_format.short_url_service">
                        <option v-for="shortener in shorteners" :value="shortener.id" :disabled="shortener.active !== true" :selected="shortener.name == post_format.short_url_service">{{ shortener.name }}{{ !shortener.active ? labels_generic.only_pro_suffix : ''}}</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="columns py-2" v-if="post_format.short_url" v-for="( credential, key_name ) in post_format.shortner_credentials">
            <div class="column col-6 col-sm-12 vertical-align">
                <b>{{ key_name | capitalize }}</b>
                <p class="text-gray">{{labels.shortner_field_desc_start}} "{{key_name}}"
                    {{labels.shortner_field_desc_end}}
                    <strong>{{post_format.short_url_service}}</strong> {{labels.shortner_api_field}}.</p>
            </div>
            <div class="column col-6 col-sm-12 vertical-align">
                <div class="form-group">
                    <input class="form-input" type="text" v-model="post_format.shortner_credentials[key_name]">
                </div>
            </div>
        </div>

        <div class="columns py-2">
            <div class="column col-6 col-sm-12 vertical-align">
                <b>{{labels.hashtags_title}}</b>
                <p class="text-gray">{{labels.hashtags_desc}}</p>
            </div>
            <div class="column col-6 col-sm-12 vertical-align">
                <div class="form-group">
                    <select class="form-select" v-model="post_format.hashtags">
                        <option value="no-hashtags">{{labels.hashtags_option_no}}</option>
                        <option value="common-hashtags">{{labels.hashtags_option_common}}</option>
                        <option value="categories-hashtags">{{labels.hashtags_option_cats}}</option>
                        <option value="tags-hashtags">{{labels.hashtags_option_tags}}</option>
                        <option value="custom-hashtags">{{labels.hashtags_option_field}}</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="columns py-2" v-if="post_format.hashtags === 'common-hashtags'">
            <div class="column col-6 col-sm-12 vertical-align">
                <b>{{labels.hastags_common_title}}</b>
                <p class="text-gray">{{labels.hastags_common_desc}} ",".</p>
            </div>
            <div class="column col-6 col-sm-12 vertical-align">
                <div class="form-group">
                    <input class="form-input" type="text" v-model="post_format.hashtags_common" value=""
                           placeholder=""/>
                </div>
            </div>
        </div>

        <div class="columns py-2" v-if="post_format.hashtags === 'custom-hashtags'">
            <div class="column col-6 col-sm-12 vertical-align">
                <b>{{labels.hastags_field_title}}</b>
                <p class="text-gray">{{labels.hastags_field_desc}}</p>
            </div>
            <div class="column col-6 col-sm-12 vertical-align">
                <div class="form-group">
                    <input class="form-input" type="text" v-model="post_format.hashtags_custom" value=""
                           placeholder=""/>
                </div>
            </div>
        </div>

        <div class="columns py-2" v-if="post_format.hashtags !== 'no-hashtags'">
            <div class="column col-6 col-sm-12 vertical-align">
                <b>{{labels.hashtags_length_title}}</b>
                <p class="text-gray">{{labels.hashtags_length_desc}}</p>
            </div>
            <div class="column col-6 col-sm-12 vertical-align">
                <div class="form-group">
                    <input class="form-input" type="number" v-model="post_format.hashtags_length"
                           value="" placeholder=""/>
                </div>
            </div>
        </div>

        <span class="divider"></span>

        <div class="columns py-2" :class="'rop-control-container-'+isPro">
            <div class="column col-6 col-sm-12 vertical-align rop-control">
                <b>{{labels.image_title}}</b>
                <p class="text-gray"><span v-html="labels.image_desc"></span></p>
            </div>
            <div class="column col-6 col-sm-12 vertical-align">
                <div class="input-group">
                    <label class="form-checkbox">
                        <input type="checkbox" v-model="post_format.image"
                               :disabled="!isPro"/>
                        <i class="form-icon"></i> {{labels.image_yes}}
                    </label>
                </div>
            </div>
        </div>

        <div class="columns " v-if="!isPro">
            <div class="column text-center">
                <p class="upsell"><i class="fa fa-info-circle"></i> {{labels.image_upsell}}</p>
            </div>
        </div>
        <span class="divider"></span>
        <!-- Google Analytics -->
        <div class="columns py-2" :class="'rop-control-container-'+isPro">
            <div class="column col-6 col-sm-12 vertical-align rop-control">
                <b>{{labels.utm_campaign_medium}}</b>
                <p class="text-gray">{{labels.utm_campaign_medium_desc}}</p>
            </div>
            <div class="column col-6 col-sm-12 vertical-align text-left rop-control">
                <div class="form-group">
                    <input type="text" :disabled="!isPro" class="form-input" v-model="post_format.utm_campaign_medium" placeholder="social"/>
                </div>
            </div>
        </div>

        <div class="columns py-2" :class="'rop-control-container-'+isPro">
            <div class="column col-6 col-sm-12 vertical-align rop-control">
                <b>{{labels.utm_campaign_name}}</b>
                <p class="text-gray">{{labels.utm_campaign_name_desc}}</p>
            </div>
            <div class="column col-6 col-sm-12 vertical-align text-left rop-control">
                <div class="form-group">
                    <input type="text" :disabled="!isPro" class="form-input" v-model="post_format.utm_campaign_name" placeholder="ReviveOldPost"/>
                </div>
            </div>
        </div>
        <div class="columns " v-if="!isPro">
            <div class="column text-center">
                <p class="upsell"><i class="fa fa-info-circle"></i> {{labels.custom_utm_upsell}}</p>
            </div>
        </div>
        <span class="divider"></span>
    </div>
</template>

<script>
    import MultipleSelect from './reusables/multiple-select.vue'

    module.exports = {
        name: "post-format",
        props: ['account_id', 'license'],
        data: function () {
            return {
                labels: this.$store.state.labels.post_format,
                labels_settings: this.$store.state.labels.settings,
                labels_generic: this.$store.state.labels.generic,
                upsell_link: ropApiSettings.upsell_link,
                wpml_active_status: ropApiSettings.rop_get_wpml_active_status,
                wpml_languages: ropApiSettings.rop_get_wpml_languages,
                selected_tax_filter: [],
                // selected_language: this.$store.state.activePostFormat[this.account_id] ? this.$store.state.activePostFormat[this.account_id].wpml_language : [],
                // post_types: this.$store.state.generalSettings.available_post_types,
            }
        },
        created: function () {
            this.get_taxonomy_list();
        },
        updated: function() {
            this.$nextTick(function () {
                if(!this.$store.state.dom_updated){
                    if(this.wpml_active_status){
                    this.refresh_language_taxonomies();
                    }
                }
            });
        },
       methods:{
            refresh_language_taxonomies: function(e){
                const lang = e && e.target ? e.target.options[e.target.options.selectedIndex].value : document.querySelector('#wpml-language-selector').value;
                if(e && e.target){
                    // clear selected taxonomies on language change
                    this.post_format.taxonomy_filter = [];
                }
                if(lang !== ''){
                    this.$store.dispatch('fetchAJAXPromise', {req: 'get_taxonomies', data: {post_types: this.postTypes, language_code: lang}});
                }
                this.$store.state.dom_updated = true;
            },
            get_taxonomy_list(){
                if (this.$store.state.generalSettings.length === 0) {
                    this.is_loading = true;
                    this.$log.info('Fetching general settings.');
                    this.$store.dispatch('fetchAJAXPromise', {req: 'get_general_settings'}).then(response => {
                        this.is_loading = false;
                        this.$log.debug('Successfully fetched.')
                    }, error => {
                        this.is_loading = false;
                        this.$log.error('Can not fetch the general settings.')
                    })
                }
            },
            updated_tax_filter(data){
                let taxonomies = []
               for (let index in data) {
                    taxonomies.push(data[index].value)
                }

                let taxonomy_objects = [];
                for(let tax  of this.taxonomy ){
                    for (let tax_id of taxonomies) {
                        tax_id = parseInt(tax_id);
                        let id = parseInt(tax.value);
                        if(id === tax_id){
                            taxonomy_objects.push(tax);
                        }
                    }

                }
                this.post_format.taxonomy_filter = taxonomy_objects;
            }

        },
        computed: {
            postTypes: function () {
                return this.$store.state.generalSettings.available_post_types;
            },
            post_format: function () {
                return this.$store.state.activePostFormat[this.account_id] ? this.$store.state.activePostFormat[this.account_id] : [];
            },
            isPro: function () {
                return (this.license > 0);
            },
            license_price_id: function () {
                return this.license;
            },
            short_url_service: function () {
                let postFormat = this.$store.state.activePostFormat[this.account_id] ? this.$store.state.activePostFormat[this.account_id] : [];
                return (postFormat.short_url_service) ? postFormat.short_url_service : '';
            },
            taxonomy_filter: function(){
                let postFormat = this.$store.state.activePostFormat[this.account_id] ? this.$store.state.activePostFormat[this.account_id] : [];
                if(postFormat.taxonomy_filter){
                    let index = 0;
                    for (let option of postFormat.taxonomy_filter) {
                        postFormat.taxonomy_filter[index].selected = true;
                        index++
                    }

                }

                return (postFormat.taxonomy_filter) ? postFormat.taxonomy_filter : [];
            },
            taxonomy: function () {
                return this.$store.state.generalSettings.available_taxonomies
            },
            shorteners: function () {
                return this.$store.state.generalSettings.available_shorteners;
            }
        },
        watch: {
            short_url_service: function () {
                this.$store.dispatch('fetchAJAXPromise', {
                    req: 'get_shortner_credentials',
                    data: {short_url_service: this.short_url_service}
                }).then(response => {
                    this.post_format.shortner_credentials = response
                }, error => {
                    Vue.$log.error('Got nothing from server. Prompt user to check internet connection and try again', error)
                })
            }
        },

        filters: {
            capitalize: function (value) {
                if (!value) return '';
                value = value.toString().split('_');
                var name = '';
                for (var x = 0; x < value.length; x++) {
                    name += value[x].charAt(0).toUpperCase() + value[x].slice(1) + ' ';
                }
                return name;
            }
        },
        components: {
            MultipleSelect
        }
    }
</script>
<style scoped>
    #rop_core .panel-body .text-gray {
        margin: 0;
        line-height: normal;
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
</style>
