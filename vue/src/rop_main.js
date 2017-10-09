/* eslint no-unused-vars: 0 */
/* exported RopApp */

import Vue from 'vue'

import store from './models/rop_store.js'
import MainPagePanel from './vue-elements/main-page-panel.vue'

window.onload = function () {
	var RopApp = new Vue( {
		el: '#rop_core',
		store,
		created () {
			store.dispatch( 'fetchAvailableServices' )
			store.dispatch( 'fetchAuthenticatedServices' )
			store.dispatch( 'fetchActiveAccounts' )
		},
		components: {
			MainPagePanel
		}
	} )
}
