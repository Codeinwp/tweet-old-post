<template>
	<div class="tab-view">
		<div class="panel-body">
			<div class=" columns mt-2" v-if="logs_no > 0">
				<div class="column  col-12 text-right ">
					<button class="btn  btn-secondary " @click="getLogs(true)">
						<i class="fa fa-remove" v-if="!is_loading"></i>
						<i class="fa fa-spinner fa-spin" v-else></i>
						{{labels.clear_btn}}
					</button>
				</div>
			</div>
			<div class="columns">
				<div class="empty column col-12" v-if="is_loading">
					<div class="empty-icon">
						<i class="fa fa-3x fa-spinner fa-spin"></i>
					</div>
				</div>
				<div class="empty column col-12" v-else-if="logs_no === 0">
					<div class="empty-icon">
						<i class="fa fa-3x fa-info-circle"></i>
					</div>
					<p class="empty-title h5">{{labels.no_logs}}</p>
				</div>

				<div class="column col-12 mt-2" v-for=" (data, index) in logs " v-else-if="logs_no >  0">
					<div class="toast log-toast" :class="'toast-' + data.type">
						<small class="pull-right text-right">{{formatDate ( data.time ) }}</small>
						<p>{{data.message}}</p>
					</div>
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
				labels: this.$store.state.labels.logs,
				upsell_link: ropApiSettings.upsell_link,
			}
		},
		mounted: function () {
			this.getLogs();
		},
		computed: {
			logs: function () {
				return this.$store.state.page.logs
			},
			logs_no: function () {
				return this.$store.state.cron_status.logs_number;
			},
		},
		watch: {
			logs_no: function () {
				this.getLogs();
			}
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
					if(true === force){
						let toast = {
							type: 'success',
							show: false,
							title: '',
							message: ''
						};
						this.$store.commit('updateState', {stateData: toast, requestName: 'update_toast'});
					}
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
