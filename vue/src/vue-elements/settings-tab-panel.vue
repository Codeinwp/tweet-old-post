<template>
    <div class="tab-view">
        <div class="panel-body">
            <h3>General Settings</h3>
            <div class="container" :class="'rop-tab-state-'+is_loading">
                <div class="columns">
                    <div class="column col-sm-12 col-md-12 col-lg-12">
                        <div class="columns">
                            <div class="column col-sm-12 col-md-6 col-xl-6 col-4 text-right">
                                <b>Minimum interval between shares</b><br/>
                                <i>Minimum time between shares (hour/hours), 0.4 can be used.</i>
                            </div>
                            <div class="column col-sm-12 col-md-6 col-xl-6 col-4 text-left">
                                <counter-input id="default_interval" :value.sync="generalSettings.default_interval"/>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="columns">
                    <div class="column col-sm-12 col-md-12 col-lg-6">
                        <div class="columns">
                            <div class="column col-sm-12 col-md-6 col-xl-6 col-8 text-right">
                                <b>Minimum post age</b><br/>
                                <i>Minimum age of posts available for sharing, in days.</i>
                            </div>
                            <div class="column col-sm-12 col-md-6 col-xl-6 col-4 text-left">
                                <counter-input id="min_post_age" :maxVal="365"
                                               :value.sync="generalSettings.minimum_post_age"/>
                            </div>
                        </div>
                    </div>
                    <div class="column col-sm-12 col-md-12 col-lg-6">
                        <div class="columns">
                            <div class="column col-sm-12 col-md-6 col-xl-6 col-4 text-right">
                                <counter-input id="max_post_age" :maxVal="365"
                                               :value.sync="generalSettings.maximum_post_age"/>
                            </div>
                            <div class="column col-sm-12 col-md-6 col-xl-6 col-8 text-left">
                                <b>Maximum post age</b><br/>
                                <i>Maximum age of posts available for sharing, in days.</i>
                            </div>
                        </div>
                    </div>
                </div>
                <hr/>
                <div class="columns">
                    <div class="column col-sm-12 col-md-12 col-lg-6">
                        <div class="columns">
                            <div class="column col-sm-12 col-md-6 col-xl-6 col-8 text-right">
                                <b>Number of posts</b><br/>
                                <i>Number of posts to share per. account per. trigger of scheduled job.</i>
                            </div>
                            <div class="column col-sm-12 col-md-6 col-xl-6 col-4 text-left">
                                <counter-input id="no_of_posts" :value.sync="generalSettings.number_of_posts"/>
                            </div>
                        </div>
                    </div>
                    <div class="column col-sm-12 col-md-12 col-lg-6">
                        <div class="columns">
                            <div class="column col-sm-12 col-md-2 col-xl-2 col-1 text-right">
                                <div class="form-group">
                                    <label class="form-checkbox">
                                        <input type="checkbox" v-model="generalSettings.more_than_once"/>
                                        <i class="form-icon"></i> Yes
                                    </label>
                                </div>
                            </div>
                            <div class="column col-sm-12 col-md-10 col-xl-10 col-11 text-left">
                                <b>Share more than once?</b><br/>
                                <i>If there are no more posts to share, we should start re-sharing the one we previously
                                    shared.</i>
                            </div>
                        </div>
                    </div>
                </div>
                <hr/>
                <div class="columns">
                    <div class="column col-sm-12 col-md-12 col-lg-12">
                        <div class="columns">
                            <div class="column col-sm-12 col-md-4 col-xl-3 col-ml-2 col-4 text-right">
                                <b>Post types</b><br/>
                                <i>Post types available to share - what post types are available for share</i>
                            </div>
                            <div class="column col-sm-12 col-md-8 col-xl-9 col-mr-4 col-7 text-left">
                                <multiple-select :options="postTypes" :disabled="isPro"
                                                 :selected="generalSettings.selected_post_types"
                                                 :changedSelection="updatedPostTypes"/>
                            </div>
                        </div>
                    </div>
                </div>
                <hr/>
                <div class="columns">
                    <div class="column col-sm-12 col-md-12 col-lg-12">
                        <div class="columns">
                            <div class="column col-sm-12 col-md-4 col-xl-3 col-ml-2 col-4 text-right">
                                <b>Taxonomies</b><br/>
                                <i>Taxonomies available for the selected post types. Use to include or exclude
                                    posts.</i>
                            </div>
                            <div class="column col-sm-12 col-md-8 col-xl-9 col-mr-4 col-7 text-left">
                                <div class="input-group">
                                    <multiple-select :options="taxonomies"
                                                     :selected="generalSettings.selected_taxonomies"
                                                     :changedSelection="updatedTaxonomies"/>
                                    <span class="input-group-addon">
										<label class="form-checkbox">
											<input type="checkbox" v-model="generalSettings.exclude_taxonomies"
                                                   @change="exludeTaxonomiesChange"/>
											<i class="form-icon"></i> Exclude?
										</label>
									</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <hr/>
                <div class="columns">
                    <div class="column col-sm-12 col-md-12 col-lg-12">
                        <div class="columns">
                            <div class="column col-sm-12 col-md-4 col-xl-3 col-ml-2 col-4 text-right">
                                <b>Posts</b><br/>
                                <i>Posts excluded/included in sharing, filtered based on previous selections.</i>
                            </div>
                            <div class="column col-sm-12 col-md-8 col-xl-9 col-mr-4 col-7 text-left">
                                <div class="input-group">
                                    <multiple-select :searchQuery="searchQuery" @update="searchUpdate"
                                                     :options="postsAvailable" :dontLock="true"
                                                     :selected="generalSettings.selected_posts"
                                                     :changedSelection="updatedPosts"/>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <hr/>
                <div class="columns">
                    <div class="column col-sm-12 col-md-12 col-lg-12">
                        <div class="columns">
                            <div class="column col-sm-12 col-md-4 col-xl-3 col-ml-2 col-4 text-right">
                                <b>Enable Google Analytics Tracking</b><br/>
                                <i>If checked an utm query willbe added to URL's so that you cand better track
                                    trafic.</i>
                            </div>
                            <div class="column col-sm-12 col-md-8 col-xl-9 col-mr-4 col-7 text-left">
                                <div class="form-group">
                                    <label class="form-checkbox">
                                        <input type="checkbox" v-model="generalSettings.ga_tracking"/>
                                        <i class="form-icon"></i> Yes
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="panel-footer">
            <button class="btn btn-primary" @click="saveGeneralSettings()"><i class="fa fa-check"
                                                                              v-if="!this.is_loading"></i> <i
                    class="fa fa-spinner fa-spin" v-else></i> Save
            </button>
        </div>
    </div>
</template>

<script>
    import CounterInput from './reusables/counter-input.vue'
    import MultipleSelect from './reusables/multiple-select.vue'

    module.exports = {
        name: 'settings-view',
        data: function () {
            return {
                searchQuery: '',
                postTimeout: '',
                is_loading: false,
            }
        },
        computed: {
            generalSettings: function () {
                return this.$store.state.generalSettings
            },
            isPro: function () {
                return this.$store.state.has_pro
            },
            postTypes: function () {
                return this.$store.state.generalSettings.available_post_types;
            },
            taxonomies: function () {
                this.requestPostUpdate()
                return this.$store.state.generalSettings.available_taxonomies
            },
            postsAvailable: function () {
                return this.$store.state.generalSettings.available_posts
            }
        },
        mounted: function () {
            this.$log.info('In General Settings state ');
            this.getGeneralSettings();
        },
        methods: {
            getGeneralSettings(){
                if (this.$store.state.generalSettings.length === 0) {
                    this.is_loading = true;
                    this.$log.info('Fetching general settings.');
                    this.$store.dispatch('fetchAJAXPromise', {req: 'get_general_settings'}).then(response => {
                        this.is_loading = false;
                        this.$log.debug('Succesfully fetched.')
                    }, error => {
                        this.is_loading = false;
                        this.$log.error('Can not fetch the general settings.')
                    })
                }
            },
            searchUpdate(newQuery) {
                this.searchQuery = newQuery
                this.requestPostUpdate()
            },
            updatedPostTypes(data) {
                let postTypes = []
                for (let index in data) {
                    postTypes.push(data[index].value)
                }

                this.$store.commit('updateState', {stateData: data, requestName: 'update_selected_post_types'})
                this.$store.dispatch('fetchAJAX', {req: 'get_taxonomies', data: {post_types: postTypes}})
                this.requestPostUpdate()
            },
            updatedTaxonomies(data) {
                let taxonomies = []
                for (let index in data) {
                    taxonomies.push(data[index].value)
                }
                this.$store.commit('updateState', {stateData: data, requestName: 'update_selected_taxonomies'})
                this.requestPostUpdate()
            },
            updatedPosts(data) {
                this.$store.commit('updateState', {stateData: data, requestName: 'update_selected_posts'})
            },
            exludeTaxonomiesChange() {
                this.requestPostUpdate()
            },
            doPostUpdate() {
                let postTypesSelected = this.$store.state.generalSettings.selected_post_types
                let taxonomiesSelected = this.$store.state.generalSettings.selected_taxonomies

                this.$store.dispatch('fetchAJAX', {
                    req: 'get_posts',
                    data: {
                        post_types: postTypesSelected,
                        search_query: this.searchQuery,
                        taxonomies: taxonomiesSelected,
                        exclude: this.generalSettings.exclude_taxonomies,
                        selected: this.generalSettings.selected_posts
                    }
                })
            },
            requestPostUpdate() {
                if (this.postTimeout !== '') {
                    clearTimeout(this.postTimeout);
                }
                this.postTimeout = setTimeout(this.doPostUpdate, 500);

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
                        post_types: postTypesSelected,
                        taxonomies: taxonomiesSelected,
                        exclude_taxonomies: excludeTaxonomies,
                        posts: postsSelected,
                        ga_tracking: this.generalSettings.ga_tracking
                    }
                }).then(response => {
                    this.is_loading = false;
                    this.$log.info('Successfully saved general settings.');
                    this.$store.dispatch('fetchAJAX', {req: 'get_queue'})
                }, error => {

                    this.$log.error('Successfully saved general settings.');
                    this.is_loading = false;
                    Vue.$log.error('Got nothing from server. Prompt user to check internet connection and try again', error)
                })
            }
        },
        components: {
            CounterInput,
            MultipleSelect
        }
    }
</script>
<style type="text/css">
    .rop-tab-state-true {
        opacity: 0.2;
    }

    .rop-tab-state-false {
        opacity: 1;
    }
</style>