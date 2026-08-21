// jshint ignore: start
/* eslint no-unused-vars: 0 */
/* exported RopApp */

import Vue from 'vue'

import store from './models/rop_store.js'
import MainPagePanel from './vue-elements/main-page-panel.vue'

/**
 * Consume a rejected start-up request, so it does not surface as an unhandled rejection.
 *
 * @param {string} req Name of the request which failed.
 * @returns {Function} Rejection handler.
 */
const logStartupFailure = ( req ) => ( error ) => {
	Vue.$log.error( 'Could not load ' + req + ' when starting the dashboard.', error )
}

window.addEventListener( 'load', function () {
	var RopApp = new Vue( {
		el: '#rop_core',
		store,
		components: {
			MainPagePanel
		},
		created() {
			store.dispatch( 'fetchAJAX', {req: 'manage_cron', data: {action: 'status'}} )
			store.dispatch( 'fetchAJAXPromise', {req: 'get_available_services'} ).catch( logStartupFailure( 'get_available_services' ) )
			store.dispatch( 'fetchAJAXPromise', {req: 'get_authenticated_services'} ).catch( logStartupFailure( 'get_authenticated_services' ) )
			store.dispatch( 'fetchAJAXPromise', {req: 'get_active_accounts'} ).catch( logStartupFailure( 'get_active_accounts' ) )
		},
	} );
} );