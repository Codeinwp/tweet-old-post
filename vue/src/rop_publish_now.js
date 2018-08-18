// jshint ignore: start
/* eslint no-unused-vars: 0 */

import Vue from 'vue'

import store from './models/rop_store.js'
import PublishNow from './vue-elements/pro/publish-now.vue'

window.addEventListener( 'load', function () {
	var RopPublishNow = new Vue( {
		el: '#rop_publish_now',
        store,
		components: {
			PublishNow
		},
		created() {
		},
	} );
} );
