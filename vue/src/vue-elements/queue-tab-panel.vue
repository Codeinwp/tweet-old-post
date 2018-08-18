<template>
	<div class="tab-view">
		<div class="panel-body" :class="'rop-tab-state-'+is_loading">
			<div class="columns" v-if="! start_status">
				<div class="column col-12 text-center empty-container">
					<div class="empty-icon">
						<i class="fa fa-3x fa-info-circle"></i>
					</div>
					<p class="empty-title h5">{{labels.sharing_not_started}}</p>
					<p class="empty-subtitle">{{labels.sharing_not_started_desc}}</p>
				</div>
			</div>

			<div v-else-if="start_status && queueCount > 0 ">

				<div class="columns py-2" v-if="! is_business">
					<div class="column text-center">
						<p class="upsell"><i class="fa fa-lock"></i> <span v-html="labels.biz_only"></span></p>
					</div>
				</div>

				<!-- When sharing is started but we  have the business plan. -->
				<div class="d-inline-block mt-2 column col-12">
					<p class="text-gray info-paragraph"><i class="fa fa-info-circle"></i> {{labels.queue_desc}}</p>
				</div>
			</div>
			<div class="empty" v-else-if="start_status && queueCount === 0">
				<div class="empty-icon">
					<i class="fa fa-3x fa-info-circle"></i>
				</div>
				<p class="empty-title h5">{{labels.no_posts}}</p>
				<p class="empty-subtitle" v-html="labels.no_posts_desc"></p>
			</div>
			<div class="columns" v-if="start_status && queueCount > 0">
				<div class="column col-12 text-left" v-for=" (data, index) in queue ">
					<queue-card :card_data="data.post_data" :id="index" :enabled="is_business"/>
				</div>
			</div>
		</div>
		<div class="panel-footer text-rightcade" v-if="start_status">
			<button class="btn btn-secondary" @click="refreshQueue(true)">
				<i class="fa fa-refresh" v-if="!is_loading"></i>
				<i class="fa fa-spinner fa-spin" v-else></i>
				{{labels.refresh_btn}}
			</button>
		</div>
	</div>
</template>

<script>
	import QueueCard from './reusables/queue-card.vue'

	module.exports = {
		name: 'queue-view',
		computed: {
			queueCount: function () {
				return Object.keys(this.$store.state.queue).length
			},
			queue: function () {
				return this.$store.state.queue
			},
			start_status: function () {
				return this.$store.state.cron_status.current_status
			},
			is_business: function () {
				return (this.$store.state.licence > 1)
			},
		},
		data: function () {
			return {
				is_loading: false,
				labels: this.$store.state.labels.queue,
				upsell_link: ropApiSettings.upsell_link,
			}
		},
		watch: {
			start_status: function (new_val) {
				this.refreshQueue();
			}
		},
		mounted: function () {
			if (this.start_status) {
				this.refreshQueue();
			}
		},
		methods: {
			refreshQueue: function (force) {
				if (this.is_loading) {
					this.$log.warn('Request in progress...Bail');
					return;
				}
				this.$store.state.queue = [];
				this.is_loading = true;
				this.$store.dispatch('fetchAJAXPromise', {req: 'get_queue', data: {force: force}}).then(response => {
					this.is_loading = false;
					this.$store.dispatch('fetchAJAX', {req: 'manage_cron'});
				}, error => {
					this.is_loading = false;
					Vue.$log.error('Got nothing from server. Prompt user to check internet connection and try again', error)
				})
			}
		},
		components: {
			QueueCard
		}
	}
</script>
