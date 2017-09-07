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
/******/ 	return __webpack_require__(__webpack_require__.s = 1);
/******/ })
/************************************************************************/
/******/ ([
/* 0 */
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

/***/ }),
/* 1 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _variables = __webpack_require__(0);

var _variables2 = _interopRequireDefault(_variables);

var _mainPagePanel = __webpack_require__(8);

var _mainPagePanel2 = _interopRequireDefault(_mainPagePanel);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

window.onload = function () {

    // create a root instance
    new Vue({
        el: '#rop_core',
        data: {
            model: {
                page: _variables2.default.page,
                tabs: _variables2.default.tabs
            }
        },
        components: {
            MainPagePanel: _mainPagePanel2.default
        },
        created: function created() {
            console.log(this.$options.components);
        }
    });
};

/***/ }),
/* 2 */,
/* 3 */,
/* 4 */,
/* 5 */
/***/ (function(module, exports, __webpack_require__) {

var __vue_script__, __vue_template__
__vue_script__ = __webpack_require__(6)
__vue_template__ = __webpack_require__(7)
module.exports = __vue_script__ || {}
if (module.exports.__esModule) module.exports = module.exports.default
if (__vue_template__) { (typeof module.exports === "function" ? module.exports.options : module.exports).template = __vue_template__ }
if (false) {(function () {  module.hot.accept()
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), true)
  if (!hotAPI.compatible) return
  var id = "/var/www/html/wp-base/wp-content/plugins/tweet-old-post/assets/js/src/vue-elements/sign-in-btn.vue"
  if (!module.hot.data) {
    hotAPI.createRecord(id, module.exports)
  } else {
    hotAPI.update(id, module.exports, __vue_template__)
  }
})()}

/***/ }),
/* 6 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


// <template>
//     <button class="btn" :id="serviceId" :class="serviceClass" v-if="!authorized" @click="isAuthorized()" >Sign In w. {{ serviceName }}</button>
//     <button class="btn" :id="serviceId" :class="serviceClass" v-else @click="isAuthorized()" >Sign Out from {{ serviceName }}</button>
// </template>
//
// <script>
module.exports = {
    name: 'sign-in-btn',
    props: ['serviceName', 'model'],
    data: function data() {
        return {
            sharedState: this.model.page.state
        };
    },
    methods: {
        isAuthorized: function isAuthorized() {
            this.model.page.updateService(this.serviceName, !this.authorized);
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

};

/***/ }),
/* 7 */
/***/ (function(module, exports) {

module.exports = "\n    <button class=\"btn\" :id=\"serviceId\" :class=\"serviceClass\" v-if=\"!authorized\" @click=\"isAuthorized()\" >Sign In w. {{ serviceName }}</button>\n    <button class=\"btn\" :id=\"serviceId\" :class=\"serviceClass\" v-else @click=\"isAuthorized()\" >Sign Out from {{ serviceName }}</button>\n";

/***/ }),
/* 8 */
/***/ (function(module, exports, __webpack_require__) {

var __vue_script__, __vue_template__
__vue_script__ = __webpack_require__(9)
__vue_template__ = __webpack_require__(10)
module.exports = __vue_script__ || {}
if (module.exports.__esModule) module.exports = module.exports.default
if (__vue_template__) { (typeof module.exports === "function" ? module.exports.options : module.exports).template = __vue_template__ }
if (false) {(function () {  module.hot.accept()
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), true)
  if (!hotAPI.compatible) return
  var id = "/var/www/html/wp-base/wp-content/plugins/tweet-old-post/assets/js/src/vue-elements/main-page-panel.vue"
  if (!module.hot.data) {
    hotAPI.createRecord(id, module.exports)
  } else {
    hotAPI.update(id, module.exports, __vue_template__)
  }
})()}

/***/ }),
/* 9 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _accountsTabPanel = __webpack_require__(12);

var _accountsTabPanel2 = _interopRequireDefault(_accountsTabPanel);

var _logsTabPanel = __webpack_require__(15);

var _logsTabPanel2 = _interopRequireDefault(_logsTabPanel);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

// <template>
//     <div>
//         <div class="panel title-panel" style="margin-bottom: 40px; padding-bottom: 20px;">
//             <div class="panel-header">
//                 <!--<img src="./../../../img/logo_rop.png" style="float: left; margin-right: 10px;" />-->
//                 <h1 class="d-inline-block">Revive Old Posts</h1><span class="powered"> by <a href="https://themeisle.com" target="_blank"><b>ThemeIsle</b></a></span>
//             </div>
//         </div>
//         <div class="panel">
//             <div class="panel-nav" style="padding: 8px;">
//                 <ul class="tab">
//                     <li class="tab-item" v-for="tab in tabs" :class="{ active: tab.isActive }"><a href="#" @click="switchTab( tab.slug )">{{ tab.name }}</a></li>
//                     <li class="tab-item tab-action">
//                         <div class="form-group">
//                             <label class="form-switch">
//                                 <input type="checkbox" />
//                                 <i class="form-icon"></i> Beta User
//                             </label>
//                             <label class="form-switch">
//                                 <input type="checkbox" />
//                                 <i class="form-icon"></i> Remote Check
//                             </label>
//                         </div>
//                     </li>
//                 </ul>
//             </div>
//
//             <component :is="sharedState.view" :model="model"></component>
//         </div>
//     </div>
// </template>
//
// <script>
module.exports = {
    name: 'main-page-panel',
    props: ['model'],
    data: function data() {
        return {
            tabs: this.model.tabs,
            sharedState: this.model.page.state
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
        'accounts': _accountsTabPanel2.default,
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
        'logs': _logsTabPanel2.default
    }
};
// </script>

/***/ }),
/* 10 */
/***/ (function(module, exports) {

module.exports = "\n    <div>\n        <div class=\"panel title-panel\" style=\"margin-bottom: 40px; padding-bottom: 20px;\">\n            <div class=\"panel-header\">\n                <!--<img src=\"./../../../img/logo_rop.png\" style=\"float: left; margin-right: 10px;\" />-->\n                <h1 class=\"d-inline-block\">Revive Old Posts</h1><span class=\"powered\"> by <a href=\"https://themeisle.com\" target=\"_blank\"><b>ThemeIsle</b></a></span>\n            </div>\n        </div>\n        <div class=\"panel\">\n            <div class=\"panel-nav\" style=\"padding: 8px;\">\n                <ul class=\"tab\">\n                    <li class=\"tab-item\" v-for=\"tab in tabs\" :class=\"{ active: tab.isActive }\"><a href=\"#\" @click=\"switchTab( tab.slug )\">{{ tab.name }}</a></li>\n                    <li class=\"tab-item tab-action\">\n                        <div class=\"form-group\">\n                            <label class=\"form-switch\">\n                                <input type=\"checkbox\" />\n                                <i class=\"form-icon\"></i> Beta User\n                            </label>\n                            <label class=\"form-switch\">\n                                <input type=\"checkbox\" />\n                                <i class=\"form-icon\"></i> Remote Check\n                            </label>\n                        </div>\n                    </li>\n                </ul>\n            </div>\n\n            <component :is=\"sharedState.view\" :model=\"model\"></component>\n        </div>\n    </div>\n";

/***/ }),
/* 11 */,
/* 12 */
/***/ (function(module, exports, __webpack_require__) {

var __vue_script__, __vue_template__
__vue_script__ = __webpack_require__(13)
__vue_template__ = __webpack_require__(14)
module.exports = __vue_script__ || {}
if (module.exports.__esModule) module.exports = module.exports.default
if (__vue_template__) { (typeof module.exports === "function" ? module.exports.options : module.exports).template = __vue_template__ }
if (false) {(function () {  module.hot.accept()
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), true)
  if (!hotAPI.compatible) return
  var id = "/var/www/html/wp-base/wp-content/plugins/tweet-old-post/assets/js/src/vue-elements/accounts-tab-panel.vue"
  if (!module.hot.data) {
    hotAPI.createRecord(id, module.exports)
  } else {
    hotAPI.update(id, module.exports, __vue_template__)
  }
})()}

/***/ }),
/* 13 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _signInBtn = __webpack_require__(5);

var _signInBtn2 = _interopRequireDefault(_signInBtn);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

module.exports = {
    name: 'account-view',
    props: ['model'],
    components: {
        SignInBtn: _signInBtn2.default
    }
    // </script>

}; // <template>
//     <div class="tab-view">
//         <div class="panel-body">
//             <h3>Accounts</h3>
//             <p>This is a <b>Vue.js</b> component.</p>
//             <sign-in-btn service-name="Twitter" :model="model" ></sign-in-btn>
//             <sign-in-btn service-name="Facebook" :model="model" ></sign-in-btn>
//         </div>
//         <div class="panel-footer">
//             <button class="btn btn-primary btn-block">Cool!</button>
//         </div>
//     </div>
// </template>
//
// <script>

/***/ }),
/* 14 */
/***/ (function(module, exports) {

module.exports = "\n    <div class=\"tab-view\">\n        <div class=\"panel-body\">\n            <h3>Accounts</h3>\n            <p>This is a <b>Vue.js</b> component.</p>\n            <sign-in-btn service-name=\"Twitter\" :model=\"model\" ></sign-in-btn>\n            <sign-in-btn service-name=\"Facebook\" :model=\"model\" ></sign-in-btn>\n        </div>\n        <div class=\"panel-footer\">\n            <button class=\"btn btn-primary btn-block\">Cool!</button>\n        </div>\n    </div>\n";

/***/ }),
/* 15 */
/***/ (function(module, exports, __webpack_require__) {

var __vue_script__, __vue_template__
__vue_script__ = __webpack_require__(16)
__vue_template__ = __webpack_require__(17)
module.exports = __vue_script__ || {}
if (module.exports.__esModule) module.exports = module.exports.default
if (__vue_template__) { (typeof module.exports === "function" ? module.exports.options : module.exports).template = __vue_template__ }
if (false) {(function () {  module.hot.accept()
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), true)
  if (!hotAPI.compatible) return
  var id = "/var/www/html/wp-base/wp-content/plugins/tweet-old-post/assets/js/src/vue-elements/logs-tab-panel.vue"
  if (!module.hot.data) {
    hotAPI.createRecord(id, module.exports)
  } else {
    hotAPI.update(id, module.exports, __vue_template__)
  }
})()}

/***/ }),
/* 16 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


// <template>
//     <div class="container">
//         <h3>Logs</h3>
//         <div class="columns">
//             <div class="column col-12">
//                 <pre class="code" data-lang="Vue.js">
//                     <code>{{ logs }}</code>
//                 </pre>
//             </div>
//         </div>
//     </div>
// </template>
//
// <script>
module.exports = {
    name: 'logs-view',
    props: ['model'],
    data: function data() {
        return {
            logs: this.model.page.logs
        };
    }
    // </script>

};

/***/ }),
/* 17 */
/***/ (function(module, exports) {

module.exports = "\n    <div class=\"container\">\n        <h3>Logs</h3>\n        <div class=\"columns\">\n            <div class=\"column col-12\">\n                <pre class=\"code\" data-lang=\"Vue.js\">\n                    <code>{{ logs }}</code>\n                </pre>\n            </div>\n        </div>\n    </div>\n";

/***/ })
/******/ ]);