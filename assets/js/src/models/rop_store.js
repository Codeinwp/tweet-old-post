import Vue from 'vue'
import Vuex from 'vuex'
import VueResource from 'vue-resource'

Vue.use( Vuex );
Vue.use( VueResource );

export default new Vuex.Store({
    state: {
        page: {
            debug: true,
            logs: 'Here starts the log \n\n',
            view: 'accounts'
        },
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
                slug: 'post',
                isActive: false
            },
            {
                name: 'Custom Schedule',
                slug: 'schedule',
                isActive: false
            },
            {
                name: 'Logs',
                slug: 'logs',
                isActive: false
            },
        ],
        availableServices: [],
        authenticatedServices: [],
        activeAccounts: [],
    },
    getters: {
      getServices( state ) {
          return state.availableServices
      },
      getActiveAccounts( state ) {
          return state.activeAccounts
      }
    },
    mutations: {
        logMessage( state, message ) {
            if( state.debug === true ) console.log( message );
            return state.logs.concat( message + '\n' );
        },
        setTabView( state, view ) {
            for( var tab in state.displayTabs ) {
                state.displayTabs[tab].isActive = false;
                if( state.displayTabs[tab].slug === view ) {
                    state.displayTabs[tab].isActive = true;
                    state.page.view = view;
                }
            }
        },
        updateAvailableServices( state, data ) {
            state.availableServices = data;
        },
        updateAuthenticatedServices( state, data ) {
            state.authenticatedServices = data;
        },
        updateActiveAccounts( state, data ) {
            state.activeAccounts = data;
        }
    },
    actions: {
        fetchAvailableServices ({ commit }) {
            Vue.http({
                url: ropApiSettings.root,
                method: 'POST',
                headers: { 'X-WP-Nonce': ropApiSettings.nonce },
                params: { 'req': 'available_services' },
                responseType: 'json'
            }).then(function (response) {
                commit( 'updateAvailableServices', response.data );
            }, function () {
                console.log( 'Error retrieving available services.' )
            })
        },
        fetchAuthenticatedServices ({ commit }) {
            Vue.http({
                url: ropApiSettings.root,
                method: 'POST',
                headers: { 'X-WP-Nonce': ropApiSettings.nonce },
                params: { 'req': 'authenticated_services' },
                responseType: 'json'
            }).then(function (response) {
                commit( 'updateAuthenticatedServices', response.data );
            }, function () {
                console.log( 'Error retrieving authenticated services.' )
            })
        },
        fetchActiveAccounts ({ commit }) {
            Vue.http({
                url: ropApiSettings.root,
                method: 'POST',
                headers: { 'X-WP-Nonce': ropApiSettings.nonce },
                params: { 'req': 'active_accounts' },
                responseType: 'json'
            }).then(function (response) {
                commit( 'updateActiveAccounts', response.data );
            }, function () {
                console.log( 'Error retrieving active accounts.' )
            })
        },
        updateActiveAccounts ({ commit }, data) {
            if( data.action === 'update' ) {
                Vue.http({
                    url: ropApiSettings.root,
                    method: 'POST',
                    headers: { 'X-WP-Nonce': ropApiSettings.nonce },
                    params: { 'req': 'update_accounts' },
                    body: data,
                    responseType: 'json'
                }).then(function (response) {
                    commit( 'updateActiveAccounts', response.data );
                }, function () {
                    console.log( 'Error retrieving active accounts.' );
                })
            } else if( data.action === 'remove' ) {
                Vue.http({
                    url: ropApiSettings.root,
                    method: 'POST',
                    headers: { 'X-WP-Nonce': ropApiSettings.nonce },
                    params: { 'req': 'remove_account' },
                    body: data,
                    responseType: 'json'
                }).then(function (response) {
                    commit( 'updateActiveAccounts', response.data );
                }, function () {
                    console.log( 'Error retrieving active accounts.' );
                })
            } else {
                console.log( 'No valid action specified.' );
            }
        },
        authenticateService ({ commit }, data) {
            Vue.http({
                url: ropApiSettings.root,
                method: 'POST',
                headers: { 'X-WP-Nonce': ropApiSettings.nonce },
                params: { 'req': 'authenticate_service' },
                body: data,
                responseType: 'json'
            }).then(function (response) {
                console.log( response.data );
                commit( 'updateAuthenticatedServices', response.data );
            }, function () {
                console.log( 'Error retrieving authenticated services.' );
            })
        },
        removeService ({ commit }, data) {
            Vue.http({
                url: ropApiSettings.root,
                method: 'POST',
                headers: { 'X-WP-Nonce': ropApiSettings.nonce },
                params: { 'req': 'remove_service' },
                body: data,
                responseType: 'json'
            }).then(function (response) {
                console.log( response.data );
                commit( 'updateAuthenticatedServices', response.data );
            }, function () {
                console.log( 'Error retrieving authenticated services.' );
            })
        }
    },
});

//store.dispatch( 'fetchAvailableServices' );