<template>
	<div class="tab-view">
		<div class="panel-body" style="overflow: inherit;">
			<h3>Post Format</h3>
			<figure class="avatar avatar-lg" style="text-align: center;">
				<img :src="img" v-if="img">
				<i class="fa" :class="icon" style="line-height: 48px;" aria-hidden="true" v-else></i>
				<i class="avatar-icon fa" :class="icon" aria-hidden="true" v-if="img"></i>
				<!--<img src="img/avatar-5.png" class="avatar-icon" alt="...">-->
			</figure>
			<div class="d-inline-block" style="vertical-align: top; margin-left: 16px;">
				<h6>{{user_name}}</h6>
				<b class="service" :class="service">{{service_name}}</b>
			</div>
			<div class="d-inline-block" style="vertical-align: top; margin-left: 16px; width: 80%">
				<h4><i class="fa fa-info-circle"></i> Info</h4>
				<p><i>Each <b>account</b> can have it's own <b>Post Format</b> for sharing, on the left you can see the
					current selected account and network, bellow are the <b>Post Format</b> options for the account.
					Don't forget to save after each change and remember, you can always reset an account to the network defaults.
				</i></p>
			</div>
			<div class="container">
				<div class="columns">
					<div class="column col-sm-12 col-md-12 col-lg-12">
						<div class="columns">
							<div class="column col-sm-12 col-md-4 col-xl-3 col-ml-2 col-4 text-right">
								<b>Account</b><br/>
								<i>Specify an account to change the settings of.</i>
							</div>
							<div class="column col-sm-12 col-md-8 col-xl-9 col-mr-4 col-7 text-left">
								<div class="form-group">
									<select class="form-select" v-model="selected_account" @change="getAccountpostFormat()">
										<option v-for="( account, id ) in active_accounts" :value="id" >{{account.user}} - {{account.service}} </option>
									</select>
								</div>
							</div>
						</div>
						<hr/>

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
									<input class="form-input" type="number" v-model="post_format.custom_meta_field" value="" placeholder="" />
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
									<input class="form-input" type="number" v-model="post_format.maximum_length" value="" placeholder="" />
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
									<textarea class="form-input" v-model="post_format.custom_text" placeholder="Custom content ...">{{post_format.custom_text}}</textarea>
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
												<input type="checkbox" v-model="post_format.include_link" />
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
										<b>URL</b><br/>
										<i>Fetch URL from custom field?</i>
									</div>
									<div class="column col-sm-12 col-md-8 col-xl-9 col-mr-4 col-7 text-left">
										<div class="input-group">
											<label class="form-checkbox">
												<input type="checkbox" v-model="post_format.url_from_meta" />
												<i class="form-icon"></i> Yes
											</label>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="columns" v-if="post_format.url_from_meta">
							<div class="column col-sm-12 col-md-4 col-xl-3 col-ml-2 col-4 text-right">
								<b>Meta Key</b><br/>
								<i>Meta key name from which to get the URL.</i>
							</div>
							<div class="column col-sm-12 col-md-8 col-xl-9 col-mr-4 col-7 text-left">
								<div class="form-group">
									<input class="form-input" type="number" v-model="post_format.url_meta_key" value="" placeholder="" />
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
										<i>Should we  use a shortner when adding the links to the content?</i>
									</div>
									<div class="column col-sm-12 col-md-8 col-xl-9 col-mr-4 col-7 text-left">
										<div class="input-group">
											<label class="form-checkbox">
												<input type="checkbox" v-model="post_format.short_url" />
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
									</select>
								</div>
							</div>
						</div>
						<div class="columns" v-for="( credential, key_name ) in shortner_credentials">
							<div class="column col-sm-12 col-md-4 col-xl-3 col-ml-2 col-4 text-right">
								<b>{{ key_name | capitalize }}</b><br/>
								<i>Add the "{{key_name}}" required by the <b>{{post_format.short_url_service}}</b> service API.</i>
							</div>
							<div class="column col-sm-12 col-md-8 col-xl-9 col-mr-4 col-7 text-left">
								<div class="form-group">
									<input class="form-input" type="text" v-model="shortner_credentials[key_name]" value="" placeholder="" @change="updateShortnerCredentials()" @keyup="updateShortnerCredentials()" />
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
										<option value="no-hashtags" >Dont add any hashtags</option>
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
									<input class="form-input" type="number" v-model="post_format.hashtags_length" value="" placeholder="" />
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
									<input class="form-input" type="text" v-model="post_format.hashtags_common" value="" placeholder="" />
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
									<input class="form-input" type="text" v-model="post_format.hashtags_custom" value="" placeholder="" />
								</div>
							</div>
						</div>

						<!-- Post with image - checkbox (either we should use the featured image when posting) -->
						<div class="columns">
							<div class="column col-sm-12 col-md-12 col-lg-12">
								<div class="columns">
									<div class="column col-sm-12 col-md-4 col-xl-3 col-ml-2 col-4 text-right">
										<b>Post with image</b><br/>
										<i>Use the featured image when posting?</i>
									</div>
									<div class="column col-sm-12 col-md-8 col-xl-9 col-mr-4 col-7 text-left">
										<div class="input-group">
											<label class="form-checkbox">
												<input type="checkbox" v-model="post_format.image" />
												<i class="form-icon"></i> Yes
											</label>
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
										<b>Stats:</b><br/>
										<i>Available char for post content</i>
									</div>
									<div class="column col-sm-12 col-md-8 col-xl-9 col-mr-4 col-7 text-left">
										{{computed_chars}}
									</div>
								</div>
							</div>
						</div>
						<hr/>
					</div>
				</div>
			</div>
		</div>
		<div class="panel-footer">
			<button class="btn btn-primary" @click="savePostFormat()"><i class="fa fa-check"></i> Save Post Format</button>
			<button class="btn btn-secondary" @click="resetPostFormat()"><i class="fa fa-ban"></i> Reset to Defaults</button>
		</div>
	</div>
</template>

<script>
	module.exports = {
		name: 'post-format-view',
		data: function () {
			let key = null
			if ( Object.keys( this.$store.state.activeAccounts )[0] !== undefined ) key = Object.keys( this.$store.state.activeAccounts )[0]
			return {
				selected_account: key,
				shortner_credentials: []
			}
		},
		mounted: function () {
			// Uncomment this when not fixed tab on post format
			this.getAccountpostFormat()
		},
		filters: {
			capitalize: function ( value ) {
				if ( !value ) return ''
				value = value.toString()
				return value.charAt( 0 ).toUpperCase() + value.slice( 1 )
			}
		},
		computed: {
			computed_chars: function () {
				let allowedChars = this.post_format.maximum_length
				let customText = 0
				let hashtagsLength = 0
				if ( this.post_format.custom_text !== undefined ) customText = this.post_format.custom_text.length
				if ( this.post_format.hashtags !== 'no-hashtags' ) hashtagsLength = this.post_format.hashtags_length
				if ( customText !== 0 ) customText = customText + 1
				let serviceReserved = 0
				if ( this.selected_account !== null && this.active_accounts[this.selected_account].service === 'twitter' ) {
					if ( this.post_format.image ) serviceReserved = serviceReserved + 25
					if ( this.post_format.include_link ) serviceReserved = serviceReserved + 25
				}
				return allowedChars - customText - hashtagsLength - serviceReserved
			},
			active_accounts: function () {
				return this.$store.state.activeAccounts
			},
			post_format: function () {
				return this.$store.state.activePostFormat
			},
			short_url_service: function () {
				let postFormat = this.$store.getters.getPostFormat
				return postFormat.short_url_service
			},
			icon: function () {
				let serviceIcon = 'fa-user'
				if ( this.selected_account !== null ) {
					serviceIcon = 'fa-'
					let account = this.active_accounts[this.selected_account]
					if ( account.service === 'facebook' ) serviceIcon = serviceIcon.concat( 'facebook-official' )
					if ( account.service === 'twitter' ) serviceIcon = serviceIcon.concat( 'twitter' )
					if ( account.service === 'linkedin' ) serviceIcon = serviceIcon.concat( 'linkedin' )
					if ( account.service === 'tumblr' ) serviceIcon = serviceIcon.concat( 'tumblr' )
				}
				return serviceIcon
			},
			img: function () {
				let img = ''
				if ( this.selected_account !== null && this.active_accounts[this.selected_account].img !== '' && this.active_accounts[this.selected_account].img !== undefined ) {
					img = this.active_accounts[this.selected_account].img
				}
				return img
			},
			service: function () {
				let serviceClass = ''
				if ( this.selected_account !== null && this.active_accounts[this.selected_account].service ) {
					serviceClass = this.active_accounts[this.selected_account].service
				}
				return serviceClass
			},
			service_name: function () {
				if ( this.service !== '' ) return this.service.charAt( 0 ).toUpperCase() + this.service.slice( 1 )
				return 'Service'
			},
			user_name: function () {
				if ( this.selected_account !== null && this.active_accounts[this.selected_account].user ) return this.active_accounts[this.selected_account].user
				return 'John Doe'
			}
		},
		watch: {
			active_accounts: function () {
				console.log( 'Accounts changed' )
				if ( Object.keys( this.$store.state.activeAccounts )[0] && this.selected_account === null ) {
					let key = Object.keys( this.$store.state.activeAccounts )[0]
					this.selected_account = key
					this.getAccountpostFormat()
				}
			},
			short_url_service: function () {
				console.log( 'Service changed' )
				console.log( this.short_url_service )
				this.$store.dispatch( 'fetchShortnerCredentials', { short_url_service: this.short_url_service } ).then( response => {
					console.log( 'Got some data, now lets show something in this component', response )
					this.shortner_credentials = response
				}, error => {
					console.error( 'Got nothing from server. Prompt user to check internet connection and try again', error )
				} )
			}
		},
		methods: {
			getAccountpostFormat () {
				console.log( 'Get Post format for', this.selected_account )
				this.$store.dispatch( 'fetchPostFormat', { service: this.active_accounts[ this.selected_account ].service, account_id: this.selected_account } )
			},
			savePostFormat () {
				console.log( 'Save Post format for', this.selected_account )
				this.$store.dispatch( 'savePostFormat', { service: this.active_accounts[ this.selected_account ].service, account_id: this.selected_account, post_format: this.post_format } )
			},
			resetPostFormat () {
				console.log( 'Reset Post format for', this.selected_account )
				this.$store.dispatch( 'resetPostFormat', { service: this.active_accounts[ this.selected_account ].service, account_id: this.selected_account } )
				this.$forceUpdate()
			},
			updateShortnerCredentials () {
				this.$store.commit( 'updatePostFormatShortnerCredentials', this.shortner_credentials )
			}
		}
	}
</script>

<style scoped>
	#rop_core .avatar .avatar-icon {
		background: #333;
		border-radius: 50%;
		font-size: 16px;
		text-align: center;
		line-height: 20px;
	}
	#rop_core .avatar .avatar-icon.fa-facebook-official { background-color: #3b5998; }
	#rop_core .avatar .avatar-icon.fa-twitter { background-color: #55acee; }
	#rop_core .avatar .avatar-icon.fa-linkedin { background-color: #007bb5; }
	#rop_core .avatar .avatar-icon.fa-tumblr { background-color: #32506d; }

	#rop_core .service.facebook {
		color: #3b5998;
	}

	#rop_core .service.twitter {
		color: #55acee;
	}

	#rop_core .service.linkedin {
		color: #007bb5;
	}

	#rop_core .service.tumblr {
		color: #32506d;
	}
</style>