<template>
	<div>
		<h4>Content</h4>
		<!-- Post Content - where to fetch the content which will be shared
			 (dropdown with 4 options ( post_title, post_content, post_content
			 and title and custom field). If custom field is selected we will
			 have a text field which users will need to fill in to fetch the
			 content from that meta key. -->
		<div class="columns">
			<div class="column col-sm-12 col-md-4 col-xl-3 col-ml-2 col-4 text-right">
				<b>Post Content</b><br/>
				<i>From where to fetch the content which will be shared.</i>
			</div>
			<div class="column col-sm-12 col-md-8 col-xl-9 col-mr-4 col-7 text-left">
				<div class="form-group">
					<select class="form-select" v-model="post_format.post_content">
						<option value="post_title">Post Title</option>
						<option value="post_content">Post Content</option>
						<option value="post_title_content">Post Title & Content</option>
						<option value="custom_field">Custom Field</option>
					</select>
				</div>
			</div>
		</div>
		<div class="columns" v-if="post_format.post_content === 'custom_field'">
			<div class="column col-sm-12 col-md-4 col-xl-3 col-ml-2 col-4 text-right">
				<b>Custom Meta Field</b><br/>
				<i>Meta field name from which to get the content.</i>
			</div>
			<div class="column col-sm-12 col-md-8 col-xl-9 col-mr-4 col-7 text-left">
				<div class="form-group">
					<input class="form-input" type="text" v-model="post_format.custom_meta_field"
					       value="" placeholder=""/>
				</div>
			</div>
		</div>
		
		<!-- Maximum length of the message( number field ) which holds the maximum
			 number of chars for the shared content. We striping the content, we need
			 to strip at the last whitespace or dot before reaching the limit, in order
			 to not trim just half of the word. -->
		<div class="columns">
			<div class="column col-sm-12 col-md-4 col-xl-3 col-ml-2 col-4 text-right">
				<b>Maximum chars</b><br/>
				<i>Maximum length of the message.</i>
			</div>
			<div class="column col-sm-12 col-md-8 col-xl-9 col-mr-4 col-7 text-left">
				<div class="form-group">
					<input class="form-input" type="number" v-model="post_format.maximum_length"
					       value="" placeholder=""/>
				</div>
			</div>
		</div>
		
		<!-- Additional text field - text field which will be used by the users to a
			 custom content before the fetched post content. -->
		<div class="columns">
			<div class="column col-sm-12 col-md-4 col-xl-3 col-ml-2 col-4 text-right">
				<b>Additional text</b><br/>
				<i>Add custom content to published items.</i>
			</div>
			<div class="column col-sm-12 col-md-8 col-xl-9 col-mr-4 col-7 text-left">
				<div class="form-group">
                                    <textarea class="form-input" v-model="post_format.custom_text"
                                              placeholder="Custom content ...">{{post_format.custom_text}}</textarea>
				</div>
			</div>
		</div>
		
		<!-- Additional text at - dropdown with 2 options, begining or end, having the
			 option where to add the additional text content. -->
		<div class="columns">
			<div class="column col-sm-12 col-md-4 col-xl-3 col-ml-2 col-4 text-right">
				<i>Where to add the custom text</i>
			</div>
			<div class="column col-sm-12 col-md-8 col-xl-9 col-mr-4 col-7 text-left">
				<div class="form-group">
					<select class="form-select" v-model="post_format.custom_text_pos">
						<option value="beginning">Beginning</option>
						<option value="end">End</option>
					</select>
				</div>
			</div>
		</div>
		<hr/>
		
		<h4>Link & URL</h4>
		<!-- Include link - checkbox either we should include the post permalink or not
			 in the shared content. This is will appended at the end of the content. -->
		<div class="columns">
			<div class="column col-sm-12 col-md-12 col-lg-12">
				<div class="columns">
					<div class="column col-sm-12 col-md-4 col-xl-3 col-ml-2 col-4 text-right">
						<b>Include link</b><br/>
						<i>Should include the post permalink or not?</i>
					</div>
					<div class="column col-sm-12 col-md-8 col-xl-9 col-mr-4 col-7 text-left">
						<div class="input-group">
							<label class="form-checkbox">
								<input type="checkbox" v-model="post_format.include_link"/>
								<i class="form-icon"></i> Yes
							</label>
						</div>
					</div>
				</div>
			</div>
		</div>
		
		<!-- Fetch url from custom field - checkbox - either we should fetch the url from
			 a meta field or not. When checked we will open a text field for entering the
			 meta key. -->
		<div class="columns">
			<div class="column col-sm-12 col-md-12 col-lg-12">
				<div class="columns">
					<div class="column col-sm-12 col-md-4 col-xl-3 col-ml-2 col-4 text-right">
						<b>Custom field</b><br/>
						<i>Fetch URL from custom field?</i>
					</div>
					<div class="column col-sm-12 col-md-8 col-xl-9 col-mr-4 col-7 text-left">
						<div class="input-group">
							<label class="form-checkbox">
								<input type="checkbox" v-model="post_format.url_from_meta"/>
								<i class="form-icon"></i> Yes
							</label>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="columns" v-if="post_format.url_from_meta">
			<div class="column col-sm-12 col-md-4 col-xl-3 col-ml-2 col-4 text-right">
				<b>Custom Field</b><br/>
				<i>Custom Field from which to get the URL.</i>
			</div>
			<div class="column col-sm-12 col-md-8 col-xl-9 col-mr-4 col-7 text-left">
				<div class="form-group">
					<input class="form-input" type="text" v-model="post_format.url_meta_key" value=""
					       placeholder=""/>
				</div>
			</div>
		</div>
		
		<!-- Use url shortner ( checkbox ) , either we should use a shortner when adding
			 the links to the content. When checked we will show a dropdown with the shortners
			 available and the api keys ( if needed ) for each one. The list of shortners will
			 be the same as the old version of the plugin. -->
		<div class="columns">
			<div class="column col-sm-12 col-md-12 col-lg-12">
				<div class="columns">
					<div class="column col-sm-12 col-md-4 col-xl-3 col-ml-2 col-4 text-right">
						<b>Use url shortner</b><br/>
						<i>Should we use a shortner when adding the links to the content?</i>
					</div>
					<div class="column col-sm-12 col-md-8 col-xl-9 col-mr-4 col-7 text-left">
						<div class="input-group">
							<label class="form-checkbox">
								<input type="checkbox" v-model="post_format.short_url"/>
								<i class="form-icon"></i> Yes
							</label>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="columns" v-if="post_format.short_url">
			<div class="column col-sm-12 col-md-4 col-xl-3 col-ml-2 col-4 text-right">
				<b>URL Shorner Service</b><br/>
				<i>Which service to use for URL shortening.</i>
			</div>
			<div class="column col-sm-12 col-md-8 col-xl-9 col-mr-4 col-7 text-left">
				<div class="form-group">
					<select class="form-select" v-model="post_format.short_url_service">
						<option value="rviv.ly">rviv.ly</option>
						<option value="bit.ly">bit.ly</option>
						<option value="shorte.st">shorte.st</option>
						<option value="goo.gl">goo.gl</option>
						<option value="ow.ly">ow.ly</option>
						<option value="is.gd">is.gd</option>
						<option value="wp_short_url">wp_short_url</option>
					</select>
				</div>
			</div>
		</div>
		<div class="columns" v-for="( credential, key_name ) in shortner_credentials">
			<div class="column col-sm-12 col-md-4 col-xl-3 col-ml-2 col-4 text-right">
				<b>{{ key_name | capitalize }}</b><br/>
				<i>Add the "{{key_name}}" required by the <b>{{post_format.short_url_service}}</b>
					service API.</i>
			</div>
			<div class="column col-sm-12 col-md-8 col-xl-9 col-mr-4 col-7 text-left">
				<div class="form-group">
					<input class="form-input" type="text" v-model="shortner_credentials[key_name]"
					       value="" placeholder="" @change="updateShortnerCredentials()"
					       @keyup="updateShortnerCredentials()"/>
				</div>
			</div>
		</div>
		<hr/>
		
		<h4>Misc.</h4>
		<!-- Hashtags - dropdown - having this options - (Dont add any hashtags, Common hastags
			 for all shares, Create hashtags from categories, Create hashtags from tags, Create
			 hashtags from custom field). If one of those options is selected, except the dont
			 any hashtags options, we will show a number field having the Maximum hashtags length.
			 Moreover for common hashtags option, we will have another text field which will contain
			 the hashtags value. -->
		<div class="columns">
			<div class="column col-sm-12 col-md-4 col-xl-3 col-ml-2 col-4 text-right">
				<b>Hashtags</b><br/>
				<i>Hashtags to published content.</i>
			</div>
			<div class="column col-sm-12 col-md-8 col-xl-9 col-mr-4 col-7 text-left">
				<div class="form-group">
					<select class="form-select" v-model="post_format.hashtags">
						<option value="no-hashtags">Dont add any hashtags</option>
						<option value="common-hashtags">Common hastags for all shares</option>
						<option value="categories-hashtags">Create hashtags from categories</option>
						<option value="tags-hashtags">Create hashtags from tags</option>
						<option value="custom-hashtags">Create hashtags from custom field</option>
					</select>
				</div>
			</div>
		</div>
		<div class="columns" v-if="post_format.hashtags !== 'no-hashtags'">
			<div class="column col-sm-12 col-md-4 col-xl-3 col-ml-2 col-4 text-right">
				<b>Maximum Hashtags length</b><br/>
				<i>The maximum hashtags length to be used when publishing.</i>
			</div>
			<div class="column col-sm-12 col-md-8 col-xl-9 col-mr-4 col-7 text-left">
				<div class="form-group">
					<input class="form-input" type="number" v-model="post_format.hashtags_length"
					       value="" placeholder=""/>
				</div>
			</div>
		</div>
		<div class="columns" v-if="post_format.hashtags === 'common-hashtags'">
			<div class="column col-sm-12 col-md-4 col-xl-3 col-ml-2 col-4 text-right">
				<b>Common Hashtags</b><br/>
				<i>List of hastags to use separated by comma ",".</i>
			</div>
			<div class="column col-sm-12 col-md-8 col-xl-9 col-mr-4 col-7 text-left">
				<div class="form-group">
					<input class="form-input" type="text" v-model="post_format.hashtags_common" value=""
					       placeholder=""/>
				</div>
			</div>
		</div>
		<div class="columns" v-if="post_format.hashtags === 'custom-hashtags'">
			<div class="column col-sm-12 col-md-4 col-xl-3 col-ml-2 col-4 text-right">
				<b>Custom Hashtags</b><br/>
				<i>The name of the meta field that contains the hashtags.</i>
			</div>
			<div class="column col-sm-12 col-md-8 col-xl-9 col-mr-4 col-7 text-left">
				<div class="form-group">
					<input class="form-input" type="text" v-model="post_format.hashtags_custom" value=""
					       placeholder=""/>
				</div>
			</div>
		</div>
		
		<div class="columns" :class="'rop-control-container-'+isPro">
			<div class="column col-sm-12 col-md-12 col-lg-12">
				<div class="columns rop-control">
					<div class="column col-sm-12 col-md-4 col-xl-3 col-ml-2 col-4 text-right">
						<b>Post with image</b><br/>
						<i>Use the featured image when posting?</i>
					</div>
					<div class="column col-sm-12 col-md-8 col-xl-9 col-mr-4 col-7 text-left">
						<div class="input-group">
							<label class="form-checkbox">
								<input type="checkbox" v-model="post_format.image"
								       :disabled="!isPro"/>
								<i class="form-icon"></i> Yes
							</label>
						</div>
					
					</div>
				</div>
				<div class="columns rop-upsell-message" v-if="! isPro">
					<div class="col-4"></div>
					<div class="col-7   text-left">
						<p><i class="fa fa-lock"></i> Posting with images ia available in the pro version. </p>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
	module.exports = {
		name: "post-format",
		props: ['account_id', 'license'],
		data: function () {
			return {
				shortner_credentials: []
			}
		},
		computed: {
			post_format: function () {
				return this.$store.state.activePostFormat[this.account_id] ? this.$store.state.activePostFormat[this.account_id] : [];
			},
			isPro: function () {
				return (this.license > 0);
			},
			short_url_service: function () {
				let postFormat = this.$store.state.activePostFormat[this.account_id] ? this.$store.state.activePostFormat[this.account_id] : [];
				return (postFormat.short_url_service) ? postFormat.short_url_service : '';
			}
		},
		watch: {
			short_url_service: function () {
				this.$store.dispatch('fetchAJAXPromise', {
					req: 'get_shortner_credentials',
					data: {short_url_service: this.short_url_service}
				}).then(response => {
					this.shortner_credentials = response
				}, error => {
					Vue.$log.error('Got nothing from server. Prompt user to check internet connection and try again', error)
				})
			}
		},
		methods: {
			updateShortnerCredentials() {
				this.$store.commit('updateState', {
					stateData: this.shortner_credentials,
					requestName: 'get_shortner_credentials'
				})
			}
		}
	}
</script>
