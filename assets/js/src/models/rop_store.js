import Vue from 'vue'
import Vuex from 'vuex'

Vue.use( Vuex )

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
        authenticatedServicces: [],
        registeredAccounts: [],
    },
    getters: {
      getServices( state ) {
          return state.availableServices
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
        getAuthenticatedServices( state ) {

        },
        getRegisteredAccounts( state ) {

        }
    },
    actions: {
        fetchAvailableServices ({ commit }) {
            Vue.http({
                url: ROP_REST_API,
                method: 'POST',
                params: { 'req': 'available_services' }
            }).then(function (response) {
                commit( 'updateAvailableServices', response.data );
            }, function () {
                console.log( 'Error retrieving available services.' )
            })
        }
    }
});

//store.dispatch( 'fetchAvailableServices' );