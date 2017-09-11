import Vue from 'vue'
import VueResource from 'vue-resource'

import store from './models/rop_store.js';
import MainPagePanel from './vue-elements/main-page-panel.vue';

window.onload = function () {
    new Vue({
        el: '#rop_core',
        store,
        create() {
            store.dispatch( 'fetchAvailableServices' );
        },
        components: {
            MainPagePanel
        }
    });
};