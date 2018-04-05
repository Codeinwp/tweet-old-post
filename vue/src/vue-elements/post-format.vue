<template>
	<div>
		<!-- Post Content - where to fetch the content which will be shared
			 (dropdown with 4 options ( post_title, post_content, post_content
			 and title and custom field). If custom field is selected we will
			 have a text field which users will need to fill in to fetch the
			 content from that meta key. -->
		<div class="columns py-2">
			<div class="column col-6 col-sm-12 vertical-align">
				<b>Post Content</b>
				<p class="text-gray">From where to fetch the content which will be shared.</p>
			</div>
			<div class="column col-6 col-sm-12 vertical-align">
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
		<!-- Custom Meta Field -->
		<div class="columns py-2" v-if="post_format.post_content === 'custom_field'">
			<div class="column col-6 col-sm-12 vertical-align">
				<b>Custom Meta Field</b>
				<p class="text-gray">Meta field name from which to get the content.</p>
			</div>
			<div class="column col-6 col-sm-12 vertical-align">
				<div class="form-group">
					<input class="form-input" type="text" v-model="post_format.custom_meta_field"
					       value="" placeholder=""/>
				</div>
			</div>
		</div>
		<span class="divider"></span>

		<!-- Maximum length of the message( number field ) which holds the maximum
			 number of chars for the shared content. We striping the content, we need
			 to strip at the last whitespace or dot before reaching the limit, in order
			 to not trim just half of the word. -->
		<div class="columns py-2">
			<div class="column col-6 col-sm-12 vertical-align">
				<b>Maximum chars</b>
				<p class="text-gray">Maximum length of the message.</p>
			</div>
			<div class="column col-6 col-sm-12 vertical-align">
				<div class="form-group">
					<input class="form-input" type="number" v-model="post_format.maximum_length"
					       value="" placeholder=""/>
				</div>
			</div>
		</div>
		<span class="divider"></span>

		<!-- Additional text field - text field which will be used by the users to a
			 custom content before the fetched post content. -->
		<div class="columns py-2">
			<div class="column col-6 col-sm-12 vertical-align">
				<b>Additional text</b>
				<p class="text-gray">Add custom content to published items.</p>
			</div>
			<div class="column col-6 col-sm-12 vertical-align">
				<div class="form-group">
					<textarea class="form-input" v-model="post_format.custom_text"
						placeholder="Custom content ...">{{post_format.custom_text}}</textarea>
				</div>
			</div>
		</div>

		<!-- Additional text at - dropdown with 2 options, begining or end, having the
			 option where to add the additional text content. -->
		<div class="columns py-2">
			<div class="column col-6 col-sm-12 vertical-align">
				<p class="text-gray">Where to add the custom text.</p>
			</div>
			<div class="column col-6 col-sm-12 vertical-align">
				<div class="form-group">
					<select class="form-select" v-model="post_format.custom_text_pos">
						<option value="beginning">Beginning</option>
						<option value="end">End</option>
					</select>
				</div>
			</div>
		</div>

		<!-- Include link - checkbox either we should include the post permalink or not
			 in the shared content. This is will appended at the end of the content. -->
        <div class="columns py-2">
            <div class="column col-6 col-sm-12 vertical-align">
                <b>Include link</b>
                <p class="text-gray">Should include the post permalink or not?</p>
            </div>
            <div class="column col-6 col-sm-12 vertical-align">
                <div class="input-group">
                    <label class="form-checkbox">
                        <input type="checkbox" v-model="post_format.include_link"/>
                        <i class="form-icon"></i> Yes
                    </label>
                </div>
            </div>
        </div>
		<span class="divider"></span>
		<!-- Fetch url from custom field - checkbox - either we should fetch the url from
			 a meta field or not. When checked we will open a text field for entering the
			 meta key. -->
		<div class="columns py-2">
			<div class="column col-6 col-sm-12 vertical-align">
				<b>Custom field</b>
				<p class="text-gray">Fetch URL from custom field?</p>
			</div>
			<div class="column col-6 col-sm-12 vertical-align">
				<div class="input-group">
					<label class="form-checkbox">
						<input type="checkbox" v-model="post_format.url_from_meta"/>
						<i class="form-icon"></i> Yes
					</label>
				</div>
			</div>
		</div>
		<!-- Custom Field -->
		<div class="columns py-2" v-if="post_format.url_from_meta">
			<div class="column col-6 col-sm-12 vertical-align">
				<b>Custom Field</b>
				<p class="text-gray">Custom Field from which to get the URL.</p>
			</div>
			<div class="column col-6 col-sm-12 vertical-align">
				<div class="form-group">
					<input class="form-input" type="text" v-model="post_format.url_meta_key" value=""
					       placeholder=""/>
				</div>
			</div>
		</div>
		<span class="divider"></span>
		<!-- Use url shortner ( checkbox ) , either we should use a shortner when adding
			 the links to the content. When checked we will show a dropdown with the shortners
			 available and the api keys ( if needed ) for each one. The list of shortners will
			 be the same as the old version of the plugin. -->
		<div class="columns py-2">
			<div class="column col-6 col-sm-12 vertical-align">
				<b>Use url shortner</b>
				<p class="text-gray">Should we use a shortner when adding the links to the content?</p>
			</div>
			<div class="column col-6 col-sm-12 vertical-align">
				<div class="input-group">
					<label class="form-checkbox">
						<input type="checkbox" v-model="post_format.short_url"/>
						<i class="form-icon"></i> Yes
					</label>
				</div>
			</div>
		</div>
		<!-- Shortner Service -->
		<div class="columns py-2" v-if="post_format.short_url">
			<div class="column col-6 col-sm-12 vertical-align">
				<b>URL Shorner Service</b>
				<p class="text-gray">Which service to use for URL shortening.</p>
			</div>
			<div class="column col-6 col-sm-12 vertical-align">
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

		<div class="columns py-2" v-if="post_format.short_url" v-for="( credential, key_name ) in shortner_credentials">
			<div class="column col-6 col-sm-12 vertical-align">
				<b>{{ key_name | capitalize }}</b>
				<p class="text-gray">Add the "{{key_name}}" required by the <strong>{{post_format.short_url_service}}</strong> service API.</p>
			</div>
			<div class="column col-6 col-sm-12 vertical-align">
				<div class="form-group">
					<input class="form-input" type="text" v-model="shortner_credentials[key_name]"
					       value="" placeholder="" @change="updateShortnerCredentials()"
					       @keyup="updateShortnerCredentials()"/>
				</div>
			</div>
		</div>

		<!-- Hashtags - dropdown - having this options - (Dont add any hashtags, Common hastags
			 for all shares, Create hashtags from categories, Create hashtags from tags, Create
			 hashtags from custom field). If one of those options is selected, except the dont
			 any hashtags options, we will show a number field having the Maximum hashtags length.
			 Moreover for common hashtags option, we will have another text field which will contain
			 the hashtags value. -->
		<div class="columns py-2">
			<div class="column col-6 col-sm-12 vertical-align">
				<b>Hashtags</b>
				<p class="text-gray">Hashtags to published content.</p>
			</div>
			<div class="column col-6 col-sm-12 vertical-align">
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
		<!-- Common Hashtags -->
		<div class="columns py-2" v-if="post_format.hashtags === 'common-hashtags'">
			<div class="column col-6 col-sm-12 vertical-align">
				<b>Common Hashtags</b>
				<p class="text-gray">List of hastags to use separated by comma ",".</p>
			</div>
			<div class="column col-6 col-sm-12 vertical-align">
				<div class="form-group">
					<input class="form-input" type="text" v-model="post_format.hashtags_common" value=""
					       placeholder=""/>
				</div>
			</div>
		</div>

		<!-- Custom Hashtags -->
		<div class="columns py-2" v-if="post_format.hashtags === 'custom-hashtags'">
			<div class="column col-6 col-sm-12 vertical-align">
				<b>Custom Hashtags</b>
				<p class="text-gray">The name of the meta field that contains the hashtags.</p>
			</div>
			<div class="column col-6 col-sm-12 vertical-align">
				<div class="form-group">
					<input class="form-input" type="text" v-model="post_format.hashtags_custom" value=""
					       placeholder=""/>
				</div>
			</div>
		</div>

		<!-- Hashtags Length -->
		<div class="columns py-2" v-if="post_format.hashtags !== 'no-hashtags'">
			<div class="column col-6 col-sm-12 vertical-align">
				<b>Maximum Hashtags length</b>
				<p class="text-gray">The maximum hashtags length to be used when publishing.</p>
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
			<div class="column col-6 col-sm-12 vertical-align">
				<b>Post with image</b>
				<p class="text-gray">Use the featured image when posting?</p>
			</div>
			<div class="column col-6 col-sm-12 vertical-align">
				<div class="input-group">
					<label class="form-checkbox">
						<input type="checkbox" v-model="post_format.image"
							   :disabled="!isPro"/>
						<i class="form-icon"></i> Yes
					</label>
				</div>
			</div>
		</div>

		<!-- Upsell -->
		<div class="columns py-2" v-if="!isPro">
			<div class="column text-center">
				<p class="upsell"><i class="fa fa-lock"></i> Posting with images ia available in the pro version. </p>
			</div>
		</div>
		<span class="divider"></span>
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
		},
        filters: {
            capitalize: function (value) {
                if (!value) return ''
                value = value.toString()
                return value.charAt(0).toUpperCase() + value.slice(1)
            }
        }
	}
</script>
<style scoped>
	#rop_core .panel-body .text-gray {
		margin: 0;
		line-height: normal;
	}
	b {
		margin-bottom :5px;
		display: block;
	}
	#rop_core .input-group .input-group-addon {
		padding: 3px 5px;
	}
	@media( max-width: 600px ) {
		#rop_core .panel-body .text-gray {
			margin-bottom: 10px;
		}
		#rop_core .text-right {
			text-align: left;
		}
	}
</style>