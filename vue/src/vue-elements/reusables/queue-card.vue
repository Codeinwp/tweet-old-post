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
				<button class="btn btn-sm btn-success" @click="saveChanges" v-if="edit" :disabled=" ! enabled">
					<i class="fa fa-check" aria-hidden="true"></i>
					Save
				</button>
				<button class="btn btn-sm btn-warning" @click="cancelChanges" v-if="edit" :disabled=" ! enabled">
					<i class="fa fa-times" aria-hidden="true"></i>
					Cancel
				</button>
			</div>
		</div>
		<div class="card-body columns">
			<div class="column col-9">
				<p v-html="hashtags( content.content )"></p>
				
			</div>
			<div class=" olumn col-3 text-right">
				<figure class="figure" v-if="content.post_image !== ''">
					<img :src="content.post_image" class="img-fit-cover"  style="max-height:50px">
				</figure>
				<summary v-else>
					<i class="fa fa-file-image-o"></i>
					No Image
				</summary>
				<p>
					<b>Link:</b> <a :href="content.post_url" target="_blank" class="tooltip"
					                data-tooltip="Link shortned the selected service">{{'{' + content.short_url_service + '}'}}</a>
				</p>
			</div>
			
			<!--<div class="card-body">-->
			<!--<div class="form-group">-->
			<!--<label class="form-label" for="image">Image</label>-->
			<!--<div class="input-group">-->
			<!--<span class="input-group-addon"><i class="fa fa-file-image-o"></i></span>-->
			<!--<input id="image" type="text" class="form-input" :value="card_data.post_image" readonly>-->
			<!--<button class="btn btn-primary input-group-btn" @click="uploadImage"><i class="fa fa-upload"-->
			<!--aria-hidden="true"></i>-->
			<!--</button>-->
			<!--<button class="btn btn-danger input-group-btn" @click="clearImage"><i class="fa fa-trash"-->
			<!--aria-hidden="true"></i>-->
			<!--</button>-->
			<!--</div>-->
			<!---->
			<!--<label class="form-label" for="content">Content</label>-->
			<!--<textarea class="form-input" id="content" placeholder="Textarea" rows="3" @keyup="checkCount">{{card_data.content}}</textarea>-->
			<!--</div>-->
			<!--</div>-->
		</div>
		<div class="card-top-footer columns" v-if="edit === false">
			<button class="btn btn-sm btn-success" @click="publishNow" :disabled=" ! enabled">
				<i class="fa fa-share" aria-hidden="true"></i>
				Share Now
			</button>
			<button class="btn btn-sm btn-warning" @click="skipPost" :disabled=" ! enabled">
				<i class="fa fa-step-forward" aria-hidden="true"></i>
				Skip
			</button>
			<button class="btn btn-sm btn-danger" @click="blockPost" :disabled=" ! enabled">
				<i class="fa fa-ban" aria-hidden="true"></i>
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
				post_edit: this.post
				// post_defaults: JSON.parse( JSON.stringify( this.post ) ) // This removes the observable/reactivity
			}
		},
		computed: {
			// post_defaults: function () {
			// 	return JSON.parse(JSON.stringify(this.post)) // This removes the observable/reactivity
			// },
			content: function () {
				if (typeof this.card_data.content !== 'undefined') {
					return this.card_data.content
				}
				return {}
			},
			active_accounts: function () {
				return this.$store.state.activeAccounts
			},
			// post_img_url: function () {
			// 	if (this.post_edit.post_img !== false) {
			// 		return this.post_edit.post_img
			// 	}
			// 	return ''
			// }
		},
		mounted: function () {
			//console.log(this.card_data);
		},
		watch: {},
		methods: {
			publishNow: function () {
				this.$store.dispatch('fetchAJAX', {
					req: 'publish_queue_event',
					data: {account_id: this.post_edit.account_id, index: this.id}
				})
			},
			skipPost: function () {
				this.$store.dispatch('fetchAJAX', {
					req: 'skip_queue_event',
					data: {account_id: this.post_edit.account_id, index: this.id}
				})
			},
			blockPost: function () {
				this.$store.dispatch('fetchAJAX', {
					req: 'block_queue_event',
					data: {account_id: this.post_edit.account_id, index: this.id}
				})
			},
			toggleEditState: function () {
				this.edit = !this.edit
			},
			checkCount: function (evt) {
				this.post_edit.custom_content = ''
				if (this.post_edit.post_content !== evt.srcElement.value) {
					this.post_edit.custom_content = evt.srcElement.value
				}
			},
			saveChanges: function () {
				this.$store.dispatch('fetchAJAX', {
					req: 'update_queue_event',
					data: {
						account_id: this.post_edit.account_id,
						post_id: this.post_edit.post_id,
						custom_data: this.post_edit
					}
				})
				this.toggleEditState()
			},
			cancelChanges: function () {
				this.post_edit = this.post_defaults
				this.toggleEditState()
			},
			clearImage: function () {
				this.post_edit.post_img = false
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
					self.post_edit.post_img = first.url
					self.custom_img = true
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
