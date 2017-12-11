/* global ropApiSettings */

import Vue from 'vue'
import Vuex from 'vuex'
import VueResource from 'vue-resource'

Vue.use( Vuex )
Vue.use( VueResource )

export default new Vuex.Store( {
	state: {
		page: {
			debug: true,
			logs: '### Here starts the log \n\n',
			// view: 'accounts'
			// view: 'post-format'
			// view: 'settings'
			// view: 'schedule'
			view: 'queue'
		},
		auth_in_progress: false,
		displayTabs: [
			{
				name: 'Accounts',
				slug: 'accounts',
				isActive: true
			},
			{
				name: 'General Settings',
				slug: 'settings',
				isActive: false
			},
			{
				name: 'Post Format',
				slug: 'post-format',
				isActive: false
			},
			{
				name: 'Custom Schedule',
				slug: 'schedule',
				isActive: false
			},
			{
				name: 'Sharing Queue',
				slug: 'queue',
				isActive: false
			},
			{
				name: 'Logs',
				slug: 'logs',
				isActive: false
			}
		],
		generalSettings: [],
		availableServices: [],
		authenticatedServices: [],
		activeAccounts: [],
		activePostFormat: [],
		activeSchedule: [],
		queue: []
	},
	mutations: {
		logMessage ( state, data ) {
			let message = data
			let type = ''

			if ( data.constructor === Array ) {
				message = data[0]
			}

			if ( data.length === 2 ) {
				type = data[1]
			}

			if ( type === '' || type === undefined ) {
				type = 'notice'
			}

			let status = '[' + type.toUpperCase() + ']'

			if ( state.page.debug === true ) {
				console.log( message )
			}
			message = status.concat( ' ' ).concat( message )
			state.page.logs = state.page.logs.concat( message + '\n' )
		},
		setTabView ( state, view ) {
			for ( let tab in state.displayTabs ) {
				state.displayTabs[tab].isActive = false
				if ( state.displayTabs[tab].slug === view ) {
					state.displayTabs[tab].isActive = true
					state.page.view = view
				}
			}
		},
		updateState ( state, { stateData, requestName } ) {
			console.log( stateData )
			console.log( requestName )
			switch ( requestName ) {
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
				state.activeAccounts = stateData
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
			default:
				state.page.logs = state.page.logs.concat( '[info] No state update for request: "' + requestName + '"\n' )
			}
		}
	},
	actions: {
		fetchAJAX ( { commit }, data ) {
			if ( data.req !== '' ) {
				Vue.http( {
					url: ropApiSettings.root,
					method: 'POST',
					headers: { 'X-WP-Nonce': ropApiSettings.nonce },
					params: { 'req': data.req },
					body: data.data,
					responseType: 'json'
				} ).then( function ( response ) {
					let stateData = response.data
					let requestName = data.req
					if ( data.updateState !== false ) {
						commit( 'updateState', { stateData, requestName } )
					}
				}, function () {
					commit( 'logMessage', ['Error when trying to do request: "' + data.req + '".', 'error'] )
				} )
			}
			return false
		},
		fetchAJAXPromise ( { commit }, data ) {
			if ( data.req !== '' ) {
				return new Promise( ( resolve, reject ) => {
					Vue.http( {
						url: ropApiSettings.root,
						method: 'POST',
						headers: { 'X-WP-Nonce': ropApiSettings.nonce },
						params: { 'req': data.req },
						body: data.data,
						responseType: 'json'
					} ).then( function ( response ) {
						let stateData = response.data
						let requestName = data.req
						resolve( response.data )
						if ( data.updateState !== false ) {
							commit( 'updateState', { stateData, requestName } )
						}
					}, function () {
						commit( 'logMessage', ['Error when trying to do request: "' + data.req + '".', 'error'] )
					} )
				} )
			}
			return false
		}
	}
} )
