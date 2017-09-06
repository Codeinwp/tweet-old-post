/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, {
/******/ 				configurable: false,
/******/ 				enumerable: true,
/******/ 				get: getter
/******/ 			});
/******/ 		}
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "";
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 0);
/******/ })
/************************************************************************/
/******/ ([
/* 0 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


__webpack_require__(1);

function logMessage(log, message) {
    return log.concat(message + '\n');
}

window.onload = function () {
    var page = {
        debug: true,
        logs: 'Here starts the log \n\n',
        state: {
            authorizedService: {
                twitter: false,
                facebook: false
            },
            view: 'accounts'
        },
        updateService: function updateService(serviceName, serviceStatus) {
            if (this.debug) console.log('updateService triggered by', serviceName);
            if (this.debug) this.logs = logMessage(this.logs, 'updateService triggered by ' + serviceName);
            this.state.authorizedService[serviceName.toLowerCase()] = serviceStatus;
        }
    };

    var tabs = [{
        name: 'Accounts',
        slug: 'accounts',
        isActive: true
    }, {
        name: 'General Settings',
        slug: 'settings',
        isActive: false
    }, {
        name: 'Post Format',
        slug: 'post',
        isActive: false
    }, {
        name: 'Custom Schedule',
        slug: 'schedule',
        isActive: false
    }, {
        name: 'Logs',
        slug: 'logs',
        isActive: false
    }];

    // register
    Vue.component('my-component', {
        template: '#panel-template',
        data: function data() {
            return {
                tabs: tabs,
                sharedState: page.state
            };
        },
        methods: {
            switchTab: function switchTab(slug) {
                for (var tab in this.tabs) {
                    this.tabs[tab].isActive = false;
                    if (this.tabs[tab].slug === slug) {
                        this.tabs[tab].isActive = true;
                        this.sharedState.view = slug;
                    }
                }
            }
        },
        components: {
            accounts: {
                name: 'account-view',
                template: '#account-template'
            },
            settings: {
                name: 'settings-view',
                template: '<span>This is not yet ready</span>'
            },
            post: {
                name: 'post-view',
                template: '<span>This is not yet ready</span>'
            },
            schedule: {
                name: 'schedule-view',
                template: '<span>This is not yet ready</span>'
            },
            logs: {
                name: 'logs-view',
                template: '#logs-template',
                data: function data() {
                    return {
                        logs: page.logs
                    };
                }
            }
        }
    });

    Vue.component('sign-in-btn', {
        props: ['serviceName'],
        template: '#sign-in-button-template',
        data: function data() {
            return {
                sharedState: page.state
            };
        },
        methods: {
            isAuthorized: function isAuthorized() {
                page.updateService(this.serviceName, !this.authorized);
            }
        },
        computed: {
            authorized: function authorized() {
                return page.state.authorizedService[this.serviceName.toLowerCase()];
            },
            serviceClass: function serviceClass() {
                return {
                    'btn-twitter': this.serviceName === 'Twitter',
                    'btn-facebook': this.serviceName === 'Facebook'
                };
            },
            serviceId: function serviceId() {
                return 'service-' + this.serviceName.toLowerCase();
            }
        }
    });
    // create a root instance
    new Vue({
        el: '#rop_core'
    });
};

/***/ }),
/* 1 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


alert('I am loaded!');

/***/ })
/******/ ]);