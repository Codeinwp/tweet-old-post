<template>
	<div class="tab-view">
		<div class="panel-body" style="overflow: inherit;">
			<h3>Post Format</h3>
			<p>This is a <b>Vue.js</b> component.</p>
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
									<select class="form-select">
										<option v-for="( account, id ) in active_accounts" >{{account.user}} - {{account.service}} </option>
									</select>
								</div>
							</div>
						</div>
						<hr/>

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
									<select class="form-select">
										<option>post_title</option>
										<option>post_content</option>
										<option>post_content and title</option>
										<option>custom field</option>
									</select>
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
									<input class="form-input" type="number" value="" placeholder="" />
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
									<textarea class="form-input" placeholder="Custom content ..."></textarea>
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
									<select class="form-select">
										<option>begining</option>
										<option>end</option>
									</select>
								</div>
							</div>
						</div>

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
												<input type="checkbox" />
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
												<input type="checkbox" />
												<i class="form-icon"></i> Yes
											</label>
										</div>
									</div>
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
												<input type="checkbox" />
												<i class="form-icon"></i> Yes
											</label>
										</div>
									</div>
								</div>
							</div>
						</div>

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
									<select class="form-select">
										<option value="no-hashtags" >Dont add any hashtags</option>
										<option value="common-hastags">Common hastags for all shares</option>
										<option value="categories-hastags">Create hashtags from categories</option>
										<option value="tags-hastags">Create hashtags from tags</option>
										<option value="custom-hastags">Create hashtags from custom field</option>
									</select>
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
												<input type="checkbox" />
												<i class="form-icon"></i> Yes
											</label>
										</div>
									</div>
								</div>
							</div>
						</div>

					</div>
				</div>
			</div>
		</div>
        <div class="panel-footer">
            <button class="btn btn-primary" @click="saveGeneralSettings()"><i class="fa fa-check"></i> Save</button>
        </div>
	</div>
</template>

<script>
	module.exports = {
		name: 'post-format-view',
		computed: {
			active_accounts: function () {
				return this.$store.state.activeAccounts
			}
		},
		methods: {

		}
	}
</script>