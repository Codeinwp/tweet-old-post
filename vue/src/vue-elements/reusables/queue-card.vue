<template>
	<div class="card col-12 rop-queue-post" style="max-width: 100%; min-height: 100px;">
		<div class="card-top-header columns">
			<div class="column col-6">
				<p class="text-gray text-left "><i class="fa fa-clock-o"></i> {{card_data.date}} <b><i
						class="fa fa-at"></i></b> <i class="service fa"
				                                     :class="iconClass( card_data.account_id )"></i>
					{{active_accounts[card_data.account_id].user}}</p>
			</div>
			<div class="column col-6 text-right">
				<button class="btn btn-sm btn-primary" @click="toggleEditState" v-if="edit === false"
				        :disabled=" ! enabled">
					<i class="fa fa-pencil" aria-hidden="true"></i> Edit
				</button>
				<button class="btn btn-sm btn-success" @click="saveChanges(card_data.account_id, card_data.post_id)"
				        v-if="edit" :disabled=" ! enabled">
					<i class="fa fa-spinner fa-spin" v-if=" is_loading === 'edit'"></i>
					<i class="fa fa-check" aria-hidden="true" v-else></i>
					Save
				</button>
				<button class="btn btn-sm btn-warning" @click="cancelChanges" v-if="edit" :disabled=" ! enabled">
					<i class="fa fa-times" aria-hidden="true"></i>
					Cancel
				</button>
			</div>
		</div>
		<div class="card-body columns" v-if="! edit ">
			<div class="column col-9">
				<p v-html="hashtags( content.content )"></p>
			
			</div>
			<div class="column col-3 text-right">
				<figure class="figure" v-if="content.post_image !== ''">
					<img :src="content.post_image" class="img-fit-cover" style="max-height:50px">
				</figure>
				<summary v-else>
					<i class="fa fa-file-image-o"></i>
					No Image
				</summary>
				<p>
					<b>Link:</b>
					<a :href="content.post_url" target="_blank" class="tooltip"
					   :data-tooltip="'Link shortned using ' + content.short_url_service +' service'">
						{{'{' + content.short_url_service + '}'}}</a>
				</p>
			</div>
		
		</div>
		<div class="card-body  columns" v-if="edit">
			<div class="form-group column col-12">
				<label class="form-label" for="image">Image</label>
				<div class="input-group">
					<span class="input-group-addon"><i class="fa fa-file-image-o"></i></span>
					<input id="image" type="text" class="form-input" :value="content.post_image" readonly>
					<button class="btn btn-primary input-group-btn" @click="uploadImage">
						<i class="fa fa-upload" aria-hidden="true"></i>
					</button>
				</div>
				<label class="form-label" for="content">Content</label>
				<textarea class="form-input" id="content" placeholder="Textarea" rows="3" @keyup="checkCount">{{content.content}}</textarea>
			</div>
		</div>
		<div class="card-top-footer columns" v-if="edit === false">
			<button class="btn btn-sm btn-warning tooltip tooltip-right"
			        @click="skipPost(card_data.account_id, card_data.post_id)"
			        data-tooltip="Reschedule this post."
			        :disabled=" ! enabled">
				<i class="fa fa-spinner fa-spin" v-if=" is_loading === 'skip'"></i>
				<i class="fa fa-step-forward" v-else aria-hidden="true"></i>
				Skip
			</button>
			<button class="btn btn-sm btn-danger tooltip  tooltip-right"
			        data-tooltip="Ban this post from sharing in the future."
			        @click="blockPost(card_data.account_id, card_data.post_id)" :disabled=" ! enabled">
				<i class="fa fa-spinner fa-spin" v-if=" is_loading === 'block'"></i>
				<i class="fa fa-ban" aria-hidden="true" v-else></i>
				Block
			</button>
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
					title: 'Insert a media',
					library: {
						type: 'image'
					},
					multiple: false,
					button: {text: 'Insert'}
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
			iconClass: function (accountId) {
				let serviceIcon = 'fa-user'
				if (accountId !== null) {
					serviceIcon = 'fa-'
					let account = this.active_accounts[accountId]
					if (account !== undefined && account.service === 'facebook') serviceIcon = serviceIcon.concat('facebook-official facebook')
					if (account !== undefined && account.service === 'twitter') serviceIcon = serviceIcon.concat('twitter twitter')
					if (account !== undefined && account.service === 'linkedin') serviceIcon = serviceIcon.concat('linkedin linkedin')
					if (account !== undefined && account.service === 'tumblr') serviceIcon = serviceIcon.concat('tumblr tumblr')
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
