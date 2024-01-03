<template>
  <div class="tab-view">
    <div class="panel-body">
      <div
        v-if="logs_no > 0"
        class=" columns mt-2"
      >
        <div class="column  col-12 text-right ">
          <button
            class="btn btn-secondary "
            @click="exportLogsAsFile"
          >
            <i
              class="fa fa-download"
            />
            {{ labels.export_btn }}
          </button>
          <button
            class="btn btn-secondary "
            @click="getLogs(true)"
          >
            <i
              v-if="!is_loading"
              class="fa fa-remove"
            />
            <i
              v-else
              class="fa fa-spinner fa-spin"
            />
            {{ labels.clear_btn }}
          </button>
        </div>
      </div>
      <div class="columns">
        <div
          v-if="is_loading"
          class="empty column col-12"
        >
          <div class="empty-icon">
            <i class="fa fa-3x fa-spinner fa-spin" />
          </div>
        </div>
        <div
          v-else-if="logs_no === 0"
          class="empty column col-12"
        >
          <div class="empty-icon">
            <i class="fa fa-3x fa-info-circle" />
          </div>
          <p class="empty-title h5">
            {{ labels.no_logs }}
          </p>
        </div>

        <template v-else-if="logs_no > 0">
          <div
            v-for=" (data, index) in logs "
            :key="index"
            class="column col-12 mt-2"
          >
            <div
              class="toast log-toast"
              :class="'toast-' + data.type"
            >
              <small class="pull-right text-right">{{ formatDate ( data.time ) }}</small>
              <p>
                {{ data.message }}
              </p>
            </div>
          </div>
        </template>
      </div>
    </div>
  </div>
</template>

<script>

	import moment from 'moment'

	export default {
		name: 'LogsView',
		props: ['model'],
		data: function () {
			return {
				is_loading: false,
				labels: this.$store.state.labels.logs,
				upsell_link: ropApiSettings.upsell_link,
			}
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
		mounted: function () {
			this.getLogs();
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
			exportLogsAsFile() {
				const content = this.logs.map(log => {
					return `[${moment.utc(log.time, 'X')}][${log.type}] ${log.message}`;
				}).join('\n');

				const element = document.createElement('a');
				element.setAttribute('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(content));
				element.setAttribute('download', `rop_logs__${moment().format('YYYY-MM-DD_HH-mm-ss')}.txt`);

				element.style.display = 'none';
				document.body.appendChild(element);
				element.click();
				document.body.removeChild(element);
			}

		},
	}
</script>
<style type="text/css" scoped>
	#rop_core .toast.log-toast p {
		margin: 0px;
		line-height: inherit;
		padding: 20px 5px;
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

	.columns {
		line-break: anywhere;
	}
</style>
