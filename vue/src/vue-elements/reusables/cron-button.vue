<template>
    <button class="btn btn-success" @click="cronStart" v-if="!cron_status"><i class="fa fa-play"></i> Start Sharing</button>
    <button class="btn btn-error" @click="cronStop" v-else><i class="fa fa-stop"></i> Stop Sharing</button>
</template>

<script>
	module.exports = {
		name: 'cron-button',
		computed: {
			cron_status: function () {
				return this.$store.state.cron_status
			}
		},
		methods: {
			cronStart () {
				this.$store.dispatch( 'fetchAJAX', { req: 'manage_cron', data: { action: 'start' } } )
			},
			cronStop () {
				this.$store.dispatch( 'fetchAJAX', { req: 'manage_cron', data: { action: 'stop' } } )
			}
		}
	}
</script>

<style></style>