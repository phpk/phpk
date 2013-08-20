(function () {
	/**
	 * xpjs
	 * @time 2013/01/07
	 * @author liuruitao txpxbbs@163.com
	 *
	 */
	var ua = window.navigator.userAgent.toLowerCase(),
	toStr = Object.prototype.toString,
	has = Object.prototype.hasOwnProperty,
	doc = window.document,
	class2type = {
		"[object HTMLDocument]" : "Document",
		"[object HTMLCollection]" : "NodeList",
		"[object StaticNodeList]" : "NodeList",
		"[object IXMLDOMNodeList]" : "NodeList",
		"[object DOMWindow]" : "Window",
		"[object global]" : "Window",
		"null" : "Null",
		"NaN" : "NaN",
		"undefined" : "Undefined"
	},
	toString = class2type.toString;
	/**
	 *  js兼容补丁
	 *  @time  2012/11/22
	 */
	if (!Array.isArray) {
		Array.isArray = function isArray(obj) {
			return Object.prototype.toString(obj) == "[object Array]";
		};
	}
	if (!Array.prototype.indexOf) {
		/*
		 * ECMAScript 5 15.4.4.14
		 * 查找某数组元素在数组中的索引，不包含则返回-1
		 * @param { Anything } 数组元素
		 * @param { Number } 查找的起始索引，负数则是数组末尾的偏移量( -2 => len - 2 )
		 * @return { String } 索引值
		 */
		Array.prototype.indexOf = function (item, i) {
			var len = this.length;
			i = parseInt(i) || 0;
			if (i < 0) {
				i += len;
			}

			for (; i < len; i++) {
				if (this[i] === item) {
					return i;
				}
			}

			return -1;
		};
	}
	if (!Array.prototype.max) {
		Array.prototype.max = function () { //最大值
			return Math.max.apply({}, this)
		}
	}
	if (!Array.prototype.min) {
		Array.prototype.min = function () { //最小值
			return Math.min.apply({}, this)
		}
	}

	if (!Array.prototype.forEach) {
		/*
		 * ECMAScript 5 15.4.4.18
		 * 遍历数组并执行回调
		 * @param { Function } 回调函数( argument : 数组元素, 数组索引, 数组 )
		 * @param { Object } this的指向对象，默认为window
		 */
		Array.prototype.forEach = function (fn, context) {
			var len = this.length,
			i = 0;
			for (; i < len; i++) {
				fn.call(context, this[i], i, this);
			}
		};
	}

	if (!Date.now) {
		Date.now = function () {
			return +new Date;
		};

		Date.prototype.getYear = function () {
			return this.getFullYear() - 1900;
		};

		Date.prototype.setYear = function (year) {
			return this.setFullYear(year);
		};
	}

	if (!Object.create) {
		Object.create = function create(o) {
			if (!o) {
				return null;
			}
			var F = function () {};
			F.prototype = o;
			var result = new F();
			F.prototype = null;
			return result;
		}
	}
	if (!String.prototype.trim) {
		// 字符串首尾去空格
		String.prototype.trim = function () {
			// http://perfectionkills.com/whitespace-deviations/
			var whiteSpaces = ['\\s', '00A0', '1680', '180E', '2000-\\u200A', '200B', '2028', '2029', '202F', '205F', '3000'].join('\\u'),
			trimLeftReg = new RegExp('^[' + whiteSpaces + ']'),
			trimRightReg = new RegExp('[' + whiteSpaces + ']$');
			return this.replace(trimLeftReg, '').replace(trimRightReg, '');
		};
	}
	
	xp = {
		/*
		 * 简单的通过id获取dom
		 */
		g : function (id) {
			if (!id) {
				return null;
			}
			return typeof id === "string" ? document.getElementById(id) : (id.nodeType ? id : null);
		},
		t : function (tag, el) {
			var doc = el || document;
			return xp.makeArray(doc.getElementsByTagName(tag));
		},
		/*
		 * 简单的获取event的目标
		 */
		e : function (event) {
			var event = event ? event : window.event;
			return event.target || event.srcElement;
		},
		/**
		 * 首字母大写转换
		 *
		 * @param {
		 *            String } 要转换的字符串
		 * @return { String } 转换后的字符串 top => Top
		 */
		s : function (str) {
			var firstStr = str.charAt(0);
			return firstStr.toUpperCase() + str.replace(firstStr, '');
		},
		/*
		 * 大写简写
		 */
		d : function (str) {
			return str.toUpperCase();
		},
		/**
		 * 根据 attribute 获取元素
		 * @param {String} attr 元素的 attribute 标签名
		 * @param {Element} el 元素所属的文档对象，默认为当前文档
		 * @return {Element} 返回元素
		 */
		attrName : function (attr, el) {
			var el = el || document;
			if (el.querySelectorAll) {
				return el.querySelectorAll("[" + attr + "]");
			} else {
				var doms = el.getElementsByTagName("*"),
				len = doms.length,
				domCache = [],
				i = 0;
				for (; i < len; i++) {
					doms[i].getAttribute(attr) && domCache.push(doms[i]);
				}
				return domCache;
			}
		},
		/**
		 * 解析xml
		 */
		parseXML : function (data) {
			var xml,
			tmp;
			try {
				// 标准浏览器
				if (window.DOMParser) {
					tmp = new DOMParser();
					xml = tmp.parseFromString(data, 'text/xml');
				}
				// IE6/7/8
				else {
					xml = new ActiveXObject('Microsoft.XMLDOM');
					xml.async = 'false';
					xml.loadXML(data);
				}
			} catch (e) {
				xml = undefined;
			}

			return xml;
		},
		/**
		 * 解析json
		 */
		parseJSON : function (data) {
			if (!data || typeof data !== "string") {
				return null;
			}
			var data = data.trim();
			// 标准浏览器可以直接使用原生的方法
			if (window.JSON && window.JSON.parse) {
				try {
					return window.JSON.parse(data);
				} catch (_) {}
			}
			var rValidtokens = /"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g,
			rValidescape = /\\(?:["\\\/bfnrt]|u[0-9a-fA-F]{4})/g,
			rValidbraces = /(?:^|:|,)(?:\s*\[)+/g,
			rValidchars = /^[\],:{}\s]*$/;
			if (rValidchars.test(data.replace(rValidescape, "@").replace(rValidtokens, "]").replace(rValidbraces, ""))) {
				try {
					return (new Function("return " + data))();
				} catch (_) {}

			}
			return null;
		},
		/**
		 * 获取元素所有的属性
		 * @pravite
		 * @param {HTMLElement} el
		 * @return {Object}
		 */
		getAttrs : function (el) {
			var el = xp.g(el),
			name = el.tagName,
			rt = {};
			//console.log(el);
			if (name) {
				var name = name.toLowerCase(),
				r = /\<(\s|\S)*?\>/,
				str = el.outerHTML,
				s = r.exec(str)[0].replace("<" + name, "").replace(">", "").split(" "),
				l = s.length,
				i = 0;

				for (; i < l; i++) {
					if (s[i] && s[i].indexOf("=") > -1) {
						var t = s[i].split("=");
						if (t[1]) {
							//格式化参数名
							var n = t[0].toLowerCase();
							if (n.indexOf("-") > -1) {
								n = this._camelCssName(n);
							}
							rt[n] = t[1].replace(/"/g, '');
							//属性中含有多个等号的情况
							if (t.length > 2) {
								t.shift();
								//t.shift();
								rt[n] = t.join("=").replace(/"/g, '');
							}
						}

					}
				}
				rt["el"] = el;
				return rt;
			}
			return null;
		},
		_filterType : function (el, dtype, ntype) {
			ntype = ntype || 1;
			while (el && el.nodeType !== ntype) {
				el = el[dtype];
			}
			return el;
		},
		/**
		 * 获取当前对象的首个子节点
		 * @param {Element} element Dom元素
		 * @return {Element}
		 */
		first : function (element) {
			var el = element.firstChild;
			return this._filterType(el, "nextSibling");
		},
		/**
		 * 获取当前对象的最后一个子节点
		 * @param {Element} element Dom元素
		 * @return {Element}
		 */
		last : function (element) {
			var el = element.lastChild;
			return this._filterType(el, "previousSibling");
		},
		/**
		 * 获取当前对象的下一个兄弟节点
		 * @param {Element} element Dom元素
		 * @return {Element}
		 */
		next : function (element) {
			var el = element.nextSibling;
			return this._filterType(el, "nextSibling");
		},
		/**
		 * 获取当前对象的上一个兄弟节点，如果不存在则取最后一个
		 * @param {Element} element Dom元素
		 * @return {Element}
		 */
		prev : function (element) {
			var el = element.previousSibling;
			return this._filterType(el, "previousSibling");
		},
		/**
		 * 将方法绑定到指定作用域执行
		 */
		proxy : function (context, handle) {
			return function () {
				return handle.apply(context, arguments);
			};
		},
		/**
		 * 处理伪数组
		 * @param {Array} arr 要处理的数组
		 * @return {Array} results 处理完的结果
		 */
		makeArray : function (arr, results) {
			var type,
			ret = results || [];
			if (arr != null) {
				type = xp.type(arr);
				if (arr.length == null || type === "string" || type === "function" || type === "regexp" || xp.isWindow(arr)) {
					ret.push(arr);
				} else {
					xp.merge(ret, arr);
				}
			}
			return ret;
		},
		/**
		 * 合并对象或者数组
		 * @param {Object} first
		 * @param {Object} second
		 */
		merge : function (first, second) {
			var l = second.length,
			i = first.length,
			j = 0;
			if (typeof l === "number") {
				for (; j < l; j++) {
					first[i++] = second[j];
				}
			} else {
				while (second[j] !== undefined) {
					first[i++] = second[j++];
				}
			}
			first.length = i;
			return first;
		},
		/**
		 * 注册事件
		 */
		addEvent : document.addEventListener ? function (target, eventType, handle) {
			target.addEventListener(eventType, handle, false);
		}
		 : function (target, eventType, handle) {
			target.attachEvent("on" + eventType, handle);
		},
		/**
		 * 注册事件,支持更改作用域
		 */
		on : function (target, etype, handle, context) {
			if (context) {
				handle = xp.proxy(context, handle);
			}
			this.addEvent(target, etype, handle);
		},
		/**
		 * 删除事件
		 */
		rmEvent : document.removeEventListener ? function (target, eventType, handle) {
			target.removeEventListener(eventType, handle, false);
		}
		 : function (target, eventType) {
			target.detachEvent("on" + eventType, handle);
		},
		/**
		 * 判断函数
		 */
		isUndefined : function (obj) {
			return typeof(obj) === "undefined";
		},
		isNull : function (obj) {
			return obj === null;
		},
		isBoolean : function (obj) {
			return (obj === false || obj) && (obj.constructor === Boolean);
		},
		isFunction : function (obj) {
			return !!(obj && obj.constructor && obj.call);
		},
		isArgument : function (obj) {
			return obj && obj.callee && this.isNumber(o.length) ? true : false;
		},
		isString : function (obj) {
			return !!(obj === '' || (obj && obj.charCodeAt && obj.substr));
		},
		isNumber : function (obj) {
			return toStr.call(obj) === '[object Number]' && isFinite(obj);
		},
		isNumeric : function (obj) {
			return !isNaN(parseFloat(obj)) && isFinite(obj);
		},
		isArray : [].isArray ||
		function (obj) {
			return toStr.call(obj) === '[object Array]';
		},
		isWindow : function (obj) {
			return obj != null && obj == obj.window;
		},
		isObject : function (obj) {
			return obj == null ? String(obj) == 'object' : toStr.call(obj) === '[object Object]' || true;
		},
		isDate : function (o) {
			return (null != o) && !isNaN(o) && ("undefined" !== typeof o.getDate);
		},
		isNode : function (obj) {
			return !!(obj && obj.nodeType);
		},
		isElement : function (value) {
			return value ? value.nodeType === 1 : false;
		},
		isTextNode : function (value) {
			return value ? value.nodeName === "#text" : false;
		},
		isNodeList : function (obj) {
			return !!(obj && (obj.toString() == '[object NodeList]' || obj.toString() == '[object HTMLCollection]' || (obj.length && this.isNode(obj[0]))));
		},
		isEmpty : function (obj) {
			return typeof(obj) == "undefined" || obj == null || (!this.isNode(obj) && this.isArray(obj) && obj.length == 0 || (this.isString(obj) && obj == ""));
		},
		isPlainObject : function (obj) {
			if (!obj || xp.type(obj) !== "object" || obj.nodeType || xp.isWindow(obj)) {
				return false;
			}
			try {
				if (obj.constructor && !has.call(obj, "constructor") && !has.call(obj.constructor.prototype, "isPrototypeOf")) {
					return false;
				}
			} catch (e) {
				return false;
			}
			var key;
			for (key in obj) {}
			return key === undefined || has.call(obj, key);
		},
		isEmptyObject : function (obj) {
			for (var name in obj) {
				return false;
			}
			return true;
		},
		/**
		 * 用于取得数据的类型（一个参数的情况下）或判定数据的类型（两个参数的情况下）
		 * @param {Any} obj 要检测的东西
		 * @param {String} str 可选，要比较的类型
		 * @return {String|Boolean}
		 */
		type : function (obj, str) {
			var result = class2type[(obj == null || obj !== obj) ? obj : toString.call(obj)] || obj.nodeName || "#";
			if (result.charAt(0) === "#") { //兼容旧式浏览器与处理个别情况,如window.opera
				//利用IE678 window == document为true,document == window竟然为false的神奇特性
				if (obj == obj.document && obj.document != obj) {
					result = 'Window';
					//返回构造器名字
				} else if (obj.nodeType === 9) {
					result = 'Document';
					//返回构造器名字
				} else if (obj.callee) {
					result = 'Arguments';
					//返回构造器名字
				} else if (isFinite(obj.length) && obj.item) {
					result = 'NodeList';
					//处理节点集合
				} else {
					result = toString.call(obj).slice(8, -1);
				}
			}
			if (str) {
				return str === result;
			}
			return result;
		},
		ie : /msie(\d+\.\d+)/i.test(ua) ? (doc.documentMode || (+RegExp['\x241'])) : undefined,
		firefox : /firefox\/(\d+\.\d+)/i.test(ua) ? (+RegExp['\x241']) : undefined,
		chrome : /chrome\/(\d+\.\d+)/i.test(ua) ? (+RegExp['\x241']) : undefined,
		opera : /opera(\/|)(\d+(\.\d+)?)(.+?(version\/(\d+(\.\d+)?)))?/i.test(ua) ? ( + (RegExp["\x246"] || RegExp["\x242"])) : undefined,
		safari : (/(\d+\.\d)?(?:\.\d)?\s+safari\/?(\d+\.\d+)?/i.test(ua) && !/chrome/i.test(ua)) ? ( + (RegExp['\x241'] || RegExp['\x242'])) : undefined,
		isGecko : /gecko/i.test(ua) && !/like gecko/i.test(ua),
		isStrict : document.compatMode == "CSS1Compat",
		isWebkit : /webkit/i.test(ua),
		noop : function () {}
	};
	xp.extend = function () {
		var options,
		name,
		src,
		copy,
		copyIsArray,
		clone,
		target = arguments[0] || {},
		i = 1,
		length = arguments.length,
		deep = false;
		// 如果第一个参数是boolean型，可能是深度拷贝
		if (typeof target === "boolean") {
			deep = target;
			target = arguments[1] || {};
			// 跳过boolean和target，从第3个开始
			i = 2;
		}
		// target不是对象也不是函数，则强制设置为空对象
		if (typeof target !== "object" && !xp.isFunction(target)) {
			target = {};
		}
		// 如果只传入一个参数，则认为是对xp扩展
		if (length === i) {
			target = this;
			--i;
		}
		for (; i < length; i++) {
			// 只处理非空参数
			if ((options = arguments[i]) != null) {
				for (name in options) {
					src = target[name];
					copy = options[name];
					// 避免循环引用
					if (target === copy) {
						continue;
					}
					// 深度拷贝且值是纯对象或数组，则递归
					if (deep && copy && (xp.isPlainObject(copy) || (copyIsArray = xp.isArray(copy)))) {
						// 如果copy是数组
						if (copyIsArray) {
							copyIsArray = false;
							clone = src && xp.isArray(src) ? src : [];
						} else {
							// 如果copy的是对象
							clone = src && xp.isPlainObject(src) ? src : {};
						}
						// 递归调用copy
						target[name] = xp.extend(deep, clone, copy);
					} else if (copy !== undefined) {
						// 不能拷贝空值
						target[name] = copy;
					}
				}
			}
		}
		return target;
	};
	/**
	 * 遍历处理每一个数组元素或对象属性
	 * @param {Object|Array} object 遍历对象
	 * @param {Fcuntion} callback 自定义函数
	 * @param {String|Array} args 参数
	 * @return {Object|Array} object 处理完的对象
	 */
	xp.each = function (obj, callback, args) {
		var name,
		i = 0,
		length = obj.length,
		isObj = length === undefined || (obj.constructor && obj.call);
		if (args) {
			if (isObj) {
				for (name in obj) {
					if (callback.apply(obj[name], args) === false) {
						break;
					}
				}
			} else {
				for (; i < length; ) {
					if (callback.apply(obj[i++], args) === false) {
						break;
					}
				}
			}
		} else {
			if (isObj) {
				for (name in obj) {
					if (callback.call(obj[name], name, obj[name]) === false) {
						break;
					}
				}
			} else {
				for (; i < length; ) {
					if (callback.call(obj[i], i, obj[i++]) === false) {
						break;
					}
				}
			}
		}
		return obj;
	};

	xp.each("android ipad iphone linux macintosh windows x11".split(" "), function (i, item) {
		xp["is" + item] = ua.indexOf(item) > -1;

	});
	
	/**
	 * 懒加载
	 */
	xp.ready = (function (document) {
		var isReady,
		readyList,
		DOMContentLoaded;
		function domReady(fn) {
			bindReady();
			readyList.add(fn);
		}

		function bindReady() {
			if (readyList) {
				return;
			}
			readyList = Callbacks();
			if (document.readyState === "complete") {
				return setTimeout(ready, 1);
			}
			if (document.addEventListener) {
				document.addEventListener("DOMContentLoaded", DOMContentLoaded, false);
				window.addEventListener("load", ready, false);
			} else if (document.attachEvent) {
				document.attachEvent("onreadystatechange", DOMContentLoaded);
				window.attachEvent("onload", ready);
				var toplevel = false;
				try {
					toplevel = window.frameElement == null;
				} catch (e) {}
				if (document.documentElement.doScroll && toplevel) {
					doScrollCheck();
				}
			}
		}

		function doScrollCheck() {
			if (isReady) {
				return
			}
			try {
				document.documentElement.doScroll("left")
			} catch (e) {
				setTimeout(doScrollCheck, 1);
				return
			}
			ready()
		}

		function ready() {
			if (!isReady) {
				if (!document.body) {
					return setTimeout(ready, 1)
				}
				isReady = true;
				readyList.fire()
			}
		}

		if (document.addEventListener) {
			DOMContentLoaded = function () {
				document.removeEventListener("DOMContentLoaded", DOMContentLoaded, false);
				ready()
			}
		} else if (document.attachEvent) {
			DOMContentLoaded = function () {
				if (document.readyState === "complete") {
					document.detachEvent("onreadystatechange", DOMContentLoaded);
					ready()
				}
			}
		}
		function Callbacks() {
			var list = [],
			fired,
			firing,
			firingStart,
			firingLength,
			firingIndex;
			var self = {
				add : function (fn) {
					var length = list.length;
					list.push(fn);
					if (firing) {
						firingLength = list.length
					} else if (fired) {
						firingStart = length;
						self.fire();
					}
				},
				fire : function () {
					fired = true;
					firing = true;
					firingIndex = firingStart || 0;
					firingStart = 0;
					firingLength = list.length;
					for (; firingIndex < firingLength; firingIndex++) {
						list[firingIndex].call(document)
					}
					firing = false;
				}
			};
			return self;
		}

		return domReady;
	})(window.document);
	// 兼容不同浏览器的 Adapter 适配层
	if (typeof window.XMLHttpRequest === "undefined") {
		window.XMLHttpRequest = function () {
			return new window.ActiveXObject(navigator.userAgent.indexOf("MSIE 5") >= 0 ? "Microsoft.XMLHTTP" : "Msxml2.XMLHTTP");
		};
	}
	/**
	 * ajax类库
	 */
	var ajax = function (uri, option) {
		var httpRequest,
		httpSuccess,
		timeout,
		isTimeout = false,
		isComplete = false;

		option = {
			method : (option.method || "GET").toUpperCase(),
			data : option.data || null,
			arguments : option.arguments || null,

			onSuccess : option.onSuccess ||
			function () {},
			onError : option.onError ||
			function () {},
			onComplete : option.onComplete ||
			function () {},
			//尚未测试
			onTimeout : option.onTimeout ||
			function () {},

			isAsync : option.isAsync || true,
			timeout : option.timeout || 30000,
			contentType : option.contentType,
			type : option.type || "xml"
		};
		if (option.data && typeof option.data === "object") {
			option.data = J.string.toQueryString(option.data);
		}

		uri = uri || "";
		timeout = option.timeout;

		httpRequest = new window.XMLHttpRequest();

		/**
		 * @ignore
		 */
		httpSuccess = function (r) {
			try {
				return (!r.status && location.protocol == "file:") || (r.status >= 200 && r.status < 300) || (r.status == 304) || (navigator.userAgent.indexOf("Safari") > -1 && typeof r.status == "undefined");
			} catch (e) {
			}
			return false;
		}
		httpRequest.onreadystatechange = function () {
			if (httpRequest.readyState == 4) {
				if (!isTimeout) {
					var o = {};
					o.responseText = httpRequest.responseText;
					o.responseXML = httpRequest.responseXML;
					o.data = option.data;
					o.status = httpRequest.status;
					o.uri = uri;
					o.arguments = option.arguments;
					if (option.type === 'json') {
						try {
							o.responseJSON = xp.parseJSON(httpRequest.responseText);
						} catch (e) {}
					}
					if (httpSuccess(httpRequest)) {
						option.onSuccess(o);
					} else {
						option.onError(o);
					}
					option.onComplete(o);
				}
				isComplete = true;
				//删除对象,防止内存溢出
				httpRequest = null;
			}
		};

		if (option.method === "GET") {
			if (option.data) {
				uri += (uri.indexOf("?") > -1 ? "&" : "?") + option.data;
				option.data = null;
			}
			httpRequest.open("GET", uri, option.isAsync);
			httpRequest.setRequestHeader("Content-Type", option.contentType || "text/plain;charset=UTF-8");
			httpRequest.send();
		} else if (option.method === "POST") {
			httpRequest.open("POST", uri, option.isAsync);
			httpRequest.setRequestHeader("Content-Type", option.contentType || "application/x-www-form-urlencoded;charset=UTF-8");
			httpRequest.send(option.data);
		} else {
			httpRequest.open(option.method, uri, option.isAsync);
			httpRequest.send();
		}

		window.setTimeout(function () {
			var o;
			if (!isComplete) {
				isTimeout = true;
				o = {};
				o.uri = uri;
				o.arguments = option.arguments;
				option.onTimeout(o);
				option.onComplete(o);
			}
		}, timeout);

		return httpRequest;
	};

	xp.ajax = {
		get : function (url, fn) {
			ajax(url, {
				'onSuccess' : function (data) {
					fn.call(this, data.responseText);
				}
			});
		},
		htm : function (url, id) {
			ajax(url, {
				'onSuccess' : function (data) {

					xp.g(id).innerHTML = data.responseText;
					xp.excutejs(xp.g(id));
				}
			});
		},
		js : function (url, fn) {
			return ajax(url, {
				'onSuccess' : function (data) {
					try {
						eval.call(window, data.responseText);
						fn.call(this);
					} catch (_) {}
				}
			});
		},
		post : function (url, data, onsuccess) {
			return ajax(url, {
				'onSuccess' : onsuccess,
				'method' : 'POST',
				'data' : data
			});
		}
	};
	xp.ui = {};
	
	xp.excutejs = function (el) {
		var el = el || document;
		obj = xp.attrName("js", el);
		xp.each(obj, function (i, dom) {
			var prop = xp.getAttrs(dom),
			js = prop['js'],
			ui = xp.ui[js];
			ui && ui(prop);
		});
	};
	//开始执行页面
	xp.ready(function () {
		xp.excutejs();
	});
	//一些简单的小函数
	/**
	 * 返回
	 */
	xp.ui.back = function(opt) {
		opt.el.onclick = function() {
			history.back(-1);
		}
	};
	/**
	 * 跳转
	 */
	xp.ui.go = function(opt) {
		opt.el.onclick = function() {
			location.href = opt.url;
		}
	};
	/**
	 * 自动加载
	 */
	xp.ui.load = function(opt) {
		opt.url && xp.ajax.htm(opt.url, opt.el);
	};
	/**
	 * 点击刷新
	 */
	xp.ui.refresh = function(opt) {
		opt.el.onclick = function() {
			window.location.reload();
		}
	};
	/**
	 * 简单的tab切换
	 */
	xp.ui.tab = function(opt) {
		var el = opt.el, 
		tag = opt.tags || "a", 
		objId = opt.id || "tabs", 
		ts = xp.t(tag, el), 
		len = ts.length, 
		cls = opt.cls || ts[0].className, 
		current = opt.current || 0;
		for (var i = 0; i < len; i++) {
			if (i != current) {
				xp.g(objId + i).style.display = "none";
			}
		}
		el.onmouseover = function(e) {
			var target = xp.e(e);
			if (target.tagName === tag.toUpperCase()) {
				var index = ts.indexOf(target);
				for (var i = 0; i < len; i++) {
					ts[i].className = "";
					xp.g(objId + i).style.display = "none";
				}
				ts[index].className = cls;
				xp.g(objId + index).style.display = "";
			}

		}
	};
	/**
	 * 简单的鼠标点击互换
	 */
	xp.ui.rcheck = function(opt){
		var el = opt.el, 
		show = opt.show, 
		hide = opt.hide;
		el.onclick = function(e){
			if(show){
				xp.g(show).style.display = '';
			}
			if(hide){
				xp.g(hide).style.display = 'none';
			}
		}
	}
	/**
	 * tip标签
	 */
	var tip = function() {
		var id = '__xpbb_tips';
		var top = 3;
		var left = 3;
		var maxw = 300;
		var speed = 10;
		var timer = 20;
		var endalpha = 95;
		var alpha = 0;
		var tt, t, c, b, h;
		var ie = document.all ? true : false;
		return {
			show : function(v, w) {
				if (tt == null) {
					tt = document.createElement('div');
					tt.setAttribute('id', id);
					t = document.createElement('div');
					t.setAttribute('id', id + 'top');
					c = document.createElement('div');
					c.setAttribute('id', id + 'cont');
					b = document.createElement('div');
					b.setAttribute('id', id + 'bot');
					tt.appendChild(t);
					tt.appendChild(c);
					tt.appendChild(b);
					document.body.appendChild(tt);
					tt.style.opacity = 0;
					tt.style.filter = 'alpha(opacity=0)';
					document.onmousemove = this.pos;
				}
				tt.style.display = 'block';
				c.innerHTML = v;
				tt.style.width = w ? w + 'px' : 'auto';
				if (!w && ie) {
					t.style.display = 'none';
					b.style.display = 'none';
					tt.style.width = tt.offsexpidth;
					t.style.display = 'block';
					b.style.display = 'block';
				}
				if (tt.offsexpidth > maxw) {
					tt.style.width = maxw + 'px'
				}
				h = parseInt(tt.offsetHeight) + top;
				clearInterval(tt.timer);
				tt.timer = setInterval(function() {
					tooltip.fade(1)
				}, timer);
			},
			pos : function(e) {
				var u = ie ? event.clientY + document.documentElement.scrollTop : e.pageY;
				var l = ie ? event.clientX + document.documentElement.scrollLeft : e.pageX;
				tt.style.top = (u - h) + 'px';
				tt.style.left = (l + left) + 'px';
			},
			fade : function(d) {
				var a = alpha;
				if ((a != endalpha && d == 1) || (a != 0 && d == -1)) {
					var i = speed;
					if (endalpha - a < speed && d == 1) {
						i = endalpha - a;
					} else if (alpha < speed && d == -1) {
						i = a;
					}
					alpha = a + (i * d);
					tt.style.opacity = alpha * .01;
					tt.style.filter = 'alpha(opacity=' + alpha + ')';
				} else {
					clearInterval(tt.timer);
					if (d == -1) {
						tt.style.display = 'none'
					}
				}
			},
			hide : function() {
				clearInterval(tt.timer);
				tt.timer = setInterval(function() {
					tooltip.fade(-1)
				}, timer);
			}
		};
	}();
	
	/**
	 * tip提示
	 */
	xp.ui.tip = function(opt) {
		if (opt.show) {
			opt.el.onmouseover = function() {
				tip.show(opt.show);
			}
			opt.el.onmouseout = function() {
				tip.hide();
			}
		}
		//跳转
		if (opt.url) {
			opt.el.onclick = function() {
				location.href = opt.url;
			}
		}
		//弹窗
		if (opt.win) {
			opt.el.onclick = function() {
				window.open(opt.win);
			}
		}
	};
	
	
	//把xp暴露到外面
	window.xp = xp;
})();
