<template>
	<div class="tile tile-centered">
		<div class="tile-icon">
			<div class="icon_box" :class="service">
				<img class="service_account_image" :src="img" v-if="img"/>
				<i class="fa  " :class="icon" aria-hidden="true"></i>
			</div>
		</div>
		<div class="tile-content">
			<div class="tile-title">{{ user }}</div>
			<div class="tile-subtitle text-gray">{{ serviceInfo }}</div>
		</div>
		<div class="tile-action" v-if="!is_loading">
			<div class="form-group">
				<label class="form-switch">
					<input type="checkbox" v-model="account_data.active" @change="startToggleAccount( account_id, type )"/>
					<i class="form-icon"></i>
				</label>
			</div>
		</div>
	</div>
</template>

<script>

	import Vue from 'vue'

	module.exports = {
		name: 'service-user-tile',
		props: ['account_data', 'account_id'],
		computed: {
			type: function () {
				return this.account_data.active === true ? 'active' : 'inactive';
			},
			is_loading: function () {
				return this.$store.state.ajaxLoader;
			},
			service: function () {
				let iconClass = this.account_data.service;
				if (this.img !== '') {
					iconClass = iconClass.concat(' ').concat('has_image')
				} else {
					iconClass = iconClass.concat(' ').concat('no-image')
				}
				return iconClass
			},
			icon: function () {
				let serviceIcon = 'fa-';
				if (this.account_data.service === 'facebook') serviceIcon = serviceIcon.concat('facebook-official');
				if (this.account_data.service === 'twitter') serviceIcon = serviceIcon.concat('twitter');
				if (this.account_data.service === 'linkedin') serviceIcon = serviceIcon.concat('linkedin');
				if (this.account_data.service === 'tumblr') serviceIcon = serviceIcon.concat('tumblr');
				return serviceIcon
			},
			img: function () {
				let img = '';
				if (this.account_data.img !== '' && this.account_data.img !== undefined) {
					img = this.account_data.img
				}
				return img
			},
			user: function () {
				return this.account_data.user
			},
			serviceInfo: function () {
				return this.account_data.account.concat(' at: ').concat(this.account_data.created)
			}
		},
		methods: {
			toggleAccount: function (id, type) {
				let parts = id.split('_');
				if (parts.length !== 3) {
					Vue.$log.error('Invalid id format for active account ', id);
					return;
				}
				let service_id = parts[0] + '_' + parts[1];

				this.$store.state.authenticatedServices[service_id].available_accounts[id].active = (type !== 'inactive');
				if (type === 'inactive') {
					Vue.delete(this.$store.state.activeAccounts, id);
				} else {
					Vue.set(this.$store.state.activeAccounts, id, this.$store.state.authenticatedServices[service_id].available_accounts[id]);
				}
				this.$store.dispatch('fetchAJAXPromise', {
					req: 'toggle_account',
					data: {account_id: id, state: type}
				}).then(response => {
					this.$store.dispatch('fetchAJAX', {req: 'get_queue'});
					this.$store.dispatch('fetchAJAX', {req: 'get_authenticated_services'})
				}, error => {
					Vue.$log.error('Got nothing from server. Prompt user to check internet connection and try again', error)
				})
			},
			startToggleAccount(id, type) {
				Vue.$log.info('Toggle account', id, type);
				setTimeout(function () { this.toggleAccount(id, type) }.bind(this), 1000);
			}
		}
	}
</script>

<style scoped>
	
	.has_image {
		border-radius: 50%;
	}
	
	.service_account_image {
		width: 150%;
		border-radius: 50%;
		margin-left: -25%;
		margin-top: -25%;
	}
	
	.icon_box {
		width: 45px;
		height: 45px;
		padding: 7px;
		text-align: center;
		background-color: #333333;
		color: #efefef;
		position: relative;
	}
	
	.icon_box.has_image .fa {
		position: absolute;
		bottom: 0px;
		right: 0px;
		padding: 4px;
		border-radius: 50%;
		font-size: 0.7em;
	}
	
	.icon_box.no-image > .fa {
		width: 30px;
		height: 30px;
		
		font-size: 30px;
	}
	
	.facebook, .fa-facebook-official {
		background-color: #3b5998;
	}
	
	.twitter, .fa-twitter {
		background-color: #55acee;
	}
	
	.linkedin, .fa-linkedin {
		background-color: #007bb5;
	}
	
	.tumblr, .fa-tumblr {
		background-color: #32506d;
	}

</style>