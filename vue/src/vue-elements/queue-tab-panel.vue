<template>
	<div class="tab-view">
		<div class="panel-body" style="overflow: inherit;">
			<h3>Sharing Queue</h3>
			<div class="empty" v-if="queueCount === 0">
				<div class="empty-icon">
					<i class="fa fa-3x fa-info-circle"></i>
				</div>
				<p class="empty-title h5">No queued posts!</p>
				<p class="empty-subtitle">Check if you have at least an <b>"Active account"</b>, what posts and pages are selected in <b>"General Settings"</b> and if a <b>"Schedule"</b> is defined.</p>
			</div>
			<div class="container columns">
				<div class="column col-sm-12 col-3 text-left" v-for=" (data, index) in queue ">
					<queue-card :account_id="data.account_id" :post="data.post" :time="data.time" :key="index" :id="index" />
				</div>
			</div>
		</div>
		<div class="panel-footer">
			<button class="btn btn-secondary" @click="refreshQueue"><i class="fa fa-refresh"></i> Refresh Queue</button>
		</div>
	</div>
</template>

<script>
	import QueueCard from './reusables/queue-card.vue'

	module.exports = {
		name: 'queue-view',
		computed: {
			queueCount: function () {
				return this.$store.state.queue.length
			},
			queue: function () {
				return this.$store.state.queue
			},
			has_pro: function () {
				return this.$store.state.has_pro
			}
		},
		mounted: function () {
			this.$store.dispatch( 'fetchAJAX', { req: 'get_queue' } )
		},
		methods: {
			refreshQueue: function () {
				this.$store.dispatch( 'fetchAJAX', { req: 'get_queue' } )
			}
		},
		components: {
			QueueCard
		}
	}
</script>