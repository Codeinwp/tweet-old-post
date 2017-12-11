<template>
	<div class="tab-view">
		<div class="panel-body" style="overflow: inherit;">
			<h3>Sharing Queue</h3>
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
			queue: function () {
				return this.$store.state.queue
			}
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