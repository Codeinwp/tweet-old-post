<template>
	<div id="rop_core" class="rop-control-container">

    <!-- Share on update -->
				<div class="columns py-2">
					<div class="column col-9 col-sm-9 vertical-align text-left">
						<div class="form-group">
							<label class="form-checkbox">
								<input type="checkbox" :checked="share_on_update_enabled" v-on:click="share_on_update_enabled = !share_on_update_enabled" name="publish_now" value="1"/>
								<i class="form-icon"></i> {{labels.share_on_update}}
							</label>
						</div>
						<div class="form-group" v-if="share_on_update_enabled" v-for="(account, key) in accounts">
							<label class="form-checkbox">
								<input type="checkbox" :checked="active != null && active.indexOf(key) >= 0" :value="key" name="publish_now_accounts[]"/>
								<i class="form-icon"></i> {{account.account}} ({{account.service}})
							</label>
						</div>
					</div>
				</div>


	</div>
</template>

<script>
	import ButtonCheckbox from '../reusables/button-checkbox.vue'

	module.exports = {
		name: 'publish-now',
  created() {
  },
		computed: {
			share_on_update: function () {
				return this.$store.state.publish_now.action === true;
			},
  },
		data: function () {
			return {
				labels: this.$store.state.labels.publish_now,
    accounts: this.$store.state.publish_now.accounts,
    active: this.$store.state.publish_now.active,
    share_on_update_enabled: this.$store.state.publish_now.action === true,
			}
		},
		components: {
			ButtonCheckbox
		}
	}
</script>
