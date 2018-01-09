<template>
	<div class="card col-12" style="max-width: 100%; min-height: 350px;">
		<div style="position: absolute; display: block; top: 0; right: 0;">
			<button class="btn btn-sm btn-primary" @click="toggleEditState" v-if="edit === false" :disabled="!has_pro"><i class="fa fa-pencil" aria-hidden="true"></i> Edit</button>
			<button class="btn btn-sm btn-success" @click="saveChanges" v-if="edit" :disabled="!has_pro"><i class="fa fa-check" aria-hidden="true"></i> Save</button>
			<button class="btn btn-sm btn-warning" @click="cancelChanges" v-if="edit" :disabled="!has_pro"><i class="fa fa-times" aria-hidden="true"></i> Cancel</button>
		</div>
		<div class="card-header">
			<p class="text-gray text-right float-right"><b>Scheduled:</b><br/>{{time}}</p>
			<div class="card-title h6">{{post.post_title}}</div>
			<div class="card-subtitle text-gray"><i class="service fa" :class="iconClass( account_id )"></i> {{active_accounts[account_id].account}}</div>
		</div>
		<hr/>
		<span v-if="edit === false">
			<details class="accordion" v-if="post_img_url !== ''">
				<summary class="accordion-header">
					<i class="fa fa-file-image-o"></i>
					Image Preview
				</summary>
				<div class="accordion-body">
					<div class="card-image" v-if="post_img_url !== ''">
						<figure class="figure" style="max-height: 250px; overflow: hidden;">
							<img :src="post_img_url" class="img-fit-cover" style=" width: 100%; height: 250px;" @error="brokenImg">
						</figure>
					</div>
				</div>
			</details>
			<details class="accordion" v-else>
				<summary class="accordion-header">
					<i class="fa fa-file-image-o"></i>
					No Image
				</summary>
				<div class="accordion-body text-gray">
					<small>
						<i class="fa fa-chain-broken" aria-hidden="true"></i> No image attached or a broken link was detected.<br/>
						<i class="fa fa-info-circle" aria-hidden="true"></i> <i>If a image should be here, update the post or edit this item.</i>
					</small>
				</div>
			</details>

			<div class="card-body" v-if="edit === false">
				<p v-html="hashtags( post_content )"></p>
				<p v-if="post.post_url"><b>Link:</b> <a :href="post.post_url" target="_blank">{{post.post_url}}</a></p>
			</div>
		</span>
		<div class="card-body" v-else>
			<div class="form-group">
				<label class="form-label" for="image">Image</label>
				<div class="input-group">
					<span class="input-group-addon"><i class="fa fa-file-image-o"></i></span>
					<input id="image" type="text" class="form-input" :value="post_img_url" readonly>
					<button class="btn btn-primary input-group-btn" @click="uploadImage"><i class="fa fa-upload" aria-hidden="true"></i></button>
					<button class="btn btn-danger input-group-btn" @click="clearImage"><i class="fa fa-trash" aria-hidden="true"></i></button>
				</div>

				<label class="form-label" for="content">Content</label>
				<textarea class="form-input" id="content" placeholder="Textarea" rows="3" @keyup="checkCount">{{post_content}}</textarea>
			</div>
		</div>
		<div style="position: absolute; display: block; bottom: 0; right: 0;" v-if="edit === false">
			<button class="btn btn-sm btn-success" @click="publishNow" :disabled="!has_pro"><i class="fa fa-share" aria-hidden="true"></i> Share Now</button>
			<button class="btn btn-sm btn-warning" @click="skipPost" :disabled="!has_pro"><i class="fa fa-step-forward" aria-hidden="true"></i> Skip</button>
			<button class="btn btn-sm btn-danger" @click="blockPost" :disabled="!has_pro"><i class="fa fa-ban" aria-hidden="true"></i> Block</button>
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
			account_id: {
				default: '',
				type: String
			},
			post: {
				default: function () {
					return {}
				},
				type: Object
			},
			time: {
				default: '',
				type: String
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
			has_pro: function () {
				return this.$store.state.has_pro
			},
			post_defaults: function () {
				return JSON.parse( JSON.stringify( this.post ) ) // This removes the observable/reactivity
			},
			post_content: function () {
				if ( this.post_edit.custom_content !== '' ) {
					return this.post_edit.custom_content
				}
				return this.post_edit.post_content
			},
			active_accounts: function () {
				return this.$store.state.activeAccounts
			},
			post_img_url: function () {
				if ( this.post_edit.post_img !== false ) {
					return this.post_edit.post_img
				}
				return ''
			}
		},
		watch: {
		},
		methods: {
			publishNow: function () {
				this.$store.dispatch( 'fetchAJAX', { req: 'publish_queue_event', data: { account_id: this.post_edit.account_id, index: this.id } } )
			},
			skipPost: function () {
				this.$store.dispatch( 'fetchAJAX', { req: 'skip_queue_event', data: { account_id: this.post_edit.account_id, index: this.id } } )
			},
			blockPost: function () {
				this.$store.dispatch( 'fetchAJAX', { req: 'block_queue_event', data: { account_id: this.post_edit.account_id, index: this.id } } )
			},
			toggleEditState: function () {
				this.edit = !this.edit
			},
			checkCount: function ( evt ) {
				this.post_edit.custom_content = ''
				if ( this.post_edit.post_content !== evt.srcElement.value ) {
					this.post_edit.custom_content = evt.srcElement.value
				}
			},
			saveChanges: function () {
				this.$store.dispatch( 'fetchAJAX', { req: 'update_queue_event', data: { account_id: this.post_edit.account_id, post_id: this.post_edit.post_id, custom_data: this.post_edit } } )
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
				let window = wp.media( {
					title: 'Insert a media',
					library: {
						type: 'image'
					},
					multiple: false,
					button: {text: 'Insert'}
				} )

				let self = this
				window.on( 'select', function () {
					let first = window.state().get( 'selection' ).first().toJSON()
					console.log( first )
					self.post_edit.post_img = first.url
					self.custom_img = true
				} )

				window.open()
			},
			iconClass: function ( accountId ) {
				let serviceIcon = 'fa-user'
				if ( accountId !== null ) {
					serviceIcon = 'fa-'
					let account = this.active_accounts[accountId]
					if ( account !== undefined && account.service === 'facebook' ) serviceIcon = serviceIcon.concat( 'facebook-official facebook' )
					if ( account !== undefined && account.service === 'twitter' ) serviceIcon = serviceIcon.concat( 'twitter twitter' )
					if ( account !== undefined && account.service === 'linkedin' ) serviceIcon = serviceIcon.concat( 'linkedin linkedin' )
					if ( account !== undefined && account.service === 'tumblr' ) serviceIcon = serviceIcon.concat( 'tumblr tumblr' )
				}
				return serviceIcon
			},
			brokenImg: function () {
				console.log( 'Image is broken' )
				this.post.post_img = false
			},
			hashtags: function ( string ) {
				let regex = '#\\S+'
				let check = new RegExp( regex, 'ig' )
				return string.toString().replace( check, function ( matchedText, a, b ) {
					if ( matchedText.slice( -1 ) === ',' ) {
						return ( '<strong>' + matchedText.substring( 0, matchedText.lastIndexOf( ',' ) ) + '</strong>,' )
					}
					return ( '<strong>' + matchedText + '</strong>' )
				} )
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

	#rop_core .btn-warning {
		background-color: #ef6c00;
		border-color: #e65100;
		color: #FFF;
	}

	#rop_core .btn-warning:hover, #rop_core .btn-warning:focus {
		border-color: #e65100;
		background-color: #fff;
		color: #ef6c00;
	}

	#rop_core .btn-warning.active, #rop_core .btn-warning:active {
		background-color: #e65100;
		border-color: #ef6c00;
	}

	#rop_core .btn-danger {
		 background-color: #c62828;
		 border-color: #b71c1c;
		 color: #FFF;
	 }

	#rop_core .btn-danger:hover, #rop_core .btn-danger:focus {
		border-color: #b71c1c;
		background-color: #fff;
		color: #c62828;
	}

	#rop_core .btn-danger.active, #rop_core .btn-danger:active {
		background-color: #b71c1c;
		border-color: #c62828;
	}

	#rop_core .btn-success {
		background-color: #8bc34a;
		border-color: #33691e;
		color: #FFF;
	}

	#rop_core .btn-success:hover, #rop_core .btn-success:focus {
		border-color: #33691e;
		background-color: #fff;
		color: #8bc34a;
	}

	#rop_core .btn-success.active, #rop_core .btn-success:active {
		background-color: #33691e;
		border-color: #8bc34a;
	}
</style>