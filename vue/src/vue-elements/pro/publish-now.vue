<template>
	<div id="rop_core" class="rop-control-container">

    <!-- Share on update -->
				<div class="columns py-2">
					<div class="column col-9 col-sm-9 vertical-align text-left">
						<div class="form-group">
							<label class="form-checkbox">
								<input type="checkbox" :checked="share_on_update == true" name="share_on_update"/>
								<i class="form-icon"></i> {{labels.share_on_update}}
							</label>
						</div>
						<div class="form-group" v-if=" share_on_update " v-for="account in accounts">
							<label class="form-checkbox">
								<input type="checkbox"/>
								<i class="form-icon"></i> {{labels.share_on_update}}
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
			}
		},
		components: {
			ButtonCheckbox
		}
	}
</script>
