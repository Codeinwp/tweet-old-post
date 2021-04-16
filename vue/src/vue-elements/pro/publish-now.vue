<template>
	<div class="rop-control-container">
			<p v-if="Object.keys(accounts).length < 1">{{labels.add_account_to_use_instant_share}}</p>
		<!-- Share on publish/update -->
		<fieldset v-if="Object.keys(accounts).length > 0">
			<label class="form-checkbox">
				<input type="checkbox" :checked="share_on_update_by_default" v-on:click="toggle_accounts = !toggle_accounts" name="publish_now" value="1"/>
				<span v-html=" labels.share_on_update"></span>
			</label>

			<div class="form-group rop-publish-now-accounts-wrapper" v-if="toggle_accounts" v-for="(account, key) in accounts" :id="key" v-bind:key="key">
				<label class="form-checkbox rop-publish-now-account" :id="key">
					<input type="checkbox" :checked="share_on_update_by_default && !choose_accounts_manually" :value="key" v-on:click="toggleServices($event, key)" name="publish_now_accounts[]" class="rop-account-names"/>
					<i class=" fa " :class="getServiceClass(account.service)"></i> {{account.user}}
				</label>
				<span v-on:click="togglefields(key)" :id="key" class="rop-edit-custom-instant-share-message-text">{{ showField[key] ? 'done' : 'edit message' }}</span>
				<p v-show="showField[key]" class="rop-custom-instant-share-message-text">Custom share message:</p>
				<textarea v-show="showField[key]" :name="key" :disabled="!isPro" class="rop-custom-instant-share-message-area"></textarea>
				<p v-if="!isPro && showField[key]" v-html="labels.custom_instant_share_messages_upsell" class="custom-instant-share-upsell"></p>
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
				license: this.$store.state.licence,
				labels: this.$store.state.labels.publish_now,
				accounts: this.$store.state.publish_now.accounts,
				share_on_update_enabled: this.$store.state.publish_now.instant_share_enabled,
				share_on_update_by_default: this.$store.state.publish_now.instant_share_by_default,
				choose_accounts_manually: this.$store.state.publish_now.choose_accounts_manually,
				showField: fields,
				toggle_accounts: this.$store.state.publish_now.instant_share_by_default,
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
				if (service === 'vk') serviceIcon = serviceIcon.concat('vk')
				if (service === 'gmb') serviceIcon = serviceIcon.concat('google')

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

		},
		computed: {
			isPro: function () {
					return (this.license > 0);
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
	.rop-edit-custom-instant-share-message-text{
		text-decoration: underline;
		color: #0073aa;
		font-size: 12px;
		font-style:italic;
		cursor: pointer;
	}
	.rop-publish-now-account, .rop-custom-instant-share-message-area{
		margin: 0 0 0 16px;
	}
	.custom-instant-share-upsell{
		color: #808080;
		margin: 0 0 12px 16px;
	}
	.rop-custom-instant-share-message-text{
		margin: 5px 0 5px 16px;
		font-style: italic;
	}
	.rop-publish-now-accounts-wrapper{
		margin-top:10px;
	}
</style>
