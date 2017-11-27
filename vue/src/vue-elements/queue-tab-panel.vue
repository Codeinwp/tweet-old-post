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
			<button class="btn btn-primary" @click="saveSchedule()"><i class="fa fa-check"></i> Save Schedule</button>
			<button class="btn btn-secondary" @click="resetSchedule()"><i class="fa fa-ban"></i> Reset to Defaults</button>
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
		},
		components: {
			QueueCard
		}
	}
</script>