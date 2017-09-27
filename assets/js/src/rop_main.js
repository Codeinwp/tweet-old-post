import Vue from 'vue'

import store from './models/rop_store.js';
import MainPagePanel from './vue-elements/main-page-panel.vue';

window.onload = function () {
    new Vue({
        el: '#rop_core',
        store,
        created() {
            store.dispatch( 'fetchAvailableServices' );
        },
        components: {
            MainPagePanel
        }
    });
};