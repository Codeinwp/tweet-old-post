<template>
	<div class="tab-view">
		<div class="panel-body" style="overflow: inherit;">
			<h3>Sharing Queue</h3>
			<div class="container columns">
				<div class="column col-sm-12 col-3 text-left" v-for=" (data, index) in queue ">
					<div class="card col-12" style="max-width: 100%; min-height: 350px;">
						<div class="card-header">
							<p class="text-gray text-right float-right"><b>Scheduled:</b><br/>{{data.time}}</p>
							<div class="card-title h6">{{data.post.post_title}}</div>
							<div class="card-subtitle text-gray"><i class="service fa" :class="iconClass( data.account_id )"></i> {{active_accounts[data.account_id].account}}</div>
						</div>
						<hr/>
						<details class="accordion" v-if="data.post.post_img">
							<summary class="accordion-header">
								<i class="fa fa-file-image-o"></i>
								Image Preview
							</summary>
							<div class="accordion-body">
								<div class="card-image" v-if="data.post.post_img">
									<figure class="figure" style="max-height: 250px; overflow: hidden;">
										<img :src="data.post.post_img" class="img-fit-cover" style=" width: 100%; height: 250px;" @error="brokenImg(index)">
									</figure>
								</div>
							</div>
						</details>

						<div class="card-body">
							<p v-html="hashtags( data.post.post_content )"></p>
						</div>
						<div class="card-footer text-center">
							<button class="btn btn-primary"><i class="fa fa-refresh" aria-hidden="true"></i> Update</button>
							<button class="btn btn-warning"><i class="fa fa-step-forward" aria-hidden="true"></i> Skip</button>
							<button class="btn btn-danger"><i class="fa fa-ban" aria-hidden="true"></i> Block</button>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="panel-footer">
			<button class="btn btn-primary" @click="saveSchedule()"><i class="fa fa-check"></i> Save Schedule</button>
			<button class="btn btn-secondary" @click="resetSchedule()"><i class="fa fa-ban"></i> Reset to Defaults</button>
		</div>
	</div>
</template>

<script>
	import ButtonCheckbox from './reusables/button-checkbox.vue'
	import VueTimepicker from 'vue2-timepicker'

	module.exports = {
		name: 'queue-view',
		data: function () {
			let key = null
			if ( Object.keys( this.$store.state.activeAccounts )[0] !== undefined ) key = Object.keys( this.$store.state.activeAccounts )[0]
			return {
				selected_account: key
			}
		},
		mounted: function () {
			// Uncomment this when not fixed tab on schedule
			// this.getAccountSchedule()
		},
		filters: {
			capitalize: function ( value ) {
				if ( !value ) return ''
				value = value.toString()
				return value.charAt( 0 ).toUpperCase() + value.slice( 1 )
			}
		},
		computed: {
			queue: function () {
				return this.$store.state.queue
			},
			active_accounts: function () {
				return this.$store.state.activeAccounts
			},
			img: function () {
				let img = ''
				if ( this.selected_account !== null && this.active_accounts[this.selected_account].img !== '' && this.active_accounts[this.selected_account].img !== undefined ) {
					img = this.active_accounts[this.selected_account].img
				}
				return img
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
		methods: {
			iconClass: function ( accountId ) {
				let serviceIcon = 'fa-user'
				if ( accountId !== null ) {
					serviceIcon = 'fa-'
					let account = this.active_accounts[accountId]
					if ( account.service === 'facebook' ) serviceIcon = serviceIcon.concat( 'facebook-official facebook' )
					if ( account.service === 'twitter' ) serviceIcon = serviceIcon.concat( 'twitter twitter' )
					if ( account.service === 'linkedin' ) serviceIcon = serviceIcon.concat( 'linkedin linkedin' )
					if ( account.service === 'tumblr' ) serviceIcon = serviceIcon.concat( 'tumblr tumblr' )
				}
				return serviceIcon
			},
			brokenImg: function ( index ) {
				console.log( 'Image is broken' )
				this.queue[index].post.post_img = false
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
		},
		components: {
			ButtonCheckbox,
			VueTimepicker
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