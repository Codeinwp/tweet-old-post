<template>
	<div class="rop-control-container" v-if="Object.keys(accounts).length > 0" >

		<!-- Share on publish/update -->
		<fieldset>
			<label class="form-checkbox">
				<input type="checkbox" :checked="share_on_update_enabled" v-on:click="share_on_update_enabled = !share_on_update_enabled" name="publish_now" value="1"/>
				<span v-html=" labels.share_on_update"></span>
			</label>

			<div class="form-group rop-publish-now-accounts-wrapper" v-if="share_on_update_enabled" v-for="(account, key) in accounts" :id="key" v-bind:key="key">
				<label class="form-checkbox rop-publish-now-account" :id="key">
					<input type="checkbox" :checked="share_on_update_enabled" :value="key" v-on:click="toggleServices($event, key)" name="publish_now_accounts[]" class="rop-account-names"/>
					<i class=" fa " :class="getServiceClass(account.service)"></i> {{account.user}}
				</label>
				<span v-on:click="togglefields(key)" :id="key" class="rop-edit-message-text">{{ showField[key] ? 'done' : 'edit message' }}</span>
				<textarea v-show="showField[key]" :name="key" class="rop-custom-message-area"></textarea>
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
			var fields = {};
			Object.keys( this.$store.state.publish_now.accounts ).forEach( e => {
				fields[e] = false;
			} );

			return {
				labels: this.$store.state.labels.publish_now,
				accounts: this.$store.state.publish_now.accounts,
				share_on_update_enabled: this.$store.state.publish_now.action,
				showField: fields
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

			toggleServices: function(event, value){
				var self = this;
				if( event.target.checked ) {
					return;
				}

				return self.showField[value] = false;
			},

			togglefields: function(value){
				var self = this;
				return self.showField[value] = ! self.showField[value];
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
	.rop-edit-message-text{
		text-decoration: underline;
		color: #0073aa;
		font-size: 12px;
		font-style:italic;
		cursor: pointer;
	}
	.rop-publish-now-account, .rop-custom-message-area{
		margin: 5px 0 10px 16px
	}
	.rop-publish-now-accounts-wrapper{
		margin-top:5px;
	}
</style>
