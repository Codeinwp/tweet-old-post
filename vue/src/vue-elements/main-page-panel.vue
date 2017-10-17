<template>
	<div>
		<div class="panel title-panel" style="margin-bottom: 40px; padding-bottom: 20px;">
			<div class="panel-header">
				<img :src="plugin_logo" style="float: left; margin-right: 10px;" />
				<h1 class="d-inline-block">Revive Old Posts</h1><span class="powered"> by <a href="https://themeisle.com" target="_blank"><b>ThemeIsle</b></a></span>
			</div>
		</div>
		<div class="panel">
			<div class="panel-nav" style="padding: 8px;">
				<ul class="tab">
					<li class="tab-item" v-for="tab in displayTabs" :class="{ active: tab.isActive }"><a href="#" @click="switchTab( tab.slug )">{{ tab.name }}</a></li>
					<li class="tab-item tab-action">
						<div class="form-group">
							<label class="form-switch">
								<input type="checkbox" />
								<i class="form-icon"></i> Beta User
							</label>
							<label class="form-switch">
								<input type="checkbox" />
								<i class="form-icon"></i> Remote Check
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
	import LogsTab from './logs-tab-panel.vue'

	import { mapState } from 'vuex'

	module.exports = {
		name: 'main-page-panel',
		computed: mapState( [ 'displayTabs', 'page' ] ),
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
			}
		},
		components: {
			'accounts': AccountsTab,
			'settings': SettingsTab,
			post: {
				name: 'post-view',
				template: '<span>This is not yet ready</span>'
			},
			schedule: {
				name: 'schedule-view',
				template: '<span>This is not yet ready</span>'
			},
			'logs': LogsTab
		}
	}
</script>