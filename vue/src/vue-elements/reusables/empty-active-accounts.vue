<template>
  <div class="empty">
    <div class="empty-icon">
      <i class="fa fa-3x fa-user-circle-o" />
    </div>
    <p class="empty-title h5">
      {{ labels.no_active_accounts }}
    </p>
    <p
      class="empty-subtitle"
      v-html="labels.no_active_accounts_desc"
    />
    <button
      class="btn btn-primary"
      @click="goToAccounts()"
    >
      {{ labels.go_to_accounts_btn }}
    </button>
  </div>
</template>
<script>
	export default {
		name: 'EmptyActiveAccounts',
		data: function () {
			return {
				labels: this.$store.state.labels.accounts,
				upsell_link: ropApiSettings.upsell_link,
			};
		},
		methods: {
			goToAccounts() {
				this.$store.commit('setTabView', 'accounts')
			}
		}
	}
</script>