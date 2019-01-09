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
					<input type="checkbox" :checked="(active != null && active.indexOf(key) >= 0) || share_on_update_enabled" :value="key"
					       name="publish_now_accounts[]"/>
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
			share_on_update_enabled: function () {
				return this.$store.state.publish_now.action === true;
			}
		},
		data: function () {

			return {
				labels: this.$store.state.labels.publish_now,
				accounts: this.$store.state.publish_now.accounts,
				active: this.$store.state.publish_now.active,
				share_on_update_enabled: this.$store.state.publish_now.action
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
				if (service === 'reddit') serviceIcon = serviceIcon.concat('reddit')

				return serviceIcon;
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
