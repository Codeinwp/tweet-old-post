// jshint ignore: start

/* global ropApiSettings */
/* exported ropApiSettings */

import Vue from 'vue'
import Vuex from 'vuex'
import VueResource from 'vue-resource'
import VueLogger from 'vuejs-logger'

const logOptions = {
	// required ['debug', 'info', 'warn', 'error', 'fatal']
	logLevel: 'info',
	// optional : defaults to false if not specified
	stringifyArguments: false,
	// optional : defaults to false if not specified
	showLogLevel: false,
	// optional : defaults to false if not specified
	showMethodName: false,
	// optional : defaults to '|' if not specified
	separator: '|',
	// optional : defaults to false if not specified
	showConsoleColors: true
};
Vue.use( Vuex );
Vue.use( VueResource );
Vue.use( VueLogger, logOptions );

export default new Vuex.Store( {
	state: {
		page: {
			debug: false,
			logs: [],
			view: 'settings',
			template: 'settings',
		},
		cron_status: {},
		toast: {
			type: 'success',
			show: false,
			title: 'Title placeholder',
			message: 'Lorem ipsum content message placeholder. This is the default.'
		},
		ajaxLoader: false,
		auth_in_progress: false,
		displayTabs: [
			{
				name: 'Accounts',
				slug: 'accounts',
				view: 'accounts',
				isActive: true
			},
			{
				name: 'General Settings',
				slug: 'settings',
				view: 'settings',
				isActive: false
			},
			{
				name: 'Post Format',
				slug: 'post-format',
				view: 'accounts-selector',
				isActive: false
			},
			{
				name: 'Custom Schedule',
				slug: 'schedule',
				view: 'accounts-selector',
				isActive: false
			},
			{
				name: 'Sharing Queue',
				slug: 'queue',
				view: 'queue',
				isActive: false
			},
			{
				name: 'Logs',
				slug: 'logs',
				view: 'logs',
				isActive: false
			}
		],
		licence: parseInt( ropApiSettings.license_type ),
		availableServices: [],
		generalSettings: [],
		authenticatedServices: [],
		activeAccounts: {},
		activePostFormat: [],
		activeSchedule: [],
		queue: {}
	},
	mutations: {

		setTabView( state, view ) {
			Vue.$log.debug( 'Changing tab to  ', view );
			for ( let tab in state.displayTabs ) {
				state.displayTabs[tab].isActive = false
				if ( state.displayTabs[tab].slug === view ) {
					state.displayTabs[tab].isActive = true
					state.page.view = state.displayTabs[tab].slug
					state.page.template = state.displayTabs[tab].view
				}
			}
		},
		setAjaxState( state, data ) {
			state.ajaxLoader = data
		},
		updateState( state, {stateData, requestName} ) {
			Vue.$log.debug( 'State change for ', requestName );
			switch ( requestName ) {
			case 'manage_cron':
				state.cron_status = stateData;
				break;
			case 'get_log':
				state.page.logs = stateData
				break
			case 'update_settings_toggle':
			case 'get_general_settings':
				state.generalSettings = stateData
				break
			case 'update_selected_post_types':
				state.generalSettings.selected_post_types = stateData
				for ( let index in state.generalSettings.available_post_types ) {
					state.generalSettings.available_post_types[index].selected = false
					for ( let indexSelected in stateData ) {
						if ( state.generalSettings.available_post_types[index].value === stateData[indexSelected].value ) {
							state.generalSettings.available_post_types[index].selected = true
						}
					}
				}
				break
			case 'update_selected_taxonomies':
				state.generalSettings.selected_taxonomies = stateData
				for ( let index in state.generalSettings.available_taxonomies ) {
					state.generalSettings.available_taxonomies[index].selected = false
					for ( let indexSelected in stateData ) {
						if ( state.generalSettings.available_taxonomies[index].value === stateData[indexSelected].value || state.generalSettings.available_taxonomies[index].parent === stateData[indexSelected].value ) {
							state.generalSettings.available_taxonomies[index].selected = true
						}
					}
				}
				break
			case 'update_selected_posts':
				state.generalSettings.selected_posts = stateData
				break
			case 'get_available_services':
				state.availableServices = stateData
				break
			case 'get_authenticated_services':
			case 'remove_service':
				state.authenticatedServices = stateData
				break
			case 'authenticate_service':
				state.authenticatedServices = stateData
				state.auth_in_progress = false
				//state.activeAccounts = stateData
				break
			case 'get_active_accounts':
			case 'update_active_accounts':
			case 'remove_account':
				state.activeAccounts = stateData
				break
			case 'get_taxonomies':
				state.generalSettings.available_taxonomies = stateData
				break
			case 'get_posts':
				state.generalSettings.available_posts = stateData
				break
			case 'get_post_format':
			case 'save_post_format':
			case 'reset_post_format':
				state.activePostFormat = stateData
				break
			case 'reset_accounts':
				state.activeAccounts = [];
				state.authenticatedServices = [];
				break
			case 'get_shortner_credentials':
				state.activePostFormat['shortner_credentials'] = stateData
				break
			case 'get_schedule':
			case 'save_schedule':
			case 'reset_schedule':
				state.activeSchedule = stateData
				break
			case 'get_queue':
			case 'update_queue_event':
			case 'publish_queue_event':
			case 'skip_queue_event':
			case 'block_queue_event':
				state.queue = stateData

				break
			case 'update_toast':
				state.toast = stateData
				break
			case 'toggle_account':

				break
			default:
				Vue.$log.error( 'No state request for ', requestName );
			}
		}
	},
	actions: {
		fetchAJAX( {commit}, data ) {
			if ( data.req !== '' ) {
				commit( 'setAjaxState', true )
				Vue.http( {
					url: ropApiSettings.root,
					method: 'POST',
					headers: {'X-WP-Nonce': ropApiSettings.nonce},
					params: {'req': data.req},
					body: data.data,
					responseType: 'json'
				} ).then( function ( response ) {
					commit( 'setAjaxState', false )
					let display = false;
					if ( display ) {
						let toast = {
							type: response.data.status,
							show: true,
							title: response.data.title,
							message: response.data.message
						}
						commit( 'updateState', {stateData: toast, requestName: 'update_toast'} )
					}
					let stateData = response.data
					if ( response.data.data ) {
						stateData = response.data.data
					}
					let requestName = data.req
					if ( data.updateState !== false ) {
						commit( 'updateState', {stateData, requestName} )
					}
				}, function () {
					commit( 'setAjaxState', false );
					Vue.$log.error( 'Error when trying to do request: ', data.req );
				} )
			}
			return false
		},
		fetchAJAXPromise( {commit}, data ) {
			if ( data.req !== '' ) {
				commit( 'setAjaxState', true )
				return new Promise( ( resolve, reject ) => {
					Vue.http( {
						url: ropApiSettings.root,
						method: 'POST',
						headers: {'X-WP-Nonce': ropApiSettings.nonce},
						params: {'req': data.req},
						body: data.data,
						responseType: 'json'
					} ).then( function ( response ) {
						commit( 'setAjaxState', false )
						let stateData = response.data
						if ( response.data.data ) {
							stateData = response.data.data
						}
						let requestName = data.req
						resolve( stateData )
						if ( data.updateState !== false ) {
							commit( 'updateState', {stateData, requestName} )
						}
					}, function () {
						commit( 'setAjaxState', false )
						Vue.$log.error( 'Error when trying to do request: ', data.req );
					} )
				} )
			}
			return false
		}
	}
} )
