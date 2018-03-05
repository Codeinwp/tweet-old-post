<template>
	<div>
		<div class="panel title-panel" style="margin-bottom: 40px; padding-bottom: 20px;">
			<div class="panel-header">
				<img :src="plugin_logo" style="float: left; margin-right: 10px;" />
				<h1 class="d-inline-block">Revive Old Posts</h1><span class="powered"> by <a href="https://themeisle.com" target="_blank"><b>ThemeIsle</b></a></span>
			</div>
		</div>

		<toast />
		<countdown v-bind:to="countdownObject" />
		<ajax-loader />
		<div class="panel">
			<div class="panel-nav" style="padding: 8px;">
				<ul class="tab">
					<li class="tab-item" v-for="tab in displayTabs" :class="{ active: tab.isActive, badge: displayProBadge( tab.slug ), upsell: displayProBadge( tab.slug ) }" data-badge="PRO"><a href="#" @click="switchTab( tab.slug )">{{ tab.name }}</a></li>
					<li class="tab-item tab-action">
						<div class="form-group">
							<label class="form-switch">
								<input type="checkbox" v-model="generalSettings.custom_messages" @change="updateSettings" :disabled="!has_pro" />
								<i class="form-icon" v-if="has_pro"></i><i class="badge" data-badge="PRO" v-else></i> <span class="hide-sm">Custom Share Messages</span>
							</label>
							<label class="form-switch">
								<input type="checkbox" v-model="generalSettings.beta_user" @change="updateSettings" />
								<i class="form-icon"></i> <span class="hide-sm">Beta User</span>
							</label>
							<label class="form-switch">
								<input type="checkbox" v-model="generalSettings.remote_check" @change="updateSettings" />
								<i class="form-icon"></i> <span class="hide-sm">Remote Check</span>
							</label>
						</div>
					</li>
				</ul>
			</div>
			<component :is="page.view"></component>
		</div>
	</div>
</template>

<script>
	/* global ROP_ASSETS_URL */
	import AccountsTab from './accounts-tab-panel.vue'
	import SettingsTab from './settings-tab-panel.vue'
	import PostFormatTab from './post-format-tab-panel.vue'
	import ScheduleTab from './schedule-tab-panel.vue'
	import QueueTab from './queue-tab-panel.vue'
	import LogsTab from './logs-tab-panel.vue'
	import Toast from './reusables/toast.vue'
	import CountDown from './reusables/countdown.vue'
	import AjaxLoader from './reusables/ajax-loader.vue'

	module.exports = {
		name: 'main-page-panel',
		computed: {
			displayTabs: function () {
				return this.$store.state.displayTabs
			},
			page: function () {
				return this.$store.state.page
			},
			generalSettings: function () {
				return this.$store.state.generalSettings
			},
			has_pro: function () {
				return this.$store.state.has_pro
			},
			countdownObject () {
				let queue = this.$store.state.queue
				let toTime = null
				let isOn = this.$store.state.cron_status
				if ( queue !== undefined && queue[Object.keys( queue )[0]] && isOn ) {
					toTime = queue[Object.keys( queue )[0]].time
				}
				return {
					toTime: toTime,
					isOn: isOn
				}
			}
		},
		created () {
		},
		data: function () {
			return {
				plugin_logo: ROP_ASSETS_URL + 'img/logo_rop.png'
			}
		},
		methods: {
			switchTab ( slug ) {
				this.$store.commit( 'setTabView', slug )
			},
			updateSettings () {
				this.$store.dispatch( 'fetchAJAX', { req: 'update_settings_toggle', data: { custom_messages: this.$store.state.generalSettings.custom_messages, beta_user: this.$store.state.generalSettings.beta_user, remote_check: this.$store.state.generalSettings.remote_check } } )
			},
			displayProBadge: function ( slug ) {
				if ( !this.has_pro && ( slug === 'schedule' || slug === 'queue' ) ) {
					return true
				}
				return false
			}
		},
		components: {
			'accounts': AccountsTab,
			'settings': SettingsTab,
			'post-format': PostFormatTab,
			'schedule': ScheduleTab,
			'queue': QueueTab,
			'logs': LogsTab,
			'toast': Toast,
			'countdown': CountDown,
			'ajax-loader': AjaxLoader
		}
	}
</script>

<style>
	#rop_core .badge[data-badge]::after {
		position: absolute;
		bottom: -16px;
		right: 0px;
	}
</style>