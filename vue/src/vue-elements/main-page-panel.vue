<template>
	<div>
		<div class="columns panel-header">
			<div class="column header-logo vertical-align">
				<div>
					<img :src="plugin_logo" class="plugin-logo avatar avatar-lg"/>
					<h1 class="plugin-title d-inline-block">Revive Old Posts</h1><span class="powered d-inline-block"> {{labels.by}} <a
						href="https://revive.social" target="_blank"><b>Revive.Social</b></a></span>
				</div>
			</div>
			<div class="sidebar sidebar-top card rop-container-start">
				<div class="toast rop-current-time" v-if="formatedDate">
					{{labels.now}}: {{ formatedDate }}
				</div>
				<countdown :current_time="current_time"/>
				<button class="btn btn-sm" :class="btn_class"
				        :data-tooltip="labels.active_account_warning"
				        @click="togglePosting()" :disabled="haveAccounts">
					<i class="fa fa-play" v-if="!is_loading && !start_status"></i>
					<i class="fa fa-stop" v-else-if="!is_loading && start_status"></i>
					<i class="fa fa-spinner fa-spin" v-else></i>
					{{( start_status ? labels.stop : labels.start )}} {{labels.sharing}}
				</button>
			</div>
		</div>
		
		<div class="columns">
			<div class="panel column col-9 col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
				<div class="panel-nav" style="padding: 8px;">
					<ul class="tab ">
						<li class="tab-item c-hand" v-for="tab in displayTabs"
						    :class="{ active: tab.isActive }">
							<a :class=" ( tab.slug === 'logs' && logs_no > 0  )  ? ' badge-logs badge' : '' "
							   :data-badge="logs_no"
							   @click="switchTab( tab.slug )">{{ tab.name }}</a>
						</li>
					</ul>
				</div>
				<component :is="page.template" :type="page.view"></component>
			</div>
			
			<div class="sidebar column col-3 col-xs-12 col-sm-12  col-md-12 col-lg-12"
			     :class="'rop-license-plan-'+license">
				
				<div class="card rop-container-start">
					<div class="toast rop-current-time" v-if="formatedDate">
						{{labels.now}}: {{ formatedDate }}
					</div>
					<countdown :current_time="current_time"/>
					<button class="btn" :class="btn_class"
					        :data-tooltip="labels.active_account_warning"
					        @click="togglePosting()" :disabled="haveAccounts">
						<i class="fa fa-play" v-if="!is_loading && !start_status"></i>
						<i class="fa fa-stop" v-else-if="!is_loading && start_status"></i>
						<i class="fa fa-spinner fa-spin" v-else></i>
						{{( start_status ? labels.stop : labels.start )}} {{labels.sharing}}
					</button>
					<upsell-sidebar></upsell-sidebar>
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
	import upsellSidebar from './upsell-sidebar.vue'

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
				to_pro_upsell: ROP_ASSETS_URL + 'img/to_pro.png',
				to_business_upsell: ROP_ASSETS_URL + 'img/to_business.png',
				plugin_logo: ROP_ASSETS_URL + 'img/logo_rop.png',
				license: this.$store.state.licence,
				labels: this.$store.state.labels.general,
				upsell_link: ropApiSettings.upsell_link,
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
		},
		components: {
			'accounts': AccountsTab,
			'settings': SettingsTab,
			'accounts-selector': AccountsSelectorTab,
			'queue': QueueTab,
			'logs': LogsTab,
			'upsell-sidebar': upsellSidebar,
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