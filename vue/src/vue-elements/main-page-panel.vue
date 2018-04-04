<template>
	<div>
		<div class="panel title-panel" style="margin-bottom: 40px;">
			<div class="panel-header">
				<img :src="plugin_logo" style="float: left; margin-right: 10px;"/>
				<h1 class="d-inline-block">Revive Old Post</h1><span class="powered"> by <a
					href="https://themeisle.com" target="_blank"><b>ThemeIsle</b></a></span>
			</div>
		</div>
		<div class="columns">
			<div class="panel  column col-9 col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-8 ">
				<div class="panel-nav" style="padding: 8px;">
					<ul class="tab ">
						<li class="tab-item" v-for="tab in displayTabs"
						    :class="{ active: tab.isActive }">
							<a href="#" :class=" ( tab.slug === 'logs' && logs_no > 0  )  ? ' badge-logs badge' : '' "
							   :data-badge="logs_no"
							   @click="switchTab( tab.slug )">{{ tab.name }}</a>
						</li>
						<li class="tab-item tab-action">
							<div class="form-group">
								<label class="form-switch col-ml-auto">
									<input type="checkbox" v-model="generalSettings.custom_messages"
									       @change="updateSettings"/>
									<i class="form-icon"></i><span class="hide-sm">Custom Share Messages</span>
								</label>
							</div>
						</li>
					</ul>
				</div>
				<component :is="page.template" :type="page.view"></component>
			</div>
			
			<div class="sidebar column col-3 col-xs-12 col-sm-12  col-md-12 col-lg-12 col-xl-4 "
			     :class="'rop-license-plan-'+license">
				
				<div class="card rop-container-start">
					<div class="toast toast-success rop-current-time" v-if="formatedDate">
						Now: {{ formatedDate }}
					</div>
					<countdown :current_time="current_time"/>
					<button class="btn" :class="btn_class"
					        data-tooltip="You will need
					         at least one active account
					         to start sharing."
					        @click="togglePosting()" :disabled="haveAccounts">
						<i class="fa fa-play" v-if="!is_loading && !start_status"></i>
						<i class="fa fa-stop" v-else-if="!is_loading && start_status"></i>
						<i class="fa fa-spinner fa-spin" v-else></i>
						{{( start_status ? 'Stop' : 'Start' )}} Sharing
					</button>
				
				</div>
				<div class="card rop-upsell-pro-card" v-if="license  < 1 ">
					Buy the pro version
				</div>
				<div class="card rop-upsell-business-card" v-if="license  === 1">
					Buy the business version
				</div>
			</div>
		</div>
	</div>
</template>

<script>
	/* global ROP_ASSETS_URL */
	import AccountsTab from './accounts-tab-panel.vue'
	import SettingsTab from './settings-tab-panel.vue'
	import AccountsSelectorTab from './accounts-selector-panel.vue'
	import QueueTab from './queue-tab-panel.vue'
	import LogsTab from './logs-tab-panel.vue'
	import Toast from './reusables/toast.vue'
	import CountDown from './reusables/countdown.vue'
	import moment from 'moment'

	module.exports = {
		name: 'main-page-panel',
		computed: {
			/**
			 * Display the clicked tab.
			 *
			 * @returns {module.exports.computed.displayTabs|*[]}
			 */
			displayTabs: function () {
				return this.$store.state.displayTabs
			},
			/**
			 * Get active page.
			 */
			page: function () {
				return this.$store.state.page
			},
			current_time: {
				get: function () {
					return this.$store.state.cron_status.current_time;
				},
				set: function (value) {
					this.$store.state.cron_status.current_time = value;
				}
			},
			date_format: function () {

				return this.$store.state.cron_status.date_format;
			},
			logs_no: function () {

				return this.$store.state.cron_status.logs_number;
			},
			/**
			 * Get btn start class.
			 */
			btn_class: function () {
				let btn_class = ('btn-' + (this.start_status ? 'danger' : 'success'));
				if (this.haveAccounts) {
					btn_class += ' tooltip button-disabled ';
				}
				return btn_class;
			},
			/**
			 * Check if we have accounts active.
			 *
			 * @returns {boolean}
			 */
			haveAccounts: function () {
				return !(Object.keys(this.$store.state.activeAccounts).length > 0);
			},
			/*
			* Check if the sharing is started.
			*/
			start_status: function () {
				return this.$store.state.cron_status.current_status
			},

			/**
			 * Get general settings.
			 * @returns {module.exports.computed.generalSettings|Array|*}
			 */
			generalSettings: function () {
				return this.$store.state.generalSettings
			},
			/**
			 * Get general settings.
			 * @returns {module.exports.computed.generalSettings|Array|*}
			 */
			formatedDate: function () {
				if (typeof this.date_format === 'undefined') {
					return '';
				}
				return moment.utc(this.current_time, 'X').format(this.date_format.replace('mm', 'mm:ss'));
			},
		},
		mounted: function () {
			setInterval(() => {
				//this.$log.info('counting');
				if (this.current_time > 0) {
					this.current_time += 1;
				}
			}, 1000);
		},
		created() {
		},
		data: function () {
			return {
				plugin_logo: ROP_ASSETS_URL + 'img/logo_rop.png',
				license: this.$store.state.licence,
				is_loading: false,
			}
		},
		methods: {
			/**
			 * Toggle sharing.
			 */
			togglePosting() {
				if (this.is_loading) {
					this.$log.warn('Request in progress...Bail');
					return;
				}
				this.is_loading = true;
				this.$store.dispatch('fetchAJAXPromise', {
					req: 'manage_cron',
					data: {
						'action': this.start_status === false ? 'start' : 'stop'
					}
				}).then(response => {
					this.is_loading = false;
				}, error => {
					this.is_loading = false;
					Vue.$log.error('Got nothing from server. Prompt user to check internet connection and try again', error)
				})
			},
			/**
			 * Toggle tab active.
			 * @param slug
			 */
			switchTab(slug) {
				this.$store.commit('setTabView', slug)
			},
			/**
			 * Update settings outside the general settings tab.
			 */
			updateSettings() {
				this.$store.dispatch('fetchAJAX', {
					req: 'update_settings_toggle',
					data: {
						custom_messages: this.$store.state.generalSettings.custom_messages,
						beta_user: this.$store.state.generalSettings.beta_user,
						remote_check: this.$store.state.generalSettings.remote_check
					}
				})
			},
		},
		components: {
			'accounts': AccountsTab,
			'settings': SettingsTab,
			'accounts-selector': AccountsSelectorTab,
			'queue': QueueTab,
			'logs': LogsTab,
			'toast': Toast,
			'countdown': CountDown
		}
	}
</script>

<style>
	#rop_core .badge[data-badge]::after {
		position: absolute;
		bottom: -16px;
		right: 0px;
	}
	
	#rop_core .badge.badge-logs::after {
		right: auto;
		top: 0px;
	}
	
	#rop_core .badge.badge-logs {
		padding-right: 10px;
	}
</style>