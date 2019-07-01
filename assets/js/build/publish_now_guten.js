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
/******/ 	__webpack_require__.p = "/";
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 314);
/******/ })
/************************************************************************/
/******/ ({

/***/ 314:
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _asyncToGenerator(fn) { return function () { var gen = fn.apply(this, arguments); return new Promise(function (resolve, reject) { function step(key, arg) { try { var info = gen[key](arg); var value = info.value; } catch (error) { reject(error); return; } if (info.done) { resolve(value); } else { return Promise.resolve(value).then(function (value) { step("next", value); }, function (err) { step("throw", err); }); } } return step("next"); }); }; }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

/**
 * WordPress dependencies.
*/
var __ = wp.i18n.__;
var _wp = wp,
    apiFetch = _wp.apiFetch;
var CheckboxControl = wp.components.CheckboxControl;
var withSelect = wp.data.withSelect;
var Component = wp.element.Component;
var PluginPostStatusInfo = wp.editPost.PluginPostStatusInfo;
var registerPlugin = wp.plugins.registerPlugin;

var ROPPublish = function (_Component) {
	_inherits(ROPPublish, _Component);

	function ROPPublish() {
		_classCallCheck(this, ROPPublish);

		var _this = _possibleConstructorReturn(this, (ROPPublish.__proto__ || Object.getPrototypeOf(ROPPublish)).apply(this, arguments));

		_this.toggleStatus = _this.toggleStatus.bind(_this);
		_this.toggleAccount = _this.toggleAccount.bind(_this);

		_this.state = {
			default: false,
			accounts: {}
		};
		return _this;
	}

	_createClass(ROPPublish, [{
		key: 'componentDidMount',
		value: function () {
			var _ref = _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee() {
				var _this2 = this;

				return regeneratorRuntime.wrap(function _callee$(_context) {
					while (1) {
						switch (_context.prev = _context.next) {
							case 0:
								_context.next = 2;
								return apiFetch({ path: 'tweet-old-post/v8/gutenberg/get_meta/?id=' + this.props.postId }).then(function (response) {
									var accounts = {};

									{
										Object.keys(window.ropApiPublish.accounts).map(function (i) {
											accounts[i] = response.rop_publish_now_accounts.includes(i);
										});
									}

									return _this2.setState({
										default: _this2.props.postPublished ? Boolean(response.rop_publish_now) : Boolean(window.ropApiPublish.action),
										accounts: accounts
									});
								}).catch(function (error) {
									var accounts = {};

									{
										Object.keys(window.ropApiPublish.accounts).map(function (i) {
											accounts[i] = _this2.props.postPublished ? false : Boolean(window.ropApiPublish.action);
										});
									}

									return _this2.setState({
										default: _this2.props.postPublished ? false : Boolean(window.ropApiPublish.action),
										accounts: accounts
									});
								});

							case 2:
								;;

							case 4:
							case 'end':
								return _context.stop();
						}
					}
				}, _callee, this);
			}));

			function componentDidMount() {
				return _ref.apply(this, arguments);
			}

			return componentDidMount;
		}()
	}, {
		key: 'toggleStatus',
		value: function toggleStatus(value) {
			if (value) {
				var accounts = {};

				{
					Object.keys(window.ropApiPublish.accounts).map(function (i) {
						accounts[i] = true;
					});
				}

				this.setState({ accounts: accounts });
			}

			this.setState({ default: !this.state.default });
		}
	}, {
		key: 'toggleAccount',
		value: function toggleAccount(key, value) {
			var accounts = this.state.accounts;
			accounts[key] = value;
			this.setState({ accounts: accounts });
		}
	}, {
		key: 'render',
		value: function render() {
			var _this3 = this;

			if (0 < Object.keys(window.ropApiPublish.accounts).length) {
				return wp.element.createElement(
					PluginPostStatusInfo,
					null,
					wp.element.createElement(
						'div',
						{ className: 'rop-publish-guten' },
						wp.element.createElement(CheckboxControl, {
							label: __('Share immediately via Revive Old Post'),
							checked: this.state.default,
							onChange: this.toggleStatus
						}),
						this.state.default && wp.element.createElement(
							'div',
							{ className: 'rop-publish-guten__list' },
							Object.keys(window.ropApiPublish.accounts).map(function (i) {
								return wp.element.createElement(CheckboxControl, {
									label: window.ropApiPublish.accounts[i].user,
									checked: _this3.state.accounts[i],
									className: 'rop-icon rop-icon-' + window.ropApiPublish.accounts[i].service,
									onChange: function onChange() {
										return _this3.toggleAccount(i, !_this3.state.accounts[i]);
									}
								});
							})
						)
					)
				);
			}

			return null;
		}
	}], [{
		key: 'getDerivedStateFromProps',
		value: function getDerivedStateFromProps(nextProps, state) {
			if ((nextProps.isPublishing || nextProps.postPublished && nextProps.isSaving) && !nextProps.isAutoSaving) {
				wp.apiRequest({ path: '/tweet-old-post/v8/gutenberg/update_meta/?id=' + nextProps.postId, method: 'POST', data: state }).then(function (data) {
					return data;
				}, function (err) {
					return err;
				});
			}
		}
	}]);

	return ROPPublish;
}(Component);

var ROP = withSelect(function (select, _ref2) {
	var forceIsSaving = _ref2.forceIsSaving;

	var _select = select('core/editor'),
	    getCurrentPostId = _select.getCurrentPostId,
	    isCurrentPostPublished = _select.isCurrentPostPublished,
	    isSavingPost = _select.isSavingPost,
	    isPublishingPost = _select.isPublishingPost,
	    isAutosavingPost = _select.isAutosavingPost;

	return {
		postId: getCurrentPostId(),
		postPublished: isCurrentPostPublished(),
		isSaving: forceIsSaving || isSavingPost(),
		isAutoSaving: isAutosavingPost(),
		isPublishing: isPublishingPost()
	};
})(ROPPublish);

registerPlugin('revive-old-post', {
	render: ROP
});

/***/ })

/******/ });