<template>
  <div
    id="rop_core"
    class="columns "
  >
    <div
      id="rop-sidebar-selector"
      class="column col-3   col-xl-5 col-lg-5 col-md-6 col-sm-6 col-xs-12  pull-right"
    >
      <div
        class="columns py-2"
        :class="'rop-control-container-'+isPro"
      >
        <div class="column col-12 col-sm-12 vertical-align rop-control">
          <b>{{ labels.post_types_title }}</b>
          <p class="text-gray">
            {{ labels.filter_by_post_types_desc }}
          </p>
        </div>
        <div class="column col-12 col-sm-12 vertical-align text-left rop-control">
          <multiple-select
            :options="postTypes"
            :disabled="isPro"
            :selected="generalSettings.selected_post_types"
            :changed-selection="updatedPostTypes"
          />
        </div>
      </div>

      <span class="divider" />
      <div
        v-if="!isPro"
        class="columns py-2"
      >
        <div class="column text-center">
          <p class="upsell">
            <i class="fa fa-lock" /> {{ labels.post_types_upsell }}
          </p>
        </div>
      </div>
      <div class="columns py-2">
        <div class="column col-12 col-sm-12 vertical-align">
          <b>{{ labels.taxonomies_title }}</b>
          <p class="text-gray">
            {{ labels.filter_by_taxonomies_desc }}
          </p>
        </div>
        <div class="column col-12 col-sm-12 vertical-align text-left">
          <div class="input-group">
            <multiple-select
              :options="taxonomies"
              :selected="generalSettings.selected_taxonomies"
              :changed-selection="updatedTaxonomies"
            />
          </div>
        </div>
      </div>
      <upsell-sidebar />
    </div>
    <div
      id="rop-posts-listing"
      class="column col-9  col-xl-7 col-lg-7 col-md-6 col-sm-6 col-xs-12 col- pull-left"
    >
      <div class="columns py-2">
        <div class="column col-12 col-sm-12 vertical-align">
          <div class="input-group has-icon-right">
            <input
              v-model="searchQuery"
              class="form-input"
              type="text"
              :placeholder="labels.search_posts_to_exclude"
            >
            <i
              v-if="is_loading"
              class="form-icon loading"
            />
          </div>
        </div>
        <div class="column col-12 col-sm-12 mt-2">
          <div
            v-if="searchQuery != '' && ! show_excluded"
            class="form-group pull-right"
          >
            <button
              class="btn btn-primary"
              @click="excludePostsBatch"
            >
              <i
                v-if="!is_loading"
                class="fa fa-save "
              />
              <i
                v-else
                class="fa fa-spinner fa-spin"
              />
              {{ labels.exclude_matching }} "{{ searchQuery }}"
            </button>
          </div>
          <div class="form-group pull-right ">
            <label class="form-switch">
              <input
                v-model="show_excluded"
                type="checkbox"
                @change="excludePostsChange"
              >
              <i class="form-icon" />{{ labels.search_posts_show_excluded }}
            </label>
          </div>

          <p
            v-if="apply_limit_exclude"
            class="text-primary rop-post-type-badge"
            v-html="labels.post_types_exclude_limit"
          />
        </div>
        <div
          v-if="postsAvailable"
          class="column col-12  px-2"
        >
          <div v-if="postsAvailable.length === 0 && !is_loading">
            {{ labels.no_posts_found }}
          </div>
          <div v-else>
            <table
              v-if=" ! is_loading"
              id="rop-posts-table"
              class="table table-striped table-hover"
            >
              <tr
                v-for="(post,index ) in postsAvailable"
                :key="index"
                class="rop-post-item"
              >
                <td :class="'rop-post-' + post.selected">
                  {{ post.name }}
                  <template>
                    <tooltip
                      placement="top-right"
                      mode="hover"
                      :is_show="apply_limit_exclude"
                    >
                      <div slot="outlet">
                        <button
                          class="btn btn-error rop-exclude-post"
                          @click="excludeSinglePost(post.value,post.selected)"
                        >
                          <i
                            v-if="!is_loading_single"
                            class="fa"
                            :class="'fa-' + (post.selected ? 'plus' : 'remove') "
                          />
                          <i
                            v-else
                            class="fa fa-spinner fa-spin"
                          />
                          <span v-html=" ( post.selected ? labels.include_single_post : labels.exclude_single_post) " />
                        </button>
                      </div>
                      <div
                        slot="tooltip"
                        v-html="labels.post_types_exclude_limit_tooltip"
                      />
                    </tooltip>
                  </template>
                </td>
              </tr>
              <tr v-if="has_pages">
                <td class="rop-load-more-posts">
                  <button
                    class="btn btn-error"
                    @click="loadMorePosts()"
                  >
                    <i
                      v-if="!is_loading_single"
                      class="fa fa-newspaper-o "
                    />
                    <i
                      v-else
                      class="fa fa-spinner fa-spin"
                    />
                    {{ labels.load_more_posts }}
                  </button>
                </td>
              </tr>
            </table>
            <div
              v-else
              class="loading loading-lg"
            />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
	import MultipleSelect from './reusables/multiple-select.vue'
	import UpsellSidebar from './upsell-sidebar.vue'
	import Tooltip from './reusables/popover.vue'

	import Vue from 'vue'

	// Vue.use(Tooltip);

	export default {
		name: 'ExcludePostsPage',
		components: {
			MultipleSelect, UpsellSidebar, Tooltip
		},
		data: function () {
			return {
				searchQuery: '',
				show_excluded: false,
				postTimeout: '',
				paged: 1,
				has_pages: true,
				labels: this.$store.state.labels.settings,
				upsell_link: ropApiSettings.upsell_link,
				is_loading: false,
				is_loading_single: false,
				is_taxonomy_message: false,
				limit_exclude_posts: 30,
				posts_selected_currently: 0,
				apply_limit_exclude: false
			}
		},
		computed: {
			generalSettings: function () {
				return this.$store.state.generalSettings
			},
			isPro: function () {
				return ( this.$store.state.license >= 1 );
			},
			isTaxLimit: function () {

				if ( ropApiSettings.tax_apply_limit > 0 ) {
					return true;
				}
				return false;
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
			},
			apply_exclude_limit: function () {
				return ropApiSettings.exclude_apply_limit > 0;

			}

		},
		watch: {
			searchQuery: function ( val ) {
				this.searchUpdate( val );
			},
			postsAvailable: function ( val ) {
				this.has_pages = ( this.postsAvailable.length % 100 === 0 );
			},
		},
		mounted: function () {
			this.$log.info( 'In General Settings state ' );
			this.getGeneralSettings();
		},
		methods: {
			calculate_limit( action ) {
				this.posts_selected_currently += action;
				if ( !this.isPro && this.apply_exclude_limit ) {
					if ( this.posts_selected_currently >= this.limit_exclude_posts ) {
						this.apply_limit_exclude = true;
					} else {
						this.apply_limit_exclude = false;
					}
				}

			},
			displayProMessage( data ) {

				if ( !this.isPro && data >= 4 ) {
					if ( true === this.isTaxLimit ) {
						this.is_taxonomy_message = true;
					} else {
						this.is_taxonomy_message = false;
					}
				}
			},
			excludeSinglePost( post_id, state ) {
				if ( false === state && this.apply_limit_exclude ) {
					//alert( this.$store.state.labels.settings.post_types_exclude_limit );
					return false;
				}


				this.$log.info( 'Excluding post ', post_id, state );
				this.is_loading_single = true;
				this.$store.dispatch( 'fetchAJAXPromise', {
					req: 'exclude_post',
					data: {
						post_id: post_id,
						exclude: state
					}
				} ).then( response => {
					if ( false === state ) {
						this.calculate_limit( 1 );
					} else {
						this.calculate_limit( -1 );
					}

					this.is_loading_single = false;
					let findex = false;
					let fdata = {};
					let exists = this.postsAvailable.some( ( post, index ) => {
						if ( post.value === post_id ) {
							findex = index;
							post.selected = true;
							fdata = post;
						}
						return post.value === post_id
					} );
					if ( findex !== false ) {
						if ( state ) {
							Vue.delete( this.postsAvailable, findex );
						} else {
							Vue.set( this.postsAvailable, findex, fdata );
						}
					}
					this.$log.info( 'Excluding post ', findex );
					this.$log.debug( 'Succesfully fetched.' )
				}, error => {
					this.is_loading_single = false;
					this.$log.error( 'Can not exclude post settings.' )
				} )
			},
			excludePostsBatch() {

				this.$log.info( 'Excluding posts batch', this.searchQuery );
				this.is_loading = true;

				let postTypesSelected = this.$store.state.generalSettings.selected_post_types
				let taxonomiesSelected = this.$store.state.generalSettings.selected_taxonomies
				this.$store.dispatch( 'fetchAJAXPromise', {
					req: 'exclude_post_batch',
					data: {
						post_types: postTypesSelected,
						search: this.searchQuery,
						taxonomies: taxonomiesSelected,
						exclude: this.generalSettings.exclude_taxonomies,
					}
				} ).then( response => {
					this.is_loading = false;
					this.postsAvailable.map( ( post, index ) => {
						post.selected = true;
					} );
					this.$log.debug( 'Succesfully excluded based on key.', this.searchQuery )
				}, error => {
					this.is_loading = false;
					this.$log.error( 'Can not exclude in batch.' )
				} )
			},
			getGeneralSettings() {
				if ( this.$store.state.generalSettings.length === 0 ) {
					this.is_loading = true;
					this.$log.info( 'Fetching general settings.' );
					this.$store.dispatch( 'fetchAJAXPromise', { req: 'get_general_settings' } ).then( response => {
						this.is_loading = false;
						this.$log.debug( 'Succesfully fetched.' );
						this.calculate_limit( response.selected_posts.length );

					}, error => {
						this.is_loading = false;
						this.$log.error( 'Can not fetch the general settings.' )
					} )
				}
			},
			searchUpdate( newQuery ) {
				this.searchQuery = newQuery
				this.requestPostUpdate()
			},
			updatedPostTypes( data ) {
				let postTypes = []
				for ( let index in data ) {
					postTypes.push( data[ index ].value )
				}

				this.$store.commit( 'updateState', { stateData: data, requestName: 'update_selected_post_types' } )
				this.$store.dispatch( 'fetchAJAX', { req: 'get_taxonomies', data: { post_types: postTypes } } )
				this.requestPostUpdate()
			},
			updatedTaxonomies( data ) {
				let taxonomies = []
				for ( let index in data ) {
					taxonomies.push( data[ index ].value )
				}
				this.$store.commit( 'updateState', { stateData: data, requestName: 'update_selected_taxonomies' } )
				this.requestPostUpdate();

			},
			excludeTaxonomiesChange() {
				this.requestPostUpdate()
			},
			excludePostsChange() {
				this.requestPostUpdate()
			},
			doPostUpdate( new_page = true ) {
				if ( new_page ) {
					this.paged = 1;
					this.is_loading = true;
				} else {
					this.is_loading_single = true;
				}
				let postTypesSelected = this.$store.state.generalSettings.selected_post_types
				let taxonomiesSelected = this.$store.state.generalSettings.selected_taxonomies

				this.$log.info( 'Sending request for loading posts..' );
				this.$store.dispatch( 'fetchAJAXPromise', {
					req: 'get_posts',
					data: {
						post_types: postTypesSelected,
						search_query: this.searchQuery,
						show_excluded: this.show_excluded,
						taxonomies: taxonomiesSelected,
						page: this.paged,
						exclude: this.generalSettings.exclude_taxonomies,
					}
				} ).then( response => {
					this.is_loading = false;
					this.is_loading_single = false;
					this.$log.info( 'Successfully loaded psots.' );
				}, error => {
					this.is_loading = false;
					this.is_loading_single = false;
					Vue.$log.error( 'Got nothing from server. Prompt user to check internet connection and try again', error )
				} )
			},
			requestPostUpdate() {
				if ( this.postTimeout !== '' ) {
					clearTimeout( this.postTimeout );
				}
				this.postTimeout = setTimeout( this.doPostUpdate, 500 );

			},
			loadMorePosts() {
				this.paged++;
				this.doPostUpdate( false );
			},
			saveGeneralSettings() {
				let postTypesSelected = this.$store.state.generalSettings.selected_post_types
				let taxonomiesSelected = this.$store.state.generalSettings.selected_taxonomies
				let excludeTaxonomies = this.generalSettings.exclude_taxonomies
				this.is_loading = true;
				this.$log.info( 'Sending request for saving general settings..' );
				this.$store.dispatch( 'fetchAJAXPromise', {
					req: 'save_general_settings',
					updateState: false,
					data: {
						selected_post_types: postTypesSelected,
						selected_taxonomies: taxonomiesSelected,
						exclude_taxonomies: excludeTaxonomies,
					}
				} ).then( response => {
					this.is_loading = false;
					this.$log.info( 'Successfully saved general settings.' );
				}, error => {

					this.$log.error( 'Successfully saved general settings.' );
					this.is_loading = false;
					Vue.$log.error( 'Got nothing from server. Prompt user to check internet connection and try again', error )
				} )
			}
		}
	}
</script>
<style scoped>
    #rop-sidebar-selector {
        border: 1px solid #e5e5e5;
        background: #fff;
    }

    #rop-posts-listing .rop-post-item td {
        position: relative;
    }

    #rop-posts-table {
        margin-top: 20px;
    }

    #rop-posts-listing .rop-post-item:hover button.rop-exclude-post {
        display: block;
    }

    #rop-posts-listing .rop-post-item td button.rop-exclude-post {
        position: absolute;
        top: 5px;
        right: 10px;
        display: none;
        padding: 0px 20px;
    }

    .rop-post-true {
        opacity: 0.8;
        background-color: #F6DBDA;

    }

    .rop-load-more-posts {
        text-align: center;
    }
</style>
