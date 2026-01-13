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
          <button
            class="btn btn-secondary"
            :class="logs_no <= 1000 ? 'd-none' : ''"
            @click="openCleanupModal()"
          >
            <i
              v-if="!is_loading"
              class="fa fa-trash"
            />
            <i
              v-else
              class="fa fa-spinner fa-spin"
            />
            {{ labels.cleanup.cta }}
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
              class="log-container"
            >
              [<span>{{ formatDate ( data.time ) }}</span>]
              [<span
                :class="'log-' + data.type"
              >{{ data.type }}</span>]
              {{ data.message }}
            </div>
          </div>
        </template>
      </div>
      <div
        class="modal rop-cleanup-modal"
        :class="cleanupModalClass"
      >
        <div class="modal-overlay" />
        <div class="modal-container">
        <div class="modal-header">
          <button
          class="btn btn-clear float-right"
          @click="closeCleanupModal()"
          />
          <div class="modal-title h5">
          {{ labels.cleanup.title }}
          </div>
        </div>
        <div class="modal-body">
          {{ labels.cleanup.description }}
        </div>
        <div class="modal-footer">
          <button
          class="btn  btn-success"
          @click="cleanupLogs()"
          >{{ labels.cleanup.btn }}</button>
        </div>
        </div>
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
        cleanupModal: false,
			}
		},
		computed: {
			logs: function () {
				return this.$store.state.page.logs
			},
			logs_no: function () {
				return this.$store.state.cron_status.logs_number;
			},
      cleanupModalClass: function() {
        return {
          'active': true === this.cleanupModal
        }
      }
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
			},
      openCleanupModal() {
        this.cleanupModal = true;
      },
      closeCleanupModal() {
        this.cleanupModal = false;
      },
      cleanupLogs: function() {
        if (this.is_loading) {
              this.$log.warn('Request in progress...Bail');
              return;
          }
          this.is_loading = true;
          this.$store.dispatch('fetchAJAXPromise', {
              req: 'cleanup_logs',
              data: {}
          }).then(response => {
              this.is_loading = false;
              this.cleanupModal = false;
              if (this.$parent.start_status === true) {
                  // Stop sharing process if enabled.
                  this.$parent.togglePosting();
              }
          }, error => {
              this.is_loading = false;
              Vue.$log.error('Got nothing from server. Prompt user to check internet connection and try again', error)
          })
      },
		},
	}
</script>
<style lang="scss" scoped>
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

	.log-container {
		font-size: 14px;
		background-color: #f3f2f1;
		padding: 10px;

		span {
			text-transform: uppercase;

			&:nth-child(even) {
				font-weight: bold;
			}

			&.log-error {
				color: #BE4B00;
			}

			&.log-success {
				color: #418331;
			}
		}

		&:has( .log-error ) {
			background-color: #FBE8E8;
		}
	}
   #rop_core .rop-cleanup-modal .modal-container{
    max-width: 500px;
    padding: 25px;
    .modal-title, .modal-footer{
      text-align: center;
    }
    .btn-success{
      border:none;
      background-color:#00a32a;
      color: #fff;
      padding: 0.5rem 1rem;
      height: auto;
      display: inline;
    }
    .btn-success:hover{
      background-color:#009528;
    }
    .modal-body{
      font-size: 0.7rem;
      margin: 10px 30px;
      padding: 0px;
    }
  }
</style>
