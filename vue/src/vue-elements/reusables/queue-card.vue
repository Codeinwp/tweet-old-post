<template>
	<div class="card">
		<div class="columns">
			<div class="column col-sm-12 col-justified">
				<div class="columns">
					<div class="column">
						<p class="text-gray text-left "><i class="fa fa-clock-o"></i> {{card_data.date}} <b><i
								class="fa fa-at"></i></b> <i class="service fa"
						                                     :class="iconClass( card_data.account_id )"></i>
							{{getAccountName(card_data.account_id)}}</p>
					</div>
				</div>
				<div class="columns" v-if="!edit">
					<div class="column col-12">
						<p v-html="content.content + hashtags( content.hashtags )"></p>
					</div>
				</div>
				<div class="form-group columns" v-if="edit">
					<div class="column col-12" v-if="content.post_with_image">
						<label class="form-label" for="image">{{labels.queue_image}}</label>
						<div class="input-group">
							<span class="input-group-addon"><i class="fa fa-file-image-o"></i></span>
							<input id="image" type="text" class="form-input" :value="content.post_image" readonly>
							<button class="btn btn-primary input-group-btn tooltip" @click="uploadImage"
							        :data-tooltip="labels.upload_image">
								<i class="fa fa-upload" aria-hidden="true"></i>
							</button>
							<button class="btn btn-danger input-group-btn tooltip" @click="removeImage"
							        :data-tooltip="labels.remove_image">
								<i class="fa fa-remove" aria-hidden="true"></i>
							</button>
						</div>
					</div>
					<div class="column col-12">
						<label class="form-label" for="content">{{labels.queue_content}}</label>
						<textarea class="form-input" id="content" placeholder="" rows="3" @keyup="checkCount">{{content.content}}</textarea>
					</div>
				</div>
				<div class="columns col-justified" v-if="!edit">
					<div class="column col-3">
						<button class="btn btn-sm btn-block btn-warning tooltip   tooltip-bottom "
						        @click="skipPost(card_data.account_id, card_data.post_id)"
						        :data-tooltip="labels.reschedule_post"
						        :disabled=" ! enabled">
							<i class="fa fa-spinner fa-spin" v-if=" is_loading === 'skip'"></i>
							<i class="fa fa-step-forward" v-else aria-hidden="true"></i>
							{{labels.skip_btn_queue}}
						</button>
					</div>
					<div class="column col-3">
						<button class="btn btn-sm btn-block btn-danger tooltip     tooltip-bottom  "
						        :data-tooltip="labels.ban_post"
						        @click="blockPost(card_data.account_id, card_data.post_id)" :disabled=" ! enabled">
							<i class="fa fa-spinner fa-spin" v-if=" is_loading === 'block'"></i>
							<i class="fa fa-ban" aria-hidden="true" v-else></i>
							{{labels.block_btn_queue}}
						</button>
					</div>
					<div class="column col-3">
						<button class="btn btn-sm btn-block btn-primary" @click="toggleEditState" v-if="!edit"
						        :disabled=" ! enabled">
							<i class="fa fa-pencil" aria-hidden="true"></i> {{labels.edit_queue}}
						</button>
					</div>
					<div class="column col-3 col-ml-auto text-right" v-if="content.post_url !== ''">
						<p class="m-0">
							<b>{{labels.link_title}}:</b>
							<a :href="content.post_url" target="_blank" class="tooltip"
							   :data-tooltip="labels.link_shortned_start + ' ' + ( content.short_url_service == '' ? 'permalink' : content.short_url_service )  ">
								{{'{' + ( content.short_url_service == '' ? 'permalink' : content.short_url_service ) +
								'}'}}</a>
						</p>
					</div>
				</div>
				<div class="columns" v-else>
					<div class="column col-3">
						<button class="btn btn-sm btn-block btn-success"
						        @click="saveChanges(card_data.account_id, card_data.post_id)"
						        v-if="edit" :disabled=" ! enabled">
							<i class="fa fa-spinner fa-spin" v-if=" is_loading === 'edit'"></i>
							<i class="fa fa-check" aria-hidden="true" v-else></i>
							{{labels.save_edit}}
						</button>
					</div>
					<div class="column col-3">
						<button class="btn btn-sm btn-block btn-warning" @click="cancelChanges" v-if="edit"
						        :disabled=" ! enabled">
							<i class="fa fa-times" aria-hidden="true"></i>
							{{labels.cancel_edit}}
						</button>
					</div>
				</div>
			</div>
			<div class="column col-4 col-sm-12 vertical-align" v-if="!edit && content.post_with_image">
				<div v-if="content.post_image !== ''">
					<figure class="figure" v-if="content.post_image !== ''">
						<img :src="( content.mimetype.type.indexOf('image') > -1 ? content.post_image : video_placeholder )"
						     class="img-fit-cover img-responsive">
					</figure>
				
				</div>
				<div class="rop-image-placeholder" v-else>
					<summary>
						<i class="fa fa-file-image-o"></i>
						{{labels.queue_no_image}}
					</summary>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
	/* global wp */

	module.exports = {
		name: 'queue-card',
		props: {
			id: {
				default: ''
			},
			enabled: {
				default: false,
				type: Boolean
			},
			card_data: {
				default: {},
				type: Object
			}
		},
		data: function () {
			return {
				edit: false,
				labels: this.$store.state.labels.queue,
				upsell_link: ropApiSettings.upsell_link,
				video_placeholder: ROP_ASSETS_URL + 'img/video_placeholder.jpg',
				is_loading: false,
				post_edit: {}
			}
		},
		computed: {
			content: function () {
				if (typeof this.card_data.content !== 'undefined') {
					return this.card_data.content
				}
				return {}
			},
			active_accounts: function () {
				return this.$store.state.activeAccounts
			},
		},
		mounted: function () {
			//console.log(this.card_data);
		},
		watch: {},
		methods: {
			skipPost: function (account, post_id) {
				if (this.is_loading) {
					this.$log.warn('Request in progress...Bail');
					return;
				}
				this.is_loading = 'skip';
				this.$store.dispatch('fetchAJAXPromise', {
					req: 'skip_queue_event',
					data: {account_id: account, post_id: post_id}
				}).then(response => {
					this.is_loading = false;
				}, error => {
					this.is_loading = false;
					Vue.$log.error('Got nothing from server. Prompt user to check internet connection and try again', error)
				})
			},
			blockPost: function (account, post_id) {
				if (this.is_loading) {
					this.$log.warn('Request in progress...Bail');
					return;
				}
				this.is_loading = 'block';
				this.$store.dispatch('fetchAJAXPromise', {
					req: 'block_queue_event',
					data: {account_id: account, post_id: post_id}
				}).then(response => {
					this.is_loading = false;
				}, error => {
					this.is_loading = false;
					Vue.$log.error('Got nothing from server. Prompt user to check internet connection and try again', error)
				})
			},
			toggleEditState: function () {
				this.edit = !this.edit;
			},
			getAccountName: function (key) {
				if (typeof  this.active_accounts[key] === 'undefined') {
					return '';
				}
				return this.active_accounts[key].user
			},
			checkCount: function (evt) {
				this.post_edit.text = ''
				if (this.post_edit.text !== evt.srcElement.value) {
					this.post_edit.text = evt.srcElement.value
				}
			},
			saveChanges: function (account, post_id) {
				if (this.is_loading) {
					this.$log.warn('Request in progress...Bail');
					return;
				}
				this.is_loading = 'edit';
				this.$store.dispatch('fetchAJAXPromise', {
					req: 'update_queue_event',
					data: {
						account_id: account,
						post_id: post_id,
						custom_data: this.post_edit
					}
				}).then(response => {
					this.is_loading = false;
					this.toggleEditState()
				}, error => {
					this.is_loading = false;
					this.toggleEditState()
					Vue.$log.error('Got nothing from server. Prompt user to check internet connection and try again', error)
				})
			},
			cancelChanges: function () {
				this.post_edit = {};
				this.toggleEditState()
			},
			uploadImage: function () {
				let window = wp.media({
					title: this.labels.insert_media_title,
					library: {
						type: 'image'
					},
					multiple: false,
					button: {text: this.labels.insert_media_btn}
				})

				let self = this
				window.on('select', function () {
					let first = window.state().get('selection').first().toJSON()
					//console.log( first )
					self.content.post_image = first.url;
					self.post_edit.image = first.url;
				})

				window.open()
			},
			removeImage: function () {
				let self = this;
				self.content.post_image = null;
				self.post_edit.image = null;
			},
			iconClass: function (accountId) {
				let serviceIcon = 'fa-user'
				if (accountId !== null) {
					serviceIcon = 'fa-'
					let account = this.active_accounts[accountId]
					if (account !== undefined && account.service === 'facebook') serviceIcon = serviceIcon.concat('facebook facebook')
					if (account !== undefined && account.service === 'twitter') serviceIcon = serviceIcon.concat('twitter twitter')
					if (account !== undefined && account.service === 'linkedin') serviceIcon = serviceIcon.concat('linkedin linkedin')
					if (account !== undefined && account.service === 'tumblr') serviceIcon = serviceIcon.concat('tumblr tumblr')
					if (account !== undefined && account.service === 'pinterest') serviceIcon = serviceIcon.concat('pinterest pinterest')
					if (account !== undefined && account.service === 'reddit') serviceIcon = serviceIcon.concat('reddit reddit')
				}
				return serviceIcon
			},
			hashtags: function (string) {
				let regex = '#\\S+'
				let check = new RegExp(regex, 'ig')
				return string.toString().replace(check, function (matchedText, a, b) {
					if (matchedText.slice(-1) === ',') {
						return ('<strong>' + matchedText.substring(0, matchedText.lastIndexOf(',')) + '</strong>,')
					}
					return ('<strong>' + matchedText + '</strong>')
				})
			}
		}
	}
</script>

<style scoped>
	.fa {
		background: transparent;
	}
	
	#rop_core .vertical-align {
		align-items: flex-end;
	}
	
	#rop_core figure.figure {
		margin: -.7em -2em -1em 0;
	}
	
	@media (max-width: 600px) {
		#rop_core .vertical-align {
			align-items: center;
		}
		
		#rop_core figure.figure {
			margin: 10px auto 0;
		}
	}
</style>
