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

/**
 * Initialize the formbricks survey.
 * 
 * @see https://github.com/formbricks/setup-examples/tree/main/html
 */
window.addEventListener('themeisle:survey:loaded', function () {
    window?.tsdk_formbricks?.init?.({
        environmentId: "clwgcs7ia03df11mgz7gh15od",
        apiHost: "https://app.formbricks.com",
        ...(window?.ropSurveyData ?? {}),
    });
});
