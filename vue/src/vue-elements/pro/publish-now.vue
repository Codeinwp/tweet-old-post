<template>
	<div class="rop-control-container" v-if="Object.keys(accounts).length > 0" >

		<!-- Share on update -->
		<fieldset>
			<input type="checkbox" :checked="share_on_update_enabled"
			       v-on:click="share_on_update_enabled = !share_on_update_enabled" name="publish_now" value="1"/>
			<label class="form-checkbox">

				  <span v-html=" labels.share_on_update"></span>
			</label>

			<div class="form-group rop-publish-now-accounts-wrapper" v-if="share_on_update_enabled" v-for="(account, key) in accounts">
				<label class="form-checkbox rop-publish-now-account">
					<input type="checkbox" :checked="(active != null && active.indexOf(key) >= 0) || (share_on_update_enabled)" v-on:click="getTheClick(key)" :value="key"
					       name="publish_now_accounts[]" class="account-names"/>
					<!-- <input type="checkbox" :checked="(active != null && active.indexOf(key) >= 0) || (share_on_update_enabled)" v-on:click="getTheClick(key)" :value="key"
					       name="publish_now_accounts[]" /> -->
								 <input type="text" v-show="showArea()" :name="key" value=""/>
								 <!-- <input type="text" v-if="show_input" :name="key" value="SHOW INPUT"/> -->
					<i class=" fa " :class="getServiceClass(account.service)"></i> {{account.user}}
				</label>
			</div>
		</fieldset>

	</div>
</template>

<script>
	import ButtonCheckbox from '../reusables/button-checkbox.vue'

	module.exports = {
		name: 'publish-now',
		created() {
		},
		computed: {
		},
		data: function () {


			return {
				labels: this.$store.state.labels.publish_now,
				accounts: this.$store.state.publish_now.accounts,
				active: this.$store.state.publish_now.active,
				share_on_update_enabled: this.$store.state.publish_now.action,
				show_input: false,
			}

		},
		components: {
			ButtonCheckbox
		},
		methods: {
			getServiceClass: function (service) {

				let serviceIcon = 'fa-'
				if (service === 'facebook') serviceIcon = serviceIcon.concat('facebook')
				if (service === 'twitter') serviceIcon = serviceIcon.concat('twitter')
				if (service === 'linkedin') serviceIcon = serviceIcon.concat('linkedin')
				if (service === 'tumblr') serviceIcon = serviceIcon.concat('tumblr')
				if (service === 'pinterest') serviceIcon = serviceIcon.concat('pinterest')

				return serviceIcon;
			},
			showArea: function(show = false){
				console.log('got into show area ' + show);
				this.show_input = true;
				console.log('got passed this.show_input ' + show);
				return show;
			},

			getTheClick: function(value){

				console.log(value)
				const field = document.querySelectorAll(".account-names")
				console.log(field);

				var self = this;

				field.forEach(function(account){
					const account_id = account.value;
					if( value === account_id ){
						console.log( 'got into if');
						console.log( 'account id: ' + account_id);
						// return show_input = true;

						self.showArea(true);
					}

				});

			},

		}
	}
</script>
<style>
	.rop-publish-now-branding{
		text-align: right;
		width:100%;
		float:right;
	}
	.rop-publish-now-account{
		margin-left: 17px;
	}
	.rop-publish-now-accounts-wrapper{
		margin-top:5px;
	}
</style>
