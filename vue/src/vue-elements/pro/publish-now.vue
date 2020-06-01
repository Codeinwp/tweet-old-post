<template>
	<div class="rop-control-container" v-if="Object.keys(accounts).length > 0" >

		<!-- Share on publish/update -->
		<fieldset>
			<label class="form-checkbox">
				<input type="checkbox" :checked="share_on_update_enabled" v-on:click="share_on_update_enabled = !share_on_update_enabled" name="publish_now" value="1"/>
				<span v-html=" labels.share_on_update"></span>
			</label>

			<div class="form-group rop-publish-now-accounts-wrapper" v-if="share_on_update_enabled" v-for="(account, key) in accounts" :id="key">
				<label class="form-checkbox rop-publish-now-account" :id="key">
					<input type="checkbox" :checked="share_on_update_enabled" :value="key" name="publish_now_accounts[]" class="rop-account-names"/>
					<i class=" fa " :class="getServiceClass(account.service)"></i> {{account.user}}
				</label>
				<span style="text-decoration: underline; color: #0073aa;font-style:italic; cursor: pointer;" v-on:click="getTheClick(key)" :id="key">edit caption</span>
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
				share_on_update_enabled: this.$store.state.publish_now.action,
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

			getTheClick: function(value){

				let edit_caption_span = document.querySelector(`span#${value}`);

				let custom_caption_textarea = `
	<p id="${value}" style="margin-left:15px">	Custom Caption:
	<textarea name="${value}" class="rop-custom-captions" style="width:100%;"></textarea>
	 </p>
				`;

				let textarea = document.querySelector(`p#${value}`);

				if( textarea === null  || textarea === undefined){
				edit_caption_span.insertAdjacentHTML('afterend', custom_caption_textarea);
			}else{
					textarea.remove();
			}
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
