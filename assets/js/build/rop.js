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


var _variables = __webpack_require__(10);

var _variables2 = _interopRequireDefault(_variables);

var _SignInBtn = __webpack_require__(7);

var _SignInBtn2 = _interopRequireDefault(_SignInBtn);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

window.onload = function () {
    // register
    var myComponent = {
        template: '#panel-template',
        data: function data() {
            return {
                tabs: _variables2.default.tabs,
                sharedState: _variables2.default.page.state
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
                template: '#account-template',
                components: {
                    'sign-in-btn': _SignInBtn2.default
                }
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
                        logs: _variables2.default.page.logs
                    };
                }
            }
        }
    };

    // Vue.component( 'sign-in-btn', SignInBtn );

    // create a root instance
    new Vue({
        el: '#rop_core',
        data: {
            page: _variables2.default.page,
            tabs: _variables2.default.tabs
        },
        components: {
            'my-component': myComponent
        },
        created: function created() {
            console.log(this.$options.components);
        }
    });
};

/***/ }),
/* 1 */,
/* 2 */,
/* 3 */,
/* 4 */,
/* 5 */,
/* 6 */,
/* 7 */
/***/ (function(module, exports, __webpack_require__) {

var __vue_script__, __vue_template__
__vue_script__ = __webpack_require__(8)
__vue_template__ = __webpack_require__(9)
module.exports = __vue_script__ || {}
if (module.exports.__esModule) module.exports = module.exports.default
if (__vue_template__) { (typeof module.exports === "function" ? module.exports.options : module.exports).template = __vue_template__ }
if (false) {(function () {  module.hot.accept()
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), true)
  if (!hotAPI.compatible) return
  var id = "/var/www/html/wp-base/wp-content/plugins/tweet-old-post/assets/js/src/vue-elements/SignInBtn.vue"
  if (!module.hot.data) {
    hotAPI.createRecord(id, module.exports)
  } else {
    hotAPI.update(id, module.exports, __vue_template__)
  }
})()}

/***/ }),
/* 8 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _variables = __webpack_require__(10);

var _variables2 = _interopRequireDefault(_variables);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

module.exports = {
    name: 'sign-in-btn',
    props: ['serviceName'],
    data: function data() {
        return {
            sharedState: _variables2.default.page.state
        };
    },
    methods: {
        isAuthorized: function isAuthorized() {
            _variables2.default.page.updateService(this.serviceName, !this.authorized);
        }
    },
    computed: {
        authorized: function authorized() {
            return this.sharedState.authorizedService[this.serviceName.toLowerCase()];
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
    // </script>

}; // <template>
//     <button class="btn" :id="serviceId" :class="serviceClass" v-if="!authorized" @click="isAuthorized()" >Sign In w. {{ serviceName }}</button>
//     <button class="btn" :id="serviceId" :class="serviceClass" v-else @click="isAuthorized()" >Sign Out from {{ serviceName }}</button>
// </template>
//
// <script>

/***/ }),
/* 9 */
/***/ (function(module, exports) {

module.exports = "\n    <button class=\"btn\" :id=\"serviceId\" :class=\"serviceClass\" v-if=\"!authorized\" @click=\"isAuthorized()\" >Sign In w. {{ serviceName }}</button>\n    <button class=\"btn\" :id=\"serviceId\" :class=\"serviceClass\" v-else @click=\"isAuthorized()\" >Sign Out from {{ serviceName }}</button>\n";

/***/ }),
/* 10 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


function logMessage(log, message) {
    return log.concat(message + '\n');
}

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

module.exports = {
    page: page,
    tabs: tabs
};

/***/ })
/******/ ]);