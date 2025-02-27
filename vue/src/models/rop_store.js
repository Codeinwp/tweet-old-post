// jshint ignore: start

/* global ropApiSettings */
/* exported ropApiSettings */

/* eslint-disable*/

import Vue from 'vue'
import Vuex from 'vuex'
import VueResource from 'vue-resource'
import VueLogger from 'vuejs-logger'

const logOptions = {
    // required ['debug', 'info', 'warn', 'error', 'fatal']
    logLevel: ((ropApiSettings.debug === 'yes') ? 'debug' : 'error'),
    // optional : defaults to false if not specified
    stringifyArguments: false,
    // optional : defaults to false if not specified
    showLogLevel: false,
    // optional : defaults to false if not specified
    showMethodName: false,
    // optional : defaults to '|' if not specified
    separator: '|',
    // optional : defaults to false if not specified
    showConsoleColors: true
};
Vue.use(Vuex);
Vue.use(VueResource);
Vue.use(VueLogger, logOptions);


// In the futures, this should be replaced with Pina library.
// For new features that do not need the existing store data, use the new Pina.
export default new Vuex.Store({
    state: {
        page: {
            debug: false,
            logs: [],
            view: 'accounts',
            template: 'accounts',
        },
        editPopup: {
            accountId: '',
            canShow: false,
        },
        cron_status: {},
        toast: {
            type: 'success',
            show: false,
            title: '',
            message: ''
        },
        ajaxLoader: false,
        api_not_available: false,
        auth_in_progress: false,
        displayTabs: [
            {
                name: ropApiSettings.labels.accounts.menu_item,
                slug: 'accounts',
                view: 'accounts',
                isActive: true
            },
            {
                name: ropApiSettings.labels.settings.menu_item,
                slug: 'settings',
                view: 'settings',
                isActive: false
            },
            {
                name: ropApiSettings.labels.post_format.menu_item,
                slug: 'post-format',
                view: 'accounts-selector',
                isActive: false
            },
            {
                name: ropApiSettings.labels.schedule.menu_item,
                slug: 'schedule',
                view: 'accounts-selector',
                isActive: false
            },
            {
                name: ropApiSettings.labels.queue.menu_item,
                slug: 'queue',
                view: 'queue',
                isActive: false
            },
            {
                name: ropApiSettings.labels.logs.menu_item,
                slug: 'logs',
                view: 'logs',
                isActive: false
            }
        ],
        licenseDataView: ropApiSettings.license_data_view,
        license: parseInt(ropApiSettings.license_type),
        labels: ropApiSettings.labels,
        availableServices: [],
        generalSettings: [],
        authenticatedServices: [],
        activeAccounts: {},
        activePostFormat: [],
        activeSchedule: [],
        queue: {},
        publish_now: ropApiSettings.publish_now,
        hide_preloading: 0,
        fb_exception_toast: ropApiSettings.fb_domain_toast_display,
        /**
         * Local or Remote Cron Job System.
         * true for remote.
         *
         * @category New Cron System
         */
        rop_cron_remote: ropApiSettings.rop_cron_remote,
        dom_updated: false,
        tracking: Boolean( ropApiSettings.tracking ),
        is_new_user: Boolean( ropApiSettings.is_new_user ),
    },
    mutations: {

        setEditPopup(state, data) {
            state.editPopup = data
        },

        setEditPopupShowPermission(state, canShow) {
            state.editPopup.canShow = canShow
        },

        setTabView(state, view) {
            Vue.$log.debug('Changing tab to  ', view);
            for (let tab in state.displayTabs) {
                state.displayTabs[tab].isActive = false
                if (state.displayTabs[tab].slug === view) {
                    state.displayTabs[tab].isActive = true
                    state.page.view = state.displayTabs[tab].slug
                    state.page.template = state.displayTabs[tab].view
                }
            }
        },
        setAjaxState(state, data) {
            state.ajaxLoader = data
        },
        apiNotAvailable(state, data) {
            state.api_not_available = data
        },
        preloading_change(state, data) {
            state.hide_preloading = data;
        },
        updateState(state, {stateData, requestName}) {
            Vue.$log.debug('State change for ', requestName, ' With value: ', stateData);
            switch (requestName) {
                /**
                 * @category New Cron System
                 */
                case 'update_cron_type':
                    state.rop_cron_remote = stateData;
                    break;
                case 'update_cron_type_agreement':
                    state.rop_cron_remote = stateData;
                    break;
                case 'manage_cron':
                    state.cron_status = stateData;
                    break;
                case 'get_log':
                    state.page.logs = stateData;
                    break;
                case 'get_toast':
                    state.page.logs = stateData;
                    break;
                case 'fb_exception_toast':
                    state.fb_exception_toast = stateData.display;
                    break;
                case 'update_settings_toggle':
                case 'get_general_settings':
                    state.generalSettings = stateData
                    break
                case 'update_selected_post_types':
                    state.generalSettings.selected_post_types = stateData
                    for (let index in state.generalSettings.available_post_types) {
                        state.generalSettings.available_post_types[index].selected = false
                        for (let indexSelected in stateData) {
                            if (state.generalSettings.available_post_types[index].value === stateData[indexSelected].value) {
                                state.generalSettings.available_post_types[index].selected = true
                            }
                        }
                    }
                    break
                case 'update_selected_taxonomies':
                    state.generalSettings.selected_taxonomies = stateData
                    for (let index in state.generalSettings.available_taxonomies) {
                        state.generalSettings.available_taxonomies[index].selected = false
                        for (let indexSelected in stateData) {
                            if (state.generalSettings.available_taxonomies[index].value === stateData[indexSelected].value || state.generalSettings.available_taxonomies[index].parent === stateData[indexSelected].value) {
                                state.generalSettings.available_taxonomies[index].selected = true
                            }
                        }
                    }
                    break
                case 'update_selected_posts':
                    state.generalSettings.selected_posts = stateData
                    break
                case 'get_available_services':
                    state.availableServices = stateData
                    break
                case 'get_authenticated_services':
                case 'remove_service':
                    state.authenticatedServices = stateData;
                    state.hide_preloading++;
                    break
                case 'authenticate_service':
                    state.authenticatedServices = stateData
                    state.auth_in_progress = false
                    //state.activeAccounts = stateData
                    break
                case 'check_account_fb':
                case 'add_account_fb':
                    state.activeAccounts = stateData
                    state.auth_in_progress = true
                    break
                case 'get_active_accounts':
                case 'update_active_accounts':
                case 'remove_account':
                    if ( 'remove_account' === requestName ) {
                        break;
                    }
                    state.activeAccounts = stateData
                    break
                case 'get_taxonomies':
                    state.generalSettings.available_taxonomies = stateData
                    break
                case 'get_posts':
                    if (stateData.page === 1) {
                        state.generalSettings.available_posts = stateData.posts;
                    } else {
                        state.generalSettings.available_posts = state.generalSettings.available_posts.concat(stateData.posts);
                    }

                    break
                case 'get_post_format':
                case 'save_post_format':
                case 'reset_post_format':
                    state.activePostFormat = stateData
                    break
                case 'reset_accounts':
                    state.activeAccounts = {};
                    state.authenticatedServices = [];
                    break
                case 'get_shortner_credentials':
                    state.activePostFormat['shortner_credentials'] = stateData
                    break
                case 'get_schedule':
                case 'save_schedule':
                case 'reset_schedule':
                    state.activeSchedule = stateData
                    break
                case 'get_queue':
                case 'update_queue_event':
                case 'publish_queue_event':
                case 'skip_queue_event':
                case 'block_queue_event':
                    state.queue = stateData

                    break
                case 'update_toast':
                    state.toast = stateData;
                    Vue.$log.debug('Toast updated ', requestName);
                    break
                case 'toggle_account':
                case 'exclude_post':
                case 'exclude_post_batch':
                case 'toggle_tracking':    

                    break
                default:
                    Vue.$log.error('No state request for ', requestName);
            }
        }
    },
    actions: {
        fetchAJAX({commit}, data) {
            if (data.req !== '') {
                commit('setAjaxState', true)
                Vue.http({
                    url: ropApiSettings.root,
                    method: 'POST',
                    headers: {'X-WP-Nonce': ropApiSettings.nonce},
                    params: {'req': data.req},
                    body: data.data,
                    responseType: 'json'
                }).then(function (response) {
                    commit('setAjaxState', false)
                    let display = false;
                    if (display) {
                        let toast = {
                            type: response.data.status,
                            show: true,
                            title: response.data.title,
                            message: response.data.message
                        }
                        commit('updateState', {stateData: toast, requestName: 'update_toast'})
                    }
                    let stateData = response.data
                    if (response.data.data) {
                        stateData = response.data.data
                    }
                    let requestName = data.req
                    if (data.updateState !== false) {
                        commit('updateState', {stateData, requestName})
                    }
                }, function () {
                    commit('setAjaxState', false);
                    Vue.$log.error('Error when trying to do request: ', data.req);
                })
            }
            return false
        },
        fetchAJAXPromise({commit}, data) {
            if (data.req !== '') {
                commit('setAjaxState', true)
                return new Promise((resolve, reject) => {
                    Vue.http({
                        url: ropApiSettings.root,
                        method: 'POST',
                        headers: {'X-WP-Nonce': ropApiSettings.nonce},
                        params: {'req': data.req},
                        body: data.data,
                        responseType: 'json'
                    }).then(function (response) {
                        commit('setAjaxState', false)
                        let stateData = response.data
                        if (response.data.data) {
                            stateData = response.data.data
                        }
                        let requestName = data.req
                        resolve(stateData)
                        if (data.updateState !== false) {
                            commit('updateState', {stateData, requestName})
                        }
                    }, function () {
                        commit('setAjaxState', false);
                        commit('apiNotAvailable', true);

                        Vue.$log.error('Error when trying to do request: ', data.req);
                    }).catch(error => {
                        commit('setAjaxState', false);
                        commit('apiNotAvailable', true);
                        commit('preloading_change', 1);
                        Vue.$log.error('Error when getting response for: ', data.req, error);
                    })
                })
            }
            return false
        }
    }
})
