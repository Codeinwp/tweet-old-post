<template>
	<div class="container">
		<h3>Logs</h3>
		<div class=" columns " v-if="logs.length > 0">
			<div class="column  col-12 text-right ">
				<button class="btn  btn-secondary " @click="getLogs(true)">
					<i class="fa fa-remove" v-if="!is_loading"></i>
					<i class="fa fa-spinner fa-spin" v-else></i>
					Clear logs
				</button>
			</div>
		</div>
		<div class="columns">
			<div class="empty column col-12" v-if="is_loading">
				<div class="empty-icon">
					<i class="fa fa-3x fa-spinner fa-spin"></i>
				</div>
			</div>
			<div class="empty column col-12" v-else-if="logs.length === 0">
				<div class="empty-icon">
					<i class="fa fa-3x fa-user-circle-o"></i>
				</div>
				<p class="empty-title h5">No recent logs!</p>
			</div>
			
			<div class="column col-12" v-for=" (data, index) in logs " v-else-if="logs.length >  0">
				<div class="toast log-toast" :class="'toast-' + data.type">
					<small class="pull-right text-right">{{formatDate ( data.time ) }}</small>
					<p>{{data.message}}</p>
				</div>
			</div>
		</div>
	</div>
</template>

<script>

	import moment from 'moment'

	module.exports = {
		name: 'logs-view',
		props: ['model'],
		data: function () {
			return {
				is_loading: false,
			}
		},
		mounted: function () {
			this.getLogs();
		},
		computed: {
			logs: function () {
				return this.$store.state.page.logs
			},
		},
		methods: {
			getLogs(force) {
				if (this.is_loading) {
					this.$log.warn('Request in progress...Bail');
					return;
				}
				this.is_loading = true;
				this.$store.dispatch('fetchAJAXPromise', {
					req: 'get_log',
					data: {force: force}
				}).then(response => {
					this.$log.info('Succesfully fetched logs.');
					this.is_loading = false;
					this.$store.dispatch('fetchAJAX', {req: 'manage_cron', data: {action: 'status'}})
				}, error => {
					Vue.$log.error('Got nothing from server. Prompt user to check internet connection and try again', error)

					this.is_loading = false;
				})
			},
			formatDate(value) {
				let format = this.$store.state.cron_status.date_format;
				if (format === 'undefined') {
					return '';
				}
				return moment.utc(value, 'X').format(format.replace('mm', 'mm:ss'));
			},

		},
	}
</script>
<style type="text/css" scoped>
	#rop_core .toast.log-toast p {
		margin: 0px;
		line-height: inherit;
	}
	
	#rop_core .toast.log-toast:hover {
		opacity: 0.9;
	}
	
	#rop_core .toast.log-toast {
		padding: 0.1rem;
		padding-left: 10px;
		margin-top: 2px;
	}
	
	#rop_core .container {
		min-height: 400px;
	}
</style>