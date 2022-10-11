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
/******/ 			Object.defineProperty(exports, name, { enumerable: true, get: getter });
/******/ 		}
/******/ 	};
/******/
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = function(exports) {
/******/ 		if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 			Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 		}
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/
/******/ 	// create a fake namespace object
/******/ 	// mode & 1: value is a module id, require it
/******/ 	// mode & 2: merge all properties of value into the ns
/******/ 	// mode & 4: return value when already ns object
/******/ 	// mode & 8|1: behave like require
/******/ 	__webpack_require__.t = function(value, mode) {
/******/ 		if(mode & 1) value = __webpack_require__(value);
/******/ 		if(mode & 8) return value;
/******/ 		if((mode & 4) && typeof value === 'object' && value && value.__esModule) return value;
/******/ 		var ns = Object.create(null);
/******/ 		__webpack_require__.r(ns);
/******/ 		Object.defineProperty(ns, 'default', { enumerable: true, value: value });
/******/ 		if(mode & 2 && typeof value != 'string') for(var key in value) __webpack_require__.d(ns, key, function(key) { return value[key]; }.bind(null, key));
/******/ 		return ns;
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
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = "./src/index.js");
/******/ })
/************************************************************************/
/******/ ({

/***/ "./src/index.js":
/*!**********************!*\
  !*** ./src/index.js ***!
  \**********************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

  "use strict";
  __webpack_require__.r(__webpack_exports__);
  /* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
  /* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
  /* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/i18n */ "@wordpress/i18n");
  /* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__);
  /* harmony import */ var _wordpress_blocks__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/blocks */ "@wordpress/blocks");
  /* harmony import */ var _wordpress_blocks__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_blocks__WEBPACK_IMPORTED_MODULE_2__);
  /* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @wordpress/block-editor */ "@wordpress/block-editor");
  /* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_3__);
  /* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
  /* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__);
  
  
  
  
  
  Object(_wordpress_blocks__WEBPACK_IMPORTED_MODULE_2__["registerBlockType"])("yay-currency/currency-switcher", {
    title: Object(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__["__"])("Currency Switcher - YayCurrency", "yay-currency"),
    icon: "index-card",
    category: "widgets",
    attributes: {
      currencyName: {
        type: "string",
        default: "United States dollar"
      },
      currencySymbol: {
        type: "string",
        default: "($)"
      },
      hyphen: {
        type: "string",
        default: " - "
      },
      currencyCode: {
        type: "string",
        default: "USD"
      },
      isShowFlag: {
        type: "boolean",
        default: true
      },
      isShowCurrencyName: {
        type: "boolean",
        default: true
      },
      isShowCurrencySymbol: {
        type: "boolean",
        default: true
      },
      isShowCurrencyCode: {
        type: "boolean",
        default: true
      },
      widgetSize: {
        type: "string",
        default: "small"
      }
    },
    edit: function edit(props) {
      var _props$attributes = props.attributes,
          currencyName = _props$attributes.currencyName,
          currencySymbol = _props$attributes.currencySymbol,
          hyphen = _props$attributes.hyphen,
          currencyCode = _props$attributes.currencyCode,
          isShowFlag = _props$attributes.isShowFlag,
          isShowCurrencyName = _props$attributes.isShowCurrencyName,
          isShowCurrencySymbol = _props$attributes.isShowCurrencySymbol,
          isShowCurrencyCode = _props$attributes.isShowCurrencyCode,
          widgetSize = _props$attributes.widgetSize,
          setAttributes = props.setAttributes;
      var _yayCurrencyGutenberg = yayCurrencyGutenberg,
          yayCurrencyPluginURL = _yayCurrencyGutenberg.yayCurrencyPluginURL;
  
      var renderSwitcherPreview = function renderSwitcherPreview() {
        isShowCurrencyName ? setAttributes({
          currencyName: "United States dollar"
        }) : setAttributes({
          currencyName: ""
        });
        isShowCurrencySymbol ? isShowCurrencyName ? setAttributes({
          currencySymbol: "($)"
        }) : setAttributes({
          currencySymbol: "$ "
        }) : setAttributes({
          currencySymbol: ""
        });
        isShowCurrencyCode ? setAttributes({
          currencyCode: "USD"
        }) : setAttributes({
          currencyCode: ""
        });
        isShowCurrencyName && isShowCurrencyCode ? setAttributes({
          hyphen: " - "
        }) : setAttributes({
          hyphen: ""
        });
        var result = "".concat(currencyName, " ").concat(currencySymbol).concat(hyphen).concat(currencyCode);
        return result;
      };
  
      var renderFlag = function renderFlag() {
        return Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])("span", {
          class: "yay-currency-flag selected ".concat(widgetSize),
          style: {
            backgroundImage: "url(".concat(yayCurrencyPluginURL, "assets/dist/flags/us.svg)"),
            backgroundSize: "cover",
            backgroundRepeat: "no-repeat"
          }
        });
      };
  
      var handleChangeIsShowFlag = function handleChangeIsShowFlag(isChecked) {
        setAttributes({
          isShowFlag: isChecked
        });
      };
  
      var handleChangeIsShowCurrencyName = function handleChangeIsShowCurrencyName(isChecked) {
        setAttributes({
          isShowCurrencyName: isChecked
        });
      };
  
      var handleChangeIsShowCurrencySymbol = function handleChangeIsShowCurrencySymbol(isChecked) {
        setAttributes({
          isShowCurrencySymbol: isChecked
        });
      };
  
      var handleChangeIsShowCurrencyCode = function handleChangeIsShowCurrencyCode(isChecked) {
        setAttributes({
          isShowCurrencyCode: isChecked
        });
      };
  
      var handleChangeWidgetSize = function handleChangeWidgetSize(size) {
        setAttributes({
          widgetSize: size
        });
      };
  
      var countDispalyElementsInWidget = function countDispalyElementsInWidget() {
        var elementKeys = [isShowFlag, isShowCurrencyName, isShowCurrencySymbol, isShowCurrencyCode];
        var displayElementsArray = [];
        elementKeys.forEach(function (element) {
          element && displayElementsArray.push(element);
        });
        return displayElementsArray.length;
      };
  
      return [Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_3__["InspectorControls"], null, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__["PanelBody"], {
        title: Object(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__["__"])("Switcher elements", "yay-currency")
      }, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__["CheckboxControl"], {
        label: "Show flag",
        checked: isShowFlag,
        onChange: handleChangeIsShowFlag
      }), Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__["CheckboxControl"], {
        label: "Show currency name",
        checked: isShowCurrencyName,
        onChange: handleChangeIsShowCurrencyName
      }), Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__["CheckboxControl"], {
        label: "Show currency symbol",
        checked: isShowCurrencySymbol,
        onChange: handleChangeIsShowCurrencySymbol
      }), Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__["CheckboxControl"], {
        label: "Show currency code",
        checked: isShowCurrencyCode,
        onChange: handleChangeIsShowCurrencyCode
      })), Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__["PanelBody"], {
        title: Object(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__["__"])("Switcher size", "yay-currency")
      }, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__["PanelRow"], null, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(_wordpress_components__WEBPACK_IMPORTED_MODULE_4__["RadioControl"], {
        selected: widgetSize,
        options: [{
          label: "Small",
          value: "small"
        }, {
          label: "Medium",
          value: "medium"
        }],
        onChange: handleChangeWidgetSize
      })))), Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])("div", {
        className: "yay-currency-custom-select-wrapper ".concat(widgetSize, " ").concat(!isShowCurrencyName && "no-currency-name", " ").concat(isShowCurrencyName && !isShowFlag && !isShowCurrencySymbol && !isShowCurrencyCode && "only-currency-name", "\n          ").concat(isShowCurrencyName && countDispalyElementsInWidget() === 2 && "only-currency-name-and-something", "\n          ")
      }, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])("div", {
        className: "yay-currency-custom-select"
      }, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])("div", {
        className: "yay-currency-custom-select__trigger ".concat(widgetSize)
      }, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])("div", {
        className: "yay-currency-custom-selected-option"
      }, isShowFlag && renderFlag(), Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])("span", {
        className: "yay-currency-selected-option"
      }, renderSwitcherPreview())), Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])("div", {
        className: "yay-currency-custom-arrow"
      }))))];
    },
    save: function save() {
      return null;
    }
  });
  
  /***/ }),
  
  /***/ "@wordpress/block-editor":
  /*!*************************************!*\
    !*** external ["wp","blockEditor"] ***!
    \*************************************/
  /*! no static exports found */
  /***/ (function(module, exports) {
  
  (function() { module.exports = window["wp"]["blockEditor"]; }());
  
  /***/ }),
  
  /***/ "@wordpress/blocks":
  /*!********************************!*\
    !*** external ["wp","blocks"] ***!
    \********************************/
  /*! no static exports found */
  /***/ (function(module, exports) {
  
  (function() { module.exports = window["wp"]["blocks"]; }());
  
  /***/ }),
  
  /***/ "@wordpress/components":
  /*!************************************!*\
    !*** external ["wp","components"] ***!
    \************************************/
  /*! no static exports found */
  /***/ (function(module, exports) {
  
  (function() { module.exports = window["wp"]["components"]; }());
  
  /***/ }),
  
  /***/ "@wordpress/element":
  /*!*********************************!*\
    !*** external ["wp","element"] ***!
    \*********************************/
  /*! no static exports found */
  /***/ (function(module, exports) {
  
  (function() { module.exports = window["wp"]["element"]; }());
  
  /***/ }),
  
  /***/ "@wordpress/i18n":
  /*!******************************!*\
    !*** external ["wp","i18n"] ***!
    \******************************/
  /*! no static exports found */
  /***/ (function(module, exports) {
  
  (function() { module.exports = window["wp"]["i18n"]; }());
  
  /***/ })
  
  /******/ });
  //# sourceMappingURL=index.js.map
  