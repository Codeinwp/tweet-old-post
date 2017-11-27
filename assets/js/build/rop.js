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
/******/ 	return __webpack_require__(__webpack_require__.s = 34);
/******/ })
/************************************************************************/
/******/ ([
/* 0 */
/***/ (function(module, exports) {

/*
	MIT License http://www.opensource.org/licenses/mit-license.php
	Author Tobias Koppers @sokra
*/
// css base code, injected by the css-loader
module.exports = function() {
	var list = [];

	// return the list of modules as css string
	list.toString = function toString() {
		var result = [];
		for(var i = 0; i < this.length; i++) {
			var item = this[i];
			if(item[2]) {
				result.push("@media " + item[2] + "{" + item[1] + "}");
			} else {
				result.push(item[1]);
			}
		}
		return result.join("");
	};

	// import a list of modules into the list
	list.i = function(modules, mediaQuery) {
		if(typeof modules === "string")
			modules = [[null, modules, ""]];
		var alreadyImportedModules = {};
		for(var i = 0; i < this.length; i++) {
			var id = this[i][0];
			if(typeof id === "number")
				alreadyImportedModules[id] = true;
		}
		for(i = 0; i < modules.length; i++) {
			var item = modules[i];
			// skip already imported module
			// this implementation is not 100% perfect for weird media query combinations
			//  when a module is imported multiple times with different media queries.
			//  I hope this will never occur (Hey this way we have smaller bundles)
			if(typeof item[0] !== "number" || !alreadyImportedModules[item[0]]) {
				if(mediaQuery && !item[2]) {
					item[2] = mediaQuery;
				} else if(mediaQuery) {
					item[2] = "(" + item[2] + ") and (" + mediaQuery + ")";
				}
				list.push(item);
			}
		}
	};
	return list;
};


/***/ }),
/* 1 */
/***/ (function(module, exports) {

/*
	MIT License http://www.opensource.org/licenses/mit-license.php
	Author Tobias Koppers @sokra
*/
var stylesInDom = {},
	memoize = function(fn) {
		var memo;
		return function () {
			if (typeof memo === "undefined") memo = fn.apply(this, arguments);
			return memo;
		};
	},
	isOldIE = memoize(function() {
		return /msie [6-9]\b/.test(self.navigator.userAgent.toLowerCase());
	}),
	getHeadElement = memoize(function () {
		return document.head || document.getElementsByTagName("head")[0];
	}),
	singletonElement = null,
	singletonCounter = 0,
	styleElementsInsertedAtTop = [];

module.exports = function(list, options) {
	if(typeof DEBUG !== "undefined" && DEBUG) {
		if(typeof document !== "object") throw new Error("The style-loader cannot be used in a non-browser environment");
	}

	options = options || {};
	// Force single-tag solution on IE6-9, which has a hard limit on the # of <style>
	// tags it will allow on a page
	if (typeof options.singleton === "undefined") options.singleton = isOldIE();

	// By default, add <style> tags to the bottom of <head>.
	if (typeof options.insertAt === "undefined") options.insertAt = "bottom";

	var styles = listToStyles(list);
	addStylesToDom(styles, options);

	return function update(newList) {
		var mayRemove = [];
		for(var i = 0; i < styles.length; i++) {
			var item = styles[i];
			var domStyle = stylesInDom[item.id];
			domStyle.refs--;
			mayRemove.push(domStyle);
		}
		if(newList) {
			var newStyles = listToStyles(newList);
			addStylesToDom(newStyles, options);
		}
		for(var i = 0; i < mayRemove.length; i++) {
			var domStyle = mayRemove[i];
			if(domStyle.refs === 0) {
				for(var j = 0; j < domStyle.parts.length; j++)
					domStyle.parts[j]();
				delete stylesInDom[domStyle.id];
			}
		}
	};
}

function addStylesToDom(styles, options) {
	for(var i = 0; i < styles.length; i++) {
		var item = styles[i];
		var domStyle = stylesInDom[item.id];
		if(domStyle) {
			domStyle.refs++;
			for(var j = 0; j < domStyle.parts.length; j++) {
				domStyle.parts[j](item.parts[j]);
			}
			for(; j < item.parts.length; j++) {
				domStyle.parts.push(addStyle(item.parts[j], options));
			}
		} else {
			var parts = [];
			for(var j = 0; j < item.parts.length; j++) {
				parts.push(addStyle(item.parts[j], options));
			}
			stylesInDom[item.id] = {id: item.id, refs: 1, parts: parts};
		}
	}
}

function listToStyles(list) {
	var styles = [];
	var newStyles = {};
	for(var i = 0; i < list.length; i++) {
		var item = list[i];
		var id = item[0];
		var css = item[1];
		var media = item[2];
		var sourceMap = item[3];
		var part = {css: css, media: media, sourceMap: sourceMap};
		if(!newStyles[id])
			styles.push(newStyles[id] = {id: id, parts: [part]});
		else
			newStyles[id].parts.push(part);
	}
	return styles;
}

function insertStyleElement(options, styleElement) {
	var head = getHeadElement();
	var lastStyleElementInsertedAtTop = styleElementsInsertedAtTop[styleElementsInsertedAtTop.length - 1];
	if (options.insertAt === "top") {
		if(!lastStyleElementInsertedAtTop) {
			head.insertBefore(styleElement, head.firstChild);
		} else if(lastStyleElementInsertedAtTop.nextSibling) {
			head.insertBefore(styleElement, lastStyleElementInsertedAtTop.nextSibling);
		} else {
			head.appendChild(styleElement);
		}
		styleElementsInsertedAtTop.push(styleElement);
	} else if (options.insertAt === "bottom") {
		head.appendChild(styleElement);
	} else {
		throw new Error("Invalid value for parameter 'insertAt'. Must be 'top' or 'bottom'.");
	}
}

function removeStyleElement(styleElement) {
	styleElement.parentNode.removeChild(styleElement);
	var idx = styleElementsInsertedAtTop.indexOf(styleElement);
	if(idx >= 0) {
		styleElementsInsertedAtTop.splice(idx, 1);
	}
}

function createStyleElement(options) {
	var styleElement = document.createElement("style");
	styleElement.type = "text/css";
	insertStyleElement(options, styleElement);
	return styleElement;
}

function createLinkElement(options) {
	var linkElement = document.createElement("link");
	linkElement.rel = "stylesheet";
	insertStyleElement(options, linkElement);
	return linkElement;
}

function addStyle(obj, options) {
	var styleElement, update, remove;

	if (options.singleton) {
		var styleIndex = singletonCounter++;
		styleElement = singletonElement || (singletonElement = createStyleElement(options));
		update = applyToSingletonTag.bind(null, styleElement, styleIndex, false);
		remove = applyToSingletonTag.bind(null, styleElement, styleIndex, true);
	} else if(obj.sourceMap &&
		typeof URL === "function" &&
		typeof URL.createObjectURL === "function" &&
		typeof URL.revokeObjectURL === "function" &&
		typeof Blob === "function" &&
		typeof btoa === "function") {
		styleElement = createLinkElement(options);
		update = updateLink.bind(null, styleElement);
		remove = function() {
			removeStyleElement(styleElement);
			if(styleElement.href)
				URL.revokeObjectURL(styleElement.href);
		};
	} else {
		styleElement = createStyleElement(options);
		update = applyToTag.bind(null, styleElement);
		remove = function() {
			removeStyleElement(styleElement);
		};
	}

	update(obj);

	return function updateStyle(newObj) {
		if(newObj) {
			if(newObj.css === obj.css && newObj.media === obj.media && newObj.sourceMap === obj.sourceMap)
				return;
			update(obj = newObj);
		} else {
			remove();
		}
	};
}

var replaceText = (function () {
	var textStore = [];

	return function (index, replacement) {
		textStore[index] = replacement;
		return textStore.filter(Boolean).join('\n');
	};
})();

function applyToSingletonTag(styleElement, index, remove, obj) {
	var css = remove ? "" : obj.css;

	if (styleElement.styleSheet) {
		styleElement.styleSheet.cssText = replaceText(index, css);
	} else {
		var cssNode = document.createTextNode(css);
		var childNodes = styleElement.childNodes;
		if (childNodes[index]) styleElement.removeChild(childNodes[index]);
		if (childNodes.length) {
			styleElement.insertBefore(cssNode, childNodes[index]);
		} else {
			styleElement.appendChild(cssNode);
		}
	}
}

function applyToTag(styleElement, obj) {
	var css = obj.css;
	var media = obj.media;

	if(media) {
		styleElement.setAttribute("media", media)
	}

	if(styleElement.styleSheet) {
		styleElement.styleSheet.cssText = css;
	} else {
		while(styleElement.firstChild) {
			styleElement.removeChild(styleElement.firstChild);
		}
		styleElement.appendChild(document.createTextNode(css));
	}
}

function updateLink(linkElement, obj) {
	var css = obj.css;
	var sourceMap = obj.sourceMap;

	if(sourceMap) {
		// http://stackoverflow.com/a/26603875
		css += "\n/*# sourceMappingURL=data:application/json;base64," + btoa(unescape(encodeURIComponent(JSON.stringify(sourceMap)))) + " */";
	}

	var blob = new Blob([css], { type: "text/css" });

	var oldSrc = linkElement.href;

	linkElement.href = URL.createObjectURL(blob);

	if(oldSrc)
		URL.revokeObjectURL(oldSrc);
}


/***/ }),
/* 2 */
/***/ (function(module, exports) {

// https://github.com/zloirock/core-js/issues/86#issuecomment-115759028
var global = module.exports = typeof window != 'undefined' && window.Math == Math
  ? window : typeof self != 'undefined' && self.Math == Math ? self
  // eslint-disable-next-line no-new-func
  : Function('return this')();
if (typeof __g == 'number') __g = global; // eslint-disable-line no-undef


/***/ }),
/* 3 */
/***/ (function(module, exports) {

var core = module.exports = { version: '2.5.1' };
if (typeof __e == 'number') __e = core; // eslint-disable-line no-undef


/***/ }),
/* 4 */
/***/ (function(module, exports, __webpack_require__) {

var store = __webpack_require__(25)('wks');
var uid = __webpack_require__(26);
var Symbol = __webpack_require__(2).Symbol;
var USE_SYMBOL = typeof Symbol == 'function';

var $exports = module.exports = function (name) {
  return store[name] || (store[name] =
    USE_SYMBOL && Symbol[name] || (USE_SYMBOL ? Symbol : uid)('Symbol.' + name));
};

$exports.store = store;


/***/ }),
/* 5 */
/***/ (function(module, exports, __webpack_require__) {

var dP = __webpack_require__(17);
var createDesc = __webpack_require__(30);
module.exports = __webpack_require__(9) ? function (object, key, value) {
  return dP.f(object, key, createDesc(1, value));
} : function (object, key, value) {
  object[key] = value;
  return object;
};


/***/ }),
/* 6 */
/***/ (function(module, exports, __webpack_require__) {

module.exports = { "default": __webpack_require__(47), __esModule: true };

/***/ }),
/* 7 */
/***/ (function(module, exports) {

var hasOwnProperty = {}.hasOwnProperty;
module.exports = function (it, key) {
  return hasOwnProperty.call(it, key);
};


/***/ }),
/* 8 */
/***/ (function(module, exports, __webpack_require__) {

var isObject = __webpack_require__(18);
module.exports = function (it) {
  if (!isObject(it)) throw TypeError(it + ' is not an object!');
  return it;
};


/***/ }),
/* 9 */
/***/ (function(module, exports, __webpack_require__) {

// Thank's IE8 for his funny defineProperty
module.exports = !__webpack_require__(19)(function () {
  return Object.defineProperty({}, 'a', { get: function () { return 7; } }).a != 7;
});


/***/ }),
/* 10 */
/***/ (function(module, exports) {

module.exports = {};


/***/ }),
/* 11 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
/* WEBPACK VAR INJECTION */(function(process, global) {/*!
 * Vue.js v2.4.4
 * (c) 2014-2017 Evan You
 * Released under the MIT License.
 */
/*  */

// these helpers produces better vm code in JS engines due to their
// explicitness and function inlining
function isUndef (v) {
  return v === undefined || v === null
}

function isDef (v) {
  return v !== undefined && v !== null
}

function isTrue (v) {
  return v === true
}

function isFalse (v) {
  return v === false
}

/**
 * Check if value is primitive
 */
function isPrimitive (value) {
  return (
    typeof value === 'string' ||
    typeof value === 'number' ||
    typeof value === 'boolean'
  )
}

/**
 * Quick object check - this is primarily used to tell
 * Objects from primitive values when we know the value
 * is a JSON-compliant type.
 */
function isObject (obj) {
  return obj !== null && typeof obj === 'object'
}

var _toString = Object.prototype.toString;

/**
 * Strict object type check. Only returns true
 * for plain JavaScript objects.
 */
function isPlainObject (obj) {
  return _toString.call(obj) === '[object Object]'
}

function isRegExp (v) {
  return _toString.call(v) === '[object RegExp]'
}

/**
 * Check if val is a valid array index.
 */
function isValidArrayIndex (val) {
  var n = parseFloat(val);
  return n >= 0 && Math.floor(n) === n && isFinite(val)
}

/**
 * Convert a value to a string that is actually rendered.
 */
function toString (val) {
  return val == null
    ? ''
    : typeof val === 'object'
      ? JSON.stringify(val, null, 2)
      : String(val)
}

/**
 * Convert a input value to a number for persistence.
 * If the conversion fails, return original string.
 */
function toNumber (val) {
  var n = parseFloat(val);
  return isNaN(n) ? val : n
}

/**
 * Make a map and return a function for checking if a key
 * is in that map.
 */
function makeMap (
  str,
  expectsLowerCase
) {
  var map = Object.create(null);
  var list = str.split(',');
  for (var i = 0; i < list.length; i++) {
    map[list[i]] = true;
  }
  return expectsLowerCase
    ? function (val) { return map[val.toLowerCase()]; }
    : function (val) { return map[val]; }
}

/**
 * Check if a tag is a built-in tag.
 */
var isBuiltInTag = makeMap('slot,component', true);

/**
 * Check if a attribute is a reserved attribute.
 */
var isReservedAttribute = makeMap('key,ref,slot,is');

/**
 * Remove an item from an array
 */
function remove (arr, item) {
  if (arr.length) {
    var index = arr.indexOf(item);
    if (index > -1) {
      return arr.splice(index, 1)
    }
  }
}

/**
 * Check whether the object has the property.
 */
var hasOwnProperty = Object.prototype.hasOwnProperty;
function hasOwn (obj, key) {
  return hasOwnProperty.call(obj, key)
}

/**
 * Create a cached version of a pure function.
 */
function cached (fn) {
  var cache = Object.create(null);
  return (function cachedFn (str) {
    var hit = cache[str];
    return hit || (cache[str] = fn(str))
  })
}

/**
 * Camelize a hyphen-delimited string.
 */
var camelizeRE = /-(\w)/g;
var camelize = cached(function (str) {
  return str.replace(camelizeRE, function (_, c) { return c ? c.toUpperCase() : ''; })
});

/**
 * Capitalize a string.
 */
var capitalize = cached(function (str) {
  return str.charAt(0).toUpperCase() + str.slice(1)
});

/**
 * Hyphenate a camelCase string.
 */
var hyphenateRE = /\B([A-Z])/g;
var hyphenate = cached(function (str) {
  return str.replace(hyphenateRE, '-$1').toLowerCase()
});

/**
 * Simple bind, faster than native
 */
function bind (fn, ctx) {
  function boundFn (a) {
    var l = arguments.length;
    return l
      ? l > 1
        ? fn.apply(ctx, arguments)
        : fn.call(ctx, a)
      : fn.call(ctx)
  }
  // record original fn length
  boundFn._length = fn.length;
  return boundFn
}

/**
 * Convert an Array-like object to a real Array.
 */
function toArray (list, start) {
  start = start || 0;
  var i = list.length - start;
  var ret = new Array(i);
  while (i--) {
    ret[i] = list[i + start];
  }
  return ret
}

/**
 * Mix properties into target object.
 */
function extend (to, _from) {
  for (var key in _from) {
    to[key] = _from[key];
  }
  return to
}

/**
 * Merge an Array of Objects into a single Object.
 */
function toObject (arr) {
  var res = {};
  for (var i = 0; i < arr.length; i++) {
    if (arr[i]) {
      extend(res, arr[i]);
    }
  }
  return res
}

/**
 * Perform no operation.
 * Stubbing args to make Flow happy without leaving useless transpiled code
 * with ...rest (https://flow.org/blog/2017/05/07/Strict-Function-Call-Arity/)
 */
function noop (a, b, c) {}

/**
 * Always return false.
 */
var no = function (a, b, c) { return false; };

/**
 * Return same value
 */
var identity = function (_) { return _; };

/**
 * Generate a static keys string from compiler modules.
 */
function genStaticKeys (modules) {
  return modules.reduce(function (keys, m) {
    return keys.concat(m.staticKeys || [])
  }, []).join(',')
}

/**
 * Check if two values are loosely equal - that is,
 * if they are plain objects, do they have the same shape?
 */
function looseEqual (a, b) {
  if (a === b) { return true }
  var isObjectA = isObject(a);
  var isObjectB = isObject(b);
  if (isObjectA && isObjectB) {
    try {
      var isArrayA = Array.isArray(a);
      var isArrayB = Array.isArray(b);
      if (isArrayA && isArrayB) {
        return a.length === b.length && a.every(function (e, i) {
          return looseEqual(e, b[i])
        })
      } else if (!isArrayA && !isArrayB) {
        var keysA = Object.keys(a);
        var keysB = Object.keys(b);
        return keysA.length === keysB.length && keysA.every(function (key) {
          return looseEqual(a[key], b[key])
        })
      } else {
        /* istanbul ignore next */
        return false
      }
    } catch (e) {
      /* istanbul ignore next */
      return false
    }
  } else if (!isObjectA && !isObjectB) {
    return String(a) === String(b)
  } else {
    return false
  }
}

function looseIndexOf (arr, val) {
  for (var i = 0; i < arr.length; i++) {
    if (looseEqual(arr[i], val)) { return i }
  }
  return -1
}

/**
 * Ensure a function is called only once.
 */
function once (fn) {
  var called = false;
  return function () {
    if (!called) {
      called = true;
      fn.apply(this, arguments);
    }
  }
}

var SSR_ATTR = 'data-server-rendered';

var ASSET_TYPES = [
  'component',
  'directive',
  'filter'
];

var LIFECYCLE_HOOKS = [
  'beforeCreate',
  'created',
  'beforeMount',
  'mounted',
  'beforeUpdate',
  'updated',
  'beforeDestroy',
  'destroyed',
  'activated',
  'deactivated'
];

/*  */

var config = ({
  /**
   * Option merge strategies (used in core/util/options)
   */
  optionMergeStrategies: Object.create(null),

  /**
   * Whether to suppress warnings.
   */
  silent: false,

  /**
   * Show production mode tip message on boot?
   */
  productionTip: process.env.NODE_ENV !== 'production',

  /**
   * Whether to enable devtools
   */
  devtools: process.env.NODE_ENV !== 'production',

  /**
   * Whether to record perf
   */
  performance: false,

  /**
   * Error handler for watcher errors
   */
  errorHandler: null,

  /**
   * Warn handler for watcher warns
   */
  warnHandler: null,

  /**
   * Ignore certain custom elements
   */
  ignoredElements: [],

  /**
   * Custom user key aliases for v-on
   */
  keyCodes: Object.create(null),

  /**
   * Check if a tag is reserved so that it cannot be registered as a
   * component. This is platform-dependent and may be overwritten.
   */
  isReservedTag: no,

  /**
   * Check if an attribute is reserved so that it cannot be used as a component
   * prop. This is platform-dependent and may be overwritten.
   */
  isReservedAttr: no,

  /**
   * Check if a tag is an unknown element.
   * Platform-dependent.
   */
  isUnknownElement: no,

  /**
   * Get the namespace of an element
   */
  getTagNamespace: noop,

  /**
   * Parse the real tag name for the specific platform.
   */
  parsePlatformTagName: identity,

  /**
   * Check if an attribute must be bound using property, e.g. value
   * Platform-dependent.
   */
  mustUseProp: no,

  /**
   * Exposed for legacy reasons
   */
  _lifecycleHooks: LIFECYCLE_HOOKS
});

/*  */

var emptyObject = Object.freeze({});

/**
 * Check if a string starts with $ or _
 */
function isReserved (str) {
  var c = (str + '').charCodeAt(0);
  return c === 0x24 || c === 0x5F
}

/**
 * Define a property.
 */
function def (obj, key, val, enumerable) {
  Object.defineProperty(obj, key, {
    value: val,
    enumerable: !!enumerable,
    writable: true,
    configurable: true
  });
}

/**
 * Parse simple path.
 */
var bailRE = /[^\w.$]/;
function parsePath (path) {
  if (bailRE.test(path)) {
    return
  }
  var segments = path.split('.');
  return function (obj) {
    for (var i = 0; i < segments.length; i++) {
      if (!obj) { return }
      obj = obj[segments[i]];
    }
    return obj
  }
}

/*  */

var warn = noop;
var tip = noop;
var formatComponentName = (null); // work around flow check

if (process.env.NODE_ENV !== 'production') {
  var hasConsole = typeof console !== 'undefined';
  var classifyRE = /(?:^|[-_])(\w)/g;
  var classify = function (str) { return str
    .replace(classifyRE, function (c) { return c.toUpperCase(); })
    .replace(/[-_]/g, ''); };

  warn = function (msg, vm) {
    var trace = vm ? generateComponentTrace(vm) : '';

    if (config.warnHandler) {
      config.warnHandler.call(null, msg, vm, trace);
    } else if (hasConsole && (!config.silent)) {
      console.error(("[Vue warn]: " + msg + trace));
    }
  };

  tip = function (msg, vm) {
    if (hasConsole && (!config.silent)) {
      console.warn("[Vue tip]: " + msg + (
        vm ? generateComponentTrace(vm) : ''
      ));
    }
  };

  formatComponentName = function (vm, includeFile) {
    if (vm.$root === vm) {
      return '<Root>'
    }
    var name = typeof vm === 'string'
      ? vm
      : typeof vm === 'function' && vm.options
        ? vm.options.name
        : vm._isVue
          ? vm.$options.name || vm.$options._componentTag
          : vm.name;

    var file = vm._isVue && vm.$options.__file;
    if (!name && file) {
      var match = file.match(/([^/\\]+)\.vue$/);
      name = match && match[1];
    }

    return (
      (name ? ("<" + (classify(name)) + ">") : "<Anonymous>") +
      (file && includeFile !== false ? (" at " + file) : '')
    )
  };

  var repeat = function (str, n) {
    var res = '';
    while (n) {
      if (n % 2 === 1) { res += str; }
      if (n > 1) { str += str; }
      n >>= 1;
    }
    return res
  };

  var generateComponentTrace = function (vm) {
    if (vm._isVue && vm.$parent) {
      var tree = [];
      var currentRecursiveSequence = 0;
      while (vm) {
        if (tree.length > 0) {
          var last = tree[tree.length - 1];
          if (last.constructor === vm.constructor) {
            currentRecursiveSequence++;
            vm = vm.$parent;
            continue
          } else if (currentRecursiveSequence > 0) {
            tree[tree.length - 1] = [last, currentRecursiveSequence];
            currentRecursiveSequence = 0;
          }
        }
        tree.push(vm);
        vm = vm.$parent;
      }
      return '\n\nfound in\n\n' + tree
        .map(function (vm, i) { return ("" + (i === 0 ? '---> ' : repeat(' ', 5 + i * 2)) + (Array.isArray(vm)
            ? ((formatComponentName(vm[0])) + "... (" + (vm[1]) + " recursive calls)")
            : formatComponentName(vm))); })
        .join('\n')
    } else {
      return ("\n\n(found in " + (formatComponentName(vm)) + ")")
    }
  };
}

/*  */

function handleError (err, vm, info) {
  if (config.errorHandler) {
    config.errorHandler.call(null, err, vm, info);
  } else {
    if (process.env.NODE_ENV !== 'production') {
      warn(("Error in " + info + ": \"" + (err.toString()) + "\""), vm);
    }
    /* istanbul ignore else */
    if (inBrowser && typeof console !== 'undefined') {
      console.error(err);
    } else {
      throw err
    }
  }
}

/*  */
/* globals MutationObserver */

// can we use __proto__?
var hasProto = '__proto__' in {};

// Browser environment sniffing
var inBrowser = typeof window !== 'undefined';
var UA = inBrowser && window.navigator.userAgent.toLowerCase();
var isIE = UA && /msie|trident/.test(UA);
var isIE9 = UA && UA.indexOf('msie 9.0') > 0;
var isEdge = UA && UA.indexOf('edge/') > 0;
var isAndroid = UA && UA.indexOf('android') > 0;
var isIOS = UA && /iphone|ipad|ipod|ios/.test(UA);
var isChrome = UA && /chrome\/\d+/.test(UA) && !isEdge;

// Firefox has a "watch" function on Object.prototype...
var nativeWatch = ({}).watch;

var supportsPassive = false;
if (inBrowser) {
  try {
    var opts = {};
    Object.defineProperty(opts, 'passive', ({
      get: function get () {
        /* istanbul ignore next */
        supportsPassive = true;
      }
    })); // https://github.com/facebook/flow/issues/285
    window.addEventListener('test-passive', null, opts);
  } catch (e) {}
}

// this needs to be lazy-evaled because vue may be required before
// vue-server-renderer can set VUE_ENV
var _isServer;
var isServerRendering = function () {
  if (_isServer === undefined) {
    /* istanbul ignore if */
    if (!inBrowser && typeof global !== 'undefined') {
      // detect presence of vue-server-renderer and avoid
      // Webpack shimming the process
      _isServer = global['process'].env.VUE_ENV === 'server';
    } else {
      _isServer = false;
    }
  }
  return _isServer
};

// detect devtools
var devtools = inBrowser && window.__VUE_DEVTOOLS_GLOBAL_HOOK__;

/* istanbul ignore next */
function isNative (Ctor) {
  return typeof Ctor === 'function' && /native code/.test(Ctor.toString())
}

var hasSymbol =
  typeof Symbol !== 'undefined' && isNative(Symbol) &&
  typeof Reflect !== 'undefined' && isNative(Reflect.ownKeys);

/**
 * Defer a task to execute it asynchronously.
 */
var nextTick = (function () {
  var callbacks = [];
  var pending = false;
  var timerFunc;

  function nextTickHandler () {
    pending = false;
    var copies = callbacks.slice(0);
    callbacks.length = 0;
    for (var i = 0; i < copies.length; i++) {
      copies[i]();
    }
  }

  // the nextTick behavior leverages the microtask queue, which can be accessed
  // via either native Promise.then or MutationObserver.
  // MutationObserver has wider support, however it is seriously bugged in
  // UIWebView in iOS >= 9.3.3 when triggered in touch event handlers. It
  // completely stops working after triggering a few times... so, if native
  // Promise is available, we will use it:
  /* istanbul ignore if */
  if (typeof Promise !== 'undefined' && isNative(Promise)) {
    var p = Promise.resolve();
    var logError = function (err) { console.error(err); };
    timerFunc = function () {
      p.then(nextTickHandler).catch(logError);
      // in problematic UIWebViews, Promise.then doesn't completely break, but
      // it can get stuck in a weird state where callbacks are pushed into the
      // microtask queue but the queue isn't being flushed, until the browser
      // needs to do some other work, e.g. handle a timer. Therefore we can
      // "force" the microtask queue to be flushed by adding an empty timer.
      if (isIOS) { setTimeout(noop); }
    };
  } else if (!isIE && typeof MutationObserver !== 'undefined' && (
    isNative(MutationObserver) ||
    // PhantomJS and iOS 7.x
    MutationObserver.toString() === '[object MutationObserverConstructor]'
  )) {
    // use MutationObserver where native Promise is not available,
    // e.g. PhantomJS, iOS7, Android 4.4
    var counter = 1;
    var observer = new MutationObserver(nextTickHandler);
    var textNode = document.createTextNode(String(counter));
    observer.observe(textNode, {
      characterData: true
    });
    timerFunc = function () {
      counter = (counter + 1) % 2;
      textNode.data = String(counter);
    };
  } else {
    // fallback to setTimeout
    /* istanbul ignore next */
    timerFunc = function () {
      setTimeout(nextTickHandler, 0);
    };
  }

  return function queueNextTick (cb, ctx) {
    var _resolve;
    callbacks.push(function () {
      if (cb) {
        try {
          cb.call(ctx);
        } catch (e) {
          handleError(e, ctx, 'nextTick');
        }
      } else if (_resolve) {
        _resolve(ctx);
      }
    });
    if (!pending) {
      pending = true;
      timerFunc();
    }
    if (!cb && typeof Promise !== 'undefined') {
      return new Promise(function (resolve, reject) {
        _resolve = resolve;
      })
    }
  }
})();

var _Set;
/* istanbul ignore if */
if (typeof Set !== 'undefined' && isNative(Set)) {
  // use native Set when available.
  _Set = Set;
} else {
  // a non-standard Set polyfill that only works with primitive keys.
  _Set = (function () {
    function Set () {
      this.set = Object.create(null);
    }
    Set.prototype.has = function has (key) {
      return this.set[key] === true
    };
    Set.prototype.add = function add (key) {
      this.set[key] = true;
    };
    Set.prototype.clear = function clear () {
      this.set = Object.create(null);
    };

    return Set;
  }());
}

/*  */


var uid = 0;

/**
 * A dep is an observable that can have multiple
 * directives subscribing to it.
 */
var Dep = function Dep () {
  this.id = uid++;
  this.subs = [];
};

Dep.prototype.addSub = function addSub (sub) {
  this.subs.push(sub);
};

Dep.prototype.removeSub = function removeSub (sub) {
  remove(this.subs, sub);
};

Dep.prototype.depend = function depend () {
  if (Dep.target) {
    Dep.target.addDep(this);
  }
};

Dep.prototype.notify = function notify () {
  // stabilize the subscriber list first
  var subs = this.subs.slice();
  for (var i = 0, l = subs.length; i < l; i++) {
    subs[i].update();
  }
};

// the current target watcher being evaluated.
// this is globally unique because there could be only one
// watcher being evaluated at any time.
Dep.target = null;
var targetStack = [];

function pushTarget (_target) {
  if (Dep.target) { targetStack.push(Dep.target); }
  Dep.target = _target;
}

function popTarget () {
  Dep.target = targetStack.pop();
}

/*
 * not type checking this file because flow doesn't play well with
 * dynamically accessing methods on Array prototype
 */

var arrayProto = Array.prototype;
var arrayMethods = Object.create(arrayProto);[
  'push',
  'pop',
  'shift',
  'unshift',
  'splice',
  'sort',
  'reverse'
]
.forEach(function (method) {
  // cache original method
  var original = arrayProto[method];
  def(arrayMethods, method, function mutator () {
    var args = [], len = arguments.length;
    while ( len-- ) args[ len ] = arguments[ len ];

    var result = original.apply(this, args);
    var ob = this.__ob__;
    var inserted;
    switch (method) {
      case 'push':
      case 'unshift':
        inserted = args;
        break
      case 'splice':
        inserted = args.slice(2);
        break
    }
    if (inserted) { ob.observeArray(inserted); }
    // notify change
    ob.dep.notify();
    return result
  });
});

/*  */

var arrayKeys = Object.getOwnPropertyNames(arrayMethods);

/**
 * By default, when a reactive property is set, the new value is
 * also converted to become reactive. However when passing down props,
 * we don't want to force conversion because the value may be a nested value
 * under a frozen data structure. Converting it would defeat the optimization.
 */
var observerState = {
  shouldConvert: true
};

/**
 * Observer class that are attached to each observed
 * object. Once attached, the observer converts target
 * object's property keys into getter/setters that
 * collect dependencies and dispatches updates.
 */
var Observer = function Observer (value) {
  this.value = value;
  this.dep = new Dep();
  this.vmCount = 0;
  def(value, '__ob__', this);
  if (Array.isArray(value)) {
    var augment = hasProto
      ? protoAugment
      : copyAugment;
    augment(value, arrayMethods, arrayKeys);
    this.observeArray(value);
  } else {
    this.walk(value);
  }
};

/**
 * Walk through each property and convert them into
 * getter/setters. This method should only be called when
 * value type is Object.
 */
Observer.prototype.walk = function walk (obj) {
  var keys = Object.keys(obj);
  for (var i = 0; i < keys.length; i++) {
    defineReactive$$1(obj, keys[i], obj[keys[i]]);
  }
};

/**
 * Observe a list of Array items.
 */
Observer.prototype.observeArray = function observeArray (items) {
  for (var i = 0, l = items.length; i < l; i++) {
    observe(items[i]);
  }
};

// helpers

/**
 * Augment an target Object or Array by intercepting
 * the prototype chain using __proto__
 */
function protoAugment (target, src, keys) {
  /* eslint-disable no-proto */
  target.__proto__ = src;
  /* eslint-enable no-proto */
}

/**
 * Augment an target Object or Array by defining
 * hidden properties.
 */
/* istanbul ignore next */
function copyAugment (target, src, keys) {
  for (var i = 0, l = keys.length; i < l; i++) {
    var key = keys[i];
    def(target, key, src[key]);
  }
}

/**
 * Attempt to create an observer instance for a value,
 * returns the new observer if successfully observed,
 * or the existing observer if the value already has one.
 */
function observe (value, asRootData) {
  if (!isObject(value)) {
    return
  }
  var ob;
  if (hasOwn(value, '__ob__') && value.__ob__ instanceof Observer) {
    ob = value.__ob__;
  } else if (
    observerState.shouldConvert &&
    !isServerRendering() &&
    (Array.isArray(value) || isPlainObject(value)) &&
    Object.isExtensible(value) &&
    !value._isVue
  ) {
    ob = new Observer(value);
  }
  if (asRootData && ob) {
    ob.vmCount++;
  }
  return ob
}

/**
 * Define a reactive property on an Object.
 */
function defineReactive$$1 (
  obj,
  key,
  val,
  customSetter,
  shallow
) {
  var dep = new Dep();

  var property = Object.getOwnPropertyDescriptor(obj, key);
  if (property && property.configurable === false) {
    return
  }

  // cater for pre-defined getter/setters
  var getter = property && property.get;
  var setter = property && property.set;

  var childOb = !shallow && observe(val);
  Object.defineProperty(obj, key, {
    enumerable: true,
    configurable: true,
    get: function reactiveGetter () {
      var value = getter ? getter.call(obj) : val;
      if (Dep.target) {
        dep.depend();
        if (childOb) {
          childOb.dep.depend();
          if (Array.isArray(value)) {
            dependArray(value);
          }
        }
      }
      return value
    },
    set: function reactiveSetter (newVal) {
      var value = getter ? getter.call(obj) : val;
      /* eslint-disable no-self-compare */
      if (newVal === value || (newVal !== newVal && value !== value)) {
        return
      }
      /* eslint-enable no-self-compare */
      if (process.env.NODE_ENV !== 'production' && customSetter) {
        customSetter();
      }
      if (setter) {
        setter.call(obj, newVal);
      } else {
        val = newVal;
      }
      childOb = !shallow && observe(newVal);
      dep.notify();
    }
  });
}

/**
 * Set a property on an object. Adds the new property and
 * triggers change notification if the property doesn't
 * already exist.
 */
function set (target, key, val) {
  if (Array.isArray(target) && isValidArrayIndex(key)) {
    target.length = Math.max(target.length, key);
    target.splice(key, 1, val);
    return val
  }
  if (hasOwn(target, key)) {
    target[key] = val;
    return val
  }
  var ob = (target).__ob__;
  if (target._isVue || (ob && ob.vmCount)) {
    process.env.NODE_ENV !== 'production' && warn(
      'Avoid adding reactive properties to a Vue instance or its root $data ' +
      'at runtime - declare it upfront in the data option.'
    );
    return val
  }
  if (!ob) {
    target[key] = val;
    return val
  }
  defineReactive$$1(ob.value, key, val);
  ob.dep.notify();
  return val
}

/**
 * Delete a property and trigger change if necessary.
 */
function del (target, key) {
  if (Array.isArray(target) && isValidArrayIndex(key)) {
    target.splice(key, 1);
    return
  }
  var ob = (target).__ob__;
  if (target._isVue || (ob && ob.vmCount)) {
    process.env.NODE_ENV !== 'production' && warn(
      'Avoid deleting properties on a Vue instance or its root $data ' +
      '- just set it to null.'
    );
    return
  }
  if (!hasOwn(target, key)) {
    return
  }
  delete target[key];
  if (!ob) {
    return
  }
  ob.dep.notify();
}

/**
 * Collect dependencies on array elements when the array is touched, since
 * we cannot intercept array element access like property getters.
 */
function dependArray (value) {
  for (var e = (void 0), i = 0, l = value.length; i < l; i++) {
    e = value[i];
    e && e.__ob__ && e.__ob__.dep.depend();
    if (Array.isArray(e)) {
      dependArray(e);
    }
  }
}

/*  */

/**
 * Option overwriting strategies are functions that handle
 * how to merge a parent option value and a child option
 * value into the final value.
 */
var strats = config.optionMergeStrategies;

/**
 * Options with restrictions
 */
if (process.env.NODE_ENV !== 'production') {
  strats.el = strats.propsData = function (parent, child, vm, key) {
    if (!vm) {
      warn(
        "option \"" + key + "\" can only be used during instance " +
        'creation with the `new` keyword.'
      );
    }
    return defaultStrat(parent, child)
  };
}

/**
 * Helper that recursively merges two data objects together.
 */
function mergeData (to, from) {
  if (!from) { return to }
  var key, toVal, fromVal;
  var keys = Object.keys(from);
  for (var i = 0; i < keys.length; i++) {
    key = keys[i];
    toVal = to[key];
    fromVal = from[key];
    if (!hasOwn(to, key)) {
      set(to, key, fromVal);
    } else if (isPlainObject(toVal) && isPlainObject(fromVal)) {
      mergeData(toVal, fromVal);
    }
  }
  return to
}

/**
 * Data
 */
function mergeDataOrFn (
  parentVal,
  childVal,
  vm
) {
  if (!vm) {
    // in a Vue.extend merge, both should be functions
    if (!childVal) {
      return parentVal
    }
    if (!parentVal) {
      return childVal
    }
    // when parentVal & childVal are both present,
    // we need to return a function that returns the
    // merged result of both functions... no need to
    // check if parentVal is a function here because
    // it has to be a function to pass previous merges.
    return function mergedDataFn () {
      return mergeData(
        typeof childVal === 'function' ? childVal.call(this) : childVal,
        typeof parentVal === 'function' ? parentVal.call(this) : parentVal
      )
    }
  } else if (parentVal || childVal) {
    return function mergedInstanceDataFn () {
      // instance merge
      var instanceData = typeof childVal === 'function'
        ? childVal.call(vm)
        : childVal;
      var defaultData = typeof parentVal === 'function'
        ? parentVal.call(vm)
        : parentVal;
      if (instanceData) {
        return mergeData(instanceData, defaultData)
      } else {
        return defaultData
      }
    }
  }
}

strats.data = function (
  parentVal,
  childVal,
  vm
) {
  if (!vm) {
    if (childVal && typeof childVal !== 'function') {
      process.env.NODE_ENV !== 'production' && warn(
        'The "data" option should be a function ' +
        'that returns a per-instance value in component ' +
        'definitions.',
        vm
      );

      return parentVal
    }
    return mergeDataOrFn.call(this, parentVal, childVal)
  }

  return mergeDataOrFn(parentVal, childVal, vm)
};

/**
 * Hooks and props are merged as arrays.
 */
function mergeHook (
  parentVal,
  childVal
) {
  return childVal
    ? parentVal
      ? parentVal.concat(childVal)
      : Array.isArray(childVal)
        ? childVal
        : [childVal]
    : parentVal
}

LIFECYCLE_HOOKS.forEach(function (hook) {
  strats[hook] = mergeHook;
});

/**
 * Assets
 *
 * When a vm is present (instance creation), we need to do
 * a three-way merge between constructor options, instance
 * options and parent options.
 */
function mergeAssets (parentVal, childVal) {
  var res = Object.create(parentVal || null);
  return childVal
    ? extend(res, childVal)
    : res
}

ASSET_TYPES.forEach(function (type) {
  strats[type + 's'] = mergeAssets;
});

/**
 * Watchers.
 *
 * Watchers hashes should not overwrite one
 * another, so we merge them as arrays.
 */
strats.watch = function (parentVal, childVal) {
  // work around Firefox's Object.prototype.watch...
  if (parentVal === nativeWatch) { parentVal = undefined; }
  if (childVal === nativeWatch) { childVal = undefined; }
  /* istanbul ignore if */
  if (!childVal) { return Object.create(parentVal || null) }
  if (!parentVal) { return childVal }
  var ret = {};
  extend(ret, parentVal);
  for (var key in childVal) {
    var parent = ret[key];
    var child = childVal[key];
    if (parent && !Array.isArray(parent)) {
      parent = [parent];
    }
    ret[key] = parent
      ? parent.concat(child)
      : Array.isArray(child) ? child : [child];
  }
  return ret
};

/**
 * Other object hashes.
 */
strats.props =
strats.methods =
strats.inject =
strats.computed = function (parentVal, childVal) {
  if (!parentVal) { return childVal }
  var ret = Object.create(null);
  extend(ret, parentVal);
  if (childVal) { extend(ret, childVal); }
  return ret
};
strats.provide = mergeDataOrFn;

/**
 * Default strategy.
 */
var defaultStrat = function (parentVal, childVal) {
  return childVal === undefined
    ? parentVal
    : childVal
};

/**
 * Validate component names
 */
function checkComponents (options) {
  for (var key in options.components) {
    var lower = key.toLowerCase();
    if (isBuiltInTag(lower) || config.isReservedTag(lower)) {
      warn(
        'Do not use built-in or reserved HTML elements as component ' +
        'id: ' + key
      );
    }
  }
}

/**
 * Ensure all props option syntax are normalized into the
 * Object-based format.
 */
function normalizeProps (options) {
  var props = options.props;
  if (!props) { return }
  var res = {};
  var i, val, name;
  if (Array.isArray(props)) {
    i = props.length;
    while (i--) {
      val = props[i];
      if (typeof val === 'string') {
        name = camelize(val);
        res[name] = { type: null };
      } else if (process.env.NODE_ENV !== 'production') {
        warn('props must be strings when using array syntax.');
      }
    }
  } else if (isPlainObject(props)) {
    for (var key in props) {
      val = props[key];
      name = camelize(key);
      res[name] = isPlainObject(val)
        ? val
        : { type: val };
    }
  }
  options.props = res;
}

/**
 * Normalize all injections into Object-based format
 */
function normalizeInject (options) {
  var inject = options.inject;
  if (Array.isArray(inject)) {
    var normalized = options.inject = {};
    for (var i = 0; i < inject.length; i++) {
      normalized[inject[i]] = inject[i];
    }
  }
}

/**
 * Normalize raw function directives into object format.
 */
function normalizeDirectives (options) {
  var dirs = options.directives;
  if (dirs) {
    for (var key in dirs) {
      var def = dirs[key];
      if (typeof def === 'function') {
        dirs[key] = { bind: def, update: def };
      }
    }
  }
}

/**
 * Merge two option objects into a new one.
 * Core utility used in both instantiation and inheritance.
 */
function mergeOptions (
  parent,
  child,
  vm
) {
  if (process.env.NODE_ENV !== 'production') {
    checkComponents(child);
  }

  if (typeof child === 'function') {
    child = child.options;
  }

  normalizeProps(child);
  normalizeInject(child);
  normalizeDirectives(child);
  var extendsFrom = child.extends;
  if (extendsFrom) {
    parent = mergeOptions(parent, extendsFrom, vm);
  }
  if (child.mixins) {
    for (var i = 0, l = child.mixins.length; i < l; i++) {
      parent = mergeOptions(parent, child.mixins[i], vm);
    }
  }
  var options = {};
  var key;
  for (key in parent) {
    mergeField(key);
  }
  for (key in child) {
    if (!hasOwn(parent, key)) {
      mergeField(key);
    }
  }
  function mergeField (key) {
    var strat = strats[key] || defaultStrat;
    options[key] = strat(parent[key], child[key], vm, key);
  }
  return options
}

/**
 * Resolve an asset.
 * This function is used because child instances need access
 * to assets defined in its ancestor chain.
 */
function resolveAsset (
  options,
  type,
  id,
  warnMissing
) {
  /* istanbul ignore if */
  if (typeof id !== 'string') {
    return
  }
  var assets = options[type];
  // check local registration variations first
  if (hasOwn(assets, id)) { return assets[id] }
  var camelizedId = camelize(id);
  if (hasOwn(assets, camelizedId)) { return assets[camelizedId] }
  var PascalCaseId = capitalize(camelizedId);
  if (hasOwn(assets, PascalCaseId)) { return assets[PascalCaseId] }
  // fallback to prototype chain
  var res = assets[id] || assets[camelizedId] || assets[PascalCaseId];
  if (process.env.NODE_ENV !== 'production' && warnMissing && !res) {
    warn(
      'Failed to resolve ' + type.slice(0, -1) + ': ' + id,
      options
    );
  }
  return res
}

/*  */

function validateProp (
  key,
  propOptions,
  propsData,
  vm
) {
  var prop = propOptions[key];
  var absent = !hasOwn(propsData, key);
  var value = propsData[key];
  // handle boolean props
  if (isType(Boolean, prop.type)) {
    if (absent && !hasOwn(prop, 'default')) {
      value = false;
    } else if (!isType(String, prop.type) && (value === '' || value === hyphenate(key))) {
      value = true;
    }
  }
  // check default value
  if (value === undefined) {
    value = getPropDefaultValue(vm, prop, key);
    // since the default value is a fresh copy,
    // make sure to observe it.
    var prevShouldConvert = observerState.shouldConvert;
    observerState.shouldConvert = true;
    observe(value);
    observerState.shouldConvert = prevShouldConvert;
  }
  if (process.env.NODE_ENV !== 'production') {
    assertProp(prop, key, value, vm, absent);
  }
  return value
}

/**
 * Get the default value of a prop.
 */
function getPropDefaultValue (vm, prop, key) {
  // no default, return undefined
  if (!hasOwn(prop, 'default')) {
    return undefined
  }
  var def = prop.default;
  // warn against non-factory defaults for Object & Array
  if (process.env.NODE_ENV !== 'production' && isObject(def)) {
    warn(
      'Invalid default value for prop "' + key + '": ' +
      'Props with type Object/Array must use a factory function ' +
      'to return the default value.',
      vm
    );
  }
  // the raw prop value was also undefined from previous render,
  // return previous default value to avoid unnecessary watcher trigger
  if (vm && vm.$options.propsData &&
    vm.$options.propsData[key] === undefined &&
    vm._props[key] !== undefined
  ) {
    return vm._props[key]
  }
  // call factory function for non-Function types
  // a value is Function if its prototype is function even across different execution context
  return typeof def === 'function' && getType(prop.type) !== 'Function'
    ? def.call(vm)
    : def
}

/**
 * Assert whether a prop is valid.
 */
function assertProp (
  prop,
  name,
  value,
  vm,
  absent
) {
  if (prop.required && absent) {
    warn(
      'Missing required prop: "' + name + '"',
      vm
    );
    return
  }
  if (value == null && !prop.required) {
    return
  }
  var type = prop.type;
  var valid = !type || type === true;
  var expectedTypes = [];
  if (type) {
    if (!Array.isArray(type)) {
      type = [type];
    }
    for (var i = 0; i < type.length && !valid; i++) {
      var assertedType = assertType(value, type[i]);
      expectedTypes.push(assertedType.expectedType || '');
      valid = assertedType.valid;
    }
  }
  if (!valid) {
    warn(
      'Invalid prop: type check failed for prop "' + name + '".' +
      ' Expected ' + expectedTypes.map(capitalize).join(', ') +
      ', got ' + Object.prototype.toString.call(value).slice(8, -1) + '.',
      vm
    );
    return
  }
  var validator = prop.validator;
  if (validator) {
    if (!validator(value)) {
      warn(
        'Invalid prop: custom validator check failed for prop "' + name + '".',
        vm
      );
    }
  }
}

var simpleCheckRE = /^(String|Number|Boolean|Function|Symbol)$/;

function assertType (value, type) {
  var valid;
  var expectedType = getType(type);
  if (simpleCheckRE.test(expectedType)) {
    var t = typeof value;
    valid = t === expectedType.toLowerCase();
    // for primitive wrapper objects
    if (!valid && t === 'object') {
      valid = value instanceof type;
    }
  } else if (expectedType === 'Object') {
    valid = isPlainObject(value);
  } else if (expectedType === 'Array') {
    valid = Array.isArray(value);
  } else {
    valid = value instanceof type;
  }
  return {
    valid: valid,
    expectedType: expectedType
  }
}

/**
 * Use function string name to check built-in types,
 * because a simple equality check will fail when running
 * across different vms / iframes.
 */
function getType (fn) {
  var match = fn && fn.toString().match(/^\s*function (\w+)/);
  return match ? match[1] : ''
}

function isType (type, fn) {
  if (!Array.isArray(fn)) {
    return getType(fn) === getType(type)
  }
  for (var i = 0, len = fn.length; i < len; i++) {
    if (getType(fn[i]) === getType(type)) {
      return true
    }
  }
  /* istanbul ignore next */
  return false
}

/*  */

var mark;
var measure;

if (process.env.NODE_ENV !== 'production') {
  var perf = inBrowser && window.performance;
  /* istanbul ignore if */
  if (
    perf &&
    perf.mark &&
    perf.measure &&
    perf.clearMarks &&
    perf.clearMeasures
  ) {
    mark = function (tag) { return perf.mark(tag); };
    measure = function (name, startTag, endTag) {
      perf.measure(name, startTag, endTag);
      perf.clearMarks(startTag);
      perf.clearMarks(endTag);
      perf.clearMeasures(name);
    };
  }
}

/* not type checking this file because flow doesn't play well with Proxy */

var initProxy;

if (process.env.NODE_ENV !== 'production') {
  var allowedGlobals = makeMap(
    'Infinity,undefined,NaN,isFinite,isNaN,' +
    'parseFloat,parseInt,decodeURI,decodeURIComponent,encodeURI,encodeURIComponent,' +
    'Math,Number,Date,Array,Object,Boolean,String,RegExp,Map,Set,JSON,Intl,' +
    'require' // for Webpack/Browserify
  );

  var warnNonPresent = function (target, key) {
    warn(
      "Property or method \"" + key + "\" is not defined on the instance but " +
      "referenced during render. Make sure to declare reactive data " +
      "properties in the data option.",
      target
    );
  };

  var hasProxy =
    typeof Proxy !== 'undefined' &&
    Proxy.toString().match(/native code/);

  if (hasProxy) {
    var isBuiltInModifier = makeMap('stop,prevent,self,ctrl,shift,alt,meta');
    config.keyCodes = new Proxy(config.keyCodes, {
      set: function set (target, key, value) {
        if (isBuiltInModifier(key)) {
          warn(("Avoid overwriting built-in modifier in config.keyCodes: ." + key));
          return false
        } else {
          target[key] = value;
          return true
        }
      }
    });
  }

  var hasHandler = {
    has: function has (target, key) {
      var has = key in target;
      var isAllowed = allowedGlobals(key) || key.charAt(0) === '_';
      if (!has && !isAllowed) {
        warnNonPresent(target, key);
      }
      return has || !isAllowed
    }
  };

  var getHandler = {
    get: function get (target, key) {
      if (typeof key === 'string' && !(key in target)) {
        warnNonPresent(target, key);
      }
      return target[key]
    }
  };

  initProxy = function initProxy (vm) {
    if (hasProxy) {
      // determine which proxy handler to use
      var options = vm.$options;
      var handlers = options.render && options.render._withStripped
        ? getHandler
        : hasHandler;
      vm._renderProxy = new Proxy(vm, handlers);
    } else {
      vm._renderProxy = vm;
    }
  };
}

/*  */

var VNode = function VNode (
  tag,
  data,
  children,
  text,
  elm,
  context,
  componentOptions,
  asyncFactory
) {
  this.tag = tag;
  this.data = data;
  this.children = children;
  this.text = text;
  this.elm = elm;
  this.ns = undefined;
  this.context = context;
  this.functionalContext = undefined;
  this.key = data && data.key;
  this.componentOptions = componentOptions;
  this.componentInstance = undefined;
  this.parent = undefined;
  this.raw = false;
  this.isStatic = false;
  this.isRootInsert = true;
  this.isComment = false;
  this.isCloned = false;
  this.isOnce = false;
  this.asyncFactory = asyncFactory;
  this.asyncMeta = undefined;
  this.isAsyncPlaceholder = false;
};

var prototypeAccessors = { child: {} };

// DEPRECATED: alias for componentInstance for backwards compat.
/* istanbul ignore next */
prototypeAccessors.child.get = function () {
  return this.componentInstance
};

Object.defineProperties( VNode.prototype, prototypeAccessors );

var createEmptyVNode = function (text) {
  if ( text === void 0 ) text = '';

  var node = new VNode();
  node.text = text;
  node.isComment = true;
  return node
};

function createTextVNode (val) {
  return new VNode(undefined, undefined, undefined, String(val))
}

// optimized shallow clone
// used for static nodes and slot nodes because they may be reused across
// multiple renders, cloning them avoids errors when DOM manipulations rely
// on their elm reference.
function cloneVNode (vnode, deep) {
  var cloned = new VNode(
    vnode.tag,
    vnode.data,
    vnode.children,
    vnode.text,
    vnode.elm,
    vnode.context,
    vnode.componentOptions,
    vnode.asyncFactory
  );
  cloned.ns = vnode.ns;
  cloned.isStatic = vnode.isStatic;
  cloned.key = vnode.key;
  cloned.isComment = vnode.isComment;
  cloned.isCloned = true;
  if (deep && vnode.children) {
    cloned.children = cloneVNodes(vnode.children);
  }
  return cloned
}

function cloneVNodes (vnodes, deep) {
  var len = vnodes.length;
  var res = new Array(len);
  for (var i = 0; i < len; i++) {
    res[i] = cloneVNode(vnodes[i], deep);
  }
  return res
}

/*  */

var normalizeEvent = cached(function (name) {
  var passive = name.charAt(0) === '&';
  name = passive ? name.slice(1) : name;
  var once$$1 = name.charAt(0) === '~'; // Prefixed last, checked first
  name = once$$1 ? name.slice(1) : name;
  var capture = name.charAt(0) === '!';
  name = capture ? name.slice(1) : name;
  var plain = !(passive || once$$1 || capture);
  return {
    name: name,
    plain: plain,
    once: once$$1,
    capture: capture,
    passive: passive
  }
});

function createFnInvoker (fns) {
  function invoker () {
    var arguments$1 = arguments;

    var fns = invoker.fns;
    if (Array.isArray(fns)) {
      var cloned = fns.slice();
      for (var i = 0; i < cloned.length; i++) {
        cloned[i].apply(null, arguments$1);
      }
    } else {
      // return handler return value for single handlers
      return fns.apply(null, arguments)
    }
  }
  invoker.fns = fns;
  return invoker
}

// #6552
function prioritizePlainEvents (a, b) {
  return a.plain ? -1 : b.plain ? 1 : 0
}

function updateListeners (
  on,
  oldOn,
  add,
  remove$$1,
  vm
) {
  var name, cur, old, event;
  var toAdd = [];
  var hasModifier = false;
  for (name in on) {
    cur = on[name];
    old = oldOn[name];
    event = normalizeEvent(name);
    if (!event.plain) { hasModifier = true; }
    if (isUndef(cur)) {
      process.env.NODE_ENV !== 'production' && warn(
        "Invalid handler for event \"" + (event.name) + "\": got " + String(cur),
        vm
      );
    } else if (isUndef(old)) {
      if (isUndef(cur.fns)) {
        cur = on[name] = createFnInvoker(cur);
      }
      event.handler = cur;
      toAdd.push(event);
    } else if (cur !== old) {
      old.fns = cur;
      on[name] = old;
    }
  }
  if (toAdd.length) {
    if (hasModifier) { toAdd.sort(prioritizePlainEvents); }
    for (var i = 0; i < toAdd.length; i++) {
      var event$1 = toAdd[i];
      add(event$1.name, event$1.handler, event$1.once, event$1.capture, event$1.passive);
    }
  }
  for (name in oldOn) {
    if (isUndef(on[name])) {
      event = normalizeEvent(name);
      remove$$1(event.name, oldOn[name], event.capture);
    }
  }
}

/*  */

function mergeVNodeHook (def, hookKey, hook) {
  var invoker;
  var oldHook = def[hookKey];

  function wrappedHook () {
    hook.apply(this, arguments);
    // important: remove merged hook to ensure it's called only once
    // and prevent memory leak
    remove(invoker.fns, wrappedHook);
  }

  if (isUndef(oldHook)) {
    // no existing hook
    invoker = createFnInvoker([wrappedHook]);
  } else {
    /* istanbul ignore if */
    if (isDef(oldHook.fns) && isTrue(oldHook.merged)) {
      // already a merged invoker
      invoker = oldHook;
      invoker.fns.push(wrappedHook);
    } else {
      // existing plain hook
      invoker = createFnInvoker([oldHook, wrappedHook]);
    }
  }

  invoker.merged = true;
  def[hookKey] = invoker;
}

/*  */

function extractPropsFromVNodeData (
  data,
  Ctor,
  tag
) {
  // we are only extracting raw values here.
  // validation and default values are handled in the child
  // component itself.
  var propOptions = Ctor.options.props;
  if (isUndef(propOptions)) {
    return
  }
  var res = {};
  var attrs = data.attrs;
  var props = data.props;
  if (isDef(attrs) || isDef(props)) {
    for (var key in propOptions) {
      var altKey = hyphenate(key);
      if (process.env.NODE_ENV !== 'production') {
        var keyInLowerCase = key.toLowerCase();
        if (
          key !== keyInLowerCase &&
          attrs && hasOwn(attrs, keyInLowerCase)
        ) {
          tip(
            "Prop \"" + keyInLowerCase + "\" is passed to component " +
            (formatComponentName(tag || Ctor)) + ", but the declared prop name is" +
            " \"" + key + "\". " +
            "Note that HTML attributes are case-insensitive and camelCased " +
            "props need to use their kebab-case equivalents when using in-DOM " +
            "templates. You should probably use \"" + altKey + "\" instead of \"" + key + "\"."
          );
        }
      }
      checkProp(res, props, key, altKey, true) ||
      checkProp(res, attrs, key, altKey, false);
    }
  }
  return res
}

function checkProp (
  res,
  hash,
  key,
  altKey,
  preserve
) {
  if (isDef(hash)) {
    if (hasOwn(hash, key)) {
      res[key] = hash[key];
      if (!preserve) {
        delete hash[key];
      }
      return true
    } else if (hasOwn(hash, altKey)) {
      res[key] = hash[altKey];
      if (!preserve) {
        delete hash[altKey];
      }
      return true
    }
  }
  return false
}

/*  */

// The template compiler attempts to minimize the need for normalization by
// statically analyzing the template at compile time.
//
// For plain HTML markup, normalization can be completely skipped because the
// generated render function is guaranteed to return Array<VNode>. There are
// two cases where extra normalization is needed:

// 1. When the children contains components - because a functional component
// may return an Array instead of a single root. In this case, just a simple
// normalization is needed - if any child is an Array, we flatten the whole
// thing with Array.prototype.concat. It is guaranteed to be only 1-level deep
// because functional components already normalize their own children.
function simpleNormalizeChildren (children) {
  for (var i = 0; i < children.length; i++) {
    if (Array.isArray(children[i])) {
      return Array.prototype.concat.apply([], children)
    }
  }
  return children
}

// 2. When the children contains constructs that always generated nested Arrays,
// e.g. <template>, <slot>, v-for, or when the children is provided by user
// with hand-written render functions / JSX. In such cases a full normalization
// is needed to cater to all possible types of children values.
function normalizeChildren (children) {
  return isPrimitive(children)
    ? [createTextVNode(children)]
    : Array.isArray(children)
      ? normalizeArrayChildren(children)
      : undefined
}

function isTextNode (node) {
  return isDef(node) && isDef(node.text) && isFalse(node.isComment)
}

function normalizeArrayChildren (children, nestedIndex) {
  var res = [];
  var i, c, last;
  for (i = 0; i < children.length; i++) {
    c = children[i];
    if (isUndef(c) || typeof c === 'boolean') { continue }
    last = res[res.length - 1];
    //  nested
    if (Array.isArray(c)) {
      res.push.apply(res, normalizeArrayChildren(c, ((nestedIndex || '') + "_" + i)));
    } else if (isPrimitive(c)) {
      if (isTextNode(last)) {
        // merge adjacent text nodes
        // this is necessary for SSR hydration because text nodes are
        // essentially merged when rendered to HTML strings
        (last).text += String(c);
      } else if (c !== '') {
        // convert primitive to vnode
        res.push(createTextVNode(c));
      }
    } else {
      if (isTextNode(c) && isTextNode(last)) {
        // merge adjacent text nodes
        res[res.length - 1] = createTextVNode(last.text + c.text);
      } else {
        // default key for nested array children (likely generated by v-for)
        if (isTrue(children._isVList) &&
          isDef(c.tag) &&
          isUndef(c.key) &&
          isDef(nestedIndex)) {
          c.key = "__vlist" + nestedIndex + "_" + i + "__";
        }
        res.push(c);
      }
    }
  }
  return res
}

/*  */

function ensureCtor (comp, base) {
  if (comp.__esModule && comp.default) {
    comp = comp.default;
  }
  return isObject(comp)
    ? base.extend(comp)
    : comp
}

function createAsyncPlaceholder (
  factory,
  data,
  context,
  children,
  tag
) {
  var node = createEmptyVNode();
  node.asyncFactory = factory;
  node.asyncMeta = { data: data, context: context, children: children, tag: tag };
  return node
}

function resolveAsyncComponent (
  factory,
  baseCtor,
  context
) {
  if (isTrue(factory.error) && isDef(factory.errorComp)) {
    return factory.errorComp
  }

  if (isDef(factory.resolved)) {
    return factory.resolved
  }

  if (isTrue(factory.loading) && isDef(factory.loadingComp)) {
    return factory.loadingComp
  }

  if (isDef(factory.contexts)) {
    // already pending
    factory.contexts.push(context);
  } else {
    var contexts = factory.contexts = [context];
    var sync = true;

    var forceRender = function () {
      for (var i = 0, l = contexts.length; i < l; i++) {
        contexts[i].$forceUpdate();
      }
    };

    var resolve = once(function (res) {
      // cache resolved
      factory.resolved = ensureCtor(res, baseCtor);
      // invoke callbacks only if this is not a synchronous resolve
      // (async resolves are shimmed as synchronous during SSR)
      if (!sync) {
        forceRender();
      }
    });

    var reject = once(function (reason) {
      process.env.NODE_ENV !== 'production' && warn(
        "Failed to resolve async component: " + (String(factory)) +
        (reason ? ("\nReason: " + reason) : '')
      );
      if (isDef(factory.errorComp)) {
        factory.error = true;
        forceRender();
      }
    });

    var res = factory(resolve, reject);

    if (isObject(res)) {
      if (typeof res.then === 'function') {
        // () => Promise
        if (isUndef(factory.resolved)) {
          res.then(resolve, reject);
        }
      } else if (isDef(res.component) && typeof res.component.then === 'function') {
        res.component.then(resolve, reject);

        if (isDef(res.error)) {
          factory.errorComp = ensureCtor(res.error, baseCtor);
        }

        if (isDef(res.loading)) {
          factory.loadingComp = ensureCtor(res.loading, baseCtor);
          if (res.delay === 0) {
            factory.loading = true;
          } else {
            setTimeout(function () {
              if (isUndef(factory.resolved) && isUndef(factory.error)) {
                factory.loading = true;
                forceRender();
              }
            }, res.delay || 200);
          }
        }

        if (isDef(res.timeout)) {
          setTimeout(function () {
            if (isUndef(factory.resolved)) {
              reject(
                process.env.NODE_ENV !== 'production'
                  ? ("timeout (" + (res.timeout) + "ms)")
                  : null
              );
            }
          }, res.timeout);
        }
      }
    }

    sync = false;
    // return in case resolved synchronously
    return factory.loading
      ? factory.loadingComp
      : factory.resolved
  }
}

/*  */

function isAsyncPlaceholder (node) {
  return node.isComment && node.asyncFactory
}

/*  */

function getFirstComponentChild (children) {
  if (Array.isArray(children)) {
    for (var i = 0; i < children.length; i++) {
      var c = children[i];
      if (isDef(c) && (isDef(c.componentOptions) || isAsyncPlaceholder(c))) {
        return c
      }
    }
  }
}

/*  */

/*  */

function initEvents (vm) {
  vm._events = Object.create(null);
  vm._hasHookEvent = false;
  // init parent attached events
  var listeners = vm.$options._parentListeners;
  if (listeners) {
    updateComponentListeners(vm, listeners);
  }
}

var target;

function add (event, fn, once$$1) {
  if (once$$1) {
    target.$once(event, fn);
  } else {
    target.$on(event, fn);
  }
}

function remove$1 (event, fn) {
  target.$off(event, fn);
}

function updateComponentListeners (
  vm,
  listeners,
  oldListeners
) {
  target = vm;
  updateListeners(listeners, oldListeners || {}, add, remove$1, vm);
}

function eventsMixin (Vue) {
  var hookRE = /^hook:/;
  Vue.prototype.$on = function (event, fn) {
    var this$1 = this;

    var vm = this;
    if (Array.isArray(event)) {
      for (var i = 0, l = event.length; i < l; i++) {
        this$1.$on(event[i], fn);
      }
    } else {
      (vm._events[event] || (vm._events[event] = [])).push(fn);
      // optimize hook:event cost by using a boolean flag marked at registration
      // instead of a hash lookup
      if (hookRE.test(event)) {
        vm._hasHookEvent = true;
      }
    }
    return vm
  };

  Vue.prototype.$once = function (event, fn) {
    var vm = this;
    function on () {
      vm.$off(event, on);
      fn.apply(vm, arguments);
    }
    on.fn = fn;
    vm.$on(event, on);
    return vm
  };

  Vue.prototype.$off = function (event, fn) {
    var this$1 = this;

    var vm = this;
    // all
    if (!arguments.length) {
      vm._events = Object.create(null);
      return vm
    }
    // array of events
    if (Array.isArray(event)) {
      for (var i = 0, l = event.length; i < l; i++) {
        this$1.$off(event[i], fn);
      }
      return vm
    }
    // specific event
    var cbs = vm._events[event];
    if (!cbs) {
      return vm
    }
    if (arguments.length === 1) {
      vm._events[event] = null;
      return vm
    }
    if (fn) {
      // specific handler
      var cb;
      var i$1 = cbs.length;
      while (i$1--) {
        cb = cbs[i$1];
        if (cb === fn || cb.fn === fn) {
          cbs.splice(i$1, 1);
          break
        }
      }
    }
    return vm
  };

  Vue.prototype.$emit = function (event) {
    var vm = this;
    if (process.env.NODE_ENV !== 'production') {
      var lowerCaseEvent = event.toLowerCase();
      if (lowerCaseEvent !== event && vm._events[lowerCaseEvent]) {
        tip(
          "Event \"" + lowerCaseEvent + "\" is emitted in component " +
          (formatComponentName(vm)) + " but the handler is registered for \"" + event + "\". " +
          "Note that HTML attributes are case-insensitive and you cannot use " +
          "v-on to listen to camelCase events when using in-DOM templates. " +
          "You should probably use \"" + (hyphenate(event)) + "\" instead of \"" + event + "\"."
        );
      }
    }
    var cbs = vm._events[event];
    if (cbs) {
      cbs = cbs.length > 1 ? toArray(cbs) : cbs;
      var args = toArray(arguments, 1);
      for (var i = 0, l = cbs.length; i < l; i++) {
        try {
          cbs[i].apply(vm, args);
        } catch (e) {
          handleError(e, vm, ("event handler for \"" + event + "\""));
        }
      }
    }
    return vm
  };
}

/*  */

/**
 * Runtime helper for resolving raw children VNodes into a slot object.
 */
function resolveSlots (
  children,
  context
) {
  var slots = {};
  if (!children) {
    return slots
  }
  var defaultSlot = [];
  for (var i = 0, l = children.length; i < l; i++) {
    var child = children[i];
    var data = child.data;
    // remove slot attribute if the node is resolved as a Vue slot node
    if (data && data.attrs && data.attrs.slot) {
      delete data.attrs.slot;
    }
    // named slots should only be respected if the vnode was rendered in the
    // same context.
    if ((child.context === context || child.functionalContext === context) &&
      data && data.slot != null
    ) {
      var name = child.data.slot;
      var slot = (slots[name] || (slots[name] = []));
      if (child.tag === 'template') {
        slot.push.apply(slot, child.children);
      } else {
        slot.push(child);
      }
    } else {
      defaultSlot.push(child);
    }
  }
  // ignore whitespace
  if (!defaultSlot.every(isWhitespace)) {
    slots.default = defaultSlot;
  }
  return slots
}

function isWhitespace (node) {
  return node.isComment || node.text === ' '
}

function resolveScopedSlots (
  fns, // see flow/vnode
  res
) {
  res = res || {};
  for (var i = 0; i < fns.length; i++) {
    if (Array.isArray(fns[i])) {
      resolveScopedSlots(fns[i], res);
    } else {
      res[fns[i].key] = fns[i].fn;
    }
  }
  return res
}

/*  */

var activeInstance = null;
var isUpdatingChildComponent = false;

function initLifecycle (vm) {
  var options = vm.$options;

  // locate first non-abstract parent
  var parent = options.parent;
  if (parent && !options.abstract) {
    while (parent.$options.abstract && parent.$parent) {
      parent = parent.$parent;
    }
    parent.$children.push(vm);
  }

  vm.$parent = parent;
  vm.$root = parent ? parent.$root : vm;

  vm.$children = [];
  vm.$refs = {};

  vm._watcher = null;
  vm._inactive = null;
  vm._directInactive = false;
  vm._isMounted = false;
  vm._isDestroyed = false;
  vm._isBeingDestroyed = false;
}

function lifecycleMixin (Vue) {
  Vue.prototype._update = function (vnode, hydrating) {
    var vm = this;
    if (vm._isMounted) {
      callHook(vm, 'beforeUpdate');
    }
    var prevEl = vm.$el;
    var prevVnode = vm._vnode;
    var prevActiveInstance = activeInstance;
    activeInstance = vm;
    vm._vnode = vnode;
    // Vue.prototype.__patch__ is injected in entry points
    // based on the rendering backend used.
    if (!prevVnode) {
      // initial render
      vm.$el = vm.__patch__(
        vm.$el, vnode, hydrating, false /* removeOnly */,
        vm.$options._parentElm,
        vm.$options._refElm
      );
      // no need for the ref nodes after initial patch
      // this prevents keeping a detached DOM tree in memory (#5851)
      vm.$options._parentElm = vm.$options._refElm = null;
    } else {
      // updates
      vm.$el = vm.__patch__(prevVnode, vnode);
    }
    activeInstance = prevActiveInstance;
    // update __vue__ reference
    if (prevEl) {
      prevEl.__vue__ = null;
    }
    if (vm.$el) {
      vm.$el.__vue__ = vm;
    }
    // if parent is an HOC, update its $el as well
    if (vm.$vnode && vm.$parent && vm.$vnode === vm.$parent._vnode) {
      vm.$parent.$el = vm.$el;
    }
    // updated hook is called by the scheduler to ensure that children are
    // updated in a parent's updated hook.
  };

  Vue.prototype.$forceUpdate = function () {
    var vm = this;
    if (vm._watcher) {
      vm._watcher.update();
    }
  };

  Vue.prototype.$destroy = function () {
    var vm = this;
    if (vm._isBeingDestroyed) {
      return
    }
    callHook(vm, 'beforeDestroy');
    vm._isBeingDestroyed = true;
    // remove self from parent
    var parent = vm.$parent;
    if (parent && !parent._isBeingDestroyed && !vm.$options.abstract) {
      remove(parent.$children, vm);
    }
    // teardown watchers
    if (vm._watcher) {
      vm._watcher.teardown();
    }
    var i = vm._watchers.length;
    while (i--) {
      vm._watchers[i].teardown();
    }
    // remove reference from data ob
    // frozen object may not have observer.
    if (vm._data.__ob__) {
      vm._data.__ob__.vmCount--;
    }
    // call the last hook...
    vm._isDestroyed = true;
    // invoke destroy hooks on current rendered tree
    vm.__patch__(vm._vnode, null);
    // fire destroyed hook
    callHook(vm, 'destroyed');
    // turn off all instance listeners.
    vm.$off();
    // remove __vue__ reference
    if (vm.$el) {
      vm.$el.__vue__ = null;
    }
  };
}

function mountComponent (
  vm,
  el,
  hydrating
) {
  vm.$el = el;
  if (!vm.$options.render) {
    vm.$options.render = createEmptyVNode;
    if (process.env.NODE_ENV !== 'production') {
      /* istanbul ignore if */
      if ((vm.$options.template && vm.$options.template.charAt(0) !== '#') ||
        vm.$options.el || el) {
        warn(
          'You are using the runtime-only build of Vue where the template ' +
          'compiler is not available. Either pre-compile the templates into ' +
          'render functions, or use the compiler-included build.',
          vm
        );
      } else {
        warn(
          'Failed to mount component: template or render function not defined.',
          vm
        );
      }
    }
  }
  callHook(vm, 'beforeMount');

  var updateComponent;
  /* istanbul ignore if */
  if (process.env.NODE_ENV !== 'production' && config.performance && mark) {
    updateComponent = function () {
      var name = vm._name;
      var id = vm._uid;
      var startTag = "vue-perf-start:" + id;
      var endTag = "vue-perf-end:" + id;

      mark(startTag);
      var vnode = vm._render();
      mark(endTag);
      measure((name + " render"), startTag, endTag);

      mark(startTag);
      vm._update(vnode, hydrating);
      mark(endTag);
      measure((name + " patch"), startTag, endTag);
    };
  } else {
    updateComponent = function () {
      vm._update(vm._render(), hydrating);
    };
  }

  vm._watcher = new Watcher(vm, updateComponent, noop);
  hydrating = false;

  // manually mounted instance, call mounted on self
  // mounted is called for render-created child components in its inserted hook
  if (vm.$vnode == null) {
    vm._isMounted = true;
    callHook(vm, 'mounted');
  }
  return vm
}

function updateChildComponent (
  vm,
  propsData,
  listeners,
  parentVnode,
  renderChildren
) {
  if (process.env.NODE_ENV !== 'production') {
    isUpdatingChildComponent = true;
  }

  // determine whether component has slot children
  // we need to do this before overwriting $options._renderChildren
  var hasChildren = !!(
    renderChildren ||               // has new static slots
    vm.$options._renderChildren ||  // has old static slots
    parentVnode.data.scopedSlots || // has new scoped slots
    vm.$scopedSlots !== emptyObject // has old scoped slots
  );

  vm.$options._parentVnode = parentVnode;
  vm.$vnode = parentVnode; // update vm's placeholder node without re-render

  if (vm._vnode) { // update child tree's parent
    vm._vnode.parent = parentVnode;
  }
  vm.$options._renderChildren = renderChildren;

  // update $attrs and $listeners hash
  // these are also reactive so they may trigger child update if the child
  // used them during render
  vm.$attrs = (parentVnode.data && parentVnode.data.attrs) || emptyObject;
  vm.$listeners = listeners || emptyObject;

  // update props
  if (propsData && vm.$options.props) {
    observerState.shouldConvert = false;
    var props = vm._props;
    var propKeys = vm.$options._propKeys || [];
    for (var i = 0; i < propKeys.length; i++) {
      var key = propKeys[i];
      props[key] = validateProp(key, vm.$options.props, propsData, vm);
    }
    observerState.shouldConvert = true;
    // keep a copy of raw propsData
    vm.$options.propsData = propsData;
  }

  // update listeners
  if (listeners) {
    var oldListeners = vm.$options._parentListeners;
    vm.$options._parentListeners = listeners;
    updateComponentListeners(vm, listeners, oldListeners);
  }
  // resolve slots + force update if has children
  if (hasChildren) {
    vm.$slots = resolveSlots(renderChildren, parentVnode.context);
    vm.$forceUpdate();
  }

  if (process.env.NODE_ENV !== 'production') {
    isUpdatingChildComponent = false;
  }
}

function isInInactiveTree (vm) {
  while (vm && (vm = vm.$parent)) {
    if (vm._inactive) { return true }
  }
  return false
}

function activateChildComponent (vm, direct) {
  if (direct) {
    vm._directInactive = false;
    if (isInInactiveTree(vm)) {
      return
    }
  } else if (vm._directInactive) {
    return
  }
  if (vm._inactive || vm._inactive === null) {
    vm._inactive = false;
    for (var i = 0; i < vm.$children.length; i++) {
      activateChildComponent(vm.$children[i]);
    }
    callHook(vm, 'activated');
  }
}

function deactivateChildComponent (vm, direct) {
  if (direct) {
    vm._directInactive = true;
    if (isInInactiveTree(vm)) {
      return
    }
  }
  if (!vm._inactive) {
    vm._inactive = true;
    for (var i = 0; i < vm.$children.length; i++) {
      deactivateChildComponent(vm.$children[i]);
    }
    callHook(vm, 'deactivated');
  }
}

function callHook (vm, hook) {
  var handlers = vm.$options[hook];
  if (handlers) {
    for (var i = 0, j = handlers.length; i < j; i++) {
      try {
        handlers[i].call(vm);
      } catch (e) {
        handleError(e, vm, (hook + " hook"));
      }
    }
  }
  if (vm._hasHookEvent) {
    vm.$emit('hook:' + hook);
  }
}

/*  */


var MAX_UPDATE_COUNT = 100;

var queue = [];
var activatedChildren = [];
var has = {};
var circular = {};
var waiting = false;
var flushing = false;
var index = 0;

/**
 * Reset the scheduler's state.
 */
function resetSchedulerState () {
  index = queue.length = activatedChildren.length = 0;
  has = {};
  if (process.env.NODE_ENV !== 'production') {
    circular = {};
  }
  waiting = flushing = false;
}

/**
 * Flush both queues and run the watchers.
 */
function flushSchedulerQueue () {
  flushing = true;
  var watcher, id;

  // Sort queue before flush.
  // This ensures that:
  // 1. Components are updated from parent to child. (because parent is always
  //    created before the child)
  // 2. A component's user watchers are run before its render watcher (because
  //    user watchers are created before the render watcher)
  // 3. If a component is destroyed during a parent component's watcher run,
  //    its watchers can be skipped.
  queue.sort(function (a, b) { return a.id - b.id; });

  // do not cache length because more watchers might be pushed
  // as we run existing watchers
  for (index = 0; index < queue.length; index++) {
    watcher = queue[index];
    id = watcher.id;
    has[id] = null;
    watcher.run();
    // in dev build, check and stop circular updates.
    if (process.env.NODE_ENV !== 'production' && has[id] != null) {
      circular[id] = (circular[id] || 0) + 1;
      if (circular[id] > MAX_UPDATE_COUNT) {
        warn(
          'You may have an infinite update loop ' + (
            watcher.user
              ? ("in watcher with expression \"" + (watcher.expression) + "\"")
              : "in a component render function."
          ),
          watcher.vm
        );
        break
      }
    }
  }

  // keep copies of post queues before resetting state
  var activatedQueue = activatedChildren.slice();
  var updatedQueue = queue.slice();

  resetSchedulerState();

  // call component updated and activated hooks
  callActivatedHooks(activatedQueue);
  callUpdatedHooks(updatedQueue);

  // devtool hook
  /* istanbul ignore if */
  if (devtools && config.devtools) {
    devtools.emit('flush');
  }
}

function callUpdatedHooks (queue) {
  var i = queue.length;
  while (i--) {
    var watcher = queue[i];
    var vm = watcher.vm;
    if (vm._watcher === watcher && vm._isMounted) {
      callHook(vm, 'updated');
    }
  }
}

/**
 * Queue a kept-alive component that was activated during patch.
 * The queue will be processed after the entire tree has been patched.
 */
function queueActivatedComponent (vm) {
  // setting _inactive to false here so that a render function can
  // rely on checking whether it's in an inactive tree (e.g. router-view)
  vm._inactive = false;
  activatedChildren.push(vm);
}

function callActivatedHooks (queue) {
  for (var i = 0; i < queue.length; i++) {
    queue[i]._inactive = true;
    activateChildComponent(queue[i], true /* true */);
  }
}

/**
 * Push a watcher into the watcher queue.
 * Jobs with duplicate IDs will be skipped unless it's
 * pushed when the queue is being flushed.
 */
function queueWatcher (watcher) {
  var id = watcher.id;
  if (has[id] == null) {
    has[id] = true;
    if (!flushing) {
      queue.push(watcher);
    } else {
      // if already flushing, splice the watcher based on its id
      // if already past its id, it will be run next immediately.
      var i = queue.length - 1;
      while (i > index && queue[i].id > watcher.id) {
        i--;
      }
      queue.splice(i + 1, 0, watcher);
    }
    // queue the flush
    if (!waiting) {
      waiting = true;
      nextTick(flushSchedulerQueue);
    }
  }
}

/*  */

var uid$2 = 0;

/**
 * A watcher parses an expression, collects dependencies,
 * and fires callback when the expression value changes.
 * This is used for both the $watch() api and directives.
 */
var Watcher = function Watcher (
  vm,
  expOrFn,
  cb,
  options
) {
  this.vm = vm;
  vm._watchers.push(this);
  // options
  if (options) {
    this.deep = !!options.deep;
    this.user = !!options.user;
    this.lazy = !!options.lazy;
    this.sync = !!options.sync;
  } else {
    this.deep = this.user = this.lazy = this.sync = false;
  }
  this.cb = cb;
  this.id = ++uid$2; // uid for batching
  this.active = true;
  this.dirty = this.lazy; // for lazy watchers
  this.deps = [];
  this.newDeps = [];
  this.depIds = new _Set();
  this.newDepIds = new _Set();
  this.expression = process.env.NODE_ENV !== 'production'
    ? expOrFn.toString()
    : '';
  // parse expression for getter
  if (typeof expOrFn === 'function') {
    this.getter = expOrFn;
  } else {
    this.getter = parsePath(expOrFn);
    if (!this.getter) {
      this.getter = function () {};
      process.env.NODE_ENV !== 'production' && warn(
        "Failed watching path: \"" + expOrFn + "\" " +
        'Watcher only accepts simple dot-delimited paths. ' +
        'For full control, use a function instead.',
        vm
      );
    }
  }
  this.value = this.lazy
    ? undefined
    : this.get();
};

/**
 * Evaluate the getter, and re-collect dependencies.
 */
Watcher.prototype.get = function get () {
  pushTarget(this);
  var value;
  var vm = this.vm;
  try {
    value = this.getter.call(vm, vm);
  } catch (e) {
    if (this.user) {
      handleError(e, vm, ("getter for watcher \"" + (this.expression) + "\""));
    } else {
      throw e
    }
  } finally {
    // "touch" every property so they are all tracked as
    // dependencies for deep watching
    if (this.deep) {
      traverse(value);
    }
    popTarget();
    this.cleanupDeps();
  }
  return value
};

/**
 * Add a dependency to this directive.
 */
Watcher.prototype.addDep = function addDep (dep) {
  var id = dep.id;
  if (!this.newDepIds.has(id)) {
    this.newDepIds.add(id);
    this.newDeps.push(dep);
    if (!this.depIds.has(id)) {
      dep.addSub(this);
    }
  }
};

/**
 * Clean up for dependency collection.
 */
Watcher.prototype.cleanupDeps = function cleanupDeps () {
    var this$1 = this;

  var i = this.deps.length;
  while (i--) {
    var dep = this$1.deps[i];
    if (!this$1.newDepIds.has(dep.id)) {
      dep.removeSub(this$1);
    }
  }
  var tmp = this.depIds;
  this.depIds = this.newDepIds;
  this.newDepIds = tmp;
  this.newDepIds.clear();
  tmp = this.deps;
  this.deps = this.newDeps;
  this.newDeps = tmp;
  this.newDeps.length = 0;
};

/**
 * Subscriber interface.
 * Will be called when a dependency changes.
 */
Watcher.prototype.update = function update () {
  /* istanbul ignore else */
  if (this.lazy) {
    this.dirty = true;
  } else if (this.sync) {
    this.run();
  } else {
    queueWatcher(this);
  }
};

/**
 * Scheduler job interface.
 * Will be called by the scheduler.
 */
Watcher.prototype.run = function run () {
  if (this.active) {
    var value = this.get();
    if (
      value !== this.value ||
      // Deep watchers and watchers on Object/Arrays should fire even
      // when the value is the same, because the value may
      // have mutated.
      isObject(value) ||
      this.deep
    ) {
      // set new value
      var oldValue = this.value;
      this.value = value;
      if (this.user) {
        try {
          this.cb.call(this.vm, value, oldValue);
        } catch (e) {
          handleError(e, this.vm, ("callback for watcher \"" + (this.expression) + "\""));
        }
      } else {
        this.cb.call(this.vm, value, oldValue);
      }
    }
  }
};

/**
 * Evaluate the value of the watcher.
 * This only gets called for lazy watchers.
 */
Watcher.prototype.evaluate = function evaluate () {
  this.value = this.get();
  this.dirty = false;
};

/**
 * Depend on all deps collected by this watcher.
 */
Watcher.prototype.depend = function depend () {
    var this$1 = this;

  var i = this.deps.length;
  while (i--) {
    this$1.deps[i].depend();
  }
};

/**
 * Remove self from all dependencies' subscriber list.
 */
Watcher.prototype.teardown = function teardown () {
    var this$1 = this;

  if (this.active) {
    // remove self from vm's watcher list
    // this is a somewhat expensive operation so we skip it
    // if the vm is being destroyed.
    if (!this.vm._isBeingDestroyed) {
      remove(this.vm._watchers, this);
    }
    var i = this.deps.length;
    while (i--) {
      this$1.deps[i].removeSub(this$1);
    }
    this.active = false;
  }
};

/**
 * Recursively traverse an object to evoke all converted
 * getters, so that every nested property inside the object
 * is collected as a "deep" dependency.
 */
var seenObjects = new _Set();
function traverse (val) {
  seenObjects.clear();
  _traverse(val, seenObjects);
}

function _traverse (val, seen) {
  var i, keys;
  var isA = Array.isArray(val);
  if ((!isA && !isObject(val)) || !Object.isExtensible(val)) {
    return
  }
  if (val.__ob__) {
    var depId = val.__ob__.dep.id;
    if (seen.has(depId)) {
      return
    }
    seen.add(depId);
  }
  if (isA) {
    i = val.length;
    while (i--) { _traverse(val[i], seen); }
  } else {
    keys = Object.keys(val);
    i = keys.length;
    while (i--) { _traverse(val[keys[i]], seen); }
  }
}

/*  */

var sharedPropertyDefinition = {
  enumerable: true,
  configurable: true,
  get: noop,
  set: noop
};

function proxy (target, sourceKey, key) {
  sharedPropertyDefinition.get = function proxyGetter () {
    return this[sourceKey][key]
  };
  sharedPropertyDefinition.set = function proxySetter (val) {
    this[sourceKey][key] = val;
  };
  Object.defineProperty(target, key, sharedPropertyDefinition);
}

function initState (vm) {
  vm._watchers = [];
  var opts = vm.$options;
  if (opts.props) { initProps(vm, opts.props); }
  if (opts.methods) { initMethods(vm, opts.methods); }
  if (opts.data) {
    initData(vm);
  } else {
    observe(vm._data = {}, true /* asRootData */);
  }
  if (opts.computed) { initComputed(vm, opts.computed); }
  if (opts.watch && opts.watch !== nativeWatch) {
    initWatch(vm, opts.watch);
  }
}

function checkOptionType (vm, name) {
  var option = vm.$options[name];
  if (!isPlainObject(option)) {
    warn(
      ("component option \"" + name + "\" should be an object."),
      vm
    );
  }
}

function initProps (vm, propsOptions) {
  var propsData = vm.$options.propsData || {};
  var props = vm._props = {};
  // cache prop keys so that future props updates can iterate using Array
  // instead of dynamic object key enumeration.
  var keys = vm.$options._propKeys = [];
  var isRoot = !vm.$parent;
  // root instance props should be converted
  observerState.shouldConvert = isRoot;
  var loop = function ( key ) {
    keys.push(key);
    var value = validateProp(key, propsOptions, propsData, vm);
    /* istanbul ignore else */
    if (process.env.NODE_ENV !== 'production') {
      if (isReservedAttribute(key) || config.isReservedAttr(key)) {
        warn(
          ("\"" + key + "\" is a reserved attribute and cannot be used as component prop."),
          vm
        );
      }
      defineReactive$$1(props, key, value, function () {
        if (vm.$parent && !isUpdatingChildComponent) {
          warn(
            "Avoid mutating a prop directly since the value will be " +
            "overwritten whenever the parent component re-renders. " +
            "Instead, use a data or computed property based on the prop's " +
            "value. Prop being mutated: \"" + key + "\"",
            vm
          );
        }
      });
    } else {
      defineReactive$$1(props, key, value);
    }
    // static props are already proxied on the component's prototype
    // during Vue.extend(). We only need to proxy props defined at
    // instantiation here.
    if (!(key in vm)) {
      proxy(vm, "_props", key);
    }
  };

  for (var key in propsOptions) loop( key );
  observerState.shouldConvert = true;
}

function initData (vm) {
  var data = vm.$options.data;
  data = vm._data = typeof data === 'function'
    ? getData(data, vm)
    : data || {};
  if (!isPlainObject(data)) {
    data = {};
    process.env.NODE_ENV !== 'production' && warn(
      'data functions should return an object:\n' +
      'https://vuejs.org/v2/guide/components.html#data-Must-Be-a-Function',
      vm
    );
  }
  // proxy data on instance
  var keys = Object.keys(data);
  var props = vm.$options.props;
  var methods = vm.$options.methods;
  var i = keys.length;
  while (i--) {
    var key = keys[i];
    if (process.env.NODE_ENV !== 'production') {
      if (methods && hasOwn(methods, key)) {
        warn(
          ("Method \"" + key + "\" has already been defined as a data property."),
          vm
        );
      }
    }
    if (props && hasOwn(props, key)) {
      process.env.NODE_ENV !== 'production' && warn(
        "The data property \"" + key + "\" is already declared as a prop. " +
        "Use prop default value instead.",
        vm
      );
    } else if (!isReserved(key)) {
      proxy(vm, "_data", key);
    }
  }
  // observe data
  observe(data, true /* asRootData */);
}

function getData (data, vm) {
  try {
    return data.call(vm)
  } catch (e) {
    handleError(e, vm, "data()");
    return {}
  }
}

var computedWatcherOptions = { lazy: true };

function initComputed (vm, computed) {
  process.env.NODE_ENV !== 'production' && checkOptionType(vm, 'computed');
  var watchers = vm._computedWatchers = Object.create(null);
  // computed properties are just getters during SSR
  var isSSR = isServerRendering();

  for (var key in computed) {
    var userDef = computed[key];
    var getter = typeof userDef === 'function' ? userDef : userDef.get;
    if (process.env.NODE_ENV !== 'production' && getter == null) {
      warn(
        ("Getter is missing for computed property \"" + key + "\"."),
        vm
      );
    }

    if (!isSSR) {
      // create internal watcher for the computed property.
      watchers[key] = new Watcher(
        vm,
        getter || noop,
        noop,
        computedWatcherOptions
      );
    }

    // component-defined computed properties are already defined on the
    // component prototype. We only need to define computed properties defined
    // at instantiation here.
    if (!(key in vm)) {
      defineComputed(vm, key, userDef);
    } else if (process.env.NODE_ENV !== 'production') {
      if (key in vm.$data) {
        warn(("The computed property \"" + key + "\" is already defined in data."), vm);
      } else if (vm.$options.props && key in vm.$options.props) {
        warn(("The computed property \"" + key + "\" is already defined as a prop."), vm);
      }
    }
  }
}

function defineComputed (
  target,
  key,
  userDef
) {
  var shouldCache = !isServerRendering();
  if (typeof userDef === 'function') {
    sharedPropertyDefinition.get = shouldCache
      ? createComputedGetter(key)
      : userDef;
    sharedPropertyDefinition.set = noop;
  } else {
    sharedPropertyDefinition.get = userDef.get
      ? shouldCache && userDef.cache !== false
        ? createComputedGetter(key)
        : userDef.get
      : noop;
    sharedPropertyDefinition.set = userDef.set
      ? userDef.set
      : noop;
  }
  if (process.env.NODE_ENV !== 'production' &&
      sharedPropertyDefinition.set === noop) {
    sharedPropertyDefinition.set = function () {
      warn(
        ("Computed property \"" + key + "\" was assigned to but it has no setter."),
        this
      );
    };
  }
  Object.defineProperty(target, key, sharedPropertyDefinition);
}

function createComputedGetter (key) {
  return function computedGetter () {
    var watcher = this._computedWatchers && this._computedWatchers[key];
    if (watcher) {
      if (watcher.dirty) {
        watcher.evaluate();
      }
      if (Dep.target) {
        watcher.depend();
      }
      return watcher.value
    }
  }
}

function initMethods (vm, methods) {
  process.env.NODE_ENV !== 'production' && checkOptionType(vm, 'methods');
  var props = vm.$options.props;
  for (var key in methods) {
    if (process.env.NODE_ENV !== 'production') {
      if (methods[key] == null) {
        warn(
          "Method \"" + key + "\" has an undefined value in the component definition. " +
          "Did you reference the function correctly?",
          vm
        );
      }
      if (props && hasOwn(props, key)) {
        warn(
          ("Method \"" + key + "\" has already been defined as a prop."),
          vm
        );
      }
      if ((key in vm) && isReserved(key)) {
        warn(
          "Method \"" + key + "\" conflicts with an existing Vue instance method. " +
          "Avoid defining component methods that start with _ or $."
        );
      }
    }
    vm[key] = methods[key] == null ? noop : bind(methods[key], vm);
  }
}

function initWatch (vm, watch) {
  process.env.NODE_ENV !== 'production' && checkOptionType(vm, 'watch');
  for (var key in watch) {
    var handler = watch[key];
    if (Array.isArray(handler)) {
      for (var i = 0; i < handler.length; i++) {
        createWatcher(vm, key, handler[i]);
      }
    } else {
      createWatcher(vm, key, handler);
    }
  }
}

function createWatcher (
  vm,
  keyOrFn,
  handler,
  options
) {
  if (isPlainObject(handler)) {
    options = handler;
    handler = handler.handler;
  }
  if (typeof handler === 'string') {
    handler = vm[handler];
  }
  return vm.$watch(keyOrFn, handler, options)
}

function stateMixin (Vue) {
  // flow somehow has problems with directly declared definition object
  // when using Object.defineProperty, so we have to procedurally build up
  // the object here.
  var dataDef = {};
  dataDef.get = function () { return this._data };
  var propsDef = {};
  propsDef.get = function () { return this._props };
  if (process.env.NODE_ENV !== 'production') {
    dataDef.set = function (newData) {
      warn(
        'Avoid replacing instance root $data. ' +
        'Use nested data properties instead.',
        this
      );
    };
    propsDef.set = function () {
      warn("$props is readonly.", this);
    };
  }
  Object.defineProperty(Vue.prototype, '$data', dataDef);
  Object.defineProperty(Vue.prototype, '$props', propsDef);

  Vue.prototype.$set = set;
  Vue.prototype.$delete = del;

  Vue.prototype.$watch = function (
    expOrFn,
    cb,
    options
  ) {
    var vm = this;
    if (isPlainObject(cb)) {
      return createWatcher(vm, expOrFn, cb, options)
    }
    options = options || {};
    options.user = true;
    var watcher = new Watcher(vm, expOrFn, cb, options);
    if (options.immediate) {
      cb.call(vm, watcher.value);
    }
    return function unwatchFn () {
      watcher.teardown();
    }
  };
}

/*  */

function initProvide (vm) {
  var provide = vm.$options.provide;
  if (provide) {
    vm._provided = typeof provide === 'function'
      ? provide.call(vm)
      : provide;
  }
}

function initInjections (vm) {
  var result = resolveInject(vm.$options.inject, vm);
  if (result) {
    observerState.shouldConvert = false;
    Object.keys(result).forEach(function (key) {
      /* istanbul ignore else */
      if (process.env.NODE_ENV !== 'production') {
        defineReactive$$1(vm, key, result[key], function () {
          warn(
            "Avoid mutating an injected value directly since the changes will be " +
            "overwritten whenever the provided component re-renders. " +
            "injection being mutated: \"" + key + "\"",
            vm
          );
        });
      } else {
        defineReactive$$1(vm, key, result[key]);
      }
    });
    observerState.shouldConvert = true;
  }
}

function resolveInject (inject, vm) {
  if (inject) {
    // inject is :any because flow is not smart enough to figure out cached
    var result = Object.create(null);
    var keys = hasSymbol
        ? Reflect.ownKeys(inject).filter(function (key) {
          /* istanbul ignore next */
          return Object.getOwnPropertyDescriptor(inject, key).enumerable
        })
        : Object.keys(inject);

    for (var i = 0; i < keys.length; i++) {
      var key = keys[i];
      var provideKey = inject[key];
      var source = vm;
      while (source) {
        if (source._provided && provideKey in source._provided) {
          result[key] = source._provided[provideKey];
          break
        }
        source = source.$parent;
      }
      if (process.env.NODE_ENV !== 'production' && !source) {
        warn(("Injection \"" + key + "\" not found"), vm);
      }
    }
    return result
  }
}

/*  */

function createFunctionalComponent (
  Ctor,
  propsData,
  data,
  context,
  children
) {
  var props = {};
  var propOptions = Ctor.options.props;
  if (isDef(propOptions)) {
    for (var key in propOptions) {
      props[key] = validateProp(key, propOptions, propsData || emptyObject);
    }
  } else {
    if (isDef(data.attrs)) { mergeProps(props, data.attrs); }
    if (isDef(data.props)) { mergeProps(props, data.props); }
  }
  // ensure the createElement function in functional components
  // gets a unique context - this is necessary for correct named slot check
  var _context = Object.create(context);
  var h = function (a, b, c, d) { return createElement(_context, a, b, c, d, true); };
  var vnode = Ctor.options.render.call(null, h, {
    data: data,
    props: props,
    children: children,
    parent: context,
    listeners: data.on || emptyObject,
    injections: resolveInject(Ctor.options.inject, context),
    slots: function () { return resolveSlots(children, context); }
  });
  if (vnode instanceof VNode) {
    vnode.functionalContext = context;
    vnode.functionalOptions = Ctor.options;
    if (data.slot) {
      (vnode.data || (vnode.data = {})).slot = data.slot;
    }
  }
  return vnode
}

function mergeProps (to, from) {
  for (var key in from) {
    to[camelize(key)] = from[key];
  }
}

/*  */

// hooks to be invoked on component VNodes during patch
var componentVNodeHooks = {
  init: function init (
    vnode,
    hydrating,
    parentElm,
    refElm
  ) {
    if (!vnode.componentInstance || vnode.componentInstance._isDestroyed) {
      var child = vnode.componentInstance = createComponentInstanceForVnode(
        vnode,
        activeInstance,
        parentElm,
        refElm
      );
      child.$mount(hydrating ? vnode.elm : undefined, hydrating);
    } else if (vnode.data.keepAlive) {
      // kept-alive components, treat as a patch
      var mountedNode = vnode; // work around flow
      componentVNodeHooks.prepatch(mountedNode, mountedNode);
    }
  },

  prepatch: function prepatch (oldVnode, vnode) {
    var options = vnode.componentOptions;
    var child = vnode.componentInstance = oldVnode.componentInstance;
    updateChildComponent(
      child,
      options.propsData, // updated props
      options.listeners, // updated listeners
      vnode, // new parent vnode
      options.children // new children
    );
  },

  insert: function insert (vnode) {
    var context = vnode.context;
    var componentInstance = vnode.componentInstance;
    if (!componentInstance._isMounted) {
      componentInstance._isMounted = true;
      callHook(componentInstance, 'mounted');
    }
    if (vnode.data.keepAlive) {
      if (context._isMounted) {
        // vue-router#1212
        // During updates, a kept-alive component's child components may
        // change, so directly walking the tree here may call activated hooks
        // on incorrect children. Instead we push them into a queue which will
        // be processed after the whole patch process ended.
        queueActivatedComponent(componentInstance);
      } else {
        activateChildComponent(componentInstance, true /* direct */);
      }
    }
  },

  destroy: function destroy (vnode) {
    var componentInstance = vnode.componentInstance;
    if (!componentInstance._isDestroyed) {
      if (!vnode.data.keepAlive) {
        componentInstance.$destroy();
      } else {
        deactivateChildComponent(componentInstance, true /* direct */);
      }
    }
  }
};

var hooksToMerge = Object.keys(componentVNodeHooks);

function createComponent (
  Ctor,
  data,
  context,
  children,
  tag
) {
  if (isUndef(Ctor)) {
    return
  }

  var baseCtor = context.$options._base;

  // plain options object: turn it into a constructor
  if (isObject(Ctor)) {
    Ctor = baseCtor.extend(Ctor);
  }

  // if at this stage it's not a constructor or an async component factory,
  // reject.
  if (typeof Ctor !== 'function') {
    if (process.env.NODE_ENV !== 'production') {
      warn(("Invalid Component definition: " + (String(Ctor))), context);
    }
    return
  }

  // async component
  var asyncFactory;
  if (isUndef(Ctor.cid)) {
    asyncFactory = Ctor;
    Ctor = resolveAsyncComponent(asyncFactory, baseCtor, context);
    if (Ctor === undefined) {
      // return a placeholder node for async component, which is rendered
      // as a comment node but preserves all the raw information for the node.
      // the information will be used for async server-rendering and hydration.
      return createAsyncPlaceholder(
        asyncFactory,
        data,
        context,
        children,
        tag
      )
    }
  }

  data = data || {};

  // resolve constructor options in case global mixins are applied after
  // component constructor creation
  resolveConstructorOptions(Ctor);

  // transform component v-model data into props & events
  if (isDef(data.model)) {
    transformModel(Ctor.options, data);
  }

  // extract props
  var propsData = extractPropsFromVNodeData(data, Ctor, tag);

  // functional component
  if (isTrue(Ctor.options.functional)) {
    return createFunctionalComponent(Ctor, propsData, data, context, children)
  }

  // extract listeners, since these needs to be treated as
  // child component listeners instead of DOM listeners
  var listeners = data.on;
  // replace with listeners with .native modifier
  // so it gets processed during parent component patch.
  data.on = data.nativeOn;

  if (isTrue(Ctor.options.abstract)) {
    // abstract components do not keep anything
    // other than props & listeners & slot

    // work around flow
    var slot = data.slot;
    data = {};
    if (slot) {
      data.slot = slot;
    }
  }

  // merge component management hooks onto the placeholder node
  mergeHooks(data);

  // return a placeholder vnode
  var name = Ctor.options.name || tag;
  var vnode = new VNode(
    ("vue-component-" + (Ctor.cid) + (name ? ("-" + name) : '')),
    data, undefined, undefined, undefined, context,
    { Ctor: Ctor, propsData: propsData, listeners: listeners, tag: tag, children: children },
    asyncFactory
  );
  return vnode
}

function createComponentInstanceForVnode (
  vnode, // we know it's MountedComponentVNode but flow doesn't
  parent, // activeInstance in lifecycle state
  parentElm,
  refElm
) {
  var vnodeComponentOptions = vnode.componentOptions;
  var options = {
    _isComponent: true,
    parent: parent,
    propsData: vnodeComponentOptions.propsData,
    _componentTag: vnodeComponentOptions.tag,
    _parentVnode: vnode,
    _parentListeners: vnodeComponentOptions.listeners,
    _renderChildren: vnodeComponentOptions.children,
    _parentElm: parentElm || null,
    _refElm: refElm || null
  };
  // check inline-template render functions
  var inlineTemplate = vnode.data.inlineTemplate;
  if (isDef(inlineTemplate)) {
    options.render = inlineTemplate.render;
    options.staticRenderFns = inlineTemplate.staticRenderFns;
  }
  return new vnodeComponentOptions.Ctor(options)
}

function mergeHooks (data) {
  if (!data.hook) {
    data.hook = {};
  }
  for (var i = 0; i < hooksToMerge.length; i++) {
    var key = hooksToMerge[i];
    var fromParent = data.hook[key];
    var ours = componentVNodeHooks[key];
    data.hook[key] = fromParent ? mergeHook$1(ours, fromParent) : ours;
  }
}

function mergeHook$1 (one, two) {
  return function (a, b, c, d) {
    one(a, b, c, d);
    two(a, b, c, d);
  }
}

// transform component v-model info (value and callback) into
// prop and event handler respectively.
function transformModel (options, data) {
  var prop = (options.model && options.model.prop) || 'value';
  var event = (options.model && options.model.event) || 'input';(data.props || (data.props = {}))[prop] = data.model.value;
  var on = data.on || (data.on = {});
  if (isDef(on[event])) {
    on[event] = [data.model.callback].concat(on[event]);
  } else {
    on[event] = data.model.callback;
  }
}

/*  */

var SIMPLE_NORMALIZE = 1;
var ALWAYS_NORMALIZE = 2;

// wrapper function for providing a more flexible interface
// without getting yelled at by flow
function createElement (
  context,
  tag,
  data,
  children,
  normalizationType,
  alwaysNormalize
) {
  if (Array.isArray(data) || isPrimitive(data)) {
    normalizationType = children;
    children = data;
    data = undefined;
  }
  if (isTrue(alwaysNormalize)) {
    normalizationType = ALWAYS_NORMALIZE;
  }
  return _createElement(context, tag, data, children, normalizationType)
}

function _createElement (
  context,
  tag,
  data,
  children,
  normalizationType
) {
  if (isDef(data) && isDef((data).__ob__)) {
    process.env.NODE_ENV !== 'production' && warn(
      "Avoid using observed data object as vnode data: " + (JSON.stringify(data)) + "\n" +
      'Always create fresh vnode data objects in each render!',
      context
    );
    return createEmptyVNode()
  }
  // object syntax in v-bind
  if (isDef(data) && isDef(data.is)) {
    tag = data.is;
  }
  if (!tag) {
    // in case of component :is set to falsy value
    return createEmptyVNode()
  }
  // warn against non-primitive key
  if (process.env.NODE_ENV !== 'production' &&
    isDef(data) && isDef(data.key) && !isPrimitive(data.key)
  ) {
    warn(
      'Avoid using non-primitive value as key, ' +
      'use string/number value instead.',
      context
    );
  }
  // support single function children as default scoped slot
  if (Array.isArray(children) &&
    typeof children[0] === 'function'
  ) {
    data = data || {};
    data.scopedSlots = { default: children[0] };
    children.length = 0;
  }
  if (normalizationType === ALWAYS_NORMALIZE) {
    children = normalizeChildren(children);
  } else if (normalizationType === SIMPLE_NORMALIZE) {
    children = simpleNormalizeChildren(children);
  }
  var vnode, ns;
  if (typeof tag === 'string') {
    var Ctor;
    ns = (context.$vnode && context.$vnode.ns) || config.getTagNamespace(tag);
    if (config.isReservedTag(tag)) {
      // platform built-in elements
      vnode = new VNode(
        config.parsePlatformTagName(tag), data, children,
        undefined, undefined, context
      );
    } else if (isDef(Ctor = resolveAsset(context.$options, 'components', tag))) {
      // component
      vnode = createComponent(Ctor, data, context, children, tag);
    } else {
      // unknown or unlisted namespaced elements
      // check at runtime because it may get assigned a namespace when its
      // parent normalizes children
      vnode = new VNode(
        tag, data, children,
        undefined, undefined, context
      );
    }
  } else {
    // direct component options / constructor
    vnode = createComponent(tag, data, context, children);
  }
  if (isDef(vnode)) {
    if (ns) { applyNS(vnode, ns); }
    return vnode
  } else {
    return createEmptyVNode()
  }
}

function applyNS (vnode, ns) {
  vnode.ns = ns;
  if (vnode.tag === 'foreignObject') {
    // use default namespace inside foreignObject
    return
  }
  if (isDef(vnode.children)) {
    for (var i = 0, l = vnode.children.length; i < l; i++) {
      var child = vnode.children[i];
      if (isDef(child.tag) && isUndef(child.ns)) {
        applyNS(child, ns);
      }
    }
  }
}

/*  */

/**
 * Runtime helper for rendering v-for lists.
 */
function renderList (
  val,
  render
) {
  var ret, i, l, keys, key;
  if (Array.isArray(val) || typeof val === 'string') {
    ret = new Array(val.length);
    for (i = 0, l = val.length; i < l; i++) {
      ret[i] = render(val[i], i);
    }
  } else if (typeof val === 'number') {
    ret = new Array(val);
    for (i = 0; i < val; i++) {
      ret[i] = render(i + 1, i);
    }
  } else if (isObject(val)) {
    keys = Object.keys(val);
    ret = new Array(keys.length);
    for (i = 0, l = keys.length; i < l; i++) {
      key = keys[i];
      ret[i] = render(val[key], key, i);
    }
  }
  if (isDef(ret)) {
    (ret)._isVList = true;
  }
  return ret
}

/*  */

/**
 * Runtime helper for rendering <slot>
 */
function renderSlot (
  name,
  fallback,
  props,
  bindObject
) {
  var scopedSlotFn = this.$scopedSlots[name];
  if (scopedSlotFn) { // scoped slot
    props = props || {};
    if (bindObject) {
      props = extend(extend({}, bindObject), props);
    }
    return scopedSlotFn(props) || fallback
  } else {
    var slotNodes = this.$slots[name];
    // warn duplicate slot usage
    if (slotNodes && process.env.NODE_ENV !== 'production') {
      slotNodes._rendered && warn(
        "Duplicate presence of slot \"" + name + "\" found in the same render tree " +
        "- this will likely cause render errors.",
        this
      );
      slotNodes._rendered = true;
    }
    return slotNodes || fallback
  }
}

/*  */

/**
 * Runtime helper for resolving filters
 */
function resolveFilter (id) {
  return resolveAsset(this.$options, 'filters', id, true) || identity
}

/*  */

/**
 * Runtime helper for checking keyCodes from config.
 */
function checkKeyCodes (
  eventKeyCode,
  key,
  builtInAlias
) {
  var keyCodes = config.keyCodes[key] || builtInAlias;
  if (Array.isArray(keyCodes)) {
    return keyCodes.indexOf(eventKeyCode) === -1
  } else {
    return keyCodes !== eventKeyCode
  }
}

/*  */

/**
 * Runtime helper for merging v-bind="object" into a VNode's data.
 */
function bindObjectProps (
  data,
  tag,
  value,
  asProp,
  isSync
) {
  if (value) {
    if (!isObject(value)) {
      process.env.NODE_ENV !== 'production' && warn(
        'v-bind without argument expects an Object or Array value',
        this
      );
    } else {
      if (Array.isArray(value)) {
        value = toObject(value);
      }
      var hash;
      var loop = function ( key ) {
        if (
          key === 'class' ||
          key === 'style' ||
          isReservedAttribute(key)
        ) {
          hash = data;
        } else {
          var type = data.attrs && data.attrs.type;
          hash = asProp || config.mustUseProp(tag, type, key)
            ? data.domProps || (data.domProps = {})
            : data.attrs || (data.attrs = {});
        }
        if (!(key in hash)) {
          hash[key] = value[key];

          if (isSync) {
            var on = data.on || (data.on = {});
            on[("update:" + key)] = function ($event) {
              value[key] = $event;
            };
          }
        }
      };

      for (var key in value) loop( key );
    }
  }
  return data
}

/*  */

/**
 * Runtime helper for rendering static trees.
 */
function renderStatic (
  index,
  isInFor
) {
  var tree = this._staticTrees[index];
  // if has already-rendered static tree and not inside v-for,
  // we can reuse the same tree by doing a shallow clone.
  if (tree && !isInFor) {
    return Array.isArray(tree)
      ? cloneVNodes(tree)
      : cloneVNode(tree)
  }
  // otherwise, render a fresh tree.
  tree = this._staticTrees[index] =
    this.$options.staticRenderFns[index].call(this._renderProxy);
  markStatic(tree, ("__static__" + index), false);
  return tree
}

/**
 * Runtime helper for v-once.
 * Effectively it means marking the node as static with a unique key.
 */
function markOnce (
  tree,
  index,
  key
) {
  markStatic(tree, ("__once__" + index + (key ? ("_" + key) : "")), true);
  return tree
}

function markStatic (
  tree,
  key,
  isOnce
) {
  if (Array.isArray(tree)) {
    for (var i = 0; i < tree.length; i++) {
      if (tree[i] && typeof tree[i] !== 'string') {
        markStaticNode(tree[i], (key + "_" + i), isOnce);
      }
    }
  } else {
    markStaticNode(tree, key, isOnce);
  }
}

function markStaticNode (node, key, isOnce) {
  node.isStatic = true;
  node.key = key;
  node.isOnce = isOnce;
}

/*  */

function bindObjectListeners (data, value) {
  if (value) {
    if (!isPlainObject(value)) {
      process.env.NODE_ENV !== 'production' && warn(
        'v-on without argument expects an Object value',
        this
      );
    } else {
      var on = data.on = data.on ? extend({}, data.on) : {};
      for (var key in value) {
        var existing = on[key];
        var ours = value[key];
        on[key] = existing ? [].concat(ours, existing) : ours;
      }
    }
  }
  return data
}

/*  */

function initRender (vm) {
  vm._vnode = null; // the root of the child tree
  vm._staticTrees = null;
  var parentVnode = vm.$vnode = vm.$options._parentVnode; // the placeholder node in parent tree
  var renderContext = parentVnode && parentVnode.context;
  vm.$slots = resolveSlots(vm.$options._renderChildren, renderContext);
  vm.$scopedSlots = emptyObject;
  // bind the createElement fn to this instance
  // so that we get proper render context inside it.
  // args order: tag, data, children, normalizationType, alwaysNormalize
  // internal version is used by render functions compiled from templates
  vm._c = function (a, b, c, d) { return createElement(vm, a, b, c, d, false); };
  // normalization is always applied for the public version, used in
  // user-written render functions.
  vm.$createElement = function (a, b, c, d) { return createElement(vm, a, b, c, d, true); };

  // $attrs & $listeners are exposed for easier HOC creation.
  // they need to be reactive so that HOCs using them are always updated
  var parentData = parentVnode && parentVnode.data;

  /* istanbul ignore else */
  if (process.env.NODE_ENV !== 'production') {
    defineReactive$$1(vm, '$attrs', parentData && parentData.attrs || emptyObject, function () {
      !isUpdatingChildComponent && warn("$attrs is readonly.", vm);
    }, true);
    defineReactive$$1(vm, '$listeners', vm.$options._parentListeners || emptyObject, function () {
      !isUpdatingChildComponent && warn("$listeners is readonly.", vm);
    }, true);
  } else {
    defineReactive$$1(vm, '$attrs', parentData && parentData.attrs || emptyObject, null, true);
    defineReactive$$1(vm, '$listeners', vm.$options._parentListeners || emptyObject, null, true);
  }
}

function renderMixin (Vue) {
  Vue.prototype.$nextTick = function (fn) {
    return nextTick(fn, this)
  };

  Vue.prototype._render = function () {
    var vm = this;
    var ref = vm.$options;
    var render = ref.render;
    var staticRenderFns = ref.staticRenderFns;
    var _parentVnode = ref._parentVnode;

    if (vm._isMounted) {
      // if the parent didn't update, the slot nodes will be the ones from
      // last render. They need to be cloned to ensure "freshness" for this render.
      for (var key in vm.$slots) {
        var slot = vm.$slots[key];
        if (slot._rendered) {
          vm.$slots[key] = cloneVNodes(slot, true /* deep */);
        }
      }
    }

    vm.$scopedSlots = (_parentVnode && _parentVnode.data.scopedSlots) || emptyObject;

    if (staticRenderFns && !vm._staticTrees) {
      vm._staticTrees = [];
    }
    // set parent vnode. this allows render functions to have access
    // to the data on the placeholder node.
    vm.$vnode = _parentVnode;
    // render self
    var vnode;
    try {
      vnode = render.call(vm._renderProxy, vm.$createElement);
    } catch (e) {
      handleError(e, vm, "render function");
      // return error render result,
      // or previous vnode to prevent render error causing blank component
      /* istanbul ignore else */
      if (process.env.NODE_ENV !== 'production') {
        vnode = vm.$options.renderError
          ? vm.$options.renderError.call(vm._renderProxy, vm.$createElement, e)
          : vm._vnode;
      } else {
        vnode = vm._vnode;
      }
    }
    // return empty vnode in case the render function errored out
    if (!(vnode instanceof VNode)) {
      if (process.env.NODE_ENV !== 'production' && Array.isArray(vnode)) {
        warn(
          'Multiple root nodes returned from render function. Render function ' +
          'should return a single root node.',
          vm
        );
      }
      vnode = createEmptyVNode();
    }
    // set parent
    vnode.parent = _parentVnode;
    return vnode
  };

  // internal render helpers.
  // these are exposed on the instance prototype to reduce generated render
  // code size.
  Vue.prototype._o = markOnce;
  Vue.prototype._n = toNumber;
  Vue.prototype._s = toString;
  Vue.prototype._l = renderList;
  Vue.prototype._t = renderSlot;
  Vue.prototype._q = looseEqual;
  Vue.prototype._i = looseIndexOf;
  Vue.prototype._m = renderStatic;
  Vue.prototype._f = resolveFilter;
  Vue.prototype._k = checkKeyCodes;
  Vue.prototype._b = bindObjectProps;
  Vue.prototype._v = createTextVNode;
  Vue.prototype._e = createEmptyVNode;
  Vue.prototype._u = resolveScopedSlots;
  Vue.prototype._g = bindObjectListeners;
}

/*  */

var uid$1 = 0;

function initMixin (Vue) {
  Vue.prototype._init = function (options) {
    var vm = this;
    // a uid
    vm._uid = uid$1++;

    var startTag, endTag;
    /* istanbul ignore if */
    if (process.env.NODE_ENV !== 'production' && config.performance && mark) {
      startTag = "vue-perf-init:" + (vm._uid);
      endTag = "vue-perf-end:" + (vm._uid);
      mark(startTag);
    }

    // a flag to avoid this being observed
    vm._isVue = true;
    // merge options
    if (options && options._isComponent) {
      // optimize internal component instantiation
      // since dynamic options merging is pretty slow, and none of the
      // internal component options needs special treatment.
      initInternalComponent(vm, options);
    } else {
      vm.$options = mergeOptions(
        resolveConstructorOptions(vm.constructor),
        options || {},
        vm
      );
    }
    /* istanbul ignore else */
    if (process.env.NODE_ENV !== 'production') {
      initProxy(vm);
    } else {
      vm._renderProxy = vm;
    }
    // expose real self
    vm._self = vm;
    initLifecycle(vm);
    initEvents(vm);
    initRender(vm);
    callHook(vm, 'beforeCreate');
    initInjections(vm); // resolve injections before data/props
    initState(vm);
    initProvide(vm); // resolve provide after data/props
    callHook(vm, 'created');

    /* istanbul ignore if */
    if (process.env.NODE_ENV !== 'production' && config.performance && mark) {
      vm._name = formatComponentName(vm, false);
      mark(endTag);
      measure(((vm._name) + " init"), startTag, endTag);
    }

    if (vm.$options.el) {
      vm.$mount(vm.$options.el);
    }
  };
}

function initInternalComponent (vm, options) {
  var opts = vm.$options = Object.create(vm.constructor.options);
  // doing this because it's faster than dynamic enumeration.
  opts.parent = options.parent;
  opts.propsData = options.propsData;
  opts._parentVnode = options._parentVnode;
  opts._parentListeners = options._parentListeners;
  opts._renderChildren = options._renderChildren;
  opts._componentTag = options._componentTag;
  opts._parentElm = options._parentElm;
  opts._refElm = options._refElm;
  if (options.render) {
    opts.render = options.render;
    opts.staticRenderFns = options.staticRenderFns;
  }
}

function resolveConstructorOptions (Ctor) {
  var options = Ctor.options;
  if (Ctor.super) {
    var superOptions = resolveConstructorOptions(Ctor.super);
    var cachedSuperOptions = Ctor.superOptions;
    if (superOptions !== cachedSuperOptions) {
      // super option changed,
      // need to resolve new options.
      Ctor.superOptions = superOptions;
      // check if there are any late-modified/attached options (#4976)
      var modifiedOptions = resolveModifiedOptions(Ctor);
      // update base extend options
      if (modifiedOptions) {
        extend(Ctor.extendOptions, modifiedOptions);
      }
      options = Ctor.options = mergeOptions(superOptions, Ctor.extendOptions);
      if (options.name) {
        options.components[options.name] = Ctor;
      }
    }
  }
  return options
}

function resolveModifiedOptions (Ctor) {
  var modified;
  var latest = Ctor.options;
  var extended = Ctor.extendOptions;
  var sealed = Ctor.sealedOptions;
  for (var key in latest) {
    if (latest[key] !== sealed[key]) {
      if (!modified) { modified = {}; }
      modified[key] = dedupe(latest[key], extended[key], sealed[key]);
    }
  }
  return modified
}

function dedupe (latest, extended, sealed) {
  // compare latest and sealed to ensure lifecycle hooks won't be duplicated
  // between merges
  if (Array.isArray(latest)) {
    var res = [];
    sealed = Array.isArray(sealed) ? sealed : [sealed];
    extended = Array.isArray(extended) ? extended : [extended];
    for (var i = 0; i < latest.length; i++) {
      // push original options and not sealed options to exclude duplicated options
      if (extended.indexOf(latest[i]) >= 0 || sealed.indexOf(latest[i]) < 0) {
        res.push(latest[i]);
      }
    }
    return res
  } else {
    return latest
  }
}

function Vue$3 (options) {
  if (process.env.NODE_ENV !== 'production' &&
    !(this instanceof Vue$3)
  ) {
    warn('Vue is a constructor and should be called with the `new` keyword');
  }
  this._init(options);
}

initMixin(Vue$3);
stateMixin(Vue$3);
eventsMixin(Vue$3);
lifecycleMixin(Vue$3);
renderMixin(Vue$3);

/*  */

function initUse (Vue) {
  Vue.use = function (plugin) {
    var installedPlugins = (this._installedPlugins || (this._installedPlugins = []));
    if (installedPlugins.indexOf(plugin) > -1) {
      return this
    }

    // additional parameters
    var args = toArray(arguments, 1);
    args.unshift(this);
    if (typeof plugin.install === 'function') {
      plugin.install.apply(plugin, args);
    } else if (typeof plugin === 'function') {
      plugin.apply(null, args);
    }
    installedPlugins.push(plugin);
    return this
  };
}

/*  */

function initMixin$1 (Vue) {
  Vue.mixin = function (mixin) {
    this.options = mergeOptions(this.options, mixin);
    return this
  };
}

/*  */

function initExtend (Vue) {
  /**
   * Each instance constructor, including Vue, has a unique
   * cid. This enables us to create wrapped "child
   * constructors" for prototypal inheritance and cache them.
   */
  Vue.cid = 0;
  var cid = 1;

  /**
   * Class inheritance
   */
  Vue.extend = function (extendOptions) {
    extendOptions = extendOptions || {};
    var Super = this;
    var SuperId = Super.cid;
    var cachedCtors = extendOptions._Ctor || (extendOptions._Ctor = {});
    if (cachedCtors[SuperId]) {
      return cachedCtors[SuperId]
    }

    var name = extendOptions.name || Super.options.name;
    if (process.env.NODE_ENV !== 'production') {
      if (!/^[a-zA-Z][\w-]*$/.test(name)) {
        warn(
          'Invalid component name: "' + name + '". Component names ' +
          'can only contain alphanumeric characters and the hyphen, ' +
          'and must start with a letter.'
        );
      }
    }

    var Sub = function VueComponent (options) {
      this._init(options);
    };
    Sub.prototype = Object.create(Super.prototype);
    Sub.prototype.constructor = Sub;
    Sub.cid = cid++;
    Sub.options = mergeOptions(
      Super.options,
      extendOptions
    );
    Sub['super'] = Super;

    // For props and computed properties, we define the proxy getters on
    // the Vue instances at extension time, on the extended prototype. This
    // avoids Object.defineProperty calls for each instance created.
    if (Sub.options.props) {
      initProps$1(Sub);
    }
    if (Sub.options.computed) {
      initComputed$1(Sub);
    }

    // allow further extension/mixin/plugin usage
    Sub.extend = Super.extend;
    Sub.mixin = Super.mixin;
    Sub.use = Super.use;

    // create asset registers, so extended classes
    // can have their private assets too.
    ASSET_TYPES.forEach(function (type) {
      Sub[type] = Super[type];
    });
    // enable recursive self-lookup
    if (name) {
      Sub.options.components[name] = Sub;
    }

    // keep a reference to the super options at extension time.
    // later at instantiation we can check if Super's options have
    // been updated.
    Sub.superOptions = Super.options;
    Sub.extendOptions = extendOptions;
    Sub.sealedOptions = extend({}, Sub.options);

    // cache constructor
    cachedCtors[SuperId] = Sub;
    return Sub
  };
}

function initProps$1 (Comp) {
  var props = Comp.options.props;
  for (var key in props) {
    proxy(Comp.prototype, "_props", key);
  }
}

function initComputed$1 (Comp) {
  var computed = Comp.options.computed;
  for (var key in computed) {
    defineComputed(Comp.prototype, key, computed[key]);
  }
}

/*  */

function initAssetRegisters (Vue) {
  /**
   * Create asset registration methods.
   */
  ASSET_TYPES.forEach(function (type) {
    Vue[type] = function (
      id,
      definition
    ) {
      if (!definition) {
        return this.options[type + 's'][id]
      } else {
        /* istanbul ignore if */
        if (process.env.NODE_ENV !== 'production') {
          if (type === 'component' && config.isReservedTag(id)) {
            warn(
              'Do not use built-in or reserved HTML elements as component ' +
              'id: ' + id
            );
          }
        }
        if (type === 'component' && isPlainObject(definition)) {
          definition.name = definition.name || id;
          definition = this.options._base.extend(definition);
        }
        if (type === 'directive' && typeof definition === 'function') {
          definition = { bind: definition, update: definition };
        }
        this.options[type + 's'][id] = definition;
        return definition
      }
    };
  });
}

/*  */

var patternTypes = [String, RegExp, Array];

function getComponentName (opts) {
  return opts && (opts.Ctor.options.name || opts.tag)
}

function matches (pattern, name) {
  if (Array.isArray(pattern)) {
    return pattern.indexOf(name) > -1
  } else if (typeof pattern === 'string') {
    return pattern.split(',').indexOf(name) > -1
  } else if (isRegExp(pattern)) {
    return pattern.test(name)
  }
  /* istanbul ignore next */
  return false
}

function pruneCache (cache, current, filter) {
  for (var key in cache) {
    var cachedNode = cache[key];
    if (cachedNode) {
      var name = getComponentName(cachedNode.componentOptions);
      if (name && !filter(name)) {
        if (cachedNode !== current) {
          pruneCacheEntry(cachedNode);
        }
        cache[key] = null;
      }
    }
  }
}

function pruneCacheEntry (vnode) {
  if (vnode) {
    vnode.componentInstance.$destroy();
  }
}

var KeepAlive = {
  name: 'keep-alive',
  abstract: true,

  props: {
    include: patternTypes,
    exclude: patternTypes
  },

  created: function created () {
    this.cache = Object.create(null);
  },

  destroyed: function destroyed () {
    var this$1 = this;

    for (var key in this$1.cache) {
      pruneCacheEntry(this$1.cache[key]);
    }
  },

  watch: {
    include: function include (val) {
      pruneCache(this.cache, this._vnode, function (name) { return matches(val, name); });
    },
    exclude: function exclude (val) {
      pruneCache(this.cache, this._vnode, function (name) { return !matches(val, name); });
    }
  },

  render: function render () {
    var vnode = getFirstComponentChild(this.$slots.default);
    var componentOptions = vnode && vnode.componentOptions;
    if (componentOptions) {
      // check pattern
      var name = getComponentName(componentOptions);
      if (name && (
        (this.include && !matches(this.include, name)) ||
        (this.exclude && matches(this.exclude, name))
      )) {
        return vnode
      }
      var key = vnode.key == null
        // same constructor may get registered as different local components
        // so cid alone is not enough (#3269)
        ? componentOptions.Ctor.cid + (componentOptions.tag ? ("::" + (componentOptions.tag)) : '')
        : vnode.key;
      if (this.cache[key]) {
        vnode.componentInstance = this.cache[key].componentInstance;
      } else {
        this.cache[key] = vnode;
      }
      vnode.data.keepAlive = true;
    }
    return vnode
  }
};

var builtInComponents = {
  KeepAlive: KeepAlive
};

/*  */

function initGlobalAPI (Vue) {
  // config
  var configDef = {};
  configDef.get = function () { return config; };
  if (process.env.NODE_ENV !== 'production') {
    configDef.set = function () {
      warn(
        'Do not replace the Vue.config object, set individual fields instead.'
      );
    };
  }
  Object.defineProperty(Vue, 'config', configDef);

  // exposed util methods.
  // NOTE: these are not considered part of the public API - avoid relying on
  // them unless you are aware of the risk.
  Vue.util = {
    warn: warn,
    extend: extend,
    mergeOptions: mergeOptions,
    defineReactive: defineReactive$$1
  };

  Vue.set = set;
  Vue.delete = del;
  Vue.nextTick = nextTick;

  Vue.options = Object.create(null);
  ASSET_TYPES.forEach(function (type) {
    Vue.options[type + 's'] = Object.create(null);
  });

  // this is used to identify the "base" constructor to extend all plain-object
  // components with in Weex's multi-instance scenarios.
  Vue.options._base = Vue;

  extend(Vue.options.components, builtInComponents);

  initUse(Vue);
  initMixin$1(Vue);
  initExtend(Vue);
  initAssetRegisters(Vue);
}

initGlobalAPI(Vue$3);

Object.defineProperty(Vue$3.prototype, '$isServer', {
  get: isServerRendering
});

Object.defineProperty(Vue$3.prototype, '$ssrContext', {
  get: function get () {
    /* istanbul ignore next */
    return this.$vnode && this.$vnode.ssrContext
  }
});

Vue$3.version = '2.4.4';

/*  */

// these are reserved for web because they are directly compiled away
// during template compilation
var isReservedAttr = makeMap('style,class');

// attributes that should be using props for binding
var acceptValue = makeMap('input,textarea,option,select,progress');
var mustUseProp = function (tag, type, attr) {
  return (
    (attr === 'value' && acceptValue(tag)) && type !== 'button' ||
    (attr === 'selected' && tag === 'option') ||
    (attr === 'checked' && tag === 'input') ||
    (attr === 'muted' && tag === 'video')
  )
};

var isEnumeratedAttr = makeMap('contenteditable,draggable,spellcheck');

var isBooleanAttr = makeMap(
  'allowfullscreen,async,autofocus,autoplay,checked,compact,controls,declare,' +
  'default,defaultchecked,defaultmuted,defaultselected,defer,disabled,' +
  'enabled,formnovalidate,hidden,indeterminate,inert,ismap,itemscope,loop,multiple,' +
  'muted,nohref,noresize,noshade,novalidate,nowrap,open,pauseonexit,readonly,' +
  'required,reversed,scoped,seamless,selected,sortable,translate,' +
  'truespeed,typemustmatch,visible'
);

var xlinkNS = 'http://www.w3.org/1999/xlink';

var isXlink = function (name) {
  return name.charAt(5) === ':' && name.slice(0, 5) === 'xlink'
};

var getXlinkProp = function (name) {
  return isXlink(name) ? name.slice(6, name.length) : ''
};

var isFalsyAttrValue = function (val) {
  return val == null || val === false
};

/*  */

function genClassForVnode (vnode) {
  var data = vnode.data;
  var parentNode = vnode;
  var childNode = vnode;
  while (isDef(childNode.componentInstance)) {
    childNode = childNode.componentInstance._vnode;
    if (childNode.data) {
      data = mergeClassData(childNode.data, data);
    }
  }
  while (isDef(parentNode = parentNode.parent)) {
    if (parentNode.data) {
      data = mergeClassData(data, parentNode.data);
    }
  }
  return renderClass(data.staticClass, data.class)
}

function mergeClassData (child, parent) {
  return {
    staticClass: concat(child.staticClass, parent.staticClass),
    class: isDef(child.class)
      ? [child.class, parent.class]
      : parent.class
  }
}

function renderClass (
  staticClass,
  dynamicClass
) {
  if (isDef(staticClass) || isDef(dynamicClass)) {
    return concat(staticClass, stringifyClass(dynamicClass))
  }
  /* istanbul ignore next */
  return ''
}

function concat (a, b) {
  return a ? b ? (a + ' ' + b) : a : (b || '')
}

function stringifyClass (value) {
  if (Array.isArray(value)) {
    return stringifyArray(value)
  }
  if (isObject(value)) {
    return stringifyObject(value)
  }
  if (typeof value === 'string') {
    return value
  }
  /* istanbul ignore next */
  return ''
}

function stringifyArray (value) {
  var res = '';
  var stringified;
  for (var i = 0, l = value.length; i < l; i++) {
    if (isDef(stringified = stringifyClass(value[i])) && stringified !== '') {
      if (res) { res += ' '; }
      res += stringified;
    }
  }
  return res
}

function stringifyObject (value) {
  var res = '';
  for (var key in value) {
    if (value[key]) {
      if (res) { res += ' '; }
      res += key;
    }
  }
  return res
}

/*  */

var namespaceMap = {
  svg: 'http://www.w3.org/2000/svg',
  math: 'http://www.w3.org/1998/Math/MathML'
};

var isHTMLTag = makeMap(
  'html,body,base,head,link,meta,style,title,' +
  'address,article,aside,footer,header,h1,h2,h3,h4,h5,h6,hgroup,nav,section,' +
  'div,dd,dl,dt,figcaption,figure,picture,hr,img,li,main,ol,p,pre,ul,' +
  'a,b,abbr,bdi,bdo,br,cite,code,data,dfn,em,i,kbd,mark,q,rp,rt,rtc,ruby,' +
  's,samp,small,span,strong,sub,sup,time,u,var,wbr,area,audio,map,track,video,' +
  'embed,object,param,source,canvas,script,noscript,del,ins,' +
  'caption,col,colgroup,table,thead,tbody,td,th,tr,' +
  'button,datalist,fieldset,form,input,label,legend,meter,optgroup,option,' +
  'output,progress,select,textarea,' +
  'details,dialog,menu,menuitem,summary,' +
  'content,element,shadow,template,blockquote,iframe,tfoot'
);

// this map is intentionally selective, only covering SVG elements that may
// contain child elements.
var isSVG = makeMap(
  'svg,animate,circle,clippath,cursor,defs,desc,ellipse,filter,font-face,' +
  'foreignObject,g,glyph,image,line,marker,mask,missing-glyph,path,pattern,' +
  'polygon,polyline,rect,switch,symbol,text,textpath,tspan,use,view',
  true
);

var isPreTag = function (tag) { return tag === 'pre'; };

var isReservedTag = function (tag) {
  return isHTMLTag(tag) || isSVG(tag)
};

function getTagNamespace (tag) {
  if (isSVG(tag)) {
    return 'svg'
  }
  // basic support for MathML
  // note it doesn't support other MathML elements being component roots
  if (tag === 'math') {
    return 'math'
  }
}

var unknownElementCache = Object.create(null);
function isUnknownElement (tag) {
  /* istanbul ignore if */
  if (!inBrowser) {
    return true
  }
  if (isReservedTag(tag)) {
    return false
  }
  tag = tag.toLowerCase();
  /* istanbul ignore if */
  if (unknownElementCache[tag] != null) {
    return unknownElementCache[tag]
  }
  var el = document.createElement(tag);
  if (tag.indexOf('-') > -1) {
    // http://stackoverflow.com/a/28210364/1070244
    return (unknownElementCache[tag] = (
      el.constructor === window.HTMLUnknownElement ||
      el.constructor === window.HTMLElement
    ))
  } else {
    return (unknownElementCache[tag] = /HTMLUnknownElement/.test(el.toString()))
  }
}

var isTextInputType = makeMap('text,number,password,search,email,tel,url');

/*  */

/**
 * Query an element selector if it's not an element already.
 */
function query (el) {
  if (typeof el === 'string') {
    var selected = document.querySelector(el);
    if (!selected) {
      process.env.NODE_ENV !== 'production' && warn(
        'Cannot find element: ' + el
      );
      return document.createElement('div')
    }
    return selected
  } else {
    return el
  }
}

/*  */

function createElement$1 (tagName, vnode) {
  var elm = document.createElement(tagName);
  if (tagName !== 'select') {
    return elm
  }
  // false or null will remove the attribute but undefined will not
  if (vnode.data && vnode.data.attrs && vnode.data.attrs.multiple !== undefined) {
    elm.setAttribute('multiple', 'multiple');
  }
  return elm
}

function createElementNS (namespace, tagName) {
  return document.createElementNS(namespaceMap[namespace], tagName)
}

function createTextNode (text) {
  return document.createTextNode(text)
}

function createComment (text) {
  return document.createComment(text)
}

function insertBefore (parentNode, newNode, referenceNode) {
  parentNode.insertBefore(newNode, referenceNode);
}

function removeChild (node, child) {
  node.removeChild(child);
}

function appendChild (node, child) {
  node.appendChild(child);
}

function parentNode (node) {
  return node.parentNode
}

function nextSibling (node) {
  return node.nextSibling
}

function tagName (node) {
  return node.tagName
}

function setTextContent (node, text) {
  node.textContent = text;
}

function setAttribute (node, key, val) {
  node.setAttribute(key, val);
}


var nodeOps = Object.freeze({
	createElement: createElement$1,
	createElementNS: createElementNS,
	createTextNode: createTextNode,
	createComment: createComment,
	insertBefore: insertBefore,
	removeChild: removeChild,
	appendChild: appendChild,
	parentNode: parentNode,
	nextSibling: nextSibling,
	tagName: tagName,
	setTextContent: setTextContent,
	setAttribute: setAttribute
});

/*  */

var ref = {
  create: function create (_, vnode) {
    registerRef(vnode);
  },
  update: function update (oldVnode, vnode) {
    if (oldVnode.data.ref !== vnode.data.ref) {
      registerRef(oldVnode, true);
      registerRef(vnode);
    }
  },
  destroy: function destroy (vnode) {
    registerRef(vnode, true);
  }
};

function registerRef (vnode, isRemoval) {
  var key = vnode.data.ref;
  if (!key) { return }

  var vm = vnode.context;
  var ref = vnode.componentInstance || vnode.elm;
  var refs = vm.$refs;
  if (isRemoval) {
    if (Array.isArray(refs[key])) {
      remove(refs[key], ref);
    } else if (refs[key] === ref) {
      refs[key] = undefined;
    }
  } else {
    if (vnode.data.refInFor) {
      if (!Array.isArray(refs[key])) {
        refs[key] = [ref];
      } else if (refs[key].indexOf(ref) < 0) {
        // $flow-disable-line
        refs[key].push(ref);
      }
    } else {
      refs[key] = ref;
    }
  }
}

/**
 * Virtual DOM patching algorithm based on Snabbdom by
 * Simon Friis Vindum (@paldepind)
 * Licensed under the MIT License
 * https://github.com/paldepind/snabbdom/blob/master/LICENSE
 *
 * modified by Evan You (@yyx990803)
 *
 * Not type-checking this because this file is perf-critical and the cost
 * of making flow understand it is not worth it.
 */

var emptyNode = new VNode('', {}, []);

var hooks = ['create', 'activate', 'update', 'remove', 'destroy'];

function sameVnode (a, b) {
  return (
    a.key === b.key && (
      (
        a.tag === b.tag &&
        a.isComment === b.isComment &&
        isDef(a.data) === isDef(b.data) &&
        sameInputType(a, b)
      ) || (
        isTrue(a.isAsyncPlaceholder) &&
        a.asyncFactory === b.asyncFactory &&
        isUndef(b.asyncFactory.error)
      )
    )
  )
}

function sameInputType (a, b) {
  if (a.tag !== 'input') { return true }
  var i;
  var typeA = isDef(i = a.data) && isDef(i = i.attrs) && i.type;
  var typeB = isDef(i = b.data) && isDef(i = i.attrs) && i.type;
  return typeA === typeB || isTextInputType(typeA) && isTextInputType(typeB)
}

function createKeyToOldIdx (children, beginIdx, endIdx) {
  var i, key;
  var map = {};
  for (i = beginIdx; i <= endIdx; ++i) {
    key = children[i].key;
    if (isDef(key)) { map[key] = i; }
  }
  return map
}

function createPatchFunction (backend) {
  var i, j;
  var cbs = {};

  var modules = backend.modules;
  var nodeOps = backend.nodeOps;

  for (i = 0; i < hooks.length; ++i) {
    cbs[hooks[i]] = [];
    for (j = 0; j < modules.length; ++j) {
      if (isDef(modules[j][hooks[i]])) {
        cbs[hooks[i]].push(modules[j][hooks[i]]);
      }
    }
  }

  function emptyNodeAt (elm) {
    return new VNode(nodeOps.tagName(elm).toLowerCase(), {}, [], undefined, elm)
  }

  function createRmCb (childElm, listeners) {
    function remove$$1 () {
      if (--remove$$1.listeners === 0) {
        removeNode(childElm);
      }
    }
    remove$$1.listeners = listeners;
    return remove$$1
  }

  function removeNode (el) {
    var parent = nodeOps.parentNode(el);
    // element may have already been removed due to v-html / v-text
    if (isDef(parent)) {
      nodeOps.removeChild(parent, el);
    }
  }

  var inPre = 0;
  function createElm (vnode, insertedVnodeQueue, parentElm, refElm, nested) {
    vnode.isRootInsert = !nested; // for transition enter check
    if (createComponent(vnode, insertedVnodeQueue, parentElm, refElm)) {
      return
    }

    var data = vnode.data;
    var children = vnode.children;
    var tag = vnode.tag;
    if (isDef(tag)) {
      if (process.env.NODE_ENV !== 'production') {
        if (data && data.pre) {
          inPre++;
        }
        if (
          !inPre &&
          !vnode.ns &&
          !(config.ignoredElements.length && config.ignoredElements.indexOf(tag) > -1) &&
          config.isUnknownElement(tag)
        ) {
          warn(
            'Unknown custom element: <' + tag + '> - did you ' +
            'register the component correctly? For recursive components, ' +
            'make sure to provide the "name" option.',
            vnode.context
          );
        }
      }
      vnode.elm = vnode.ns
        ? nodeOps.createElementNS(vnode.ns, tag)
        : nodeOps.createElement(tag, vnode);
      setScope(vnode);

      /* istanbul ignore if */
      {
        createChildren(vnode, children, insertedVnodeQueue);
        if (isDef(data)) {
          invokeCreateHooks(vnode, insertedVnodeQueue);
        }
        insert(parentElm, vnode.elm, refElm);
      }

      if (process.env.NODE_ENV !== 'production' && data && data.pre) {
        inPre--;
      }
    } else if (isTrue(vnode.isComment)) {
      vnode.elm = nodeOps.createComment(vnode.text);
      insert(parentElm, vnode.elm, refElm);
    } else {
      vnode.elm = nodeOps.createTextNode(vnode.text);
      insert(parentElm, vnode.elm, refElm);
    }
  }

  function createComponent (vnode, insertedVnodeQueue, parentElm, refElm) {
    var i = vnode.data;
    if (isDef(i)) {
      var isReactivated = isDef(vnode.componentInstance) && i.keepAlive;
      if (isDef(i = i.hook) && isDef(i = i.init)) {
        i(vnode, false /* hydrating */, parentElm, refElm);
      }
      // after calling the init hook, if the vnode is a child component
      // it should've created a child instance and mounted it. the child
      // component also has set the placeholder vnode's elm.
      // in that case we can just return the element and be done.
      if (isDef(vnode.componentInstance)) {
        initComponent(vnode, insertedVnodeQueue);
        if (isTrue(isReactivated)) {
          reactivateComponent(vnode, insertedVnodeQueue, parentElm, refElm);
        }
        return true
      }
    }
  }

  function initComponent (vnode, insertedVnodeQueue) {
    if (isDef(vnode.data.pendingInsert)) {
      insertedVnodeQueue.push.apply(insertedVnodeQueue, vnode.data.pendingInsert);
      vnode.data.pendingInsert = null;
    }
    vnode.elm = vnode.componentInstance.$el;
    if (isPatchable(vnode)) {
      invokeCreateHooks(vnode, insertedVnodeQueue);
      setScope(vnode);
    } else {
      // empty component root.
      // skip all element-related modules except for ref (#3455)
      registerRef(vnode);
      // make sure to invoke the insert hook
      insertedVnodeQueue.push(vnode);
    }
  }

  function reactivateComponent (vnode, insertedVnodeQueue, parentElm, refElm) {
    var i;
    // hack for #4339: a reactivated component with inner transition
    // does not trigger because the inner node's created hooks are not called
    // again. It's not ideal to involve module-specific logic in here but
    // there doesn't seem to be a better way to do it.
    var innerNode = vnode;
    while (innerNode.componentInstance) {
      innerNode = innerNode.componentInstance._vnode;
      if (isDef(i = innerNode.data) && isDef(i = i.transition)) {
        for (i = 0; i < cbs.activate.length; ++i) {
          cbs.activate[i](emptyNode, innerNode);
        }
        insertedVnodeQueue.push(innerNode);
        break
      }
    }
    // unlike a newly created component,
    // a reactivated keep-alive component doesn't insert itself
    insert(parentElm, vnode.elm, refElm);
  }

  function insert (parent, elm, ref$$1) {
    if (isDef(parent)) {
      if (isDef(ref$$1)) {
        if (ref$$1.parentNode === parent) {
          nodeOps.insertBefore(parent, elm, ref$$1);
        }
      } else {
        nodeOps.appendChild(parent, elm);
      }
    }
  }

  function createChildren (vnode, children, insertedVnodeQueue) {
    if (Array.isArray(children)) {
      for (var i = 0; i < children.length; ++i) {
        createElm(children[i], insertedVnodeQueue, vnode.elm, null, true);
      }
    } else if (isPrimitive(vnode.text)) {
      nodeOps.appendChild(vnode.elm, nodeOps.createTextNode(vnode.text));
    }
  }

  function isPatchable (vnode) {
    while (vnode.componentInstance) {
      vnode = vnode.componentInstance._vnode;
    }
    return isDef(vnode.tag)
  }

  function invokeCreateHooks (vnode, insertedVnodeQueue) {
    for (var i$1 = 0; i$1 < cbs.create.length; ++i$1) {
      cbs.create[i$1](emptyNode, vnode);
    }
    i = vnode.data.hook; // Reuse variable
    if (isDef(i)) {
      if (isDef(i.create)) { i.create(emptyNode, vnode); }
      if (isDef(i.insert)) { insertedVnodeQueue.push(vnode); }
    }
  }

  // set scope id attribute for scoped CSS.
  // this is implemented as a special case to avoid the overhead
  // of going through the normal attribute patching process.
  function setScope (vnode) {
    var i;
    var ancestor = vnode;
    while (ancestor) {
      if (isDef(i = ancestor.context) && isDef(i = i.$options._scopeId)) {
        nodeOps.setAttribute(vnode.elm, i, '');
      }
      ancestor = ancestor.parent;
    }
    // for slot content they should also get the scopeId from the host instance.
    if (isDef(i = activeInstance) &&
      i !== vnode.context &&
      isDef(i = i.$options._scopeId)
    ) {
      nodeOps.setAttribute(vnode.elm, i, '');
    }
  }

  function addVnodes (parentElm, refElm, vnodes, startIdx, endIdx, insertedVnodeQueue) {
    for (; startIdx <= endIdx; ++startIdx) {
      createElm(vnodes[startIdx], insertedVnodeQueue, parentElm, refElm);
    }
  }

  function invokeDestroyHook (vnode) {
    var i, j;
    var data = vnode.data;
    if (isDef(data)) {
      if (isDef(i = data.hook) && isDef(i = i.destroy)) { i(vnode); }
      for (i = 0; i < cbs.destroy.length; ++i) { cbs.destroy[i](vnode); }
    }
    if (isDef(i = vnode.children)) {
      for (j = 0; j < vnode.children.length; ++j) {
        invokeDestroyHook(vnode.children[j]);
      }
    }
  }

  function removeVnodes (parentElm, vnodes, startIdx, endIdx) {
    for (; startIdx <= endIdx; ++startIdx) {
      var ch = vnodes[startIdx];
      if (isDef(ch)) {
        if (isDef(ch.tag)) {
          removeAndInvokeRemoveHook(ch);
          invokeDestroyHook(ch);
        } else { // Text node
          removeNode(ch.elm);
        }
      }
    }
  }

  function removeAndInvokeRemoveHook (vnode, rm) {
    if (isDef(rm) || isDef(vnode.data)) {
      var i;
      var listeners = cbs.remove.length + 1;
      if (isDef(rm)) {
        // we have a recursively passed down rm callback
        // increase the listeners count
        rm.listeners += listeners;
      } else {
        // directly removing
        rm = createRmCb(vnode.elm, listeners);
      }
      // recursively invoke hooks on child component root node
      if (isDef(i = vnode.componentInstance) && isDef(i = i._vnode) && isDef(i.data)) {
        removeAndInvokeRemoveHook(i, rm);
      }
      for (i = 0; i < cbs.remove.length; ++i) {
        cbs.remove[i](vnode, rm);
      }
      if (isDef(i = vnode.data.hook) && isDef(i = i.remove)) {
        i(vnode, rm);
      } else {
        rm();
      }
    } else {
      removeNode(vnode.elm);
    }
  }

  function updateChildren (parentElm, oldCh, newCh, insertedVnodeQueue, removeOnly) {
    var oldStartIdx = 0;
    var newStartIdx = 0;
    var oldEndIdx = oldCh.length - 1;
    var oldStartVnode = oldCh[0];
    var oldEndVnode = oldCh[oldEndIdx];
    var newEndIdx = newCh.length - 1;
    var newStartVnode = newCh[0];
    var newEndVnode = newCh[newEndIdx];
    var oldKeyToIdx, idxInOld, elmToMove, refElm;

    // removeOnly is a special flag used only by <transition-group>
    // to ensure removed elements stay in correct relative positions
    // during leaving transitions
    var canMove = !removeOnly;

    while (oldStartIdx <= oldEndIdx && newStartIdx <= newEndIdx) {
      if (isUndef(oldStartVnode)) {
        oldStartVnode = oldCh[++oldStartIdx]; // Vnode has been moved left
      } else if (isUndef(oldEndVnode)) {
        oldEndVnode = oldCh[--oldEndIdx];
      } else if (sameVnode(oldStartVnode, newStartVnode)) {
        patchVnode(oldStartVnode, newStartVnode, insertedVnodeQueue);
        oldStartVnode = oldCh[++oldStartIdx];
        newStartVnode = newCh[++newStartIdx];
      } else if (sameVnode(oldEndVnode, newEndVnode)) {
        patchVnode(oldEndVnode, newEndVnode, insertedVnodeQueue);
        oldEndVnode = oldCh[--oldEndIdx];
        newEndVnode = newCh[--newEndIdx];
      } else if (sameVnode(oldStartVnode, newEndVnode)) { // Vnode moved right
        patchVnode(oldStartVnode, newEndVnode, insertedVnodeQueue);
        canMove && nodeOps.insertBefore(parentElm, oldStartVnode.elm, nodeOps.nextSibling(oldEndVnode.elm));
        oldStartVnode = oldCh[++oldStartIdx];
        newEndVnode = newCh[--newEndIdx];
      } else if (sameVnode(oldEndVnode, newStartVnode)) { // Vnode moved left
        patchVnode(oldEndVnode, newStartVnode, insertedVnodeQueue);
        canMove && nodeOps.insertBefore(parentElm, oldEndVnode.elm, oldStartVnode.elm);
        oldEndVnode = oldCh[--oldEndIdx];
        newStartVnode = newCh[++newStartIdx];
      } else {
        if (isUndef(oldKeyToIdx)) { oldKeyToIdx = createKeyToOldIdx(oldCh, oldStartIdx, oldEndIdx); }
        idxInOld = isDef(newStartVnode.key)
          ? oldKeyToIdx[newStartVnode.key]
          : findIdxInOld(newStartVnode, oldCh, oldStartIdx, oldEndIdx);
        if (isUndef(idxInOld)) { // New element
          createElm(newStartVnode, insertedVnodeQueue, parentElm, oldStartVnode.elm);
        } else {
          elmToMove = oldCh[idxInOld];
          /* istanbul ignore if */
          if (process.env.NODE_ENV !== 'production' && !elmToMove) {
            warn(
              'It seems there are duplicate keys that is causing an update error. ' +
              'Make sure each v-for item has a unique key.'
            );
          }
          if (sameVnode(elmToMove, newStartVnode)) {
            patchVnode(elmToMove, newStartVnode, insertedVnodeQueue);
            oldCh[idxInOld] = undefined;
            canMove && nodeOps.insertBefore(parentElm, elmToMove.elm, oldStartVnode.elm);
          } else {
            // same key but different element. treat as new element
            createElm(newStartVnode, insertedVnodeQueue, parentElm, oldStartVnode.elm);
          }
        }
        newStartVnode = newCh[++newStartIdx];
      }
    }
    if (oldStartIdx > oldEndIdx) {
      refElm = isUndef(newCh[newEndIdx + 1]) ? null : newCh[newEndIdx + 1].elm;
      addVnodes(parentElm, refElm, newCh, newStartIdx, newEndIdx, insertedVnodeQueue);
    } else if (newStartIdx > newEndIdx) {
      removeVnodes(parentElm, oldCh, oldStartIdx, oldEndIdx);
    }
  }

  function findIdxInOld (node, oldCh, start, end) {
    for (var i = start; i < end; i++) {
      var c = oldCh[i];
      if (isDef(c) && sameVnode(node, c)) { return i }
    }
  }

  function patchVnode (oldVnode, vnode, insertedVnodeQueue, removeOnly) {
    if (oldVnode === vnode) {
      return
    }

    var elm = vnode.elm = oldVnode.elm;

    if (isTrue(oldVnode.isAsyncPlaceholder)) {
      if (isDef(vnode.asyncFactory.resolved)) {
        hydrate(oldVnode.elm, vnode, insertedVnodeQueue);
      } else {
        vnode.isAsyncPlaceholder = true;
      }
      return
    }

    // reuse element for static trees.
    // note we only do this if the vnode is cloned -
    // if the new node is not cloned it means the render functions have been
    // reset by the hot-reload-api and we need to do a proper re-render.
    if (isTrue(vnode.isStatic) &&
      isTrue(oldVnode.isStatic) &&
      vnode.key === oldVnode.key &&
      (isTrue(vnode.isCloned) || isTrue(vnode.isOnce))
    ) {
      vnode.componentInstance = oldVnode.componentInstance;
      return
    }

    var i;
    var data = vnode.data;
    if (isDef(data) && isDef(i = data.hook) && isDef(i = i.prepatch)) {
      i(oldVnode, vnode);
    }

    var oldCh = oldVnode.children;
    var ch = vnode.children;
    if (isDef(data) && isPatchable(vnode)) {
      for (i = 0; i < cbs.update.length; ++i) { cbs.update[i](oldVnode, vnode); }
      if (isDef(i = data.hook) && isDef(i = i.update)) { i(oldVnode, vnode); }
    }
    if (isUndef(vnode.text)) {
      if (isDef(oldCh) && isDef(ch)) {
        if (oldCh !== ch) { updateChildren(elm, oldCh, ch, insertedVnodeQueue, removeOnly); }
      } else if (isDef(ch)) {
        if (isDef(oldVnode.text)) { nodeOps.setTextContent(elm, ''); }
        addVnodes(elm, null, ch, 0, ch.length - 1, insertedVnodeQueue);
      } else if (isDef(oldCh)) {
        removeVnodes(elm, oldCh, 0, oldCh.length - 1);
      } else if (isDef(oldVnode.text)) {
        nodeOps.setTextContent(elm, '');
      }
    } else if (oldVnode.text !== vnode.text) {
      nodeOps.setTextContent(elm, vnode.text);
    }
    if (isDef(data)) {
      if (isDef(i = data.hook) && isDef(i = i.postpatch)) { i(oldVnode, vnode); }
    }
  }

  function invokeInsertHook (vnode, queue, initial) {
    // delay insert hooks for component root nodes, invoke them after the
    // element is really inserted
    if (isTrue(initial) && isDef(vnode.parent)) {
      vnode.parent.data.pendingInsert = queue;
    } else {
      for (var i = 0; i < queue.length; ++i) {
        queue[i].data.hook.insert(queue[i]);
      }
    }
  }

  var bailed = false;
  // list of modules that can skip create hook during hydration because they
  // are already rendered on the client or has no need for initialization
  var isRenderedModule = makeMap('attrs,style,class,staticClass,staticStyle,key');

  // Note: this is a browser-only function so we can assume elms are DOM nodes.
  function hydrate (elm, vnode, insertedVnodeQueue) {
    if (isTrue(vnode.isComment) && isDef(vnode.asyncFactory)) {
      vnode.elm = elm;
      vnode.isAsyncPlaceholder = true;
      return true
    }
    if (process.env.NODE_ENV !== 'production') {
      if (!assertNodeMatch(elm, vnode)) {
        return false
      }
    }
    vnode.elm = elm;
    var tag = vnode.tag;
    var data = vnode.data;
    var children = vnode.children;
    if (isDef(data)) {
      if (isDef(i = data.hook) && isDef(i = i.init)) { i(vnode, true /* hydrating */); }
      if (isDef(i = vnode.componentInstance)) {
        // child component. it should have hydrated its own tree.
        initComponent(vnode, insertedVnodeQueue);
        return true
      }
    }
    if (isDef(tag)) {
      if (isDef(children)) {
        // empty element, allow client to pick up and populate children
        if (!elm.hasChildNodes()) {
          createChildren(vnode, children, insertedVnodeQueue);
        } else {
          // v-html and domProps: innerHTML
          if (isDef(i = data) && isDef(i = i.domProps) && isDef(i = i.innerHTML)) {
            if (i !== elm.innerHTML) {
              /* istanbul ignore if */
              if (process.env.NODE_ENV !== 'production' &&
                typeof console !== 'undefined' &&
                !bailed
              ) {
                bailed = true;
                console.warn('Parent: ', elm);
                console.warn('server innerHTML: ', i);
                console.warn('client innerHTML: ', elm.innerHTML);
              }
              return false
            }
          } else {
            // iterate and compare children lists
            var childrenMatch = true;
            var childNode = elm.firstChild;
            for (var i$1 = 0; i$1 < children.length; i$1++) {
              if (!childNode || !hydrate(childNode, children[i$1], insertedVnodeQueue)) {
                childrenMatch = false;
                break
              }
              childNode = childNode.nextSibling;
            }
            // if childNode is not null, it means the actual childNodes list is
            // longer than the virtual children list.
            if (!childrenMatch || childNode) {
              /* istanbul ignore if */
              if (process.env.NODE_ENV !== 'production' &&
                typeof console !== 'undefined' &&
                !bailed
              ) {
                bailed = true;
                console.warn('Parent: ', elm);
                console.warn('Mismatching childNodes vs. VNodes: ', elm.childNodes, children);
              }
              return false
            }
          }
        }
      }
      if (isDef(data)) {
        for (var key in data) {
          if (!isRenderedModule(key)) {
            invokeCreateHooks(vnode, insertedVnodeQueue);
            break
          }
        }
      }
    } else if (elm.data !== vnode.text) {
      elm.data = vnode.text;
    }
    return true
  }

  function assertNodeMatch (node, vnode) {
    if (isDef(vnode.tag)) {
      return (
        vnode.tag.indexOf('vue-component') === 0 ||
        vnode.tag.toLowerCase() === (node.tagName && node.tagName.toLowerCase())
      )
    } else {
      return node.nodeType === (vnode.isComment ? 8 : 3)
    }
  }

  return function patch (oldVnode, vnode, hydrating, removeOnly, parentElm, refElm) {
    if (isUndef(vnode)) {
      if (isDef(oldVnode)) { invokeDestroyHook(oldVnode); }
      return
    }

    var isInitialPatch = false;
    var insertedVnodeQueue = [];

    if (isUndef(oldVnode)) {
      // empty mount (likely as component), create new root element
      isInitialPatch = true;
      createElm(vnode, insertedVnodeQueue, parentElm, refElm);
    } else {
      var isRealElement = isDef(oldVnode.nodeType);
      if (!isRealElement && sameVnode(oldVnode, vnode)) {
        // patch existing root node
        patchVnode(oldVnode, vnode, insertedVnodeQueue, removeOnly);
      } else {
        if (isRealElement) {
          // mounting to a real element
          // check if this is server-rendered content and if we can perform
          // a successful hydration.
          if (oldVnode.nodeType === 1 && oldVnode.hasAttribute(SSR_ATTR)) {
            oldVnode.removeAttribute(SSR_ATTR);
            hydrating = true;
          }
          if (isTrue(hydrating)) {
            if (hydrate(oldVnode, vnode, insertedVnodeQueue)) {
              invokeInsertHook(vnode, insertedVnodeQueue, true);
              return oldVnode
            } else if (process.env.NODE_ENV !== 'production') {
              warn(
                'The client-side rendered virtual DOM tree is not matching ' +
                'server-rendered content. This is likely caused by incorrect ' +
                'HTML markup, for example nesting block-level elements inside ' +
                '<p>, or missing <tbody>. Bailing hydration and performing ' +
                'full client-side render.'
              );
            }
          }
          // either not server-rendered, or hydration failed.
          // create an empty node and replace it
          oldVnode = emptyNodeAt(oldVnode);
        }
        // replacing existing element
        var oldElm = oldVnode.elm;
        var parentElm$1 = nodeOps.parentNode(oldElm);
        createElm(
          vnode,
          insertedVnodeQueue,
          // extremely rare edge case: do not insert if old element is in a
          // leaving transition. Only happens when combining transition +
          // keep-alive + HOCs. (#4590)
          oldElm._leaveCb ? null : parentElm$1,
          nodeOps.nextSibling(oldElm)
        );

        if (isDef(vnode.parent)) {
          // component root element replaced.
          // update parent placeholder node element, recursively
          var ancestor = vnode.parent;
          var patchable = isPatchable(vnode);
          while (ancestor) {
            for (var i = 0; i < cbs.destroy.length; ++i) {
              cbs.destroy[i](ancestor);
            }
            ancestor.elm = vnode.elm;
            if (patchable) {
              for (var i$1 = 0; i$1 < cbs.create.length; ++i$1) {
                cbs.create[i$1](emptyNode, ancestor);
              }
              // #6513
              // invoke insert hooks that may have been merged by create hooks.
              // e.g. for directives that uses the "inserted" hook.
              var insert = ancestor.data.hook.insert;
              if (insert.merged) {
                // start at index 1 to avoid re-invoking component mounted hook
                for (var i$2 = 1; i$2 < insert.fns.length; i$2++) {
                  insert.fns[i$2]();
                }
              }
            }
            ancestor = ancestor.parent;
          }
        }

        if (isDef(parentElm$1)) {
          removeVnodes(parentElm$1, [oldVnode], 0, 0);
        } else if (isDef(oldVnode.tag)) {
          invokeDestroyHook(oldVnode);
        }
      }
    }

    invokeInsertHook(vnode, insertedVnodeQueue, isInitialPatch);
    return vnode.elm
  }
}

/*  */

var directives = {
  create: updateDirectives,
  update: updateDirectives,
  destroy: function unbindDirectives (vnode) {
    updateDirectives(vnode, emptyNode);
  }
};

function updateDirectives (oldVnode, vnode) {
  if (oldVnode.data.directives || vnode.data.directives) {
    _update(oldVnode, vnode);
  }
}

function _update (oldVnode, vnode) {
  var isCreate = oldVnode === emptyNode;
  var isDestroy = vnode === emptyNode;
  var oldDirs = normalizeDirectives$1(oldVnode.data.directives, oldVnode.context);
  var newDirs = normalizeDirectives$1(vnode.data.directives, vnode.context);

  var dirsWithInsert = [];
  var dirsWithPostpatch = [];

  var key, oldDir, dir;
  for (key in newDirs) {
    oldDir = oldDirs[key];
    dir = newDirs[key];
    if (!oldDir) {
      // new directive, bind
      callHook$1(dir, 'bind', vnode, oldVnode);
      if (dir.def && dir.def.inserted) {
        dirsWithInsert.push(dir);
      }
    } else {
      // existing directive, update
      dir.oldValue = oldDir.value;
      callHook$1(dir, 'update', vnode, oldVnode);
      if (dir.def && dir.def.componentUpdated) {
        dirsWithPostpatch.push(dir);
      }
    }
  }

  if (dirsWithInsert.length) {
    var callInsert = function () {
      for (var i = 0; i < dirsWithInsert.length; i++) {
        callHook$1(dirsWithInsert[i], 'inserted', vnode, oldVnode);
      }
    };
    if (isCreate) {
      mergeVNodeHook(vnode.data.hook || (vnode.data.hook = {}), 'insert', callInsert);
    } else {
      callInsert();
    }
  }

  if (dirsWithPostpatch.length) {
    mergeVNodeHook(vnode.data.hook || (vnode.data.hook = {}), 'postpatch', function () {
      for (var i = 0; i < dirsWithPostpatch.length; i++) {
        callHook$1(dirsWithPostpatch[i], 'componentUpdated', vnode, oldVnode);
      }
    });
  }

  if (!isCreate) {
    for (key in oldDirs) {
      if (!newDirs[key]) {
        // no longer present, unbind
        callHook$1(oldDirs[key], 'unbind', oldVnode, oldVnode, isDestroy);
      }
    }
  }
}

var emptyModifiers = Object.create(null);

function normalizeDirectives$1 (
  dirs,
  vm
) {
  var res = Object.create(null);
  if (!dirs) {
    return res
  }
  var i, dir;
  for (i = 0; i < dirs.length; i++) {
    dir = dirs[i];
    if (!dir.modifiers) {
      dir.modifiers = emptyModifiers;
    }
    res[getRawDirName(dir)] = dir;
    dir.def = resolveAsset(vm.$options, 'directives', dir.name, true);
  }
  return res
}

function getRawDirName (dir) {
  return dir.rawName || ((dir.name) + "." + (Object.keys(dir.modifiers || {}).join('.')))
}

function callHook$1 (dir, hook, vnode, oldVnode, isDestroy) {
  var fn = dir.def && dir.def[hook];
  if (fn) {
    try {
      fn(vnode.elm, dir, vnode, oldVnode, isDestroy);
    } catch (e) {
      handleError(e, vnode.context, ("directive " + (dir.name) + " " + hook + " hook"));
    }
  }
}

var baseModules = [
  ref,
  directives
];

/*  */

function updateAttrs (oldVnode, vnode) {
  var opts = vnode.componentOptions;
  if (isDef(opts) && opts.Ctor.options.inheritAttrs === false) {
    return
  }
  if (isUndef(oldVnode.data.attrs) && isUndef(vnode.data.attrs)) {
    return
  }
  var key, cur, old;
  var elm = vnode.elm;
  var oldAttrs = oldVnode.data.attrs || {};
  var attrs = vnode.data.attrs || {};
  // clone observed objects, as the user probably wants to mutate it
  if (isDef(attrs.__ob__)) {
    attrs = vnode.data.attrs = extend({}, attrs);
  }

  for (key in attrs) {
    cur = attrs[key];
    old = oldAttrs[key];
    if (old !== cur) {
      setAttr(elm, key, cur);
    }
  }
  // #4391: in IE9, setting type can reset value for input[type=radio]
  /* istanbul ignore if */
  if (isIE9 && attrs.value !== oldAttrs.value) {
    setAttr(elm, 'value', attrs.value);
  }
  for (key in oldAttrs) {
    if (isUndef(attrs[key])) {
      if (isXlink(key)) {
        elm.removeAttributeNS(xlinkNS, getXlinkProp(key));
      } else if (!isEnumeratedAttr(key)) {
        elm.removeAttribute(key);
      }
    }
  }
}

function setAttr (el, key, value) {
  if (isBooleanAttr(key)) {
    // set attribute for blank value
    // e.g. <option disabled>Select one</option>
    if (isFalsyAttrValue(value)) {
      el.removeAttribute(key);
    } else {
      // technically allowfullscreen is a boolean attribute for <iframe>,
      // but Flash expects a value of "true" when used on <embed> tag
      value = key === 'allowfullscreen' && el.tagName === 'EMBED'
        ? 'true'
        : key;
      el.setAttribute(key, value);
    }
  } else if (isEnumeratedAttr(key)) {
    el.setAttribute(key, isFalsyAttrValue(value) || value === 'false' ? 'false' : 'true');
  } else if (isXlink(key)) {
    if (isFalsyAttrValue(value)) {
      el.removeAttributeNS(xlinkNS, getXlinkProp(key));
    } else {
      el.setAttributeNS(xlinkNS, key, value);
    }
  } else {
    if (isFalsyAttrValue(value)) {
      el.removeAttribute(key);
    } else {
      el.setAttribute(key, value);
    }
  }
}

var attrs = {
  create: updateAttrs,
  update: updateAttrs
};

/*  */

function updateClass (oldVnode, vnode) {
  var el = vnode.elm;
  var data = vnode.data;
  var oldData = oldVnode.data;
  if (
    isUndef(data.staticClass) &&
    isUndef(data.class) && (
      isUndef(oldData) || (
        isUndef(oldData.staticClass) &&
        isUndef(oldData.class)
      )
    )
  ) {
    return
  }

  var cls = genClassForVnode(vnode);

  // handle transition classes
  var transitionClass = el._transitionClasses;
  if (isDef(transitionClass)) {
    cls = concat(cls, stringifyClass(transitionClass));
  }

  // set the class
  if (cls !== el._prevClass) {
    el.setAttribute('class', cls);
    el._prevClass = cls;
  }
}

var klass = {
  create: updateClass,
  update: updateClass
};

/*  */

var validDivisionCharRE = /[\w).+\-_$\]]/;

function parseFilters (exp) {
  var inSingle = false;
  var inDouble = false;
  var inTemplateString = false;
  var inRegex = false;
  var curly = 0;
  var square = 0;
  var paren = 0;
  var lastFilterIndex = 0;
  var c, prev, i, expression, filters;

  for (i = 0; i < exp.length; i++) {
    prev = c;
    c = exp.charCodeAt(i);
    if (inSingle) {
      if (c === 0x27 && prev !== 0x5C) { inSingle = false; }
    } else if (inDouble) {
      if (c === 0x22 && prev !== 0x5C) { inDouble = false; }
    } else if (inTemplateString) {
      if (c === 0x60 && prev !== 0x5C) { inTemplateString = false; }
    } else if (inRegex) {
      if (c === 0x2f && prev !== 0x5C) { inRegex = false; }
    } else if (
      c === 0x7C && // pipe
      exp.charCodeAt(i + 1) !== 0x7C &&
      exp.charCodeAt(i - 1) !== 0x7C &&
      !curly && !square && !paren
    ) {
      if (expression === undefined) {
        // first filter, end of expression
        lastFilterIndex = i + 1;
        expression = exp.slice(0, i).trim();
      } else {
        pushFilter();
      }
    } else {
      switch (c) {
        case 0x22: inDouble = true; break         // "
        case 0x27: inSingle = true; break         // '
        case 0x60: inTemplateString = true; break // `
        case 0x28: paren++; break                 // (
        case 0x29: paren--; break                 // )
        case 0x5B: square++; break                // [
        case 0x5D: square--; break                // ]
        case 0x7B: curly++; break                 // {
        case 0x7D: curly--; break                 // }
      }
      if (c === 0x2f) { // /
        var j = i - 1;
        var p = (void 0);
        // find first non-whitespace prev char
        for (; j >= 0; j--) {
          p = exp.charAt(j);
          if (p !== ' ') { break }
        }
        if (!p || !validDivisionCharRE.test(p)) {
          inRegex = true;
        }
      }
    }
  }

  if (expression === undefined) {
    expression = exp.slice(0, i).trim();
  } else if (lastFilterIndex !== 0) {
    pushFilter();
  }

  function pushFilter () {
    (filters || (filters = [])).push(exp.slice(lastFilterIndex, i).trim());
    lastFilterIndex = i + 1;
  }

  if (filters) {
    for (i = 0; i < filters.length; i++) {
      expression = wrapFilter(expression, filters[i]);
    }
  }

  return expression
}

function wrapFilter (exp, filter) {
  var i = filter.indexOf('(');
  if (i < 0) {
    // _f: resolveFilter
    return ("_f(\"" + filter + "\")(" + exp + ")")
  } else {
    var name = filter.slice(0, i);
    var args = filter.slice(i + 1);
    return ("_f(\"" + name + "\")(" + exp + "," + args)
  }
}

/*  */

function baseWarn (msg) {
  console.error(("[Vue compiler]: " + msg));
}

function pluckModuleFunction (
  modules,
  key
) {
  return modules
    ? modules.map(function (m) { return m[key]; }).filter(function (_) { return _; })
    : []
}

function addProp (el, name, value) {
  (el.props || (el.props = [])).push({ name: name, value: value });
}

function addAttr (el, name, value) {
  (el.attrs || (el.attrs = [])).push({ name: name, value: value });
}

function addDirective (
  el,
  name,
  rawName,
  value,
  arg,
  modifiers
) {
  (el.directives || (el.directives = [])).push({ name: name, rawName: rawName, value: value, arg: arg, modifiers: modifiers });
}

function addHandler (
  el,
  name,
  value,
  modifiers,
  important,
  warn
) {
  // warn prevent and passive modifier
  /* istanbul ignore if */
  if (
    process.env.NODE_ENV !== 'production' && warn &&
    modifiers && modifiers.prevent && modifiers.passive
  ) {
    warn(
      'passive and prevent can\'t be used together. ' +
      'Passive handler can\'t prevent default event.'
    );
  }
  // check capture modifier
  if (modifiers && modifiers.capture) {
    delete modifiers.capture;
    name = '!' + name; // mark the event as captured
  }
  if (modifiers && modifiers.once) {
    delete modifiers.once;
    name = '~' + name; // mark the event as once
  }
  /* istanbul ignore if */
  if (modifiers && modifiers.passive) {
    delete modifiers.passive;
    name = '&' + name; // mark the event as passive
  }
  var events;
  if (modifiers && modifiers.native) {
    delete modifiers.native;
    events = el.nativeEvents || (el.nativeEvents = {});
  } else {
    events = el.events || (el.events = {});
  }
  var newHandler = { value: value, modifiers: modifiers };
  var handlers = events[name];
  /* istanbul ignore if */
  if (Array.isArray(handlers)) {
    important ? handlers.unshift(newHandler) : handlers.push(newHandler);
  } else if (handlers) {
    events[name] = important ? [newHandler, handlers] : [handlers, newHandler];
  } else {
    events[name] = newHandler;
  }
}

function getBindingAttr (
  el,
  name,
  getStatic
) {
  var dynamicValue =
    getAndRemoveAttr(el, ':' + name) ||
    getAndRemoveAttr(el, 'v-bind:' + name);
  if (dynamicValue != null) {
    return parseFilters(dynamicValue)
  } else if (getStatic !== false) {
    var staticValue = getAndRemoveAttr(el, name);
    if (staticValue != null) {
      return JSON.stringify(staticValue)
    }
  }
}

function getAndRemoveAttr (el, name) {
  var val;
  if ((val = el.attrsMap[name]) != null) {
    var list = el.attrsList;
    for (var i = 0, l = list.length; i < l; i++) {
      if (list[i].name === name) {
        list.splice(i, 1);
        break
      }
    }
  }
  return val
}

/*  */

/**
 * Cross-platform code generation for component v-model
 */
function genComponentModel (
  el,
  value,
  modifiers
) {
  var ref = modifiers || {};
  var number = ref.number;
  var trim = ref.trim;

  var baseValueExpression = '$$v';
  var valueExpression = baseValueExpression;
  if (trim) {
    valueExpression =
      "(typeof " + baseValueExpression + " === 'string'" +
        "? " + baseValueExpression + ".trim()" +
        ": " + baseValueExpression + ")";
  }
  if (number) {
    valueExpression = "_n(" + valueExpression + ")";
  }
  var assignment = genAssignmentCode(value, valueExpression);

  el.model = {
    value: ("(" + value + ")"),
    expression: ("\"" + value + "\""),
    callback: ("function (" + baseValueExpression + ") {" + assignment + "}")
  };
}

/**
 * Cross-platform codegen helper for generating v-model value assignment code.
 */
function genAssignmentCode (
  value,
  assignment
) {
  var modelRs = parseModel(value);
  if (modelRs.idx === null) {
    return (value + "=" + assignment)
  } else {
    return ("$set(" + (modelRs.exp) + ", " + (modelRs.idx) + ", " + assignment + ")")
  }
}

/**
 * parse directive model to do the array update transform. a[idx] = val => $$a.splice($$idx, 1, val)
 *
 * for loop possible cases:
 *
 * - test
 * - test[idx]
 * - test[test1[idx]]
 * - test["a"][idx]
 * - xxx.test[a[a].test1[idx]]
 * - test.xxx.a["asa"][test1[idx]]
 *
 */

var len;
var str;
var chr;
var index$1;
var expressionPos;
var expressionEndPos;

function parseModel (val) {
  str = val;
  len = str.length;
  index$1 = expressionPos = expressionEndPos = 0;

  if (val.indexOf('[') < 0 || val.lastIndexOf(']') < len - 1) {
    return {
      exp: val,
      idx: null
    }
  }

  while (!eof()) {
    chr = next();
    /* istanbul ignore if */
    if (isStringStart(chr)) {
      parseString(chr);
    } else if (chr === 0x5B) {
      parseBracket(chr);
    }
  }

  return {
    exp: val.substring(0, expressionPos),
    idx: val.substring(expressionPos + 1, expressionEndPos)
  }
}

function next () {
  return str.charCodeAt(++index$1)
}

function eof () {
  return index$1 >= len
}

function isStringStart (chr) {
  return chr === 0x22 || chr === 0x27
}

function parseBracket (chr) {
  var inBracket = 1;
  expressionPos = index$1;
  while (!eof()) {
    chr = next();
    if (isStringStart(chr)) {
      parseString(chr);
      continue
    }
    if (chr === 0x5B) { inBracket++; }
    if (chr === 0x5D) { inBracket--; }
    if (inBracket === 0) {
      expressionEndPos = index$1;
      break
    }
  }
}

function parseString (chr) {
  var stringQuote = chr;
  while (!eof()) {
    chr = next();
    if (chr === stringQuote) {
      break
    }
  }
}

/*  */

var warn$1;

// in some cases, the event used has to be determined at runtime
// so we used some reserved tokens during compile.
var RANGE_TOKEN = '__r';
var CHECKBOX_RADIO_TOKEN = '__c';

function model (
  el,
  dir,
  _warn
) {
  warn$1 = _warn;
  var value = dir.value;
  var modifiers = dir.modifiers;
  var tag = el.tag;
  var type = el.attrsMap.type;

  if (process.env.NODE_ENV !== 'production') {
    var dynamicType = el.attrsMap['v-bind:type'] || el.attrsMap[':type'];
    if (tag === 'input' && dynamicType) {
      warn$1(
        "<input :type=\"" + dynamicType + "\" v-model=\"" + value + "\">:\n" +
        "v-model does not support dynamic input types. Use v-if branches instead."
      );
    }
    // inputs with type="file" are read only and setting the input's
    // value will throw an error.
    if (tag === 'input' && type === 'file') {
      warn$1(
        "<" + (el.tag) + " v-model=\"" + value + "\" type=\"file\">:\n" +
        "File inputs are read only. Use a v-on:change listener instead."
      );
    }
  }

  if (el.component) {
    genComponentModel(el, value, modifiers);
    // component v-model doesn't need extra runtime
    return false
  } else if (tag === 'select') {
    genSelect(el, value, modifiers);
  } else if (tag === 'input' && type === 'checkbox') {
    genCheckboxModel(el, value, modifiers);
  } else if (tag === 'input' && type === 'radio') {
    genRadioModel(el, value, modifiers);
  } else if (tag === 'input' || tag === 'textarea') {
    genDefaultModel(el, value, modifiers);
  } else if (!config.isReservedTag(tag)) {
    genComponentModel(el, value, modifiers);
    // component v-model doesn't need extra runtime
    return false
  } else if (process.env.NODE_ENV !== 'production') {
    warn$1(
      "<" + (el.tag) + " v-model=\"" + value + "\">: " +
      "v-model is not supported on this element type. " +
      'If you are working with contenteditable, it\'s recommended to ' +
      'wrap a library dedicated for that purpose inside a custom component.'
    );
  }

  // ensure runtime directive metadata
  return true
}

function genCheckboxModel (
  el,
  value,
  modifiers
) {
  var number = modifiers && modifiers.number;
  var valueBinding = getBindingAttr(el, 'value') || 'null';
  var trueValueBinding = getBindingAttr(el, 'true-value') || 'true';
  var falseValueBinding = getBindingAttr(el, 'false-value') || 'false';
  addProp(el, 'checked',
    "Array.isArray(" + value + ")" +
      "?_i(" + value + "," + valueBinding + ")>-1" + (
        trueValueBinding === 'true'
          ? (":(" + value + ")")
          : (":_q(" + value + "," + trueValueBinding + ")")
      )
  );
  addHandler(el, CHECKBOX_RADIO_TOKEN,
    "var $$a=" + value + "," +
        '$$el=$event.target,' +
        "$$c=$$el.checked?(" + trueValueBinding + "):(" + falseValueBinding + ");" +
    'if(Array.isArray($$a)){' +
      "var $$v=" + (number ? '_n(' + valueBinding + ')' : valueBinding) + "," +
          '$$i=_i($$a,$$v);' +
      "if($$el.checked){$$i<0&&(" + value + "=$$a.concat([$$v]))}" +
      "else{$$i>-1&&(" + value + "=$$a.slice(0,$$i).concat($$a.slice($$i+1)))}" +
    "}else{" + (genAssignmentCode(value, '$$c')) + "}",
    null, true
  );
}

function genRadioModel (
    el,
    value,
    modifiers
) {
  var number = modifiers && modifiers.number;
  var valueBinding = getBindingAttr(el, 'value') || 'null';
  valueBinding = number ? ("_n(" + valueBinding + ")") : valueBinding;
  addProp(el, 'checked', ("_q(" + value + "," + valueBinding + ")"));
  addHandler(el, CHECKBOX_RADIO_TOKEN, genAssignmentCode(value, valueBinding), null, true);
}

function genSelect (
    el,
    value,
    modifiers
) {
  var number = modifiers && modifiers.number;
  var selectedVal = "Array.prototype.filter" +
    ".call($event.target.options,function(o){return o.selected})" +
    ".map(function(o){var val = \"_value\" in o ? o._value : o.value;" +
    "return " + (number ? '_n(val)' : 'val') + "})";

  var assignment = '$event.target.multiple ? $$selectedVal : $$selectedVal[0]';
  var code = "var $$selectedVal = " + selectedVal + ";";
  code = code + " " + (genAssignmentCode(value, assignment));
  addHandler(el, 'change', code, null, true);
}

function genDefaultModel (
  el,
  value,
  modifiers
) {
  var type = el.attrsMap.type;
  var ref = modifiers || {};
  var lazy = ref.lazy;
  var number = ref.number;
  var trim = ref.trim;
  var needCompositionGuard = !lazy && type !== 'range';
  var event = lazy
    ? 'change'
    : type === 'range'
      ? RANGE_TOKEN
      : 'input';

  var valueExpression = '$event.target.value';
  if (trim) {
    valueExpression = "$event.target.value.trim()";
  }
  if (number) {
    valueExpression = "_n(" + valueExpression + ")";
  }

  var code = genAssignmentCode(value, valueExpression);
  if (needCompositionGuard) {
    code = "if($event.target.composing)return;" + code;
  }

  addProp(el, 'value', ("(" + value + ")"));
  addHandler(el, event, code, null, true);
  if (trim || number) {
    addHandler(el, 'blur', '$forceUpdate()');
  }
}

/*  */

// normalize v-model event tokens that can only be determined at runtime.
// it's important to place the event as the first in the array because
// the whole point is ensuring the v-model callback gets called before
// user-attached handlers.
function normalizeEvents (on) {
  var event;
  /* istanbul ignore if */
  if (isDef(on[RANGE_TOKEN])) {
    // IE input[type=range] only supports `change` event
    event = isIE ? 'change' : 'input';
    on[event] = [].concat(on[RANGE_TOKEN], on[event] || []);
    delete on[RANGE_TOKEN];
  }
  if (isDef(on[CHECKBOX_RADIO_TOKEN])) {
    // Chrome fires microtasks in between click/change, leads to #4521
    event = isChrome ? 'click' : 'change';
    on[event] = [].concat(on[CHECKBOX_RADIO_TOKEN], on[event] || []);
    delete on[CHECKBOX_RADIO_TOKEN];
  }
}

var target$1;

function add$1 (
  event,
  handler,
  once$$1,
  capture,
  passive
) {
  if (once$$1) {
    var oldHandler = handler;
    var _target = target$1; // save current target element in closure
    handler = function (ev) {
      var res = arguments.length === 1
        ? oldHandler(ev)
        : oldHandler.apply(null, arguments);
      if (res !== null) {
        remove$2(event, handler, capture, _target);
      }
    };
  }
  target$1.addEventListener(
    event,
    handler,
    supportsPassive
      ? { capture: capture, passive: passive }
      : capture
  );
}

function remove$2 (
  event,
  handler,
  capture,
  _target
) {
  (_target || target$1).removeEventListener(event, handler, capture);
}

function updateDOMListeners (oldVnode, vnode) {
  if (isUndef(oldVnode.data.on) && isUndef(vnode.data.on)) {
    return
  }
  var on = vnode.data.on || {};
  var oldOn = oldVnode.data.on || {};
  target$1 = vnode.elm;
  normalizeEvents(on);
  updateListeners(on, oldOn, add$1, remove$2, vnode.context);
}

var events = {
  create: updateDOMListeners,
  update: updateDOMListeners
};

/*  */

function updateDOMProps (oldVnode, vnode) {
  if (isUndef(oldVnode.data.domProps) && isUndef(vnode.data.domProps)) {
    return
  }
  var key, cur;
  var elm = vnode.elm;
  var oldProps = oldVnode.data.domProps || {};
  var props = vnode.data.domProps || {};
  // clone observed objects, as the user probably wants to mutate it
  if (isDef(props.__ob__)) {
    props = vnode.data.domProps = extend({}, props);
  }

  for (key in oldProps) {
    if (isUndef(props[key])) {
      elm[key] = '';
    }
  }
  for (key in props) {
    cur = props[key];
    // ignore children if the node has textContent or innerHTML,
    // as these will throw away existing DOM nodes and cause removal errors
    // on subsequent patches (#3360)
    if (key === 'textContent' || key === 'innerHTML') {
      if (vnode.children) { vnode.children.length = 0; }
      if (cur === oldProps[key]) { continue }
    }

    if (key === 'value') {
      // store value as _value as well since
      // non-string values will be stringified
      elm._value = cur;
      // avoid resetting cursor position when value is the same
      var strCur = isUndef(cur) ? '' : String(cur);
      if (shouldUpdateValue(elm, vnode, strCur)) {
        elm.value = strCur;
      }
    } else {
      elm[key] = cur;
    }
  }
}

// check platforms/web/util/attrs.js acceptValue


function shouldUpdateValue (
  elm,
  vnode,
  checkVal
) {
  return (!elm.composing && (
    vnode.tag === 'option' ||
    isDirty(elm, checkVal) ||
    isInputChanged(elm, checkVal)
  ))
}

function isDirty (elm, checkVal) {
  // return true when textbox (.number and .trim) loses focus and its value is
  // not equal to the updated value
  var notInFocus = true;
  // #6157
  // work around IE bug when accessing document.activeElement in an iframe
  try { notInFocus = document.activeElement !== elm; } catch (e) {}
  return notInFocus && elm.value !== checkVal
}

function isInputChanged (elm, newVal) {
  var value = elm.value;
  var modifiers = elm._vModifiers; // injected by v-model runtime
  if (isDef(modifiers) && modifiers.number) {
    return toNumber(value) !== toNumber(newVal)
  }
  if (isDef(modifiers) && modifiers.trim) {
    return value.trim() !== newVal.trim()
  }
  return value !== newVal
}

var domProps = {
  create: updateDOMProps,
  update: updateDOMProps
};

/*  */

var parseStyleText = cached(function (cssText) {
  var res = {};
  var listDelimiter = /;(?![^(]*\))/g;
  var propertyDelimiter = /:(.+)/;
  cssText.split(listDelimiter).forEach(function (item) {
    if (item) {
      var tmp = item.split(propertyDelimiter);
      tmp.length > 1 && (res[tmp[0].trim()] = tmp[1].trim());
    }
  });
  return res
});

// merge static and dynamic style data on the same vnode
function normalizeStyleData (data) {
  var style = normalizeStyleBinding(data.style);
  // static style is pre-processed into an object during compilation
  // and is always a fresh object, so it's safe to merge into it
  return data.staticStyle
    ? extend(data.staticStyle, style)
    : style
}

// normalize possible array / string values into Object
function normalizeStyleBinding (bindingStyle) {
  if (Array.isArray(bindingStyle)) {
    return toObject(bindingStyle)
  }
  if (typeof bindingStyle === 'string') {
    return parseStyleText(bindingStyle)
  }
  return bindingStyle
}

/**
 * parent component style should be after child's
 * so that parent component's style could override it
 */
function getStyle (vnode, checkChild) {
  var res = {};
  var styleData;

  if (checkChild) {
    var childNode = vnode;
    while (childNode.componentInstance) {
      childNode = childNode.componentInstance._vnode;
      if (childNode.data && (styleData = normalizeStyleData(childNode.data))) {
        extend(res, styleData);
      }
    }
  }

  if ((styleData = normalizeStyleData(vnode.data))) {
    extend(res, styleData);
  }

  var parentNode = vnode;
  while ((parentNode = parentNode.parent)) {
    if (parentNode.data && (styleData = normalizeStyleData(parentNode.data))) {
      extend(res, styleData);
    }
  }
  return res
}

/*  */

var cssVarRE = /^--/;
var importantRE = /\s*!important$/;
var setProp = function (el, name, val) {
  /* istanbul ignore if */
  if (cssVarRE.test(name)) {
    el.style.setProperty(name, val);
  } else if (importantRE.test(val)) {
    el.style.setProperty(name, val.replace(importantRE, ''), 'important');
  } else {
    var normalizedName = normalize(name);
    if (Array.isArray(val)) {
      // Support values array created by autoprefixer, e.g.
      // {display: ["-webkit-box", "-ms-flexbox", "flex"]}
      // Set them one by one, and the browser will only set those it can recognize
      for (var i = 0, len = val.length; i < len; i++) {
        el.style[normalizedName] = val[i];
      }
    } else {
      el.style[normalizedName] = val;
    }
  }
};

var vendorNames = ['Webkit', 'Moz', 'ms'];

var emptyStyle;
var normalize = cached(function (prop) {
  emptyStyle = emptyStyle || document.createElement('div').style;
  prop = camelize(prop);
  if (prop !== 'filter' && (prop in emptyStyle)) {
    return prop
  }
  var capName = prop.charAt(0).toUpperCase() + prop.slice(1);
  for (var i = 0; i < vendorNames.length; i++) {
    var name = vendorNames[i] + capName;
    if (name in emptyStyle) {
      return name
    }
  }
});

function updateStyle (oldVnode, vnode) {
  var data = vnode.data;
  var oldData = oldVnode.data;

  if (isUndef(data.staticStyle) && isUndef(data.style) &&
    isUndef(oldData.staticStyle) && isUndef(oldData.style)
  ) {
    return
  }

  var cur, name;
  var el = vnode.elm;
  var oldStaticStyle = oldData.staticStyle;
  var oldStyleBinding = oldData.normalizedStyle || oldData.style || {};

  // if static style exists, stylebinding already merged into it when doing normalizeStyleData
  var oldStyle = oldStaticStyle || oldStyleBinding;

  var style = normalizeStyleBinding(vnode.data.style) || {};

  // store normalized style under a different key for next diff
  // make sure to clone it if it's reactive, since the user likely wants
  // to mutate it.
  vnode.data.normalizedStyle = isDef(style.__ob__)
    ? extend({}, style)
    : style;

  var newStyle = getStyle(vnode, true);

  for (name in oldStyle) {
    if (isUndef(newStyle[name])) {
      setProp(el, name, '');
    }
  }
  for (name in newStyle) {
    cur = newStyle[name];
    if (cur !== oldStyle[name]) {
      // ie9 setting to null has no effect, must use empty string
      setProp(el, name, cur == null ? '' : cur);
    }
  }
}

var style = {
  create: updateStyle,
  update: updateStyle
};

/*  */

/**
 * Add class with compatibility for SVG since classList is not supported on
 * SVG elements in IE
 */
function addClass (el, cls) {
  /* istanbul ignore if */
  if (!cls || !(cls = cls.trim())) {
    return
  }

  /* istanbul ignore else */
  if (el.classList) {
    if (cls.indexOf(' ') > -1) {
      cls.split(/\s+/).forEach(function (c) { return el.classList.add(c); });
    } else {
      el.classList.add(cls);
    }
  } else {
    var cur = " " + (el.getAttribute('class') || '') + " ";
    if (cur.indexOf(' ' + cls + ' ') < 0) {
      el.setAttribute('class', (cur + cls).trim());
    }
  }
}

/**
 * Remove class with compatibility for SVG since classList is not supported on
 * SVG elements in IE
 */
function removeClass (el, cls) {
  /* istanbul ignore if */
  if (!cls || !(cls = cls.trim())) {
    return
  }

  /* istanbul ignore else */
  if (el.classList) {
    if (cls.indexOf(' ') > -1) {
      cls.split(/\s+/).forEach(function (c) { return el.classList.remove(c); });
    } else {
      el.classList.remove(cls);
    }
    if (!el.classList.length) {
      el.removeAttribute('class');
    }
  } else {
    var cur = " " + (el.getAttribute('class') || '') + " ";
    var tar = ' ' + cls + ' ';
    while (cur.indexOf(tar) >= 0) {
      cur = cur.replace(tar, ' ');
    }
    cur = cur.trim();
    if (cur) {
      el.setAttribute('class', cur);
    } else {
      el.removeAttribute('class');
    }
  }
}

/*  */

function resolveTransition (def$$1) {
  if (!def$$1) {
    return
  }
  /* istanbul ignore else */
  if (typeof def$$1 === 'object') {
    var res = {};
    if (def$$1.css !== false) {
      extend(res, autoCssTransition(def$$1.name || 'v'));
    }
    extend(res, def$$1);
    return res
  } else if (typeof def$$1 === 'string') {
    return autoCssTransition(def$$1)
  }
}

var autoCssTransition = cached(function (name) {
  return {
    enterClass: (name + "-enter"),
    enterToClass: (name + "-enter-to"),
    enterActiveClass: (name + "-enter-active"),
    leaveClass: (name + "-leave"),
    leaveToClass: (name + "-leave-to"),
    leaveActiveClass: (name + "-leave-active")
  }
});

var hasTransition = inBrowser && !isIE9;
var TRANSITION = 'transition';
var ANIMATION = 'animation';

// Transition property/event sniffing
var transitionProp = 'transition';
var transitionEndEvent = 'transitionend';
var animationProp = 'animation';
var animationEndEvent = 'animationend';
if (hasTransition) {
  /* istanbul ignore if */
  if (window.ontransitionend === undefined &&
    window.onwebkittransitionend !== undefined
  ) {
    transitionProp = 'WebkitTransition';
    transitionEndEvent = 'webkitTransitionEnd';
  }
  if (window.onanimationend === undefined &&
    window.onwebkitanimationend !== undefined
  ) {
    animationProp = 'WebkitAnimation';
    animationEndEvent = 'webkitAnimationEnd';
  }
}

// binding to window is necessary to make hot reload work in IE in strict mode
var raf = inBrowser && window.requestAnimationFrame
  ? window.requestAnimationFrame.bind(window)
  : setTimeout;

function nextFrame (fn) {
  raf(function () {
    raf(fn);
  });
}

function addTransitionClass (el, cls) {
  var transitionClasses = el._transitionClasses || (el._transitionClasses = []);
  if (transitionClasses.indexOf(cls) < 0) {
    transitionClasses.push(cls);
    addClass(el, cls);
  }
}

function removeTransitionClass (el, cls) {
  if (el._transitionClasses) {
    remove(el._transitionClasses, cls);
  }
  removeClass(el, cls);
}

function whenTransitionEnds (
  el,
  expectedType,
  cb
) {
  var ref = getTransitionInfo(el, expectedType);
  var type = ref.type;
  var timeout = ref.timeout;
  var propCount = ref.propCount;
  if (!type) { return cb() }
  var event = type === TRANSITION ? transitionEndEvent : animationEndEvent;
  var ended = 0;
  var end = function () {
    el.removeEventListener(event, onEnd);
    cb();
  };
  var onEnd = function (e) {
    if (e.target === el) {
      if (++ended >= propCount) {
        end();
      }
    }
  };
  setTimeout(function () {
    if (ended < propCount) {
      end();
    }
  }, timeout + 1);
  el.addEventListener(event, onEnd);
}

var transformRE = /\b(transform|all)(,|$)/;

function getTransitionInfo (el, expectedType) {
  var styles = window.getComputedStyle(el);
  var transitionDelays = styles[transitionProp + 'Delay'].split(', ');
  var transitionDurations = styles[transitionProp + 'Duration'].split(', ');
  var transitionTimeout = getTimeout(transitionDelays, transitionDurations);
  var animationDelays = styles[animationProp + 'Delay'].split(', ');
  var animationDurations = styles[animationProp + 'Duration'].split(', ');
  var animationTimeout = getTimeout(animationDelays, animationDurations);

  var type;
  var timeout = 0;
  var propCount = 0;
  /* istanbul ignore if */
  if (expectedType === TRANSITION) {
    if (transitionTimeout > 0) {
      type = TRANSITION;
      timeout = transitionTimeout;
      propCount = transitionDurations.length;
    }
  } else if (expectedType === ANIMATION) {
    if (animationTimeout > 0) {
      type = ANIMATION;
      timeout = animationTimeout;
      propCount = animationDurations.length;
    }
  } else {
    timeout = Math.max(transitionTimeout, animationTimeout);
    type = timeout > 0
      ? transitionTimeout > animationTimeout
        ? TRANSITION
        : ANIMATION
      : null;
    propCount = type
      ? type === TRANSITION
        ? transitionDurations.length
        : animationDurations.length
      : 0;
  }
  var hasTransform =
    type === TRANSITION &&
    transformRE.test(styles[transitionProp + 'Property']);
  return {
    type: type,
    timeout: timeout,
    propCount: propCount,
    hasTransform: hasTransform
  }
}

function getTimeout (delays, durations) {
  /* istanbul ignore next */
  while (delays.length < durations.length) {
    delays = delays.concat(delays);
  }

  return Math.max.apply(null, durations.map(function (d, i) {
    return toMs(d) + toMs(delays[i])
  }))
}

function toMs (s) {
  return Number(s.slice(0, -1)) * 1000
}

/*  */

function enter (vnode, toggleDisplay) {
  var el = vnode.elm;

  // call leave callback now
  if (isDef(el._leaveCb)) {
    el._leaveCb.cancelled = true;
    el._leaveCb();
  }

  var data = resolveTransition(vnode.data.transition);
  if (isUndef(data)) {
    return
  }

  /* istanbul ignore if */
  if (isDef(el._enterCb) || el.nodeType !== 1) {
    return
  }

  var css = data.css;
  var type = data.type;
  var enterClass = data.enterClass;
  var enterToClass = data.enterToClass;
  var enterActiveClass = data.enterActiveClass;
  var appearClass = data.appearClass;
  var appearToClass = data.appearToClass;
  var appearActiveClass = data.appearActiveClass;
  var beforeEnter = data.beforeEnter;
  var enter = data.enter;
  var afterEnter = data.afterEnter;
  var enterCancelled = data.enterCancelled;
  var beforeAppear = data.beforeAppear;
  var appear = data.appear;
  var afterAppear = data.afterAppear;
  var appearCancelled = data.appearCancelled;
  var duration = data.duration;

  // activeInstance will always be the <transition> component managing this
  // transition. One edge case to check is when the <transition> is placed
  // as the root node of a child component. In that case we need to check
  // <transition>'s parent for appear check.
  var context = activeInstance;
  var transitionNode = activeInstance.$vnode;
  while (transitionNode && transitionNode.parent) {
    transitionNode = transitionNode.parent;
    context = transitionNode.context;
  }

  var isAppear = !context._isMounted || !vnode.isRootInsert;

  if (isAppear && !appear && appear !== '') {
    return
  }

  var startClass = isAppear && appearClass
    ? appearClass
    : enterClass;
  var activeClass = isAppear && appearActiveClass
    ? appearActiveClass
    : enterActiveClass;
  var toClass = isAppear && appearToClass
    ? appearToClass
    : enterToClass;

  var beforeEnterHook = isAppear
    ? (beforeAppear || beforeEnter)
    : beforeEnter;
  var enterHook = isAppear
    ? (typeof appear === 'function' ? appear : enter)
    : enter;
  var afterEnterHook = isAppear
    ? (afterAppear || afterEnter)
    : afterEnter;
  var enterCancelledHook = isAppear
    ? (appearCancelled || enterCancelled)
    : enterCancelled;

  var explicitEnterDuration = toNumber(
    isObject(duration)
      ? duration.enter
      : duration
  );

  if (process.env.NODE_ENV !== 'production' && explicitEnterDuration != null) {
    checkDuration(explicitEnterDuration, 'enter', vnode);
  }

  var expectsCSS = css !== false && !isIE9;
  var userWantsControl = getHookArgumentsLength(enterHook);

  var cb = el._enterCb = once(function () {
    if (expectsCSS) {
      removeTransitionClass(el, toClass);
      removeTransitionClass(el, activeClass);
    }
    if (cb.cancelled) {
      if (expectsCSS) {
        removeTransitionClass(el, startClass);
      }
      enterCancelledHook && enterCancelledHook(el);
    } else {
      afterEnterHook && afterEnterHook(el);
    }
    el._enterCb = null;
  });

  if (!vnode.data.show) {
    // remove pending leave element on enter by injecting an insert hook
    mergeVNodeHook(vnode.data.hook || (vnode.data.hook = {}), 'insert', function () {
      var parent = el.parentNode;
      var pendingNode = parent && parent._pending && parent._pending[vnode.key];
      if (pendingNode &&
        pendingNode.tag === vnode.tag &&
        pendingNode.elm._leaveCb
      ) {
        pendingNode.elm._leaveCb();
      }
      enterHook && enterHook(el, cb);
    });
  }

  // start enter transition
  beforeEnterHook && beforeEnterHook(el);
  if (expectsCSS) {
    addTransitionClass(el, startClass);
    addTransitionClass(el, activeClass);
    nextFrame(function () {
      addTransitionClass(el, toClass);
      removeTransitionClass(el, startClass);
      if (!cb.cancelled && !userWantsControl) {
        if (isValidDuration(explicitEnterDuration)) {
          setTimeout(cb, explicitEnterDuration);
        } else {
          whenTransitionEnds(el, type, cb);
        }
      }
    });
  }

  if (vnode.data.show) {
    toggleDisplay && toggleDisplay();
    enterHook && enterHook(el, cb);
  }

  if (!expectsCSS && !userWantsControl) {
    cb();
  }
}

function leave (vnode, rm) {
  var el = vnode.elm;

  // call enter callback now
  if (isDef(el._enterCb)) {
    el._enterCb.cancelled = true;
    el._enterCb();
  }

  var data = resolveTransition(vnode.data.transition);
  if (isUndef(data)) {
    return rm()
  }

  /* istanbul ignore if */
  if (isDef(el._leaveCb) || el.nodeType !== 1) {
    return
  }

  var css = data.css;
  var type = data.type;
  var leaveClass = data.leaveClass;
  var leaveToClass = data.leaveToClass;
  var leaveActiveClass = data.leaveActiveClass;
  var beforeLeave = data.beforeLeave;
  var leave = data.leave;
  var afterLeave = data.afterLeave;
  var leaveCancelled = data.leaveCancelled;
  var delayLeave = data.delayLeave;
  var duration = data.duration;

  var expectsCSS = css !== false && !isIE9;
  var userWantsControl = getHookArgumentsLength(leave);

  var explicitLeaveDuration = toNumber(
    isObject(duration)
      ? duration.leave
      : duration
  );

  if (process.env.NODE_ENV !== 'production' && isDef(explicitLeaveDuration)) {
    checkDuration(explicitLeaveDuration, 'leave', vnode);
  }

  var cb = el._leaveCb = once(function () {
    if (el.parentNode && el.parentNode._pending) {
      el.parentNode._pending[vnode.key] = null;
    }
    if (expectsCSS) {
      removeTransitionClass(el, leaveToClass);
      removeTransitionClass(el, leaveActiveClass);
    }
    if (cb.cancelled) {
      if (expectsCSS) {
        removeTransitionClass(el, leaveClass);
      }
      leaveCancelled && leaveCancelled(el);
    } else {
      rm();
      afterLeave && afterLeave(el);
    }
    el._leaveCb = null;
  });

  if (delayLeave) {
    delayLeave(performLeave);
  } else {
    performLeave();
  }

  function performLeave () {
    // the delayed leave may have already been cancelled
    if (cb.cancelled) {
      return
    }
    // record leaving element
    if (!vnode.data.show) {
      (el.parentNode._pending || (el.parentNode._pending = {}))[(vnode.key)] = vnode;
    }
    beforeLeave && beforeLeave(el);
    if (expectsCSS) {
      addTransitionClass(el, leaveClass);
      addTransitionClass(el, leaveActiveClass);
      nextFrame(function () {
        addTransitionClass(el, leaveToClass);
        removeTransitionClass(el, leaveClass);
        if (!cb.cancelled && !userWantsControl) {
          if (isValidDuration(explicitLeaveDuration)) {
            setTimeout(cb, explicitLeaveDuration);
          } else {
            whenTransitionEnds(el, type, cb);
          }
        }
      });
    }
    leave && leave(el, cb);
    if (!expectsCSS && !userWantsControl) {
      cb();
    }
  }
}

// only used in dev mode
function checkDuration (val, name, vnode) {
  if (typeof val !== 'number') {
    warn(
      "<transition> explicit " + name + " duration is not a valid number - " +
      "got " + (JSON.stringify(val)) + ".",
      vnode.context
    );
  } else if (isNaN(val)) {
    warn(
      "<transition> explicit " + name + " duration is NaN - " +
      'the duration expression might be incorrect.',
      vnode.context
    );
  }
}

function isValidDuration (val) {
  return typeof val === 'number' && !isNaN(val)
}

/**
 * Normalize a transition hook's argument length. The hook may be:
 * - a merged hook (invoker) with the original in .fns
 * - a wrapped component method (check ._length)
 * - a plain function (.length)
 */
function getHookArgumentsLength (fn) {
  if (isUndef(fn)) {
    return false
  }
  var invokerFns = fn.fns;
  if (isDef(invokerFns)) {
    // invoker
    return getHookArgumentsLength(
      Array.isArray(invokerFns)
        ? invokerFns[0]
        : invokerFns
    )
  } else {
    return (fn._length || fn.length) > 1
  }
}

function _enter (_, vnode) {
  if (vnode.data.show !== true) {
    enter(vnode);
  }
}

var transition = inBrowser ? {
  create: _enter,
  activate: _enter,
  remove: function remove$$1 (vnode, rm) {
    /* istanbul ignore else */
    if (vnode.data.show !== true) {
      leave(vnode, rm);
    } else {
      rm();
    }
  }
} : {};

var platformModules = [
  attrs,
  klass,
  events,
  domProps,
  style,
  transition
];

/*  */

// the directive module should be applied last, after all
// built-in modules have been applied.
var modules = platformModules.concat(baseModules);

var patch = createPatchFunction({ nodeOps: nodeOps, modules: modules });

/**
 * Not type checking this file because flow doesn't like attaching
 * properties to Elements.
 */

/* istanbul ignore if */
if (isIE9) {
  // http://www.matts411.com/post/internet-explorer-9-oninput/
  document.addEventListener('selectionchange', function () {
    var el = document.activeElement;
    if (el && el.vmodel) {
      trigger(el, 'input');
    }
  });
}

var model$1 = {
  inserted: function inserted (el, binding, vnode) {
    if (vnode.tag === 'select') {
      setSelected(el, binding, vnode.context);
      el._vOptions = [].map.call(el.options, getValue);
    } else if (vnode.tag === 'textarea' || isTextInputType(el.type)) {
      el._vModifiers = binding.modifiers;
      if (!binding.modifiers.lazy) {
        // Safari < 10.2 & UIWebView doesn't fire compositionend when
        // switching focus before confirming composition choice
        // this also fixes the issue where some browsers e.g. iOS Chrome
        // fires "change" instead of "input" on autocomplete.
        el.addEventListener('change', onCompositionEnd);
        if (!isAndroid) {
          el.addEventListener('compositionstart', onCompositionStart);
          el.addEventListener('compositionend', onCompositionEnd);
        }
        /* istanbul ignore if */
        if (isIE9) {
          el.vmodel = true;
        }
      }
    }
  },
  componentUpdated: function componentUpdated (el, binding, vnode) {
    if (vnode.tag === 'select') {
      setSelected(el, binding, vnode.context);
      // in case the options rendered by v-for have changed,
      // it's possible that the value is out-of-sync with the rendered options.
      // detect such cases and filter out values that no longer has a matching
      // option in the DOM.
      var prevOptions = el._vOptions;
      var curOptions = el._vOptions = [].map.call(el.options, getValue);
      if (curOptions.some(function (o, i) { return !looseEqual(o, prevOptions[i]); })) {
        // trigger change event if
        // no matching option found for at least one value
        var needReset = el.multiple
          ? binding.value.some(function (v) { return hasNoMatchingOption(v, curOptions); })
          : binding.value !== binding.oldValue && hasNoMatchingOption(binding.value, curOptions);
        if (needReset) {
          trigger(el, 'change');
        }
      }
    }
  }
};

function setSelected (el, binding, vm) {
  actuallySetSelected(el, binding, vm);
  /* istanbul ignore if */
  if (isIE || isEdge) {
    setTimeout(function () {
      actuallySetSelected(el, binding, vm);
    }, 0);
  }
}

function actuallySetSelected (el, binding, vm) {
  var value = binding.value;
  var isMultiple = el.multiple;
  if (isMultiple && !Array.isArray(value)) {
    process.env.NODE_ENV !== 'production' && warn(
      "<select multiple v-model=\"" + (binding.expression) + "\"> " +
      "expects an Array value for its binding, but got " + (Object.prototype.toString.call(value).slice(8, -1)),
      vm
    );
    return
  }
  var selected, option;
  for (var i = 0, l = el.options.length; i < l; i++) {
    option = el.options[i];
    if (isMultiple) {
      selected = looseIndexOf(value, getValue(option)) > -1;
      if (option.selected !== selected) {
        option.selected = selected;
      }
    } else {
      if (looseEqual(getValue(option), value)) {
        if (el.selectedIndex !== i) {
          el.selectedIndex = i;
        }
        return
      }
    }
  }
  if (!isMultiple) {
    el.selectedIndex = -1;
  }
}

function hasNoMatchingOption (value, options) {
  return options.every(function (o) { return !looseEqual(o, value); })
}

function getValue (option) {
  return '_value' in option
    ? option._value
    : option.value
}

function onCompositionStart (e) {
  e.target.composing = true;
}

function onCompositionEnd (e) {
  // prevent triggering an input event for no reason
  if (!e.target.composing) { return }
  e.target.composing = false;
  trigger(e.target, 'input');
}

function trigger (el, type) {
  var e = document.createEvent('HTMLEvents');
  e.initEvent(type, true, true);
  el.dispatchEvent(e);
}

/*  */

// recursively search for possible transition defined inside the component root
function locateNode (vnode) {
  return vnode.componentInstance && (!vnode.data || !vnode.data.transition)
    ? locateNode(vnode.componentInstance._vnode)
    : vnode
}

var show = {
  bind: function bind (el, ref, vnode) {
    var value = ref.value;

    vnode = locateNode(vnode);
    var transition$$1 = vnode.data && vnode.data.transition;
    var originalDisplay = el.__vOriginalDisplay =
      el.style.display === 'none' ? '' : el.style.display;
    if (value && transition$$1) {
      vnode.data.show = true;
      enter(vnode, function () {
        el.style.display = originalDisplay;
      });
    } else {
      el.style.display = value ? originalDisplay : 'none';
    }
  },

  update: function update (el, ref, vnode) {
    var value = ref.value;
    var oldValue = ref.oldValue;

    /* istanbul ignore if */
    if (value === oldValue) { return }
    vnode = locateNode(vnode);
    var transition$$1 = vnode.data && vnode.data.transition;
    if (transition$$1) {
      vnode.data.show = true;
      if (value) {
        enter(vnode, function () {
          el.style.display = el.__vOriginalDisplay;
        });
      } else {
        leave(vnode, function () {
          el.style.display = 'none';
        });
      }
    } else {
      el.style.display = value ? el.__vOriginalDisplay : 'none';
    }
  },

  unbind: function unbind (
    el,
    binding,
    vnode,
    oldVnode,
    isDestroy
  ) {
    if (!isDestroy) {
      el.style.display = el.__vOriginalDisplay;
    }
  }
};

var platformDirectives = {
  model: model$1,
  show: show
};

/*  */

// Provides transition support for a single element/component.
// supports transition mode (out-in / in-out)

var transitionProps = {
  name: String,
  appear: Boolean,
  css: Boolean,
  mode: String,
  type: String,
  enterClass: String,
  leaveClass: String,
  enterToClass: String,
  leaveToClass: String,
  enterActiveClass: String,
  leaveActiveClass: String,
  appearClass: String,
  appearActiveClass: String,
  appearToClass: String,
  duration: [Number, String, Object]
};

// in case the child is also an abstract component, e.g. <keep-alive>
// we want to recursively retrieve the real component to be rendered
function getRealChild (vnode) {
  var compOptions = vnode && vnode.componentOptions;
  if (compOptions && compOptions.Ctor.options.abstract) {
    return getRealChild(getFirstComponentChild(compOptions.children))
  } else {
    return vnode
  }
}

function extractTransitionData (comp) {
  var data = {};
  var options = comp.$options;
  // props
  for (var key in options.propsData) {
    data[key] = comp[key];
  }
  // events.
  // extract listeners and pass them directly to the transition methods
  var listeners = options._parentListeners;
  for (var key$1 in listeners) {
    data[camelize(key$1)] = listeners[key$1];
  }
  return data
}

function placeholder (h, rawChild) {
  if (/\d-keep-alive$/.test(rawChild.tag)) {
    return h('keep-alive', {
      props: rawChild.componentOptions.propsData
    })
  }
}

function hasParentTransition (vnode) {
  while ((vnode = vnode.parent)) {
    if (vnode.data.transition) {
      return true
    }
  }
}

function isSameChild (child, oldChild) {
  return oldChild.key === child.key && oldChild.tag === child.tag
}

var Transition = {
  name: 'transition',
  props: transitionProps,
  abstract: true,

  render: function render (h) {
    var this$1 = this;

    var children = this.$options._renderChildren;
    if (!children) {
      return
    }

    // filter out text nodes (possible whitespaces)
    children = children.filter(function (c) { return c.tag || isAsyncPlaceholder(c); });
    /* istanbul ignore if */
    if (!children.length) {
      return
    }

    // warn multiple elements
    if (process.env.NODE_ENV !== 'production' && children.length > 1) {
      warn(
        '<transition> can only be used on a single element. Use ' +
        '<transition-group> for lists.',
        this.$parent
      );
    }

    var mode = this.mode;

    // warn invalid mode
    if (process.env.NODE_ENV !== 'production' &&
      mode && mode !== 'in-out' && mode !== 'out-in'
    ) {
      warn(
        'invalid <transition> mode: ' + mode,
        this.$parent
      );
    }

    var rawChild = children[0];

    // if this is a component root node and the component's
    // parent container node also has transition, skip.
    if (hasParentTransition(this.$vnode)) {
      return rawChild
    }

    // apply transition data to child
    // use getRealChild() to ignore abstract components e.g. keep-alive
    var child = getRealChild(rawChild);
    /* istanbul ignore if */
    if (!child) {
      return rawChild
    }

    if (this._leaving) {
      return placeholder(h, rawChild)
    }

    // ensure a key that is unique to the vnode type and to this transition
    // component instance. This key will be used to remove pending leaving nodes
    // during entering.
    var id = "__transition-" + (this._uid) + "-";
    child.key = child.key == null
      ? child.isComment
        ? id + 'comment'
        : id + child.tag
      : isPrimitive(child.key)
        ? (String(child.key).indexOf(id) === 0 ? child.key : id + child.key)
        : child.key;

    var data = (child.data || (child.data = {})).transition = extractTransitionData(this);
    var oldRawChild = this._vnode;
    var oldChild = getRealChild(oldRawChild);

    // mark v-show
    // so that the transition module can hand over the control to the directive
    if (child.data.directives && child.data.directives.some(function (d) { return d.name === 'show'; })) {
      child.data.show = true;
    }

    if (
      oldChild &&
      oldChild.data &&
      !isSameChild(child, oldChild) &&
      !isAsyncPlaceholder(oldChild)
    ) {
      // replace old child transition data with fresh one
      // important for dynamic transitions!
      var oldData = oldChild && (oldChild.data.transition = extend({}, data));
      // handle transition mode
      if (mode === 'out-in') {
        // return placeholder node and queue update when leave finishes
        this._leaving = true;
        mergeVNodeHook(oldData, 'afterLeave', function () {
          this$1._leaving = false;
          this$1.$forceUpdate();
        });
        return placeholder(h, rawChild)
      } else if (mode === 'in-out') {
        if (isAsyncPlaceholder(child)) {
          return oldRawChild
        }
        var delayedLeave;
        var performLeave = function () { delayedLeave(); };
        mergeVNodeHook(data, 'afterEnter', performLeave);
        mergeVNodeHook(data, 'enterCancelled', performLeave);
        mergeVNodeHook(oldData, 'delayLeave', function (leave) { delayedLeave = leave; });
      }
    }

    return rawChild
  }
};

/*  */

// Provides transition support for list items.
// supports move transitions using the FLIP technique.

// Because the vdom's children update algorithm is "unstable" - i.e.
// it doesn't guarantee the relative positioning of removed elements,
// we force transition-group to update its children into two passes:
// in the first pass, we remove all nodes that need to be removed,
// triggering their leaving transition; in the second pass, we insert/move
// into the final desired state. This way in the second pass removed
// nodes will remain where they should be.

var props = extend({
  tag: String,
  moveClass: String
}, transitionProps);

delete props.mode;

var TransitionGroup = {
  props: props,

  render: function render (h) {
    var tag = this.tag || this.$vnode.data.tag || 'span';
    var map = Object.create(null);
    var prevChildren = this.prevChildren = this.children;
    var rawChildren = this.$slots.default || [];
    var children = this.children = [];
    var transitionData = extractTransitionData(this);

    for (var i = 0; i < rawChildren.length; i++) {
      var c = rawChildren[i];
      if (c.tag) {
        if (c.key != null && String(c.key).indexOf('__vlist') !== 0) {
          children.push(c);
          map[c.key] = c
          ;(c.data || (c.data = {})).transition = transitionData;
        } else if (process.env.NODE_ENV !== 'production') {
          var opts = c.componentOptions;
          var name = opts ? (opts.Ctor.options.name || opts.tag || '') : c.tag;
          warn(("<transition-group> children must be keyed: <" + name + ">"));
        }
      }
    }

    if (prevChildren) {
      var kept = [];
      var removed = [];
      for (var i$1 = 0; i$1 < prevChildren.length; i$1++) {
        var c$1 = prevChildren[i$1];
        c$1.data.transition = transitionData;
        c$1.data.pos = c$1.elm.getBoundingClientRect();
        if (map[c$1.key]) {
          kept.push(c$1);
        } else {
          removed.push(c$1);
        }
      }
      this.kept = h(tag, null, kept);
      this.removed = removed;
    }

    return h(tag, null, children)
  },

  beforeUpdate: function beforeUpdate () {
    // force removing pass
    this.__patch__(
      this._vnode,
      this.kept,
      false, // hydrating
      true // removeOnly (!important, avoids unnecessary moves)
    );
    this._vnode = this.kept;
  },

  updated: function updated () {
    var children = this.prevChildren;
    var moveClass = this.moveClass || ((this.name || 'v') + '-move');
    if (!children.length || !this.hasMove(children[0].elm, moveClass)) {
      return
    }

    // we divide the work into three loops to avoid mixing DOM reads and writes
    // in each iteration - which helps prevent layout thrashing.
    children.forEach(callPendingCbs);
    children.forEach(recordPosition);
    children.forEach(applyTranslation);

    // force reflow to put everything in position
    var body = document.body;
    var f = body.offsetHeight; // eslint-disable-line

    children.forEach(function (c) {
      if (c.data.moved) {
        var el = c.elm;
        var s = el.style;
        addTransitionClass(el, moveClass);
        s.transform = s.WebkitTransform = s.transitionDuration = '';
        el.addEventListener(transitionEndEvent, el._moveCb = function cb (e) {
          if (!e || /transform$/.test(e.propertyName)) {
            el.removeEventListener(transitionEndEvent, cb);
            el._moveCb = null;
            removeTransitionClass(el, moveClass);
          }
        });
      }
    });
  },

  methods: {
    hasMove: function hasMove (el, moveClass) {
      /* istanbul ignore if */
      if (!hasTransition) {
        return false
      }
      /* istanbul ignore if */
      if (this._hasMove) {
        return this._hasMove
      }
      // Detect whether an element with the move class applied has
      // CSS transitions. Since the element may be inside an entering
      // transition at this very moment, we make a clone of it and remove
      // all other transition classes applied to ensure only the move class
      // is applied.
      var clone = el.cloneNode();
      if (el._transitionClasses) {
        el._transitionClasses.forEach(function (cls) { removeClass(clone, cls); });
      }
      addClass(clone, moveClass);
      clone.style.display = 'none';
      this.$el.appendChild(clone);
      var info = getTransitionInfo(clone);
      this.$el.removeChild(clone);
      return (this._hasMove = info.hasTransform)
    }
  }
};

function callPendingCbs (c) {
  /* istanbul ignore if */
  if (c.elm._moveCb) {
    c.elm._moveCb();
  }
  /* istanbul ignore if */
  if (c.elm._enterCb) {
    c.elm._enterCb();
  }
}

function recordPosition (c) {
  c.data.newPos = c.elm.getBoundingClientRect();
}

function applyTranslation (c) {
  var oldPos = c.data.pos;
  var newPos = c.data.newPos;
  var dx = oldPos.left - newPos.left;
  var dy = oldPos.top - newPos.top;
  if (dx || dy) {
    c.data.moved = true;
    var s = c.elm.style;
    s.transform = s.WebkitTransform = "translate(" + dx + "px," + dy + "px)";
    s.transitionDuration = '0s';
  }
}

var platformComponents = {
  Transition: Transition,
  TransitionGroup: TransitionGroup
};

/*  */

// install platform specific utils
Vue$3.config.mustUseProp = mustUseProp;
Vue$3.config.isReservedTag = isReservedTag;
Vue$3.config.isReservedAttr = isReservedAttr;
Vue$3.config.getTagNamespace = getTagNamespace;
Vue$3.config.isUnknownElement = isUnknownElement;

// install platform runtime directives & components
extend(Vue$3.options.directives, platformDirectives);
extend(Vue$3.options.components, platformComponents);

// install platform patch function
Vue$3.prototype.__patch__ = inBrowser ? patch : noop;

// public mount method
Vue$3.prototype.$mount = function (
  el,
  hydrating
) {
  el = el && inBrowser ? query(el) : undefined;
  return mountComponent(this, el, hydrating)
};

// devtools global hook
/* istanbul ignore next */
setTimeout(function () {
  if (config.devtools) {
    if (devtools) {
      devtools.emit('init', Vue$3);
    } else if (process.env.NODE_ENV !== 'production' && isChrome) {
      console[console.info ? 'info' : 'log'](
        'Download the Vue Devtools extension for a better development experience:\n' +
        'https://github.com/vuejs/vue-devtools'
      );
    }
  }
  if (process.env.NODE_ENV !== 'production' &&
    config.productionTip !== false &&
    inBrowser && typeof console !== 'undefined'
  ) {
    console[console.info ? 'info' : 'log'](
      "You are running Vue in development mode.\n" +
      "Make sure to turn on production mode when deploying for production.\n" +
      "See more tips at https://vuejs.org/guide/deployment.html"
    );
  }
}, 0);

/*  */

// check whether current browser encodes a char inside attribute values
function shouldDecode (content, encoded) {
  var div = document.createElement('div');
  div.innerHTML = "<div a=\"" + content + "\"/>";
  return div.innerHTML.indexOf(encoded) > 0
}

// #3663
// IE encodes newlines inside attribute values while other browsers don't
var shouldDecodeNewlines = inBrowser ? shouldDecode('\n', '&#10;') : false;

/*  */

var defaultTagRE = /\{\{((?:.|\n)+?)\}\}/g;
var regexEscapeRE = /[-.*+?^${}()|[\]\/\\]/g;

var buildRegex = cached(function (delimiters) {
  var open = delimiters[0].replace(regexEscapeRE, '\\$&');
  var close = delimiters[1].replace(regexEscapeRE, '\\$&');
  return new RegExp(open + '((?:.|\\n)+?)' + close, 'g')
});

function parseText (
  text,
  delimiters
) {
  var tagRE = delimiters ? buildRegex(delimiters) : defaultTagRE;
  if (!tagRE.test(text)) {
    return
  }
  var tokens = [];
  var lastIndex = tagRE.lastIndex = 0;
  var match, index;
  while ((match = tagRE.exec(text))) {
    index = match.index;
    // push text token
    if (index > lastIndex) {
      tokens.push(JSON.stringify(text.slice(lastIndex, index)));
    }
    // tag token
    var exp = parseFilters(match[1].trim());
    tokens.push(("_s(" + exp + ")"));
    lastIndex = index + match[0].length;
  }
  if (lastIndex < text.length) {
    tokens.push(JSON.stringify(text.slice(lastIndex)));
  }
  return tokens.join('+')
}

/*  */

function transformNode (el, options) {
  var warn = options.warn || baseWarn;
  var staticClass = getAndRemoveAttr(el, 'class');
  if (process.env.NODE_ENV !== 'production' && staticClass) {
    var expression = parseText(staticClass, options.delimiters);
    if (expression) {
      warn(
        "class=\"" + staticClass + "\": " +
        'Interpolation inside attributes has been removed. ' +
        'Use v-bind or the colon shorthand instead. For example, ' +
        'instead of <div class="{{ val }}">, use <div :class="val">.'
      );
    }
  }
  if (staticClass) {
    el.staticClass = JSON.stringify(staticClass);
  }
  var classBinding = getBindingAttr(el, 'class', false /* getStatic */);
  if (classBinding) {
    el.classBinding = classBinding;
  }
}

function genData (el) {
  var data = '';
  if (el.staticClass) {
    data += "staticClass:" + (el.staticClass) + ",";
  }
  if (el.classBinding) {
    data += "class:" + (el.classBinding) + ",";
  }
  return data
}

var klass$1 = {
  staticKeys: ['staticClass'],
  transformNode: transformNode,
  genData: genData
};

/*  */

function transformNode$1 (el, options) {
  var warn = options.warn || baseWarn;
  var staticStyle = getAndRemoveAttr(el, 'style');
  if (staticStyle) {
    /* istanbul ignore if */
    if (process.env.NODE_ENV !== 'production') {
      var expression = parseText(staticStyle, options.delimiters);
      if (expression) {
        warn(
          "style=\"" + staticStyle + "\": " +
          'Interpolation inside attributes has been removed. ' +
          'Use v-bind or the colon shorthand instead. For example, ' +
          'instead of <div style="{{ val }}">, use <div :style="val">.'
        );
      }
    }
    el.staticStyle = JSON.stringify(parseStyleText(staticStyle));
  }

  var styleBinding = getBindingAttr(el, 'style', false /* getStatic */);
  if (styleBinding) {
    el.styleBinding = styleBinding;
  }
}

function genData$1 (el) {
  var data = '';
  if (el.staticStyle) {
    data += "staticStyle:" + (el.staticStyle) + ",";
  }
  if (el.styleBinding) {
    data += "style:(" + (el.styleBinding) + "),";
  }
  return data
}

var style$1 = {
  staticKeys: ['staticStyle'],
  transformNode: transformNode$1,
  genData: genData$1
};

var modules$1 = [
  klass$1,
  style$1
];

/*  */

function text (el, dir) {
  if (dir.value) {
    addProp(el, 'textContent', ("_s(" + (dir.value) + ")"));
  }
}

/*  */

function html (el, dir) {
  if (dir.value) {
    addProp(el, 'innerHTML', ("_s(" + (dir.value) + ")"));
  }
}

var directives$1 = {
  model: model,
  text: text,
  html: html
};

/*  */

var isUnaryTag = makeMap(
  'area,base,br,col,embed,frame,hr,img,input,isindex,keygen,' +
  'link,meta,param,source,track,wbr'
);

// Elements that you can, intentionally, leave open
// (and which close themselves)
var canBeLeftOpenTag = makeMap(
  'colgroup,dd,dt,li,options,p,td,tfoot,th,thead,tr,source'
);

// HTML5 tags https://html.spec.whatwg.org/multipage/indices.html#elements-3
// Phrasing Content https://html.spec.whatwg.org/multipage/dom.html#phrasing-content
var isNonPhrasingTag = makeMap(
  'address,article,aside,base,blockquote,body,caption,col,colgroup,dd,' +
  'details,dialog,div,dl,dt,fieldset,figcaption,figure,footer,form,' +
  'h1,h2,h3,h4,h5,h6,head,header,hgroup,hr,html,legend,li,menuitem,meta,' +
  'optgroup,option,param,rp,rt,source,style,summary,tbody,td,tfoot,th,thead,' +
  'title,tr,track'
);

/*  */

var baseOptions = {
  expectHTML: true,
  modules: modules$1,
  directives: directives$1,
  isPreTag: isPreTag,
  isUnaryTag: isUnaryTag,
  mustUseProp: mustUseProp,
  canBeLeftOpenTag: canBeLeftOpenTag,
  isReservedTag: isReservedTag,
  getTagNamespace: getTagNamespace,
  staticKeys: genStaticKeys(modules$1)
};

/*  */

var decoder;

var he = {
  decode: function decode (html) {
    decoder = decoder || document.createElement('div');
    decoder.innerHTML = html;
    return decoder.textContent
  }
};

/**
 * Not type-checking this file because it's mostly vendor code.
 */

/*!
 * HTML Parser By John Resig (ejohn.org)
 * Modified by Juriy "kangax" Zaytsev
 * Original code by Erik Arvidsson, Mozilla Public License
 * http://erik.eae.net/simplehtmlparser/simplehtmlparser.js
 */

// Regular Expressions for parsing tags and attributes
var attribute = /^\s*([^\s"'<>\/=]+)(?:\s*(=)\s*(?:"([^"]*)"+|'([^']*)'+|([^\s"'=<>`]+)))?/;
// could use https://www.w3.org/TR/1999/REC-xml-names-19990114/#NT-QName
// but for Vue templates we can enforce a simple charset
var ncname = '[a-zA-Z_][\\w\\-\\.]*';
var qnameCapture = "((?:" + ncname + "\\:)?" + ncname + ")";
var startTagOpen = new RegExp(("^<" + qnameCapture));
var startTagClose = /^\s*(\/?)>/;
var endTag = new RegExp(("^<\\/" + qnameCapture + "[^>]*>"));
var doctype = /^<!DOCTYPE [^>]+>/i;
var comment = /^<!--/;
var conditionalComment = /^<!\[/;

var IS_REGEX_CAPTURING_BROKEN = false;
'x'.replace(/x(.)?/g, function (m, g) {
  IS_REGEX_CAPTURING_BROKEN = g === '';
});

// Special Elements (can contain anything)
var isPlainTextElement = makeMap('script,style,textarea', true);
var reCache = {};

var decodingMap = {
  '&lt;': '<',
  '&gt;': '>',
  '&quot;': '"',
  '&amp;': '&',
  '&#10;': '\n'
};
var encodedAttr = /&(?:lt|gt|quot|amp);/g;
var encodedAttrWithNewLines = /&(?:lt|gt|quot|amp|#10);/g;

// #5992
var isIgnoreNewlineTag = makeMap('pre,textarea', true);
var shouldIgnoreFirstNewline = function (tag, html) { return tag && isIgnoreNewlineTag(tag) && html[0] === '\n'; };

function decodeAttr (value, shouldDecodeNewlines) {
  var re = shouldDecodeNewlines ? encodedAttrWithNewLines : encodedAttr;
  return value.replace(re, function (match) { return decodingMap[match]; })
}

function parseHTML (html, options) {
  var stack = [];
  var expectHTML = options.expectHTML;
  var isUnaryTag$$1 = options.isUnaryTag || no;
  var canBeLeftOpenTag$$1 = options.canBeLeftOpenTag || no;
  var index = 0;
  var last, lastTag;
  while (html) {
    last = html;
    // Make sure we're not in a plaintext content element like script/style
    if (!lastTag || !isPlainTextElement(lastTag)) {
      var textEnd = html.indexOf('<');
      if (textEnd === 0) {
        // Comment:
        if (comment.test(html)) {
          var commentEnd = html.indexOf('-->');

          if (commentEnd >= 0) {
            if (options.shouldKeepComment) {
              options.comment(html.substring(4, commentEnd));
            }
            advance(commentEnd + 3);
            continue
          }
        }

        // http://en.wikipedia.org/wiki/Conditional_comment#Downlevel-revealed_conditional_comment
        if (conditionalComment.test(html)) {
          var conditionalEnd = html.indexOf(']>');

          if (conditionalEnd >= 0) {
            advance(conditionalEnd + 2);
            continue
          }
        }

        // Doctype:
        var doctypeMatch = html.match(doctype);
        if (doctypeMatch) {
          advance(doctypeMatch[0].length);
          continue
        }

        // End tag:
        var endTagMatch = html.match(endTag);
        if (endTagMatch) {
          var curIndex = index;
          advance(endTagMatch[0].length);
          parseEndTag(endTagMatch[1], curIndex, index);
          continue
        }

        // Start tag:
        var startTagMatch = parseStartTag();
        if (startTagMatch) {
          handleStartTag(startTagMatch);
          if (shouldIgnoreFirstNewline(lastTag, html)) {
            advance(1);
          }
          continue
        }
      }

      var text = (void 0), rest = (void 0), next = (void 0);
      if (textEnd >= 0) {
        rest = html.slice(textEnd);
        while (
          !endTag.test(rest) &&
          !startTagOpen.test(rest) &&
          !comment.test(rest) &&
          !conditionalComment.test(rest)
        ) {
          // < in plain text, be forgiving and treat it as text
          next = rest.indexOf('<', 1);
          if (next < 0) { break }
          textEnd += next;
          rest = html.slice(textEnd);
        }
        text = html.substring(0, textEnd);
        advance(textEnd);
      }

      if (textEnd < 0) {
        text = html;
        html = '';
      }

      if (options.chars && text) {
        options.chars(text);
      }
    } else {
      var endTagLength = 0;
      var stackedTag = lastTag.toLowerCase();
      var reStackedTag = reCache[stackedTag] || (reCache[stackedTag] = new RegExp('([\\s\\S]*?)(</' + stackedTag + '[^>]*>)', 'i'));
      var rest$1 = html.replace(reStackedTag, function (all, text, endTag) {
        endTagLength = endTag.length;
        if (!isPlainTextElement(stackedTag) && stackedTag !== 'noscript') {
          text = text
            .replace(/<!--([\s\S]*?)-->/g, '$1')
            .replace(/<!\[CDATA\[([\s\S]*?)]]>/g, '$1');
        }
        if (shouldIgnoreFirstNewline(stackedTag, text)) {
          text = text.slice(1);
        }
        if (options.chars) {
          options.chars(text);
        }
        return ''
      });
      index += html.length - rest$1.length;
      html = rest$1;
      parseEndTag(stackedTag, index - endTagLength, index);
    }

    if (html === last) {
      options.chars && options.chars(html);
      if (process.env.NODE_ENV !== 'production' && !stack.length && options.warn) {
        options.warn(("Mal-formatted tag at end of template: \"" + html + "\""));
      }
      break
    }
  }

  // Clean up any remaining tags
  parseEndTag();

  function advance (n) {
    index += n;
    html = html.substring(n);
  }

  function parseStartTag () {
    var start = html.match(startTagOpen);
    if (start) {
      var match = {
        tagName: start[1],
        attrs: [],
        start: index
      };
      advance(start[0].length);
      var end, attr;
      while (!(end = html.match(startTagClose)) && (attr = html.match(attribute))) {
        advance(attr[0].length);
        match.attrs.push(attr);
      }
      if (end) {
        match.unarySlash = end[1];
        advance(end[0].length);
        match.end = index;
        return match
      }
    }
  }

  function handleStartTag (match) {
    var tagName = match.tagName;
    var unarySlash = match.unarySlash;

    if (expectHTML) {
      if (lastTag === 'p' && isNonPhrasingTag(tagName)) {
        parseEndTag(lastTag);
      }
      if (canBeLeftOpenTag$$1(tagName) && lastTag === tagName) {
        parseEndTag(tagName);
      }
    }

    var unary = isUnaryTag$$1(tagName) || !!unarySlash;

    var l = match.attrs.length;
    var attrs = new Array(l);
    for (var i = 0; i < l; i++) {
      var args = match.attrs[i];
      // hackish work around FF bug https://bugzilla.mozilla.org/show_bug.cgi?id=369778
      if (IS_REGEX_CAPTURING_BROKEN && args[0].indexOf('""') === -1) {
        if (args[3] === '') { delete args[3]; }
        if (args[4] === '') { delete args[4]; }
        if (args[5] === '') { delete args[5]; }
      }
      var value = args[3] || args[4] || args[5] || '';
      attrs[i] = {
        name: args[1],
        value: decodeAttr(
          value,
          options.shouldDecodeNewlines
        )
      };
    }

    if (!unary) {
      stack.push({ tag: tagName, lowerCasedTag: tagName.toLowerCase(), attrs: attrs });
      lastTag = tagName;
    }

    if (options.start) {
      options.start(tagName, attrs, unary, match.start, match.end);
    }
  }

  function parseEndTag (tagName, start, end) {
    var pos, lowerCasedTagName;
    if (start == null) { start = index; }
    if (end == null) { end = index; }

    if (tagName) {
      lowerCasedTagName = tagName.toLowerCase();
    }

    // Find the closest opened tag of the same type
    if (tagName) {
      for (pos = stack.length - 1; pos >= 0; pos--) {
        if (stack[pos].lowerCasedTag === lowerCasedTagName) {
          break
        }
      }
    } else {
      // If no tag name is provided, clean shop
      pos = 0;
    }

    if (pos >= 0) {
      // Close all the open elements, up the stack
      for (var i = stack.length - 1; i >= pos; i--) {
        if (process.env.NODE_ENV !== 'production' &&
          (i > pos || !tagName) &&
          options.warn
        ) {
          options.warn(
            ("tag <" + (stack[i].tag) + "> has no matching end tag.")
          );
        }
        if (options.end) {
          options.end(stack[i].tag, start, end);
        }
      }

      // Remove the open elements from the stack
      stack.length = pos;
      lastTag = pos && stack[pos - 1].tag;
    } else if (lowerCasedTagName === 'br') {
      if (options.start) {
        options.start(tagName, [], true, start, end);
      }
    } else if (lowerCasedTagName === 'p') {
      if (options.start) {
        options.start(tagName, [], false, start, end);
      }
      if (options.end) {
        options.end(tagName, start, end);
      }
    }
  }
}

/*  */

var onRE = /^@|^v-on:/;
var dirRE = /^v-|^@|^:/;
var forAliasRE = /(.*?)\s+(?:in|of)\s+(.*)/;
var forIteratorRE = /\((\{[^}]*\}|[^,]*),([^,]*)(?:,([^,]*))?\)/;

var argRE = /:(.*)$/;
var bindRE = /^:|^v-bind:/;
var modifierRE = /\.[^.]+/g;

var decodeHTMLCached = cached(he.decode);

// configurable state
var warn$2;
var delimiters;
var transforms;
var preTransforms;
var postTransforms;
var platformIsPreTag;
var platformMustUseProp;
var platformGetTagNamespace;

/**
 * Convert HTML string to AST.
 */
function parse (
  template,
  options
) {
  warn$2 = options.warn || baseWarn;

  platformIsPreTag = options.isPreTag || no;
  platformMustUseProp = options.mustUseProp || no;
  platformGetTagNamespace = options.getTagNamespace || no;

  transforms = pluckModuleFunction(options.modules, 'transformNode');
  preTransforms = pluckModuleFunction(options.modules, 'preTransformNode');
  postTransforms = pluckModuleFunction(options.modules, 'postTransformNode');

  delimiters = options.delimiters;

  var stack = [];
  var preserveWhitespace = options.preserveWhitespace !== false;
  var root;
  var currentParent;
  var inVPre = false;
  var inPre = false;
  var warned = false;

  function warnOnce (msg) {
    if (!warned) {
      warned = true;
      warn$2(msg);
    }
  }

  function endPre (element) {
    // check pre state
    if (element.pre) {
      inVPre = false;
    }
    if (platformIsPreTag(element.tag)) {
      inPre = false;
    }
  }

  parseHTML(template, {
    warn: warn$2,
    expectHTML: options.expectHTML,
    isUnaryTag: options.isUnaryTag,
    canBeLeftOpenTag: options.canBeLeftOpenTag,
    shouldDecodeNewlines: options.shouldDecodeNewlines,
    shouldKeepComment: options.comments,
    start: function start (tag, attrs, unary) {
      // check namespace.
      // inherit parent ns if there is one
      var ns = (currentParent && currentParent.ns) || platformGetTagNamespace(tag);

      // handle IE svg bug
      /* istanbul ignore if */
      if (isIE && ns === 'svg') {
        attrs = guardIESVGBug(attrs);
      }

      var element = {
        type: 1,
        tag: tag,
        attrsList: attrs,
        attrsMap: makeAttrsMap(attrs),
        parent: currentParent,
        children: []
      };
      if (ns) {
        element.ns = ns;
      }

      if (isForbiddenTag(element) && !isServerRendering()) {
        element.forbidden = true;
        process.env.NODE_ENV !== 'production' && warn$2(
          'Templates should only be responsible for mapping the state to the ' +
          'UI. Avoid placing tags with side-effects in your templates, such as ' +
          "<" + tag + ">" + ', as they will not be parsed.'
        );
      }

      // apply pre-transforms
      for (var i = 0; i < preTransforms.length; i++) {
        preTransforms[i](element, options);
      }

      if (!inVPre) {
        processPre(element);
        if (element.pre) {
          inVPre = true;
        }
      }
      if (platformIsPreTag(element.tag)) {
        inPre = true;
      }
      if (inVPre) {
        processRawAttrs(element);
      } else {
        processFor(element);
        processIf(element);
        processOnce(element);
        processKey(element);

        // determine whether this is a plain element after
        // removing structural attributes
        element.plain = !element.key && !attrs.length;

        processRef(element);
        processSlot(element);
        processComponent(element);
        for (var i$1 = 0; i$1 < transforms.length; i$1++) {
          transforms[i$1](element, options);
        }
        processAttrs(element);
      }

      function checkRootConstraints (el) {
        if (process.env.NODE_ENV !== 'production') {
          if (el.tag === 'slot' || el.tag === 'template') {
            warnOnce(
              "Cannot use <" + (el.tag) + "> as component root element because it may " +
              'contain multiple nodes.'
            );
          }
          if (el.attrsMap.hasOwnProperty('v-for')) {
            warnOnce(
              'Cannot use v-for on stateful component root element because ' +
              'it renders multiple elements.'
            );
          }
        }
      }

      // tree management
      if (!root) {
        root = element;
        checkRootConstraints(root);
      } else if (!stack.length) {
        // allow root elements with v-if, v-else-if and v-else
        if (root.if && (element.elseif || element.else)) {
          checkRootConstraints(element);
          addIfCondition(root, {
            exp: element.elseif,
            block: element
          });
        } else if (process.env.NODE_ENV !== 'production') {
          warnOnce(
            "Component template should contain exactly one root element. " +
            "If you are using v-if on multiple elements, " +
            "use v-else-if to chain them instead."
          );
        }
      }
      if (currentParent && !element.forbidden) {
        if (element.elseif || element.else) {
          processIfConditions(element, currentParent);
        } else if (element.slotScope) { // scoped slot
          currentParent.plain = false;
          var name = element.slotTarget || '"default"';(currentParent.scopedSlots || (currentParent.scopedSlots = {}))[name] = element;
        } else {
          currentParent.children.push(element);
          element.parent = currentParent;
        }
      }
      if (!unary) {
        currentParent = element;
        stack.push(element);
      } else {
        endPre(element);
      }
      // apply post-transforms
      for (var i$2 = 0; i$2 < postTransforms.length; i$2++) {
        postTransforms[i$2](element, options);
      }
    },

    end: function end () {
      // remove trailing whitespace
      var element = stack[stack.length - 1];
      var lastNode = element.children[element.children.length - 1];
      if (lastNode && lastNode.type === 3 && lastNode.text === ' ' && !inPre) {
        element.children.pop();
      }
      // pop stack
      stack.length -= 1;
      currentParent = stack[stack.length - 1];
      endPre(element);
    },

    chars: function chars (text) {
      if (!currentParent) {
        if (process.env.NODE_ENV !== 'production') {
          if (text === template) {
            warnOnce(
              'Component template requires a root element, rather than just text.'
            );
          } else if ((text = text.trim())) {
            warnOnce(
              ("text \"" + text + "\" outside root element will be ignored.")
            );
          }
        }
        return
      }
      // IE textarea placeholder bug
      /* istanbul ignore if */
      if (isIE &&
        currentParent.tag === 'textarea' &&
        currentParent.attrsMap.placeholder === text
      ) {
        return
      }
      var children = currentParent.children;
      text = inPre || text.trim()
        ? isTextTag(currentParent) ? text : decodeHTMLCached(text)
        // only preserve whitespace if its not right after a starting tag
        : preserveWhitespace && children.length ? ' ' : '';
      if (text) {
        var expression;
        if (!inVPre && text !== ' ' && (expression = parseText(text, delimiters))) {
          children.push({
            type: 2,
            expression: expression,
            text: text
          });
        } else if (text !== ' ' || !children.length || children[children.length - 1].text !== ' ') {
          children.push({
            type: 3,
            text: text
          });
        }
      }
    },
    comment: function comment (text) {
      currentParent.children.push({
        type: 3,
        text: text,
        isComment: true
      });
    }
  });
  return root
}

function processPre (el) {
  if (getAndRemoveAttr(el, 'v-pre') != null) {
    el.pre = true;
  }
}

function processRawAttrs (el) {
  var l = el.attrsList.length;
  if (l) {
    var attrs = el.attrs = new Array(l);
    for (var i = 0; i < l; i++) {
      attrs[i] = {
        name: el.attrsList[i].name,
        value: JSON.stringify(el.attrsList[i].value)
      };
    }
  } else if (!el.pre) {
    // non root node in pre blocks with no attributes
    el.plain = true;
  }
}

function processKey (el) {
  var exp = getBindingAttr(el, 'key');
  if (exp) {
    if (process.env.NODE_ENV !== 'production' && el.tag === 'template') {
      warn$2("<template> cannot be keyed. Place the key on real elements instead.");
    }
    el.key = exp;
  }
}

function processRef (el) {
  var ref = getBindingAttr(el, 'ref');
  if (ref) {
    el.ref = ref;
    el.refInFor = checkInFor(el);
  }
}

function processFor (el) {
  var exp;
  if ((exp = getAndRemoveAttr(el, 'v-for'))) {
    var inMatch = exp.match(forAliasRE);
    if (!inMatch) {
      process.env.NODE_ENV !== 'production' && warn$2(
        ("Invalid v-for expression: " + exp)
      );
      return
    }
    el.for = inMatch[2].trim();
    var alias = inMatch[1].trim();
    var iteratorMatch = alias.match(forIteratorRE);
    if (iteratorMatch) {
      el.alias = iteratorMatch[1].trim();
      el.iterator1 = iteratorMatch[2].trim();
      if (iteratorMatch[3]) {
        el.iterator2 = iteratorMatch[3].trim();
      }
    } else {
      el.alias = alias;
    }
  }
}

function processIf (el) {
  var exp = getAndRemoveAttr(el, 'v-if');
  if (exp) {
    el.if = exp;
    addIfCondition(el, {
      exp: exp,
      block: el
    });
  } else {
    if (getAndRemoveAttr(el, 'v-else') != null) {
      el.else = true;
    }
    var elseif = getAndRemoveAttr(el, 'v-else-if');
    if (elseif) {
      el.elseif = elseif;
    }
  }
}

function processIfConditions (el, parent) {
  var prev = findPrevElement(parent.children);
  if (prev && prev.if) {
    addIfCondition(prev, {
      exp: el.elseif,
      block: el
    });
  } else if (process.env.NODE_ENV !== 'production') {
    warn$2(
      "v-" + (el.elseif ? ('else-if="' + el.elseif + '"') : 'else') + " " +
      "used on element <" + (el.tag) + "> without corresponding v-if."
    );
  }
}

function findPrevElement (children) {
  var i = children.length;
  while (i--) {
    if (children[i].type === 1) {
      return children[i]
    } else {
      if (process.env.NODE_ENV !== 'production' && children[i].text !== ' ') {
        warn$2(
          "text \"" + (children[i].text.trim()) + "\" between v-if and v-else(-if) " +
          "will be ignored."
        );
      }
      children.pop();
    }
  }
}

function addIfCondition (el, condition) {
  if (!el.ifConditions) {
    el.ifConditions = [];
  }
  el.ifConditions.push(condition);
}

function processOnce (el) {
  var once$$1 = getAndRemoveAttr(el, 'v-once');
  if (once$$1 != null) {
    el.once = true;
  }
}

function processSlot (el) {
  if (el.tag === 'slot') {
    el.slotName = getBindingAttr(el, 'name');
    if (process.env.NODE_ENV !== 'production' && el.key) {
      warn$2(
        "`key` does not work on <slot> because slots are abstract outlets " +
        "and can possibly expand into multiple elements. " +
        "Use the key on a wrapping element instead."
      );
    }
  } else {
    var slotTarget = getBindingAttr(el, 'slot');
    if (slotTarget) {
      el.slotTarget = slotTarget === '""' ? '"default"' : slotTarget;
      // preserve slot as an attribute for native shadow DOM compat
      addAttr(el, 'slot', slotTarget);
    }
    if (el.tag === 'template') {
      el.slotScope = getAndRemoveAttr(el, 'scope');
    }
  }
}

function processComponent (el) {
  var binding;
  if ((binding = getBindingAttr(el, 'is'))) {
    el.component = binding;
  }
  if (getAndRemoveAttr(el, 'inline-template') != null) {
    el.inlineTemplate = true;
  }
}

function processAttrs (el) {
  var list = el.attrsList;
  var i, l, name, rawName, value, modifiers, isProp;
  for (i = 0, l = list.length; i < l; i++) {
    name = rawName = list[i].name;
    value = list[i].value;
    if (dirRE.test(name)) {
      // mark element as dynamic
      el.hasBindings = true;
      // modifiers
      modifiers = parseModifiers(name);
      if (modifiers) {
        name = name.replace(modifierRE, '');
      }
      if (bindRE.test(name)) { // v-bind
        name = name.replace(bindRE, '');
        value = parseFilters(value);
        isProp = false;
        if (modifiers) {
          if (modifiers.prop) {
            isProp = true;
            name = camelize(name);
            if (name === 'innerHtml') { name = 'innerHTML'; }
          }
          if (modifiers.camel) {
            name = camelize(name);
          }
          if (modifiers.sync) {
            addHandler(
              el,
              ("update:" + (camelize(name))),
              genAssignmentCode(value, "$event")
            );
          }
        }
        if (isProp || (
          !el.component && platformMustUseProp(el.tag, el.attrsMap.type, name)
        )) {
          addProp(el, name, value);
        } else {
          addAttr(el, name, value);
        }
      } else if (onRE.test(name)) { // v-on
        name = name.replace(onRE, '');
        addHandler(el, name, value, modifiers, false, warn$2);
      } else { // normal directives
        name = name.replace(dirRE, '');
        // parse arg
        var argMatch = name.match(argRE);
        var arg = argMatch && argMatch[1];
        if (arg) {
          name = name.slice(0, -(arg.length + 1));
        }
        addDirective(el, name, rawName, value, arg, modifiers);
        if (process.env.NODE_ENV !== 'production' && name === 'model') {
          checkForAliasModel(el, value);
        }
      }
    } else {
      // literal attribute
      if (process.env.NODE_ENV !== 'production') {
        var expression = parseText(value, delimiters);
        if (expression) {
          warn$2(
            name + "=\"" + value + "\": " +
            'Interpolation inside attributes has been removed. ' +
            'Use v-bind or the colon shorthand instead. For example, ' +
            'instead of <div id="{{ val }}">, use <div :id="val">.'
          );
        }
      }
      addAttr(el, name, JSON.stringify(value));
    }
  }
}

function checkInFor (el) {
  var parent = el;
  while (parent) {
    if (parent.for !== undefined) {
      return true
    }
    parent = parent.parent;
  }
  return false
}

function parseModifiers (name) {
  var match = name.match(modifierRE);
  if (match) {
    var ret = {};
    match.forEach(function (m) { ret[m.slice(1)] = true; });
    return ret
  }
}

function makeAttrsMap (attrs) {
  var map = {};
  for (var i = 0, l = attrs.length; i < l; i++) {
    if (
      process.env.NODE_ENV !== 'production' &&
      map[attrs[i].name] && !isIE && !isEdge
    ) {
      warn$2('duplicate attribute: ' + attrs[i].name);
    }
    map[attrs[i].name] = attrs[i].value;
  }
  return map
}

// for script (e.g. type="x/template") or style, do not decode content
function isTextTag (el) {
  return el.tag === 'script' || el.tag === 'style'
}

function isForbiddenTag (el) {
  return (
    el.tag === 'style' ||
    (el.tag === 'script' && (
      !el.attrsMap.type ||
      el.attrsMap.type === 'text/javascript'
    ))
  )
}

var ieNSBug = /^xmlns:NS\d+/;
var ieNSPrefix = /^NS\d+:/;

/* istanbul ignore next */
function guardIESVGBug (attrs) {
  var res = [];
  for (var i = 0; i < attrs.length; i++) {
    var attr = attrs[i];
    if (!ieNSBug.test(attr.name)) {
      attr.name = attr.name.replace(ieNSPrefix, '');
      res.push(attr);
    }
  }
  return res
}

function checkForAliasModel (el, value) {
  var _el = el;
  while (_el) {
    if (_el.for && _el.alias === value) {
      warn$2(
        "<" + (el.tag) + " v-model=\"" + value + "\">: " +
        "You are binding v-model directly to a v-for iteration alias. " +
        "This will not be able to modify the v-for source array because " +
        "writing to the alias is like modifying a function local variable. " +
        "Consider using an array of objects and use v-model on an object property instead."
      );
    }
    _el = _el.parent;
  }
}

/*  */

var isStaticKey;
var isPlatformReservedTag;

var genStaticKeysCached = cached(genStaticKeys$1);

/**
 * Goal of the optimizer: walk the generated template AST tree
 * and detect sub-trees that are purely static, i.e. parts of
 * the DOM that never needs to change.
 *
 * Once we detect these sub-trees, we can:
 *
 * 1. Hoist them into constants, so that we no longer need to
 *    create fresh nodes for them on each re-render;
 * 2. Completely skip them in the patching process.
 */
function optimize (root, options) {
  if (!root) { return }
  isStaticKey = genStaticKeysCached(options.staticKeys || '');
  isPlatformReservedTag = options.isReservedTag || no;
  // first pass: mark all non-static nodes.
  markStatic$1(root);
  // second pass: mark static roots.
  markStaticRoots(root, false);
}

function genStaticKeys$1 (keys) {
  return makeMap(
    'type,tag,attrsList,attrsMap,plain,parent,children,attrs' +
    (keys ? ',' + keys : '')
  )
}

function markStatic$1 (node) {
  node.static = isStatic(node);
  if (node.type === 1) {
    // do not make component slot content static. this avoids
    // 1. components not able to mutate slot nodes
    // 2. static slot content fails for hot-reloading
    if (
      !isPlatformReservedTag(node.tag) &&
      node.tag !== 'slot' &&
      node.attrsMap['inline-template'] == null
    ) {
      return
    }
    for (var i = 0, l = node.children.length; i < l; i++) {
      var child = node.children[i];
      markStatic$1(child);
      if (!child.static) {
        node.static = false;
      }
    }
    if (node.ifConditions) {
      for (var i$1 = 1, l$1 = node.ifConditions.length; i$1 < l$1; i$1++) {
        var block = node.ifConditions[i$1].block;
        markStatic$1(block);
        if (!block.static) {
          node.static = false;
        }
      }
    }
  }
}

function markStaticRoots (node, isInFor) {
  if (node.type === 1) {
    if (node.static || node.once) {
      node.staticInFor = isInFor;
    }
    // For a node to qualify as a static root, it should have children that
    // are not just static text. Otherwise the cost of hoisting out will
    // outweigh the benefits and it's better off to just always render it fresh.
    if (node.static && node.children.length && !(
      node.children.length === 1 &&
      node.children[0].type === 3
    )) {
      node.staticRoot = true;
      return
    } else {
      node.staticRoot = false;
    }
    if (node.children) {
      for (var i = 0, l = node.children.length; i < l; i++) {
        markStaticRoots(node.children[i], isInFor || !!node.for);
      }
    }
    if (node.ifConditions) {
      for (var i$1 = 1, l$1 = node.ifConditions.length; i$1 < l$1; i$1++) {
        markStaticRoots(node.ifConditions[i$1].block, isInFor);
      }
    }
  }
}

function isStatic (node) {
  if (node.type === 2) { // expression
    return false
  }
  if (node.type === 3) { // text
    return true
  }
  return !!(node.pre || (
    !node.hasBindings && // no dynamic bindings
    !node.if && !node.for && // not v-if or v-for or v-else
    !isBuiltInTag(node.tag) && // not a built-in
    isPlatformReservedTag(node.tag) && // not a component
    !isDirectChildOfTemplateFor(node) &&
    Object.keys(node).every(isStaticKey)
  ))
}

function isDirectChildOfTemplateFor (node) {
  while (node.parent) {
    node = node.parent;
    if (node.tag !== 'template') {
      return false
    }
    if (node.for) {
      return true
    }
  }
  return false
}

/*  */

var fnExpRE = /^\s*([\w$_]+|\([^)]*?\))\s*=>|^function\s*\(/;
var simplePathRE = /^\s*[A-Za-z_$][\w$]*(?:\.[A-Za-z_$][\w$]*|\['.*?']|\[".*?"]|\[\d+]|\[[A-Za-z_$][\w$]*])*\s*$/;

// keyCode aliases
var keyCodes = {
  esc: 27,
  tab: 9,
  enter: 13,
  space: 32,
  up: 38,
  left: 37,
  right: 39,
  down: 40,
  'delete': [8, 46]
};

// #4868: modifiers that prevent the execution of the listener
// need to explicitly return null so that we can determine whether to remove
// the listener for .once
var genGuard = function (condition) { return ("if(" + condition + ")return null;"); };

var modifierCode = {
  stop: '$event.stopPropagation();',
  prevent: '$event.preventDefault();',
  self: genGuard("$event.target !== $event.currentTarget"),
  ctrl: genGuard("!$event.ctrlKey"),
  shift: genGuard("!$event.shiftKey"),
  alt: genGuard("!$event.altKey"),
  meta: genGuard("!$event.metaKey"),
  left: genGuard("'button' in $event && $event.button !== 0"),
  middle: genGuard("'button' in $event && $event.button !== 1"),
  right: genGuard("'button' in $event && $event.button !== 2")
};

function genHandlers (
  events,
  isNative,
  warn
) {
  var res = isNative ? 'nativeOn:{' : 'on:{';
  for (var name in events) {
    var handler = events[name];
    // #5330: warn click.right, since right clicks do not actually fire click events.
    if (process.env.NODE_ENV !== 'production' &&
      name === 'click' &&
      handler && handler.modifiers && handler.modifiers.right
    ) {
      warn(
        "Use \"contextmenu\" instead of \"click.right\" since right clicks " +
        "do not actually fire \"click\" events."
      );
    }
    res += "\"" + name + "\":" + (genHandler(name, handler)) + ",";
  }
  return res.slice(0, -1) + '}'
}

function genHandler (
  name,
  handler
) {
  if (!handler) {
    return 'function(){}'
  }

  if (Array.isArray(handler)) {
    return ("[" + (handler.map(function (handler) { return genHandler(name, handler); }).join(',')) + "]")
  }

  var isMethodPath = simplePathRE.test(handler.value);
  var isFunctionExpression = fnExpRE.test(handler.value);

  if (!handler.modifiers) {
    return isMethodPath || isFunctionExpression
      ? handler.value
      : ("function($event){" + (handler.value) + "}") // inline statement
  } else {
    var code = '';
    var genModifierCode = '';
    var keys = [];
    for (var key in handler.modifiers) {
      if (modifierCode[key]) {
        genModifierCode += modifierCode[key];
        // left/right
        if (keyCodes[key]) {
          keys.push(key);
        }
      } else {
        keys.push(key);
      }
    }
    if (keys.length) {
      code += genKeyFilter(keys);
    }
    // Make sure modifiers like prevent and stop get executed after key filtering
    if (genModifierCode) {
      code += genModifierCode;
    }
    var handlerCode = isMethodPath
      ? handler.value + '($event)'
      : isFunctionExpression
        ? ("(" + (handler.value) + ")($event)")
        : handler.value;
    return ("function($event){" + code + handlerCode + "}")
  }
}

function genKeyFilter (keys) {
  return ("if(!('button' in $event)&&" + (keys.map(genFilterCode).join('&&')) + ")return null;")
}

function genFilterCode (key) {
  var keyVal = parseInt(key, 10);
  if (keyVal) {
    return ("$event.keyCode!==" + keyVal)
  }
  var alias = keyCodes[key];
  return ("_k($event.keyCode," + (JSON.stringify(key)) + (alias ? ',' + JSON.stringify(alias) : '') + ")")
}

/*  */

function on (el, dir) {
  if (process.env.NODE_ENV !== 'production' && dir.modifiers) {
    warn("v-on without argument does not support modifiers.");
  }
  el.wrapListeners = function (code) { return ("_g(" + code + "," + (dir.value) + ")"); };
}

/*  */

function bind$1 (el, dir) {
  el.wrapData = function (code) {
    return ("_b(" + code + ",'" + (el.tag) + "'," + (dir.value) + "," + (dir.modifiers && dir.modifiers.prop ? 'true' : 'false') + (dir.modifiers && dir.modifiers.sync ? ',true' : '') + ")")
  };
}

/*  */

var baseDirectives = {
  on: on,
  bind: bind$1,
  cloak: noop
};

/*  */

var CodegenState = function CodegenState (options) {
  this.options = options;
  this.warn = options.warn || baseWarn;
  this.transforms = pluckModuleFunction(options.modules, 'transformCode');
  this.dataGenFns = pluckModuleFunction(options.modules, 'genData');
  this.directives = extend(extend({}, baseDirectives), options.directives);
  var isReservedTag = options.isReservedTag || no;
  this.maybeComponent = function (el) { return !isReservedTag(el.tag); };
  this.onceId = 0;
  this.staticRenderFns = [];
};



function generate (
  ast,
  options
) {
  var state = new CodegenState(options);
  var code = ast ? genElement(ast, state) : '_c("div")';
  return {
    render: ("with(this){return " + code + "}"),
    staticRenderFns: state.staticRenderFns
  }
}

function genElement (el, state) {
  if (el.staticRoot && !el.staticProcessed) {
    return genStatic(el, state)
  } else if (el.once && !el.onceProcessed) {
    return genOnce(el, state)
  } else if (el.for && !el.forProcessed) {
    return genFor(el, state)
  } else if (el.if && !el.ifProcessed) {
    return genIf(el, state)
  } else if (el.tag === 'template' && !el.slotTarget) {
    return genChildren(el, state) || 'void 0'
  } else if (el.tag === 'slot') {
    return genSlot(el, state)
  } else {
    // component or element
    var code;
    if (el.component) {
      code = genComponent(el.component, el, state);
    } else {
      var data = el.plain ? undefined : genData$2(el, state);

      var children = el.inlineTemplate ? null : genChildren(el, state, true);
      code = "_c('" + (el.tag) + "'" + (data ? ("," + data) : '') + (children ? ("," + children) : '') + ")";
    }
    // module transforms
    for (var i = 0; i < state.transforms.length; i++) {
      code = state.transforms[i](el, code);
    }
    return code
  }
}

// hoist static sub-trees out
function genStatic (el, state) {
  el.staticProcessed = true;
  state.staticRenderFns.push(("with(this){return " + (genElement(el, state)) + "}"));
  return ("_m(" + (state.staticRenderFns.length - 1) + (el.staticInFor ? ',true' : '') + ")")
}

// v-once
function genOnce (el, state) {
  el.onceProcessed = true;
  if (el.if && !el.ifProcessed) {
    return genIf(el, state)
  } else if (el.staticInFor) {
    var key = '';
    var parent = el.parent;
    while (parent) {
      if (parent.for) {
        key = parent.key;
        break
      }
      parent = parent.parent;
    }
    if (!key) {
      process.env.NODE_ENV !== 'production' && state.warn(
        "v-once can only be used inside v-for that is keyed. "
      );
      return genElement(el, state)
    }
    return ("_o(" + (genElement(el, state)) + "," + (state.onceId++) + "," + key + ")")
  } else {
    return genStatic(el, state)
  }
}

function genIf (
  el,
  state,
  altGen,
  altEmpty
) {
  el.ifProcessed = true; // avoid recursion
  return genIfConditions(el.ifConditions.slice(), state, altGen, altEmpty)
}

function genIfConditions (
  conditions,
  state,
  altGen,
  altEmpty
) {
  if (!conditions.length) {
    return altEmpty || '_e()'
  }

  var condition = conditions.shift();
  if (condition.exp) {
    return ("(" + (condition.exp) + ")?" + (genTernaryExp(condition.block)) + ":" + (genIfConditions(conditions, state, altGen, altEmpty)))
  } else {
    return ("" + (genTernaryExp(condition.block)))
  }

  // v-if with v-once should generate code like (a)?_m(0):_m(1)
  function genTernaryExp (el) {
    return altGen
      ? altGen(el, state)
      : el.once
        ? genOnce(el, state)
        : genElement(el, state)
  }
}

function genFor (
  el,
  state,
  altGen,
  altHelper
) {
  var exp = el.for;
  var alias = el.alias;
  var iterator1 = el.iterator1 ? ("," + (el.iterator1)) : '';
  var iterator2 = el.iterator2 ? ("," + (el.iterator2)) : '';

  if (process.env.NODE_ENV !== 'production' &&
    state.maybeComponent(el) &&
    el.tag !== 'slot' &&
    el.tag !== 'template' &&
    !el.key
  ) {
    state.warn(
      "<" + (el.tag) + " v-for=\"" + alias + " in " + exp + "\">: component lists rendered with " +
      "v-for should have explicit keys. " +
      "See https://vuejs.org/guide/list.html#key for more info.",
      true /* tip */
    );
  }

  el.forProcessed = true; // avoid recursion
  return (altHelper || '_l') + "((" + exp + ")," +
    "function(" + alias + iterator1 + iterator2 + "){" +
      "return " + ((altGen || genElement)(el, state)) +
    '})'
}

function genData$2 (el, state) {
  var data = '{';

  // directives first.
  // directives may mutate the el's other properties before they are generated.
  var dirs = genDirectives(el, state);
  if (dirs) { data += dirs + ','; }

  // key
  if (el.key) {
    data += "key:" + (el.key) + ",";
  }
  // ref
  if (el.ref) {
    data += "ref:" + (el.ref) + ",";
  }
  if (el.refInFor) {
    data += "refInFor:true,";
  }
  // pre
  if (el.pre) {
    data += "pre:true,";
  }
  // record original tag name for components using "is" attribute
  if (el.component) {
    data += "tag:\"" + (el.tag) + "\",";
  }
  // module data generation functions
  for (var i = 0; i < state.dataGenFns.length; i++) {
    data += state.dataGenFns[i](el);
  }
  // attributes
  if (el.attrs) {
    data += "attrs:{" + (genProps(el.attrs)) + "},";
  }
  // DOM props
  if (el.props) {
    data += "domProps:{" + (genProps(el.props)) + "},";
  }
  // event handlers
  if (el.events) {
    data += (genHandlers(el.events, false, state.warn)) + ",";
  }
  if (el.nativeEvents) {
    data += (genHandlers(el.nativeEvents, true, state.warn)) + ",";
  }
  // slot target
  if (el.slotTarget) {
    data += "slot:" + (el.slotTarget) + ",";
  }
  // scoped slots
  if (el.scopedSlots) {
    data += (genScopedSlots(el.scopedSlots, state)) + ",";
  }
  // component v-model
  if (el.model) {
    data += "model:{value:" + (el.model.value) + ",callback:" + (el.model.callback) + ",expression:" + (el.model.expression) + "},";
  }
  // inline-template
  if (el.inlineTemplate) {
    var inlineTemplate = genInlineTemplate(el, state);
    if (inlineTemplate) {
      data += inlineTemplate + ",";
    }
  }
  data = data.replace(/,$/, '') + '}';
  // v-bind data wrap
  if (el.wrapData) {
    data = el.wrapData(data);
  }
  // v-on data wrap
  if (el.wrapListeners) {
    data = el.wrapListeners(data);
  }
  return data
}

function genDirectives (el, state) {
  var dirs = el.directives;
  if (!dirs) { return }
  var res = 'directives:[';
  var hasRuntime = false;
  var i, l, dir, needRuntime;
  for (i = 0, l = dirs.length; i < l; i++) {
    dir = dirs[i];
    needRuntime = true;
    var gen = state.directives[dir.name];
    if (gen) {
      // compile-time directive that manipulates AST.
      // returns true if it also needs a runtime counterpart.
      needRuntime = !!gen(el, dir, state.warn);
    }
    if (needRuntime) {
      hasRuntime = true;
      res += "{name:\"" + (dir.name) + "\",rawName:\"" + (dir.rawName) + "\"" + (dir.value ? (",value:(" + (dir.value) + "),expression:" + (JSON.stringify(dir.value))) : '') + (dir.arg ? (",arg:\"" + (dir.arg) + "\"") : '') + (dir.modifiers ? (",modifiers:" + (JSON.stringify(dir.modifiers))) : '') + "},";
    }
  }
  if (hasRuntime) {
    return res.slice(0, -1) + ']'
  }
}

function genInlineTemplate (el, state) {
  var ast = el.children[0];
  if (process.env.NODE_ENV !== 'production' && (
    el.children.length > 1 || ast.type !== 1
  )) {
    state.warn('Inline-template components must have exactly one child element.');
  }
  if (ast.type === 1) {
    var inlineRenderFns = generate(ast, state.options);
    return ("inlineTemplate:{render:function(){" + (inlineRenderFns.render) + "},staticRenderFns:[" + (inlineRenderFns.staticRenderFns.map(function (code) { return ("function(){" + code + "}"); }).join(',')) + "]}")
  }
}

function genScopedSlots (
  slots,
  state
) {
  return ("scopedSlots:_u([" + (Object.keys(slots).map(function (key) {
      return genScopedSlot(key, slots[key], state)
    }).join(',')) + "])")
}

function genScopedSlot (
  key,
  el,
  state
) {
  if (el.for && !el.forProcessed) {
    return genForScopedSlot(key, el, state)
  }
  return "{key:" + key + ",fn:function(" + (String(el.attrsMap.scope)) + "){" +
    "return " + (el.tag === 'template'
      ? genChildren(el, state) || 'void 0'
      : genElement(el, state)) + "}}"
}

function genForScopedSlot (
  key,
  el,
  state
) {
  var exp = el.for;
  var alias = el.alias;
  var iterator1 = el.iterator1 ? ("," + (el.iterator1)) : '';
  var iterator2 = el.iterator2 ? ("," + (el.iterator2)) : '';
  el.forProcessed = true; // avoid recursion
  return "_l((" + exp + ")," +
    "function(" + alias + iterator1 + iterator2 + "){" +
      "return " + (genScopedSlot(key, el, state)) +
    '})'
}

function genChildren (
  el,
  state,
  checkSkip,
  altGenElement,
  altGenNode
) {
  var children = el.children;
  if (children.length) {
    var el$1 = children[0];
    // optimize single v-for
    if (children.length === 1 &&
      el$1.for &&
      el$1.tag !== 'template' &&
      el$1.tag !== 'slot'
    ) {
      return (altGenElement || genElement)(el$1, state)
    }
    var normalizationType = checkSkip
      ? getNormalizationType(children, state.maybeComponent)
      : 0;
    var gen = altGenNode || genNode;
    return ("[" + (children.map(function (c) { return gen(c, state); }).join(',')) + "]" + (normalizationType ? ("," + normalizationType) : ''))
  }
}

// determine the normalization needed for the children array.
// 0: no normalization needed
// 1: simple normalization needed (possible 1-level deep nested array)
// 2: full normalization needed
function getNormalizationType (
  children,
  maybeComponent
) {
  var res = 0;
  for (var i = 0; i < children.length; i++) {
    var el = children[i];
    if (el.type !== 1) {
      continue
    }
    if (needsNormalization(el) ||
        (el.ifConditions && el.ifConditions.some(function (c) { return needsNormalization(c.block); }))) {
      res = 2;
      break
    }
    if (maybeComponent(el) ||
        (el.ifConditions && el.ifConditions.some(function (c) { return maybeComponent(c.block); }))) {
      res = 1;
    }
  }
  return res
}

function needsNormalization (el) {
  return el.for !== undefined || el.tag === 'template' || el.tag === 'slot'
}

function genNode (node, state) {
  if (node.type === 1) {
    return genElement(node, state)
  } if (node.type === 3 && node.isComment) {
    return genComment(node)
  } else {
    return genText(node)
  }
}

function genText (text) {
  return ("_v(" + (text.type === 2
    ? text.expression // no need for () because already wrapped in _s()
    : transformSpecialNewlines(JSON.stringify(text.text))) + ")")
}

function genComment (comment) {
  return ("_e(" + (JSON.stringify(comment.text)) + ")")
}

function genSlot (el, state) {
  var slotName = el.slotName || '"default"';
  var children = genChildren(el, state);
  var res = "_t(" + slotName + (children ? ("," + children) : '');
  var attrs = el.attrs && ("{" + (el.attrs.map(function (a) { return ((camelize(a.name)) + ":" + (a.value)); }).join(',')) + "}");
  var bind$$1 = el.attrsMap['v-bind'];
  if ((attrs || bind$$1) && !children) {
    res += ",null";
  }
  if (attrs) {
    res += "," + attrs;
  }
  if (bind$$1) {
    res += (attrs ? '' : ',null') + "," + bind$$1;
  }
  return res + ')'
}

// componentName is el.component, take it as argument to shun flow's pessimistic refinement
function genComponent (
  componentName,
  el,
  state
) {
  var children = el.inlineTemplate ? null : genChildren(el, state, true);
  return ("_c(" + componentName + "," + (genData$2(el, state)) + (children ? ("," + children) : '') + ")")
}

function genProps (props) {
  var res = '';
  for (var i = 0; i < props.length; i++) {
    var prop = props[i];
    res += "\"" + (prop.name) + "\":" + (transformSpecialNewlines(prop.value)) + ",";
  }
  return res.slice(0, -1)
}

// #3895, #4268
function transformSpecialNewlines (text) {
  return text
    .replace(/\u2028/g, '\\u2028')
    .replace(/\u2029/g, '\\u2029')
}

/*  */

// these keywords should not appear inside expressions, but operators like
// typeof, instanceof and in are allowed
var prohibitedKeywordRE = new RegExp('\\b' + (
  'do,if,for,let,new,try,var,case,else,with,await,break,catch,class,const,' +
  'super,throw,while,yield,delete,export,import,return,switch,default,' +
  'extends,finally,continue,debugger,function,arguments'
).split(',').join('\\b|\\b') + '\\b');

// these unary operators should not be used as property/method names
var unaryOperatorsRE = new RegExp('\\b' + (
  'delete,typeof,void'
).split(',').join('\\s*\\([^\\)]*\\)|\\b') + '\\s*\\([^\\)]*\\)');

// check valid identifier for v-for
var identRE = /[A-Za-z_$][\w$]*/;

// strip strings in expressions
var stripStringRE = /'(?:[^'\\]|\\.)*'|"(?:[^"\\]|\\.)*"|`(?:[^`\\]|\\.)*\$\{|\}(?:[^`\\]|\\.)*`|`(?:[^`\\]|\\.)*`/g;

// detect problematic expressions in a template
function detectErrors (ast) {
  var errors = [];
  if (ast) {
    checkNode(ast, errors);
  }
  return errors
}

function checkNode (node, errors) {
  if (node.type === 1) {
    for (var name in node.attrsMap) {
      if (dirRE.test(name)) {
        var value = node.attrsMap[name];
        if (value) {
          if (name === 'v-for') {
            checkFor(node, ("v-for=\"" + value + "\""), errors);
          } else if (onRE.test(name)) {
            checkEvent(value, (name + "=\"" + value + "\""), errors);
          } else {
            checkExpression(value, (name + "=\"" + value + "\""), errors);
          }
        }
      }
    }
    if (node.children) {
      for (var i = 0; i < node.children.length; i++) {
        checkNode(node.children[i], errors);
      }
    }
  } else if (node.type === 2) {
    checkExpression(node.expression, node.text, errors);
  }
}

function checkEvent (exp, text, errors) {
  var stipped = exp.replace(stripStringRE, '');
  var keywordMatch = stipped.match(unaryOperatorsRE);
  if (keywordMatch && stipped.charAt(keywordMatch.index - 1) !== '$') {
    errors.push(
      "avoid using JavaScript unary operator as property name: " +
      "\"" + (keywordMatch[0]) + "\" in expression " + (text.trim())
    );
  }
  checkExpression(exp, text, errors);
}

function checkFor (node, text, errors) {
  checkExpression(node.for || '', text, errors);
  checkIdentifier(node.alias, 'v-for alias', text, errors);
  checkIdentifier(node.iterator1, 'v-for iterator', text, errors);
  checkIdentifier(node.iterator2, 'v-for iterator', text, errors);
}

function checkIdentifier (ident, type, text, errors) {
  if (typeof ident === 'string' && !identRE.test(ident)) {
    errors.push(("invalid " + type + " \"" + ident + "\" in expression: " + (text.trim())));
  }
}

function checkExpression (exp, text, errors) {
  try {
    new Function(("return " + exp));
  } catch (e) {
    var keywordMatch = exp.replace(stripStringRE, '').match(prohibitedKeywordRE);
    if (keywordMatch) {
      errors.push(
        "avoid using JavaScript keyword as property name: " +
        "\"" + (keywordMatch[0]) + "\" in expression " + (text.trim())
      );
    } else {
      errors.push(("invalid expression: " + (text.trim())));
    }
  }
}

/*  */

function createFunction (code, errors) {
  try {
    return new Function(code)
  } catch (err) {
    errors.push({ err: err, code: code });
    return noop
  }
}

function createCompileToFunctionFn (compile) {
  var cache = Object.create(null);

  return function compileToFunctions (
    template,
    options,
    vm
  ) {
    options = options || {};

    /* istanbul ignore if */
    if (process.env.NODE_ENV !== 'production') {
      // detect possible CSP restriction
      try {
        new Function('return 1');
      } catch (e) {
        if (e.toString().match(/unsafe-eval|CSP/)) {
          warn(
            'It seems you are using the standalone build of Vue.js in an ' +
            'environment with Content Security Policy that prohibits unsafe-eval. ' +
            'The template compiler cannot work in this environment. Consider ' +
            'relaxing the policy to allow unsafe-eval or pre-compiling your ' +
            'templates into render functions.'
          );
        }
      }
    }

    // check cache
    var key = options.delimiters
      ? String(options.delimiters) + template
      : template;
    if (cache[key]) {
      return cache[key]
    }

    // compile
    var compiled = compile(template, options);

    // check compilation errors/tips
    if (process.env.NODE_ENV !== 'production') {
      if (compiled.errors && compiled.errors.length) {
        warn(
          "Error compiling template:\n\n" + template + "\n\n" +
          compiled.errors.map(function (e) { return ("- " + e); }).join('\n') + '\n',
          vm
        );
      }
      if (compiled.tips && compiled.tips.length) {
        compiled.tips.forEach(function (msg) { return tip(msg, vm); });
      }
    }

    // turn code into functions
    var res = {};
    var fnGenErrors = [];
    res.render = createFunction(compiled.render, fnGenErrors);
    res.staticRenderFns = compiled.staticRenderFns.map(function (code) {
      return createFunction(code, fnGenErrors)
    });

    // check function generation errors.
    // this should only happen if there is a bug in the compiler itself.
    // mostly for codegen development use
    /* istanbul ignore if */
    if (process.env.NODE_ENV !== 'production') {
      if ((!compiled.errors || !compiled.errors.length) && fnGenErrors.length) {
        warn(
          "Failed to generate render function:\n\n" +
          fnGenErrors.map(function (ref) {
            var err = ref.err;
            var code = ref.code;

            return ((err.toString()) + " in\n\n" + code + "\n");
        }).join('\n'),
          vm
        );
      }
    }

    return (cache[key] = res)
  }
}

/*  */

function createCompilerCreator (baseCompile) {
  return function createCompiler (baseOptions) {
    function compile (
      template,
      options
    ) {
      var finalOptions = Object.create(baseOptions);
      var errors = [];
      var tips = [];
      finalOptions.warn = function (msg, tip) {
        (tip ? tips : errors).push(msg);
      };

      if (options) {
        // merge custom modules
        if (options.modules) {
          finalOptions.modules =
            (baseOptions.modules || []).concat(options.modules);
        }
        // merge custom directives
        if (options.directives) {
          finalOptions.directives = extend(
            Object.create(baseOptions.directives),
            options.directives
          );
        }
        // copy other options
        for (var key in options) {
          if (key !== 'modules' && key !== 'directives') {
            finalOptions[key] = options[key];
          }
        }
      }

      var compiled = baseCompile(template, finalOptions);
      if (process.env.NODE_ENV !== 'production') {
        errors.push.apply(errors, detectErrors(compiled.ast));
      }
      compiled.errors = errors;
      compiled.tips = tips;
      return compiled
    }

    return {
      compile: compile,
      compileToFunctions: createCompileToFunctionFn(compile)
    }
  }
}

/*  */

// `createCompilerCreator` allows creating compilers that use alternative
// parser/optimizer/codegen, e.g the SSR optimizing compiler.
// Here we just export a default compiler using the default parts.
var createCompiler = createCompilerCreator(function baseCompile (
  template,
  options
) {
  var ast = parse(template.trim(), options);
  optimize(ast, options);
  var code = generate(ast, options);
  return {
    ast: ast,
    render: code.render,
    staticRenderFns: code.staticRenderFns
  }
});

/*  */

var ref$1 = createCompiler(baseOptions);
var compileToFunctions = ref$1.compileToFunctions;

/*  */

var idToTemplate = cached(function (id) {
  var el = query(id);
  return el && el.innerHTML
});

var mount = Vue$3.prototype.$mount;
Vue$3.prototype.$mount = function (
  el,
  hydrating
) {
  el = el && query(el);

  /* istanbul ignore if */
  if (el === document.body || el === document.documentElement) {
    process.env.NODE_ENV !== 'production' && warn(
      "Do not mount Vue to <html> or <body> - mount to normal elements instead."
    );
    return this
  }

  var options = this.$options;
  // resolve template/el and convert to render function
  if (!options.render) {
    var template = options.template;
    if (template) {
      if (typeof template === 'string') {
        if (template.charAt(0) === '#') {
          template = idToTemplate(template);
          /* istanbul ignore if */
          if (process.env.NODE_ENV !== 'production' && !template) {
            warn(
              ("Template element not found or is empty: " + (options.template)),
              this
            );
          }
        }
      } else if (template.nodeType) {
        template = template.innerHTML;
      } else {
        if (process.env.NODE_ENV !== 'production') {
          warn('invalid template option:' + template, this);
        }
        return this
      }
    } else if (el) {
      template = getOuterHTML(el);
    }
    if (template) {
      /* istanbul ignore if */
      if (process.env.NODE_ENV !== 'production' && config.performance && mark) {
        mark('compile');
      }

      var ref = compileToFunctions(template, {
        shouldDecodeNewlines: shouldDecodeNewlines,
        delimiters: options.delimiters,
        comments: options.comments
      }, this);
      var render = ref.render;
      var staticRenderFns = ref.staticRenderFns;
      options.render = render;
      options.staticRenderFns = staticRenderFns;

      /* istanbul ignore if */
      if (process.env.NODE_ENV !== 'production' && config.performance && mark) {
        mark('compile end');
        measure(((this._name) + " compile"), 'compile', 'compile end');
      }
    }
  }
  return mount.call(this, el, hydrating)
};

/**
 * Get outerHTML of elements, taking care
 * of SVG elements in IE as well.
 */
function getOuterHTML (el) {
  if (el.outerHTML) {
    return el.outerHTML
  } else {
    var container = document.createElement('div');
    container.appendChild(el.cloneNode(true));
    return container.innerHTML
  }
}

Vue$3.compile = compileToFunctions;

/* harmony default export */ __webpack_exports__["default"] = (Vue$3);

/* WEBPACK VAR INJECTION */}.call(__webpack_exports__, __webpack_require__(12), __webpack_require__(35)))

/***/ }),
/* 12 */
/***/ (function(module, exports) {

// shim for using process in browser
var process = module.exports = {};

// cached from whatever global is present so that test runners that stub it
// don't break things.  But we need to wrap it in a try catch in case it is
// wrapped in strict mode code which doesn't define any globals.  It's inside a
// function because try/catches deoptimize in certain engines.

var cachedSetTimeout;
var cachedClearTimeout;

function defaultSetTimout() {
    throw new Error('setTimeout has not been defined');
}
function defaultClearTimeout () {
    throw new Error('clearTimeout has not been defined');
}
(function () {
    try {
        if (typeof setTimeout === 'function') {
            cachedSetTimeout = setTimeout;
        } else {
            cachedSetTimeout = defaultSetTimout;
        }
    } catch (e) {
        cachedSetTimeout = defaultSetTimout;
    }
    try {
        if (typeof clearTimeout === 'function') {
            cachedClearTimeout = clearTimeout;
        } else {
            cachedClearTimeout = defaultClearTimeout;
        }
    } catch (e) {
        cachedClearTimeout = defaultClearTimeout;
    }
} ())
function runTimeout(fun) {
    if (cachedSetTimeout === setTimeout) {
        //normal enviroments in sane situations
        return setTimeout(fun, 0);
    }
    // if setTimeout wasn't available but was latter defined
    if ((cachedSetTimeout === defaultSetTimout || !cachedSetTimeout) && setTimeout) {
        cachedSetTimeout = setTimeout;
        return setTimeout(fun, 0);
    }
    try {
        // when when somebody has screwed with setTimeout but no I.E. maddness
        return cachedSetTimeout(fun, 0);
    } catch(e){
        try {
            // When we are in I.E. but the script has been evaled so I.E. doesn't trust the global object when called normally
            return cachedSetTimeout.call(null, fun, 0);
        } catch(e){
            // same as above but when it's a version of I.E. that must have the global object for 'this', hopfully our context correct otherwise it will throw a global error
            return cachedSetTimeout.call(this, fun, 0);
        }
    }


}
function runClearTimeout(marker) {
    if (cachedClearTimeout === clearTimeout) {
        //normal enviroments in sane situations
        return clearTimeout(marker);
    }
    // if clearTimeout wasn't available but was latter defined
    if ((cachedClearTimeout === defaultClearTimeout || !cachedClearTimeout) && clearTimeout) {
        cachedClearTimeout = clearTimeout;
        return clearTimeout(marker);
    }
    try {
        // when when somebody has screwed with setTimeout but no I.E. maddness
        return cachedClearTimeout(marker);
    } catch (e){
        try {
            // When we are in I.E. but the script has been evaled so I.E. doesn't  trust the global object when called normally
            return cachedClearTimeout.call(null, marker);
        } catch (e){
            // same as above but when it's a version of I.E. that must have the global object for 'this', hopfully our context correct otherwise it will throw a global error.
            // Some versions of I.E. have different rules for clearTimeout vs setTimeout
            return cachedClearTimeout.call(this, marker);
        }
    }



}
var queue = [];
var draining = false;
var currentQueue;
var queueIndex = -1;

function cleanUpNextTick() {
    if (!draining || !currentQueue) {
        return;
    }
    draining = false;
    if (currentQueue.length) {
        queue = currentQueue.concat(queue);
    } else {
        queueIndex = -1;
    }
    if (queue.length) {
        drainQueue();
    }
}

function drainQueue() {
    if (draining) {
        return;
    }
    var timeout = runTimeout(cleanUpNextTick);
    draining = true;

    var len = queue.length;
    while(len) {
        currentQueue = queue;
        queue = [];
        while (++queueIndex < len) {
            if (currentQueue) {
                currentQueue[queueIndex].run();
            }
        }
        queueIndex = -1;
        len = queue.length;
    }
    currentQueue = null;
    draining = false;
    runClearTimeout(timeout);
}

process.nextTick = function (fun) {
    var args = new Array(arguments.length - 1);
    if (arguments.length > 1) {
        for (var i = 1; i < arguments.length; i++) {
            args[i - 1] = arguments[i];
        }
    }
    queue.push(new Item(fun, args));
    if (queue.length === 1 && !draining) {
        runTimeout(drainQueue);
    }
};

// v8 likes predictible objects
function Item(fun, array) {
    this.fun = fun;
    this.array = array;
}
Item.prototype.run = function () {
    this.fun.apply(null, this.array);
};
process.title = 'browser';
process.browser = true;
process.env = {};
process.argv = [];
process.version = ''; // empty string to avoid regexp issues
process.versions = {};

function noop() {}

process.on = noop;
process.addListener = noop;
process.once = noop;
process.off = noop;
process.removeListener = noop;
process.removeAllListeners = noop;
process.emit = noop;
process.prependListener = noop;
process.prependOnceListener = noop;

process.listeners = function (name) { return [] }

process.binding = function (name) {
    throw new Error('process.binding is not supported');
};

process.cwd = function () { return '/' };
process.chdir = function (dir) {
    throw new Error('process.chdir is not supported');
};
process.umask = function() { return 0; };


/***/ }),
/* 13 */
/***/ (function(module, exports) {

// 7.2.1 RequireObjectCoercible(argument)
module.exports = function (it) {
  if (it == undefined) throw TypeError("Can't call method on  " + it);
  return it;
};


/***/ }),
/* 14 */
/***/ (function(module, exports, __webpack_require__) {

// to indexed object, toObject with fallback for non-array-like ES3 strings
var IObject = __webpack_require__(50);
var defined = __webpack_require__(13);
module.exports = function (it) {
  return IObject(defined(it));
};


/***/ }),
/* 15 */
/***/ (function(module, exports) {

// 7.1.4 ToInteger
var ceil = Math.ceil;
var floor = Math.floor;
module.exports = function (it) {
  return isNaN(it = +it) ? 0 : (it > 0 ? floor : ceil)(it);
};


/***/ }),
/* 16 */
/***/ (function(module, exports, __webpack_require__) {

var shared = __webpack_require__(25)('keys');
var uid = __webpack_require__(26);
module.exports = function (key) {
  return shared[key] || (shared[key] = uid(key));
};


/***/ }),
/* 17 */
/***/ (function(module, exports, __webpack_require__) {

var anObject = __webpack_require__(8);
var IE8_DOM_DEFINE = __webpack_require__(57);
var toPrimitive = __webpack_require__(58);
var dP = Object.defineProperty;

exports.f = __webpack_require__(9) ? Object.defineProperty : function defineProperty(O, P, Attributes) {
  anObject(O);
  P = toPrimitive(P, true);
  anObject(Attributes);
  if (IE8_DOM_DEFINE) try {
    return dP(O, P, Attributes);
  } catch (e) { /* empty */ }
  if ('get' in Attributes || 'set' in Attributes) throw TypeError('Accessors not supported!');
  if ('value' in Attributes) O[P] = Attributes.value;
  return O;
};


/***/ }),
/* 18 */
/***/ (function(module, exports) {

module.exports = function (it) {
  return typeof it === 'object' ? it !== null : typeof it === 'function';
};


/***/ }),
/* 19 */
/***/ (function(module, exports) {

module.exports = function (exec) {
  try {
    return !!exec();
  } catch (e) {
    return true;
  }
};


/***/ }),
/* 20 */
/***/ (function(module, exports, __webpack_require__) {

module.exports = { "default": __webpack_require__(59), __esModule: true };

/***/ }),
/* 21 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
/* WEBPACK VAR INJECTION */(function(process) {/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "Store", function() { return Store; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "install", function() { return install; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "mapState", function() { return mapState; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "mapMutations", function() { return mapMutations; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "mapGetters", function() { return mapGetters; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "mapActions", function() { return mapActions; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "createNamespacedHelpers", function() { return createNamespacedHelpers; });
/**
 * vuex v2.4.1
 * (c) 2017 Evan You
 * @license MIT
 */
var applyMixin = function (Vue) {
  var version = Number(Vue.version.split('.')[0]);

  if (version >= 2) {
    Vue.mixin({ beforeCreate: vuexInit });
  } else {
    // override init and inject vuex init procedure
    // for 1.x backwards compatibility.
    var _init = Vue.prototype._init;
    Vue.prototype._init = function (options) {
      if ( options === void 0 ) options = {};

      options.init = options.init
        ? [vuexInit].concat(options.init)
        : vuexInit;
      _init.call(this, options);
    };
  }

  /**
   * Vuex init hook, injected into each instances init hooks list.
   */

  function vuexInit () {
    var options = this.$options;
    // store injection
    if (options.store) {
      this.$store = typeof options.store === 'function'
        ? options.store()
        : options.store;
    } else if (options.parent && options.parent.$store) {
      this.$store = options.parent.$store;
    }
  }
};

var devtoolHook =
  typeof window !== 'undefined' &&
  window.__VUE_DEVTOOLS_GLOBAL_HOOK__;

function devtoolPlugin (store) {
  if (!devtoolHook) { return }

  store._devtoolHook = devtoolHook;

  devtoolHook.emit('vuex:init', store);

  devtoolHook.on('vuex:travel-to-state', function (targetState) {
    store.replaceState(targetState);
  });

  store.subscribe(function (mutation, state) {
    devtoolHook.emit('vuex:mutation', mutation, state);
  });
}

/**
 * Get the first item that pass the test
 * by second argument function
 *
 * @param {Array} list
 * @param {Function} f
 * @return {*}
 */
/**
 * Deep copy the given object considering circular structure.
 * This function caches all nested objects and its copies.
 * If it detects circular structure, use cached copy to avoid infinite loop.
 *
 * @param {*} obj
 * @param {Array<Object>} cache
 * @return {*}
 */


/**
 * forEach for object
 */
function forEachValue (obj, fn) {
  Object.keys(obj).forEach(function (key) { return fn(obj[key], key); });
}

function isObject (obj) {
  return obj !== null && typeof obj === 'object'
}

function isPromise (val) {
  return val && typeof val.then === 'function'
}

function assert (condition, msg) {
  if (!condition) { throw new Error(("[vuex] " + msg)) }
}

var Module = function Module (rawModule, runtime) {
  this.runtime = runtime;
  this._children = Object.create(null);
  this._rawModule = rawModule;
  var rawState = rawModule.state;
  this.state = (typeof rawState === 'function' ? rawState() : rawState) || {};
};

var prototypeAccessors$1 = { namespaced: { configurable: true } };

prototypeAccessors$1.namespaced.get = function () {
  return !!this._rawModule.namespaced
};

Module.prototype.addChild = function addChild (key, module) {
  this._children[key] = module;
};

Module.prototype.removeChild = function removeChild (key) {
  delete this._children[key];
};

Module.prototype.getChild = function getChild (key) {
  return this._children[key]
};

Module.prototype.update = function update (rawModule) {
  this._rawModule.namespaced = rawModule.namespaced;
  if (rawModule.actions) {
    this._rawModule.actions = rawModule.actions;
  }
  if (rawModule.mutations) {
    this._rawModule.mutations = rawModule.mutations;
  }
  if (rawModule.getters) {
    this._rawModule.getters = rawModule.getters;
  }
};

Module.prototype.forEachChild = function forEachChild (fn) {
  forEachValue(this._children, fn);
};

Module.prototype.forEachGetter = function forEachGetter (fn) {
  if (this._rawModule.getters) {
    forEachValue(this._rawModule.getters, fn);
  }
};

Module.prototype.forEachAction = function forEachAction (fn) {
  if (this._rawModule.actions) {
    forEachValue(this._rawModule.actions, fn);
  }
};

Module.prototype.forEachMutation = function forEachMutation (fn) {
  if (this._rawModule.mutations) {
    forEachValue(this._rawModule.mutations, fn);
  }
};

Object.defineProperties( Module.prototype, prototypeAccessors$1 );

var ModuleCollection = function ModuleCollection (rawRootModule) {
  // register root module (Vuex.Store options)
  this.register([], rawRootModule, false);
};

ModuleCollection.prototype.get = function get (path) {
  return path.reduce(function (module, key) {
    return module.getChild(key)
  }, this.root)
};

ModuleCollection.prototype.getNamespace = function getNamespace (path) {
  var module = this.root;
  return path.reduce(function (namespace, key) {
    module = module.getChild(key);
    return namespace + (module.namespaced ? key + '/' : '')
  }, '')
};

ModuleCollection.prototype.update = function update$1 (rawRootModule) {
  update([], this.root, rawRootModule);
};

ModuleCollection.prototype.register = function register (path, rawModule, runtime) {
    var this$1 = this;
    if ( runtime === void 0 ) runtime = true;

  if (process.env.NODE_ENV !== 'production') {
    assertRawModule(path, rawModule);
  }

  var newModule = new Module(rawModule, runtime);
  if (path.length === 0) {
    this.root = newModule;
  } else {
    var parent = this.get(path.slice(0, -1));
    parent.addChild(path[path.length - 1], newModule);
  }

  // register nested modules
  if (rawModule.modules) {
    forEachValue(rawModule.modules, function (rawChildModule, key) {
      this$1.register(path.concat(key), rawChildModule, runtime);
    });
  }
};

ModuleCollection.prototype.unregister = function unregister (path) {
  var parent = this.get(path.slice(0, -1));
  var key = path[path.length - 1];
  if (!parent.getChild(key).runtime) { return }

  parent.removeChild(key);
};

function update (path, targetModule, newModule) {
  if (process.env.NODE_ENV !== 'production') {
    assertRawModule(path, newModule);
  }

  // update target module
  targetModule.update(newModule);

  // update nested modules
  if (newModule.modules) {
    for (var key in newModule.modules) {
      if (!targetModule.getChild(key)) {
        if (process.env.NODE_ENV !== 'production') {
          console.warn(
            "[vuex] trying to add a new module '" + key + "' on hot reloading, " +
            'manual reload is needed'
          );
        }
        return
      }
      update(
        path.concat(key),
        targetModule.getChild(key),
        newModule.modules[key]
      );
    }
  }
}

function assertRawModule (path, rawModule) {
  ['getters', 'actions', 'mutations'].forEach(function (key) {
    if (!rawModule[key]) { return }

    forEachValue(rawModule[key], function (value, type) {
      assert(
        typeof value === 'function',
        makeAssertionMessage(path, key, type, value)
      );
    });
  });
}

function makeAssertionMessage (path, key, type, value) {
  var buf = key + " should be function but \"" + key + "." + type + "\"";
  if (path.length > 0) {
    buf += " in module \"" + (path.join('.')) + "\"";
  }
  buf += " is " + (JSON.stringify(value)) + ".";

  return buf
}

var Vue; // bind on install

var Store = function Store (options) {
  var this$1 = this;
  if ( options === void 0 ) options = {};

  // Auto install if it is not done yet and `window` has `Vue`.
  // To allow users to avoid auto-installation in some cases,
  // this code should be placed here. See #731
  if (!Vue && typeof window !== 'undefined' && window.Vue) {
    install(window.Vue);
  }

  if (process.env.NODE_ENV !== 'production') {
    assert(Vue, "must call Vue.use(Vuex) before creating a store instance.");
    assert(typeof Promise !== 'undefined', "vuex requires a Promise polyfill in this browser.");
    assert(this instanceof Store, "Store must be called with the new operator.");
  }

  var plugins = options.plugins; if ( plugins === void 0 ) plugins = [];
  var strict = options.strict; if ( strict === void 0 ) strict = false;

  var state = options.state; if ( state === void 0 ) state = {};
  if (typeof state === 'function') {
    state = state();
  }

  // store internal state
  this._committing = false;
  this._actions = Object.create(null);
  this._mutations = Object.create(null);
  this._wrappedGetters = Object.create(null);
  this._modules = new ModuleCollection(options);
  this._modulesNamespaceMap = Object.create(null);
  this._subscribers = [];
  this._watcherVM = new Vue();

  // bind commit and dispatch to self
  var store = this;
  var ref = this;
  var dispatch = ref.dispatch;
  var commit = ref.commit;
  this.dispatch = function boundDispatch (type, payload) {
    return dispatch.call(store, type, payload)
  };
  this.commit = function boundCommit (type, payload, options) {
    return commit.call(store, type, payload, options)
  };

  // strict mode
  this.strict = strict;

  // init root module.
  // this also recursively registers all sub-modules
  // and collects all module getters inside this._wrappedGetters
  installModule(this, state, [], this._modules.root);

  // initialize the store vm, which is responsible for the reactivity
  // (also registers _wrappedGetters as computed properties)
  resetStoreVM(this, state);

  // apply plugins
  plugins.forEach(function (plugin) { return plugin(this$1); });

  if (Vue.config.devtools) {
    devtoolPlugin(this);
  }
};

var prototypeAccessors = { state: { configurable: true } };

prototypeAccessors.state.get = function () {
  return this._vm._data.$$state
};

prototypeAccessors.state.set = function (v) {
  if (process.env.NODE_ENV !== 'production') {
    assert(false, "Use store.replaceState() to explicit replace store state.");
  }
};

Store.prototype.commit = function commit (_type, _payload, _options) {
    var this$1 = this;

  // check object-style commit
  var ref = unifyObjectStyle(_type, _payload, _options);
    var type = ref.type;
    var payload = ref.payload;
    var options = ref.options;

  var mutation = { type: type, payload: payload };
  var entry = this._mutations[type];
  if (!entry) {
    if (process.env.NODE_ENV !== 'production') {
      console.error(("[vuex] unknown mutation type: " + type));
    }
    return
  }
  this._withCommit(function () {
    entry.forEach(function commitIterator (handler) {
      handler(payload);
    });
  });
  this._subscribers.forEach(function (sub) { return sub(mutation, this$1.state); });

  if (
    process.env.NODE_ENV !== 'production' &&
    options && options.silent
  ) {
    console.warn(
      "[vuex] mutation type: " + type + ". Silent option has been removed. " +
      'Use the filter functionality in the vue-devtools'
    );
  }
};

Store.prototype.dispatch = function dispatch (_type, _payload) {
  // check object-style dispatch
  var ref = unifyObjectStyle(_type, _payload);
    var type = ref.type;
    var payload = ref.payload;

  var entry = this._actions[type];
  if (!entry) {
    if (process.env.NODE_ENV !== 'production') {
      console.error(("[vuex] unknown action type: " + type));
    }
    return
  }
  return entry.length > 1
    ? Promise.all(entry.map(function (handler) { return handler(payload); }))
    : entry[0](payload)
};

Store.prototype.subscribe = function subscribe (fn) {
  var subs = this._subscribers;
  if (subs.indexOf(fn) < 0) {
    subs.push(fn);
  }
  return function () {
    var i = subs.indexOf(fn);
    if (i > -1) {
      subs.splice(i, 1);
    }
  }
};

Store.prototype.watch = function watch (getter, cb, options) {
    var this$1 = this;

  if (process.env.NODE_ENV !== 'production') {
    assert(typeof getter === 'function', "store.watch only accepts a function.");
  }
  return this._watcherVM.$watch(function () { return getter(this$1.state, this$1.getters); }, cb, options)
};

Store.prototype.replaceState = function replaceState (state) {
    var this$1 = this;

  this._withCommit(function () {
    this$1._vm._data.$$state = state;
  });
};

Store.prototype.registerModule = function registerModule (path, rawModule) {
  if (typeof path === 'string') { path = [path]; }

  if (process.env.NODE_ENV !== 'production') {
    assert(Array.isArray(path), "module path must be a string or an Array.");
    assert(path.length > 0, 'cannot register the root module by using registerModule.');
  }

  this._modules.register(path, rawModule);
  installModule(this, this.state, path, this._modules.get(path));
  // reset store to update getters...
  resetStoreVM(this, this.state);
};

Store.prototype.unregisterModule = function unregisterModule (path) {
    var this$1 = this;

  if (typeof path === 'string') { path = [path]; }

  if (process.env.NODE_ENV !== 'production') {
    assert(Array.isArray(path), "module path must be a string or an Array.");
  }

  this._modules.unregister(path);
  this._withCommit(function () {
    var parentState = getNestedState(this$1.state, path.slice(0, -1));
    Vue.delete(parentState, path[path.length - 1]);
  });
  resetStore(this);
};

Store.prototype.hotUpdate = function hotUpdate (newOptions) {
  this._modules.update(newOptions);
  resetStore(this, true);
};

Store.prototype._withCommit = function _withCommit (fn) {
  var committing = this._committing;
  this._committing = true;
  fn();
  this._committing = committing;
};

Object.defineProperties( Store.prototype, prototypeAccessors );

function resetStore (store, hot) {
  store._actions = Object.create(null);
  store._mutations = Object.create(null);
  store._wrappedGetters = Object.create(null);
  store._modulesNamespaceMap = Object.create(null);
  var state = store.state;
  // init all modules
  installModule(store, state, [], store._modules.root, true);
  // reset vm
  resetStoreVM(store, state, hot);
}

function resetStoreVM (store, state, hot) {
  var oldVm = store._vm;

  // bind store public getters
  store.getters = {};
  var wrappedGetters = store._wrappedGetters;
  var computed = {};
  forEachValue(wrappedGetters, function (fn, key) {
    // use computed to leverage its lazy-caching mechanism
    computed[key] = function () { return fn(store); };
    Object.defineProperty(store.getters, key, {
      get: function () { return store._vm[key]; },
      enumerable: true // for local getters
    });
  });

  // use a Vue instance to store the state tree
  // suppress warnings just in case the user has added
  // some funky global mixins
  var silent = Vue.config.silent;
  Vue.config.silent = true;
  store._vm = new Vue({
    data: {
      $$state: state
    },
    computed: computed
  });
  Vue.config.silent = silent;

  // enable strict mode for new vm
  if (store.strict) {
    enableStrictMode(store);
  }

  if (oldVm) {
    if (hot) {
      // dispatch changes in all subscribed watchers
      // to force getter re-evaluation for hot reloading.
      store._withCommit(function () {
        oldVm._data.$$state = null;
      });
    }
    Vue.nextTick(function () { return oldVm.$destroy(); });
  }
}

function installModule (store, rootState, path, module, hot) {
  var isRoot = !path.length;
  var namespace = store._modules.getNamespace(path);

  // register in namespace map
  if (module.namespaced) {
    store._modulesNamespaceMap[namespace] = module;
  }

  // set state
  if (!isRoot && !hot) {
    var parentState = getNestedState(rootState, path.slice(0, -1));
    var moduleName = path[path.length - 1];
    store._withCommit(function () {
      Vue.set(parentState, moduleName, module.state);
    });
  }

  var local = module.context = makeLocalContext(store, namespace, path);

  module.forEachMutation(function (mutation, key) {
    var namespacedType = namespace + key;
    registerMutation(store, namespacedType, mutation, local);
  });

  module.forEachAction(function (action, key) {
    var namespacedType = namespace + key;
    registerAction(store, namespacedType, action, local);
  });

  module.forEachGetter(function (getter, key) {
    var namespacedType = namespace + key;
    registerGetter(store, namespacedType, getter, local);
  });

  module.forEachChild(function (child, key) {
    installModule(store, rootState, path.concat(key), child, hot);
  });
}

/**
 * make localized dispatch, commit, getters and state
 * if there is no namespace, just use root ones
 */
function makeLocalContext (store, namespace, path) {
  var noNamespace = namespace === '';

  var local = {
    dispatch: noNamespace ? store.dispatch : function (_type, _payload, _options) {
      var args = unifyObjectStyle(_type, _payload, _options);
      var payload = args.payload;
      var options = args.options;
      var type = args.type;

      if (!options || !options.root) {
        type = namespace + type;
        if (process.env.NODE_ENV !== 'production' && !store._actions[type]) {
          console.error(("[vuex] unknown local action type: " + (args.type) + ", global type: " + type));
          return
        }
      }

      return store.dispatch(type, payload)
    },

    commit: noNamespace ? store.commit : function (_type, _payload, _options) {
      var args = unifyObjectStyle(_type, _payload, _options);
      var payload = args.payload;
      var options = args.options;
      var type = args.type;

      if (!options || !options.root) {
        type = namespace + type;
        if (process.env.NODE_ENV !== 'production' && !store._mutations[type]) {
          console.error(("[vuex] unknown local mutation type: " + (args.type) + ", global type: " + type));
          return
        }
      }

      store.commit(type, payload, options);
    }
  };

  // getters and state object must be gotten lazily
  // because they will be changed by vm update
  Object.defineProperties(local, {
    getters: {
      get: noNamespace
        ? function () { return store.getters; }
        : function () { return makeLocalGetters(store, namespace); }
    },
    state: {
      get: function () { return getNestedState(store.state, path); }
    }
  });

  return local
}

function makeLocalGetters (store, namespace) {
  var gettersProxy = {};

  var splitPos = namespace.length;
  Object.keys(store.getters).forEach(function (type) {
    // skip if the target getter is not match this namespace
    if (type.slice(0, splitPos) !== namespace) { return }

    // extract local getter type
    var localType = type.slice(splitPos);

    // Add a port to the getters proxy.
    // Define as getter property because
    // we do not want to evaluate the getters in this time.
    Object.defineProperty(gettersProxy, localType, {
      get: function () { return store.getters[type]; },
      enumerable: true
    });
  });

  return gettersProxy
}

function registerMutation (store, type, handler, local) {
  var entry = store._mutations[type] || (store._mutations[type] = []);
  entry.push(function wrappedMutationHandler (payload) {
    handler.call(store, local.state, payload);
  });
}

function registerAction (store, type, handler, local) {
  var entry = store._actions[type] || (store._actions[type] = []);
  entry.push(function wrappedActionHandler (payload, cb) {
    var res = handler.call(store, {
      dispatch: local.dispatch,
      commit: local.commit,
      getters: local.getters,
      state: local.state,
      rootGetters: store.getters,
      rootState: store.state
    }, payload, cb);
    if (!isPromise(res)) {
      res = Promise.resolve(res);
    }
    if (store._devtoolHook) {
      return res.catch(function (err) {
        store._devtoolHook.emit('vuex:error', err);
        throw err
      })
    } else {
      return res
    }
  });
}

function registerGetter (store, type, rawGetter, local) {
  if (store._wrappedGetters[type]) {
    if (process.env.NODE_ENV !== 'production') {
      console.error(("[vuex] duplicate getter key: " + type));
    }
    return
  }
  store._wrappedGetters[type] = function wrappedGetter (store) {
    return rawGetter(
      local.state, // local state
      local.getters, // local getters
      store.state, // root state
      store.getters // root getters
    )
  };
}

function enableStrictMode (store) {
  store._vm.$watch(function () { return this._data.$$state }, function () {
    if (process.env.NODE_ENV !== 'production') {
      assert(store._committing, "Do not mutate vuex store state outside mutation handlers.");
    }
  }, { deep: true, sync: true });
}

function getNestedState (state, path) {
  return path.length
    ? path.reduce(function (state, key) { return state[key]; }, state)
    : state
}

function unifyObjectStyle (type, payload, options) {
  if (isObject(type) && type.type) {
    options = payload;
    payload = type;
    type = type.type;
  }

  if (process.env.NODE_ENV !== 'production') {
    assert(typeof type === 'string', ("Expects string as the type, but found " + (typeof type) + "."));
  }

  return { type: type, payload: payload, options: options }
}

function install (_Vue) {
  if (Vue && _Vue === Vue) {
    if (process.env.NODE_ENV !== 'production') {
      console.error(
        '[vuex] already installed. Vue.use(Vuex) should be called only once.'
      );
    }
    return
  }
  Vue = _Vue;
  applyMixin(Vue);
}

var mapState = normalizeNamespace(function (namespace, states) {
  var res = {};
  normalizeMap(states).forEach(function (ref) {
    var key = ref.key;
    var val = ref.val;

    res[key] = function mappedState () {
      var state = this.$store.state;
      var getters = this.$store.getters;
      if (namespace) {
        var module = getModuleByNamespace(this.$store, 'mapState', namespace);
        if (!module) {
          return
        }
        state = module.context.state;
        getters = module.context.getters;
      }
      return typeof val === 'function'
        ? val.call(this, state, getters)
        : state[val]
    };
    // mark vuex getter for devtools
    res[key].vuex = true;
  });
  return res
});

var mapMutations = normalizeNamespace(function (namespace, mutations) {
  var res = {};
  normalizeMap(mutations).forEach(function (ref) {
    var key = ref.key;
    var val = ref.val;

    res[key] = function mappedMutation () {
      var args = [], len = arguments.length;
      while ( len-- ) args[ len ] = arguments[ len ];

      var commit = this.$store.commit;
      if (namespace) {
        var module = getModuleByNamespace(this.$store, 'mapMutations', namespace);
        if (!module) {
          return
        }
        commit = module.context.commit;
      }
      return typeof val === 'function'
        ? val.apply(this, [commit].concat(args))
        : commit.apply(this.$store, [val].concat(args))
    };
  });
  return res
});

var mapGetters = normalizeNamespace(function (namespace, getters) {
  var res = {};
  normalizeMap(getters).forEach(function (ref) {
    var key = ref.key;
    var val = ref.val;

    val = namespace + val;
    res[key] = function mappedGetter () {
      if (namespace && !getModuleByNamespace(this.$store, 'mapGetters', namespace)) {
        return
      }
      if (process.env.NODE_ENV !== 'production' && !(val in this.$store.getters)) {
        console.error(("[vuex] unknown getter: " + val));
        return
      }
      return this.$store.getters[val]
    };
    // mark vuex getter for devtools
    res[key].vuex = true;
  });
  return res
});

var mapActions = normalizeNamespace(function (namespace, actions) {
  var res = {};
  normalizeMap(actions).forEach(function (ref) {
    var key = ref.key;
    var val = ref.val;

    res[key] = function mappedAction () {
      var args = [], len = arguments.length;
      while ( len-- ) args[ len ] = arguments[ len ];

      var dispatch = this.$store.dispatch;
      if (namespace) {
        var module = getModuleByNamespace(this.$store, 'mapActions', namespace);
        if (!module) {
          return
        }
        dispatch = module.context.dispatch;
      }
      return typeof val === 'function'
        ? val.apply(this, [dispatch].concat(args))
        : dispatch.apply(this.$store, [val].concat(args))
    };
  });
  return res
});

var createNamespacedHelpers = function (namespace) { return ({
  mapState: mapState.bind(null, namespace),
  mapGetters: mapGetters.bind(null, namespace),
  mapMutations: mapMutations.bind(null, namespace),
  mapActions: mapActions.bind(null, namespace)
}); };

function normalizeMap (map) {
  return Array.isArray(map)
    ? map.map(function (key) { return ({ key: key, val: key }); })
    : Object.keys(map).map(function (key) { return ({ key: key, val: map[key] }); })
}

function normalizeNamespace (fn) {
  return function (namespace, map) {
    if (typeof namespace !== 'string') {
      map = namespace;
      namespace = '';
    } else if (namespace.charAt(namespace.length - 1) !== '/') {
      namespace += '/';
    }
    return fn(namespace, map)
  }
}

function getModuleByNamespace (store, helper, namespace) {
  var module = store._modulesNamespaceMap[namespace];
  if (process.env.NODE_ENV !== 'production' && !module) {
    console.error(("[vuex] module namespace not found in " + helper + "(): " + namespace));
  }
  return module
}

var index_esm = {
  Store: Store,
  install: install,
  version: '2.4.1',
  mapState: mapState,
  mapMutations: mapMutations,
  mapGetters: mapGetters,
  mapActions: mapActions,
  createNamespacedHelpers: createNamespacedHelpers
};


/* harmony default export */ __webpack_exports__["default"] = (index_esm);

/* WEBPACK VAR INJECTION */}.call(__webpack_exports__, __webpack_require__(12)))

/***/ }),
/* 22 */
/***/ (function(module, exports, __webpack_require__) {

// 7.1.13 ToObject(argument)
var defined = __webpack_require__(13);
module.exports = function (it) {
  return Object(defined(it));
};


/***/ }),
/* 23 */
/***/ (function(module, exports, __webpack_require__) {

// 19.1.2.14 / 15.2.3.14 Object.keys(O)
var $keys = __webpack_require__(49);
var enumBugKeys = __webpack_require__(27);

module.exports = Object.keys || function keys(O) {
  return $keys(O, enumBugKeys);
};


/***/ }),
/* 24 */
/***/ (function(module, exports) {

var toString = {}.toString;

module.exports = function (it) {
  return toString.call(it).slice(8, -1);
};


/***/ }),
/* 25 */
/***/ (function(module, exports, __webpack_require__) {

var global = __webpack_require__(2);
var SHARED = '__core-js_shared__';
var store = global[SHARED] || (global[SHARED] = {});
module.exports = function (key) {
  return store[key] || (store[key] = {});
};


/***/ }),
/* 26 */
/***/ (function(module, exports) {

var id = 0;
var px = Math.random();
module.exports = function (key) {
  return 'Symbol('.concat(key === undefined ? '' : key, ')_', (++id + px).toString(36));
};


/***/ }),
/* 27 */
/***/ (function(module, exports) {

// IE 8- don't enum bug keys
module.exports = (
  'constructor,hasOwnProperty,isPrototypeOf,propertyIsEnumerable,toLocaleString,toString,valueOf'
).split(',');


/***/ }),
/* 28 */
/***/ (function(module, exports, __webpack_require__) {

var global = __webpack_require__(2);
var core = __webpack_require__(3);
var ctx = __webpack_require__(55);
var hide = __webpack_require__(5);
var PROTOTYPE = 'prototype';

var $export = function (type, name, source) {
  var IS_FORCED = type & $export.F;
  var IS_GLOBAL = type & $export.G;
  var IS_STATIC = type & $export.S;
  var IS_PROTO = type & $export.P;
  var IS_BIND = type & $export.B;
  var IS_WRAP = type & $export.W;
  var exports = IS_GLOBAL ? core : core[name] || (core[name] = {});
  var expProto = exports[PROTOTYPE];
  var target = IS_GLOBAL ? global : IS_STATIC ? global[name] : (global[name] || {})[PROTOTYPE];
  var key, own, out;
  if (IS_GLOBAL) source = name;
  for (key in source) {
    // contains in native
    own = !IS_FORCED && target && target[key] !== undefined;
    if (own && key in exports) continue;
    // export native or passed
    out = own ? target[key] : source[key];
    // prevent global pollution for namespaces
    exports[key] = IS_GLOBAL && typeof target[key] != 'function' ? source[key]
    // bind timers to global for call from export context
    : IS_BIND && own ? ctx(out, global)
    // wrap global constructors for prevent change them in library
    : IS_WRAP && target[key] == out ? (function (C) {
      var F = function (a, b, c) {
        if (this instanceof C) {
          switch (arguments.length) {
            case 0: return new C();
            case 1: return new C(a);
            case 2: return new C(a, b);
          } return new C(a, b, c);
        } return C.apply(this, arguments);
      };
      F[PROTOTYPE] = C[PROTOTYPE];
      return F;
    // make static versions for prototype methods
    })(out) : IS_PROTO && typeof out == 'function' ? ctx(Function.call, out) : out;
    // export proto methods to core.%CONSTRUCTOR%.methods.%NAME%
    if (IS_PROTO) {
      (exports.virtual || (exports.virtual = {}))[key] = out;
      // export proto methods to core.%CONSTRUCTOR%.prototype.%NAME%
      if (type & $export.R && expProto && !expProto[key]) hide(expProto, key, out);
    }
  }
};
// type bitmap
$export.F = 1;   // forced
$export.G = 2;   // global
$export.S = 4;   // static
$export.P = 8;   // proto
$export.B = 16;  // bind
$export.W = 32;  // wrap
$export.U = 64;  // safe
$export.R = 128; // real proto method for `library`
module.exports = $export;


/***/ }),
/* 29 */
/***/ (function(module, exports, __webpack_require__) {

var isObject = __webpack_require__(18);
var document = __webpack_require__(2).document;
// typeof document.createElement is 'object' in old IE
var is = isObject(document) && isObject(document.createElement);
module.exports = function (it) {
  return is ? document.createElement(it) : {};
};


/***/ }),
/* 30 */
/***/ (function(module, exports) {

module.exports = function (bitmap, value) {
  return {
    enumerable: !(bitmap & 1),
    configurable: !(bitmap & 2),
    writable: !(bitmap & 4),
    value: value
  };
};


/***/ }),
/* 31 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

var LIBRARY = __webpack_require__(64);
var $export = __webpack_require__(28);
var redefine = __webpack_require__(65);
var hide = __webpack_require__(5);
var has = __webpack_require__(7);
var Iterators = __webpack_require__(10);
var $iterCreate = __webpack_require__(66);
var setToStringTag = __webpack_require__(32);
var getPrototypeOf = __webpack_require__(70);
var ITERATOR = __webpack_require__(4)('iterator');
var BUGGY = !([].keys && 'next' in [].keys()); // Safari has buggy iterators w/o `next`
var FF_ITERATOR = '@@iterator';
var KEYS = 'keys';
var VALUES = 'values';

var returnThis = function () { return this; };

module.exports = function (Base, NAME, Constructor, next, DEFAULT, IS_SET, FORCED) {
  $iterCreate(Constructor, NAME, next);
  var getMethod = function (kind) {
    if (!BUGGY && kind in proto) return proto[kind];
    switch (kind) {
      case KEYS: return function keys() { return new Constructor(this, kind); };
      case VALUES: return function values() { return new Constructor(this, kind); };
    } return function entries() { return new Constructor(this, kind); };
  };
  var TAG = NAME + ' Iterator';
  var DEF_VALUES = DEFAULT == VALUES;
  var VALUES_BUG = false;
  var proto = Base.prototype;
  var $native = proto[ITERATOR] || proto[FF_ITERATOR] || DEFAULT && proto[DEFAULT];
  var $default = $native || getMethod(DEFAULT);
  var $entries = DEFAULT ? !DEF_VALUES ? $default : getMethod('entries') : undefined;
  var $anyNative = NAME == 'Array' ? proto.entries || $native : $native;
  var methods, key, IteratorPrototype;
  // Fix native
  if ($anyNative) {
    IteratorPrototype = getPrototypeOf($anyNative.call(new Base()));
    if (IteratorPrototype !== Object.prototype && IteratorPrototype.next) {
      // Set @@toStringTag to native iterators
      setToStringTag(IteratorPrototype, TAG, true);
      // fix for some old engines
      if (!LIBRARY && !has(IteratorPrototype, ITERATOR)) hide(IteratorPrototype, ITERATOR, returnThis);
    }
  }
  // fix Array#{values, @@iterator}.name in V8 / FF
  if (DEF_VALUES && $native && $native.name !== VALUES) {
    VALUES_BUG = true;
    $default = function values() { return $native.call(this); };
  }
  // Define iterator
  if ((!LIBRARY || FORCED) && (BUGGY || VALUES_BUG || !proto[ITERATOR])) {
    hide(proto, ITERATOR, $default);
  }
  // Plug for library
  Iterators[NAME] = $default;
  Iterators[TAG] = returnThis;
  if (DEFAULT) {
    methods = {
      values: DEF_VALUES ? $default : getMethod(VALUES),
      keys: IS_SET ? $default : getMethod(KEYS),
      entries: $entries
    };
    if (FORCED) for (key in methods) {
      if (!(key in proto)) redefine(proto, key, methods[key]);
    } else $export($export.P + $export.F * (BUGGY || VALUES_BUG), NAME, methods);
  }
  return methods;
};


/***/ }),
/* 32 */
/***/ (function(module, exports, __webpack_require__) {

var def = __webpack_require__(17).f;
var has = __webpack_require__(7);
var TAG = __webpack_require__(4)('toStringTag');

module.exports = function (it, tag, stat) {
  if (it && !has(it = stat ? it : it.prototype, TAG)) def(it, TAG, { configurable: true, value: tag });
};


/***/ }),
/* 33 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";
/* WEBPACK VAR INJECTION */(function(process) {

var Vue = __webpack_require__(11);
Vue = 'default' in Vue ? Vue['default'] : Vue;

var version = '2.1.0';

var compatible = (/^2\./).test(Vue.version);
if (!compatible) {
  Vue.util.warn('VueClickaway ' + version + ' only supports Vue 2.x, and does not support Vue ' + Vue.version);
}



// @SECTION: implementation

var HANDLER = '_vue_clickaway_handler';

function bind(el, binding) {
  unbind(el);

  var callback = binding.value;
  if (typeof callback !== 'function') {
    if (process.env.NODE_ENV !== 'production') {
      Vue.util.warn(
        'v-' + binding.name + '="' +
        binding.expression + '" expects a function value, ' +
        'got ' + callback
      );
    }
    return;
  }

  // @NOTE: Vue binds directives in microtasks, while UI events are dispatched
  //        in macrotasks. This causes the listener to be set up before
  //        the "origin" click event (the event that lead to the binding of
  //        the directive) arrives at the document root. To work around that,
  //        we ignore events until the end of the "initial" macrotask.
  // @REFERENCE: https://jakearchibald.com/2015/tasks-microtasks-queues-and-schedules/
  // @REFERENCE: https://github.com/simplesmiler/vue-clickaway/issues/8
  var initialMacrotaskEnded = false;
  setTimeout(function() {
    initialMacrotaskEnded = true;
  }, 0);

  el[HANDLER] = function(ev) {
    // @NOTE: IE 5.0+
    // @REFERENCE: https://developer.mozilla.org/en/docs/Web/API/Node/contains
    if (initialMacrotaskEnded && !el.contains(ev.target)) {
      return callback(ev);
    }
  };

  document.documentElement.addEventListener('click', el[HANDLER], false);
}

function unbind(el) {
  document.documentElement.removeEventListener('click', el[HANDLER], false);
  delete el[HANDLER];
}

var directive = {
  bind: bind,
  update: function(el, binding) {
    if (binding.value === binding.oldValue) return;
    bind(el, binding);
  },
  unbind: unbind,
};

var mixin = {
  directives: { onClickaway: directive },
};

exports.version = version;
exports.directive = directive;
exports.mixin = mixin;
/* WEBPACK VAR INJECTION */}.call(exports, __webpack_require__(12)))

/***/ }),
/* 34 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _vue = __webpack_require__(11);

var _vue2 = _interopRequireDefault(_vue);

var _rop_store = __webpack_require__(36);

var _rop_store2 = _interopRequireDefault(_rop_store);

var _mainPagePanel = __webpack_require__(39);

var _mainPagePanel2 = _interopRequireDefault(_mainPagePanel);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

window.onload = function () {
	var RopApp = new _vue2.default({
		el: '#rop_core',
		store: _rop_store2.default,
		created: function created() {
			_rop_store2.default.dispatch('getGeneralSettings');
			_rop_store2.default.dispatch('fetchAvailableServices');
			_rop_store2.default.dispatch('fetchAuthenticatedServices');
			_rop_store2.default.dispatch('fetchActiveAccounts');
			_rop_store2.default.dispatch('fetchQueue');
		},

		components: {
			MainPagePanel: _mainPagePanel2.default
		}
	});
}; /* eslint no-unused-vars: 0 */
/* exported RopApp */

/***/ }),
/* 35 */
/***/ (function(module, exports) {

var g;

// This works in non-strict mode
g = (function() {
	return this;
})();

try {
	// This works if eval is allowed (see CSP)
	g = g || Function("return this")() || (1,eval)("this");
} catch(e) {
	// This works if the window reference is available
	if(typeof window === "object")
		g = window;
}

// g can still be undefined, but nothing to do about it...
// We return undefined, instead of nothing here, so it's
// easier to handle this case. if(!global) { ...}

module.exports = g;


/***/ }),
/* 36 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
	value: true
});

var _vue = __webpack_require__(11);

var _vue2 = _interopRequireDefault(_vue);

var _vuex = __webpack_require__(21);

var _vuex2 = _interopRequireDefault(_vuex);

var _vueResource = __webpack_require__(37);

var _vueResource2 = _interopRequireDefault(_vueResource);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

_vue2.default.use(_vuex2.default); /* global ropApiSettings */

_vue2.default.use(_vueResource2.default);

exports.default = new _vuex2.default.Store({
	state: {
		page: {
			debug: true,
			logs: '### Here starts the log \n\n',
			// view: 'accounts'
			// view: 'post-format'
			// view: 'settings'
			// view: 'schedule'
			view: 'queue'
		},
		auth_in_progress: false,
		displayTabs: [{
			name: 'Accounts',
			slug: 'accounts',
			isActive: true
		}, {
			name: 'General Settings',
			slug: 'settings',
			isActive: false
		}, {
			name: 'Post Format',
			slug: 'post-format',
			isActive: false
		}, {
			name: 'Custom Schedule',
			slug: 'schedule',
			isActive: false
		}, {
			name: 'Sharing Queue',
			slug: 'queue',
			isActive: false
		}, {
			name: 'Logs',
			slug: 'logs',
			isActive: false
		}],
		generalSettings: [],
		availableServices: [],
		authenticatedServices: [],
		activeAccounts: [],
		activePostFormat: [],
		activeSchedule: [],
		queue: []
	},
	getters: {
		getServices: function getServices(state) {
			return state.availableServices;
		},
		getActiveAccounts: function getActiveAccounts(state) {
			return state.activeAccounts;
		},
		getPostFormat: function getPostFormat(state) {
			return state.activePostFormat;
		}
	},
	mutations: {
		logMessage: function logMessage(state, data) {
			var message = data;
			var type = '';

			if (data.constructor === Array) {
				message = data[0];
			}

			if (data.length === 2) {
				type = data[1];
			}

			if (type === '' || type === undefined) {
				type = 'notice';
			}

			var status = '[' + type.toUpperCase() + ']';

			if (state.page.debug === true) {
				console.log(message);
			}
			message = status.concat(' ').concat(message);
			state.page.logs = state.page.logs.concat(message + '\n');
		},
		setTabView: function setTabView(state, view) {
			for (var tab in state.displayTabs) {
				state.displayTabs[tab].isActive = false;
				if (state.displayTabs[tab].slug === view) {
					state.displayTabs[tab].isActive = true;
					state.page.view = view;
				}
			}
		},
		updateAuthProgress: function updateAuthProgress(state, data) {
			if (state.auth_in_progress === true) {
				state.auth_in_progress = false;
			}
		},
		updateAvailableServices: function updateAvailableServices(state, data) {
			state.availableServices = data;
		},
		updateAuthenticatedServices: function updateAuthenticatedServices(state, data) {
			state.authenticatedServices = data;
		},
		updateActiveAccounts: function updateActiveAccounts(state, data) {
			state.activeAccounts = data;
		},
		updateGeneralSettings: function updateGeneralSettings(state, data) {
			state.generalSettings = data;
		},
		updateSelectedPostTypes: function updateSelectedPostTypes(state, data) {
			state.generalSettings.selected_post_types = data;
			for (var index in state.generalSettings.available_post_types) {
				state.generalSettings.available_post_types[index].selected = false;
				for (var indexSelected in data) {
					if (state.generalSettings.available_post_types[index].value === data[indexSelected].value) {
						state.generalSettings.available_post_types[index].selected = true;
					}
				}
			}
		},
		updateAvailableTaxonomies: function updateAvailableTaxonomies(state, data) {
			state.generalSettings.available_taxonomies = data;
		},
		updateSelectedTaxonomies: function updateSelectedTaxonomies(state, data) {
			state.generalSettings.selected_taxonomies = data;
			for (var index in state.generalSettings.available_taxonomies) {
				state.generalSettings.available_taxonomies[index].selected = false;
				for (var indexSelected in data) {
					if (state.generalSettings.available_taxonomies[index].value === data[indexSelected].value || state.generalSettings.available_taxonomies[index].parent === data[indexSelected].value) {
						state.generalSettings.available_taxonomies[index].selected = true;
					}
				}
			}
		},
		updateAvailablePosts: function updateAvailablePosts(state, data) {
			state.generalSettings.available_posts = data;
		},
		updateSelectedPosts: function updateSelectedPosts(state, data) {
			state.generalSettings.selected_posts = data;
		},
		updatePostFormat: function updatePostFormat(state, data) {
			state.activePostFormat = data;
		},
		updatePostFormatShortnerCredentials: function updatePostFormatShortnerCredentials(state, data) {
			state.activePostFormat['shortner_credentials'] = data;
		},
		updateSchedule: function updateSchedule(state, data) {
			state.activeSchedule = data;
		},
		updateQueue: function updateQueue(state, data) {
			state.queue = data;
		}
	},
	actions: {
		fetchAvailableServices: function fetchAvailableServices(_ref) {
			var commit = _ref.commit;

			_vue2.default.http({
				url: ropApiSettings.root,
				method: 'POST',
				headers: { 'X-WP-Nonce': ropApiSettings.nonce },
				params: { 'req': 'available_services' },
				responseType: 'json'
			}).then(function (response) {
				commit('updateAvailableServices', response.data);
				commit('logMessage', ['Fetching available services.', 'success']);
			}, function () {
				commit('logMessage', ['Error retrieving available services.', 'error']);
			});
		},
		getServiceSignInUrl: function getServiceSignInUrl(_ref2, data) {
			var commit = _ref2.commit;

			return new Promise(function (resolve, reject) {
				_vue2.default.http({
					url: ropApiSettings.root,
					method: 'POST',
					headers: { 'X-WP-Nonce': ropApiSettings.nonce },
					params: { 'req': 'service_sign_in_url' },
					body: data,
					responseType: 'json'
				}).then(function (response) {
					resolve(response.data);
				}, function (error) {
					reject(error);
					commit('logMessage', ['Error retrieving active accounts.', 'error']);
				});
			});
		},
		fetchAuthenticatedServices: function fetchAuthenticatedServices(_ref3) {
			var commit = _ref3.commit;

			_vue2.default.http({
				url: ropApiSettings.root,
				method: 'POST',
				headers: { 'X-WP-Nonce': ropApiSettings.nonce },
				params: { 'req': 'authenticated_services' },
				responseType: 'json'
			}).then(function (response) {
				commit('updateAuthenticatedServices', response.data);
			}, function () {
				commit('logMessage', ['Error retrieving authenticated services.', 'error']);
			});
		},
		fetchActiveAccounts: function fetchActiveAccounts(_ref4) {
			var commit = _ref4.commit;

			_vue2.default.http({
				url: ropApiSettings.root,
				method: 'POST',
				headers: { 'X-WP-Nonce': ropApiSettings.nonce },
				params: { 'req': 'active_accounts' },
				responseType: 'json'
			}).then(function (response) {
				commit('updateActiveAccounts', response.data);
			}, function () {
				commit('logMessage', ['Error retrieving active accounts.', 'error']);
			});
		},
		updateActiveAccounts: function updateActiveAccounts(_ref5, data) {
			var commit = _ref5.commit;

			if (data.action === 'update') {
				_vue2.default.http({
					url: ropApiSettings.root,
					method: 'POST',
					headers: { 'X-WP-Nonce': ropApiSettings.nonce },
					params: { 'req': 'update_accounts' },
					body: data,
					responseType: 'json'
				}).then(function (response) {
					commit('updateActiveAccounts', response.data);
				}, function () {
					commit('logMessage', ['Error when trying to update active accounts.', 'error']);
				});
			} else if (data.action === 'remove') {
				_vue2.default.http({
					url: ropApiSettings.root,
					method: 'POST',
					headers: { 'X-WP-Nonce': ropApiSettings.nonce },
					params: { 'req': 'remove_account' },
					body: data,
					responseType: 'json'
				}).then(function (response) {
					commit('updateActiveAccounts', response.data);
				}, function () {
					commit('logMessage', ['Error when trying to remove and update active accounts.', 'error']);
				});
			} else {
				console.log('No valid action specified.');
			}
		},
		authenticateService: function authenticateService(_ref6, data) {
			var commit = _ref6.commit;

			_vue2.default.http({
				url: ropApiSettings.root,
				method: 'POST',
				headers: { 'X-WP-Nonce': ropApiSettings.nonce },
				params: { 'req': 'authenticate_service' },
				body: data,
				responseType: 'json'
			}).then(function (response) {
				commit('updateAuthenticatedServices', response.data);
				commit('updateAuthProgress', false);
				commit('logMessage', ['Service authenticated: ' + data.service, 'success']);
			}, function () {
				commit('logMessage', ['Error retrieving authenticated services.', 'error']);
			});
		},
		removeService: function removeService(_ref7, data) {
			var commit = _ref7.commit;

			_vue2.default.http({
				url: ropApiSettings.root,
				method: 'POST',
				headers: { 'X-WP-Nonce': ropApiSettings.nonce },
				params: { 'req': 'remove_service' },
				body: data,
				responseType: 'json'
			}).then(function (response) {
				console.log(response.data);
				commit('updateAuthenticatedServices', response.data);
			}, function () {
				commit('logMessage', ['Error when trying to remove and update authenticated services.', 'error']);
			});
		},
		getGeneralSettings: function getGeneralSettings(_ref8, data) {
			var commit = _ref8.commit;

			_vue2.default.http({
				url: ropApiSettings.root,
				method: 'POST',
				headers: { 'X-WP-Nonce': ropApiSettings.nonce },
				params: { 'req': 'get_general_settings' },
				responseType: 'json'
			}).then(function (response) {
				commit('updateGeneralSettings', response.data);
			}, function () {
				commit('logMessage', ['Error retrieving general settings.', 'error']);
			});
		},
		fetchTaxonomies: function fetchTaxonomies(_ref9, data) {
			var commit = _ref9.commit;

			_vue2.default.http({
				url: ropApiSettings.root,
				method: 'POST',
				headers: { 'X-WP-Nonce': ropApiSettings.nonce },
				params: { 'req': 'get_taxonomies' },
				body: data,
				responseType: 'json'
			}).then(function (response) {
				console.log(response.data);
				commit('updateAvailableTaxonomies', response.data);
			}, function () {
				commit('logMessage', ['Error retrieving taxonomies.', 'error']);
			});
		},
		fetchPosts: function fetchPosts(_ref10, data) {
			var commit = _ref10.commit;

			_vue2.default.http({
				url: ropApiSettings.root,
				method: 'POST',
				headers: { 'X-WP-Nonce': ropApiSettings.nonce },
				params: { 'req': 'get_posts' },
				body: data,
				responseType: 'json'
			}).then(function (response) {
				console.log(response.data);
				commit('updateAvailablePosts', response.data);
			}, function () {
				commit('logMessage', ['Error retrieving posts.', 'error']);
			});
		},
		saveGeneralSettings: function saveGeneralSettings(_ref11, data) {
			var commit = _ref11.commit;

			_vue2.default.http({
				url: ropApiSettings.root,
				method: 'POST',
				headers: { 'X-WP-Nonce': ropApiSettings.nonce },
				params: { 'req': 'save_general_settings' },
				body: data,
				responseType: 'json'
			}).then(function (response) {
				console.log(response.data);
				// commit( 'updateAvailablePosts', response.data )
			}, function () {
				commit('logMessage', ['Error saving general settings.', 'error']);
			});
		},
		fetchPostFormat: function fetchPostFormat(_ref12, data) {
			var commit = _ref12.commit;

			_vue2.default.http({
				url: ropApiSettings.root,
				method: 'POST',
				headers: { 'X-WP-Nonce': ropApiSettings.nonce },
				params: { 'req': 'get_post_format' },
				body: data,
				responseType: 'json'
			}).then(function (response) {
				console.log(response.data);
				commit('updatePostFormat', response.data);
			}, function () {
				commit('logMessage', ['Error retrieving posts.', 'error']);
			});
		},
		savePostFormat: function savePostFormat(_ref13, data) {
			var commit = _ref13.commit;

			_vue2.default.http({
				url: ropApiSettings.root,
				method: 'POST',
				headers: { 'X-WP-Nonce': ropApiSettings.nonce },
				params: { 'req': 'save_post_format' },
				body: data,
				responseType: 'json'
			}).then(function (response) {
				console.log(response.data);
				commit('updatePostFormat', response.data);
			}, function () {
				commit('logMessage', ['Error retrieving posts.', 'error']);
			});
		},
		resetPostFormat: function resetPostFormat(_ref14, data) {
			var commit = _ref14.commit;

			_vue2.default.http({
				url: ropApiSettings.root,
				method: 'POST',
				headers: { 'X-WP-Nonce': ropApiSettings.nonce },
				params: { 'req': 'reset_post_format' },
				body: data,
				responseType: 'json'
			}).then(function (response) {
				console.log(response.data);
				commit('updatePostFormat', response.data);
			}, function () {
				commit('logMessage', ['Error retrieving posts.', 'error']);
			});
		},
		fetchShortnerCredentials: function fetchShortnerCredentials(_ref15, data) {
			var commit = _ref15.commit;

			return new Promise(function (resolve, reject) {
				_vue2.default.http({
					url: ropApiSettings.root,
					method: 'POST',
					headers: { 'X-WP-Nonce': ropApiSettings.nonce },
					params: { 'req': 'shortner_credentials' },
					body: data,
					responseType: 'json'
				}).then(function (response) {
					resolve(response.data);
					commit('updatePostFormatShortnerCredentials', response.data);
					console.log(response.data);
				}, function () {
					commit('logMessage', ['Error retrieving shortner credentials.', 'error']);
				});
			});
		},
		fetchSchedule: function fetchSchedule(_ref16, data) {
			var commit = _ref16.commit;

			_vue2.default.http({
				url: ropApiSettings.root,
				method: 'POST',
				headers: { 'X-WP-Nonce': ropApiSettings.nonce },
				params: { 'req': 'get_schedule' },
				body: data,
				responseType: 'json'
			}).then(function (response) {
				console.log(response.data);
				commit('updateSchedule', response.data);
			}, function () {
				commit('logMessage', ['Error retrieving schedule.', 'error']);
			});
		},
		saveSchedule: function saveSchedule(_ref17, data) {
			var commit = _ref17.commit;

			_vue2.default.http({
				url: ropApiSettings.root,
				method: 'POST',
				headers: { 'X-WP-Nonce': ropApiSettings.nonce },
				params: { 'req': 'save_schedule' },
				body: data,
				responseType: 'json'
			}).then(function (response) {
				console.log(response.data);
				commit('updateSchedule', response.data);
			}, function () {
				commit('logMessage', ['Error retrieving schedule.', 'error']);
			});
		},
		resetSchedule: function resetSchedule(_ref18, data) {
			var commit = _ref18.commit;

			_vue2.default.http({
				url: ropApiSettings.root,
				method: 'POST',
				headers: { 'X-WP-Nonce': ropApiSettings.nonce },
				params: { 'req': 'reset_schedule' },
				body: data,
				responseType: 'json'
			}).then(function (response) {
				console.log(response.data);
				commit('updateSchedule', response.data);
			}, function () {
				commit('logMessage', ['Error retrieving schedule.', 'error']);
			});
		},
		fetchQueue: function fetchQueue(_ref19, data) {
			var commit = _ref19.commit;

			_vue2.default.http({
				url: ropApiSettings.root,
				method: 'POST',
				headers: { 'X-WP-Nonce': ropApiSettings.nonce },
				params: { 'req': 'get_queue' },
				body: data,
				responseType: 'json'
			}).then(function (response) {
				console.log(response.data);
				commit('updateQueue', response.data);
			}, function () {
				commit('logMessage', ['Error retrieving queue.', 'error']);
			});
		}
	}
});

/***/ }),
/* 37 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "Url", function() { return Url; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "Http", function() { return Http; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "Resource", function() { return Resource; });
/*!
 * vue-resource v1.3.4
 * https://github.com/pagekit/vue-resource
 * Released under the MIT License.
 */

/**
 * Promises/A+ polyfill v1.1.4 (https://github.com/bramstein/promis)
 */

var RESOLVED = 0;
var REJECTED = 1;
var PENDING  = 2;

function Promise$1(executor) {

    this.state = PENDING;
    this.value = undefined;
    this.deferred = [];

    var promise = this;

    try {
        executor(function (x) {
            promise.resolve(x);
        }, function (r) {
            promise.reject(r);
        });
    } catch (e) {
        promise.reject(e);
    }
}

Promise$1.reject = function (r) {
    return new Promise$1(function (resolve, reject) {
        reject(r);
    });
};

Promise$1.resolve = function (x) {
    return new Promise$1(function (resolve, reject) {
        resolve(x);
    });
};

Promise$1.all = function all(iterable) {
    return new Promise$1(function (resolve, reject) {
        var count = 0, result = [];

        if (iterable.length === 0) {
            resolve(result);
        }

        function resolver(i) {
            return function (x) {
                result[i] = x;
                count += 1;

                if (count === iterable.length) {
                    resolve(result);
                }
            };
        }

        for (var i = 0; i < iterable.length; i += 1) {
            Promise$1.resolve(iterable[i]).then(resolver(i), reject);
        }
    });
};

Promise$1.race = function race(iterable) {
    return new Promise$1(function (resolve, reject) {
        for (var i = 0; i < iterable.length; i += 1) {
            Promise$1.resolve(iterable[i]).then(resolve, reject);
        }
    });
};

var p$1 = Promise$1.prototype;

p$1.resolve = function resolve(x) {
    var promise = this;

    if (promise.state === PENDING) {
        if (x === promise) {
            throw new TypeError('Promise settled with itself.');
        }

        var called = false;

        try {
            var then = x && x['then'];

            if (x !== null && typeof x === 'object' && typeof then === 'function') {
                then.call(x, function (x) {
                    if (!called) {
                        promise.resolve(x);
                    }
                    called = true;

                }, function (r) {
                    if (!called) {
                        promise.reject(r);
                    }
                    called = true;
                });
                return;
            }
        } catch (e) {
            if (!called) {
                promise.reject(e);
            }
            return;
        }

        promise.state = RESOLVED;
        promise.value = x;
        promise.notify();
    }
};

p$1.reject = function reject(reason) {
    var promise = this;

    if (promise.state === PENDING) {
        if (reason === promise) {
            throw new TypeError('Promise settled with itself.');
        }

        promise.state = REJECTED;
        promise.value = reason;
        promise.notify();
    }
};

p$1.notify = function notify() {
    var promise = this;

    nextTick(function () {
        if (promise.state !== PENDING) {
            while (promise.deferred.length) {
                var deferred = promise.deferred.shift(),
                    onResolved = deferred[0],
                    onRejected = deferred[1],
                    resolve = deferred[2],
                    reject = deferred[3];

                try {
                    if (promise.state === RESOLVED) {
                        if (typeof onResolved === 'function') {
                            resolve(onResolved.call(undefined, promise.value));
                        } else {
                            resolve(promise.value);
                        }
                    } else if (promise.state === REJECTED) {
                        if (typeof onRejected === 'function') {
                            resolve(onRejected.call(undefined, promise.value));
                        } else {
                            reject(promise.value);
                        }
                    }
                } catch (e) {
                    reject(e);
                }
            }
        }
    });
};

p$1.then = function then(onResolved, onRejected) {
    var promise = this;

    return new Promise$1(function (resolve, reject) {
        promise.deferred.push([onResolved, onRejected, resolve, reject]);
        promise.notify();
    });
};

p$1.catch = function (onRejected) {
    return this.then(undefined, onRejected);
};

/**
 * Promise adapter.
 */

if (typeof Promise === 'undefined') {
    window.Promise = Promise$1;
}

function PromiseObj(executor, context) {

    if (executor instanceof Promise) {
        this.promise = executor;
    } else {
        this.promise = new Promise(executor.bind(context));
    }

    this.context = context;
}

PromiseObj.all = function (iterable, context) {
    return new PromiseObj(Promise.all(iterable), context);
};

PromiseObj.resolve = function (value, context) {
    return new PromiseObj(Promise.resolve(value), context);
};

PromiseObj.reject = function (reason, context) {
    return new PromiseObj(Promise.reject(reason), context);
};

PromiseObj.race = function (iterable, context) {
    return new PromiseObj(Promise.race(iterable), context);
};

var p = PromiseObj.prototype;

p.bind = function (context) {
    this.context = context;
    return this;
};

p.then = function (fulfilled, rejected) {

    if (fulfilled && fulfilled.bind && this.context) {
        fulfilled = fulfilled.bind(this.context);
    }

    if (rejected && rejected.bind && this.context) {
        rejected = rejected.bind(this.context);
    }

    return new PromiseObj(this.promise.then(fulfilled, rejected), this.context);
};

p.catch = function (rejected) {

    if (rejected && rejected.bind && this.context) {
        rejected = rejected.bind(this.context);
    }

    return new PromiseObj(this.promise.catch(rejected), this.context);
};

p.finally = function (callback) {

    return this.then(function (value) {
            callback.call(this);
            return value;
        }, function (reason) {
            callback.call(this);
            return Promise.reject(reason);
        }
    );
};

/**
 * Utility functions.
 */

var ref = {};
var hasOwnProperty = ref.hasOwnProperty;

var ref$1 = [];
var slice = ref$1.slice;
var debug = false;
var ntick;

var inBrowser = typeof window !== 'undefined';

var Util = function (ref) {
    var config = ref.config;
    var nextTick = ref.nextTick;

    ntick = nextTick;
    debug = config.debug || !config.silent;
};

function warn(msg) {
    if (typeof console !== 'undefined' && debug) {
        console.warn('[VueResource warn]: ' + msg);
    }
}

function error(msg) {
    if (typeof console !== 'undefined') {
        console.error(msg);
    }
}

function nextTick(cb, ctx) {
    return ntick(cb, ctx);
}

function trim(str) {
    return str ? str.replace(/^\s*|\s*$/g, '') : '';
}

function trimEnd(str, chars) {

    if (str && chars === undefined) {
        return str.replace(/\s+$/, '');
    }

    if (!str || !chars) {
        return str;
    }

    return str.replace(new RegExp(("[" + chars + "]+$")), '');
}

function toLower(str) {
    return str ? str.toLowerCase() : '';
}

function toUpper(str) {
    return str ? str.toUpperCase() : '';
}

var isArray = Array.isArray;

function isString(val) {
    return typeof val === 'string';
}



function isFunction(val) {
    return typeof val === 'function';
}

function isObject(obj) {
    return obj !== null && typeof obj === 'object';
}

function isPlainObject(obj) {
    return isObject(obj) && Object.getPrototypeOf(obj) == Object.prototype;
}

function isBlob(obj) {
    return typeof Blob !== 'undefined' && obj instanceof Blob;
}

function isFormData(obj) {
    return typeof FormData !== 'undefined' && obj instanceof FormData;
}

function when(value, fulfilled, rejected) {

    var promise = PromiseObj.resolve(value);

    if (arguments.length < 2) {
        return promise;
    }

    return promise.then(fulfilled, rejected);
}

function options(fn, obj, opts) {

    opts = opts || {};

    if (isFunction(opts)) {
        opts = opts.call(obj);
    }

    return merge(fn.bind({$vm: obj, $options: opts}), fn, {$options: opts});
}

function each(obj, iterator) {

    var i, key;

    if (isArray(obj)) {
        for (i = 0; i < obj.length; i++) {
            iterator.call(obj[i], obj[i], i);
        }
    } else if (isObject(obj)) {
        for (key in obj) {
            if (hasOwnProperty.call(obj, key)) {
                iterator.call(obj[key], obj[key], key);
            }
        }
    }

    return obj;
}

var assign = Object.assign || _assign;

function merge(target) {

    var args = slice.call(arguments, 1);

    args.forEach(function (source) {
        _merge(target, source, true);
    });

    return target;
}

function defaults(target) {

    var args = slice.call(arguments, 1);

    args.forEach(function (source) {

        for (var key in source) {
            if (target[key] === undefined) {
                target[key] = source[key];
            }
        }

    });

    return target;
}

function _assign(target) {

    var args = slice.call(arguments, 1);

    args.forEach(function (source) {
        _merge(target, source);
    });

    return target;
}

function _merge(target, source, deep) {
    for (var key in source) {
        if (deep && (isPlainObject(source[key]) || isArray(source[key]))) {
            if (isPlainObject(source[key]) && !isPlainObject(target[key])) {
                target[key] = {};
            }
            if (isArray(source[key]) && !isArray(target[key])) {
                target[key] = [];
            }
            _merge(target[key], source[key], deep);
        } else if (source[key] !== undefined) {
            target[key] = source[key];
        }
    }
}

/**
 * Root Prefix Transform.
 */

var root = function (options$$1, next) {

    var url = next(options$$1);

    if (isString(options$$1.root) && !/^(https?:)?\//.test(url)) {
        url = trimEnd(options$$1.root, '/') + '/' + url;
    }

    return url;
};

/**
 * Query Parameter Transform.
 */

var query = function (options$$1, next) {

    var urlParams = Object.keys(Url.options.params), query = {}, url = next(options$$1);

    each(options$$1.params, function (value, key) {
        if (urlParams.indexOf(key) === -1) {
            query[key] = value;
        }
    });

    query = Url.params(query);

    if (query) {
        url += (url.indexOf('?') == -1 ? '?' : '&') + query;
    }

    return url;
};

/**
 * URL Template v2.0.6 (https://github.com/bramstein/url-template)
 */

function expand(url, params, variables) {

    var tmpl = parse(url), expanded = tmpl.expand(params);

    if (variables) {
        variables.push.apply(variables, tmpl.vars);
    }

    return expanded;
}

function parse(template) {

    var operators = ['+', '#', '.', '/', ';', '?', '&'], variables = [];

    return {
        vars: variables,
        expand: function expand(context) {
            return template.replace(/\{([^\{\}]+)\}|([^\{\}]+)/g, function (_, expression, literal) {
                if (expression) {

                    var operator = null, values = [];

                    if (operators.indexOf(expression.charAt(0)) !== -1) {
                        operator = expression.charAt(0);
                        expression = expression.substr(1);
                    }

                    expression.split(/,/g).forEach(function (variable) {
                        var tmp = /([^:\*]*)(?::(\d+)|(\*))?/.exec(variable);
                        values.push.apply(values, getValues(context, operator, tmp[1], tmp[2] || tmp[3]));
                        variables.push(tmp[1]);
                    });

                    if (operator && operator !== '+') {

                        var separator = ',';

                        if (operator === '?') {
                            separator = '&';
                        } else if (operator !== '#') {
                            separator = operator;
                        }

                        return (values.length !== 0 ? operator : '') + values.join(separator);
                    } else {
                        return values.join(',');
                    }

                } else {
                    return encodeReserved(literal);
                }
            });
        }
    };
}

function getValues(context, operator, key, modifier) {

    var value = context[key], result = [];

    if (isDefined(value) && value !== '') {
        if (typeof value === 'string' || typeof value === 'number' || typeof value === 'boolean') {
            value = value.toString();

            if (modifier && modifier !== '*') {
                value = value.substring(0, parseInt(modifier, 10));
            }

            result.push(encodeValue(operator, value, isKeyOperator(operator) ? key : null));
        } else {
            if (modifier === '*') {
                if (Array.isArray(value)) {
                    value.filter(isDefined).forEach(function (value) {
                        result.push(encodeValue(operator, value, isKeyOperator(operator) ? key : null));
                    });
                } else {
                    Object.keys(value).forEach(function (k) {
                        if (isDefined(value[k])) {
                            result.push(encodeValue(operator, value[k], k));
                        }
                    });
                }
            } else {
                var tmp = [];

                if (Array.isArray(value)) {
                    value.filter(isDefined).forEach(function (value) {
                        tmp.push(encodeValue(operator, value));
                    });
                } else {
                    Object.keys(value).forEach(function (k) {
                        if (isDefined(value[k])) {
                            tmp.push(encodeURIComponent(k));
                            tmp.push(encodeValue(operator, value[k].toString()));
                        }
                    });
                }

                if (isKeyOperator(operator)) {
                    result.push(encodeURIComponent(key) + '=' + tmp.join(','));
                } else if (tmp.length !== 0) {
                    result.push(tmp.join(','));
                }
            }
        }
    } else {
        if (operator === ';') {
            result.push(encodeURIComponent(key));
        } else if (value === '' && (operator === '&' || operator === '?')) {
            result.push(encodeURIComponent(key) + '=');
        } else if (value === '') {
            result.push('');
        }
    }

    return result;
}

function isDefined(value) {
    return value !== undefined && value !== null;
}

function isKeyOperator(operator) {
    return operator === ';' || operator === '&' || operator === '?';
}

function encodeValue(operator, value, key) {

    value = (operator === '+' || operator === '#') ? encodeReserved(value) : encodeURIComponent(value);

    if (key) {
        return encodeURIComponent(key) + '=' + value;
    } else {
        return value;
    }
}

function encodeReserved(str) {
    return str.split(/(%[0-9A-Fa-f]{2})/g).map(function (part) {
        if (!/%[0-9A-Fa-f]/.test(part)) {
            part = encodeURI(part);
        }
        return part;
    }).join('');
}

/**
 * URL Template (RFC 6570) Transform.
 */

var template = function (options) {

    var variables = [], url = expand(options.url, options.params, variables);

    variables.forEach(function (key) {
        delete options.params[key];
    });

    return url;
};

/**
 * Service for URL templating.
 */

function Url(url, params) {

    var self = this || {}, options$$1 = url, transform;

    if (isString(url)) {
        options$$1 = {url: url, params: params};
    }

    options$$1 = merge({}, Url.options, self.$options, options$$1);

    Url.transforms.forEach(function (handler) {

        if (isString(handler)) {
            handler = Url.transform[handler];
        }

        if (isFunction(handler)) {
            transform = factory(handler, transform, self.$vm);
        }

    });

    return transform(options$$1);
}

/**
 * Url options.
 */

Url.options = {
    url: '',
    root: null,
    params: {}
};

/**
 * Url transforms.
 */

Url.transform = {template: template, query: query, root: root};
Url.transforms = ['template', 'query', 'root'];

/**
 * Encodes a Url parameter string.
 *
 * @param {Object} obj
 */

Url.params = function (obj) {

    var params = [], escape = encodeURIComponent;

    params.add = function (key, value) {

        if (isFunction(value)) {
            value = value();
        }

        if (value === null) {
            value = '';
        }

        this.push(escape(key) + '=' + escape(value));
    };

    serialize(params, obj);

    return params.join('&').replace(/%20/g, '+');
};

/**
 * Parse a URL and return its components.
 *
 * @param {String} url
 */

Url.parse = function (url) {

    var el = document.createElement('a');

    if (document.documentMode) {
        el.href = url;
        url = el.href;
    }

    el.href = url;

    return {
        href: el.href,
        protocol: el.protocol ? el.protocol.replace(/:$/, '') : '',
        port: el.port,
        host: el.host,
        hostname: el.hostname,
        pathname: el.pathname.charAt(0) === '/' ? el.pathname : '/' + el.pathname,
        search: el.search ? el.search.replace(/^\?/, '') : '',
        hash: el.hash ? el.hash.replace(/^#/, '') : ''
    };
};

function factory(handler, next, vm) {
    return function (options$$1) {
        return handler.call(vm, options$$1, next);
    };
}

function serialize(params, obj, scope) {

    var array = isArray(obj), plain = isPlainObject(obj), hash;

    each(obj, function (value, key) {

        hash = isObject(value) || isArray(value);

        if (scope) {
            key = scope + '[' + (plain || hash ? key : '') + ']';
        }

        if (!scope && array) {
            params.add(value.name, value.value);
        } else if (hash) {
            serialize(params, value, key);
        } else {
            params.add(key, value);
        }
    });
}

/**
 * XDomain client (Internet Explorer).
 */

var xdrClient = function (request) {
    return new PromiseObj(function (resolve) {

        var xdr = new XDomainRequest(), handler = function (ref) {
            var type = ref.type;


            var status = 0;

            if (type === 'load') {
                status = 200;
            } else if (type === 'error') {
                status = 500;
            }

            resolve(request.respondWith(xdr.responseText, {status: status}));
        };

        request.abort = function () { return xdr.abort(); };

        xdr.open(request.method, request.getUrl());

        if (request.timeout) {
            xdr.timeout = request.timeout;
        }

        xdr.onload = handler;
        xdr.onabort = handler;
        xdr.onerror = handler;
        xdr.ontimeout = handler;
        xdr.onprogress = function () {};
        xdr.send(request.getBody());
    });
};

/**
 * CORS Interceptor.
 */

var SUPPORTS_CORS = inBrowser && 'withCredentials' in new XMLHttpRequest();

var cors = function (request, next) {

    if (inBrowser) {

        var orgUrl = Url.parse(location.href);
        var reqUrl = Url.parse(request.getUrl());

        if (reqUrl.protocol !== orgUrl.protocol || reqUrl.host !== orgUrl.host) {

            request.crossOrigin = true;
            request.emulateHTTP = false;

            if (!SUPPORTS_CORS) {
                request.client = xdrClient;
            }
        }
    }

    next();
};

/**
 * Form data Interceptor.
 */

var form = function (request, next) {

    if (isFormData(request.body)) {

        request.headers.delete('Content-Type');

    } else if (isObject(request.body) && request.emulateJSON) {

        request.body = Url.params(request.body);
        request.headers.set('Content-Type', 'application/x-www-form-urlencoded');
    }

    next();
};

/**
 * JSON Interceptor.
 */

var json = function (request, next) {

    var type = request.headers.get('Content-Type') || '';

    if (isObject(request.body) && type.indexOf('application/json') === 0) {
        request.body = JSON.stringify(request.body);
    }

    next(function (response) {

        return response.bodyText ? when(response.text(), function (text) {

            type = response.headers.get('Content-Type') || '';

            if (type.indexOf('application/json') === 0 || isJson(text)) {

                try {
                    response.body = JSON.parse(text);
                } catch (e) {
                    response.body = null;
                }

            } else {
                response.body = text;
            }

            return response;

        }) : response;

    });
};

function isJson(str) {

    var start = str.match(/^\[|^\{(?!\{)/), end = {'[': /]$/, '{': /}$/};

    return start && end[start[0]].test(str);
}

/**
 * JSONP client (Browser).
 */

var jsonpClient = function (request) {
    return new PromiseObj(function (resolve) {

        var name = request.jsonp || 'callback', callback = request.jsonpCallback || '_jsonp' + Math.random().toString(36).substr(2), body = null, handler, script;

        handler = function (ref) {
            var type = ref.type;


            var status = 0;

            if (type === 'load' && body !== null) {
                status = 200;
            } else if (type === 'error') {
                status = 500;
            }

            if (status && window[callback]) {
                delete window[callback];
                document.body.removeChild(script);
            }

            resolve(request.respondWith(body, {status: status}));
        };

        window[callback] = function (result) {
            body = JSON.stringify(result);
        };

        request.abort = function () {
            handler({type: 'abort'});
        };

        request.params[name] = callback;

        if (request.timeout) {
            setTimeout(request.abort, request.timeout);
        }

        script = document.createElement('script');
        script.src = request.getUrl();
        script.type = 'text/javascript';
        script.async = true;
        script.onload = handler;
        script.onerror = handler;

        document.body.appendChild(script);
    });
};

/**
 * JSONP Interceptor.
 */

var jsonp = function (request, next) {

    if (request.method == 'JSONP') {
        request.client = jsonpClient;
    }

    next();
};

/**
 * Before Interceptor.
 */

var before = function (request, next) {

    if (isFunction(request.before)) {
        request.before.call(this, request);
    }

    next();
};

/**
 * HTTP method override Interceptor.
 */

var method = function (request, next) {

    if (request.emulateHTTP && /^(PUT|PATCH|DELETE)$/i.test(request.method)) {
        request.headers.set('X-HTTP-Method-Override', request.method);
        request.method = 'POST';
    }

    next();
};

/**
 * Header Interceptor.
 */

var header = function (request, next) {

    var headers = assign({}, Http.headers.common,
        !request.crossOrigin ? Http.headers.custom : {},
        Http.headers[toLower(request.method)]
    );

    each(headers, function (value, name) {
        if (!request.headers.has(name)) {
            request.headers.set(name, value);
        }
    });

    next();
};

/**
 * XMLHttp client (Browser).
 */

var xhrClient = function (request) {
    return new PromiseObj(function (resolve) {

        var xhr = new XMLHttpRequest(), handler = function (event) {

            var response = request.respondWith(
                'response' in xhr ? xhr.response : xhr.responseText, {
                    status: xhr.status === 1223 ? 204 : xhr.status, // IE9 status bug
                    statusText: xhr.status === 1223 ? 'No Content' : trim(xhr.statusText)
                }
            );

            each(trim(xhr.getAllResponseHeaders()).split('\n'), function (row) {
                response.headers.append(row.slice(0, row.indexOf(':')), row.slice(row.indexOf(':') + 1));
            });

            resolve(response);
        };

        request.abort = function () { return xhr.abort(); };

        if (request.progress) {
            if (request.method === 'GET') {
                xhr.addEventListener('progress', request.progress);
            } else if (/^(POST|PUT)$/i.test(request.method)) {
                xhr.upload.addEventListener('progress', request.progress);
            }
        }

        xhr.open(request.method, request.getUrl(), true);

        if (request.timeout) {
            xhr.timeout = request.timeout;
        }

        if (request.responseType && 'responseType' in xhr) {
            xhr.responseType = request.responseType;
        }

        if (request.withCredentials || request.credentials) {
            xhr.withCredentials = true;
        }

        if (!request.crossOrigin) {
            request.headers.set('X-Requested-With', 'XMLHttpRequest');
        }

        request.headers.forEach(function (value, name) {
            xhr.setRequestHeader(name, value);
        });

        xhr.onload = handler;
        xhr.onabort = handler;
        xhr.onerror = handler;
        xhr.ontimeout = handler;
        xhr.send(request.getBody());
    });
};

/**
 * Http client (Node).
 */

var nodeClient = function (request) {

    var client = __webpack_require__(38);

    return new PromiseObj(function (resolve) {

        var url = request.getUrl();
        var body = request.getBody();
        var method = request.method;
        var headers = {}, handler;

        request.headers.forEach(function (value, name) {
            headers[name] = value;
        });

        client(url, {body: body, method: method, headers: headers}).then(handler = function (resp) {

            var response = request.respondWith(resp.body, {
                    status: resp.statusCode,
                    statusText: trim(resp.statusMessage)
                }
            );

            each(resp.headers, function (value, name) {
                response.headers.set(name, value);
            });

            resolve(response);

        }, function (error$$1) { return handler(error$$1.response); });
    });
};

/**
 * Base client.
 */

var Client = function (context) {

    var reqHandlers = [sendRequest], resHandlers = [], handler;

    if (!isObject(context)) {
        context = null;
    }

    function Client(request) {
        return new PromiseObj(function (resolve, reject) {

            function exec() {

                handler = reqHandlers.pop();

                if (isFunction(handler)) {
                    handler.call(context, request, next);
                } else {
                    warn(("Invalid interceptor of type " + (typeof handler) + ", must be a function"));
                    next();
                }
            }

            function next(response) {

                if (isFunction(response)) {

                    resHandlers.unshift(response);

                } else if (isObject(response)) {

                    resHandlers.forEach(function (handler) {
                        response = when(response, function (response) {
                            return handler.call(context, response) || response;
                        }, reject);
                    });

                    when(response, resolve, reject);

                    return;
                }

                exec();
            }

            exec();

        }, context);
    }

    Client.use = function (handler) {
        reqHandlers.push(handler);
    };

    return Client;
};

function sendRequest(request, resolve) {

    var client = request.client || (inBrowser ? xhrClient : nodeClient);

    resolve(client(request));
}

/**
 * HTTP Headers.
 */

var Headers = function Headers(headers) {
    var this$1 = this;


    this.map = {};

    each(headers, function (value, name) { return this$1.append(name, value); });
};

Headers.prototype.has = function has (name) {
    return getName(this.map, name) !== null;
};

Headers.prototype.get = function get (name) {

    var list = this.map[getName(this.map, name)];

    return list ? list.join() : null;
};

Headers.prototype.getAll = function getAll (name) {
    return this.map[getName(this.map, name)] || [];
};

Headers.prototype.set = function set (name, value) {
    this.map[normalizeName(getName(this.map, name) || name)] = [trim(value)];
};

Headers.prototype.append = function append (name, value){

    var list = this.map[getName(this.map, name)];

    if (list) {
        list.push(trim(value));
    } else {
        this.set(name, value);
    }
};

Headers.prototype.delete = function delete$1 (name){
    delete this.map[getName(this.map, name)];
};

Headers.prototype.deleteAll = function deleteAll (){
    this.map = {};
};

Headers.prototype.forEach = function forEach (callback, thisArg) {
        var this$1 = this;

    each(this.map, function (list, name) {
        each(list, function (value) { return callback.call(thisArg, value, name, this$1); });
    });
};

function getName(map, name) {
    return Object.keys(map).reduce(function (prev, curr) {
        return toLower(name) === toLower(curr) ? curr : prev;
    }, null);
}

function normalizeName(name) {

    if (/[^a-z0-9\-#$%&'*+.\^_`|~]/i.test(name)) {
        throw new TypeError('Invalid character in header field name');
    }

    return trim(name);
}

/**
 * HTTP Response.
 */

var Response = function Response(body, ref) {
    var url = ref.url;
    var headers = ref.headers;
    var status = ref.status;
    var statusText = ref.statusText;


    this.url = url;
    this.ok = status >= 200 && status < 300;
    this.status = status || 0;
    this.statusText = statusText || '';
    this.headers = new Headers(headers);
    this.body = body;

    if (isString(body)) {

        this.bodyText = body;

    } else if (isBlob(body)) {

        this.bodyBlob = body;

        if (isBlobText(body)) {
            this.bodyText = blobText(body);
        }
    }
};

Response.prototype.blob = function blob () {
    return when(this.bodyBlob);
};

Response.prototype.text = function text () {
    return when(this.bodyText);
};

Response.prototype.json = function json () {
    return when(this.text(), function (text) { return JSON.parse(text); });
};

Object.defineProperty(Response.prototype, 'data', {

    get: function get() {
        return this.body;
    },

    set: function set(body) {
        this.body = body;
    }

});

function blobText(body) {
    return new PromiseObj(function (resolve) {

        var reader = new FileReader();

        reader.readAsText(body);
        reader.onload = function () {
            resolve(reader.result);
        };

    });
}

function isBlobText(body) {
    return body.type.indexOf('text') === 0 || body.type.indexOf('json') !== -1;
}

/**
 * HTTP Request.
 */

var Request = function Request(options$$1) {

    this.body = null;
    this.params = {};

    assign(this, options$$1, {
        method: toUpper(options$$1.method || 'GET')
    });

    if (!(this.headers instanceof Headers)) {
        this.headers = new Headers(this.headers);
    }
};

Request.prototype.getUrl = function getUrl (){
    return Url(this);
};

Request.prototype.getBody = function getBody (){
    return this.body;
};

Request.prototype.respondWith = function respondWith (body, options$$1) {
    return new Response(body, assign(options$$1 || {}, {url: this.getUrl()}));
};

/**
 * Service for sending network requests.
 */

var COMMON_HEADERS = {'Accept': 'application/json, text/plain, */*'};
var JSON_CONTENT_TYPE = {'Content-Type': 'application/json;charset=utf-8'};

function Http(options$$1) {

    var self = this || {}, client = Client(self.$vm);

    defaults(options$$1 || {}, self.$options, Http.options);

    Http.interceptors.forEach(function (handler) {

        if (isString(handler)) {
            handler = Http.interceptor[handler];
        }

        if (isFunction(handler)) {
            client.use(handler);
        }

    });

    return client(new Request(options$$1)).then(function (response) {

        return response.ok ? response : PromiseObj.reject(response);

    }, function (response) {

        if (response instanceof Error) {
            error(response);
        }

        return PromiseObj.reject(response);
    });
}

Http.options = {};

Http.headers = {
    put: JSON_CONTENT_TYPE,
    post: JSON_CONTENT_TYPE,
    patch: JSON_CONTENT_TYPE,
    delete: JSON_CONTENT_TYPE,
    common: COMMON_HEADERS,
    custom: {}
};

Http.interceptor = {before: before, method: method, jsonp: jsonp, json: json, form: form, header: header, cors: cors};
Http.interceptors = ['before', 'method', 'jsonp', 'json', 'form', 'header', 'cors'];

['get', 'delete', 'head', 'jsonp'].forEach(function (method$$1) {

    Http[method$$1] = function (url, options$$1) {
        return this(assign(options$$1 || {}, {url: url, method: method$$1}));
    };

});

['post', 'put', 'patch'].forEach(function (method$$1) {

    Http[method$$1] = function (url, body, options$$1) {
        return this(assign(options$$1 || {}, {url: url, method: method$$1, body: body}));
    };

});

/**
 * Service for interacting with RESTful services.
 */

function Resource(url, params, actions, options$$1) {

    var self = this || {}, resource = {};

    actions = assign({},
        Resource.actions,
        actions
    );

    each(actions, function (action, name) {

        action = merge({url: url, params: assign({}, params)}, options$$1, action);

        resource[name] = function () {
            return (self.$http || Http)(opts(action, arguments));
        };
    });

    return resource;
}

function opts(action, args) {

    var options$$1 = assign({}, action), params = {}, body;

    switch (args.length) {

        case 2:

            params = args[0];
            body = args[1];

            break;

        case 1:

            if (/^(POST|PUT|PATCH)$/i.test(options$$1.method)) {
                body = args[0];
            } else {
                params = args[0];
            }

            break;

        case 0:

            break;

        default:

            throw 'Expected up to 2 arguments [params, body], got ' + args.length + ' arguments';
    }

    options$$1.body = body;
    options$$1.params = assign({}, options$$1.params, params);

    return options$$1;
}

Resource.actions = {

    get: {method: 'GET'},
    save: {method: 'POST'},
    query: {method: 'GET'},
    update: {method: 'PUT'},
    remove: {method: 'DELETE'},
    delete: {method: 'DELETE'}

};

/**
 * Install plugin.
 */

function plugin(Vue) {

    if (plugin.installed) {
        return;
    }

    Util(Vue);

    Vue.url = Url;
    Vue.http = Http;
    Vue.resource = Resource;
    Vue.Promise = PromiseObj;

    Object.defineProperties(Vue.prototype, {

        $url: {
            get: function get() {
                return options(Vue.url, this, this.$options.url);
            }
        },

        $http: {
            get: function get() {
                return options(Vue.http, this, this.$options.http);
            }
        },

        $resource: {
            get: function get() {
                return Vue.resource.bind(this);
            }
        },

        $promise: {
            get: function get() {
                var this$1 = this;

                return function (executor) { return new Vue.Promise(executor, this$1); };
            }
        }

    });
}

if (typeof window !== 'undefined' && window.Vue) {
    window.Vue.use(plugin);
}

/* harmony default export */ __webpack_exports__["default"] = (plugin);



/***/ }),
/* 38 */
/***/ (function(module, exports) {

/* (ignored) */

/***/ }),
/* 39 */
/***/ (function(module, exports, __webpack_require__) {

var __vue_script__, __vue_template__
__vue_script__ = __webpack_require__(40)
__vue_template__ = __webpack_require__(132)
module.exports = __vue_script__ || {}
if (module.exports.__esModule) module.exports = module.exports.default
if (__vue_template__) { (typeof module.exports === "function" ? module.exports.options : module.exports).template = __vue_template__ }
if (false) {(function () {  module.hot.accept()
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), true)
  if (!hotAPI.compatible) return
  var id = "/var/www/html/wp-base/wp-content/plugins/tweet-old-post/vue/src/vue-elements/main-page-panel.vue"
  if (!module.hot.data) {
    hotAPI.createRecord(id, module.exports)
  } else {
    hotAPI.update(id, module.exports, __vue_template__)
  }
})()}

/***/ }),
/* 40 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _accountsTabPanel = __webpack_require__(41);

var _accountsTabPanel2 = _interopRequireDefault(_accountsTabPanel);

var _settingsTabPanel = __webpack_require__(94);

var _settingsTabPanel2 = _interopRequireDefault(_settingsTabPanel);

var _postFormatTabPanel = __webpack_require__(105);

var _postFormatTabPanel2 = _interopRequireDefault(_postFormatTabPanel);

var _scheduleTabPanel = __webpack_require__(110);

var _scheduleTabPanel2 = _interopRequireDefault(_scheduleTabPanel);

var _queueTabPanel = __webpack_require__(133);

var _queueTabPanel2 = _interopRequireDefault(_queueTabPanel);

var _logsTabPanel = __webpack_require__(129);

var _logsTabPanel2 = _interopRequireDefault(_logsTabPanel);

var _vuex = __webpack_require__(21);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

module.exports = {
	name: 'main-page-panel',
	computed: (0, _vuex.mapState)(['displayTabs', 'page']),
	created: function created() {},

	data: function data() {
		return {
			plugin_logo: ROP_ASSETS_URL + 'img/logo_rop.png'
		};
	},
	methods: {
		switchTab: function switchTab(slug) {
			this.$store.commit('setTabView', slug);
		}
	},
	components: {
		'accounts': _accountsTabPanel2.default,
		'settings': _settingsTabPanel2.default,
		'post-format': _postFormatTabPanel2.default,
		'schedule': _scheduleTabPanel2.default,
		'queue': _queueTabPanel2.default,
		'logs': _logsTabPanel2.default
	}
	// </script>

}; // <template>
// 	<div>
// 		<div class="panel title-panel" style="margin-bottom: 40px; padding-bottom: 20px;">
// 			<div class="panel-header">
// 				<img :src="plugin_logo" style="float: left; margin-right: 10px;" />
// 				<h1 class="d-inline-block">Revive Old Posts</h1><span class="powered"> by <a href="https://themeisle.com" target="_blank"><b>ThemeIsle</b></a></span>
// 			</div>
// 		</div>
// 		<div class="panel">
// 			<div class="panel-nav" style="padding: 8px;">
// 				<ul class="tab">
// 					<li class="tab-item" v-for="tab in displayTabs" :class="{ active: tab.isActive }"><a href="#" @click="switchTab( tab.slug )">{{ tab.name }}</a></li>
// 					<li class="tab-item tab-action">
// 						<div class="form-group">
// 							<label class="form-switch">
// 								<input type="checkbox" />
// 								<i class="form-icon"></i> Beta User
// 							</label>
// 							<label class="form-switch">
// 								<input type="checkbox" />
// 								<i class="form-icon"></i> Remote Check
// 							</label>
// 						</div>
// 					</li>
// 				</ul>
// 			</div>
//
// 			<component :is="page.view"></component>
// 		</div>
// 	</div>
// </template>
//
// <script>
/* global ROP_ASSETS_URL */

/***/ }),
/* 41 */
/***/ (function(module, exports, __webpack_require__) {

var __vue_script__, __vue_template__
__vue_script__ = __webpack_require__(42)
__vue_template__ = __webpack_require__(93)
module.exports = __vue_script__ || {}
if (module.exports.__esModule) module.exports = module.exports.default
if (__vue_template__) { (typeof module.exports === "function" ? module.exports.options : module.exports).template = __vue_template__ }
if (false) {(function () {  module.hot.accept()
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), true)
  if (!hotAPI.compatible) return
  var id = "/var/www/html/wp-base/wp-content/plugins/tweet-old-post/vue/src/vue-elements/accounts-tab-panel.vue"
  if (!module.hot.data) {
    hotAPI.createRecord(id, module.exports)
  } else {
    hotAPI.update(id, module.exports, __vue_template__)
  }
})()}

/***/ }),
/* 42 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _signInBtn = __webpack_require__(43);

var _signInBtn2 = _interopRequireDefault(_signInBtn);

var _serviceTile = __webpack_require__(77);

var _serviceTile2 = _interopRequireDefault(_serviceTile);

var _serviceUserTile = __webpack_require__(88);

var _serviceUserTile2 = _interopRequireDefault(_serviceUserTile);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

module.exports = {
	name: 'account-view',
	computed: {
		authenticated_services: function authenticated_services() {
			return this.$store.state.authenticatedServices;
		},
		active_accounts: function active_accounts() {
			return this.$store.state.activeAccounts;
		}
	},
	components: {
		SignInBtn: _signInBtn2.default,
		ServiceTile: _serviceTile2.default,
		ServiceUserTile: _serviceUserTile2.default
	}
	// </script>

}; // <template>
//     <div class="tab-view">
//         <div class="panel-body">
//             <h3>Accounts</h3>
//             <p>This is a <b>Vue.js</b> component.</p>
//             <div class="container">
//                 <div class="columns">
//                     <div class="column col-sm-12 col-md-12 col-lg-6">
//                         <div class="columns">
//                             <div class="column col-sm-12 col-md-12 col-xl-6 col-8 text-right">
//                                 <b>New Service</b><br/>
//                                 <i>Select a service and sign in with an account for that service.</i>
//                             </div>
//                             <div class="column col-sm-12 col-md-12 col-xl-6 col-4 text-left">
//                                 <sign-in-btn></sign-in-btn>
//                             </div>
//                         </div>
//                         <div class="columns">
//                             <div class="column col-sm-12 col-md-12 col-lg-12 text-left">
//                                 <hr/>
//                                 <h5>Authenticated Services</h5>
//                                 <div class="empty" v-if="authenticated_services.length == 0">
//                                     <div class="empty-icon">
//                                         <i class="fa fa-3x fa-cloud"></i>
//                                     </div>
//                                     <p class="empty-title h5">No authenticated service!</p>
//                                     <p class="empty-subtitle">Add one from the <b>"New Service"</b> section.</p>
//                                 </div>
//                                 <service-tile v-for="service in authenticated_services" :key="service.id" :service="service"></service-tile>
//                             </div>
//                         </div>
//                     </div>
//                     <div class="column col-sm-12 col-md-12 col-lg-6 text-left">
//                         <hr style="margin-top: 45px" />
//                         <h5>Active Accounts</h5>
//                         <div class="empty" v-if="active_accounts.length == 0">
//                             <div class="empty-icon">
//                                 <i class="fa fa-3x fa-user-circle-o"></i>
//                             </div>
//                             <p class="empty-title h5">No active accounts!</p>
//                             <p class="empty-subtitle">Add one from the <b>"Authenticated Services"</b> section.</p>
//                         </div>
//                         <div v-for="( account, id ) in active_accounts">
//                             <service-user-tile :account_data="account" :account_id="id"></service-user-tile>
//                             <div class="divider"></div>
//                         </div>
//                     </div>
//                 </div>
//             </div>
//             <div class="columns">
//                 <div class="column col-12">
//                     <h4><i class="fa fa-info-circle"></i> Info</h4>
//                     <p><i>Authenticate a new service (eg. Facebook, Twitter etc. ), select the accounts you want to add from that service and <b>activate</b> them. Only the accounts displayed in the <b>"Active accounts"</b> section will be used.</i></p>
//                 </div>
//             </div>
//         </div>
//         <div class="panel-footer">
//             <button class="btn btn-primary">Save</button>
//         </div>
//     </div>
// </template>
//
// <script>

/***/ }),
/* 43 */
/***/ (function(module, exports, __webpack_require__) {

var __vue_script__, __vue_template__
__webpack_require__(44)
__vue_script__ = __webpack_require__(46)
__vue_template__ = __webpack_require__(76)
module.exports = __vue_script__ || {}
if (module.exports.__esModule) module.exports = module.exports.default
if (__vue_template__) { (typeof module.exports === "function" ? module.exports.options : module.exports).template = __vue_template__ }
if (false) {(function () {  module.hot.accept()
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), true)
  if (!hotAPI.compatible) return
  var id = "/var/www/html/wp-base/wp-content/plugins/tweet-old-post/vue/src/vue-elements/sign-in-btn.vue"
  if (!module.hot.data) {
    hotAPI.createRecord(id, module.exports)
  } else {
    hotAPI.update(id, module.exports, __vue_template__)
  }
})()}

/***/ }),
/* 44 */
/***/ (function(module, exports, __webpack_require__) {

// style-loader: Adds some css to the DOM by adding a <style> tag

// load the styles
var content = __webpack_require__(45);
if(typeof content === 'string') content = [[module.i, content, '']];
// add the styles to the DOM
var update = __webpack_require__(1)(content, {});
if(content.locals) module.exports = content.locals;
// Hot Module Replacement
if(false) {
	// When the styles change, update the <style> tags
	if(!content.locals) {
		module.hot.accept("!!../../../node_modules/css-loader/index.js!../../../node_modules/vue-loader/lib/style-rewriter.js?id=_v-7e903530&file=sign-in-btn.vue&scoped=true!../../../node_modules/vue-loader/lib/selector.js?type=style&index=0!../../../node_modules/eslint-loader/index.js!../../../node_modules/eslint-loader/index.js!./sign-in-btn.vue", function() {
			var newContent = require("!!../../../node_modules/css-loader/index.js!../../../node_modules/vue-loader/lib/style-rewriter.js?id=_v-7e903530&file=sign-in-btn.vue&scoped=true!../../../node_modules/vue-loader/lib/selector.js?type=style&index=0!../../../node_modules/eslint-loader/index.js!../../../node_modules/eslint-loader/index.js!./sign-in-btn.vue");
			if(typeof newContent === 'string') newContent = [[module.id, newContent, '']];
			update(newContent);
		});
	}
	// When the module is disposed, remove the <style> tags
	module.hot.dispose(function() { update(); });
}

/***/ }),
/* 45 */
/***/ (function(module, exports, __webpack_require__) {

exports = module.exports = __webpack_require__(0)();
// imports


// module
exports.push([module.i, "\n\t#rop_core .sign-in-btn > .modal[_v-7e903530] {\n\t\tposition: absolute;\n\t\ttop: 20px;\n\t}\n\n\t#rop_core .sign-in-btn > .modal > .modal-container[_v-7e903530] {\n\t\twidth: 100%;\n\t}\n\n", ""]);

// exports


/***/ }),
/* 46 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _keys = __webpack_require__(6);

var _keys2 = _interopRequireDefault(_keys);

var _getIterator2 = __webpack_require__(20);

var _getIterator3 = _interopRequireDefault(_getIterator2);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

// <template>
// 	<div class="sign-in-btn">
// 		<div class="input-group">
// 			<select class="form-select" v-model="selected_network">
// 				<option v-for="( service, network ) in services" v-bind:value="network" :disabled="checkDisabled( service.active )">{{ service.name }}</option>
// 			</select>
//
// 			<button class="btn input-group-btn" :class="serviceClass" @click="requestAuthorization()" :disabled="checkDisabled(true)" >
// 				<i class="fa fa-fw" :class="serviceIcon" aria-hidden="true"></i> Sign In
// 			</button>
// 		</div>
// 		<div class="modal" :class="modalActiveClass">
// 			<div class="modal-overlay"></div>
// 			<div class="modal-container">
// 				<div class="modal-header">
// 					<button class="btn btn-clear float-right" @click="closeModal()"></button>
// 					<div class="modal-title h5">{{ modal.serviceName }} Service Credentials</div>
// 				</div>
// 				<div class="modal-body">
// 					<div class="content">
// 						<div class="form-group" v-for="( field, id ) in modal.data">
// 							<label class="form-label" :for="field.id">{{ field.name }}</label>
// 							<input class="form-input" type="text" :id="field.id" v-model="field.value" :placeholder="field.name" />
// 							<i>{{ field.description }}</i>
// 						</div>
// 					</div>
// 				</div>
// 				<div class="modal-footer">
// 					<button class="btn btn-primary" @click="closeModal()">Sign in</button>
// 				</div>
// 			</div>
// 		</div>
// 	</div>
// </template>
//
// <script>
module.exports = {
	name: 'sign-in-btn',
	created: function created() {},

	data: function data() {
		return {
			modal: {
				isOpen: false,
				serviceName: '',
				data: {}
			},
			activePopup: ''
		};
	},
	methods: {
		checkDisabled: function checkDisabled(active) {
			if (active === false) {
				return true;
			}

			return this.$store.state.auth_in_progress;
		},

		requestAuthorization: function requestAuthorization() {
			this.$store.state.auth_in_progress = true;
			if (this.$store.state.availableServices[this.selected_network].two_step_sign_in) {
				this.modal.serviceName = this.$store.state.availableServices[this.selected_network].name;
				this.modal.data = this.$store.state.availableServices[this.selected_network].credentials;
				this.openModal();
			} else {
				this.activePopup = this.selected_network;
				var w = 560;
				var h = 340;
				var y = window.top.outerHeight / 2 + window.top.screenY - w / 2;
				var x = window.top.outerWidth / 2 + window.top.screenX - h / 2;
				window.open('', this.activePopup, 'width=' + w + ', height=' + h + ', toolbar=0, menubar=0, location=0, top=' + y + ', left=' + x);
				this.getUrlAndGo([]);
			}
		},
		openPopup: function openPopup(url) {
			this.$store.commit('logMessage', ['Trying to open popup for url:' + url, 'notice']);
			var newWindow = window.open(url, this.activePopup);
			if (window.focus) {
				newWindow.focus();
			}
			var instance = this;
			var pollTimer = window.setInterval(function () {
				if (newWindow.closed !== false) {
					window.clearInterval(pollTimer);
					instance.requestAuthentication();
				}
			}, 200);
		},
		getUrlAndGo: function getUrlAndGo(credentials) {
			var _this = this;

			console.log('Credentials recieved:', credentials);
			this.$store.dispatch('getServiceSignInUrl', { service: this.selected_network, credentials: credentials }).then(function (response) {
				console.log('Got some data, now lets show something in this component', response);
				_this.openPopup(response.url);
			}, function (error) {
				console.error('Got nothing from server. Prompt user to check internet connection and try again', error);
			});
		},
		requestAuthentication: function requestAuthentication() {
			this.$store.dispatch('authenticateService', { service: this.selected_network });
		},

		openModal: function openModal() {
			this.modal.isOpen = true;
		},
		closeModal: function closeModal() {
			var credentials = {};
			var _iteratorNormalCompletion = true;
			var _didIteratorError = false;
			var _iteratorError = undefined;

			try {
				for (var _iterator = (0, _getIterator3.default)((0, _keys2.default)(this.modal.data)), _step; !(_iteratorNormalCompletion = (_step = _iterator.next()).done); _iteratorNormalCompletion = true) {
					var index = _step.value;

					credentials[index] = '';
					if ('value' in this.modal.data[index]) {
						credentials[index] = this.modal.data[index]['value'];
					}
				}
			} catch (err) {
				_didIteratorError = true;
				_iteratorError = err;
			} finally {
				try {
					if (!_iteratorNormalCompletion && _iterator.return) {
						_iterator.return();
					}
				} finally {
					if (_didIteratorError) {
						throw _iteratorError;
					}
				}
			}

			this.activePopup = this.selected_network;
			var w = 560;
			var h = 340;
			var y = window.top.outerHeight / 2 + window.top.screenY - w / 2;
			var x = window.top.outerWidth / 2 + window.top.screenX - h / 2;
			window.open('', this.activePopup, 'width=' + w + ', height=' + h + ', toolbar=0, menubar=0, location=0, top=' + y + ', left=' + x);
			this.getUrlAndGo(credentials);

			this.modal.isOpen = false;
		}
	},
	computed: {
		selected_network: {
			get: function get() {
				var defaultNetwork = this.modal.serviceName;
				if ((0, _keys2.default)(this.services)[0] && defaultNetwork === '') {
					defaultNetwork = (0, _keys2.default)(this.services)[0];
				}
				return defaultNetwork.toLowerCase();
			},
			set: function set(newNetwork) {
				this.modal.serviceName = newNetwork;
			}
		},
		services: function services() {
			return this.$store.state.availableServices;
		},
		modalActiveClass: function modalActiveClass() {
			return {
				'active': this.modal.isOpen === true
			};
		},
		serviceClass: function serviceClass() {
			return {
				'btn-twitter': this.selected_network === 'twitter',
				'btn-facebook': this.selected_network === 'facebook',
				'btn-linkedin': this.selected_network === 'linkedin',
				'btn-tumblr': this.selected_network === 'tumblr',
				'loading': this.$store.state.auth_in_progress
			};
		},
		serviceIcon: function serviceIcon() {
			return {
				'fa-twitter': this.selected_network === 'twitter',
				'fa-facebook-official': this.selected_network === 'facebook',
				'fa-linkedin': this.selected_network === 'linkedin',
				'fa-tumblr': this.selected_network === 'tumblr'
			};
		},
		serviceId: function serviceId() {
			return 'service-' + this.modal.serviceName.toLowerCase();
		}
	}
	// </script>
	//
	// <style scoped>
	// 	#rop_core .sign-in-btn > .modal {
	// 		position: absolute;
	// 		top: 20px;
	// 	}
	//
	// 	#rop_core .sign-in-btn > .modal > .modal-container {
	// 		width: 100%;
	// 	}
	//
	// </style>

};

/***/ }),
/* 47 */
/***/ (function(module, exports, __webpack_require__) {

__webpack_require__(48);
module.exports = __webpack_require__(3).Object.keys;


/***/ }),
/* 48 */
/***/ (function(module, exports, __webpack_require__) {

// 19.1.2.14 Object.keys(O)
var toObject = __webpack_require__(22);
var $keys = __webpack_require__(23);

__webpack_require__(54)('keys', function () {
  return function keys(it) {
    return $keys(toObject(it));
  };
});


/***/ }),
/* 49 */
/***/ (function(module, exports, __webpack_require__) {

var has = __webpack_require__(7);
var toIObject = __webpack_require__(14);
var arrayIndexOf = __webpack_require__(51)(false);
var IE_PROTO = __webpack_require__(16)('IE_PROTO');

module.exports = function (object, names) {
  var O = toIObject(object);
  var i = 0;
  var result = [];
  var key;
  for (key in O) if (key != IE_PROTO) has(O, key) && result.push(key);
  // Don't enum bug & hidden keys
  while (names.length > i) if (has(O, key = names[i++])) {
    ~arrayIndexOf(result, key) || result.push(key);
  }
  return result;
};


/***/ }),
/* 50 */
/***/ (function(module, exports, __webpack_require__) {

// fallback for non-array-like ES3 and non-enumerable old V8 strings
var cof = __webpack_require__(24);
// eslint-disable-next-line no-prototype-builtins
module.exports = Object('z').propertyIsEnumerable(0) ? Object : function (it) {
  return cof(it) == 'String' ? it.split('') : Object(it);
};


/***/ }),
/* 51 */
/***/ (function(module, exports, __webpack_require__) {

// false -> Array#indexOf
// true  -> Array#includes
var toIObject = __webpack_require__(14);
var toLength = __webpack_require__(52);
var toAbsoluteIndex = __webpack_require__(53);
module.exports = function (IS_INCLUDES) {
  return function ($this, el, fromIndex) {
    var O = toIObject($this);
    var length = toLength(O.length);
    var index = toAbsoluteIndex(fromIndex, length);
    var value;
    // Array#includes uses SameValueZero equality algorithm
    // eslint-disable-next-line no-self-compare
    if (IS_INCLUDES && el != el) while (length > index) {
      value = O[index++];
      // eslint-disable-next-line no-self-compare
      if (value != value) return true;
    // Array#indexOf ignores holes, Array#includes - not
    } else for (;length > index; index++) if (IS_INCLUDES || index in O) {
      if (O[index] === el) return IS_INCLUDES || index || 0;
    } return !IS_INCLUDES && -1;
  };
};


/***/ }),
/* 52 */
/***/ (function(module, exports, __webpack_require__) {

// 7.1.15 ToLength
var toInteger = __webpack_require__(15);
var min = Math.min;
module.exports = function (it) {
  return it > 0 ? min(toInteger(it), 0x1fffffffffffff) : 0; // pow(2, 53) - 1 == 9007199254740991
};


/***/ }),
/* 53 */
/***/ (function(module, exports, __webpack_require__) {

var toInteger = __webpack_require__(15);
var max = Math.max;
var min = Math.min;
module.exports = function (index, length) {
  index = toInteger(index);
  return index < 0 ? max(index + length, 0) : min(index, length);
};


/***/ }),
/* 54 */
/***/ (function(module, exports, __webpack_require__) {

// most Object methods by ES6 should accept primitives
var $export = __webpack_require__(28);
var core = __webpack_require__(3);
var fails = __webpack_require__(19);
module.exports = function (KEY, exec) {
  var fn = (core.Object || {})[KEY] || Object[KEY];
  var exp = {};
  exp[KEY] = exec(fn);
  $export($export.S + $export.F * fails(function () { fn(1); }), 'Object', exp);
};


/***/ }),
/* 55 */
/***/ (function(module, exports, __webpack_require__) {

// optional / simple context binding
var aFunction = __webpack_require__(56);
module.exports = function (fn, that, length) {
  aFunction(fn);
  if (that === undefined) return fn;
  switch (length) {
    case 1: return function (a) {
      return fn.call(that, a);
    };
    case 2: return function (a, b) {
      return fn.call(that, a, b);
    };
    case 3: return function (a, b, c) {
      return fn.call(that, a, b, c);
    };
  }
  return function (/* ...args */) {
    return fn.apply(that, arguments);
  };
};


/***/ }),
/* 56 */
/***/ (function(module, exports) {

module.exports = function (it) {
  if (typeof it != 'function') throw TypeError(it + ' is not a function!');
  return it;
};


/***/ }),
/* 57 */
/***/ (function(module, exports, __webpack_require__) {

module.exports = !__webpack_require__(9) && !__webpack_require__(19)(function () {
  return Object.defineProperty(__webpack_require__(29)('div'), 'a', { get: function () { return 7; } }).a != 7;
});


/***/ }),
/* 58 */
/***/ (function(module, exports, __webpack_require__) {

// 7.1.1 ToPrimitive(input [, PreferredType])
var isObject = __webpack_require__(18);
// instead of the ES6 spec version, we didn't implement @@toPrimitive case
// and the second argument - flag - preferred type is a string
module.exports = function (it, S) {
  if (!isObject(it)) return it;
  var fn, val;
  if (S && typeof (fn = it.toString) == 'function' && !isObject(val = fn.call(it))) return val;
  if (typeof (fn = it.valueOf) == 'function' && !isObject(val = fn.call(it))) return val;
  if (!S && typeof (fn = it.toString) == 'function' && !isObject(val = fn.call(it))) return val;
  throw TypeError("Can't convert object to primitive value");
};


/***/ }),
/* 59 */
/***/ (function(module, exports, __webpack_require__) {

__webpack_require__(60);
__webpack_require__(71);
module.exports = __webpack_require__(73);


/***/ }),
/* 60 */
/***/ (function(module, exports, __webpack_require__) {

__webpack_require__(61);
var global = __webpack_require__(2);
var hide = __webpack_require__(5);
var Iterators = __webpack_require__(10);
var TO_STRING_TAG = __webpack_require__(4)('toStringTag');

var DOMIterables = ('CSSRuleList,CSSStyleDeclaration,CSSValueList,ClientRectList,DOMRectList,DOMStringList,' +
  'DOMTokenList,DataTransferItemList,FileList,HTMLAllCollection,HTMLCollection,HTMLFormElement,HTMLSelectElement,' +
  'MediaList,MimeTypeArray,NamedNodeMap,NodeList,PaintRequestList,Plugin,PluginArray,SVGLengthList,SVGNumberList,' +
  'SVGPathSegList,SVGPointList,SVGStringList,SVGTransformList,SourceBufferList,StyleSheetList,TextTrackCueList,' +
  'TextTrackList,TouchList').split(',');

for (var i = 0; i < DOMIterables.length; i++) {
  var NAME = DOMIterables[i];
  var Collection = global[NAME];
  var proto = Collection && Collection.prototype;
  if (proto && !proto[TO_STRING_TAG]) hide(proto, TO_STRING_TAG, NAME);
  Iterators[NAME] = Iterators.Array;
}


/***/ }),
/* 61 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

var addToUnscopables = __webpack_require__(62);
var step = __webpack_require__(63);
var Iterators = __webpack_require__(10);
var toIObject = __webpack_require__(14);

// 22.1.3.4 Array.prototype.entries()
// 22.1.3.13 Array.prototype.keys()
// 22.1.3.29 Array.prototype.values()
// 22.1.3.30 Array.prototype[@@iterator]()
module.exports = __webpack_require__(31)(Array, 'Array', function (iterated, kind) {
  this._t = toIObject(iterated); // target
  this._i = 0;                   // next index
  this._k = kind;                // kind
// 22.1.5.2.1 %ArrayIteratorPrototype%.next()
}, function () {
  var O = this._t;
  var kind = this._k;
  var index = this._i++;
  if (!O || index >= O.length) {
    this._t = undefined;
    return step(1);
  }
  if (kind == 'keys') return step(0, index);
  if (kind == 'values') return step(0, O[index]);
  return step(0, [index, O[index]]);
}, 'values');

// argumentsList[@@iterator] is %ArrayProto_values% (9.4.4.6, 9.4.4.7)
Iterators.Arguments = Iterators.Array;

addToUnscopables('keys');
addToUnscopables('values');
addToUnscopables('entries');


/***/ }),
/* 62 */
/***/ (function(module, exports) {

module.exports = function () { /* empty */ };


/***/ }),
/* 63 */
/***/ (function(module, exports) {

module.exports = function (done, value) {
  return { value: value, done: !!done };
};


/***/ }),
/* 64 */
/***/ (function(module, exports) {

module.exports = true;


/***/ }),
/* 65 */
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(5);


/***/ }),
/* 66 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

var create = __webpack_require__(67);
var descriptor = __webpack_require__(30);
var setToStringTag = __webpack_require__(32);
var IteratorPrototype = {};

// 25.1.2.1.1 %IteratorPrototype%[@@iterator]()
__webpack_require__(5)(IteratorPrototype, __webpack_require__(4)('iterator'), function () { return this; });

module.exports = function (Constructor, NAME, next) {
  Constructor.prototype = create(IteratorPrototype, { next: descriptor(1, next) });
  setToStringTag(Constructor, NAME + ' Iterator');
};


/***/ }),
/* 67 */
/***/ (function(module, exports, __webpack_require__) {

// 19.1.2.2 / 15.2.3.5 Object.create(O [, Properties])
var anObject = __webpack_require__(8);
var dPs = __webpack_require__(68);
var enumBugKeys = __webpack_require__(27);
var IE_PROTO = __webpack_require__(16)('IE_PROTO');
var Empty = function () { /* empty */ };
var PROTOTYPE = 'prototype';

// Create object with fake `null` prototype: use iframe Object with cleared prototype
var createDict = function () {
  // Thrash, waste and sodomy: IE GC bug
  var iframe = __webpack_require__(29)('iframe');
  var i = enumBugKeys.length;
  var lt = '<';
  var gt = '>';
  var iframeDocument;
  iframe.style.display = 'none';
  __webpack_require__(69).appendChild(iframe);
  iframe.src = 'javascript:'; // eslint-disable-line no-script-url
  // createDict = iframe.contentWindow.Object;
  // html.removeChild(iframe);
  iframeDocument = iframe.contentWindow.document;
  iframeDocument.open();
  iframeDocument.write(lt + 'script' + gt + 'document.F=Object' + lt + '/script' + gt);
  iframeDocument.close();
  createDict = iframeDocument.F;
  while (i--) delete createDict[PROTOTYPE][enumBugKeys[i]];
  return createDict();
};

module.exports = Object.create || function create(O, Properties) {
  var result;
  if (O !== null) {
    Empty[PROTOTYPE] = anObject(O);
    result = new Empty();
    Empty[PROTOTYPE] = null;
    // add "__proto__" for Object.getPrototypeOf polyfill
    result[IE_PROTO] = O;
  } else result = createDict();
  return Properties === undefined ? result : dPs(result, Properties);
};


/***/ }),
/* 68 */
/***/ (function(module, exports, __webpack_require__) {

var dP = __webpack_require__(17);
var anObject = __webpack_require__(8);
var getKeys = __webpack_require__(23);

module.exports = __webpack_require__(9) ? Object.defineProperties : function defineProperties(O, Properties) {
  anObject(O);
  var keys = getKeys(Properties);
  var length = keys.length;
  var i = 0;
  var P;
  while (length > i) dP.f(O, P = keys[i++], Properties[P]);
  return O;
};


/***/ }),
/* 69 */
/***/ (function(module, exports, __webpack_require__) {

var document = __webpack_require__(2).document;
module.exports = document && document.documentElement;


/***/ }),
/* 70 */
/***/ (function(module, exports, __webpack_require__) {

// 19.1.2.9 / 15.2.3.2 Object.getPrototypeOf(O)
var has = __webpack_require__(7);
var toObject = __webpack_require__(22);
var IE_PROTO = __webpack_require__(16)('IE_PROTO');
var ObjectProto = Object.prototype;

module.exports = Object.getPrototypeOf || function (O) {
  O = toObject(O);
  if (has(O, IE_PROTO)) return O[IE_PROTO];
  if (typeof O.constructor == 'function' && O instanceof O.constructor) {
    return O.constructor.prototype;
  } return O instanceof Object ? ObjectProto : null;
};


/***/ }),
/* 71 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

var $at = __webpack_require__(72)(true);

// 21.1.3.27 String.prototype[@@iterator]()
__webpack_require__(31)(String, 'String', function (iterated) {
  this._t = String(iterated); // target
  this._i = 0;                // next index
// 21.1.5.2.1 %StringIteratorPrototype%.next()
}, function () {
  var O = this._t;
  var index = this._i;
  var point;
  if (index >= O.length) return { value: undefined, done: true };
  point = $at(O, index);
  this._i += point.length;
  return { value: point, done: false };
});


/***/ }),
/* 72 */
/***/ (function(module, exports, __webpack_require__) {

var toInteger = __webpack_require__(15);
var defined = __webpack_require__(13);
// true  -> String#at
// false -> String#codePointAt
module.exports = function (TO_STRING) {
  return function (that, pos) {
    var s = String(defined(that));
    var i = toInteger(pos);
    var l = s.length;
    var a, b;
    if (i < 0 || i >= l) return TO_STRING ? '' : undefined;
    a = s.charCodeAt(i);
    return a < 0xd800 || a > 0xdbff || i + 1 === l || (b = s.charCodeAt(i + 1)) < 0xdc00 || b > 0xdfff
      ? TO_STRING ? s.charAt(i) : a
      : TO_STRING ? s.slice(i, i + 2) : (a - 0xd800 << 10) + (b - 0xdc00) + 0x10000;
  };
};


/***/ }),
/* 73 */
/***/ (function(module, exports, __webpack_require__) {

var anObject = __webpack_require__(8);
var get = __webpack_require__(74);
module.exports = __webpack_require__(3).getIterator = function (it) {
  var iterFn = get(it);
  if (typeof iterFn != 'function') throw TypeError(it + ' is not iterable!');
  return anObject(iterFn.call(it));
};


/***/ }),
/* 74 */
/***/ (function(module, exports, __webpack_require__) {

var classof = __webpack_require__(75);
var ITERATOR = __webpack_require__(4)('iterator');
var Iterators = __webpack_require__(10);
module.exports = __webpack_require__(3).getIteratorMethod = function (it) {
  if (it != undefined) return it[ITERATOR]
    || it['@@iterator']
    || Iterators[classof(it)];
};


/***/ }),
/* 75 */
/***/ (function(module, exports, __webpack_require__) {

// getting tag from 19.1.3.6 Object.prototype.toString()
var cof = __webpack_require__(24);
var TAG = __webpack_require__(4)('toStringTag');
// ES3 wrong here
var ARG = cof(function () { return arguments; }()) == 'Arguments';

// fallback for IE11 Script Access Denied error
var tryGet = function (it, key) {
  try {
    return it[key];
  } catch (e) { /* empty */ }
};

module.exports = function (it) {
  var O, T, B;
  return it === undefined ? 'Undefined' : it === null ? 'Null'
    // @@toStringTag case
    : typeof (T = tryGet(O = Object(it), TAG)) == 'string' ? T
    // builtinTag case
    : ARG ? cof(O)
    // ES3 arguments fallback
    : (B = cof(O)) == 'Object' && typeof O.callee == 'function' ? 'Arguments' : B;
};


/***/ }),
/* 76 */
/***/ (function(module, exports) {

module.exports = "\n\t<div class=\"sign-in-btn\" _v-7e903530=\"\">\n\t\t<div class=\"input-group\" _v-7e903530=\"\">\n\t\t\t<select class=\"form-select\" v-model=\"selected_network\" _v-7e903530=\"\">\n\t\t\t\t<option v-for=\"( service, network ) in services\" v-bind:value=\"network\" :disabled=\"checkDisabled( service.active )\" _v-7e903530=\"\">{{ service.name }}</option>\n\t\t\t</select>\n\n\t\t\t<button class=\"btn input-group-btn\" :class=\"serviceClass\" @click=\"requestAuthorization()\" :disabled=\"checkDisabled(true)\" _v-7e903530=\"\">\n\t\t\t\t<i class=\"fa fa-fw\" :class=\"serviceIcon\" aria-hidden=\"true\" _v-7e903530=\"\"></i> Sign In\n\t\t\t</button>\n\t\t</div>\n\t\t<div class=\"modal\" :class=\"modalActiveClass\" _v-7e903530=\"\">\n\t\t\t<div class=\"modal-overlay\" _v-7e903530=\"\"></div>\n\t\t\t<div class=\"modal-container\" _v-7e903530=\"\">\n\t\t\t\t<div class=\"modal-header\" _v-7e903530=\"\">\n\t\t\t\t\t<button class=\"btn btn-clear float-right\" @click=\"closeModal()\" _v-7e903530=\"\"></button>\n\t\t\t\t\t<div class=\"modal-title h5\" _v-7e903530=\"\">{{ modal.serviceName }} Service Credentials</div>\n\t\t\t\t</div>\n\t\t\t\t<div class=\"modal-body\" _v-7e903530=\"\">\n\t\t\t\t\t<div class=\"content\" _v-7e903530=\"\">\n\t\t\t\t\t\t<div class=\"form-group\" v-for=\"( field, id ) in modal.data\" _v-7e903530=\"\">\n\t\t\t\t\t\t\t<label class=\"form-label\" :for=\"field.id\" _v-7e903530=\"\">{{ field.name }}</label>\n\t\t\t\t\t\t\t<input class=\"form-input\" type=\"text\" :id=\"field.id\" v-model=\"field.value\" :placeholder=\"field.name\" _v-7e903530=\"\">\n\t\t\t\t\t\t\t<i _v-7e903530=\"\">{{ field.description }}</i>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t\t<div class=\"modal-footer\" _v-7e903530=\"\">\n\t\t\t\t\t<button class=\"btn btn-primary\" @click=\"closeModal()\" _v-7e903530=\"\">Sign in</button>\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t</div>\n\t</div>\n";

/***/ }),
/* 77 */
/***/ (function(module, exports, __webpack_require__) {

var __vue_script__, __vue_template__
__webpack_require__(78)
__vue_script__ = __webpack_require__(80)
__vue_template__ = __webpack_require__(87)
module.exports = __vue_script__ || {}
if (module.exports.__esModule) module.exports = module.exports.default
if (__vue_template__) { (typeof module.exports === "function" ? module.exports.options : module.exports).template = __vue_template__ }
if (false) {(function () {  module.hot.accept()
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), true)
  if (!hotAPI.compatible) return
  var id = "/var/www/html/wp-base/wp-content/plugins/tweet-old-post/vue/src/vue-elements/service-tile.vue"
  if (!module.hot.data) {
    hotAPI.createRecord(id, module.exports)
  } else {
    hotAPI.update(id, module.exports, __vue_template__)
  }
})()}

/***/ }),
/* 78 */
/***/ (function(module, exports, __webpack_require__) {

// style-loader: Adds some css to the DOM by adding a <style> tag

// load the styles
var content = __webpack_require__(79);
if(typeof content === 'string') content = [[module.i, content, '']];
// add the styles to the DOM
var update = __webpack_require__(1)(content, {});
if(content.locals) module.exports = content.locals;
// Hot Module Replacement
if(false) {
	// When the styles change, update the <style> tags
	if(!content.locals) {
		module.hot.accept("!!../../../node_modules/css-loader/index.js!../../../node_modules/vue-loader/lib/style-rewriter.js?id=_v-4ed4525c&file=service-tile.vue&scoped=true!../../../node_modules/vue-loader/lib/selector.js?type=style&index=0!../../../node_modules/eslint-loader/index.js!../../../node_modules/eslint-loader/index.js!./service-tile.vue", function() {
			var newContent = require("!!../../../node_modules/css-loader/index.js!../../../node_modules/vue-loader/lib/style-rewriter.js?id=_v-4ed4525c&file=service-tile.vue&scoped=true!../../../node_modules/vue-loader/lib/selector.js?type=style&index=0!../../../node_modules/eslint-loader/index.js!../../../node_modules/eslint-loader/index.js!./service-tile.vue");
			if(typeof newContent === 'string') newContent = [[module.id, newContent, '']];
			update(newContent);
		});
	}
	// When the module is disposed, remove the <style> tags
	module.hot.dispose(function() { update(); });
}

/***/ }),
/* 79 */
/***/ (function(module, exports, __webpack_require__) {

exports = module.exports = __webpack_require__(0)();
// imports


// module
exports.push([module.i, "\n\n    #rop_core .btn.btn-danger[_v-4ed4525c] {\n        background-color: #d50000;\n        color: #efefef;\n        border-color: #b71c1c;\n    }\n\n    #rop_core .btn.btn-danger[_v-4ed4525c]:hover, #rop_core[_v-4ed4525c] {\n        background-color: #efefef;\n        color: #d50000;\n        border-color: #b71c1c;\n    }\n\n    #rop_core .btn.btn-info[_v-4ed4525c] {\n        background-color: #2196f3;\n        color: #efefef;\n        border-color: #1565c0;\n    }\n\n    #rop_core .btn.btn-info[_v-4ed4525c]:hover, #rop_core[_v-4ed4525c] {\n        background-color: #efefef;\n        color: #2196f3;\n        border-color: #1565c0;\n    }\n\n", ""]);

// exports


/***/ }),
/* 80 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _serviceAutocomplete = __webpack_require__(81);

var _serviceAutocomplete2 = _interopRequireDefault(_serviceAutocomplete);

var _secretInput = __webpack_require__(84);

var _secretInput2 = _interopRequireDefault(_secretInput);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

// <template>
//     <div class="service-tile">
//         <label class="show-md hide-xl"><b>{{service_url}}/</b></label>
//         <div class="input-group">
//             <button class="btn input-group-btn btn-danger" @click="removeService()" >
//                 <i class="fa fa-fw fa-trash" aria-hidden="true"></i>
//             </button>
//             <button class="btn input-group-btn btn-info" @click="toggleCredentials()" v-if="service.public_credentials" >
//                 <i class="fa fa-fw fa-info-circle" aria-hidden="true"></i>
//             </button>
//             <span class="input-group-addon hide-md" style="min-width: 115px; text-align: right;">{{service_url}}/</span>
//             <service-autocomplete :accounts="service.available_accounts" :to_be_activated="to_be_activated"></service-autocomplete>
//             <button class="btn input-group-btn" :class="serviceClass" @click="activateSelected( service.id )">
//                 <i class="fa fa-fw fa-plus" aria-hidden="true"></i> <span class="hide-md">Activate</span>
//             </button>
//         </div>
//         <div class="card centered" :class="credentialsDisplayClass" v-if="service.public_credentials">
//             <div class="card-header">
//                 <div class="card-title h5">{{serviceName}}</div>
//                 <div class="card-subtitle text-gray">{{service.id}}</div>
//             </div>
//             <div class="card-body">
//                 <div class="form-horizontal">
//                     <div class="form-group" v-for="( credential, index ) in service.public_credentials">
//                         <div class="col-3">
//                             <label class="form-label" :for="credentialID(index)">{{credential.name}}:</label>
//                         </div>
//                         <div class="col-9">
//                             <secret-input :id="credentialID(index)" :value="credential.value" :secret="credential.private" />
//                         </div>
//                     </div>
//                 </div>
//             </div>
//         </div>
//         <div class="divider clearfix"></div>
//     </div>
// </template>
//
// <script>
function capitalizeFirstLetter(string) {
	return string.charAt(0).toUpperCase().concat(string.slice(1));
}

module.exports = {
	name: 'service-tile',
	props: {
		service: {
			type: Object,
			required: true
		}
	},
	data: function data() {
		return {
			show_credentials: false,
			to_be_activated: []
		};
	},
	computed: {
		service_url: function service_url() {
			if (this.service.service === 'facebook') {
				return 'facebook.com';
			}
			if (this.service.service === 'twitter') {
				return 'twitter.com';
			}
			if (this.service.service === 'linkedin') {
				return 'linkedin.com';
			}
			if (this.service.service === 'tumblr') {
				return 'tumblr.com';
			}

			return 'service.url';
		},
		serviceName: function serviceName() {
			return capitalizeFirstLetter(this.service.service);
		},
		serviceClass: function serviceClass() {
			return {
				'btn-twitter': this.service.service === 'twitter',
				'btn-facebook': this.service.service === 'facebook',
				'btn-linkedin': this.service.service === 'linkedin',
				'btn-tumblr': this.service.service === 'tumblr'
			};
		},
		credentialsDisplayClass: function credentialsDisplayClass() {
			return {
				'd-block': this.show_credentials === true,
				'd-none': this.show_credentials === false
			};
		}
	},
	methods: {
		credentialID: function credentialID(index) {
			return 'service-' + index + '-field';
		},
		toggleCredentials: function toggleCredentials() {
			this.show_credentials = !this.show_credentials;
		},
		activateSelected: function activateSelected(serviceId) {
			this.$store.dispatch('updateActiveAccounts', { action: 'update', service_id: serviceId, service: this.service.service, to_be_activated: this.to_be_activated, current_active: this.$store.state.activeAccounts });
		},
		removeService: function removeService() {
			this.$store.dispatch('removeService', { id: this.service.id, service: this.service.service });
		}
	},
	components: {
		ServiceAutocomplete: _serviceAutocomplete2.default,
		SecretInput: _secretInput2.default
	}
	// </script>
	//
	// <style scoped>
	//
	//     #rop_core .btn.btn-danger {
	//         background-color: #d50000;
	//         color: #efefef;
	//         border-color: #b71c1c;
	//     }
	//
	//     #rop_core .btn.btn-danger:hover, #rop_core {
	//         background-color: #efefef;
	//         color: #d50000;
	//         border-color: #b71c1c;
	//     }
	//
	//     #rop_core .btn.btn-info {
	//         background-color: #2196f3;
	//         color: #efefef;
	//         border-color: #1565c0;
	//     }
	//
	//     #rop_core .btn.btn-info:hover, #rop_core {
	//         background-color: #efefef;
	//         color: #2196f3;
	//         border-color: #1565c0;
	//     }
	//
	// </style>

};

/***/ }),
/* 81 */
/***/ (function(module, exports, __webpack_require__) {

var __vue_script__, __vue_template__
__vue_script__ = __webpack_require__(82)
__vue_template__ = __webpack_require__(83)
module.exports = __vue_script__ || {}
if (module.exports.__esModule) module.exports = module.exports.default
if (__vue_template__) { (typeof module.exports === "function" ? module.exports.options : module.exports).template = __vue_template__ }
if (false) {(function () {  module.hot.accept()
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), true)
  if (!hotAPI.compatible) return
  var id = "/var/www/html/wp-base/wp-content/plugins/tweet-old-post/vue/src/vue-elements/service-autocomplete.vue"
  if (!module.hot.data) {
    hotAPI.createRecord(id, module.exports)
  } else {
    hotAPI.update(id, module.exports, __vue_template__)
  }
})()}

/***/ }),
/* 82 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _getIterator2 = __webpack_require__(20);

var _getIterator3 = _interopRequireDefault(_getIterator2);

var _vueClickaway = __webpack_require__(33);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function containsObject(obj, list) {
	var i = void 0;
	for (i = 0; i < list.length; i++) {
		if (list[i] === obj) {
			return true;
		}
	}
	return false;
} // <template>
//     <div class="form-autocomplete" style="width: 100%;" v-on-clickaway="closeDropdown">
//         <!-- autocomplete input container -->
//         <div class="form-autocomplete-input form-input" :class="is_focused">
//
//             <!-- autocomplete chips -->
//             <label class="chip" v-for="( account, index ) in to_be_activated">
//                 <img :src="getImg(account.img)" class="avatar avatar-sm" alt="{account.name}">
//                 {{account.name}}
//                 <a href="#" class="btn btn-clear" aria-label="Close" @click.prevent="removeToBeActivated(index)" role="button" v-if="!is_one"></a>
//             </label>
//
//             <!-- autocomplete real input box -->
//             <input style="height: 1.0rem;" class="form-input" type="text" ref="search" v-model="search" :placeholder="autocomplete_placeholder" @click="magic_flag = true" @focus="magic_flag = true" @keyup="magic_flag = true" @keydown.8="popLast()" @keydown.38="highlightItem(true)" @keydown.40="highlightItem()" :readonly="is_one">
//         </div>
//
//         <!-- autocomplete suggestion list -->
//         <ul class="menu" ref="autocomplete_results" :class="is_visible" v-if="!is_one">
//             <!-- menu list chips -->
//             <li class="menu-item" v-for="( account, index ) in accounts" v-if="filterSearch(account)">
//                 <a href="#" @click.prevent="addToBeActivated(index)" @keydown.38="highlightItem(true)" @keydown.40="highlightItem()">
//                     <div class="tile tile-centered">
//                         <div class="tile-icon">
//                             <img :src="getImg(account.img)" class="avatar avatar-sm" alt="{account.name}">
//                         </div>
//                         <div class="tile-content" v-html="markMatch(account.name, search)"></div>
//                     </div>
//                 </a>
//             </li>
//             <li v-if="has_results">
//                 <a href="#">
//                     <div class="tile tile-centered">
//                         <div class="tile-content"><i>Nothing found matching "{{search}}" ...</i></div>
//                     </div>
//                 </a>
//             </li>
//         </ul>
//     </div>
//
// </template>
//
// <script>
/* global ROP_ASSETS_URL */


module.exports = {
	name: 'service-autocomplete',
	mixins: [_vueClickaway.mixin],
	props: ['accounts', 'to_be_activated'],
	mounted: function mounted() {
		var index = 0;
		var _iteratorNormalCompletion = true;
		var _didIteratorError = false;
		var _iteratorError = undefined;

		try {
			for (var _iterator = (0, _getIterator3.default)(this.accounts), _step; !(_iteratorNormalCompletion = (_step = _iterator.next()).done); _iteratorNormalCompletion = true) {
				var account = _step.value;

				if (account.active) {
					this.addToBeActivated(index);
				}
				index++;
			}
		} catch (err) {
			_didIteratorError = true;
			_iteratorError = err;
		} finally {
			try {
				if (!_iteratorNormalCompletion && _iterator.return) {
					_iterator.return();
				}
			} finally {
				if (_didIteratorError) {
					throw _iteratorError;
				}
			}
		}
	},

	data: function data() {
		return {
			search: '',
			highlighted: -1,
			no_results: false,
			magic_flag: false,
			account_def_img: ROP_ASSETS_URL + 'img/accounts_icon.jpg'
		};
	},
	computed: {
		is_focused: function is_focused() {
			return {
				'is-focused': this.magic_flag === true
			};
		},
		is_visible: function is_visible() {
			return {
				'd-none': this.magic_flag === false
			};
		},
		is_one: function is_one() {
			if (this.accounts.length === 1 && this.accounts[0].active === false) {
				this.to_be_activated.push(this.accounts[0]);
				return true;
			} else if (this.accounts.length === 1 && this.accounts[0].active === true) {
				return true;
			}
			return false;
		},
		autocomplete_placeholder: function autocomplete_placeholder() {
			if (this.is_one) {
				return '';
			}
			return 'Accounts ...';
		},
		has_results: function has_results() {
			var found = 0;
			var _iteratorNormalCompletion2 = true;
			var _didIteratorError2 = false;
			var _iteratorError2 = undefined;

			try {
				for (var _iterator2 = (0, _getIterator3.default)(this.accounts), _step2; !(_iteratorNormalCompletion2 = (_step2 = _iterator2.next()).done); _iteratorNormalCompletion2 = true) {
					var account = _step2.value;

					if (this.filterSearch(account)) {
						found++;
					}
				}
			} catch (err) {
				_didIteratorError2 = true;
				_iteratorError2 = err;
			} finally {
				try {
					if (!_iteratorNormalCompletion2 && _iterator2.return) {
						_iterator2.return();
					}
				} finally {
					if (_didIteratorError2) {
						throw _iteratorError2;
					}
				}
			}

			if (found) {
				return false;
			}
			return true;
		}
	},
	methods: {
		closeDropdown: function closeDropdown() {
			this.magic_flag = false;
		},
		highlightItem: function highlightItem() {
			var up = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : false;

			if (up) {
				this.highlighted--;
			} else {
				this.highlighted++;
			}
			var size = this.$refs.autocomplete_results.children.length - 1;
			if (size < 0) size = 0;
			if (this.highlighted > size) this.highlighted = 0;
			if (this.highlighted < 0) this.highlighted = size;
			this.$refs.autocomplete_results.children[this.highlighted].firstChild.focus();
		},
		popLast: function popLast() {
			if (this.search === '') {
				this.to_be_activated.pop();
				this.magic_flag = false;
			}
		},
		markMatch: function markMatch(value, search) {
			var result = value;
			if (value.toLowerCase().indexOf(search.toLowerCase()) !== -1 && search !== '') {
				var rex = new RegExp(search, 'ig');
				result = value.replace(rex, function (match) {
					return '<mark>' + match + '</mark>';
				});
			}
			return result;
		},
		getImg: function getImg(img) {
			if (img === '' || img === undefined || img === null) {
				return this.account_def_img;
			}
			return img;
		},
		filterSearch: function filterSearch(element) {
			if (element.name.toLowerCase().indexOf(this.search.toLowerCase()) !== -1 || this.search === '') {
				if (containsObject(element, this.to_be_activated)) {
					return false;
				}
				return true;
			}
			return false;
		},
		addToBeActivated: function addToBeActivated(index) {
			this.to_be_activated.push(this.accounts[index]);
			this.$refs.search.focus();
			this.magic_flag = false;
			this.search = '';
		},
		removeToBeActivated: function removeToBeActivated(index) {
			this.to_be_activated.splice(index, 1);
			this.$refs.search.focus();
			this.magic_flag = false;
			this.search = '';
		}
	}
	// </script>

};

/***/ }),
/* 83 */
/***/ (function(module, exports) {

module.exports = "\n    <div class=\"form-autocomplete\" style=\"width: 100%;\" v-on-clickaway=\"closeDropdown\">\n        <!-- autocomplete input container -->\n        <div class=\"form-autocomplete-input form-input\" :class=\"is_focused\">\n\n            <!-- autocomplete chips -->\n            <label class=\"chip\" v-for=\"( account, index ) in to_be_activated\">\n                <img :src=\"getImg(account.img)\" class=\"avatar avatar-sm\" alt=\"{account.name}\">\n                {{account.name}}\n                <a href=\"#\" class=\"btn btn-clear\" aria-label=\"Close\" @click.prevent=\"removeToBeActivated(index)\" role=\"button\" v-if=\"!is_one\"></a>\n            </label>\n\n            <!-- autocomplete real input box -->\n            <input style=\"height: 1.0rem;\" class=\"form-input\" type=\"text\" ref=\"search\" v-model=\"search\" :placeholder=\"autocomplete_placeholder\" @click=\"magic_flag = true\" @focus=\"magic_flag = true\" @keyup=\"magic_flag = true\" @keydown.8=\"popLast()\" @keydown.38=\"highlightItem(true)\" @keydown.40=\"highlightItem()\" :readonly=\"is_one\">\n        </div>\n\n        <!-- autocomplete suggestion list -->\n        <ul class=\"menu\" ref=\"autocomplete_results\" :class=\"is_visible\" v-if=\"!is_one\">\n            <!-- menu list chips -->\n            <li class=\"menu-item\" v-for=\"( account, index ) in accounts\" v-if=\"filterSearch(account)\">\n                <a href=\"#\" @click.prevent=\"addToBeActivated(index)\" @keydown.38=\"highlightItem(true)\" @keydown.40=\"highlightItem()\">\n                    <div class=\"tile tile-centered\">\n                        <div class=\"tile-icon\">\n                            <img :src=\"getImg(account.img)\" class=\"avatar avatar-sm\" alt=\"{account.name}\">\n                        </div>\n                        <div class=\"tile-content\" v-html=\"markMatch(account.name, search)\"></div>\n                    </div>\n                </a>\n            </li>\n            <li v-if=\"has_results\">\n                <a href=\"#\">\n                    <div class=\"tile tile-centered\">\n                        <div class=\"tile-content\"><i>Nothing found matching \"{{search}}\" ...</i></div>\n                    </div>\n                </a>\n            </li>\n        </ul>\n    </div>\n\n";

/***/ }),
/* 84 */
/***/ (function(module, exports, __webpack_require__) {

var __vue_script__, __vue_template__
__vue_script__ = __webpack_require__(85)
__vue_template__ = __webpack_require__(86)
module.exports = __vue_script__ || {}
if (module.exports.__esModule) module.exports = module.exports.default
if (__vue_template__) { (typeof module.exports === "function" ? module.exports.options : module.exports).template = __vue_template__ }
if (false) {(function () {  module.hot.accept()
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), true)
  if (!hotAPI.compatible) return
  var id = "/var/www/html/wp-base/wp-content/plugins/tweet-old-post/vue/src/vue-elements/reusables/secret-input.vue"
  if (!module.hot.data) {
    hotAPI.createRecord(id, module.exports)
  } else {
    hotAPI.update(id, module.exports, __vue_template__)
  }
})()}

/***/ }),
/* 85 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


// <template>
//     <div class="input-group" v-if="secret">
//         <input class="form-input" :type="input_type" :id="id" :value="value" :readonly="readonly">
//         <button class="btn input-group-btn" @mouseenter="showHideSecret()" @mouseleave="showHideSecret()"><i class="fa fa-fw" :class="visibileClass"></i></button>
//     </div>
//     <input class="form-input" type="text" :id="id" :value="value" :readonly="readonly" v-else>
// </template>
// <script>
module.exports = {
	name: 'secret-input',
	props: {
		id: {
			default: ''
		},
		secret: {
			type: Boolean,
			default: true
		},
		value: {
			default: ''
		},
		readonly: {
			type: Boolean,
			default: true
		}
	},
	data: function data() {
		return {
			visible: false
		};
	},
	computed: {
		input_type: function input_type() {
			if (this.visible) {
				return 'text';
			}
			return 'password';
		},
		visibileClass: function visibileClass() {
			return {
				'fa-eye': this.visible === true,
				'fa-eye-slash': this.visible === false
			};
		}
	},
	methods: {
		showHideSecret: function showHideSecret() {
			this.visible = !this.visible;
		}
	}
	// </script>

};

/***/ }),
/* 86 */
/***/ (function(module, exports) {

module.exports = "\n    <div class=\"input-group\" v-if=\"secret\">\n        <input class=\"form-input\" :type=\"input_type\" :id=\"id\" :value=\"value\" :readonly=\"readonly\">\n        <button class=\"btn input-group-btn\" @mouseenter=\"showHideSecret()\" @mouseleave=\"showHideSecret()\"><i class=\"fa fa-fw\" :class=\"visibileClass\"></i></button>\n    </div>\n    <input class=\"form-input\" type=\"text\" :id=\"id\" :value=\"value\" :readonly=\"readonly\" v-else>\n";

/***/ }),
/* 87 */
/***/ (function(module, exports) {

module.exports = "\n    <div class=\"service-tile\" _v-4ed4525c=\"\">\n        <label class=\"show-md hide-xl\" _v-4ed4525c=\"\"><b _v-4ed4525c=\"\">{{service_url}}/</b></label>\n        <div class=\"input-group\" _v-4ed4525c=\"\">\n            <button class=\"btn input-group-btn btn-danger\" @click=\"removeService()\" _v-4ed4525c=\"\">\n                <i class=\"fa fa-fw fa-trash\" aria-hidden=\"true\" _v-4ed4525c=\"\"></i>\n            </button>\n            <button class=\"btn input-group-btn btn-info\" @click=\"toggleCredentials()\" v-if=\"service.public_credentials\" _v-4ed4525c=\"\">\n                <i class=\"fa fa-fw fa-info-circle\" aria-hidden=\"true\" _v-4ed4525c=\"\"></i>\n            </button>\n            <span class=\"input-group-addon hide-md\" style=\"min-width: 115px; text-align: right;\" _v-4ed4525c=\"\">{{service_url}}/</span>\n            <service-autocomplete :accounts=\"service.available_accounts\" :to_be_activated=\"to_be_activated\" _v-4ed4525c=\"\"></service-autocomplete>\n            <button class=\"btn input-group-btn\" :class=\"serviceClass\" @click=\"activateSelected( service.id )\" _v-4ed4525c=\"\">\n                <i class=\"fa fa-fw fa-plus\" aria-hidden=\"true\" _v-4ed4525c=\"\"></i> <span class=\"hide-md\" _v-4ed4525c=\"\">Activate</span>\n            </button>\n        </div>\n        <div class=\"card centered\" :class=\"credentialsDisplayClass\" v-if=\"service.public_credentials\" _v-4ed4525c=\"\">\n            <div class=\"card-header\" _v-4ed4525c=\"\">\n                <div class=\"card-title h5\" _v-4ed4525c=\"\">{{serviceName}}</div>\n                <div class=\"card-subtitle text-gray\" _v-4ed4525c=\"\">{{service.id}}</div>\n            </div>\n            <div class=\"card-body\" _v-4ed4525c=\"\">\n                <div class=\"form-horizontal\" _v-4ed4525c=\"\">\n                    <div class=\"form-group\" v-for=\"( credential, index ) in service.public_credentials\" _v-4ed4525c=\"\">\n                        <div class=\"col-3\" _v-4ed4525c=\"\">\n                            <label class=\"form-label\" :for=\"credentialID(index)\" _v-4ed4525c=\"\">{{credential.name}}:</label>\n                        </div>\n                        <div class=\"col-9\" _v-4ed4525c=\"\">\n                            <secret-input :id=\"credentialID(index)\" :value=\"credential.value\" :secret=\"credential.private\" _v-4ed4525c=\"\">\n                        </secret-input></div>\n                    </div>\n                </div>\n            </div>\n        </div>\n        <div class=\"divider clearfix\" _v-4ed4525c=\"\"></div>\n    </div>\n";

/***/ }),
/* 88 */
/***/ (function(module, exports, __webpack_require__) {

var __vue_script__, __vue_template__
__webpack_require__(89)
__vue_script__ = __webpack_require__(91)
__vue_template__ = __webpack_require__(92)
module.exports = __vue_script__ || {}
if (module.exports.__esModule) module.exports = module.exports.default
if (__vue_template__) { (typeof module.exports === "function" ? module.exports.options : module.exports).template = __vue_template__ }
if (false) {(function () {  module.hot.accept()
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), true)
  if (!hotAPI.compatible) return
  var id = "/var/www/html/wp-base/wp-content/plugins/tweet-old-post/vue/src/vue-elements/service-user-tile.vue"
  if (!module.hot.data) {
    hotAPI.createRecord(id, module.exports)
  } else {
    hotAPI.update(id, module.exports, __vue_template__)
  }
})()}

/***/ }),
/* 89 */
/***/ (function(module, exports, __webpack_require__) {

// style-loader: Adds some css to the DOM by adding a <style> tag

// load the styles
var content = __webpack_require__(90);
if(typeof content === 'string') content = [[module.i, content, '']];
// add the styles to the DOM
var update = __webpack_require__(1)(content, {});
if(content.locals) module.exports = content.locals;
// Hot Module Replacement
if(false) {
	// When the styles change, update the <style> tags
	if(!content.locals) {
		module.hot.accept("!!../../../node_modules/css-loader/index.js!../../../node_modules/vue-loader/lib/style-rewriter.js?id=_v-d8a56e08&file=service-user-tile.vue&scoped=true!../../../node_modules/vue-loader/lib/selector.js?type=style&index=0!../../../node_modules/eslint-loader/index.js!../../../node_modules/eslint-loader/index.js!./service-user-tile.vue", function() {
			var newContent = require("!!../../../node_modules/css-loader/index.js!../../../node_modules/vue-loader/lib/style-rewriter.js?id=_v-d8a56e08&file=service-user-tile.vue&scoped=true!../../../node_modules/vue-loader/lib/selector.js?type=style&index=0!../../../node_modules/eslint-loader/index.js!../../../node_modules/eslint-loader/index.js!./service-user-tile.vue");
			if(typeof newContent === 'string') newContent = [[module.id, newContent, '']];
			update(newContent);
		});
	}
	// When the module is disposed, remove the <style> tags
	module.hot.dispose(function() { update(); });
}

/***/ }),
/* 90 */
/***/ (function(module, exports, __webpack_require__) {

exports = module.exports = __webpack_require__(0)();
// imports


// module
exports.push([module.i, "\n    #rop_core .btn.btn-link.btn-danger[_v-d8a56e08] {\n        color: #d50000;\n    }\n    #rop_core .btn.btn-link.btn-danger[_v-d8a56e08]:hover {\n        color: #b71c1c;\n    }\n\n    .has_image[_v-d8a56e08] {\n        border-radius: 50%;\n    }\n\n    .service_account_image[_v-d8a56e08] {\n        width: 150%;\n        border-radius: 50%;\n        margin-left: -25%;\n        margin-top: -25%;\n    }\n\n    .icon_box[_v-d8a56e08] {\n        width: 45px;\n        height: 45px;\n        padding: 7px;\n        text-align: center;\n        background-color: #333333;\n        color: #efefef;\n    }\n\n    .icon_box > .fa[_v-d8a56e08] {\n        width: 30px;\n        height: 30px;\n        font-size: 30px;\n    }\n\n    .facebook[_v-d8a56e08] {\n        background-color: #3b5998;\n    }\n\n    .twitter[_v-d8a56e08] {\n        background-color: #55acee;\n    }\n\n    .linkedin[_v-d8a56e08] {\n        background-color: #007bb5;\n    }\n\n    .tumblr[_v-d8a56e08] {\n        background-color: #32506d;\n    }\n\n", ""]);

// exports


/***/ }),
/* 91 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


// <template>
//     <div class="tile tile-centered">
//         <div class="tile-icon">
//             <div class="icon_box" :class="service">
//                 <img class="service_account_image" :src="img" v-if="img" />
//                 <i class="fa" :class="icon" aria-hidden="true" v-else></i>
//             </div>
//         </div>
//         <div class="tile-content">
//             <div class="tile-title">{{ user }}</div>
//             <div class="tile-subtitle text-gray">{{ serviceInfo }}</div>
//         </div>
//         <div class="tile-action">
//             <div class="dropdown dropdown-right">
//                 <a href="#" class="btn btn-link btn-danger" tabindex="0" @click.prevent="removeActiveAccount( account_id )">
//                     <i class="fa fa-trash" aria-hidden="true"></i>
//                 </a>
//             </div>
//         </div>
//     </div>
// </template>
//
// <script>
module.exports = {
	name: 'service-user-tile',
	props: ['account_data', 'account_id'],
	computed: {
		service: function service() {
			var iconClass = this.account_data.service;
			if (this.img !== '') {
				iconClass = iconClass.concat(' ').concat('has_image');
			}
			return iconClass;
		},
		icon: function icon() {
			var serviceIcon = 'fa-';
			if (this.account_data.service === 'facebook') serviceIcon = serviceIcon.concat('facebook-official');
			if (this.account_data.service === 'twitter') serviceIcon = serviceIcon.concat('twitter');
			if (this.account_data.service === 'linkedin') serviceIcon = serviceIcon.concat('linkedin');
			if (this.account_data.service === 'tumblr') serviceIcon = serviceIcon.concat('tumblr');
			return serviceIcon;
		},
		img: function img() {
			var img = '';
			if (this.account_data.img !== '' && this.account_data.img !== undefined) {
				img = this.account_data.img;
			}
			return img;
		},
		user: function user() {
			return this.account_data.user;
		},
		serviceInfo: function serviceInfo() {
			var serviceTextInfo = this.account_data.account.concat(' at: ').concat(this.account_data.created);
			return serviceTextInfo;
		}
	},
	methods: {
		removeActiveAccount: function removeActiveAccount(id) {
			this.$store.dispatch('updateActiveAccounts', { action: 'remove', account_id: id, current_active: this.$store.state.activeAccounts });
		}
	}
	// </script>
	//
	// <style scoped>
	//     #rop_core .btn.btn-link.btn-danger {
	//         color: #d50000;
	//     }
	//     #rop_core .btn.btn-link.btn-danger:hover {
	//         color: #b71c1c;
	//     }
	//
	//     .has_image {
	//         border-radius: 50%;
	//     }
	//
	//     .service_account_image {
	//         width: 150%;
	//         border-radius: 50%;
	//         margin-left: -25%;
	//         margin-top: -25%;
	//     }
	//
	//     .icon_box {
	//         width: 45px;
	//         height: 45px;
	//         padding: 7px;
	//         text-align: center;
	//         background-color: #333333;
	//         color: #efefef;
	//     }
	//
	//     .icon_box > .fa {
	//         width: 30px;
	//         height: 30px;
	//         font-size: 30px;
	//     }
	//
	//     .facebook {
	//         background-color: #3b5998;
	//     }
	//
	//     .twitter {
	//         background-color: #55acee;
	//     }
	//
	//     .linkedin {
	//         background-color: #007bb5;
	//     }
	//
	//     .tumblr {
	//         background-color: #32506d;
	//     }
	//
	// </style>

};

/***/ }),
/* 92 */
/***/ (function(module, exports) {

module.exports = "\n    <div class=\"tile tile-centered\" _v-d8a56e08=\"\">\n        <div class=\"tile-icon\" _v-d8a56e08=\"\">\n            <div class=\"icon_box\" :class=\"service\" _v-d8a56e08=\"\">\n                <img class=\"service_account_image\" :src=\"img\" v-if=\"img\" _v-d8a56e08=\"\">\n                <i class=\"fa\" :class=\"icon\" aria-hidden=\"true\" v-else=\"\" _v-d8a56e08=\"\"></i>\n            </div>\n        </div>\n        <div class=\"tile-content\" _v-d8a56e08=\"\">\n            <div class=\"tile-title\" _v-d8a56e08=\"\">{{ user }}</div>\n            <div class=\"tile-subtitle text-gray\" _v-d8a56e08=\"\">{{ serviceInfo }}</div>\n        </div>\n        <div class=\"tile-action\" _v-d8a56e08=\"\">\n            <div class=\"dropdown dropdown-right\" _v-d8a56e08=\"\">\n                <a href=\"#\" class=\"btn btn-link btn-danger\" tabindex=\"0\" @click.prevent=\"removeActiveAccount( account_id )\" _v-d8a56e08=\"\">\n                    <i class=\"fa fa-trash\" aria-hidden=\"true\" _v-d8a56e08=\"\"></i>\n                </a>\n            </div>\n        </div>\n    </div>\n";

/***/ }),
/* 93 */
/***/ (function(module, exports) {

module.exports = "\n    <div class=\"tab-view\">\n        <div class=\"panel-body\">\n            <h3>Accounts</h3>\n            <p>This is a <b>Vue.js</b> component.</p>\n            <div class=\"container\">\n                <div class=\"columns\">\n                    <div class=\"column col-sm-12 col-md-12 col-lg-6\">\n                        <div class=\"columns\">\n                            <div class=\"column col-sm-12 col-md-12 col-xl-6 col-8 text-right\">\n                                <b>New Service</b><br/>\n                                <i>Select a service and sign in with an account for that service.</i>\n                            </div>\n                            <div class=\"column col-sm-12 col-md-12 col-xl-6 col-4 text-left\">\n                                <sign-in-btn></sign-in-btn>\n                            </div>\n                        </div>\n                        <div class=\"columns\">\n                            <div class=\"column col-sm-12 col-md-12 col-lg-12 text-left\">\n                                <hr/>\n                                <h5>Authenticated Services</h5>\n                                <div class=\"empty\" v-if=\"authenticated_services.length == 0\">\n                                    <div class=\"empty-icon\">\n                                        <i class=\"fa fa-3x fa-cloud\"></i>\n                                    </div>\n                                    <p class=\"empty-title h5\">No authenticated service!</p>\n                                    <p class=\"empty-subtitle\">Add one from the <b>\"New Service\"</b> section.</p>\n                                </div>\n                                <service-tile v-for=\"service in authenticated_services\" :key=\"service.id\" :service=\"service\"></service-tile>\n                            </div>\n                        </div>\n                    </div>\n                    <div class=\"column col-sm-12 col-md-12 col-lg-6 text-left\">\n                        <hr style=\"margin-top: 45px\" />\n                        <h5>Active Accounts</h5>\n                        <div class=\"empty\" v-if=\"active_accounts.length == 0\">\n                            <div class=\"empty-icon\">\n                                <i class=\"fa fa-3x fa-user-circle-o\"></i>\n                            </div>\n                            <p class=\"empty-title h5\">No active accounts!</p>\n                            <p class=\"empty-subtitle\">Add one from the <b>\"Authenticated Services\"</b> section.</p>\n                        </div>\n                        <div v-for=\"( account, id ) in active_accounts\">\n                            <service-user-tile :account_data=\"account\" :account_id=\"id\"></service-user-tile>\n                            <div class=\"divider\"></div>\n                        </div>\n                    </div>\n                </div>\n            </div>\n            <div class=\"columns\">\n                <div class=\"column col-12\">\n                    <h4><i class=\"fa fa-info-circle\"></i> Info</h4>\n                    <p><i>Authenticate a new service (eg. Facebook, Twitter etc. ), select the accounts you want to add from that service and <b>activate</b> them. Only the accounts displayed in the <b>\"Active accounts\"</b> section will be used.</i></p>\n                </div>\n            </div>\n        </div>\n        <div class=\"panel-footer\">\n            <button class=\"btn btn-primary\">Save</button>\n        </div>\n    </div>\n";

/***/ }),
/* 94 */
/***/ (function(module, exports, __webpack_require__) {

var __vue_script__, __vue_template__
__vue_script__ = __webpack_require__(95)
__vue_template__ = __webpack_require__(104)
module.exports = __vue_script__ || {}
if (module.exports.__esModule) module.exports = module.exports.default
if (__vue_template__) { (typeof module.exports === "function" ? module.exports.options : module.exports).template = __vue_template__ }
if (false) {(function () {  module.hot.accept()
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), true)
  if (!hotAPI.compatible) return
  var id = "/var/www/html/wp-base/wp-content/plugins/tweet-old-post/vue/src/vue-elements/settings-tab-panel.vue"
  if (!module.hot.data) {
    hotAPI.createRecord(id, module.exports)
  } else {
    hotAPI.update(id, module.exports, __vue_template__)
  }
})()}

/***/ }),
/* 95 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _counterInput = __webpack_require__(96);

var _counterInput2 = _interopRequireDefault(_counterInput);

var _multipleSelect = __webpack_require__(101);

var _multipleSelect2 = _interopRequireDefault(_multipleSelect);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

// <template>
// 	<div class="tab-view">
// 		<div class="panel-body" style="overflow: inherit;">
// 			<h3>General Settings</h3>
// 			<p>This is a <b>Vue.js</b> component.</p>
// 			<div class="container">
// 				<div class="columns">
// 					<!-- Minimum age of posts available for sharing, in days
// 					(number) -->
// 					<div class="column col-sm-12 col-md-12 col-lg-6">
// 						<div class="columns">
// 							<div class="column col-sm-12 col-md-6 col-xl-6 col-8 text-right">
// 								<b>Minimum post age</b><br/>
// 								<i>Minimum age of posts available for sharing, in days.</i>
// 							</div>
// 							<div class="column col-sm-12 col-md-6 col-xl-6 col-4 text-left">
// 								<counter-input id="min_post_age" :maxVal="365" :value.sync="generalSettings.minimum_post_age" />
// 							</div>
// 						</div>
// 					</div>
// 					<!-- Maximum age of posts available for sharing, in days
// 					(number) -->
// 					<div class="column col-sm-12 col-md-12 col-lg-6">
// 						<div class="columns">
// 							<div class="column col-sm-12 col-md-6 col-xl-6 col-4 text-right">
// 								<counter-input id="max_post_age" :maxVal="365" :value.sync="generalSettings.maximum_post_age" />
// 							</div>
// 							<div class="column col-sm-12 col-md-6 col-xl-6 col-8 text-left">
// 								<b>Maximum post age</b><br/>
// 								<i>Maximum age of posts available for sharing, in days.</i>
// 							</div>
// 						</div>
// 					</div>
// 				</div>
// 				<hr/>
// 				<div class="columns">
// 					<!-- Number of posts to share per account per trigger
// 					(number) -->
// 					<div class="column col-sm-12 col-md-12 col-lg-6">
// 						<div class="columns">
// 							<div class="column col-sm-12 col-md-6 col-xl-6 col-8 text-right">
// 								<b>Number of posts</b><br/>
// 								<i>Number of posts to share per. account per. trigger of scheduled job.</i>
// 							</div>
// 							<div class="column col-sm-12 col-md-6 col-xl-6 col-4 text-left">
// 								<counter-input id="no_of_posts" :value.sync="generalSettings.number_of_posts" />
// 							</div>
// 						</div>
// 					</div>
// 					<!-- Share more than once, if there are no more posts to share, we should start re-sharing the one we
// 					previously shared
// 					(boolean) -->
// 					<div class="column col-sm-12 col-md-12 col-lg-6">
// 						<div class="columns">
// 							<div class="column col-sm-12 col-md-2 col-xl-2 col-1 text-right">
// 								<div class="form-group">
// 									<label class="form-checkbox">
// 										<input type="checkbox" v-model="generalSettings.more_than_once" />
// 										<i class="form-icon"></i> Yes
// 									</label>
// 								</div>
// 							</div>
// 							<div class="column col-sm-12 col-md-10 col-xl-10 col-11 text-left">
// 								<b>Share more than once?</b><br/>
// 								<i>If there are no more posts to share, we should start re-sharing the one we previously shared.</i>
// 							</div>
// 						</div>
// 					</div>
// 				</div>
// 				<hr/>
// 				<div class="columns">
// 					<!-- Post types available to share - what post types are available for share
// 					( multi-select list ) -->
// 					<div class="column col-sm-12 col-md-12 col-lg-12">
// 						<div class="columns">
// 							<div class="column col-sm-12 col-md-4 col-xl-3 col-ml-2 col-4 text-right">
// 								<b>Post types</b><br/>
// 								<i>Post types available to share - what post types are available for share</i>
// 							</div>
// 							<div class="column col-sm-12 col-md-8 col-xl-9 col-mr-4 col-7 text-left">
// 								<multiple-select :options="postTypes" :selected="generalSettings.selected_post_types" :changedSelection="updatedPostTypes" />
// 							</div>
// 						</div>
// 					</div>
// 				</div>
// 				<hr/>
// 				<div class="columns">
// 					<!-- Taxonomies available for posts to share - based on what post types users choose to share, we should
// 					show the taxonomies available for that post type, along with their terms, which user can select to share.
// 					Here we should have also a toggle if either the taxonomies selected are included or excluded.
// 					( multi-select list ) -->
// 					<div class="column col-sm-12 col-md-12 col-lg-12">
// 						<div class="columns">
// 							<div class="column col-sm-12 col-md-4 col-xl-3 col-ml-2 col-4 text-right">
// 								<b>Taxonomies</b><br/>
// 								<i>Taxonomies available for the selected post types. Use to include or exclude posts.</i>
// 							</div>
// 							<div class="column col-sm-12 col-md-8 col-xl-9 col-mr-4 col-7 text-left">
// 								<div class="input-group">
// 									<multiple-select :options="taxonomies" :selected="generalSettings.selected_taxonomies" :changedSelection="updatedTaxonomies" />
// 									<span class="input-group-addon">
// 										<label class="form-checkbox">
// 											<input type="checkbox" v-model="generalSettings.exclude_taxonomies" @change="exludeTaxonomiesChange" />
// 											<i class="form-icon"></i> Exclude?
// 										</label>
// 									</span>
// 								</div>
// 							</div>
// 						</div>
// 					</div>
// 				</div>
// 				<hr/>
// 				<div class="columns">
// 					<!-- Posts excluded/included in sharing - what posts we should exclude or include in sharing
// 					- we should have have an autocomplete list which should fetch posts from the previously select post_types
// 					and terms and allow them to be include/excluded.
// 					( multi-select list ) -->
// 					<div class="column col-sm-12 col-md-12 col-lg-12">
// 						<div class="columns">
// 							<div class="column col-sm-12 col-md-4 col-xl-3 col-ml-2 col-4 text-right">
// 								<b>Posts</b><br/>
// 								<i>Posts excluded/included in sharing, filtered based on previous selections.</i>
// 							</div>
// 							<div class="column col-sm-12 col-md-8 col-xl-9 col-mr-4 col-7 text-left">
// 								<div class="input-group">
// 									<multiple-select :searchQuery="searchQuery" @update="searchUpdate" :options="postsAvailable" :dontLock="true" :selected="generalSettings.selected_posts" :changedSelection="updatedPosts" />
// 									<span class="input-group-addon">
// 										<label class="form-checkbox">
// 											<input type="checkbox" v-model="generalSettings.exclude_posts" />
// 											<i class="form-icon"></i> Exclude?
// 										</label>
// 									</span>
// 								</div>
// 							</div>
// 						</div>
// 					</div>
// 				</div>
// 			</div>
// 		</div>
// 		<div class="panel-footer">
// 			<button class="btn btn-primary" @click="saveGeneralSettings()"><i class="fa fa-check"></i> Save</button>
// 		</div>
// 	</div>
// </template>
//
// <script>
module.exports = {
	name: 'settings-view',
	data: function data() {
		return {
			searchQuery: ''
		};
	},
	computed: {
		generalSettings: function generalSettings() {
			return this.$store.state.generalSettings;
		},
		postTypes: function postTypes() {
			return this.$store.state.generalSettings.available_post_types;
		},
		taxonomies: function taxonomies() {
			this.requestPostUpdate();
			return this.$store.state.generalSettings.available_taxonomies;
		},
		postsAvailable: function postsAvailable() {
			return this.$store.state.generalSettings.available_posts;
		}
	},
	methods: {
		searchUpdate: function searchUpdate(newQuery) {
			this.searchQuery = newQuery;
			this.requestPostUpdate();
		},
		updatedPostTypes: function updatedPostTypes(data) {
			var postTypes = [];
			for (var index in data) {
				postTypes.push(data[index].value);
			}
			this.$store.commit('updateSelectedPostTypes', data);
			this.$store.dispatch('fetchTaxonomies', { post_types: postTypes });
			this.requestPostUpdate();
		},
		updatedTaxonomies: function updatedTaxonomies(data) {
			var taxonomies = [];
			for (var index in data) {
				taxonomies.push(data[index].value);
			}
			this.$store.commit('updateSelectedTaxonomies', data);
			this.requestPostUpdate();
		},
		updatedPosts: function updatedPosts(data) {
			this.$store.commit('updateSelectedPosts', data);
		},
		exludeTaxonomiesChange: function exludeTaxonomiesChange() {
			this.requestPostUpdate();
		},
		requestPostUpdate: function requestPostUpdate() {
			var postTypesSelected = this.$store.state.generalSettings.selected_post_types;
			var taxonomiesSelected = this.$store.state.generalSettings.selected_taxonomies;

			this.$store.dispatch('fetchPosts', { post_types: postTypesSelected, search_query: this.searchQuery, taxonomies: taxonomiesSelected, exclude: this.generalSettings.exclude_taxonomies });
		},
		saveGeneralSettings: function saveGeneralSettings() {
			var postTypesSelected = this.$store.state.generalSettings.selected_post_types;
			var taxonomiesSelected = this.$store.state.generalSettings.selected_taxonomies;
			var excludeTaxonomies = this.generalSettings.exclude_taxonomies;
			var postsSelected = this.generalSettings.selected_posts;

			this.$store.dispatch('saveGeneralSettings', {
				available_taxonomies: this.generalSettings.available_taxonomies,
				minimum_post_age: this.generalSettings.minimum_post_age,
				maximum_post_age: this.generalSettings.maximum_post_age,
				number_of_posts: this.generalSettings.number_of_posts,
				more_than_once: this.generalSettings.more_than_once,
				post_types: postTypesSelected,
				taxonomies: taxonomiesSelected,
				exclude_taxonomies: excludeTaxonomies,
				posts: postsSelected,
				exclude_posts: this.generalSettings.exclude_posts
			});
		}
	},
	components: {
		CounterInput: _counterInput2.default,
		MultipleSelect: _multipleSelect2.default
	}
	// </script>

};

/***/ }),
/* 96 */
/***/ (function(module, exports, __webpack_require__) {

var __vue_script__, __vue_template__
__webpack_require__(97)
__vue_script__ = __webpack_require__(99)
__vue_template__ = __webpack_require__(100)
module.exports = __vue_script__ || {}
if (module.exports.__esModule) module.exports = module.exports.default
if (__vue_template__) { (typeof module.exports === "function" ? module.exports.options : module.exports).template = __vue_template__ }
if (false) {(function () {  module.hot.accept()
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), true)
  if (!hotAPI.compatible) return
  var id = "/var/www/html/wp-base/wp-content/plugins/tweet-old-post/vue/src/vue-elements/reusables/counter-input.vue"
  if (!module.hot.data) {
    hotAPI.createRecord(id, module.exports)
  } else {
    hotAPI.update(id, module.exports, __vue_template__)
  }
})()}

/***/ }),
/* 97 */
/***/ (function(module, exports, __webpack_require__) {

// style-loader: Adds some css to the DOM by adding a <style> tag

// load the styles
var content = __webpack_require__(98);
if(typeof content === 'string') content = [[module.i, content, '']];
// add the styles to the DOM
var update = __webpack_require__(1)(content, {});
if(content.locals) module.exports = content.locals;
// Hot Module Replacement
if(false) {
	// When the styles change, update the <style> tags
	if(!content.locals) {
		module.hot.accept("!!../../../../node_modules/css-loader/index.js!../../../../node_modules/vue-loader/lib/style-rewriter.js?id=_v-0e4d6f14&file=counter-input.vue!../../../../node_modules/vue-loader/lib/selector.js?type=style&index=0!../../../../node_modules/eslint-loader/index.js!../../../../node_modules/eslint-loader/index.js!./counter-input.vue", function() {
			var newContent = require("!!../../../../node_modules/css-loader/index.js!../../../../node_modules/vue-loader/lib/style-rewriter.js?id=_v-0e4d6f14&file=counter-input.vue!../../../../node_modules/vue-loader/lib/selector.js?type=style&index=0!../../../../node_modules/eslint-loader/index.js!../../../../node_modules/eslint-loader/index.js!./counter-input.vue");
			if(typeof newContent === 'string') newContent = [[module.id, newContent, '']];
			update(newContent);
		});
	}
	// When the module is disposed, remove the <style> tags
	module.hot.dispose(function() { update(); });
}

/***/ }),
/* 98 */
/***/ (function(module, exports, __webpack_require__) {

exports = module.exports = __webpack_require__(0)();
// imports


// module
exports.push([module.i, "\n\t#rop_core .input-group.rop-counter-group {\n\t\tposition: relative;\n\t}\n\t#rop_core .btn.increment-btn {\n\t\tposition: absolute;\n\t\tright: 0;\n\t\twidth: 1rem;\n\t\theight: 0.85rem;\n\t\tpadding: 0.025rem 0.010rem;\n\t\tline-height: 0.3rem;\n\t\tz-index: 2;\n\t}\n\n\t#rop_core .btn.increment-btn.up { top: 0; }\n\t#rop_core .btn.increment-btn.down { bottom: 0; }\n\n\tinput.rop-counter::-webkit-inner-spin-button {\n\t\tdisplay: none;\n\t}\n", ""]);

// exports


/***/ }),
/* 99 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


// <template>
// 	<div class="input-group rop-counter-group">
// 		<input class="form-input rop-counter" type="number" :id="id" :value="value" readonly>
// 		<button class="btn input-group-btn increment-btn up" @mousedown="isPressed('up')" @mouseup="isReleased('up')"><i class="fa fa-fw fa-caret-up"></i></button>
// 		<button class="btn input-group-btn increment-btn down" @mousedown="isPressed('down')" @mouseup="isReleased('down')"><i class="fa fa-fw fa-caret-down"></i></button>
// 	</div>
// </template>
//
// <script>
var intervalID = null;

module.exports = {
	name: 'counter-input',
	props: {
		id: {
			default: ''
		},
		value: {
			default: 0,
			type: Number
		},
		allowNegative: {
			default: false,
			type: Boolean
		},
		minVal: {
			default: 0,
			type: Number
		},
		maxVal: {
			default: 0,
			type: Number
		}
	},
	data: function data() {
		return {
			pressStartTime: null,
			incrementUp: 0,
			incrementDown: 0,
			inputValue: 0
		};
	},
	methods: {
		updateInput: function updateInput() {
			this.inputValue = this.value;
			var now = new Date();
			var secondsPassed = parseInt((now.getTime() - this.pressStartTime.getTime()) / 1000);
			var increment = secondsPassed;
			if (secondsPassed === 0) increment = 1;

			if (this.incrementUp === 1) {
				this.inputValue += increment;
				if (this.inputValue > this.maxVal && this.maxVal !== 0) this.inputValue = this.maxVal;
			}
			if (this.incrementDown === 1) {
				this.inputValue -= increment;
				if (this.inputValue < 0 && this.allowNegative === false) this.inputValue = 0;
				if (this.inputValue < this.minVal) this.inputValue = this.minVal;
			}
			this.$emit('update:value', this.inputValue);
		},
		isPressed: function isPressed(type) {
			if (type === 'up') {
				this.incrementUp = 1;
			} else {
				this.incrementDown = 1;
			}
			this.pressStartTime = new Date();
			this.updateInput();
			intervalID = setInterval(this.updateInput, 250);
		},
		isReleased: function isReleased(type) {
			if (type === 'up') {
				this.incrementUp = 0;
			} else {
				this.incrementDown = 0;
			}
			this.pressStartTime = null;
			clearInterval(intervalID);
		}
	}
	// </script>
	//
	// <style>
	// 	#rop_core .input-group.rop-counter-group {
	// 		position: relative;
	// 	}
	// 	#rop_core .btn.increment-btn {
	// 		position: absolute;
	// 		right: 0;
	// 		width: 1rem;
	// 		height: 0.85rem;
	// 		padding: 0.025rem 0.010rem;
	// 		line-height: 0.3rem;
	// 		z-index: 2;
	// 	}
	//
	// 	#rop_core .btn.increment-btn.up { top: 0; }
	// 	#rop_core .btn.increment-btn.down { bottom: 0; }
	//
	// 	input.rop-counter::-webkit-inner-spin-button {
	// 		display: none;
	// 	}
	// </style>

};

/***/ }),
/* 100 */
/***/ (function(module, exports) {

module.exports = "\n\t<div class=\"input-group rop-counter-group\">\n\t\t<input class=\"form-input rop-counter\" type=\"number\" :id=\"id\" :value=\"value\" readonly>\n\t\t<button class=\"btn input-group-btn increment-btn up\" @mousedown=\"isPressed('up')\" @mouseup=\"isReleased('up')\"><i class=\"fa fa-fw fa-caret-up\"></i></button>\n\t\t<button class=\"btn input-group-btn increment-btn down\" @mousedown=\"isPressed('down')\" @mouseup=\"isReleased('down')\"><i class=\"fa fa-fw fa-caret-down\"></i></button>\n\t</div>\n";

/***/ }),
/* 101 */
/***/ (function(module, exports, __webpack_require__) {

var __vue_script__, __vue_template__
__vue_script__ = __webpack_require__(102)
__vue_template__ = __webpack_require__(103)
module.exports = __vue_script__ || {}
if (module.exports.__esModule) module.exports = module.exports.default
if (__vue_template__) { (typeof module.exports === "function" ? module.exports.options : module.exports).template = __vue_template__ }
if (false) {(function () {  module.hot.accept()
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), true)
  if (!hotAPI.compatible) return
  var id = "/var/www/html/wp-base/wp-content/plugins/tweet-old-post/vue/src/vue-elements/reusables/multiple-select.vue"
  if (!module.hot.data) {
    hotAPI.createRecord(id, module.exports)
  } else {
    hotAPI.update(id, module.exports, __vue_template__)
  }
})()}

/***/ }),
/* 102 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _getIterator2 = __webpack_require__(20);

var _getIterator3 = _interopRequireDefault(_getIterator2);

var _vueClickaway = __webpack_require__(33);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function containsObject(obj, list) {
	var i = void 0;
	for (i = 0; i < list.length; i++) {
		if (list[i] === obj) {
			return true;
		}
	}
	return false;
} // <template>
// 	<div class="form-autocomplete" style="width: 100%;" v-on-clickaway="closeDropdown">
// 		<!-- autocomplete input container -->
// 		<div class="form-autocomplete-input form-input" :class="is_focused">
//
// 			<!-- autocomplete chips -->
// 			<label class="chip" v-for="( option, index ) in selected">
// 				{{option.name}}
// 				<a href="#" class="btn btn-clear" aria-label="Close" @click.prevent="removeSelected(index)" role="button" v-if="!is_one"></a>
// 			</label>
//
// 			<!-- autocomplete real input box -->
// 			<input style="height: 1.0rem;" class="form-input" type="text" ref="search" v-model="search" :placeholder="autocomplete_placeholder" @click="magic_flag = true" @focus="magic_flag = true" @keyup="magic_flag = true" @keydown.8="popLast()" @keydown.38="highlightItem(true)" @keydown.40="highlightItem()" :readonly="is_one">
// 		</div>
//
// 		<!-- autocomplete suggestion list -->
// 		<ul class="menu" ref="autocomplete_results" :class="is_visible" v-if="!is_one" style="overflow-y: scroll; max-height: 120px">
// 			<!-- menu list chips -->
// 			<li class="menu-item" v-for="( option, index ) in options" v-if="filterSearch(option)">
// 				<a href="#" @click.prevent="addToSelected(index)" @keydown.38="highlightItem(true)" @keydown.40="highlightItem()">
// 					<div class="tile tile-centered">
// 						<div class="tile-content" v-html="markMatch(option.name, search)"></div>
// 					</div>
// 				</a>
// 			</li>
// 			<li v-if="has_results">
// 				<a href="#">
// 					<div class="tile tile-centered">
// 						<div class="tile-content"><i>Nothing found matching "{{search}}" ...</i></div>
// 					</div>
// 				</a>
// 			</li>
// 		</ul>
// 	</div>
//
// </template>
//
// <script>


module.exports = {
	name: 'multiple-select',
	mixins: [_vueClickaway.mixin],
	props: {
		options: {
			default: function _default() {
				return [];
			},
			type: Array
		},
		selected: {
			default: function _default() {
				return [];
			},
			type: Array
		},
		placeHolderText: {
			default: '',
			type: String
		},
		changedSelection: {
			default: function _default(data) {
				return true;
			},
			type: Function
		},
		dontLock: {
			default: false,
			type: Boolean
		}
	},
	mounted: function mounted() {
		var index = 0;
		var _iteratorNormalCompletion = true;
		var _didIteratorError = false;
		var _iteratorError = undefined;

		try {
			for (var _iterator = (0, _getIterator3.default)(this.options), _step; !(_iteratorNormalCompletion = (_step = _iterator.next()).done); _iteratorNormalCompletion = true) {
				var option = _step.value;

				if (option.selected) {
					this.addToSelected(index);
				}
				index++;
			}
			// this.$emit( 'update', this.search )
		} catch (err) {
			_didIteratorError = true;
			_iteratorError = err;
		} finally {
			try {
				if (!_iteratorNormalCompletion && _iterator.return) {
					_iterator.return();
				}
			} finally {
				if (_didIteratorError) {
					throw _iteratorError;
				}
			}
		}
	},

	data: function data() {
		return {
			search: '',
			highlighted: -1,
			no_results: false,
			magic_flag: false
		};
	},
	watch: {
		search: function search(val) {
			this.$emit('update', val);
		}
	},
	computed: {
		is_focused: function is_focused() {
			return {
				'is-focused': this.magic_flag === true
			};
		},
		is_visible: function is_visible() {
			return {
				'd-none': this.magic_flag === false
			};
		},
		is_one: function is_one() {
			if (!this.dontLock) {
				if (this.options.length === 1 && this.options[0].selected === false) {
					this.selected.push(this.options[0]);
					return true;
				} else if (this.options.length === 1 && this.options[0].selected === true) {
					return true;
				}
			}
			return false;
		},
		autocomplete_placeholder: function autocomplete_placeholder() {
			if (this.is_one) {
				return '';
			}
			return this.placeHolderText;
		},
		has_results: function has_results() {
			var found = 0;
			var _iteratorNormalCompletion2 = true;
			var _didIteratorError2 = false;
			var _iteratorError2 = undefined;

			try {
				for (var _iterator2 = (0, _getIterator3.default)(this.options), _step2; !(_iteratorNormalCompletion2 = (_step2 = _iterator2.next()).done); _iteratorNormalCompletion2 = true) {
					var option = _step2.value;

					if (this.filterSearch(option)) {
						found++;
					}
				}
			} catch (err) {
				_didIteratorError2 = true;
				_iteratorError2 = err;
			} finally {
				try {
					if (!_iteratorNormalCompletion2 && _iterator2.return) {
						_iterator2.return();
					}
				} finally {
					if (_didIteratorError2) {
						throw _iteratorError2;
					}
				}
			}

			if (found) {
				return false;
			}
			return true;
		}
	},
	methods: {
		closeDropdown: function closeDropdown() {
			this.magic_flag = false;
		},
		highlightItem: function highlightItem() {
			var up = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : false;

			if (up) {
				this.highlighted--;
			} else {
				this.highlighted++;
			}
			var size = this.$refs.autocomplete_results.children.length - 1;
			if (size < 0) size = 0;
			if (this.highlighted > size) this.highlighted = 0;
			if (this.highlighted < 0) this.highlighted = size;
			this.$refs.autocomplete_results.children[this.highlighted].firstChild.focus();
		},
		popLast: function popLast() {
			if (this.search === '') {
				this.selected.pop();
				this.magic_flag = false;
			}
		},
		markMatch: function markMatch(value, search) {
			var result = value;
			if (value.toLowerCase().indexOf(search.toLowerCase()) !== -1 && search !== '') {
				var rex = new RegExp(search, 'ig');
				result = value.replace(rex, function (match) {
					return '<mark>' + match + '</mark>';
				});
			}
			return result;
		},
		filterSearch: function filterSearch(element) {
			if (element.name.toLowerCase().indexOf(this.search.toLowerCase()) !== -1 || this.search === '') {
				if (element.selected) {
					return false;
				}
				if (containsObject(element, this.selected)) {
					return false;
				}
				return true;
			}
			return false;
		},
		addToSelected: function addToSelected(index) {
			var newSelection = this.options[index];
			newSelection.selected = true;
			this.selected.push(newSelection);
			this.$refs.search.focus();
			this.magic_flag = false;
			this.search = '';
			this.changedSelection(this.selected);
		},
		removeSelected: function removeSelected(index) {
			this.selected.splice(index, 1);
			this.$refs.search.focus();
			this.magic_flag = false;
			this.search = '';
			this.changedSelection(this.selected);
		}
	}
	// </script>

};

/***/ }),
/* 103 */
/***/ (function(module, exports) {

module.exports = "\n\t<div class=\"form-autocomplete\" style=\"width: 100%;\" v-on-clickaway=\"closeDropdown\">\n\t\t<!-- autocomplete input container -->\n\t\t<div class=\"form-autocomplete-input form-input\" :class=\"is_focused\">\n\n\t\t\t<!-- autocomplete chips -->\n\t\t\t<label class=\"chip\" v-for=\"( option, index ) in selected\">\n\t\t\t\t{{option.name}}\n\t\t\t\t<a href=\"#\" class=\"btn btn-clear\" aria-label=\"Close\" @click.prevent=\"removeSelected(index)\" role=\"button\" v-if=\"!is_one\"></a>\n\t\t\t</label>\n\n\t\t\t<!-- autocomplete real input box -->\n\t\t\t<input style=\"height: 1.0rem;\" class=\"form-input\" type=\"text\" ref=\"search\" v-model=\"search\" :placeholder=\"autocomplete_placeholder\" @click=\"magic_flag = true\" @focus=\"magic_flag = true\" @keyup=\"magic_flag = true\" @keydown.8=\"popLast()\" @keydown.38=\"highlightItem(true)\" @keydown.40=\"highlightItem()\" :readonly=\"is_one\">\n\t\t</div>\n\n\t\t<!-- autocomplete suggestion list -->\n\t\t<ul class=\"menu\" ref=\"autocomplete_results\" :class=\"is_visible\" v-if=\"!is_one\" style=\"overflow-y: scroll; max-height: 120px\">\n\t\t\t<!-- menu list chips -->\n\t\t\t<li class=\"menu-item\" v-for=\"( option, index ) in options\" v-if=\"filterSearch(option)\">\n\t\t\t\t<a href=\"#\" @click.prevent=\"addToSelected(index)\" @keydown.38=\"highlightItem(true)\" @keydown.40=\"highlightItem()\">\n\t\t\t\t\t<div class=\"tile tile-centered\">\n\t\t\t\t\t\t<div class=\"tile-content\" v-html=\"markMatch(option.name, search)\"></div>\n\t\t\t\t\t</div>\n\t\t\t\t</a>\n\t\t\t</li>\n\t\t\t<li v-if=\"has_results\">\n\t\t\t\t<a href=\"#\">\n\t\t\t\t\t<div class=\"tile tile-centered\">\n\t\t\t\t\t\t<div class=\"tile-content\"><i>Nothing found matching \"{{search}}\" ...</i></div>\n\t\t\t\t\t</div>\n\t\t\t\t</a>\n\t\t\t</li>\n\t\t</ul>\n\t</div>\n\n";

/***/ }),
/* 104 */
/***/ (function(module, exports) {

module.exports = "\n\t<div class=\"tab-view\">\n\t\t<div class=\"panel-body\" style=\"overflow: inherit;\">\n\t\t\t<h3>General Settings</h3>\n\t\t\t<p>This is a <b>Vue.js</b> component.</p>\n\t\t\t<div class=\"container\">\n\t\t\t\t<div class=\"columns\">\n\t\t\t\t\t<!-- Minimum age of posts available for sharing, in days\n\t\t\t\t\t(number) -->\n\t\t\t\t\t<div class=\"column col-sm-12 col-md-12 col-lg-6\">\n\t\t\t\t\t\t<div class=\"columns\">\n\t\t\t\t\t\t\t<div class=\"column col-sm-12 col-md-6 col-xl-6 col-8 text-right\">\n\t\t\t\t\t\t\t\t<b>Minimum post age</b><br/>\n\t\t\t\t\t\t\t\t<i>Minimum age of posts available for sharing, in days.</i>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t<div class=\"column col-sm-12 col-md-6 col-xl-6 col-4 text-left\">\n\t\t\t\t\t\t\t\t<counter-input id=\"min_post_age\" :maxVal=\"365\" :value.sync=\"generalSettings.minimum_post_age\" />\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</div>\n\t\t\t\t\t<!-- Maximum age of posts available for sharing, in days\n\t\t\t\t\t(number) -->\n\t\t\t\t\t<div class=\"column col-sm-12 col-md-12 col-lg-6\">\n\t\t\t\t\t\t<div class=\"columns\">\n\t\t\t\t\t\t\t<div class=\"column col-sm-12 col-md-6 col-xl-6 col-4 text-right\">\n\t\t\t\t\t\t\t\t<counter-input id=\"max_post_age\" :maxVal=\"365\" :value.sync=\"generalSettings.maximum_post_age\" />\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t<div class=\"column col-sm-12 col-md-6 col-xl-6 col-8 text-left\">\n\t\t\t\t\t\t\t\t<b>Maximum post age</b><br/>\n\t\t\t\t\t\t\t\t<i>Maximum age of posts available for sharing, in days.</i>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t\t<hr/>\n\t\t\t\t<div class=\"columns\">\n\t\t\t\t\t<!-- Number of posts to share per account per trigger\n\t\t\t\t\t(number) -->\n\t\t\t\t\t<div class=\"column col-sm-12 col-md-12 col-lg-6\">\n\t\t\t\t\t\t<div class=\"columns\">\n\t\t\t\t\t\t\t<div class=\"column col-sm-12 col-md-6 col-xl-6 col-8 text-right\">\n\t\t\t\t\t\t\t\t<b>Number of posts</b><br/>\n\t\t\t\t\t\t\t\t<i>Number of posts to share per. account per. trigger of scheduled job.</i>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t<div class=\"column col-sm-12 col-md-6 col-xl-6 col-4 text-left\">\n\t\t\t\t\t\t\t\t<counter-input id=\"no_of_posts\" :value.sync=\"generalSettings.number_of_posts\" />\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</div>\n\t\t\t\t\t<!-- Share more than once, if there are no more posts to share, we should start re-sharing the one we\n\t\t\t\t\tpreviously shared\n\t\t\t\t\t(boolean) -->\n\t\t\t\t\t<div class=\"column col-sm-12 col-md-12 col-lg-6\">\n\t\t\t\t\t\t<div class=\"columns\">\n\t\t\t\t\t\t\t<div class=\"column col-sm-12 col-md-2 col-xl-2 col-1 text-right\">\n\t\t\t\t\t\t\t\t<div class=\"form-group\">\n\t\t\t\t\t\t\t\t\t<label class=\"form-checkbox\">\n\t\t\t\t\t\t\t\t\t\t<input type=\"checkbox\" v-model=\"generalSettings.more_than_once\" />\n\t\t\t\t\t\t\t\t\t\t<i class=\"form-icon\"></i> Yes\n\t\t\t\t\t\t\t\t\t</label>\n\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t<div class=\"column col-sm-12 col-md-10 col-xl-10 col-11 text-left\">\n\t\t\t\t\t\t\t\t<b>Share more than once?</b><br/>\n\t\t\t\t\t\t\t\t<i>If there are no more posts to share, we should start re-sharing the one we previously shared.</i>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t\t<hr/>\n\t\t\t\t<div class=\"columns\">\n\t\t\t\t\t<!-- Post types available to share - what post types are available for share\n\t\t\t\t\t( multi-select list ) -->\n\t\t\t\t\t<div class=\"column col-sm-12 col-md-12 col-lg-12\">\n\t\t\t\t\t\t<div class=\"columns\">\n\t\t\t\t\t\t\t<div class=\"column col-sm-12 col-md-4 col-xl-3 col-ml-2 col-4 text-right\">\n\t\t\t\t\t\t\t\t<b>Post types</b><br/>\n\t\t\t\t\t\t\t\t<i>Post types available to share - what post types are available for share</i>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t<div class=\"column col-sm-12 col-md-8 col-xl-9 col-mr-4 col-7 text-left\">\n\t\t\t\t\t\t\t\t<multiple-select :options=\"postTypes\" :selected=\"generalSettings.selected_post_types\" :changedSelection=\"updatedPostTypes\" />\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t\t<hr/>\n\t\t\t\t<div class=\"columns\">\n\t\t\t\t\t<!-- Taxonomies available for posts to share - based on what post types users choose to share, we should\n\t\t\t\t\tshow the taxonomies available for that post type, along with their terms, which user can select to share.\n\t\t\t\t\tHere we should have also a toggle if either the taxonomies selected are included or excluded.\n\t\t\t\t\t( multi-select list ) -->\n\t\t\t\t\t<div class=\"column col-sm-12 col-md-12 col-lg-12\">\n\t\t\t\t\t\t<div class=\"columns\">\n\t\t\t\t\t\t\t<div class=\"column col-sm-12 col-md-4 col-xl-3 col-ml-2 col-4 text-right\">\n\t\t\t\t\t\t\t\t<b>Taxonomies</b><br/>\n\t\t\t\t\t\t\t\t<i>Taxonomies available for the selected post types. Use to include or exclude posts.</i>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t<div class=\"column col-sm-12 col-md-8 col-xl-9 col-mr-4 col-7 text-left\">\n\t\t\t\t\t\t\t\t<div class=\"input-group\">\n\t\t\t\t\t\t\t\t\t<multiple-select :options=\"taxonomies\" :selected=\"generalSettings.selected_taxonomies\" :changedSelection=\"updatedTaxonomies\" />\n\t\t\t\t\t\t\t\t\t<span class=\"input-group-addon\">\n\t\t\t\t\t\t\t\t\t\t<label class=\"form-checkbox\">\n\t\t\t\t\t\t\t\t\t\t\t<input type=\"checkbox\" v-model=\"generalSettings.exclude_taxonomies\" @change=\"exludeTaxonomiesChange\" />\n\t\t\t\t\t\t\t\t\t\t\t<i class=\"form-icon\"></i> Exclude?\n\t\t\t\t\t\t\t\t\t\t</label>\n\t\t\t\t\t\t\t\t\t</span>\n\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t\t<hr/>\n\t\t\t\t<div class=\"columns\">\n\t\t\t\t\t<!-- Posts excluded/included in sharing - what posts we should exclude or include in sharing\n\t\t\t\t\t- we should have have an autocomplete list which should fetch posts from the previously select post_types\n\t\t\t\t\tand terms and allow them to be include/excluded.\n\t\t\t\t\t( multi-select list ) -->\n\t\t\t\t\t<div class=\"column col-sm-12 col-md-12 col-lg-12\">\n\t\t\t\t\t\t<div class=\"columns\">\n\t\t\t\t\t\t\t<div class=\"column col-sm-12 col-md-4 col-xl-3 col-ml-2 col-4 text-right\">\n\t\t\t\t\t\t\t\t<b>Posts</b><br/>\n\t\t\t\t\t\t\t\t<i>Posts excluded/included in sharing, filtered based on previous selections.</i>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t<div class=\"column col-sm-12 col-md-8 col-xl-9 col-mr-4 col-7 text-left\">\n\t\t\t\t\t\t\t\t<div class=\"input-group\">\n\t\t\t\t\t\t\t\t\t<multiple-select :searchQuery=\"searchQuery\" @update=\"searchUpdate\" :options=\"postsAvailable\" :dontLock=\"true\" :selected=\"generalSettings.selected_posts\" :changedSelection=\"updatedPosts\" />\n\t\t\t\t\t\t\t\t\t<span class=\"input-group-addon\">\n\t\t\t\t\t\t\t\t\t\t<label class=\"form-checkbox\">\n\t\t\t\t\t\t\t\t\t\t\t<input type=\"checkbox\" v-model=\"generalSettings.exclude_posts\" />\n\t\t\t\t\t\t\t\t\t\t\t<i class=\"form-icon\"></i> Exclude?\n\t\t\t\t\t\t\t\t\t\t</label>\n\t\t\t\t\t\t\t\t\t</span>\n\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t</div>\n\t\t<div class=\"panel-footer\">\n\t\t\t<button class=\"btn btn-primary\" @click=\"saveGeneralSettings()\"><i class=\"fa fa-check\"></i> Save</button>\n\t\t</div>\n\t</div>\n";

/***/ }),
/* 105 */
/***/ (function(module, exports, __webpack_require__) {

var __vue_script__, __vue_template__
__webpack_require__(106)
__vue_script__ = __webpack_require__(108)
__vue_template__ = __webpack_require__(109)
module.exports = __vue_script__ || {}
if (module.exports.__esModule) module.exports = module.exports.default
if (__vue_template__) { (typeof module.exports === "function" ? module.exports.options : module.exports).template = __vue_template__ }
if (false) {(function () {  module.hot.accept()
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), true)
  if (!hotAPI.compatible) return
  var id = "/var/www/html/wp-base/wp-content/plugins/tweet-old-post/vue/src/vue-elements/post-format-tab-panel.vue"
  if (!module.hot.data) {
    hotAPI.createRecord(id, module.exports)
  } else {
    hotAPI.update(id, module.exports, __vue_template__)
  }
})()}

/***/ }),
/* 106 */
/***/ (function(module, exports, __webpack_require__) {

// style-loader: Adds some css to the DOM by adding a <style> tag

// load the styles
var content = __webpack_require__(107);
if(typeof content === 'string') content = [[module.i, content, '']];
// add the styles to the DOM
var update = __webpack_require__(1)(content, {});
if(content.locals) module.exports = content.locals;
// Hot Module Replacement
if(false) {
	// When the styles change, update the <style> tags
	if(!content.locals) {
		module.hot.accept("!!../../../node_modules/css-loader/index.js!../../../node_modules/vue-loader/lib/style-rewriter.js?id=_v-051e6fb2&file=post-format-tab-panel.vue&scoped=true!../../../node_modules/vue-loader/lib/selector.js?type=style&index=0!../../../node_modules/eslint-loader/index.js!../../../node_modules/eslint-loader/index.js!./post-format-tab-panel.vue", function() {
			var newContent = require("!!../../../node_modules/css-loader/index.js!../../../node_modules/vue-loader/lib/style-rewriter.js?id=_v-051e6fb2&file=post-format-tab-panel.vue&scoped=true!../../../node_modules/vue-loader/lib/selector.js?type=style&index=0!../../../node_modules/eslint-loader/index.js!../../../node_modules/eslint-loader/index.js!./post-format-tab-panel.vue");
			if(typeof newContent === 'string') newContent = [[module.id, newContent, '']];
			update(newContent);
		});
	}
	// When the module is disposed, remove the <style> tags
	module.hot.dispose(function() { update(); });
}

/***/ }),
/* 107 */
/***/ (function(module, exports, __webpack_require__) {

exports = module.exports = __webpack_require__(0)();
// imports


// module
exports.push([module.i, "\n\t#rop_core .avatar .avatar-icon[_v-051e6fb2] {\n\t\tbackground: #333;\n\t\tborder-radius: 50%;\n\t\tfont-size: 16px;\n\t\ttext-align: center;\n\t\tline-height: 20px;\n\t}\n\t#rop_core .avatar .avatar-icon.fa-facebook-official[_v-051e6fb2] { background-color: #3b5998; }\n\t#rop_core .avatar .avatar-icon.fa-twitter[_v-051e6fb2] { background-color: #55acee; }\n\t#rop_core .avatar .avatar-icon.fa-linkedin[_v-051e6fb2] { background-color: #007bb5; }\n\t#rop_core .avatar .avatar-icon.fa-tumblr[_v-051e6fb2] { background-color: #32506d; }\n\n\t#rop_core .service.facebook[_v-051e6fb2] {\n\t\tcolor: #3b5998;\n\t}\n\n\t#rop_core .service.twitter[_v-051e6fb2] {\n\t\tcolor: #55acee;\n\t}\n\n\t#rop_core .service.linkedin[_v-051e6fb2] {\n\t\tcolor: #007bb5;\n\t}\n\n\t#rop_core .service.tumblr[_v-051e6fb2] {\n\t\tcolor: #32506d;\n\t}\n", ""]);

// exports


/***/ }),
/* 108 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _keys = __webpack_require__(6);

var _keys2 = _interopRequireDefault(_keys);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

// <template>
// 	<div class="tab-view">
// 		<div class="panel-body" style="overflow: inherit;">
// 			<h3>Post Format</h3>
// 			<figure class="avatar avatar-lg" style="text-align: center;">
// 				<img :src="img" v-if="img">
// 				<i class="fa" :class="icon" style="line-height: 48px;" aria-hidden="true" v-else></i>
// 				<i class="avatar-icon fa" :class="icon" aria-hidden="true" v-if="img"></i>
// 				<!--<img src="img/avatar-5.png" class="avatar-icon" alt="...">-->
// 			</figure>
// 			<div class="d-inline-block" style="vertical-align: top; margin-left: 16px;">
// 				<h6>{{user_name}}</h6>
// 				<b class="service" :class="service">{{service_name}}</b>
// 			</div>
// 			<div class="d-inline-block" style="vertical-align: top; margin-left: 16px; width: 80%">
// 				<h4><i class="fa fa-info-circle"></i> Info</h4>
// 				<p><i>Each <b>account</b> can have it's own <b>Post Format</b> for sharing, on the left you can see the
// 					current selected account and network, bellow are the <b>Post Format</b> options for the account.
// 					Don't forget to save after each change and remember, you can always reset an account to the network defaults.
// 				</i></p>
// 			</div>
// 			<div class="container">
// 				<div class="columns">
// 					<div class="column col-sm-12 col-md-12 col-lg-12">
// 						<div class="columns">
// 							<div class="column col-sm-12 col-md-4 col-xl-3 col-ml-2 col-4 text-right">
// 								<b>Account</b><br/>
// 								<i>Specify an account to change the settings of.</i>
// 							</div>
// 							<div class="column col-sm-12 col-md-8 col-xl-9 col-mr-4 col-7 text-left">
// 								<div class="form-group">
// 									<select class="form-select" v-model="selected_account" @change="getAccountpostFormat()">
// 										<option v-for="( account, id ) in active_accounts" :value="id" >{{account.user}} - {{account.service}} </option>
// 									</select>
// 								</div>
// 							</div>
// 						</div>
// 						<hr/>
//
// 						<h4>Content</h4>
// 						<!-- Post Content - where to fetch the content which will be shared
// 							 (dropdown with 4 options ( post_title, post_content, post_content
// 							 and title and custom field). If custom field is selected we will
// 							 have a text field which users will need to fill in to fetch the
// 							 content from that meta key. -->
// 						<div class="columns">
// 							<div class="column col-sm-12 col-md-4 col-xl-3 col-ml-2 col-4 text-right">
// 								<b>Post Content</b><br/>
// 								<i>From where to fetch the content which will be shared.</i>
// 							</div>
// 							<div class="column col-sm-12 col-md-8 col-xl-9 col-mr-4 col-7 text-left">
// 								<div class="form-group">
// 									<select class="form-select" v-model="post_format.post_content">
// 										<option value="post_title">Post Title</option>
// 										<option value="post_content">Post Content</option>
// 										<option value="post_title_content">Post Title & Content</option>
// 										<option value="custom_field">Custom Field</option>
// 									</select>
// 								</div>
// 							</div>
// 						</div>
// 						<div class="columns" v-if="post_format.post_content === 'custom_field'">
// 							<div class="column col-sm-12 col-md-4 col-xl-3 col-ml-2 col-4 text-right">
// 								<b>Custom Meta Field</b><br/>
// 								<i>Meta field name from which to get the content.</i>
// 							</div>
// 							<div class="column col-sm-12 col-md-8 col-xl-9 col-mr-4 col-7 text-left">
// 								<div class="form-group">
// 									<input class="form-input" type="number" v-model="post_format.custom_meta_field" value="" placeholder="" />
// 								</div>
// 							</div>
// 						</div>
//
// 						<!-- Maximum length of the message( number field ) which holds the maximum
// 							 number of chars for the shared content. We striping the content, we need
// 							 to strip at the last whitespace or dot before reaching the limit, in order
// 							 to not trim just half of the word. -->
// 						<div class="columns">
// 							<div class="column col-sm-12 col-md-4 col-xl-3 col-ml-2 col-4 text-right">
// 								<b>Maximum chars</b><br/>
// 								<i>Maximum length of the message.</i>
// 							</div>
// 							<div class="column col-sm-12 col-md-8 col-xl-9 col-mr-4 col-7 text-left">
// 								<div class="form-group">
// 									<input class="form-input" type="number" v-model="post_format.maximum_length" value="" placeholder="" />
// 								</div>
// 							</div>
// 						</div>
//
// 						<!-- Additional text field - text field which will be used by the users to a
// 							 custom content before the fetched post content. -->
// 						<div class="columns">
// 							<div class="column col-sm-12 col-md-4 col-xl-3 col-ml-2 col-4 text-right">
// 								<b>Additional text</b><br/>
// 								<i>Add custom content to published items.</i>
// 							</div>
// 							<div class="column col-sm-12 col-md-8 col-xl-9 col-mr-4 col-7 text-left">
// 								<div class="form-group">
// 									<textarea class="form-input" v-model="post_format.custom_text" placeholder="Custom content ...">{{post_format.custom_text}}</textarea>
// 								</div>
// 							</div>
// 						</div>
//
// 						<!-- Additional text at - dropdown with 2 options, begining or end, having the
// 							 option where to add the additional text content. -->
// 						<div class="columns">
// 							<div class="column col-sm-12 col-md-4 col-xl-3 col-ml-2 col-4 text-right">
// 								<i>Where to add the custom text</i>
// 							</div>
// 							<div class="column col-sm-12 col-md-8 col-xl-9 col-mr-4 col-7 text-left">
// 								<div class="form-group">
// 									<select class="form-select" v-model="post_format.custom_text_pos">
// 										<option value="beginning">Beginning</option>
// 										<option value="end">End</option>
// 									</select>
// 								</div>
// 							</div>
// 						</div>
// 						<hr/>
//
// 						<h4>Link & URL</h4>
// 						<!-- Include link - checkbox either we should include the post permalink or not
// 							 in the shared content. This is will appended at the end of the content. -->
// 						<div class="columns">
// 							<div class="column col-sm-12 col-md-12 col-lg-12">
// 								<div class="columns">
// 									<div class="column col-sm-12 col-md-4 col-xl-3 col-ml-2 col-4 text-right">
// 										<b>Include link</b><br/>
// 										<i>Should include the post permalink or not?</i>
// 									</div>
// 									<div class="column col-sm-12 col-md-8 col-xl-9 col-mr-4 col-7 text-left">
// 										<div class="input-group">
// 											<label class="form-checkbox">
// 												<input type="checkbox" v-model="post_format.include_link" />
// 												<i class="form-icon"></i> Yes
// 											</label>
// 										</div>
// 									</div>
// 								</div>
// 							</div>
// 						</div>
//
// 						<!-- Fetch url from custom field - checkbox - either we should fetch the url from
// 							 a meta field or not. When checked we will open a text field for entering the
// 							 meta key. -->
// 						<div class="columns">
// 							<div class="column col-sm-12 col-md-12 col-lg-12">
// 								<div class="columns">
// 									<div class="column col-sm-12 col-md-4 col-xl-3 col-ml-2 col-4 text-right">
// 										<b>URL</b><br/>
// 										<i>Fetch URL from custom field?</i>
// 									</div>
// 									<div class="column col-sm-12 col-md-8 col-xl-9 col-mr-4 col-7 text-left">
// 										<div class="input-group">
// 											<label class="form-checkbox">
// 												<input type="checkbox" v-model="post_format.url_from_meta" />
// 												<i class="form-icon"></i> Yes
// 											</label>
// 										</div>
// 									</div>
// 								</div>
// 							</div>
// 						</div>
// 						<div class="columns" v-if="post_format.url_from_meta">
// 							<div class="column col-sm-12 col-md-4 col-xl-3 col-ml-2 col-4 text-right">
// 								<b>Meta Key</b><br/>
// 								<i>Meta key name from which to get the URL.</i>
// 							</div>
// 							<div class="column col-sm-12 col-md-8 col-xl-9 col-mr-4 col-7 text-left">
// 								<div class="form-group">
// 									<input class="form-input" type="number" v-model="post_format.url_meta_key" value="" placeholder="" />
// 								</div>
// 							</div>
// 						</div>
//
// 						<!-- Use url shortner ( checkbox ) , either we should use a shortner when adding
// 							 the links to the content. When checked we will show a dropdown with the shortners
// 							 available and the api keys ( if needed ) for each one. The list of shortners will
// 							 be the same as the old version of the plugin. -->
// 						<div class="columns">
// 							<div class="column col-sm-12 col-md-12 col-lg-12">
// 								<div class="columns">
// 									<div class="column col-sm-12 col-md-4 col-xl-3 col-ml-2 col-4 text-right">
// 										<b>Use url shortner</b><br/>
// 										<i>Should we  use a shortner when adding the links to the content?</i>
// 									</div>
// 									<div class="column col-sm-12 col-md-8 col-xl-9 col-mr-4 col-7 text-left">
// 										<div class="input-group">
// 											<label class="form-checkbox">
// 												<input type="checkbox" v-model="post_format.short_url" />
// 												<i class="form-icon"></i> Yes
// 											</label>
// 										</div>
// 									</div>
// 								</div>
// 							</div>
// 						</div>
// 						<div class="columns" v-if="post_format.short_url">
// 							<div class="column col-sm-12 col-md-4 col-xl-3 col-ml-2 col-4 text-right">
// 								<b>URL Shorner Service</b><br/>
// 								<i>Which service to use for URL shortening.</i>
// 							</div>
// 							<div class="column col-sm-12 col-md-8 col-xl-9 col-mr-4 col-7 text-left">
// 								<div class="form-group">
// 									<select class="form-select" v-model="post_format.short_url_service">
// 										<option value="rviv.ly">rviv.ly</option>
// 										<option value="bit.ly">bit.ly</option>
// 										<option value="shorte.st">shorte.st</option>
// 										<option value="goo.gl">goo.gl</option>
// 										<option value="ow.ly">ow.ly</option>
// 										<option value="is.gd">is.gd</option>
// 									</select>
// 								</div>
// 							</div>
// 						</div>
// 						<div class="columns" v-for="( credential, key_name ) in shortner_credentials">
// 							<div class="column col-sm-12 col-md-4 col-xl-3 col-ml-2 col-4 text-right">
// 								<b>{{ key_name | capitalize }}</b><br/>
// 								<i>Add the "{{key_name}}" required by the <b>{{post_format.short_url_service}}</b> service API.</i>
// 							</div>
// 							<div class="column col-sm-12 col-md-8 col-xl-9 col-mr-4 col-7 text-left">
// 								<div class="form-group">
// 									<input class="form-input" type="text" v-model="shortner_credentials[key_name]" value="" placeholder="" @change="updateShortnerCredentials()" @keyup="updateShortnerCredentials()" />
// 								</div>
// 							</div>
// 						</div>
// 						<hr/>
//
// 						<h4>Misc.</h4>
// 						<!-- Hashtags - dropdown - having this options - (Dont add any hashtags, Common hastags
// 							 for all shares, Create hashtags from categories, Create hashtags from tags, Create
// 							 hashtags from custom field). If one of those options is selected, except the dont
// 							 any hashtags options, we will show a number field having the Maximum hashtags length.
// 							 Moreover for common hashtags option, we will have another text field which will contain
// 							 the hashtags value. -->
// 						<div class="columns">
// 							<div class="column col-sm-12 col-md-4 col-xl-3 col-ml-2 col-4 text-right">
// 								<b>Hashtags</b><br/>
// 								<i>Hashtags to published content.</i>
// 							</div>
// 							<div class="column col-sm-12 col-md-8 col-xl-9 col-mr-4 col-7 text-left">
// 								<div class="form-group">
// 									<select class="form-select" v-model="post_format.hashtags">
// 										<option value="no-hashtags" >Dont add any hashtags</option>
// 										<option value="common-hashtags">Common hastags for all shares</option>
// 										<option value="categories-hashtags">Create hashtags from categories</option>
// 										<option value="tags-hashtags">Create hashtags from tags</option>
// 										<option value="custom-hashtags">Create hashtags from custom field</option>
// 									</select>
// 								</div>
// 							</div>
// 						</div>
// 						<div class="columns" v-if="post_format.hashtags !== 'no-hashtags'">
// 							<div class="column col-sm-12 col-md-4 col-xl-3 col-ml-2 col-4 text-right">
// 								<b>Maximum Hashtags length</b><br/>
// 								<i>The maximum hashtags length to be used when publishing.</i>
// 							</div>
// 							<div class="column col-sm-12 col-md-8 col-xl-9 col-mr-4 col-7 text-left">
// 								<div class="form-group">
// 									<input class="form-input" type="number" v-model="post_format.hashtags_length" value="" placeholder="" />
// 								</div>
// 							</div>
// 						</div>
// 						<div class="columns" v-if="post_format.hashtags === 'common-hashtags'">
// 							<div class="column col-sm-12 col-md-4 col-xl-3 col-ml-2 col-4 text-right">
// 								<b>Common Hashtags</b><br/>
// 								<i>List of hastags to use separated by comma ",".</i>
// 							</div>
// 							<div class="column col-sm-12 col-md-8 col-xl-9 col-mr-4 col-7 text-left">
// 								<div class="form-group">
// 									<input class="form-input" type="text" v-model="post_format.hashtags_common" value="" placeholder="" />
// 								</div>
// 							</div>
// 						</div>
// 						<div class="columns" v-if="post_format.hashtags === 'custom-hashtags'">
// 							<div class="column col-sm-12 col-md-4 col-xl-3 col-ml-2 col-4 text-right">
// 								<b>Custom Hashtags</b><br/>
// 								<i>The name of the meta field that contains the hashtags.</i>
// 							</div>
// 							<div class="column col-sm-12 col-md-8 col-xl-9 col-mr-4 col-7 text-left">
// 								<div class="form-group">
// 									<input class="form-input" type="text" v-model="post_format.hashtags_custom" value="" placeholder="" />
// 								</div>
// 							</div>
// 						</div>
//
// 						<!-- Post with image - checkbox (either we should use the featured image when posting) -->
// 						<div class="columns">
// 							<div class="column col-sm-12 col-md-12 col-lg-12">
// 								<div class="columns">
// 									<div class="column col-sm-12 col-md-4 col-xl-3 col-ml-2 col-4 text-right">
// 										<b>Post with image</b><br/>
// 										<i>Use the featured image when posting?</i>
// 									</div>
// 									<div class="column col-sm-12 col-md-8 col-xl-9 col-mr-4 col-7 text-left">
// 										<div class="input-group">
// 											<label class="form-checkbox">
// 												<input type="checkbox" v-model="post_format.image" />
// 												<i class="form-icon"></i> Yes
// 											</label>
// 										</div>
// 									</div>
// 								</div>
// 							</div>
// 						</div>
// 						<hr/>
// 						<div class="columns">
// 							<div class="column col-sm-12 col-md-12 col-lg-12">
// 								<div class="columns">
// 									<div class="column col-sm-12 col-md-4 col-xl-3 col-ml-2 col-4 text-right">
// 										<b>Stats:</b><br/>
// 										<i>Available char for post content</i>
// 									</div>
// 									<div class="column col-sm-12 col-md-8 col-xl-9 col-mr-4 col-7 text-left">
// 										{{computed_chars}}
// 									</div>
// 								</div>
// 							</div>
// 						</div>
// 						<hr/>
// 					</div>
// 				</div>
// 			</div>
// 		</div>
// 		<div class="panel-footer">
// 			<button class="btn btn-primary" @click="savePostFormat()"><i class="fa fa-check"></i> Save Post Format</button>
// 			<button class="btn btn-secondary" @click="resetPostFormat()"><i class="fa fa-ban"></i> Reset to Defaults</button>
// 		</div>
// 	</div>
// </template>
//
// <script>
module.exports = {
	name: 'post-format-view',
	data: function data() {
		var key = null;
		if ((0, _keys2.default)(this.$store.state.activeAccounts)[0] !== undefined) key = (0, _keys2.default)(this.$store.state.activeAccounts)[0];
		return {
			selected_account: key,
			shortner_credentials: []
		};
	},
	mounted: function mounted() {
		// Uncomment this when not fixed tab on post format
		this.getAccountpostFormat();
	},
	filters: {
		capitalize: function capitalize(value) {
			if (!value) return '';
			value = value.toString();
			return value.charAt(0).toUpperCase() + value.slice(1);
		}
	},
	computed: {
		computed_chars: function computed_chars() {
			var allowedChars = this.post_format.maximum_length;
			var customText = 0;
			var hashtagsLength = 0;
			if (this.post_format.custom_text !== undefined) customText = this.post_format.custom_text.length;
			if (this.post_format.hashtags !== 'no-hashtags') hashtagsLength = this.post_format.hashtags_length;
			if (customText !== 0) customText = customText + 1;
			var serviceReserved = 0;
			if (this.selected_account !== null && this.active_accounts[this.selected_account].service === 'twitter') {
				if (this.post_format.image) serviceReserved = serviceReserved + 25;
				if (this.post_format.include_link) serviceReserved = serviceReserved + 25;
			}
			return allowedChars - customText - hashtagsLength - serviceReserved;
		},
		active_accounts: function active_accounts() {
			return this.$store.state.activeAccounts;
		},
		post_format: function post_format() {
			return this.$store.state.activePostFormat;
		},
		short_url_service: function short_url_service() {
			var postFormat = this.$store.getters.getPostFormat;
			return postFormat.short_url_service;
		},
		icon: function icon() {
			var serviceIcon = 'fa-user';
			if (this.selected_account !== null) {
				serviceIcon = 'fa-';
				var account = this.active_accounts[this.selected_account];
				if (account.service === 'facebook') serviceIcon = serviceIcon.concat('facebook-official');
				if (account.service === 'twitter') serviceIcon = serviceIcon.concat('twitter');
				if (account.service === 'linkedin') serviceIcon = serviceIcon.concat('linkedin');
				if (account.service === 'tumblr') serviceIcon = serviceIcon.concat('tumblr');
			}
			return serviceIcon;
		},
		img: function img() {
			var img = '';
			if (this.selected_account !== null && this.active_accounts[this.selected_account].img !== '' && this.active_accounts[this.selected_account].img !== undefined) {
				img = this.active_accounts[this.selected_account].img;
			}
			return img;
		},
		service: function service() {
			var serviceClass = '';
			if (this.selected_account !== null && this.active_accounts[this.selected_account].service) {
				serviceClass = this.active_accounts[this.selected_account].service;
			}
			return serviceClass;
		},
		service_name: function service_name() {
			if (this.service !== '') return this.service.charAt(0).toUpperCase() + this.service.slice(1);
			return 'Service';
		},
		user_name: function user_name() {
			if (this.selected_account !== null && this.active_accounts[this.selected_account].user) return this.active_accounts[this.selected_account].user;
			return 'John Doe';
		}
	},
	watch: {
		active_accounts: function active_accounts() {
			console.log('Accounts changed');
			if ((0, _keys2.default)(this.$store.state.activeAccounts)[0] && this.selected_account === null) {
				var key = (0, _keys2.default)(this.$store.state.activeAccounts)[0];
				this.selected_account = key;
				this.getAccountpostFormat();
			}
		},
		short_url_service: function short_url_service() {
			var _this = this;

			console.log('Service changed');
			console.log(this.short_url_service);
			this.$store.dispatch('fetchShortnerCredentials', { short_url_service: this.short_url_service }).then(function (response) {
				console.log('Got some data, now lets show something in this component', response);
				_this.shortner_credentials = response;
			}, function (error) {
				console.error('Got nothing from server. Prompt user to check internet connection and try again', error);
			});
		}
	},
	methods: {
		getAccountpostFormat: function getAccountpostFormat() {
			console.log('Get Post format for', this.selected_account);
			this.$store.dispatch('fetchPostFormat', { service: this.active_accounts[this.selected_account].service, account_id: this.selected_account });
		},
		savePostFormat: function savePostFormat() {
			console.log('Save Post format for', this.selected_account);
			this.$store.dispatch('savePostFormat', { service: this.active_accounts[this.selected_account].service, account_id: this.selected_account, post_format: this.post_format });
		},
		resetPostFormat: function resetPostFormat() {
			console.log('Reset Post format for', this.selected_account);
			this.$store.dispatch('resetPostFormat', { service: this.active_accounts[this.selected_account].service, account_id: this.selected_account });
			this.$forceUpdate();
		},
		updateShortnerCredentials: function updateShortnerCredentials() {
			this.$store.commit('updatePostFormatShortnerCredentials', this.shortner_credentials);
		}
	}
	// </script>
	//
	// <style scoped>
	// 	#rop_core .avatar .avatar-icon {
	// 		background: #333;
	// 		border-radius: 50%;
	// 		font-size: 16px;
	// 		text-align: center;
	// 		line-height: 20px;
	// 	}
	// 	#rop_core .avatar .avatar-icon.fa-facebook-official { background-color: #3b5998; }
	// 	#rop_core .avatar .avatar-icon.fa-twitter { background-color: #55acee; }
	// 	#rop_core .avatar .avatar-icon.fa-linkedin { background-color: #007bb5; }
	// 	#rop_core .avatar .avatar-icon.fa-tumblr { background-color: #32506d; }
	//
	// 	#rop_core .service.facebook {
	// 		color: #3b5998;
	// 	}
	//
	// 	#rop_core .service.twitter {
	// 		color: #55acee;
	// 	}
	//
	// 	#rop_core .service.linkedin {
	// 		color: #007bb5;
	// 	}
	//
	// 	#rop_core .service.tumblr {
	// 		color: #32506d;
	// 	}
	// </style>

};

/***/ }),
/* 109 */
/***/ (function(module, exports) {

module.exports = "\n\t<div class=\"tab-view\" _v-051e6fb2=\"\">\n\t\t<div class=\"panel-body\" style=\"overflow: inherit;\" _v-051e6fb2=\"\">\n\t\t\t<h3 _v-051e6fb2=\"\">Post Format</h3>\n\t\t\t<figure class=\"avatar avatar-lg\" style=\"text-align: center;\" _v-051e6fb2=\"\">\n\t\t\t\t<img :src=\"img\" v-if=\"img\" _v-051e6fb2=\"\">\n\t\t\t\t<i class=\"fa\" :class=\"icon\" style=\"line-height: 48px;\" aria-hidden=\"true\" v-else=\"\" _v-051e6fb2=\"\"></i>\n\t\t\t\t<i class=\"avatar-icon fa\" :class=\"icon\" aria-hidden=\"true\" v-if=\"img\" _v-051e6fb2=\"\"></i>\n\t\t\t\t<!--<img src=\"img/avatar-5.png\" class=\"avatar-icon\" alt=\"...\">-->\n\t\t\t</figure>\n\t\t\t<div class=\"d-inline-block\" style=\"vertical-align: top; margin-left: 16px;\" _v-051e6fb2=\"\">\n\t\t\t\t<h6 _v-051e6fb2=\"\">{{user_name}}</h6>\n\t\t\t\t<b class=\"service\" :class=\"service\" _v-051e6fb2=\"\">{{service_name}}</b>\n\t\t\t</div>\n\t\t\t<div class=\"d-inline-block\" style=\"vertical-align: top; margin-left: 16px; width: 80%\" _v-051e6fb2=\"\">\n\t\t\t\t<h4 _v-051e6fb2=\"\"><i class=\"fa fa-info-circle\" _v-051e6fb2=\"\"></i> Info</h4>\n\t\t\t\t<p _v-051e6fb2=\"\"><i _v-051e6fb2=\"\">Each <b _v-051e6fb2=\"\">account</b> can have it's own <b _v-051e6fb2=\"\">Post Format</b> for sharing, on the left you can see the\n\t\t\t\t\tcurrent selected account and network, bellow are the <b _v-051e6fb2=\"\">Post Format</b> options for the account.\n\t\t\t\t\tDon't forget to save after each change and remember, you can always reset an account to the network defaults.\n\t\t\t\t</i></p>\n\t\t\t</div>\n\t\t\t<div class=\"container\" _v-051e6fb2=\"\">\n\t\t\t\t<div class=\"columns\" _v-051e6fb2=\"\">\n\t\t\t\t\t<div class=\"column col-sm-12 col-md-12 col-lg-12\" _v-051e6fb2=\"\">\n\t\t\t\t\t\t<div class=\"columns\" _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t<div class=\"column col-sm-12 col-md-4 col-xl-3 col-ml-2 col-4 text-right\" _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t\t<b _v-051e6fb2=\"\">Account</b><br _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t\t<i _v-051e6fb2=\"\">Specify an account to change the settings of.</i>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t<div class=\"column col-sm-12 col-md-8 col-xl-9 col-mr-4 col-7 text-left\" _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t\t<div class=\"form-group\" _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t\t\t<select class=\"form-select\" v-model=\"selected_account\" @change=\"getAccountpostFormat()\" _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t\t\t\t<option v-for=\"( account, id ) in active_accounts\" :value=\"id\" _v-051e6fb2=\"\">{{account.user}} - {{account.service}} </option>\n\t\t\t\t\t\t\t\t\t</select>\n\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t\t<hr _v-051e6fb2=\"\">\n\n\t\t\t\t\t\t<h4 _v-051e6fb2=\"\">Content</h4>\n\t\t\t\t\t\t<!-- Post Content - where to fetch the content which will be shared\n\t\t\t\t\t\t\t (dropdown with 4 options ( post_title, post_content, post_content\n\t\t\t\t\t\t\t and title and custom field). If custom field is selected we will\n\t\t\t\t\t\t\t have a text field which users will need to fill in to fetch the\n\t\t\t\t\t\t\t content from that meta key. -->\n\t\t\t\t\t\t<div class=\"columns\" _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t<div class=\"column col-sm-12 col-md-4 col-xl-3 col-ml-2 col-4 text-right\" _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t\t<b _v-051e6fb2=\"\">Post Content</b><br _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t\t<i _v-051e6fb2=\"\">From where to fetch the content which will be shared.</i>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t<div class=\"column col-sm-12 col-md-8 col-xl-9 col-mr-4 col-7 text-left\" _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t\t<div class=\"form-group\" _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t\t\t<select class=\"form-select\" v-model=\"post_format.post_content\" _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t\t\t\t<option value=\"post_title\" _v-051e6fb2=\"\">Post Title</option>\n\t\t\t\t\t\t\t\t\t\t<option value=\"post_content\" _v-051e6fb2=\"\">Post Content</option>\n\t\t\t\t\t\t\t\t\t\t<option value=\"post_title_content\" _v-051e6fb2=\"\">Post Title &amp; Content</option>\n\t\t\t\t\t\t\t\t\t\t<option value=\"custom_field\" _v-051e6fb2=\"\">Custom Field</option>\n\t\t\t\t\t\t\t\t\t</select>\n\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t\t<div class=\"columns\" v-if=\"post_format.post_content === 'custom_field'\" _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t<div class=\"column col-sm-12 col-md-4 col-xl-3 col-ml-2 col-4 text-right\" _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t\t<b _v-051e6fb2=\"\">Custom Meta Field</b><br _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t\t<i _v-051e6fb2=\"\">Meta field name from which to get the content.</i>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t<div class=\"column col-sm-12 col-md-8 col-xl-9 col-mr-4 col-7 text-left\" _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t\t<div class=\"form-group\" _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t\t\t<input class=\"form-input\" type=\"number\" v-model=\"post_format.custom_meta_field\" value=\"\" placeholder=\"\" _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t</div>\n\n\t\t\t\t\t\t<!-- Maximum length of the message( number field ) which holds the maximum\n\t\t\t\t\t\t\t number of chars for the shared content. We striping the content, we need\n\t\t\t\t\t\t\t to strip at the last whitespace or dot before reaching the limit, in order\n\t\t\t\t\t\t\t to not trim just half of the word. -->\n\t\t\t\t\t\t<div class=\"columns\" _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t<div class=\"column col-sm-12 col-md-4 col-xl-3 col-ml-2 col-4 text-right\" _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t\t<b _v-051e6fb2=\"\">Maximum chars</b><br _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t\t<i _v-051e6fb2=\"\">Maximum length of the message.</i>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t<div class=\"column col-sm-12 col-md-8 col-xl-9 col-mr-4 col-7 text-left\" _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t\t<div class=\"form-group\" _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t\t\t<input class=\"form-input\" type=\"number\" v-model=\"post_format.maximum_length\" value=\"\" placeholder=\"\" _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t</div>\n\n\t\t\t\t\t\t<!-- Additional text field - text field which will be used by the users to a\n\t\t\t\t\t\t\t custom content before the fetched post content. -->\n\t\t\t\t\t\t<div class=\"columns\" _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t<div class=\"column col-sm-12 col-md-4 col-xl-3 col-ml-2 col-4 text-right\" _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t\t<b _v-051e6fb2=\"\">Additional text</b><br _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t\t<i _v-051e6fb2=\"\">Add custom content to published items.</i>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t<div class=\"column col-sm-12 col-md-8 col-xl-9 col-mr-4 col-7 text-left\" _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t\t<div class=\"form-group\" _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t\t\t<textarea class=\"form-input\" v-model=\"post_format.custom_text\" placeholder=\"Custom content ...\" _v-051e6fb2=\"\">{{post_format.custom_text}}</textarea>\n\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t</div>\n\n\t\t\t\t\t\t<!-- Additional text at - dropdown with 2 options, begining or end, having the\n\t\t\t\t\t\t\t option where to add the additional text content. -->\n\t\t\t\t\t\t<div class=\"columns\" _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t<div class=\"column col-sm-12 col-md-4 col-xl-3 col-ml-2 col-4 text-right\" _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t\t<i _v-051e6fb2=\"\">Where to add the custom text</i>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t<div class=\"column col-sm-12 col-md-8 col-xl-9 col-mr-4 col-7 text-left\" _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t\t<div class=\"form-group\" _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t\t\t<select class=\"form-select\" v-model=\"post_format.custom_text_pos\" _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t\t\t\t<option value=\"beginning\" _v-051e6fb2=\"\">Beginning</option>\n\t\t\t\t\t\t\t\t\t\t<option value=\"end\" _v-051e6fb2=\"\">End</option>\n\t\t\t\t\t\t\t\t\t</select>\n\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t\t<hr _v-051e6fb2=\"\">\n\n\t\t\t\t\t\t<h4 _v-051e6fb2=\"\">Link &amp; URL</h4>\n\t\t\t\t\t\t<!-- Include link - checkbox either we should include the post permalink or not\n\t\t\t\t\t\t\t in the shared content. This is will appended at the end of the content. -->\n\t\t\t\t\t\t<div class=\"columns\" _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t<div class=\"column col-sm-12 col-md-12 col-lg-12\" _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t\t<div class=\"columns\" _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t\t\t<div class=\"column col-sm-12 col-md-4 col-xl-3 col-ml-2 col-4 text-right\" _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t\t\t\t<b _v-051e6fb2=\"\">Include link</b><br _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t\t\t\t<i _v-051e6fb2=\"\">Should include the post permalink or not?</i>\n\t\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t\t<div class=\"column col-sm-12 col-md-8 col-xl-9 col-mr-4 col-7 text-left\" _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t\t\t\t<div class=\"input-group\" _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t\t\t\t\t<label class=\"form-checkbox\" _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t\t\t\t\t\t<input type=\"checkbox\" v-model=\"post_format.include_link\" _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t\t\t\t\t\t<i class=\"form-icon\" _v-051e6fb2=\"\"></i> Yes\n\t\t\t\t\t\t\t\t\t\t\t</label>\n\t\t\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t</div>\n\n\t\t\t\t\t\t<!-- Fetch url from custom field - checkbox - either we should fetch the url from\n\t\t\t\t\t\t\t a meta field or not. When checked we will open a text field for entering the\n\t\t\t\t\t\t\t meta key. -->\n\t\t\t\t\t\t<div class=\"columns\" _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t<div class=\"column col-sm-12 col-md-12 col-lg-12\" _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t\t<div class=\"columns\" _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t\t\t<div class=\"column col-sm-12 col-md-4 col-xl-3 col-ml-2 col-4 text-right\" _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t\t\t\t<b _v-051e6fb2=\"\">URL</b><br _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t\t\t\t<i _v-051e6fb2=\"\">Fetch URL from custom field?</i>\n\t\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t\t<div class=\"column col-sm-12 col-md-8 col-xl-9 col-mr-4 col-7 text-left\" _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t\t\t\t<div class=\"input-group\" _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t\t\t\t\t<label class=\"form-checkbox\" _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t\t\t\t\t\t<input type=\"checkbox\" v-model=\"post_format.url_from_meta\" _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t\t\t\t\t\t<i class=\"form-icon\" _v-051e6fb2=\"\"></i> Yes\n\t\t\t\t\t\t\t\t\t\t\t</label>\n\t\t\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t\t<div class=\"columns\" v-if=\"post_format.url_from_meta\" _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t<div class=\"column col-sm-12 col-md-4 col-xl-3 col-ml-2 col-4 text-right\" _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t\t<b _v-051e6fb2=\"\">Meta Key</b><br _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t\t<i _v-051e6fb2=\"\">Meta key name from which to get the URL.</i>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t<div class=\"column col-sm-12 col-md-8 col-xl-9 col-mr-4 col-7 text-left\" _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t\t<div class=\"form-group\" _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t\t\t<input class=\"form-input\" type=\"number\" v-model=\"post_format.url_meta_key\" value=\"\" placeholder=\"\" _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t</div>\n\n\t\t\t\t\t\t<!-- Use url shortner ( checkbox ) , either we should use a shortner when adding\n\t\t\t\t\t\t\t the links to the content. When checked we will show a dropdown with the shortners\n\t\t\t\t\t\t\t available and the api keys ( if needed ) for each one. The list of shortners will\n\t\t\t\t\t\t\t be the same as the old version of the plugin. -->\n\t\t\t\t\t\t<div class=\"columns\" _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t<div class=\"column col-sm-12 col-md-12 col-lg-12\" _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t\t<div class=\"columns\" _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t\t\t<div class=\"column col-sm-12 col-md-4 col-xl-3 col-ml-2 col-4 text-right\" _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t\t\t\t<b _v-051e6fb2=\"\">Use url shortner</b><br _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t\t\t\t<i _v-051e6fb2=\"\">Should we  use a shortner when adding the links to the content?</i>\n\t\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t\t<div class=\"column col-sm-12 col-md-8 col-xl-9 col-mr-4 col-7 text-left\" _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t\t\t\t<div class=\"input-group\" _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t\t\t\t\t<label class=\"form-checkbox\" _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t\t\t\t\t\t<input type=\"checkbox\" v-model=\"post_format.short_url\" _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t\t\t\t\t\t<i class=\"form-icon\" _v-051e6fb2=\"\"></i> Yes\n\t\t\t\t\t\t\t\t\t\t\t</label>\n\t\t\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t\t<div class=\"columns\" v-if=\"post_format.short_url\" _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t<div class=\"column col-sm-12 col-md-4 col-xl-3 col-ml-2 col-4 text-right\" _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t\t<b _v-051e6fb2=\"\">URL Shorner Service</b><br _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t\t<i _v-051e6fb2=\"\">Which service to use for URL shortening.</i>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t<div class=\"column col-sm-12 col-md-8 col-xl-9 col-mr-4 col-7 text-left\" _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t\t<div class=\"form-group\" _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t\t\t<select class=\"form-select\" v-model=\"post_format.short_url_service\" _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t\t\t\t<option value=\"rviv.ly\" _v-051e6fb2=\"\">rviv.ly</option>\n\t\t\t\t\t\t\t\t\t\t<option value=\"bit.ly\" _v-051e6fb2=\"\">bit.ly</option>\n\t\t\t\t\t\t\t\t\t\t<option value=\"shorte.st\" _v-051e6fb2=\"\">shorte.st</option>\n\t\t\t\t\t\t\t\t\t\t<option value=\"goo.gl\" _v-051e6fb2=\"\">goo.gl</option>\n\t\t\t\t\t\t\t\t\t\t<option value=\"ow.ly\" _v-051e6fb2=\"\">ow.ly</option>\n\t\t\t\t\t\t\t\t\t\t<option value=\"is.gd\" _v-051e6fb2=\"\">is.gd</option>\n\t\t\t\t\t\t\t\t\t</select>\n\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t\t<div class=\"columns\" v-for=\"( credential, key_name ) in shortner_credentials\" _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t<div class=\"column col-sm-12 col-md-4 col-xl-3 col-ml-2 col-4 text-right\" _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t\t<b _v-051e6fb2=\"\">{{ key_name | capitalize }}</b><br _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t\t<i _v-051e6fb2=\"\">Add the \"{{key_name}}\" required by the <b _v-051e6fb2=\"\">{{post_format.short_url_service}}</b> service API.</i>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t<div class=\"column col-sm-12 col-md-8 col-xl-9 col-mr-4 col-7 text-left\" _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t\t<div class=\"form-group\" _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t\t\t<input class=\"form-input\" type=\"text\" v-model=\"shortner_credentials[key_name]\" value=\"\" placeholder=\"\" @change=\"updateShortnerCredentials()\" @keyup=\"updateShortnerCredentials()\" _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t\t<hr _v-051e6fb2=\"\">\n\n\t\t\t\t\t\t<h4 _v-051e6fb2=\"\">Misc.</h4>\n\t\t\t\t\t\t<!-- Hashtags - dropdown - having this options - (Dont add any hashtags, Common hastags\n\t\t\t\t\t\t\t for all shares, Create hashtags from categories, Create hashtags from tags, Create\n\t\t\t\t\t\t\t hashtags from custom field). If one of those options is selected, except the dont\n\t\t\t\t\t\t\t any hashtags options, we will show a number field having the Maximum hashtags length.\n\t\t\t\t\t\t\t Moreover for common hashtags option, we will have another text field which will contain\n\t\t\t\t\t\t\t the hashtags value. -->\n\t\t\t\t\t\t<div class=\"columns\" _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t<div class=\"column col-sm-12 col-md-4 col-xl-3 col-ml-2 col-4 text-right\" _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t\t<b _v-051e6fb2=\"\">Hashtags</b><br _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t\t<i _v-051e6fb2=\"\">Hashtags to published content.</i>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t<div class=\"column col-sm-12 col-md-8 col-xl-9 col-mr-4 col-7 text-left\" _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t\t<div class=\"form-group\" _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t\t\t<select class=\"form-select\" v-model=\"post_format.hashtags\" _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t\t\t\t<option value=\"no-hashtags\" _v-051e6fb2=\"\">Dont add any hashtags</option>\n\t\t\t\t\t\t\t\t\t\t<option value=\"common-hashtags\" _v-051e6fb2=\"\">Common hastags for all shares</option>\n\t\t\t\t\t\t\t\t\t\t<option value=\"categories-hashtags\" _v-051e6fb2=\"\">Create hashtags from categories</option>\n\t\t\t\t\t\t\t\t\t\t<option value=\"tags-hashtags\" _v-051e6fb2=\"\">Create hashtags from tags</option>\n\t\t\t\t\t\t\t\t\t\t<option value=\"custom-hashtags\" _v-051e6fb2=\"\">Create hashtags from custom field</option>\n\t\t\t\t\t\t\t\t\t</select>\n\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t\t<div class=\"columns\" v-if=\"post_format.hashtags !== 'no-hashtags'\" _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t<div class=\"column col-sm-12 col-md-4 col-xl-3 col-ml-2 col-4 text-right\" _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t\t<b _v-051e6fb2=\"\">Maximum Hashtags length</b><br _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t\t<i _v-051e6fb2=\"\">The maximum hashtags length to be used when publishing.</i>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t<div class=\"column col-sm-12 col-md-8 col-xl-9 col-mr-4 col-7 text-left\" _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t\t<div class=\"form-group\" _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t\t\t<input class=\"form-input\" type=\"number\" v-model=\"post_format.hashtags_length\" value=\"\" placeholder=\"\" _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t\t<div class=\"columns\" v-if=\"post_format.hashtags === 'common-hashtags'\" _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t<div class=\"column col-sm-12 col-md-4 col-xl-3 col-ml-2 col-4 text-right\" _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t\t<b _v-051e6fb2=\"\">Common Hashtags</b><br _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t\t<i _v-051e6fb2=\"\">List of hastags to use separated by comma \",\".</i>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t<div class=\"column col-sm-12 col-md-8 col-xl-9 col-mr-4 col-7 text-left\" _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t\t<div class=\"form-group\" _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t\t\t<input class=\"form-input\" type=\"text\" v-model=\"post_format.hashtags_common\" value=\"\" placeholder=\"\" _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t\t<div class=\"columns\" v-if=\"post_format.hashtags === 'custom-hashtags'\" _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t<div class=\"column col-sm-12 col-md-4 col-xl-3 col-ml-2 col-4 text-right\" _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t\t<b _v-051e6fb2=\"\">Custom Hashtags</b><br _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t\t<i _v-051e6fb2=\"\">The name of the meta field that contains the hashtags.</i>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t<div class=\"column col-sm-12 col-md-8 col-xl-9 col-mr-4 col-7 text-left\" _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t\t<div class=\"form-group\" _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t\t\t<input class=\"form-input\" type=\"text\" v-model=\"post_format.hashtags_custom\" value=\"\" placeholder=\"\" _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t</div>\n\n\t\t\t\t\t\t<!-- Post with image - checkbox (either we should use the featured image when posting) -->\n\t\t\t\t\t\t<div class=\"columns\" _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t<div class=\"column col-sm-12 col-md-12 col-lg-12\" _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t\t<div class=\"columns\" _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t\t\t<div class=\"column col-sm-12 col-md-4 col-xl-3 col-ml-2 col-4 text-right\" _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t\t\t\t<b _v-051e6fb2=\"\">Post with image</b><br _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t\t\t\t<i _v-051e6fb2=\"\">Use the featured image when posting?</i>\n\t\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t\t<div class=\"column col-sm-12 col-md-8 col-xl-9 col-mr-4 col-7 text-left\" _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t\t\t\t<div class=\"input-group\" _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t\t\t\t\t<label class=\"form-checkbox\" _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t\t\t\t\t\t<input type=\"checkbox\" v-model=\"post_format.image\" _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t\t\t\t\t\t<i class=\"form-icon\" _v-051e6fb2=\"\"></i> Yes\n\t\t\t\t\t\t\t\t\t\t\t</label>\n\t\t\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t\t<hr _v-051e6fb2=\"\">\n\t\t\t\t\t\t<div class=\"columns\" _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t<div class=\"column col-sm-12 col-md-12 col-lg-12\" _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t\t<div class=\"columns\" _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t\t\t<div class=\"column col-sm-12 col-md-4 col-xl-3 col-ml-2 col-4 text-right\" _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t\t\t\t<b _v-051e6fb2=\"\">Stats:</b><br _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t\t\t\t<i _v-051e6fb2=\"\">Available char for post content</i>\n\t\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t\t<div class=\"column col-sm-12 col-md-8 col-xl-9 col-mr-4 col-7 text-left\" _v-051e6fb2=\"\">\n\t\t\t\t\t\t\t\t\t\t{{computed_chars}}\n\t\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t\t<hr _v-051e6fb2=\"\">\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t</div>\n\t\t<div class=\"panel-footer\" _v-051e6fb2=\"\">\n\t\t\t<button class=\"btn btn-primary\" @click=\"savePostFormat()\" _v-051e6fb2=\"\"><i class=\"fa fa-check\" _v-051e6fb2=\"\"></i> Save Post Format</button>\n\t\t\t<button class=\"btn btn-secondary\" @click=\"resetPostFormat()\" _v-051e6fb2=\"\"><i class=\"fa fa-ban\" _v-051e6fb2=\"\"></i> Reset to Defaults</button>\n\t\t</div>\n\t</div>\n";

/***/ }),
/* 110 */
/***/ (function(module, exports, __webpack_require__) {

var __vue_script__, __vue_template__
__webpack_require__(111)
__webpack_require__(113)
__vue_script__ = __webpack_require__(115)
__vue_template__ = __webpack_require__(128)
module.exports = __vue_script__ || {}
if (module.exports.__esModule) module.exports = module.exports.default
if (__vue_template__) { (typeof module.exports === "function" ? module.exports.options : module.exports).template = __vue_template__ }
if (false) {(function () {  module.hot.accept()
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), true)
  if (!hotAPI.compatible) return
  var id = "/var/www/html/wp-base/wp-content/plugins/tweet-old-post/vue/src/vue-elements/schedule-tab-panel.vue"
  if (!module.hot.data) {
    hotAPI.createRecord(id, module.exports)
  } else {
    hotAPI.update(id, module.exports, __vue_template__)
  }
})()}

/***/ }),
/* 111 */
/***/ (function(module, exports, __webpack_require__) {

// style-loader: Adds some css to the DOM by adding a <style> tag

// load the styles
var content = __webpack_require__(112);
if(typeof content === 'string') content = [[module.i, content, '']];
// add the styles to the DOM
var update = __webpack_require__(1)(content, {});
if(content.locals) module.exports = content.locals;
// Hot Module Replacement
if(false) {
	// When the styles change, update the <style> tags
	if(!content.locals) {
		module.hot.accept("!!../../../node_modules/css-loader/index.js!../../../node_modules/vue-loader/lib/style-rewriter.js?id=_v-d77321bc&file=schedule-tab-panel.vue&scoped=true!../../../node_modules/vue-loader/lib/selector.js?type=style&index=0!../../../node_modules/eslint-loader/index.js!../../../node_modules/eslint-loader/index.js!./schedule-tab-panel.vue", function() {
			var newContent = require("!!../../../node_modules/css-loader/index.js!../../../node_modules/vue-loader/lib/style-rewriter.js?id=_v-d77321bc&file=schedule-tab-panel.vue&scoped=true!../../../node_modules/vue-loader/lib/selector.js?type=style&index=0!../../../node_modules/eslint-loader/index.js!../../../node_modules/eslint-loader/index.js!./schedule-tab-panel.vue");
			if(typeof newContent === 'string') newContent = [[module.id, newContent, '']];
			update(newContent);
		});
	}
	// When the module is disposed, remove the <style> tags
	module.hot.dispose(function() { update(); });
}

/***/ }),
/* 112 */
/***/ (function(module, exports, __webpack_require__) {

exports = module.exports = __webpack_require__(0)();
// imports


// module
exports.push([module.i, "\n\t#rop_core .avatar .avatar-icon[_v-d77321bc] {\n\t\tbackground: #333;\n\t\tborder-radius: 50%;\n\t\tfont-size: 16px;\n\t\ttext-align: center;\n\t\tline-height: 20px;\n\t}\n\t#rop_core .avatar .avatar-icon.fa-facebook-official[_v-d77321bc] { background-color: #3b5998; }\n\t#rop_core .avatar .avatar-icon.fa-twitter[_v-d77321bc] { background-color: #55acee; }\n\t#rop_core .avatar .avatar-icon.fa-linkedin[_v-d77321bc] { background-color: #007bb5; }\n\t#rop_core .avatar .avatar-icon.fa-tumblr[_v-d77321bc] { background-color: #32506d; }\n\n\t#rop_core .service.facebook[_v-d77321bc] {\n\t\tcolor: #3b5998;\n\t}\n\n\t#rop_core .service.twitter[_v-d77321bc] {\n\t\tcolor: #55acee;\n\t}\n\n\t#rop_core .service.linkedin[_v-d77321bc] {\n\t\tcolor: #007bb5;\n\t}\n\n\t#rop_core .service.tumblr[_v-d77321bc] {\n\t\tcolor: #32506d;\n\t}\n", ""]);

// exports


/***/ }),
/* 113 */
/***/ (function(module, exports, __webpack_require__) {

// style-loader: Adds some css to the DOM by adding a <style> tag

// load the styles
var content = __webpack_require__(114);
if(typeof content === 'string') content = [[module.i, content, '']];
// add the styles to the DOM
var update = __webpack_require__(1)(content, {});
if(content.locals) module.exports = content.locals;
// Hot Module Replacement
if(false) {
	// When the styles change, update the <style> tags
	if(!content.locals) {
		module.hot.accept("!!../../../node_modules/css-loader/index.js!../../../node_modules/vue-loader/lib/style-rewriter.js?id=_v-d77321bc&file=schedule-tab-panel.vue!../../../node_modules/vue-loader/lib/selector.js?type=style&index=1!../../../node_modules/eslint-loader/index.js!../../../node_modules/eslint-loader/index.js!./schedule-tab-panel.vue", function() {
			var newContent = require("!!../../../node_modules/css-loader/index.js!../../../node_modules/vue-loader/lib/style-rewriter.js?id=_v-d77321bc&file=schedule-tab-panel.vue!../../../node_modules/vue-loader/lib/selector.js?type=style&index=1!../../../node_modules/eslint-loader/index.js!../../../node_modules/eslint-loader/index.js!./schedule-tab-panel.vue");
			if(typeof newContent === 'string') newContent = [[module.id, newContent, '']];
			update(newContent);
		});
	}
	// When the module is disposed, remove the <style> tags
	module.hot.dispose(function() { update(); });
}

/***/ }),
/* 114 */
/***/ (function(module, exports, __webpack_require__) {

exports = module.exports = __webpack_require__(0)();
// imports


// module
exports.push([module.i, "\n\t#rop_core .time-picker.timepicker-style-fix .dropdown {\n\t\ttop: 4px;\n\t}\n\t#rop_core .time-picker.timepicker-style-fix ul {\n\t\tmargin: 0;\n\t}\n\t#rop_core .time-picker.timepicker-style-fix ul li {\n\t\tlist-style: none;\n\t}\n\n\t#rop_core .time-picker.timepicker-style-fix .dropdown ul li.active,\n\t#rop_core .time-picker.timepicker-style-fix .dropdown ul li.active:hover {\n\t\tbackground: #e85407;\n\t}\n", ""]);

// exports


/***/ }),
/* 115 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _keys = __webpack_require__(6);

var _keys2 = _interopRequireDefault(_keys);

var _buttonCheckbox = __webpack_require__(116);

var _buttonCheckbox2 = _interopRequireDefault(_buttonCheckbox);

var _vue2Timepicker = __webpack_require__(119);

var _vue2Timepicker2 = _interopRequireDefault(_vue2Timepicker);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

// <template>
// 	<div class="tab-view">
// 		<div class="panel-body" style="overflow: inherit;">
// 			<h3>Custom Schedule</h3>
// 			<figure class="avatar avatar-lg" style="text-align: center;">
// 				<img :src="img" v-if="img">
// 				<i class="fa" :class="icon" style="line-height: 48px;" aria-hidden="true" v-else></i>
// 				<i class="avatar-icon fa" :class="icon" aria-hidden="true" v-if="img"></i>
// 				<!--<img src="img/avatar-5.png" class="avatar-icon" alt="...">-->
// 			</figure>
// 			<div class="d-inline-block" style="vertical-align: top; margin-left: 16px;">
// 				<h6>{{user_name}}</h6>
// 				<b class="service" :class="service">{{service_name}}</b>
// 			</div>
// 			<div class="d-inline-block" style="vertical-align: top; margin-left: 16px; width: 80%">
// 				<h4><i class="fa fa-info-circle"></i> Info</h4>
// 				<p><i>Each <b>account</b> can have it's own <b>Schedule</b> for sharing, on the left you can see the
// 					current selected account and network, bellow are the <b>Schedule</b> options for the account.
// 					Don't forget to save after each change and remember, you can always reset an account to the defaults.
// 				</i></p>
// 			</div>
// 			<div class="container">
// 				<div class="columns">
// 					<div class="column col-sm-12 col-md-12 col-lg-12">
// 						<div class="columns">
// 							<div class="column col-sm-12 col-md-4 col-xl-3 col-ml-2 col-4 text-right">
// 								<b>Account</b><br/>
// 								<i>Specify an account to change the settings of.</i>
// 							</div>
// 							<div class="column col-sm-12 col-md-8 col-xl-9 col-mr-4 col-7 text-left">
// 								<div class="form-group">
// 									<select class="form-select" v-model="selected_account" @change="getAccountSchedule()">
// 										<option v-for="( account, id ) in active_accounts" :value="id" >{{account.user}} - {{account.service}} </option>
// 									</select>
// 								</div>
// 							</div>
// 						</div>
// 						<hr/>
//
// 						<h4>Schedule</h4>
// 						<!-- Schedule Type - Can be 'recurring' or 'fixed'
// 							 If Recurring than an repeating interval is filled (float) Eg. 2.5 hours
// 							 If Fixed days of the week are selected and a specific time is selected. -->
// 						<div class="columns">
// 							<div class="column col-sm-12 col-md-4 col-xl-3 col-ml-2 col-4 text-right">
// 								<b>Schedule Type</b><br/>
// 								<i>What type of schedule to use.</i>
// 							</div>
// 							<div class="column col-sm-12 col-md-8 col-xl-9 col-mr-4 col-7 text-left">
// 								<div class="form-group">
// 									<select class="form-select" v-model="schedule.type">
// 										<option value="recurring">Recurring</option>
// 										<option value="fixed">Fixed</option>
// 									</select>
// 								</div>
// 							</div>
// 						</div>
//
// 						<div class="columns" v-if="schedule.type === 'fixed'">
// 							<div class="column col-sm-12 col-md-4 col-xl-3 col-ml-2 col-4 text-right">
// 								<b>Fixed Schedule Days</b><br/>
// 								<i>The days when to share for this account.</i>
// 							</div>
// 							<div class="column col-sm-12 col-md-8 col-xl-9 col-mr-4 col-7 text-left">
// 								<div class="form-group">
// 									<button-checkbox v-for="( data, label ) in daysObject" :key="label" :value="data.value" :label="label" :checked="data.checked" @add-day="addDay" @rmv-day="rmvDay"></button-checkbox>
// 								</div>
// 							</div>
// 						</div>
// 						<div class="columns" v-if="schedule.type === 'fixed'">
// 							<div class="column col-sm-12 col-md-4 col-xl-3 col-ml-2 col-4 text-right">
// 								<b>Fixed Schedule Time</b><br/>
// 								<i>The time at witch to share for this account.</i>
// 							</div>
// 							<div class="column col-sm-12 col-md-8 col-xl-9 col-mr-4 col-7 text-left">
// 								<div class="form-group">
// 									<div class="input-group" v-for="( time, index ) in schedule.interval_f.time">
// 										<vue-timepicker :minute-interval="5" class="timepicker-style-fix" :value="getTime( index )" @change="syncTime( $event, index )" hide-clear-button></vue-timepicker>
// 										<button class="btn btn-success input-group-btn" v-if="schedule.interval_f.time.length > 1" @click="rmvTime( index )">
// 											<i class="fa fa-fw fa-minus"></i>
// 										</button>
//                                         <button class="btn btn-success input-group-btn" v-if="index == schedule.interval_f.time.length - 1" @click="addTime()">
//                                             <i class="fa fa-fw fa-plus"></i>
//                                         </button>
// 									</div>
// 								</div>
// 							</div>
// 						</div>
// 						<div class="columns" v-else>
// 							<div class="column col-sm-12 col-md-4 col-xl-3 col-ml-2 col-4 text-right">
// 								<b>Recurring Schedule Interval</b><br/>
// 								<i>A recurring interval to use for sharing. Once every 'X' hours.</i>
// 							</div>
// 							<div class="column col-sm-12 col-md-8 col-xl-9 col-mr-4 col-7 text-left">
// 								<div class="form-group">
// 									<input type="number" class="form-input" v-model="schedule.interval_r" placeholder="hours.min (Eg. 2.5)" />
// 								</div>
// 							</div>
// 						</div>
//
//
//
// 						<hr/>
// 					</div>
// 				</div>
// 			</div>
// 		</div>
// 		<div class="panel-footer">
// 			<button class="btn btn-primary" @click="saveSchedule()"><i class="fa fa-check"></i> Save Schedule</button>
// 			<button class="btn btn-secondary" @click="resetSchedule()"><i class="fa fa-ban"></i> Reset to Defaults</button>
// 		</div>
// 	</div>
// </template>
//
// <script>
module.exports = {
	name: 'schedule-view',
	data: function data() {
		var key = null;
		if ((0, _keys2.default)(this.$store.state.activeAccounts)[0] !== undefined) key = (0, _keys2.default)(this.$store.state.activeAccounts)[0];
		return {
			selected_account: key,
			days: {
				'Mon': {
					'value': '1',
					'checked': false
				},
				'Tue': {
					'value': '2',
					'checked': false
				},
				'Wen': {
					'value': '3',
					'checked': false
				},
				'Thu': {
					'value': '4',
					'checked': false
				},
				'Fri': {
					'value': '5',
					'checked': false
				},
				'Sat': {
					'value': '6',
					'checked': false
				},
				'Sun': {
					'value': '7',
					'checked': false
				}
			}
		};
	},
	mounted: function mounted() {
		// Uncomment this when not fixed tab on schedule
		// this.getAccountSchedule()
	},
	filters: {
		capitalize: function capitalize(value) {
			if (!value) return '';
			value = value.toString();
			return value.charAt(0).toUpperCase() + value.slice(1);
		}
	},
	computed: {
		schedule: function schedule() {
			return this.$store.state.activeSchedule;
		},
		daysObject: function daysObject() {
			var daysObject = this.days;
			for (var day in daysObject) {
				daysObject[day].checked = this.isChecked(daysObject[day].value);
			}
			return daysObject;
		},
		active_accounts: function active_accounts() {
			return this.$store.state.activeAccounts;
		},
		icon: function icon() {
			var serviceIcon = 'fa-user';
			if (this.selected_account !== null) {
				serviceIcon = 'fa-';
				var account = this.active_accounts[this.selected_account];
				if (account.service === 'facebook') serviceIcon = serviceIcon.concat('facebook-official');
				if (account.service === 'twitter') serviceIcon = serviceIcon.concat('twitter');
				if (account.service === 'linkedin') serviceIcon = serviceIcon.concat('linkedin');
				if (account.service === 'tumblr') serviceIcon = serviceIcon.concat('tumblr');
			}
			return serviceIcon;
		},
		img: function img() {
			var img = '';
			if (this.selected_account !== null && this.active_accounts[this.selected_account].img !== '' && this.active_accounts[this.selected_account].img !== undefined) {
				img = this.active_accounts[this.selected_account].img;
			}
			return img;
		},
		service: function service() {
			var serviceClass = '';
			if (this.selected_account !== null && this.active_accounts[this.selected_account].service) {
				serviceClass = this.active_accounts[this.selected_account].service;
			}
			return serviceClass;
		},
		service_name: function service_name() {
			if (this.service !== '') return this.service.charAt(0).toUpperCase() + this.service.slice(1);
			return 'Service';
		},
		user_name: function user_name() {
			if (this.selected_account !== null && this.active_accounts[this.selected_account].user) return this.active_accounts[this.selected_account].user;
			return 'John Doe';
		}
	},
	watch: {
		active_accounts: function active_accounts() {
			if ((0, _keys2.default)(this.$store.state.activeAccounts)[0] && this.selected_account === null) {
				var key = (0, _keys2.default)(this.$store.state.activeAccounts)[0];
				this.selected_account = key;
				this.getAccountSchedule();
			}
		}
	},
	methods: {
		isChecked: function isChecked(value) {
			if (this.schedule.interval_f !== undefined && this.schedule.interval_f.week_days.indexOf(value) > -1) {
				return true;
			}
			return false;
		},
		getTime: function getTime(index) {
			var currentTime = this.schedule.interval_f.time[index];
			var timeParts = currentTime.split(':');
			return {
				'HH': timeParts[0],
				'mm': timeParts[1]
			};
		},
		syncTime: function syncTime(dataEvent, index) {
			if (this.schedule.interval_f.time[index] !== undefined) {
				this.schedule.interval_f.time[index] = dataEvent.data.HH + ':' + dataEvent.data.mm;
			}
		},
		addTime: function addTime() {
			this.schedule.interval_f.time.push('00:00');
		},
		rmvTime: function rmvTime(index) {
			this.schedule.interval_f.time.splice(index, 1);
		},
		addDay: function addDay(value) {
			this.schedule.interval_f.week_days.push(value);
		},
		rmvDay: function rmvDay(value) {
			var index = this.schedule.interval_f.week_days.indexOf(value);
			if (index > -1) {
				this.schedule.interval_f.week_days.splice(index, 1);
			}
		},
		getAccountSchedule: function getAccountSchedule() {
			console.log('Get Schedule for', this.selected_account);
			this.$store.dispatch('fetchSchedule', { service: this.active_accounts[this.selected_account].service, account_id: this.selected_account });
		},
		saveSchedule: function saveSchedule() {
			console.log('Save Schedule for', this.selected_account);
			this.$store.dispatch('saveSchedule', { service: this.active_accounts[this.selected_account].service, account_id: this.selected_account, schedule: this.schedule });
		},
		resetSchedule: function resetSchedule() {
			console.log('Reset Schedule for', this.selected_account);
			this.$store.dispatch('resetSchedule', { service: this.active_accounts[this.selected_account].service, account_id: this.selected_account });
			this.$forceUpdate();
		}
	},
	components: {
		ButtonCheckbox: _buttonCheckbox2.default,
		VueTimepicker: _vue2Timepicker2.default
	}
	// </script>
	//
	// <style scoped>
	// 	#rop_core .avatar .avatar-icon {
	// 		background: #333;
	// 		border-radius: 50%;
	// 		font-size: 16px;
	// 		text-align: center;
	// 		line-height: 20px;
	// 	}
	// 	#rop_core .avatar .avatar-icon.fa-facebook-official { background-color: #3b5998; }
	// 	#rop_core .avatar .avatar-icon.fa-twitter { background-color: #55acee; }
	// 	#rop_core .avatar .avatar-icon.fa-linkedin { background-color: #007bb5; }
	// 	#rop_core .avatar .avatar-icon.fa-tumblr { background-color: #32506d; }
	//
	// 	#rop_core .service.facebook {
	// 		color: #3b5998;
	// 	}
	//
	// 	#rop_core .service.twitter {
	// 		color: #55acee;
	// 	}
	//
	// 	#rop_core .service.linkedin {
	// 		color: #007bb5;
	// 	}
	//
	// 	#rop_core .service.tumblr {
	// 		color: #32506d;
	// 	}
	// </style>
	// <style>
	// 	#rop_core .time-picker.timepicker-style-fix .dropdown {
	// 		top: 4px;
	// 	}
	// 	#rop_core .time-picker.timepicker-style-fix ul {
	// 		margin: 0;
	// 	}
	// 	#rop_core .time-picker.timepicker-style-fix ul li {
	// 		list-style: none;
	// 	}
	//
	// 	#rop_core .time-picker.timepicker-style-fix .dropdown ul li.active,
	// 	#rop_core .time-picker.timepicker-style-fix .dropdown ul li.active:hover {
	// 		background: #e85407;
	// 	}
	// </style>

};

/***/ }),
/* 116 */
/***/ (function(module, exports, __webpack_require__) {

var __vue_script__, __vue_template__
__vue_script__ = __webpack_require__(117)
__vue_template__ = __webpack_require__(118)
module.exports = __vue_script__ || {}
if (module.exports.__esModule) module.exports = module.exports.default
if (__vue_template__) { (typeof module.exports === "function" ? module.exports.options : module.exports).template = __vue_template__ }
if (false) {(function () {  module.hot.accept()
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), true)
  if (!hotAPI.compatible) return
  var id = "/var/www/html/wp-base/wp-content/plugins/tweet-old-post/vue/src/vue-elements/reusables/button-checkbox.vue"
  if (!module.hot.data) {
    hotAPI.createRecord(id, module.exports)
  } else {
    hotAPI.update(id, module.exports, __vue_template__)
  }
})()}

/***/ }),
/* 117 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


// <template>
// 	<button class="btn" :class="is_active" @click="toggleThis()" >{{label}}</button>
// </template>
//
// <script>
module.exports = {
	name: 'button-checkbox',
	props: {
		value: {
			default: '0',
			type: String
		},
		label: {
			default: '',
			type: String
		},
		id: {
			default: function _default() {
				var base = 'day';
				if (this.label !== '' && this.label !== undefined) {
					base = base + '_' + this.label.toLowerCase();
				}

				return base;
			}
		},
		checked: {
			default: false,
			type: Boolean
		}
	},
	data: function data() {
		return {
			componentCheckState: this.checked
		};
	},
	computed: {
		is_active: function is_active() {
			return {
				'active': this.componentCheckState === true
			};
		}
	},
	watch: {
		checked: function checked() {
			this.componentCheckState = this.checked;
		}
	},
	methods: {
		toggleThis: function toggleThis() {
			this.componentCheckState = !this.componentCheckState;
			if (this.componentCheckState) {
				this.$emit('add-day', this.value);
			} else {
				this.$emit('rmv-day', this.value);
			}
		}
	}
	// </script>

};

/***/ }),
/* 118 */
/***/ (function(module, exports) {

module.exports = "\n\t<button class=\"btn\" :class=\"is_active\" @click=\"toggleThis()\" >{{label}}</button>\n";

/***/ }),
/* 119 */
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(120)


/***/ }),
/* 120 */
/***/ (function(module, exports, __webpack_require__) {

var __vue_script__, __vue_template__
__webpack_require__(121)
__vue_script__ = __webpack_require__(124)
__vue_template__ = __webpack_require__(127)
module.exports = __vue_script__ || {}
if (module.exports.__esModule) module.exports = module.exports.default
if (__vue_template__) { (typeof module.exports === "function" ? module.exports.options : module.exports).template = __vue_template__ }
if (false) {(function () {  module.hot.accept()
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), true)
  if (!hotAPI.compatible) return
  var id = "/var/www/html/wp-base/wp-content/plugins/tweet-old-post/node_modules/vue2-timepicker/src/vue-timepicker.vue"
  if (!module.hot.data) {
    hotAPI.createRecord(id, module.exports)
  } else {
    hotAPI.update(id, module.exports, __vue_template__)
  }
})()}

/***/ }),
/* 121 */
/***/ (function(module, exports, __webpack_require__) {

// style-loader: Adds some css to the DOM by adding a <style> tag

// load the styles
var content = __webpack_require__(122);
if(typeof content === 'string') content = [[module.i, content, '']];
// add the styles to the DOM
var update = __webpack_require__(1)(content, {});
if(content.locals) module.exports = content.locals;
// Hot Module Replacement
if(false) {
	// When the styles change, update the <style> tags
	if(!content.locals) {
		module.hot.accept("!!../../css-loader/index.js!../../vue-loader/lib/style-rewriter.js?id=_v-58411cf4&file=vue-timepicker.vue!../../vue-loader/lib/selector.js?type=style&index=0!./vue-timepicker.vue", function() {
			var newContent = require("!!../../css-loader/index.js!../../vue-loader/lib/style-rewriter.js?id=_v-58411cf4&file=vue-timepicker.vue!../../vue-loader/lib/selector.js?type=style&index=0!./vue-timepicker.vue");
			if(typeof newContent === 'string') newContent = [[module.id, newContent, '']];
			update(newContent);
		});
	}
	// When the module is disposed, remove the <style> tags
	module.hot.dispose(function() { update(); });
}

/***/ }),
/* 122 */
/***/ (function(module, exports, __webpack_require__) {

exports = module.exports = __webpack_require__(0)();
// imports
exports.i(__webpack_require__(123), "");

// module
exports.push([module.i, "\n", ""]);

// exports


/***/ }),
/* 123 */
/***/ (function(module, exports, __webpack_require__) {

exports = module.exports = __webpack_require__(0)();
// imports


// module
exports.push([module.i, ".time-picker {\n  display: inline-block;\n  position: relative;\n  font-size: 1em;\n  width: 10em;\n  font-family: sans-serif;\n  vertical-align: middle;\n}\n\n.time-picker * {\n  box-sizing: border-box;\n}\n\n.time-picker input.display-time {\n  border: 1px solid #d2d2d2;\n  width: 10em;\n  height: 2.2em;\n  padding: 0.3em 0.5em;\n  font-size: 1em;\n}\n\n.time-picker .clear-btn {\n  position: absolute;\n  display: flex;\n  flex-flow: column nowrap;\n  justify-content: center;\n  align-items: center;\n  top: 0;\n  right: 0;\n  bottom: 0;\n  margin-top: -0.15em;\n  z-index: 3;\n  font-size: 1.1em;\n  line-height: 1em;\n  vertical-align: middle;\n  width: 1.3em;\n  color: #d2d2d2;\n  background: rgba(255,255,255,0);\n  text-align: center;\n  font-style: normal;\n\n  -webkit-transition: color .2s;\n  transition: color .2s;\n}\n\n.time-picker .clear-btn:hover {\n  color: #797979;\n  cursor: pointer;\n}\n\n.time-picker .time-picker-overlay {\n  z-index: 2;\n  position: fixed;\n  top: 0;\n  left: 0;\n  right: 0;\n  bottom: 0;\n}\n\n.time-picker .dropdown {\n  position: absolute;\n  z-index: 5;\n  top: calc(2.2em + 2px);\n  left: 0;\n  background: #fff;\n  box-shadow: 0 1px 6px rgba(0,0,0,0.15);\n  width: 10em;\n  height: 10em;\n  font-weight: normal;\n}\n\n.time-picker .dropdown .select-list {\n  width: 10em;\n  height: 10em;\n  overflow: hidden;\n  display: flex;\n  flex-flow: row nowrap;\n  align-items: stretch;\n  justify-content: space-between;\n}\n\n.time-picker .dropdown ul {\n  padding: 0;\n  margin: 0;\n  list-style: none;\n\n  flex: 1;\n  overflow-x: hidden;\n  overflow-y: auto;\n}\n\n.time-picker .dropdown ul.minutes,\n.time-picker .dropdown ul.seconds,\n.time-picker .dropdown ul.apms{\n  border-left: 1px solid #fff;\n}\n\n.time-picker .dropdown ul li {\n  text-align: center;\n  padding: 0.3em 0;\n  color: #161616;\n}\n\n.time-picker .dropdown ul li:not(.hint):hover {\n  background: rgba(0,0,0,.08);\n  color: #161616;\n  cursor: pointer;\n}\n\n.time-picker .dropdown ul li.active,\n.time-picker .dropdown ul li.active:hover {\n  background: #41B883;\n  color: #fff;\n}\n\n.time-picker .dropdown .hint {\n  color: #a5a5a5;\n  cursor: default;\n  font-size: 0.8em;\n}\n", ""]);

// exports


/***/ }),
/* 124 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
  value: true
});

var _keys = __webpack_require__(6);

var _keys2 = _interopRequireDefault(_keys);

var _stringify = __webpack_require__(125);

var _stringify2 = _interopRequireDefault(_stringify);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

var CONFIG = {
  HOUR_TOKENS: ['HH', 'H', 'hh', 'h', 'kk', 'k'],
  MINUTE_TOKENS: ['mm', 'm'],
  SECOND_TOKENS: ['ss', 's'],
  APM_TOKENS: ['A', 'a']
};

exports.default = {
  name: 'VueTimepicker',

  props: {
    value: { type: Object },
    hideClearButton: { type: Boolean },
    format: { type: String },
    minuteInterval: { type: Number },
    secondInterval: { type: Number },
    id: { type: String }
  },

  data: function data() {
    return {
      hours: [],
      minutes: [],
      seconds: [],
      apms: [],
      showDropdown: false,
      muteWatch: false,
      hourType: 'HH',
      minuteType: 'mm',
      secondType: '',
      apmType: '',
      hour: '',
      minute: '',
      second: '',
      apm: '',
      fullValues: undefined
    };
  },


  computed: {
    displayTime: function displayTime() {
      var formatString = String(this.format || 'HH:mm');
      if (this.hour) {
        formatString = formatString.replace(new RegExp(this.hourType, 'g'), this.hour);
      }
      if (this.minute) {
        formatString = formatString.replace(new RegExp(this.minuteType, 'g'), this.minute);
      }
      if (this.second && this.secondType) {
        formatString = formatString.replace(new RegExp(this.secondType, 'g'), this.second);
      }
      if (this.apm && this.apmType) {
        formatString = formatString.replace(new RegExp(this.apmType, 'g'), this.apm);
      }
      return formatString;
    },
    showClearBtn: function showClearBtn() {
      if (this.hour && this.hour !== '' || this.minute && this.minute !== '') {
        return true;
      }
      return false;
    }
  },

  watch: {
    'format': 'renderFormat',
    minuteInterval: function minuteInterval(newInteval) {
      this.renderList('minute', newInteval);
    },
    secondInterval: function secondInterval(newInteval) {
      this.renderList('second', newInteval);
    },

    'value': 'readValues',
    'displayTime': 'fillValues'
  },

  methods: {
    formatValue: function formatValue(type, i) {
      switch (type) {
        case 'H':
        case 'm':
        case 's':
          return String(i);
        case 'HH':
        case 'mm':
        case 'ss':
          return i < 10 ? '0' + i : String(i);
        case 'h':
        case 'k':
          return String(i + 1);
        case 'hh':
        case 'kk':
          return i + 1 < 10 ? '0' + (i + 1) : String(i + 1);
        default:
          return '';
      }
    },
    checkAcceptingType: function checkAcceptingType(validValues, formatString, fallbackValue) {
      if (!validValues || !formatString || !formatString.length) {
        return '';
      }
      for (var i = 0; i < validValues.length; i++) {
        if (formatString.indexOf(validValues[i]) > -1) {
          return validValues[i];
        }
      }
      return fallbackValue || '';
    },
    renderFormat: function renderFormat(newFormat) {
      newFormat = newFormat || this.format;
      if (!newFormat || !newFormat.length) {
        newFormat = 'HH:mm';
      }

      this.hourType = this.checkAcceptingType(CONFIG.HOUR_TOKENS, newFormat, 'HH');
      this.minuteType = this.checkAcceptingType(CONFIG.MINUTE_TOKENS, newFormat, 'mm');
      this.secondType = this.checkAcceptingType(CONFIG.SECOND_TOKENS, newFormat);
      this.apmType = this.checkAcceptingType(CONFIG.APM_TOKENS, newFormat);

      this.renderHoursList();
      this.renderList('minute');

      if (this.secondType) {
        this.renderList('second');
      }

      if (this.apmType) {
        this.renderApmList();
      }

      var self = this;
      this.$nextTick(function () {
        self.readValues();
      });
    },
    renderHoursList: function renderHoursList() {
      var hoursCount = this.hourType === 'h' || this.hourType === 'hh' ? 12 : 24;
      this.hours = [];
      for (var i = 0; i < hoursCount; i++) {
        this.hours.push(this.formatValue(this.hourType, i));
      }
    },
    renderList: function renderList(listType, interval) {
      if (listType === 'second') {
        interval = interval || this.secondInterval;
      } else if (listType === 'minute') {
        interval = interval || this.minuteInterval;
      } else {
        return;
      }

      if (interval === 0) {
        interval = 60;
      } else if (interval > 60) {
        window.console.warn('`' + listType + '-interval` should be less than 60. Current value is', interval);
        interval = 1;
      } else if (interval < 1) {
        window.console.warn('`' + listType + '-interval` should be NO less than 1. Current value is', interval);
        interval = 1;
      } else if (!interval) {
        interval = 1;
      }

      if (listType === 'minute') {
        this.minutes = [];
      } else {
        this.seconds = [];
      }

      for (var i = 0; i < 60; i += interval) {
        if (listType === 'minute') {
          this.minutes.push(this.formatValue(this.minuteType, i));
        } else {
          this.seconds.push(this.formatValue(this.secondType, i));
        }
      }
    },
    renderApmList: function renderApmList() {
      this.apms = [];
      if (!this.apmType) {
        return;
      }
      this.apms = this.apmType === 'A' ? ['AM', 'PM'] : ['am', 'pm'];
    },
    readValues: function readValues() {
      if (!this.value || this.muteWatch) {
        return;
      }

      var timeValue = JSON.parse((0, _stringify2.default)(this.value || {}));

      var values = (0, _keys2.default)(timeValue);
      if (values.length === 0) {
        return;
      }

      if (values.indexOf(this.hourType) > -1) {
        this.hour = timeValue[this.hourType];
      }

      if (values.indexOf(this.minuteType) > -1) {
        this.minute = timeValue[this.minuteType];
      }

      if (values.indexOf(this.secondType) > -1) {
        this.second = timeValue[this.secondType];
      } else {
        this.second = 0;
      }

      if (values.indexOf(this.apmType) > -1) {
        this.apm = timeValue[this.apmType];
      }

      this.fillValues();
    },
    fillValues: function fillValues() {
      var fullValues = {};

      var baseHour = this.hour;
      var baseHourType = this.hourType;

      var hourValue = baseHour || baseHour === 0 ? Number(baseHour) : '';
      var baseOnTwelveHours = this.isTwelveHours(baseHourType);
      var apmValue = baseOnTwelveHours && this.apm ? String(this.apm).toLowerCase() : false;

      CONFIG.HOUR_TOKENS.forEach(function (token) {
        if (token === baseHourType) {
          fullValues[token] = baseHour;
          return;
        }

        var value = void 0;
        var apm = void 0;
        switch (token) {
          case 'H':
          case 'HH':
            if (!String(hourValue).length) {
              fullValues[token] = '';
              return;
            } else if (baseOnTwelveHours) {
              if (apmValue === 'pm') {
                value = hourValue < 12 ? hourValue + 12 : hourValue;
              } else {
                value = hourValue % 12;
              }
            } else {
              value = hourValue % 24;
            }
            fullValues[token] = token === 'HH' && value < 10 ? '0' + value : String(value);
            break;
          case 'k':
          case 'kk':
            if (!String(hourValue).length) {
              fullValues[token] = '';
              return;
            } else if (baseOnTwelveHours) {
              if (apmValue === 'pm') {
                value = hourValue < 12 ? hourValue + 12 : hourValue;
              } else {
                value = hourValue === 12 ? 24 : hourValue;
              }
            } else {
              value = hourValue === 0 ? 24 : hourValue;
            }
            fullValues[token] = token === 'kk' && value < 10 ? '0' + value : String(value);
            break;
          case 'h':
          case 'hh':
            if (apmValue) {
              value = hourValue;
              apm = apmValue || 'am';
            } else {
              if (!String(hourValue).length) {
                fullValues[token] = '';
                fullValues.a = '';
                fullValues.A = '';
                return;
              } else if (hourValue > 11) {
                apm = 'pm';
                value = hourValue === 12 ? 12 : hourValue % 12;
              } else {
                if (baseOnTwelveHours) {
                  apm = '';
                } else {
                  apm = 'am';
                }
                value = hourValue % 12 === 0 ? 12 : hourValue;
              }
            }
            fullValues[token] = token === 'hh' && value < 10 ? '0' + value : String(value);
            fullValues.a = apm;
            fullValues.A = apm.toUpperCase();
            break;
        }
      });

      if (this.minute || this.minute === 0) {
        var minuteValue = Number(this.minute);
        fullValues.m = String(minuteValue);
        fullValues.mm = minuteValue < 10 ? '0' + minuteValue : String(minuteValue);
      } else {
        fullValues.m = '';
        fullValues.mm = '';
      }

      if (this.second || this.second === 0) {
        var secondValue = Number(this.second);
        fullValues.s = String(secondValue);
        fullValues.ss = secondValue < 10 ? '0' + secondValue : String(secondValue);
      } else {
        fullValues.s = '';
        fullValues.ss = '';
      }

      this.fullValues = fullValues;
      this.updateTimeValue(fullValues);
      this.$emit('change', { data: fullValues });
    },
    updateTimeValue: function updateTimeValue(fullValues) {
      this.muteWatch = true;

      var self = this;

      var baseTimeValue = JSON.parse((0, _stringify2.default)(this.value || {}));
      var timeValue = {};

      (0, _keys2.default)(baseTimeValue).forEach(function (key) {
        timeValue[key] = fullValues[key];
      });

      this.$emit('input', timeValue);

      this.$nextTick(function () {
        self.muteWatch = false;
      });
    },
    isTwelveHours: function isTwelveHours(token) {
      return token === 'h' || token === 'hh';
    },
    toggleDropdown: function toggleDropdown() {
      this.showDropdown = !this.showDropdown;
    },
    select: function select(type, value) {
      if (type === 'hour') {
        this.hour = value;
      } else if (type === 'minute') {
        this.minute = value;
      } else if (type === 'second') {
        this.second = value;
      } else if (type === 'apm') {
        this.apm = value;
      }
    },
    clearTime: function clearTime() {
      this.hour = '';
      this.minute = '';
      this.second = '';
      this.apm = '';
    }
  },

  mounted: function mounted() {
    this.renderFormat();
  }
};

/***/ }),
/* 125 */
/***/ (function(module, exports, __webpack_require__) {

module.exports = { "default": __webpack_require__(126), __esModule: true };

/***/ }),
/* 126 */
/***/ (function(module, exports, __webpack_require__) {

var core = __webpack_require__(3);
var $JSON = core.JSON || (core.JSON = { stringify: JSON.stringify });
module.exports = function stringify(it) { // eslint-disable-line no-unused-vars
  return $JSON.stringify.apply($JSON, arguments);
};


/***/ }),
/* 127 */
/***/ (function(module, exports) {

module.exports = "\n<span class=\"time-picker\">\n  <input class=\"display-time\" :id=\"id\" v-model=\"displayTime\" @click.stop=\"toggleDropdown\" type=\"text\" readonly />\n  <span class=\"clear-btn\" v-if=\"!hideClearButton\" v-show=\"!showDropdown && showClearBtn\" @click.stop=\"clearTime\">&times;</span>\n  <div class=\"time-picker-overlay\" v-if=\"showDropdown\" @click.stop=\"toggleDropdown\"></div>\n  <div class=\"dropdown\" v-show=\"showDropdown\">\n    <div class=\"select-list\">\n      <ul class=\"hours\">\n        <li class=\"hint\" v-text=\"hourType\"></li>\n        <li v-for=\"hr in hours\" v-text=\"hr\" :class=\"{active: hour === hr}\" @click.stop=\"select('hour', hr)\"></li>\n      </ul>\n      <ul class=\"minutes\">\n        <li class=\"hint\" v-text=\"minuteType\"></li>\n        <li v-for=\"m in minutes\" v-text=\"m\" :class=\"{active: minute === m}\" @click.stop=\"select('minute', m)\"></li>\n      </ul>\n      <ul class=\"seconds\" v-if=\"secondType\">\n        <li class=\"hint\" v-text=\"secondType\"></li>\n        <li v-for=\"s in seconds\" v-text=\"s\" :class=\"{active: second === s}\" @click.stop=\"select('second', s)\"></li>\n      </ul>\n      <ul class=\"apms\" v-if=\"apmType\">\n        <li class=\"hint\" v-text=\"apmType\"></li>\n        <li v-for=\"a in apms\" v-text=\"a\" :class=\"{active: apm === a}\" @click.stop=\"select('apm', a)\"></li>\n      </ul>\n    </div>\n  </div>\n</span>\n";

/***/ }),
/* 128 */
/***/ (function(module, exports) {

module.exports = "\n\t<div class=\"tab-view\" _v-d77321bc=\"\">\n\t\t<div class=\"panel-body\" style=\"overflow: inherit;\" _v-d77321bc=\"\">\n\t\t\t<h3 _v-d77321bc=\"\">Custom Schedule</h3>\n\t\t\t<figure class=\"avatar avatar-lg\" style=\"text-align: center;\" _v-d77321bc=\"\">\n\t\t\t\t<img :src=\"img\" v-if=\"img\" _v-d77321bc=\"\">\n\t\t\t\t<i class=\"fa\" :class=\"icon\" style=\"line-height: 48px;\" aria-hidden=\"true\" v-else=\"\" _v-d77321bc=\"\"></i>\n\t\t\t\t<i class=\"avatar-icon fa\" :class=\"icon\" aria-hidden=\"true\" v-if=\"img\" _v-d77321bc=\"\"></i>\n\t\t\t\t<!--<img src=\"img/avatar-5.png\" class=\"avatar-icon\" alt=\"...\">-->\n\t\t\t</figure>\n\t\t\t<div class=\"d-inline-block\" style=\"vertical-align: top; margin-left: 16px;\" _v-d77321bc=\"\">\n\t\t\t\t<h6 _v-d77321bc=\"\">{{user_name}}</h6>\n\t\t\t\t<b class=\"service\" :class=\"service\" _v-d77321bc=\"\">{{service_name}}</b>\n\t\t\t</div>\n\t\t\t<div class=\"d-inline-block\" style=\"vertical-align: top; margin-left: 16px; width: 80%\" _v-d77321bc=\"\">\n\t\t\t\t<h4 _v-d77321bc=\"\"><i class=\"fa fa-info-circle\" _v-d77321bc=\"\"></i> Info</h4>\n\t\t\t\t<p _v-d77321bc=\"\"><i _v-d77321bc=\"\">Each <b _v-d77321bc=\"\">account</b> can have it's own <b _v-d77321bc=\"\">Schedule</b> for sharing, on the left you can see the\n\t\t\t\t\tcurrent selected account and network, bellow are the <b _v-d77321bc=\"\">Schedule</b> options for the account.\n\t\t\t\t\tDon't forget to save after each change and remember, you can always reset an account to the defaults.\n\t\t\t\t</i></p>\n\t\t\t</div>\n\t\t\t<div class=\"container\" _v-d77321bc=\"\">\n\t\t\t\t<div class=\"columns\" _v-d77321bc=\"\">\n\t\t\t\t\t<div class=\"column col-sm-12 col-md-12 col-lg-12\" _v-d77321bc=\"\">\n\t\t\t\t\t\t<div class=\"columns\" _v-d77321bc=\"\">\n\t\t\t\t\t\t\t<div class=\"column col-sm-12 col-md-4 col-xl-3 col-ml-2 col-4 text-right\" _v-d77321bc=\"\">\n\t\t\t\t\t\t\t\t<b _v-d77321bc=\"\">Account</b><br _v-d77321bc=\"\">\n\t\t\t\t\t\t\t\t<i _v-d77321bc=\"\">Specify an account to change the settings of.</i>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t<div class=\"column col-sm-12 col-md-8 col-xl-9 col-mr-4 col-7 text-left\" _v-d77321bc=\"\">\n\t\t\t\t\t\t\t\t<div class=\"form-group\" _v-d77321bc=\"\">\n\t\t\t\t\t\t\t\t\t<select class=\"form-select\" v-model=\"selected_account\" @change=\"getAccountSchedule()\" _v-d77321bc=\"\">\n\t\t\t\t\t\t\t\t\t\t<option v-for=\"( account, id ) in active_accounts\" :value=\"id\" _v-d77321bc=\"\">{{account.user}} - {{account.service}} </option>\n\t\t\t\t\t\t\t\t\t</select>\n\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t\t<hr _v-d77321bc=\"\">\n\n\t\t\t\t\t\t<h4 _v-d77321bc=\"\">Schedule</h4>\n\t\t\t\t\t\t<!-- Schedule Type - Can be 'recurring' or 'fixed'\n\t\t\t\t\t\t\t If Recurring than an repeating interval is filled (float) Eg. 2.5 hours\n\t\t\t\t\t\t\t If Fixed days of the week are selected and a specific time is selected. -->\n\t\t\t\t\t\t<div class=\"columns\" _v-d77321bc=\"\">\n\t\t\t\t\t\t\t<div class=\"column col-sm-12 col-md-4 col-xl-3 col-ml-2 col-4 text-right\" _v-d77321bc=\"\">\n\t\t\t\t\t\t\t\t<b _v-d77321bc=\"\">Schedule Type</b><br _v-d77321bc=\"\">\n\t\t\t\t\t\t\t\t<i _v-d77321bc=\"\">What type of schedule to use.</i>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t<div class=\"column col-sm-12 col-md-8 col-xl-9 col-mr-4 col-7 text-left\" _v-d77321bc=\"\">\n\t\t\t\t\t\t\t\t<div class=\"form-group\" _v-d77321bc=\"\">\n\t\t\t\t\t\t\t\t\t<select class=\"form-select\" v-model=\"schedule.type\" _v-d77321bc=\"\">\n\t\t\t\t\t\t\t\t\t\t<option value=\"recurring\" _v-d77321bc=\"\">Recurring</option>\n\t\t\t\t\t\t\t\t\t\t<option value=\"fixed\" _v-d77321bc=\"\">Fixed</option>\n\t\t\t\t\t\t\t\t\t</select>\n\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t</div>\n\n\t\t\t\t\t\t<div class=\"columns\" v-if=\"schedule.type === 'fixed'\" _v-d77321bc=\"\">\n\t\t\t\t\t\t\t<div class=\"column col-sm-12 col-md-4 col-xl-3 col-ml-2 col-4 text-right\" _v-d77321bc=\"\">\n\t\t\t\t\t\t\t\t<b _v-d77321bc=\"\">Fixed Schedule Days</b><br _v-d77321bc=\"\">\n\t\t\t\t\t\t\t\t<i _v-d77321bc=\"\">The days when to share for this account.</i>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t<div class=\"column col-sm-12 col-md-8 col-xl-9 col-mr-4 col-7 text-left\" _v-d77321bc=\"\">\n\t\t\t\t\t\t\t\t<div class=\"form-group\" _v-d77321bc=\"\">\n\t\t\t\t\t\t\t\t\t<button-checkbox v-for=\"( data, label ) in daysObject\" :key=\"label\" :value=\"data.value\" :label=\"label\" :checked=\"data.checked\" @add-day=\"addDay\" @rmv-day=\"rmvDay\" _v-d77321bc=\"\"></button-checkbox>\n\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t\t<div class=\"columns\" v-if=\"schedule.type === 'fixed'\" _v-d77321bc=\"\">\n\t\t\t\t\t\t\t<div class=\"column col-sm-12 col-md-4 col-xl-3 col-ml-2 col-4 text-right\" _v-d77321bc=\"\">\n\t\t\t\t\t\t\t\t<b _v-d77321bc=\"\">Fixed Schedule Time</b><br _v-d77321bc=\"\">\n\t\t\t\t\t\t\t\t<i _v-d77321bc=\"\">The time at witch to share for this account.</i>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t<div class=\"column col-sm-12 col-md-8 col-xl-9 col-mr-4 col-7 text-left\" _v-d77321bc=\"\">\n\t\t\t\t\t\t\t\t<div class=\"form-group\" _v-d77321bc=\"\">\n\t\t\t\t\t\t\t\t\t<div class=\"input-group\" v-for=\"( time, index ) in schedule.interval_f.time\" _v-d77321bc=\"\">\n\t\t\t\t\t\t\t\t\t\t<vue-timepicker :minute-interval=\"5\" class=\"timepicker-style-fix\" :value=\"getTime( index )\" @change=\"syncTime( $event, index )\" hide-clear-button=\"\" _v-d77321bc=\"\"></vue-timepicker>\n\t\t\t\t\t\t\t\t\t\t<button class=\"btn btn-success input-group-btn\" v-if=\"schedule.interval_f.time.length > 1\" @click=\"rmvTime( index )\" _v-d77321bc=\"\">\n\t\t\t\t\t\t\t\t\t\t\t<i class=\"fa fa-fw fa-minus\" _v-d77321bc=\"\"></i>\n\t\t\t\t\t\t\t\t\t\t</button>\n                                        <button class=\"btn btn-success input-group-btn\" v-if=\"index == schedule.interval_f.time.length - 1\" @click=\"addTime()\" _v-d77321bc=\"\">\n                                            <i class=\"fa fa-fw fa-plus\" _v-d77321bc=\"\"></i>\n                                        </button>\n\t\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t\t<div class=\"columns\" v-else=\"\" _v-d77321bc=\"\">\n\t\t\t\t\t\t\t<div class=\"column col-sm-12 col-md-4 col-xl-3 col-ml-2 col-4 text-right\" _v-d77321bc=\"\">\n\t\t\t\t\t\t\t\t<b _v-d77321bc=\"\">Recurring Schedule Interval</b><br _v-d77321bc=\"\">\n\t\t\t\t\t\t\t\t<i _v-d77321bc=\"\">A recurring interval to use for sharing. Once every 'X' hours.</i>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t<div class=\"column col-sm-12 col-md-8 col-xl-9 col-mr-4 col-7 text-left\" _v-d77321bc=\"\">\n\t\t\t\t\t\t\t\t<div class=\"form-group\" _v-d77321bc=\"\">\n\t\t\t\t\t\t\t\t\t<input type=\"number\" class=\"form-input\" v-model=\"schedule.interval_r\" placeholder=\"hours.min (Eg. 2.5)\" _v-d77321bc=\"\">\n\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t</div>\n\n\n\n\t\t\t\t\t\t<hr _v-d77321bc=\"\">\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t</div>\n\t\t<div class=\"panel-footer\" _v-d77321bc=\"\">\n\t\t\t<button class=\"btn btn-primary\" @click=\"saveSchedule()\" _v-d77321bc=\"\"><i class=\"fa fa-check\" _v-d77321bc=\"\"></i> Save Schedule</button>\n\t\t\t<button class=\"btn btn-secondary\" @click=\"resetSchedule()\" _v-d77321bc=\"\"><i class=\"fa fa-ban\" _v-d77321bc=\"\"></i> Reset to Defaults</button>\n\t\t</div>\n\t</div>\n";

/***/ }),
/* 129 */
/***/ (function(module, exports, __webpack_require__) {

var __vue_script__, __vue_template__
__vue_script__ = __webpack_require__(130)
__vue_template__ = __webpack_require__(131)
module.exports = __vue_script__ || {}
if (module.exports.__esModule) module.exports = module.exports.default
if (__vue_template__) { (typeof module.exports === "function" ? module.exports.options : module.exports).template = __vue_template__ }
if (false) {(function () {  module.hot.accept()
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), true)
  if (!hotAPI.compatible) return
  var id = "/var/www/html/wp-base/wp-content/plugins/tweet-old-post/vue/src/vue-elements/logs-tab-panel.vue"
  if (!module.hot.data) {
    hotAPI.createRecord(id, module.exports)
  } else {
    hotAPI.update(id, module.exports, __vue_template__)
  }
})()}

/***/ }),
/* 130 */
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
			logs: this.$store.state.page.logs
		};
	}
	// </script>

};

/***/ }),
/* 131 */
/***/ (function(module, exports) {

module.exports = "\n    <div class=\"container\">\n        <h3>Logs</h3>\n        <div class=\"columns\">\n            <div class=\"column col-12\">\n                <pre class=\"code\" data-lang=\"Vue.js\">\n                    <code>{{ logs }}</code>\n                </pre>\n            </div>\n        </div>\n    </div>\n";

/***/ }),
/* 132 */
/***/ (function(module, exports) {

module.exports = "\n\t<div>\n\t\t<div class=\"panel title-panel\" style=\"margin-bottom: 40px; padding-bottom: 20px;\">\n\t\t\t<div class=\"panel-header\">\n\t\t\t\t<img :src=\"plugin_logo\" style=\"float: left; margin-right: 10px;\" />\n\t\t\t\t<h1 class=\"d-inline-block\">Revive Old Posts</h1><span class=\"powered\"> by <a href=\"https://themeisle.com\" target=\"_blank\"><b>ThemeIsle</b></a></span>\n\t\t\t</div>\n\t\t</div>\n\t\t<div class=\"panel\">\n\t\t\t<div class=\"panel-nav\" style=\"padding: 8px;\">\n\t\t\t\t<ul class=\"tab\">\n\t\t\t\t\t<li class=\"tab-item\" v-for=\"tab in displayTabs\" :class=\"{ active: tab.isActive }\"><a href=\"#\" @click=\"switchTab( tab.slug )\">{{ tab.name }}</a></li>\n\t\t\t\t\t<li class=\"tab-item tab-action\">\n\t\t\t\t\t\t<div class=\"form-group\">\n\t\t\t\t\t\t\t<label class=\"form-switch\">\n\t\t\t\t\t\t\t\t<input type=\"checkbox\" />\n\t\t\t\t\t\t\t\t<i class=\"form-icon\"></i> Beta User\n\t\t\t\t\t\t\t</label>\n\t\t\t\t\t\t\t<label class=\"form-switch\">\n\t\t\t\t\t\t\t\t<input type=\"checkbox\" />\n\t\t\t\t\t\t\t\t<i class=\"form-icon\"></i> Remote Check\n\t\t\t\t\t\t\t</label>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</li>\n\t\t\t\t</ul>\n\t\t\t</div>\n\n\t\t\t<component :is=\"page.view\"></component>\n\t\t</div>\n\t</div>\n";

/***/ }),
/* 133 */
/***/ (function(module, exports, __webpack_require__) {

var __vue_script__, __vue_template__
__vue_script__ = __webpack_require__(138)
__vue_template__ = __webpack_require__(143)
module.exports = __vue_script__ || {}
if (module.exports.__esModule) module.exports = module.exports.default
if (__vue_template__) { (typeof module.exports === "function" ? module.exports.options : module.exports).template = __vue_template__ }
if (false) {(function () {  module.hot.accept()
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), true)
  if (!hotAPI.compatible) return
  var id = "/var/www/html/wp-base/wp-content/plugins/tweet-old-post/vue/src/vue-elements/queue-tab-panel.vue"
  if (!module.hot.data) {
    hotAPI.createRecord(id, module.exports)
  } else {
    hotAPI.update(id, module.exports, __vue_template__)
  }
})()}

/***/ }),
/* 134 */,
/* 135 */,
/* 136 */,
/* 137 */,
/* 138 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _queueCard = __webpack_require__(140);

var _queueCard2 = _interopRequireDefault(_queueCard);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

module.exports = {
	name: 'queue-view',
	computed: {
		queue: function queue() {
			return this.$store.state.queue;
		}
	},
	methods: {},
	components: {
		QueueCard: _queueCard2.default
	}
	// </script>

}; // <template>
// 	<div class="tab-view">
// 		<div class="panel-body" style="overflow: inherit;">
// 			<h3>Sharing Queue</h3>
// 			<div class="container columns">
// 				<div class="column col-sm-12 col-3 text-left" v-for=" (data, index) in queue ">
//                     <queue-card :account_id="data.account_id" :post="data.post" :time="data.time" :key="index" :id="index" />
// 				</div>
// 			</div>
// 		</div>
// 		<div class="panel-footer">
// 			<button class="btn btn-primary" @click="saveSchedule()"><i class="fa fa-check"></i> Save Schedule</button>
// 			<button class="btn btn-secondary" @click="resetSchedule()"><i class="fa fa-ban"></i> Reset to Defaults</button>
// 		</div>
// 	</div>
// </template>
//
// <script>

/***/ }),
/* 139 */,
/* 140 */
/***/ (function(module, exports, __webpack_require__) {

var __vue_script__, __vue_template__
__webpack_require__(144)
__vue_script__ = __webpack_require__(141)
__vue_template__ = __webpack_require__(146)
module.exports = __vue_script__ || {}
if (module.exports.__esModule) module.exports = module.exports.default
if (__vue_template__) { (typeof module.exports === "function" ? module.exports.options : module.exports).template = __vue_template__ }
if (false) {(function () {  module.hot.accept()
  var hotAPI = require("vue-hot-reload-api")
  hotAPI.install(require("vue"), true)
  if (!hotAPI.compatible) return
  var id = "/var/www/html/wp-base/wp-content/plugins/tweet-old-post/vue/src/vue-elements/reusables/queue-card.vue"
  if (!module.hot.data) {
    hotAPI.createRecord(id, module.exports)
  } else {
    hotAPI.update(id, module.exports, __vue_template__)
  }
})()}

/***/ }),
/* 141 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


// <template>
// 	<div class="card col-12" style="max-width: 100%; min-height: 350px;">
// 		<div style="position: absolute; display: block; top: 0; right: 0;">
// 			<button class="btn btn-sm btn-primary" @click="toggleEditState" v-if="edit === false"><i class="fa fa-pencil" aria-hidden="true"></i> Edit</button>
// 			<button class="btn btn-sm btn-success" @click="saveChanges" v-if="edit"><i class="fa fa-check" aria-hidden="true"></i> Save</button>
// 			<button class="btn btn-sm btn-warning" @click="toggleEditState" v-if="edit"><i class="fa fa-times" aria-hidden="true"></i> Cancel</button>
// 		</div>
// 		<div class="card-header">
// 			<p class="text-gray text-right float-right"><b>Scheduled:</b><br/>{{time}}</p>
// 			<div class="card-title h6">{{post.post_title}}</div>
// 			<div class="card-subtitle text-gray"><i class="service fa" :class="iconClass( account_id )"></i> {{active_accounts[account_id].account}}</div>
// 		</div>
// 		<hr/>
// 		<span v-if="edit === false">
// 			<details class="accordion" v-if="post.post_img">
// 				<summary class="accordion-header">
// 					<i class="fa fa-file-image-o"></i>
// 					Image Preview
// 				</summary>
// 				<div class="accordion-body">
// 					<div class="card-image" v-if="post.post_img">
// 						<figure class="figure" style="max-height: 250px; overflow: hidden;">
// 							<img :src="post.post_img" class="img-fit-cover" style=" width: 100%; height: 250px;" @error="brokenImg">
// 						</figure>
// 					</div>
// 				</div>
// 			</details>
// 			<details class="accordion" v-else>
// 				<summary class="accordion-header">
// 					<i class="fa fa-file-image-o"></i>
// 					No Image
// 				</summary>
// 				<div class="accordion-body text-gray">
// 					<small>
// 						<i class="fa fa-chain-broken" aria-hidden="true"></i> No image attached or a broken link was detected.<br/>
// 						<i class="fa fa-info-circle" aria-hidden="true"></i> <i>If a image should be here, update the post or edit this item.</i>
// 					</small>
// 				</div>
// 			</details>
//
// 			<div class="card-body" v-if="edit === false">
// 				<p v-html="hashtags( post_content )"></p>
// 				<p v-if="post.post_url"><b>Link:</b> <a :href="post.post_url" target="_blank">{{post.post_url}}</a></p>
// 			</div>
// 		</span>
// 		<div class="card-body" v-else>
// 			<div class="form-group">
// 				<label class="form-label" for="image">Image</label>
// 				<div class="input-group">
// 					<span class="input-group-addon"><i class="fa fa-file-image-o"></i></span>
// 					<input id="image" type="text" class="form-input" :value="post_img_url" readonly>
// 					<button class="btn btn-primary input-group-btn" @click="uploadImage"><i class="fa fa-upload" aria-hidden="true"></i></button>
// 					<button class="btn btn-danger input-group-btn" @click="clearImage"><i class="fa fa-trash" aria-hidden="true"></i></button>
// 				</div>
//
// 				<label class="form-label" for="content">Content</label>
// 				<textarea class="form-input" id="content" placeholder="Textarea" rows="3" @keydown="checkCount">{{post_content}}</textarea>
// 			</div>
// 		</div>
// 		<div style="position: absolute; display: block; bottom: 0; right: 0;" v-if="edit === false">
// 			<button class="btn btn-sm btn-warning"><i class="fa fa-step-forward" aria-hidden="true"></i> Skip</button>
// 			<button class="btn btn-sm btn-danger"><i class="fa fa-ban" aria-hidden="true"></i> Block</button>
// 		</div>
// 	</div>
// </template>
//
// <script>
/* global wp */

module.exports = {
	name: 'queue-card',
	props: {
		id: {
			default: ''
		},
		account_id: {
			default: '',
			type: String
		},
		post: {
			default: function _default() {
				return {};
			},
			type: Object
		},
		time: {
			default: '',
			type: String
		}
	},
	data: function data() {
		return {
			edit: false,
			post_edit: this.post
		};
	},
	computed: {
		post_content: function post_content() {
			if (this.post.custom_content !== '') {
				return this.post.custom_content;
			}
			return this.post.post_content;
		},
		active_accounts: function active_accounts() {
			return this.$store.state.activeAccounts;
		},
		post_img_url: function post_img_url() {
			if (this.post_edit.post_img !== false) {
				return this.post_edit.post_img;
			}
			return '';
		}
	},
	watch: {},
	methods: {
		toggleEditState: function toggleEditState() {
			this.edit = !this.edit;
		},
		checkCount: function checkCount(evt) {
			console.log(evt);
		},
		saveChanges: function saveChanges() {},
		clearImage: function clearImage() {
			this.post_edit.post_img = false;
		},
		uploadImage: function uploadImage() {
			var window = wp.media({
				title: 'Insert a media',
				library: {
					type: 'image'
				},
				multiple: false,
				button: { text: 'Insert' }
			});

			var self = this;
			window.on('select', function () {
				var first = window.state().get('selection').first().toJSON();
				console.log(first);
				self.post_edit.post_img = first.url;
			});

			window.open();
		},
		iconClass: function iconClass(accountId) {
			var serviceIcon = 'fa-user';
			if (accountId !== null) {
				serviceIcon = 'fa-';
				var account = this.active_accounts[accountId];
				if (account.service === 'facebook') serviceIcon = serviceIcon.concat('facebook-official facebook');
				if (account.service === 'twitter') serviceIcon = serviceIcon.concat('twitter twitter');
				if (account.service === 'linkedin') serviceIcon = serviceIcon.concat('linkedin linkedin');
				if (account.service === 'tumblr') serviceIcon = serviceIcon.concat('tumblr tumblr');
			}
			return serviceIcon;
		},
		brokenImg: function brokenImg() {
			console.log('Image is broken');
			this.post.post_img = false;
		},
		hashtags: function hashtags(string) {
			var regex = '#\\S+';
			var check = new RegExp(regex, 'ig');
			return string.toString().replace(check, function (matchedText, a, b) {
				if (matchedText.slice(-1) === ',') {
					return '<strong>' + matchedText.substring(0, matchedText.lastIndexOf(',')) + '</strong>,';
				}
				return '<strong>' + matchedText + '</strong>';
			});
		}
	}
	// </script>
	//
	// <style scoped>
	// 	#rop_core .avatar .avatar-icon {
	// 		background: #333;
	// 		border-radius: 50%;
	// 		font-size: 16px;
	// 		text-align: center;
	// 		line-height: 20px;
	// 	}
	// 	#rop_core .avatar .avatar-icon.fa-facebook-official { background-color: #3b5998; }
	// 	#rop_core .avatar .avatar-icon.fa-twitter { background-color: #55acee; }
	// 	#rop_core .avatar .avatar-icon.fa-linkedin { background-color: #007bb5; }
	// 	#rop_core .avatar .avatar-icon.fa-tumblr { background-color: #32506d; }
	//
	// 	#rop_core .service.facebook {
	// 		color: #3b5998;
	// 	}
	//
	// 	#rop_core .service.twitter {
	// 		color: #55acee;
	// 	}
	//
	// 	#rop_core .service.linkedin {
	// 		color: #007bb5;
	// 	}
	//
	// 	#rop_core .service.tumblr {
	// 		color: #32506d;
	// 	}
	//
	// 	#rop_core .btn-warning {
	// 		background-color: #ef6c00;
	// 		border-color: #e65100;
	// 		color: #FFF;
	// 	}
	//
	// 	#rop_core .btn-warning:hover, #rop_core .btn-warning:focus {
	// 		border-color: #e65100;
	// 		background-color: #fff;
	// 		color: #ef6c00;
	// 	}
	//
	// 	#rop_core .btn-warning.active, #rop_core .btn-warning:active {
	// 		background-color: #e65100;
	// 		border-color: #ef6c00;
	// 	}
	//
	// 	#rop_core .btn-danger {
	// 		 background-color: #c62828;
	// 		 border-color: #b71c1c;
	// 		 color: #FFF;
	// 	 }
	//
	// 	#rop_core .btn-danger:hover, #rop_core .btn-danger:focus {
	// 		border-color: #b71c1c;
	// 		background-color: #fff;
	// 		color: #c62828;
	// 	}
	//
	// 	#rop_core .btn-danger.active, #rop_core .btn-danger:active {
	// 		background-color: #b71c1c;
	// 		border-color: #c62828;
	// 	}
	//
	// 	#rop_core .btn-success {
	// 		background-color: #8bc34a;
	// 		border-color: #33691e;
	// 		color: #FFF;
	// 	}
	//
	// 	#rop_core .btn-success:hover, #rop_core .btn-success:focus {
	// 		border-color: #33691e;
	// 		background-color: #fff;
	// 		color: #8bc34a;
	// 	}
	//
	// 	#rop_core .btn-success.active, #rop_core .btn-success:active {
	// 		background-color: #33691e;
	// 		border-color: #8bc34a;
	// 	}
	// </style>

};

/***/ }),
/* 142 */,
/* 143 */
/***/ (function(module, exports) {

module.exports = "\n\t<div class=\"tab-view\">\n\t\t<div class=\"panel-body\" style=\"overflow: inherit;\">\n\t\t\t<h3>Sharing Queue</h3>\n\t\t\t<div class=\"container columns\">\n\t\t\t\t<div class=\"column col-sm-12 col-3 text-left\" v-for=\" (data, index) in queue \">\n                    <queue-card :account_id=\"data.account_id\" :post=\"data.post\" :time=\"data.time\" :key=\"index\" :id=\"index\" />\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t</div>\n\t\t<div class=\"panel-footer\">\n\t\t\t<button class=\"btn btn-primary\" @click=\"saveSchedule()\"><i class=\"fa fa-check\"></i> Save Schedule</button>\n\t\t\t<button class=\"btn btn-secondary\" @click=\"resetSchedule()\"><i class=\"fa fa-ban\"></i> Reset to Defaults</button>\n\t\t</div>\n\t</div>\n";

/***/ }),
/* 144 */
/***/ (function(module, exports, __webpack_require__) {

// style-loader: Adds some css to the DOM by adding a <style> tag

// load the styles
var content = __webpack_require__(145);
if(typeof content === 'string') content = [[module.i, content, '']];
// add the styles to the DOM
var update = __webpack_require__(1)(content, {});
if(content.locals) module.exports = content.locals;
// Hot Module Replacement
if(false) {
	// When the styles change, update the <style> tags
	if(!content.locals) {
		module.hot.accept("!!../../../../node_modules/css-loader/index.js!../../../../node_modules/vue-loader/lib/style-rewriter.js?id=_v-2719575f&file=queue-card.vue&scoped=true!../../../../node_modules/vue-loader/lib/selector.js?type=style&index=0!../../../../node_modules/eslint-loader/index.js!../../../../node_modules/eslint-loader/index.js!./queue-card.vue", function() {
			var newContent = require("!!../../../../node_modules/css-loader/index.js!../../../../node_modules/vue-loader/lib/style-rewriter.js?id=_v-2719575f&file=queue-card.vue&scoped=true!../../../../node_modules/vue-loader/lib/selector.js?type=style&index=0!../../../../node_modules/eslint-loader/index.js!../../../../node_modules/eslint-loader/index.js!./queue-card.vue");
			if(typeof newContent === 'string') newContent = [[module.id, newContent, '']];
			update(newContent);
		});
	}
	// When the module is disposed, remove the <style> tags
	module.hot.dispose(function() { update(); });
}

/***/ }),
/* 145 */
/***/ (function(module, exports, __webpack_require__) {

exports = module.exports = __webpack_require__(0)();
// imports


// module
exports.push([module.i, "\n\t#rop_core .avatar .avatar-icon[_v-2719575f] {\n\t\tbackground: #333;\n\t\tborder-radius: 50%;\n\t\tfont-size: 16px;\n\t\ttext-align: center;\n\t\tline-height: 20px;\n\t}\n\t#rop_core .avatar .avatar-icon.fa-facebook-official[_v-2719575f] { background-color: #3b5998; }\n\t#rop_core .avatar .avatar-icon.fa-twitter[_v-2719575f] { background-color: #55acee; }\n\t#rop_core .avatar .avatar-icon.fa-linkedin[_v-2719575f] { background-color: #007bb5; }\n\t#rop_core .avatar .avatar-icon.fa-tumblr[_v-2719575f] { background-color: #32506d; }\n\n\t#rop_core .service.facebook[_v-2719575f] {\n\t\tcolor: #3b5998;\n\t}\n\n\t#rop_core .service.twitter[_v-2719575f] {\n\t\tcolor: #55acee;\n\t}\n\n\t#rop_core .service.linkedin[_v-2719575f] {\n\t\tcolor: #007bb5;\n\t}\n\n\t#rop_core .service.tumblr[_v-2719575f] {\n\t\tcolor: #32506d;\n\t}\n\n\t#rop_core .btn-warning[_v-2719575f] {\n\t\tbackground-color: #ef6c00;\n\t\tborder-color: #e65100;\n\t\tcolor: #FFF;\n\t}\n\n\t#rop_core .btn-warning[_v-2719575f]:hover, #rop_core .btn-warning[_v-2719575f]:focus {\n\t\tborder-color: #e65100;\n\t\tbackground-color: #fff;\n\t\tcolor: #ef6c00;\n\t}\n\n\t#rop_core .btn-warning.active[_v-2719575f], #rop_core .btn-warning[_v-2719575f]:active {\n\t\tbackground-color: #e65100;\n\t\tborder-color: #ef6c00;\n\t}\n\n\t#rop_core .btn-danger[_v-2719575f] {\n\t\t background-color: #c62828;\n\t\t border-color: #b71c1c;\n\t\t color: #FFF;\n\t }\n\n\t#rop_core .btn-danger[_v-2719575f]:hover, #rop_core .btn-danger[_v-2719575f]:focus {\n\t\tborder-color: #b71c1c;\n\t\tbackground-color: #fff;\n\t\tcolor: #c62828;\n\t}\n\n\t#rop_core .btn-danger.active[_v-2719575f], #rop_core .btn-danger[_v-2719575f]:active {\n\t\tbackground-color: #b71c1c;\n\t\tborder-color: #c62828;\n\t}\n\n\t#rop_core .btn-success[_v-2719575f] {\n\t\tbackground-color: #8bc34a;\n\t\tborder-color: #33691e;\n\t\tcolor: #FFF;\n\t}\n\n\t#rop_core .btn-success[_v-2719575f]:hover, #rop_core .btn-success[_v-2719575f]:focus {\n\t\tborder-color: #33691e;\n\t\tbackground-color: #fff;\n\t\tcolor: #8bc34a;\n\t}\n\n\t#rop_core .btn-success.active[_v-2719575f], #rop_core .btn-success[_v-2719575f]:active {\n\t\tbackground-color: #33691e;\n\t\tborder-color: #8bc34a;\n\t}\n", ""]);

// exports


/***/ }),
/* 146 */
/***/ (function(module, exports) {

module.exports = "\n\t<div class=\"card col-12\" style=\"max-width: 100%; min-height: 350px;\" _v-2719575f=\"\">\n\t\t<div style=\"position: absolute; display: block; top: 0; right: 0;\" _v-2719575f=\"\">\n\t\t\t<button class=\"btn btn-sm btn-primary\" @click=\"toggleEditState\" v-if=\"edit === false\" _v-2719575f=\"\"><i class=\"fa fa-pencil\" aria-hidden=\"true\" _v-2719575f=\"\"></i> Edit</button>\n\t\t\t<button class=\"btn btn-sm btn-success\" @click=\"saveChanges\" v-if=\"edit\" _v-2719575f=\"\"><i class=\"fa fa-check\" aria-hidden=\"true\" _v-2719575f=\"\"></i> Save</button>\n\t\t\t<button class=\"btn btn-sm btn-warning\" @click=\"toggleEditState\" v-if=\"edit\" _v-2719575f=\"\"><i class=\"fa fa-times\" aria-hidden=\"true\" _v-2719575f=\"\"></i> Cancel</button>\n\t\t</div>\n\t\t<div class=\"card-header\" _v-2719575f=\"\">\n\t\t\t<p class=\"text-gray text-right float-right\" _v-2719575f=\"\"><b _v-2719575f=\"\">Scheduled:</b><br _v-2719575f=\"\">{{time}}</p>\n\t\t\t<div class=\"card-title h6\" _v-2719575f=\"\">{{post.post_title}}</div>\n\t\t\t<div class=\"card-subtitle text-gray\" _v-2719575f=\"\"><i class=\"service fa\" :class=\"iconClass( account_id )\" _v-2719575f=\"\"></i> {{active_accounts[account_id].account}}</div>\n\t\t</div>\n\t\t<hr _v-2719575f=\"\">\n\t\t<span v-if=\"edit === false\" _v-2719575f=\"\">\n\t\t\t<details class=\"accordion\" v-if=\"post.post_img\" _v-2719575f=\"\">\n\t\t\t\t<summary class=\"accordion-header\" _v-2719575f=\"\">\n\t\t\t\t\t<i class=\"fa fa-file-image-o\" _v-2719575f=\"\"></i>\n\t\t\t\t\tImage Preview\n\t\t\t\t</summary>\n\t\t\t\t<div class=\"accordion-body\" _v-2719575f=\"\">\n\t\t\t\t\t<div class=\"card-image\" v-if=\"post.post_img\" _v-2719575f=\"\">\n\t\t\t\t\t\t<figure class=\"figure\" style=\"max-height: 250px; overflow: hidden;\" _v-2719575f=\"\">\n\t\t\t\t\t\t\t<img :src=\"post.post_img\" class=\"img-fit-cover\" style=\" width: 100%; height: 250px;\" @error=\"brokenImg\" _v-2719575f=\"\">\n\t\t\t\t\t\t</figure>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t</details>\n\t\t\t<details class=\"accordion\" v-else=\"\" _v-2719575f=\"\">\n\t\t\t\t<summary class=\"accordion-header\" _v-2719575f=\"\">\n\t\t\t\t\t<i class=\"fa fa-file-image-o\" _v-2719575f=\"\"></i>\n\t\t\t\t\tNo Image\n\t\t\t\t</summary>\n\t\t\t\t<div class=\"accordion-body text-gray\" _v-2719575f=\"\">\n\t\t\t\t\t<small _v-2719575f=\"\">\n\t\t\t\t\t\t<i class=\"fa fa-chain-broken\" aria-hidden=\"true\" _v-2719575f=\"\"></i> No image attached or a broken link was detected.<br _v-2719575f=\"\">\n\t\t\t\t\t\t<i class=\"fa fa-info-circle\" aria-hidden=\"true\" _v-2719575f=\"\"></i> <i _v-2719575f=\"\">If a image should be here, update the post or edit this item.</i>\n\t\t\t\t\t</small>\n\t\t\t\t</div>\n\t\t\t</details>\n\n\t\t\t<div class=\"card-body\" v-if=\"edit === false\" _v-2719575f=\"\">\n\t\t\t\t<p v-html=\"hashtags( post_content )\" _v-2719575f=\"\"></p>\n\t\t\t\t<p v-if=\"post.post_url\" _v-2719575f=\"\"><b _v-2719575f=\"\">Link:</b> <a :href=\"post.post_url\" target=\"_blank\" _v-2719575f=\"\">{{post.post_url}}</a></p>\n\t\t\t</div>\n\t\t</span>\n\t\t<div class=\"card-body\" v-else=\"\" _v-2719575f=\"\">\n\t\t\t<div class=\"form-group\" _v-2719575f=\"\">\n\t\t\t\t<label class=\"form-label\" for=\"image\" _v-2719575f=\"\">Image</label>\n\t\t\t\t<div class=\"input-group\" _v-2719575f=\"\">\n\t\t\t\t\t<span class=\"input-group-addon\" _v-2719575f=\"\"><i class=\"fa fa-file-image-o\" _v-2719575f=\"\"></i></span>\n\t\t\t\t\t<input id=\"image\" type=\"text\" class=\"form-input\" :value=\"post_img_url\" readonly=\"\" _v-2719575f=\"\">\n\t\t\t\t\t<button class=\"btn btn-primary input-group-btn\" @click=\"uploadImage\" _v-2719575f=\"\"><i class=\"fa fa-upload\" aria-hidden=\"true\" _v-2719575f=\"\"></i></button>\n\t\t\t\t\t<button class=\"btn btn-danger input-group-btn\" @click=\"clearImage\" _v-2719575f=\"\"><i class=\"fa fa-trash\" aria-hidden=\"true\" _v-2719575f=\"\"></i></button>\n\t\t\t\t</div>\n\n\t\t\t\t<label class=\"form-label\" for=\"content\" _v-2719575f=\"\">Content</label>\n\t\t\t\t<textarea class=\"form-input\" id=\"content\" placeholder=\"Textarea\" rows=\"3\" @keydown=\"checkCount\" _v-2719575f=\"\">{{post_content}}</textarea>\n\t\t\t</div>\n\t\t</div>\n\t\t<div style=\"position: absolute; display: block; bottom: 0; right: 0;\" v-if=\"edit === false\" _v-2719575f=\"\">\n\t\t\t<button class=\"btn btn-sm btn-warning\" _v-2719575f=\"\"><i class=\"fa fa-step-forward\" aria-hidden=\"true\" _v-2719575f=\"\"></i> Skip</button>\n\t\t\t<button class=\"btn btn-sm btn-danger\" _v-2719575f=\"\"><i class=\"fa fa-ban\" aria-hidden=\"true\" _v-2719575f=\"\"></i> Block</button>\n\t\t</div>\n\t</div>\n";

/***/ })
/******/ ]);