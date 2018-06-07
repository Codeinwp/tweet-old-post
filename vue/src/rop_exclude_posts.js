// jshint ignore: start
/* eslint no-unused-vars: 0 */
/* exported RopExcludePosts */

import Vue from 'vue'

import store from './models/rop_store.js'
import ExcludePostsPage from './vue-elements/exclude-posts-page.vue'


window.addEventListener( 'load', function () {
	var RopExcludePosts = new Vue( {
		el: '#rop_content_filters',
		store,
		components: {
			ExcludePostsPage
		},
		created() {
		},
	} );
} );
