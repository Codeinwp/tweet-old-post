<template>
	<div class="tab-view">
		<div class="panel-body">
			<div class="container" :class="'rop-tab-state-'+is_loading">
				<div class="columns py-2" v-if="! isPro">
					<div class="column col-6 col-sm-12 vertical-align">
						<b>{{labels.min_interval_title}}</b>
						<p class="text-gray">{{labels.min_interval_desc}}</p>
					</div>
					<div class="column col-6 col-sm-12 vertical-align">
						<counter-input id="default_interval"
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
							<label class="form-checkbox">
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
						<p class="text-gray">{{labels.post_types_desc}}</p>
					</div>
					<div class="column col-6 col-sm-12 vertical-align text-left rop-control">
						<multiple-select :options="postTypes" :disabled="isPro"
						                 :selected="generalSettings.selected_post_types"
						                 :changed-selection="updatedPostTypes"></multiple-select>
					</div>
				</div>
				
				<div class="columns py-2" v-if="!isPro">
					<div class="column text-center">
						<p class="upsell"><i class="fa fa-lock"></i> {{labels.post_types_upsell}}</p>
					</div>
				</div>
				
				<span class="divider"></span>
				
				<!-- Taxonomies -->
				<div class="columns py-2">
					<div class="column col-6 col-sm-12 vertical-align">
						<b>{{labels.taxonomies_title}}</b>
						<p class="text-gray">{{labels.taxonomies_desc}}</p>
					</div>
					<div class="column col-6 col-sm-12 vertical-align text-left">
						<div class="input-group">
							<multiple-select :options="taxonomies"
							                 :selected="generalSettings.selected_taxonomies"
							                 :changed-selection="updatedTaxonomies"></multiple-select>
							<span class="input-group-addon vertical-align">
								<label class="form-checkbox">
									<input type="checkbox" v-model="generalSettings.exclude_taxonomies"
									       @change="exludeTaxonomiesChange"/>
									<i class="form-icon"></i>{{labels.taxonomies_exclude}}
								</label>
							</span>
						
						</div>
					
					</div>
				
				</div>
				
				<span class="divider"></span>
				<div class="columns py-2">
					<div class="column col-6 col-sm-12 vertical-align">
						<b>{{labels.posts_title}}</b>
						<p class="text-gray">{{labels.posts_desc}}</p>
					</div>
					<div class="column col-6 col-sm-12 vertical-align text-left">
						<div class="input-group">
							<multiple-select :searchQuery="searchQuery" @update="searchUpdate"
							                 :options="postsAvailable" :dont-lock="true"
							                 :selected="generalSettings.selected_posts"
							                 :changed-selection="updatedPosts"></multiple-select>
						
						</div>
					</div>
				</div>
				<span class="divider"></span>
				
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
				
				<div class="columns py-2" :class="'rop-control-container-'+isPro">
					<div class="column col-6 col-sm-12 vertical-align rop-control">
						<b>{{labels.custom_share_title}}</b>
						<p class="text-gray">{{labels.custom_share_desc}}</p>
					</div>
					<div class="column col-6 col-sm-12 vertical-align text-left rop-control">
						<div class="form-group">
							<label class="form-checkbox">
								<input type="checkbox" :disabled="!isPro" v-model="generalSettings.custom_messages"/>
								<i class="form-icon"></i>{{labels.custom_share_yes}}
							</label>
						</div>
					</div>
				</div>
				<!-- Upsell -->
				<div class="columns py-2" v-if="!isPro">
					<div class="column text-center">
						<p class="upsell"><i class="fa fa-lock"></i> {{labels.custom_share_upsell}}</p>
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
	import counterInput from './reusables/counter-input.vue'
	import MultipleSelect from './reusables/multiple-select.vue'

	module.exports = {
		name: 'settings-view',
		data: function () {
			return {
				searchQuery: '',
				postTimeout: '',
				labels: this.$store.state.labels.settings,
				upsell_link: ropApiSettings.upsell_link,
				is_loading: false,
			}
		},
		computed: {
			generalSettings: function () {
				return this.$store.state.generalSettings
			},
			isPro: function () {
				return (this.$store.state.licence > 0);
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
			getGeneralSettings() {
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
						ga_tracking: this.generalSettings.ga_tracking,
						custom_messages: this.generalSettings.custom_messages
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
</style>