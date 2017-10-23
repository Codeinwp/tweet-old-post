<template>
	<div class="tab-view">
		<div class="panel-body">
			<h3>General Settings</h3>
			<p>This is a <b>Vue.js</b> component.</p>
			<div class="container">
				<div class="columns">
					<!-- Minimum age of posts available for sharing, in days
					(number) -->
					<div class="column col-sm-12 col-md-12 col-lg-6">
						<div class="columns">
							<div class="column col-sm-12 col-md-6 col-xl-6 col-8 text-right">
								<b>Minimum post age</b><br/>
								<i>Minimum age of posts available for sharing, in days.</i>
							</div>
							<div class="column col-sm-12 col-md-6 col-xl-6 col-4 text-left">
								<counter-input id="min_post_age" :maxVal="365" v-model="generalSettings.minimum_post_age" />
							</div>
						</div>
					</div>
					<!-- Maximum age of posts available for sharing, in days
					(number) -->
					<div class="column col-sm-12 col-md-12 col-lg-6">
						<div class="columns">
							<div class="column col-sm-12 col-md-6 col-xl-6 col-4 text-right">
								<counter-input id="max_post_age" :maxVal="365" v-model="generalSettings.maximum_post_age" />
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
					<!-- Number of posts to share per account per trigger
					(number) -->
					<div class="column col-sm-12 col-md-12 col-lg-6">
						<div class="columns">
							<div class="column col-sm-12 col-md-6 col-xl-6 col-8 text-right">
								<b>Number of posts</b><br/>
								<i>Number of posts to share per. account per. trigger of scheduled job.</i>
							</div>
							<div class="column col-sm-12 col-md-6 col-xl-6 col-4 text-left">
								<counter-input id="no_of_posts" v-model="generalSettings.number_of_posts" />
							</div>
						</div>
					</div>
					<!-- Share more than once, if there are no more posts to share, we should start re-sharing the one we
					previously shared
					(boolean) -->
					<div class="column col-sm-12 col-md-12 col-lg-6">
						<div class="columns">
							<div class="column col-sm-12 col-md-2 col-xl-2 col-1 text-right">
								<div class="form-group">
									<label class="form-checkbox">
										<input type="checkbox" v-model="generalSettings.more_than_once" />
										<i class="form-icon"></i> Yes
									</label>
								</div>
							</div>
							<div class="column col-sm-12 col-md-10 col-xl-10 col-11 text-left">
								<b>Share more than once?</b><br/>
								<i>If there are no more posts to share, we should start re-sharing the one we previously shared.</i>
							</div>
						</div>
					</div>
				</div>
				<hr/>
				<div class="columns">
					<!-- Post types available to share - what post types are available for share
					( multi-select list ) -->
					<div class="column col-sm-12 col-md-12 col-lg-12">
						<div class="columns">
							<div class="column col-sm-12 col-md-4 col-xl-3 col-ml-2 col-4 text-right">
								<b>Post types</b><br/>
								<i>Post types available to share - what post types are available for share</i>
							</div>
							<div class="column col-sm-12 col-md-8 col-xl-9 col-mr-4 col-7 text-left">
								<multiple-select :options="postTypes" :selected="generalSettings.selected_post_types" :changedSelection="updatedPostTypes" />
							</div>
						</div>
					</div>
				</div>
				<hr/>
				<div class="columns">
					<!-- Taxonomies available for posts to share - based on what post types users choose to share, we should
					show the taxonomies available for that post type, along with their terms, which user can select to share.
					Here we should have also a toggle if either the taxonomies selected are included or excluded.
					( multi-select list ) -->
					<div class="column col-sm-12 col-md-12 col-lg-12">
						<div class="columns">
							<div class="column col-sm-12 col-md-4 col-xl-3 col-ml-2 col-4 text-right">
								<b>Taxonomies</b><br/>
								<i>Taxonomies available for the selected post types. Use to include or exclude posts.</i>
							</div>
							<div class="column col-sm-12 col-md-8 col-xl-9 col-mr-4 col-7 text-left">
								<div class="input-group">
									<multiple-select :options="taxonomies" :selected="generalSettings.selected_taxonomies" :changedSelection="updatedTaxonomies" />
									<span class="input-group-addon">
										<label class="form-checkbox">
											<input type="checkbox" v-model="generalSettings.exclude_taxonomies" />
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
					<!-- Posts excluded/included in sharing - what posts we should exclude or include in sharing
					- we should have have an autocomplete list which should fetch posts from the previously select post_types
					and terms and allow them to be include/excluded.
					( multi-select list ) -->
					<div class="column col-sm-12 col-md-12 col-lg-12">
						<div class="columns">
							<div class="column col-sm-12 col-md-4 col-xl-3 col-ml-2 col-4 text-right">
								<b>Posts</b><br/>
								<i>Posts excluded/included in sharing, filtered based on previous selections.</i>
							</div>
							<div class="column col-sm-12 col-md-8 col-xl-9 col-mr-4 col-7 text-left">
								<div class="input-group">
									<multiple-select :options="postsAvailable" :selected="[]" />
									<span class="input-group-addon">
										<label class="form-checkbox">
											<input type="checkbox" />
											<i class="form-icon"></i> Exclude?
										</label>
									</span>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="panel-footer">
			<button class="btn btn-primary"><i class="fa fa-check"></i> Save</button>
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
				taxonomiesSelected: []
			}
		},
		computed: {
			generalSettings: function () {
				return this.$store.state.generalSettings
			},
			postTypes: function () {
				let options = []
				for ( let index in this.generalSettings.available_post_types ) {
					let item = this.generalSettings.available_post_types[index]
					options.push( { name: item.label, value: item.name, selected: false } )
				}

				return options
			},
			taxonomies: function () {
				let options = []
				let taxonomiesSelected = this.taxonomiesSelected
				for ( let taxIndex in this.generalSettings.available_taxonomies ) {
					console.log( taxIndex )
					let taxName = this.generalSettings.available_taxonomies[taxIndex]['name']
					let taxSelected = false
					if ( taxonomiesSelected.includes( taxIndex + '_all' ) ) taxSelected = true
					options.push( { name: taxName, value: taxIndex + '_all', selected: taxSelected } )
					for ( let termIndex in this.generalSettings.available_taxonomies[taxIndex]['terms'] ) {
						let termName = this.generalSettings.available_taxonomies[taxIndex]['terms'][termIndex]['name']
						let termSlug = this.generalSettings.available_taxonomies[taxIndex]['terms'][termIndex]['slug']
						let termSelected = taxSelected
						if ( taxonomiesSelected.includes( taxIndex + '_' + termSlug ) ) termSelected = true
						options.push( { name: taxName + ': ' + termName, value: taxIndex + '_' + termSlug, selected: termSelected } )
					}
				}

				return options
			},
			postsAvailable: function () {
				return [
					{ name: 'This cool post!', selected: false },
					{ name: 'Hello World', selected: true },
					{ name: 'The curious case of autonomous AI.', selected: false }
				]
			}
		},
		methods: {
			updatedPostTypes ( data ) {
				let postTypes = []
				for ( let index in data ) {
					postTypes.push( data[index].value )
				}
				this.$store.commit( 'updateSelectedPostTypes', data )
				this.$store.dispatch( 'fetchTaxonomies', { post_types: postTypes } )
			},
			updatedTaxonomies ( data ) {
				let taxonomiesSelectedList = []
				for ( let index in data ) {
					taxonomiesSelectedList.push( data[index].value )
				}

				this.taxonomiesSelected = taxonomiesSelectedList
				this.$store.commit( 'updateSelectedTaxonomies', data )

				let postTypesSelected = this.$store.state.generalSettings.selected_post_types
				let taxonomiesSelected = this.$store.state.generalSettings.selected_taxonomies

				this.$store.dispatch( 'fetchPosts', { post_types: postTypesSelected, taxonomies: taxonomiesSelected } )
			}
		},
		components: {
			CounterInput,
			MultipleSelect
		}
	}
</script>