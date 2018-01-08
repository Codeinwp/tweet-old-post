<template>
    <div class="empty">
        <div class="empty-icon">
            <i class="fa fa-3x fa-user-circle-o"></i>
        </div>
        <p class="empty-title h5">No active accounts!</p>
        <p class="empty-subtitle">Add one from the <b>"Accounts"</b> section.</p>
        <button class="btn btn-primary" @click="goToAccounts()">Go to Accounts</button>
    </div>
</template>
<script>
	module.exports = {
		name: 'empty-active-accounts',
		methods: {
			goToAccounts () {
				this.$store.commit( 'setTabView', 'accounts' )
			}
		}
	}
</script>