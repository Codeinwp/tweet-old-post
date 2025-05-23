// jshint ignore: start
/* eslint no-unused-vars: 0 */
/* exported RopApp */

import Vue from 'vue'

import store from './models/rop_store.js'
import MainPagePanel from './vue-elements/main-page-panel.vue'

window.addEventListener( 'load', function () {
	var RopApp = new Vue( {
		el: '#rop_core',
		store,
		components: {
			MainPagePanel
		},
		created() {
			store.dispatch( 'fetchAJAX', {req: 'manage_cron', data: {action: 'status'}} )
			store.dispatch( 'fetchAJAXPromise', {req: 'get_available_services'} )
			store.dispatch( 'fetchAJAXPromise', {req: 'get_authenticated_services'} )
			store.dispatch( 'fetchAJAXPromise', {req: 'get_active_accounts'} )
		},
	} );
} );