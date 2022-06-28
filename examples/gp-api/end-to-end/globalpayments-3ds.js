this.GlobalPayments = this.GlobalPayments || {};
this.GlobalPayments.ThreeDSecure = (function (exports) {
    'use strict';

    /*! *****************************************************************************
    Copyright (c) Microsoft Corporation.

    Permission to use, copy, modify, and/or distribute this software for any
    purpose with or without fee is hereby granted.

    THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES WITH
    REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF MERCHANTABILITY
    AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY SPECIAL, DIRECT,
    INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES WHATSOEVER RESULTING FROM
    LOSS OF USE, DATA OR PROFITS, WHETHER IN AN ACTION OF CONTRACT, NEGLIGENCE OR
    OTHER TORTIOUS ACTION, ARISING OUT OF OR IN CONNECTION WITH THE USE OR
    PERFORMANCE OF THIS SOFTWARE.
    ***************************************************************************** */
    /* global Reflect, Promise */

    var extendStatics = function(d, b) {
        extendStatics = Object.setPrototypeOf ||
            ({ __proto__: [] } instanceof Array && function (d, b) { d.__proto__ = b; }) ||
            function (d, b) { for (var p in b) if (b.hasOwnProperty(p)) d[p] = b[p]; };
        return extendStatics(d, b);
    };

    function __extends(d, b) {
        extendStatics(d, b);
        function __() { this.constructor = d; }
        d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
    }

    function __awaiter(thisArg, _arguments, P, generator) {
        function adopt(value) { return value instanceof P ? value : new P(function (resolve) { resolve(value); }); }
        return new (P || (P = Promise))(function (resolve, reject) {
            function fulfilled(value) { try { step(generator.next(value)); } catch (e) { reject(e); } }
            function rejected(value) { try { step(generator["throw"](value)); } catch (e) { reject(e); } }
            function step(result) { result.done ? resolve(result.value) : adopt(result.value).then(fulfilled, rejected); }
            step((generator = generator.apply(thisArg, _arguments || [])).next());
        });
    }

    function __generator(thisArg, body) {
        var _ = { label: 0, sent: function() { if (t[0] & 1) throw t[1]; return t[1]; }, trys: [], ops: [] }, f, y, t, g;
        return g = { next: verb(0), "throw": verb(1), "return": verb(2) }, typeof Symbol === "function" && (g[Symbol.iterator] = function() { return this; }), g;
        function verb(n) { return function (v) { return step([n, v]); }; }
        function step(op) {
            if (f) throw new TypeError("Generator is already executing.");
            while (_) try {
                if (f = 1, y && (t = op[0] & 2 ? y["return"] : op[0] ? y["throw"] || ((t = y["return"]) && t.call(y), 0) : y.next) && !(t = t.call(y, op[1])).done) return t;
                if (y = 0, t) op = [op[0] & 2, t.value];
                switch (op[0]) {
                    case 0: case 1: t = op; break;
                    case 4: _.label++; return { value: op[1], done: false };
                    case 5: _.label++; y = op[1]; op = [0]; continue;
                    case 7: op = _.ops.pop(); _.trys.pop(); continue;
                    default:
                        if (!(t = _.trys, t = t.length > 0 && t[t.length - 1]) && (op[0] === 6 || op[0] === 2)) { _ = 0; continue; }
                        if (op[0] === 3 && (!t || (op[1] > t[0] && op[1] < t[3]))) { _.label = op[1]; break; }
                        if (op[0] === 6 && _.label < t[1]) { _.label = t[1]; t = op; break; }
                        if (t && _.label < t[2]) { _.label = t[2]; _.ops.push(op); break; }
                        if (t[2]) _.ops.pop();
                        _.trys.pop(); continue;
                }
                op = body.call(thisArg, _);
            } catch (e) { op = [6, e]; y = 0; } finally { f = t = 0; }
            if (op[0] & 5) throw op[1]; return { value: op[0] ? op[1] : void 0, done: true };
        }
    }

    function createCommonjsModule(fn, basedir, module) {
    	return module = {
    		path: basedir,
    		exports: {},
    		require: function (path, base) {
    			return commonjsRequire(path, (base === undefined || base === null) ? module.path : base);
    		}
    	}, fn(module, module.exports), module.exports;
    }

    function getAugmentedNamespace(n) {
    	if (n.__esModule) return n;
    	var a = Object.defineProperty({}, '__esModule', {value: true});
    	Object.keys(n).forEach(function (k) {
    		var d = Object.getOwnPropertyDescriptor(n, k);
    		Object.defineProperty(a, k, d.get ? d : {
    			enumerable: true,
    			get: function () {
    				return n[k];
    			}
    		});
    	});
    	return a;
    }

    function commonjsRequire () {
    	throw new Error('Dynamic requires are not currently supported by @rollup/plugin-commonjs');
    }

    self.fetch||(self.fetch=function(e,n){return n=n||{},new Promise(function(t,s){var r=new XMLHttpRequest,o=[],u=[],i={},a=function(){return {ok:2==(r.status/100|0),statusText:r.statusText,status:r.status,url:r.responseURL,text:function(){return Promise.resolve(r.responseText)},json:function(){return Promise.resolve(r.responseText).then(JSON.parse)},blob:function(){return Promise.resolve(new Blob([r.response]))},clone:a,headers:{keys:function(){return o},entries:function(){return u},get:function(e){return i[e.toLowerCase()]},has:function(e){return e.toLowerCase()in i}}}};for(var c in r.open(n.method||"get",e,!0),r.onload=function(){r.getAllResponseHeaders().replace(/^(.*?):[^\S\n]*([\s\S]*?)$/gm,function(e,n,t){o.push(n=n.toLowerCase()),u.push([n,t]),i[n]=i[n]?i[n]+","+t:t;}),t(a());},r.onerror=s,r.withCredentials="include"==n.credentials,n.headers)r.setRequestHeader(c,n.headers[c]);r.send(n.body||null);})});

    if (!Array.prototype.forEach) {
        Array.prototype.forEach = function (fn) {
            for (var i = 0; i < this.length; i++) {
                fn(this[i], i, this);
            }
        };
    }

    var byteLength_1 = byteLength;
    var toByteArray_1 = toByteArray;
    var fromByteArray_1 = fromByteArray;

    var lookup = [];
    var revLookup = [];
    var Arr = typeof Uint8Array !== 'undefined' ? Uint8Array : Array;

    var code = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/';
    for (var i = 0, len = code.length; i < len; ++i) {
      lookup[i] = code[i];
      revLookup[code.charCodeAt(i)] = i;
    }

    // Support decoding URL-safe base64 strings, as Node.js does.
    // See: https://en.wikipedia.org/wiki/Base64#URL_applications
    revLookup['-'.charCodeAt(0)] = 62;
    revLookup['_'.charCodeAt(0)] = 63;

    function getLens (b64) {
      var len = b64.length;

      if (len % 4 > 0) {
        throw new Error('Invalid string. Length must be a multiple of 4')
      }

      // Trim off extra bytes after placeholder bytes are found
      // See: https://github.com/beatgammit/base64-js/issues/42
      var validLen = b64.indexOf('=');
      if (validLen === -1) validLen = len;

      var placeHoldersLen = validLen === len
        ? 0
        : 4 - (validLen % 4);

      return [validLen, placeHoldersLen]
    }

    // base64 is 4/3 + up to two characters of the original data
    function byteLength (b64) {
      var lens = getLens(b64);
      var validLen = lens[0];
      var placeHoldersLen = lens[1];
      return ((validLen + placeHoldersLen) * 3 / 4) - placeHoldersLen
    }

    function _byteLength (b64, validLen, placeHoldersLen) {
      return ((validLen + placeHoldersLen) * 3 / 4) - placeHoldersLen
    }

    function toByteArray (b64) {
      var tmp;
      var lens = getLens(b64);
      var validLen = lens[0];
      var placeHoldersLen = lens[1];

      var arr = new Arr(_byteLength(b64, validLen, placeHoldersLen));

      var curByte = 0;

      // if there are placeholders, only get up to the last complete 4 chars
      var len = placeHoldersLen > 0
        ? validLen - 4
        : validLen;

      var i;
      for (i = 0; i < len; i += 4) {
        tmp =
          (revLookup[b64.charCodeAt(i)] << 18) |
          (revLookup[b64.charCodeAt(i + 1)] << 12) |
          (revLookup[b64.charCodeAt(i + 2)] << 6) |
          revLookup[b64.charCodeAt(i + 3)];
        arr[curByte++] = (tmp >> 16) & 0xFF;
        arr[curByte++] = (tmp >> 8) & 0xFF;
        arr[curByte++] = tmp & 0xFF;
      }

      if (placeHoldersLen === 2) {
        tmp =
          (revLookup[b64.charCodeAt(i)] << 2) |
          (revLookup[b64.charCodeAt(i + 1)] >> 4);
        arr[curByte++] = tmp & 0xFF;
      }

      if (placeHoldersLen === 1) {
        tmp =
          (revLookup[b64.charCodeAt(i)] << 10) |
          (revLookup[b64.charCodeAt(i + 1)] << 4) |
          (revLookup[b64.charCodeAt(i + 2)] >> 2);
        arr[curByte++] = (tmp >> 8) & 0xFF;
        arr[curByte++] = tmp & 0xFF;
      }

      return arr
    }

    function tripletToBase64 (num) {
      return lookup[num >> 18 & 0x3F] +
        lookup[num >> 12 & 0x3F] +
        lookup[num >> 6 & 0x3F] +
        lookup[num & 0x3F]
    }

    function encodeChunk (uint8, start, end) {
      var tmp;
      var output = [];
      for (var i = start; i < end; i += 3) {
        tmp =
          ((uint8[i] << 16) & 0xFF0000) +
          ((uint8[i + 1] << 8) & 0xFF00) +
          (uint8[i + 2] & 0xFF);
        output.push(tripletToBase64(tmp));
      }
      return output.join('')
    }

    function fromByteArray (uint8) {
      var tmp;
      var len = uint8.length;
      var extraBytes = len % 3; // if we have 1 byte left, pad 2 bytes
      var parts = [];
      var maxChunkLength = 16383; // must be multiple of 3

      // go through the array every three bytes, we'll deal with trailing stuff later
      for (var i = 0, len2 = len - extraBytes; i < len2; i += maxChunkLength) {
        parts.push(encodeChunk(uint8, i, (i + maxChunkLength) > len2 ? len2 : (i + maxChunkLength)));
      }

      // pad the end with zeros, but make sure to not forget the extra bytes
      if (extraBytes === 1) {
        tmp = uint8[len - 1];
        parts.push(
          lookup[tmp >> 2] +
          lookup[(tmp << 4) & 0x3F] +
          '=='
        );
      } else if (extraBytes === 2) {
        tmp = (uint8[len - 2] << 8) + uint8[len - 1];
        parts.push(
          lookup[tmp >> 10] +
          lookup[(tmp >> 4) & 0x3F] +
          lookup[(tmp << 2) & 0x3F] +
          '='
        );
      }

      return parts.join('')
    }

    var base64Js = {
    	byteLength: byteLength_1,
    	toByteArray: toByteArray_1,
    	fromByteArray: fromByteArray_1
    };

    var base64 = createCommonjsModule(function (module, exports) {
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.base64decode = exports.base64encode = void 0;

    function base64encode(text) {
        var i;
        var len = text.length;
        var Arr = typeof Uint8Array !== "undefined" ? Uint8Array : Array;
        var u8array = new Arr(len);
        for (i = 0; i < len; i++) {
            u8array[i] = text.charCodeAt(i);
        }
        return base64Js.fromByteArray(u8array);
    }
    exports.base64encode = base64encode;
    function base64decode(text) {
        var u8Array = base64Js.toByteArray(text);
        var i;
        var len = u8Array.length;
        var bStr = "";
        for (i = 0; i < len; i++) {
            bStr += String.fromCharCode(u8Array[i]);
        }
        return bStr;
    }
    exports.base64decode = base64decode;
    window.btoa = window.btoa || base64encode;
    window.atob = window.atob || base64decode;

    });

    var json2 = createCommonjsModule(function (module, exports) {
    /* -----------------------------------------------------------------------------
    This file is based on or incorporates material from the projects listed below
    (collectively, "Third Party Code"). Microsoft is not the original author of the
    Third Party Code. The original copyright notice and the license, under which
    Microsoft received such Third Party Code, are set forth below. Such licenses
    and notices are provided for informational purposes only. Microsoft, not the
    third party, licenses the Third Party Code to you under the terms of the
    Apache License, Version 2.0. See License.txt in the project root for complete
    license information. Microsoft reserves all rights not expressly granted under
    the Apache 2.0 License, whether by implication, estoppel or otherwise.
    ----------------------------------------------------------------------------- */
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.JSON = void 0;
    /*
        json2.js
        2011-10-19

        Public Domain.

        NO WARRANTY EXPRESSED OR IMPLIED. USE AT YOUR OWN RISK.

        See http://www.JSON.org/js.html

        This code should be minified before deployment.
        See http://javascript.crockford.com/jsmin.html

        USE YOUR OWN COPY. IT IS EXTREMELY UNWISE TO LOAD CODE FROM SERVERS YOU DO
        NOT CONTROL.

        This file creates a global JSON object containing two methods: stringify
        and parse.

            JSON.stringify(value, replacer, space)
                value       any JavaScript value, usually an object or array.

                replacer    an optional parameter that determines how object
                            values are stringified for objects. It can be a
                            function or an array of strings.

                space       an optional parameter that specifies the indentation
                            of nested structures. If it is omitted, the text will
                            be packed without extra whitespace. If it is a number,
                            it will specify the number of spaces to indent at each
                            level. If it is a string (such as "\t" or "&nbsp;"),
                            it contains the characters used to indent at each level.

                This method produces a JSON text from a JavaScript value.

                When an object value is found, if the object contains a toJSON
                method, its toJSON method will be called and the result will be
                stringified. A toJSON method does not serialize: it returns the
                value represented by the name/value pair that should be serialized,
                or undefined if nothing should be serialized. The toJSON method
                will be passed the key associated with the value, and this will be
                bound to the value

                For example, this would serialize Dates as ISO strings.

                    Date.prototype.toJSON = function (key) {
                        function f(n) {
                            // Format integers to have at least two digits.
                            return n < 10 ? "0" + n : n;
                        }

                        return this.getUTCFullYear()   + "-" +
                             f(this.getUTCMonth() + 1) + "-" +
                             f(this.getUTCDate())      + "T" +
                             f(this.getUTCHours())     + ":" +
                             f(this.getUTCMinutes())   + ":" +
                             f(this.getUTCSeconds())   + "Z";
                    };

                You can provide an optional replacer method. It will be passed the
                key and value of each member, with this bound to the containing
                object. The value that is returned from your method will be
                serialized. If your method returns undefined, then the member will
                be excluded from the serialization.

                If the replacer parameter is an array of strings, then it will be
                used to select the members to be serialized. It filters the results
                such that only members with keys listed in the replacer array are
                stringified.

                Values that do not have JSON representations, such as undefined or
                functions, will not be serialized. Such values in objects will be
                dropped; in arrays they will be replaced with null. You can use
                a replacer function to replace those with JSON values.
                JSON.stringify(undefined) returns undefined.

                The optional space parameter produces a stringification of the
                value that is filled with line breaks and indentation to make it
                easier to read.

                If the space parameter is a non-empty string, then that string will
                be used for indentation. If the space parameter is a number, then
                the indentation will be that many spaces.

                Example:

                text = JSON.stringify(["e", {pluribus: "unum"}]);
                // text is "["e",{"pluribus":"unum"}]"

                text = JSON.stringify(["e", {pluribus: "unum"}], null, "\t");
                // text is "[\n\t"e",\n\t{\n\t\t"pluribus": "unum"\n\t}\n]"

                text = JSON.stringify([new Date()], function (key, value) {
                    return this[key] instanceof Date ?
                        "Date(" + this[key] + ")" : value;
                });
                // text is "["Date(---current time---)"]"

            JSON.parse(text, reviver)
                This method parses a JSON text to produce an object or array.
                It can throw a SyntaxError exception.

                The optional reviver parameter is a function that can filter and
                transform the results. It receives each of the keys and values,
                and its return value is used instead of the original value.
                If it returns what it received, then the structure is not modified.
                If it returns undefined then the member is deleted.

                Example:

                // Parse the text. Values that look like ISO date strings will
                // be converted to Date objects.

                myData = JSON.parse(text, function (key, value) {
                    let a;
                    if (typeof value === "string") {
                        a =
    /^(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2}):(\d{2}(?:\.\d*)?)Z$/.exec(value);
                        if (a) {
                            return new Date(Date.UTC(+a[1], +a[2] - 1, +a[3], +a[4],
                                +a[5], +a[6]));
                        }
                    }
                    return value;
                });

                myData = JSON.parse("["Date(09/09/2001)"]", function (key, value) {
                    let d;
                    if (typeof value === "string" &&
                            value.slice(0, 5) === "Date(" &&
                            value.slice(-1) === ")") {
                        d = new Date(value.slice(5, -1));
                        if (d) {
                            return d;
                        }
                    }
                    return value;
                });

        This is a reference implementation. You are free to copy, modify, or
        redistribute.
    */
    /*jslint evil: true, regexp: true */
    /*members "", "\b", "\t", "\n", "\f", "\r", "\"", JSON, "\\", apply,
        call, charCodeAt, getUTCDate, getUTCFullYear, getUTCHours,
        getUTCMinutes, getUTCMonth, getUTCSeconds, hasOwnProperty, join,
        lastIndex, length, parse, prototype, push, replace, slice, stringify,
        test, toJSON, toString, valueOf
    */
    // create a JSON object only if one does not already exist. We create the
    // methods in a closure to avoid creating global variables.
    exports.JSON = {};
    (function () {
        function f(n) {
            // format integers to have at least two digits.
            return n < 10 ? "0" + n : n;
        }
        if (typeof Date.prototype.toJSON !== "function") {
            Date.prototype.toJSON = function (_KEY) {
                return isFinite(this.valueOf())
                    ? this.getUTCFullYear() +
                        "-" +
                        f(this.getUTCMonth() + 1) +
                        "-" +
                        f(this.getUTCDate()) +
                        "T" +
                        f(this.getUTCHours()) +
                        ":" +
                        f(this.getUTCMinutes()) +
                        ":" +
                        f(this.getUTCSeconds()) +
                        "Z"
                    : "";
            };
            var strProto = String.prototype;
            var numProto = Number.prototype;
            numProto.JSON = strProto.JSON = Boolean.prototype.toJSON = function (_KEY) {
                return this.valueOf();
            };
        }
        var cx = /[\u0000\u00ad\u0600-\u0604\u070f\u17b4\u17b5\u200c-\u200f\u2028-\u202f\u2060-\u206f\ufeff\ufff0-\uffff]/g;
        // tslint:disable-next-line
        var esc = /[\\\"\x00-\x1f\x7f-\x9f\u00ad\u0600-\u0604\u070f\u17b4\u17b5\u200c-\u200f\u2028-\u202f\u2060-\u206f\ufeff\ufff0-\uffff]/g;
        var gap;
        var indent;
        var meta = {
            // table of character substitutions
            "\b": "\\b",
            "\t": "\\t",
            "\n": "\\n",
            "\f": "\\f",
            "\r": "\\r",
            '"': '\\"',
            "\\": "\\\\",
        };
        var rep;
        function quote(quoteStr) {
            // if the string contains no control characters, no quote characters, and no
            // backslash characters, then we can safely slap some quotes around it.
            // otherwise we must also replace the offending characters with safe escape
            // sequences.
            esc.lastIndex = 0;
            return esc.test(quoteStr)
                ? '"' +
                    quoteStr.replace(esc, function (a) {
                        var c = meta[a];
                        return typeof c === "string"
                            ? c
                            : "\\u" + ("0000" + a.charCodeAt(0).toString(16)).slice(-4);
                    }) +
                    '"'
                : '"' + quoteStr + '"';
        }
        function str(key, holder) {
            // produce a string from holder[key].
            var i; // the loop counter.
            var k; // the member key.
            var v; // the member value.
            var length;
            var mind = gap;
            var partial;
            var value = holder[key];
            // if the value has a toJSON method, call it to obtain a replacement value.
            if (value &&
                typeof value === "object" &&
                typeof value.toJSON === "function") {
                value = value.toJSON(key);
            }
            // if we were called with a replacer function, then call the replacer to
            // obtain a replacement value.
            if (typeof rep === "function") {
                value = rep.call(holder, key, value);
            }
            // what happens next depends on the value"s type.
            switch (typeof value) {
                case "string":
                    return quote(value);
                case "number":
                    // json numbers must be finite. Encode non-finite numbers as null.
                    return isFinite(value) ? String(value) : "null";
                case "boolean":
                case "null":
                    // if the value is a boolean or null, convert it to a string. Note:
                    // typeof null does not produce "null". The case is included here in
                    // the remote chance that this gets fixed someday.
                    return String(value);
                // if the type is "object", we might be dealing with an object or an array or
                // null.
                case "object":
                    // due to a specification blunder in ECMAScript, typeof null is "object",
                    // so watch out for that case.
                    if (!value) {
                        return "null";
                    }
                    // make an array to hold the partial: string[] results of stringifying this object value.
                    gap += indent;
                    partial = [];
                    // is the value an array?
                    if (Object.prototype.toString.apply(value, []) === "[object Array]") {
                        // the value is an array. Stringify every element. Use null as a placeholder
                        // for non-JSON values.
                        length = value.length;
                        for (i = 0; i < length; i += 1) {
                            partial[i] = str(i.toString(), value) || "null";
                        }
                        // join all of the elements together, separated with commas, and wrap them in
                        // brackets.
                        v =
                            partial.length === 0
                                ? "[]"
                                : gap
                                    ? "[\n" + gap + partial.join(",\n" + gap) + "\n" + mind + "]"
                                    : "[" + partial.join(",") + "]";
                        gap = mind;
                        return v;
                    }
                    // if the replacer is an array, use it to select the members to be stringified.
                    if (rep && typeof rep === "object") {
                        length = rep.length;
                        for (i = 0; i < length; i += 1) {
                            if (typeof rep[i] === "string") {
                                k = rep[i];
                                v = str(k, value);
                                if (v) {
                                    partial.push(quote(k) + (gap ? ": " : ":") + v);
                                }
                            }
                        }
                    }
                    else {
                        // otherwise, iterate through all of the keys in the object.
                        for (k in value) {
                            if (Object.prototype.hasOwnProperty.call(value, k)) {
                                v = str(k, value);
                                if (v) {
                                    partial.push(quote(k) + (gap ? ": " : ":") + v);
                                }
                            }
                        }
                    }
                    // join all of the member texts together, separated with commas,
                    // and wrap them in braces.
                    v =
                        partial.length === 0
                            ? "{}"
                            : gap
                                ? "{\n" + gap + partial.join(",\n" + gap) + "\n" + mind + "}"
                                : "{" + partial.join(",") + "}";
                    gap = mind;
                    return v;
            }
            return undefined;
        }
        // if the JSON object does not yet have a stringify method, give it one.
        if (typeof exports.JSON.stringify !== "function") {
            exports.JSON.stringify = function (value, replacer, space) {
                // the stringify method takes a value and an optional replacer, and an optional
                // space parameter, and returns a JSON text. The replacer can be a function
                // that can replace values, or an array of strings that will select the keys.
                // a default replacer method can be provided. Use of the space parameter can
                // produce text that is more easily readable.
                var i;
                gap = "";
                indent = "";
                // if the space parameter is a number, make an indent string containing that
                // many spaces.
                if (typeof space === "number") {
                    for (i = 0; i < space; i += 1) {
                        indent += " ";
                    }
                    // if the space parameter is a string, it will be used as the indent string.
                }
                else if (typeof space === "string") {
                    indent = space;
                }
                // if there is a replacer, it must be a function or an array.
                // otherwise, throw an error.
                rep = replacer;
                if (replacer &&
                    typeof replacer !== "function" &&
                    (typeof replacer !== "object" || typeof replacer.length !== "number")) {
                    throw new Error("JSON.stringify");
                }
                // make a fake root object containing our value under the key of "".
                // return the result of stringifying the value.
                return str("", { "": value });
            };
        }
        // if the JSON object does not yet have a parse method, give it one.
        if (typeof exports.JSON.parse !== "function") {
            exports.JSON.parse = function (text, reviver) {
                // the parse method takes a text and an optional reviver function, and returns
                // a JavaScript value if the text is a valid JSON text.
                var j;
                function walk(holder, key) {
                    // the walk method is used to recursively walk the resulting structure so
                    // that modifications can be made.
                    var k;
                    var v;
                    var value = holder[key];
                    if (value && typeof value === "object") {
                        for (k in value) {
                            if (Object.prototype.hasOwnProperty.call(value, k)) {
                                v = walk(value, k);
                                value[k] = v;
                            }
                        }
                    }
                    return reviver.call(holder, key, value);
                }
                // parsing happens in four stages. In the first stage, we replace certain
                // unicode characters with escape sequences. JavaScript handles many characters
                // incorrectly, either silently deleting them, or treating them as line endings.
                text = String(text);
                cx.lastIndex = 0;
                if (cx.test(text)) {
                    text = text.replace(cx, function (a) {
                        return "\\u" + ("0000" + a.charCodeAt(0).toString(16)).slice(-4);
                    });
                }
                // in the second stage, we run the text against regular expressions that look
                // for non-JSON patterns. We are especially concerned with "()" and "new"
                // because they can cause invocation, and "=" because it can cause mutation.
                // but just to be safe, we want to reject all unexpected forms.
                // we split the second stage into 4 regexp operations in order to work around
                // crippling inefficiencies in IE"s and Safari"s regexp engines. First we
                // replace the JSON backslash pairs with "@" (a non-JSON character). Second, we
                // replace all simple value tokens with "]" characters. Third, we delete all
                // open brackets that follow a colon or comma or that begin the text. Finally,
                // we look to see that the remaining characters are only whitespace or "]" or
                // "," or ":" or "{" or "}". If that is so, then the text is safe for eval.
                if (/^[\],:{}\s]*$/.test(text
                    .replace(/\\(?:["\\\/bfnrt]|u[0-9a-fA-F]{4})/g, "@")
                    .replace(/"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g, "]")
                    .replace(/(?:^|:|,)(?:\s*\[)+/g, ""))) {
                    // in the third stage we use the eval function to compile the text into a
                    // javascript structure. The "{" operator is subject to a syntactic ambiguity
                    // in JavaScript: it can begin a block or an object literal. We wrap the text
                    // in parens to eliminate the ambiguity.
                    // tslint:disable-next-line:function-constructor
                    j = new Function("return (" + text + ")")();
                    // in the optional fourth stage, we recursively walk the new structure, passing
                    // each name/value pair to a reviver function for possible transformation.
                    return typeof reviver === "function" ? walk({ "": j }, "") : j;
                }
                // if the text is not JSON parseable, then a SyntaxError is thrown.
                throw new SyntaxError("JSON.parse");
            };
        }
    })();

    });

    var json = createCommonjsModule(function (module, exports) {
    Object.defineProperty(exports, "__esModule", { value: true });

    window.JSON = window.JSON || json2.JSON;

    });

    // ES5 15.2.3.9
    // http://es5.github.com/#x15.2.3.9
    if (!Object.freeze) {
        Object.freeze = function (object) {
            if (Object(object) !== object) {
                throw new TypeError("Object.freeze can only be called on Objects.");
            }
            // this is misleading and breaks feature-detection, but
            // allows "securable" code to "gracefully" degrade to working
            // but insecure code.
            return object;
        };
    }
    // detect a Rhino bug and patch it
    try {
        Object.freeze(function () { return undefined; });
    }
    catch (exception) {
        Object.freeze = (function (freezeObject) {
            return function (object) {
                if (typeof object === "function") {
                    return object;
                }
                else {
                    return freezeObject(object);
                }
            };
        })(Object.freeze);
    }

    if (!Object.prototype.hasOwnProperty) {
        Object.prototype.hasOwnProperty = function (prop) {
            return typeof this[prop] !== "undefined";
        };
    }
    if (!Object.getOwnPropertyNames) {
        Object.getOwnPropertyNames = function (obj) {
            var keys = [];
            for (var key in obj) {
                if (typeof obj.hasOwnProperty !== "undefined" &&
                    obj.hasOwnProperty(key)) {
                    keys.push(key);
                }
            }
            return keys;
        };
    }

    // Source: https://developer.mozilla.org/en-US/docs/Web/API/ParentNode/prepend
    (function (arr) {
        arr.forEach(function (item) {
            if (item.hasOwnProperty('prepend')) {
                return;
            }
            Object.defineProperty(item, 'prepend', {
                configurable: true,
                enumerable: true,
                writable: true,
                value: function prepend() {
                    var argArr = Array.prototype.slice.call(arguments);
                    var docFrag = document.createDocumentFragment();
                    argArr.forEach(function (argItem) {
                        var isNode = argItem instanceof Node;
                        docFrag.appendChild(isNode ? argItem : document.createTextNode(String(argItem)));
                    });
                    this.insertBefore(docFrag, this.firstChild);
                }
            });
        });
    })([Element.prototype, Document.prototype, DocumentFragment.prototype]);

    /**
     * @this {Promise}
     */
    function finallyConstructor(callback) {
      var constructor = this.constructor;
      return this.then(
        function(value) {
          // @ts-ignore
          return constructor.resolve(callback()).then(function() {
            return value;
          });
        },
        function(reason) {
          // @ts-ignore
          return constructor.resolve(callback()).then(function() {
            // @ts-ignore
            return constructor.reject(reason);
          });
        }
      );
    }

    function allSettled(arr) {
      var P = this;
      return new P(function(resolve, reject) {
        if (!(arr && typeof arr.length !== 'undefined')) {
          return reject(
            new TypeError(
              typeof arr +
                ' ' +
                arr +
                ' is not iterable(cannot read property Symbol(Symbol.iterator))'
            )
          );
        }
        var args = Array.prototype.slice.call(arr);
        if (args.length === 0) return resolve([]);
        var remaining = args.length;

        function res(i, val) {
          if (val && (typeof val === 'object' || typeof val === 'function')) {
            var then = val.then;
            if (typeof then === 'function') {
              then.call(
                val,
                function(val) {
                  res(i, val);
                },
                function(e) {
                  args[i] = { status: 'rejected', reason: e };
                  if (--remaining === 0) {
                    resolve(args);
                  }
                }
              );
              return;
            }
          }
          args[i] = { status: 'fulfilled', value: val };
          if (--remaining === 0) {
            resolve(args);
          }
        }

        for (var i = 0; i < args.length; i++) {
          res(i, args[i]);
        }
      });
    }

    // Store setTimeout reference so promise-polyfill will be unaffected by
    // other code modifying setTimeout (like sinon.useFakeTimers())
    var setTimeoutFunc = setTimeout;

    function isArray(x) {
      return Boolean(x && typeof x.length !== 'undefined');
    }

    function noop() {}

    // Polyfill for Function.prototype.bind
    function bind(fn, thisArg) {
      return function() {
        fn.apply(thisArg, arguments);
      };
    }

    /**
     * @constructor
     * @param {Function} fn
     */
    function Promise$1(fn) {
      if (!(this instanceof Promise$1))
        throw new TypeError('Promises must be constructed via new');
      if (typeof fn !== 'function') throw new TypeError('not a function');
      /** @type {!number} */
      this._state = 0;
      /** @type {!boolean} */
      this._handled = false;
      /** @type {Promise|undefined} */
      this._value = undefined;
      /** @type {!Array<!Function>} */
      this._deferreds = [];

      doResolve(fn, this);
    }

    function handle(self, deferred) {
      while (self._state === 3) {
        self = self._value;
      }
      if (self._state === 0) {
        self._deferreds.push(deferred);
        return;
      }
      self._handled = true;
      Promise$1._immediateFn(function() {
        var cb = self._state === 1 ? deferred.onFulfilled : deferred.onRejected;
        if (cb === null) {
          (self._state === 1 ? resolve : reject)(deferred.promise, self._value);
          return;
        }
        var ret;
        try {
          ret = cb(self._value);
        } catch (e) {
          reject(deferred.promise, e);
          return;
        }
        resolve(deferred.promise, ret);
      });
    }

    function resolve(self, newValue) {
      try {
        // Promise Resolution Procedure: https://github.com/promises-aplus/promises-spec#the-promise-resolution-procedure
        if (newValue === self)
          throw new TypeError('A promise cannot be resolved with itself.');
        if (
          newValue &&
          (typeof newValue === 'object' || typeof newValue === 'function')
        ) {
          var then = newValue.then;
          if (newValue instanceof Promise$1) {
            self._state = 3;
            self._value = newValue;
            finale(self);
            return;
          } else if (typeof then === 'function') {
            doResolve(bind(then, newValue), self);
            return;
          }
        }
        self._state = 1;
        self._value = newValue;
        finale(self);
      } catch (e) {
        reject(self, e);
      }
    }

    function reject(self, newValue) {
      self._state = 2;
      self._value = newValue;
      finale(self);
    }

    function finale(self) {
      if (self._state === 2 && self._deferreds.length === 0) {
        Promise$1._immediateFn(function() {
          if (!self._handled) {
            Promise$1._unhandledRejectionFn(self._value);
          }
        });
      }

      for (var i = 0, len = self._deferreds.length; i < len; i++) {
        handle(self, self._deferreds[i]);
      }
      self._deferreds = null;
    }

    /**
     * @constructor
     */
    function Handler(onFulfilled, onRejected, promise) {
      this.onFulfilled = typeof onFulfilled === 'function' ? onFulfilled : null;
      this.onRejected = typeof onRejected === 'function' ? onRejected : null;
      this.promise = promise;
    }

    /**
     * Take a potentially misbehaving resolver function and make sure
     * onFulfilled and onRejected are only called once.
     *
     * Makes no guarantees about asynchrony.
     */
    function doResolve(fn, self) {
      var done = false;
      try {
        fn(
          function(value) {
            if (done) return;
            done = true;
            resolve(self, value);
          },
          function(reason) {
            if (done) return;
            done = true;
            reject(self, reason);
          }
        );
      } catch (ex) {
        if (done) return;
        done = true;
        reject(self, ex);
      }
    }

    Promise$1.prototype['catch'] = function(onRejected) {
      return this.then(null, onRejected);
    };

    Promise$1.prototype.then = function(onFulfilled, onRejected) {
      // @ts-ignore
      var prom = new this.constructor(noop);

      handle(this, new Handler(onFulfilled, onRejected, prom));
      return prom;
    };

    Promise$1.prototype['finally'] = finallyConstructor;

    Promise$1.all = function(arr) {
      return new Promise$1(function(resolve, reject) {
        if (!isArray(arr)) {
          return reject(new TypeError('Promise.all accepts an array'));
        }

        var args = Array.prototype.slice.call(arr);
        if (args.length === 0) return resolve([]);
        var remaining = args.length;

        function res(i, val) {
          try {
            if (val && (typeof val === 'object' || typeof val === 'function')) {
              var then = val.then;
              if (typeof then === 'function') {
                then.call(
                  val,
                  function(val) {
                    res(i, val);
                  },
                  reject
                );
                return;
              }
            }
            args[i] = val;
            if (--remaining === 0) {
              resolve(args);
            }
          } catch (ex) {
            reject(ex);
          }
        }

        for (var i = 0; i < args.length; i++) {
          res(i, args[i]);
        }
      });
    };

    Promise$1.allSettled = allSettled;

    Promise$1.resolve = function(value) {
      if (value && typeof value === 'object' && value.constructor === Promise$1) {
        return value;
      }

      return new Promise$1(function(resolve) {
        resolve(value);
      });
    };

    Promise$1.reject = function(value) {
      return new Promise$1(function(resolve, reject) {
        reject(value);
      });
    };

    Promise$1.race = function(arr) {
      return new Promise$1(function(resolve, reject) {
        if (!isArray(arr)) {
          return reject(new TypeError('Promise.race accepts an array'));
        }

        for (var i = 0, len = arr.length; i < len; i++) {
          Promise$1.resolve(arr[i]).then(resolve, reject);
        }
      });
    };

    // Use polyfill for setImmediate for performance gains
    Promise$1._immediateFn =
      // @ts-ignore
      (typeof setImmediate === 'function' &&
        function(fn) {
          // @ts-ignore
          setImmediate(fn);
        }) ||
      function(fn) {
        setTimeoutFunc(fn, 0);
      };

    Promise$1._unhandledRejectionFn = function _unhandledRejectionFn(err) {
      if (typeof console !== 'undefined' && console) {
        console.warn('Possible Unhandled Promise Rejection:', err); // eslint-disable-line no-console
      }
    };

    var src = /*#__PURE__*/Object.freeze({
        __proto__: null,
        'default': Promise$1
    });

    var Promise$2 = /*@__PURE__*/getAugmentedNamespace(src);

    var promise = createCommonjsModule(function (module, exports) {
    Object.defineProperty(exports, "__esModule", { value: true });

    window.Promise =
        window.Promise || Promise$2.default || Promise$2;

    });

    if (!String.prototype.repeat) {
        String.prototype.repeat = function (length) {
            var result = "";
            for (var i = 0; i < length; i++) {
                result += this;
            }
            return result;
        };
    }

    var polyfills = createCommonjsModule(function (module, exports) {
    Object.defineProperty(exports, "__esModule", { value: true });










    });

    (function (AuthenticationSource) {
        AuthenticationSource["Browser"] = "BROWSER";
        AuthenticationSource["MobileSDK"] = "MOBILE_SDK";
        AuthenticationSource["StoredRecurring"] = "STORED_RECURRING";
    })(exports.AuthenticationSource || (exports.AuthenticationSource = {}));
    (function (AuthenticationRequestType) {
        AuthenticationRequestType["AddCard"] = "ADD_CARD";
        AuthenticationRequestType["CardholderVerification"] = "CARDHOLDER_VERIFICATION";
        AuthenticationRequestType["InstalmentTransaction"] = "INSTALMENT_TRANSACTION";
        AuthenticationRequestType["MaintainCard"] = "MAINTAIN_CARD";
        AuthenticationRequestType["PaymentTransaction"] = "PAYMENT_TRANSACTION";
        AuthenticationRequestType["RecurringTransaction"] = "RECURRING_TRANSACTION";
    })(exports.AuthenticationRequestType || (exports.AuthenticationRequestType = {}));
    (function (ChallengeRequestIndicator) {
        ChallengeRequestIndicator["ChallengeMandated"] = "CHALLENGE_MANDATED";
        ChallengeRequestIndicator["ChallengePreferred"] = "CHALLENGE_PREFERRED";
        ChallengeRequestIndicator["NoChallengeRequested"] = "NO_CHALLENGE_REQUESTED";
        ChallengeRequestIndicator["NoPreference"] = "NO_PREFERENCE";
    })(exports.ChallengeRequestIndicator || (exports.ChallengeRequestIndicator = {}));
    (function (ChallengeWindowSize) {
        ChallengeWindowSize["FullScreen"] = "FULL_SCREEN";
        ChallengeWindowSize["Windowed250x400"] = "WINDOWED_250X400";
        ChallengeWindowSize["Windowed390x400"] = "WINDOWED_390X400";
        ChallengeWindowSize["Windowed500x600"] = "WINDOWED_500X600";
        ChallengeWindowSize["Windowed600x400"] = "WINDOWED_600X400";
    })(exports.ChallengeWindowSize || (exports.ChallengeWindowSize = {}));
    (function (MessageCategory) {
        MessageCategory["NonPayment"] = "NON_PAYMENT_AUTHENTICATION";
        MessageCategory["Payment"] = "PAYMENT_AUTHENTICATION";
    })(exports.MessageCategory || (exports.MessageCategory = {}));
    (function (MethodUrlCompletion) {
        MethodUrlCompletion["Unavailable"] = "UNAVAILABLE";
        MethodUrlCompletion["No"] = "NO";
        MethodUrlCompletion["Yes"] = "YES";
    })(exports.MethodUrlCompletion || (exports.MethodUrlCompletion = {}));
    (function (TransactionStatus) {
        TransactionStatus["AuthenticationAttemptedButNotSuccessful"] = "AUTHENTICATION_ATTEMPTED_BUT_NOT_SUCCESSFUL";
        TransactionStatus["AuthenticationCouldNotBePerformed"] = "AUTHENTICATION_COULD_NOT_BE_PERFORMED";
        TransactionStatus["AuthenticationFailed"] = "AUTHENTICATION_FAILED";
        TransactionStatus["AuthenticationIssuerRejected"] = "AUTHENTICATION_ISSUER_REJECTED";
        TransactionStatus["AuthenticationSuccessful"] = "AUTHENTICATION_SUCCESSFUL";
        TransactionStatus["ChallengeRequired"] = "CHALLENGE_REQUIRED";
    })(exports.TransactionStatus || (exports.TransactionStatus = {}));
    (function (TransactionStatusReason) {
        TransactionStatusReason["CardAuthenticationFailed"] = "CARD_AUTHENTICATION_FAILED";
        TransactionStatusReason["UnknownDevice"] = "UNKNOWN_DEVICE";
        TransactionStatusReason["UnsupportedDevice"] = "UNSUPPORTED_DEVICE";
        TransactionStatusReason["ExceedsAuthenticationFrequencyLimit"] = "EXCEEDS_AUTHENTICATION_FREQUENCY_LIMIT";
        TransactionStatusReason["ExpiredCard"] = "EXPIRED_CARD";
        TransactionStatusReason["InvalidCardNumber"] = "INVALID_CARD_NUMBER";
        TransactionStatusReason["InvalidTransaction"] = "INVALID_TRANSACTION";
        TransactionStatusReason["NoCardRecord"] = "NO_CARD_RECORD";
        TransactionStatusReason["SecurityFailure"] = "SECURITY_FAILURE";
        TransactionStatusReason["StolenCard"] = "STOLEN_CARD";
        TransactionStatusReason["SuspectedFraud"] = "SUSPECTED_FRAUD";
        TransactionStatusReason["TransactionNotPermittedToCardholder"] = "TRANSACTION_NOT_PERMITTED_TO_CARDHOLDER";
        TransactionStatusReason["CardholderNotEnrolledInService"] = "CARDHOLDER_NOT_ENROLLED_IN_SERVICE";
        TransactionStatusReason["TransactionTimedOutAtTheAcs"] = "TRANSACTION_TIMED_OUT_AT_THE_ACS";
        TransactionStatusReason["LowConfidence"] = "LOW_CONFIDENCE";
        TransactionStatusReason["MediumConfidence"] = "MEDIUM_CONFIDENCE";
        TransactionStatusReason["HighConfidence"] = "HIGH_CONFIDENCE";
        TransactionStatusReason["VeryHighConfidence"] = "VERY_HIGH_CONFIDENCE";
        TransactionStatusReason["ExceedsAcsMaximumChallenges"] = "EXCEEDS_ACS_MAXIMUM_CHALLENGES";
        TransactionStatusReason["NonPaymentTransactionNotSupported"] = "NON_PAYMENT_TRANSACTION_NOT_SUPPORTED";
        TransactionStatusReason["ThreeriTransactionNotSupported"] = "THREERI_TRANSACTION_NOT_SUPPORTED";
    })(exports.TransactionStatusReason || (exports.TransactionStatusReason = {}));
    function colorDepth(value) {
        var result = "";
        switch (value) {
            case 1:
                return "ONE_BIT";
            case 2:
                result += "TWO";
                break;
            case 4:
                result += "FOUR";
                break;
            case 8:
                result += "EIGHT";
                break;
            case 15:
                result += "FIFTEEN";
                break;
            case 16:
                result += "SIXTEEN";
                break;
            case 24:
            case 30:
                result += "TWENTY_FOUR";
                break;
            case 32:
                result += "THIRTY_TWO";
                break;
            case 48:
                result += "FORTY_EIGHT";
                break;
            default:
                throw new Error("Unknown color depth '" + value + "'");
        }
        return result + "_BITS";
    }
    function dimensionsFromChallengeWindowSize(options) {
        var height = 0;
        var width = 0;
        switch (options.size || options.windowSize) {
            case exports.ChallengeWindowSize.Windowed250x400:
                height = 250;
                width = 400;
                break;
            case exports.ChallengeWindowSize.Windowed390x400:
                height = 390;
                width = 400;
                break;
            case exports.ChallengeWindowSize.Windowed500x600:
                height = 500;
                width = 600;
                break;
            case exports.ChallengeWindowSize.Windowed600x400:
                height = 600;
                width = 400;
                break;
        }
        return { height: height, width: width };
    }
    function messageCategoryFromAuthenticationRequestType(authenticationRequestType) {
        switch (authenticationRequestType) {
            case exports.AuthenticationRequestType.AddCard:
            case exports.AuthenticationRequestType.CardholderVerification:
            case exports.AuthenticationRequestType.MaintainCard:
                return exports.MessageCategory.NonPayment;
            case exports.AuthenticationRequestType.InstalmentTransaction:
            case exports.AuthenticationRequestType.PaymentTransaction:
            case exports.AuthenticationRequestType.RecurringTransaction:
            default:
                return exports.MessageCategory.Payment;
        }
    }

    var GPError = /** @class */ (function (_super) {
        __extends(GPError, _super);
        function GPError(reasons, message) {
            var _this = _super.call(this, message || "Error: see `reasons` property") || this;
            _this.error = true;
            _this.reasons = reasons;
            return _this;
        }
        return GPError;
    }(Error));

    function handleNotificationMessageEvent(event, data, origin) {
        if (window.parent !== window) {
            window.parent.postMessage({ data: data, event: event }, origin || window.location.origin);
        }
    }

    function makeRequest(endpoint, data) {
        return __awaiter(this, void 0, void 0, function () {
            var headers, rawResponse, _a, e_1, reasons;
            var _b;
            return __generator(this, function (_c) {
                switch (_c.label) {
                    case 0:
                        headers = {
                            "Content-Type": "application/json",
                        };
                        _c.label = 1;
                    case 1:
                        _c.trys.push([1, 6, , 7]);
                        return [4 /*yield*/, fetch(endpoint, {
                                body: JSON.stringify(data),
                                credentials: "omit",
                                headers: typeof Headers !== "undefined" ? new Headers(headers) : headers,
                                method: "POST",
                            })];
                    case 2:
                        rawResponse = _c.sent();
                        if (!!rawResponse.ok) return [3 /*break*/, 4];
                        _a = GPError.bind;
                        _b = {
                            code: rawResponse.status.toString()
                        };
                        return [4 /*yield*/, rawResponse.text()];
                    case 3: throw new (_a.apply(GPError, [void 0, [
                            (_b.message = _c.sent(),
                                _b)
                        ], rawResponse.statusText]))();
                    case 4: return [4 /*yield*/, rawResponse.json()];
                    case 5: return [2 /*return*/, _c.sent()];
                    case 6:
                        e_1 = _c.sent();
                        reasons = [{ code: e_1.name, message: e_1.message }];
                        if (e_1.reasons) {
                            reasons = reasons.concat(e_1.reasons);
                        }
                        throw new GPError(reasons);
                    case 7: return [2 /*return*/];
                }
            });
        });
    }

    // most of this module is pulled from the legacy Realex Payments JavaScript library
    var isWindowsMobileOs = /Windows Phone|IEMobile/.test(navigator.userAgent);
    var isAndroidOrIOs = /Android|iPad|iPhone|iPod/.test(navigator.userAgent);
    var isMobileXS = ((window.innerWidth > 0 ? window.innerWidth : screen.width) <= 360
        ? true
        : false) ||
        ((window.innerHeight > 0 ? window.innerHeight : screen.height) <= 360
            ? true
            : false);
    // For IOs/Android and small screen devices always open in new tab/window
    // TODO: Confirm/implement once sandbox support is in place
    var isMobileNewTab = !isWindowsMobileOs && (isAndroidOrIOs || isMobileXS);
    // Display IFrame on WIndows Phone OS mobile devices
    var isMobileIFrame = isWindowsMobileOs || isMobileNewTab;
    var randomId = Math.random()
        .toString(16)
        .substr(2, 8);
    function createLightbox(iFrame, options) {
        // Create the overlay
        var overlayElement = createOverlay();
        // Create the spinner
        var spinner = createSpinner();
        document.body.appendChild(spinner);
        var _a = dimensionsFromChallengeWindowSize(options), height = _a.height, width = _a.width;
        // Configure the iframe
        if (height) {
            iFrame.setAttribute("height", height + "px");
        }
        iFrame.setAttribute("frameBorder", "0");
        if (width) {
            iFrame.setAttribute("width", width + "px");
        }
        iFrame.setAttribute("seamless", "seamless");
        iFrame.style.zIndex = "10001";
        iFrame.style.position = "absolute";
        iFrame.style.transition = "transform 0.5s ease-in-out";
        iFrame.style.transform = "scale(0.7)";
        iFrame.style.opacity = "0";
        overlayElement.appendChild(iFrame);
        if (isMobileIFrame || options.windowSize === exports.ChallengeWindowSize.FullScreen) {
            iFrame.style.top = "0px";
            iFrame.style.bottom = "0px";
            iFrame.style.left = "0px";
            iFrame.style.marginLeft = "0px;";
            iFrame.style.width = "100%";
            iFrame.style.height = "100%";
            iFrame.style.minHeight = "100%";
            iFrame.style.WebkitTransform = "translate3d(0,0,0)";
            iFrame.style.transform = "translate3d(0, 0, 0)";
            var metaTag = document.createElement("meta");
            metaTag.name = "viewport";
            metaTag.content =
                "width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0";
            document.getElementsByTagName("head")[0].appendChild(metaTag);
        }
        else {
            iFrame.style.top = "40px";
            iFrame.style.left = "50%";
            iFrame.style.marginLeft = "-" + width / 2 + "px";
        }
        iFrame.onload = getIFrameOnloadEventHandler(iFrame, spinner, overlayElement, options);
    }
    function closeModal() {
        Array.prototype.slice.call(document
            .querySelectorAll("[target$=\"-" + randomId + "\"],[id$=\"-" + randomId + "\"]"))
            .forEach(function (element) {
            if (element.parentNode) {
                element.parentNode.removeChild(element);
            }
        });
    }
    function createOverlay() {
        var overlay = document.createElement("div");
        overlay.setAttribute("id", "GlobalPayments-overlay-" + randomId);
        overlay.style.position = "fixed";
        overlay.style.width = "100%";
        overlay.style.height = "100%";
        overlay.style.top = "0";
        overlay.style.left = "0";
        overlay.style.transition = "all 0.3s ease-in-out";
        overlay.style.zIndex = "100";
        if (isMobileIFrame) {
            overlay.style.position = "absolute !important";
            overlay.style.WebkitOverflowScrolling = "touch";
            overlay.style.overflowX = "hidden";
            overlay.style.overflowY = "scroll";
        }
        document.body.appendChild(overlay);
        setTimeout(function () {
            overlay.style.background = "rgba(0, 0, 0, 0.7)";
        }, 1);
        return overlay;
    }
    function createCloseButton(options) {
        if (document.getElementById("GlobalPayments-frame-close-" + randomId) !== null) {
            return;
        }
        var closeButton = document.createElement("img");
        closeButton.id = "GlobalPayments-frame-close-" + randomId;
        closeButton.src =
            // tslint:disable-next-line:max-line-length
            "data:image/gif;base64,iVBORw0KGgoAAAANSUhEUgAAABEAAAARCAYAAAA7bUf6AAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyJpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuMy1jMDExIDY2LjE0NTY2MSwgMjAxMi8wMi8wNi0xNDo1NjoyNyAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENTNiAoV2luZG93cykiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6QUJFRjU1MEIzMUQ3MTFFNThGQjNERjg2NEZCRjFDOTUiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6QUJFRjU1MEMzMUQ3MTFFNThGQjNERjg2NEZCRjFDOTUiPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlpZDpBQkVGNTUwOTMxRDcxMUU1OEZCM0RGODY0RkJGMUM5NSIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDpBQkVGNTUwQTMxRDcxMUU1OEZCM0RGODY0RkJGMUM5NSIvPiA8L3JkZjpEZXNjcmlwdGlvbj4gPC9yZGY6UkRGPiA8L3g6eG1wbWV0YT4gPD94cGFja2V0IGVuZD0iciI/PlHco5QAAAHpSURBVHjafFRdTsJAEF42JaTKn4glGIg++qgX4AAchHAJkiZcwnAQD8AF4NFHCaC2VgWkIQQsfl/jNJUik8Duzs/XmW9mN7Xb7VRc5vP5zWKxaK5Wq8Zmu72FqobfJG0YQ9M0+/l8/qFQKDzGY1JxENd1288vLy1s786KRZXJZCLber1Wn7MZt4PLarVnWdZ9AmQ8Hncc17UvymVdBMB/MgPQm+cFFcuy6/V6lzqDf57ntWGwYdBIVx0TfkBD6I9M35iRJgfIoAVjBLDZbA4CiJ5+9AdQi/EahibqDTkQx6fRSIHcPwA8Uy9A9Gcc47Xv+w2wzhRDYzqdVihLIbsIiCvP1NNOoX/29FQx3vgOgtt4FyRdCgPRarX4+goB9vkyAMh443cOEsIAAcjncuoI4TXWMAmCIGFhCQLAdZ8jym/cRJ+Y5nC5XCYAhINKpZLgSISZgoqh5iiLQrojAFICVwGS7tCfe5DbZzkP56XS4NVxwvTI/vXVVYIDnqmnnX70ZxzjNS8THHooK5hMpxHQIREA+tEfA9djfHR3MHkdx3Hspe9r3B+VzWaj2RESyR2mlCUE4MoGQDdxiwHURq2t94+PO9bMIYyTyDNLwMoM7g8+BfKeYGniyw2MdfSehF3Qmk1IvCc/AgwAaS86Etp38bUAAAAASUVORK5CYII=";
        closeButton.style.transition = "all 0.5s ease-in-out";
        closeButton.style.opacity = "0";
        closeButton.style.float = "left";
        closeButton.style.position = "absolute";
        closeButton.style.left = "50%";
        closeButton.style.zIndex = "99999999";
        closeButton.style.top = "30px";
        var width = dimensionsFromChallengeWindowSize(options).width;
        if (width) {
            closeButton.style.marginLeft = width / 2 - 8 /* half image width */ + "px";
        }
        setTimeout(function () {
            closeButton.style.opacity = "1";
        }, 500);
        if (isMobileIFrame || options.windowSize === exports.ChallengeWindowSize.FullScreen) {
            closeButton.style.float = "right";
            closeButton.style.top = "20px";
            closeButton.style.left = "initial";
            closeButton.style.marginLeft = "0px";
            closeButton.style.right = "20px";
        }
        return closeButton;
    }
    function createSpinner() {
        var spinner = document.createElement("img");
        spinner.setAttribute("src", 
        // tslint:disable-next-line:max-line-length
        "data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiIHN0YW5kYWxvbmU9Im5vIj8+PHN2ZyB4bWxuczpzdmc9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB2ZXJzaW9uPSIxLjAiIHdpZHRoPSIzMnB4IiBoZWlnaHQ9IjMycHgiIHZpZXdCb3g9IjAgMCAxMjggMTI4IiB4bWw6c3BhY2U9InByZXNlcnZlIj48Zz48cGF0aCBkPSJNMzguNTIgMzMuMzdMMjEuMzYgMTYuMkE2My42IDYzLjYgMCAwIDEgNTkuNS4xNnYyNC4zYTM5LjUgMzkuNSAwIDAgMC0yMC45OCA4LjkyeiIgZmlsbD0iIzAwNzBiYSIgZmlsbC1vcGFjaXR5PSIxIi8+PHBhdGggZD0iTTM4LjUyIDMzLjM3TDIxLjM2IDE2LjJBNjMuNiA2My42IDAgMCAxIDU5LjUuMTZ2MjQuM2EzOS41IDM5LjUgMCAwIDAtMjAuOTggOC45MnoiIGZpbGw9IiNjMGRjZWUiIGZpbGwtb3BhY2l0eT0iMC4yNSIgdHJhbnNmb3JtPSJyb3RhdGUoNDUgNjQgNjQpIi8+PHBhdGggZD0iTTM4LjUyIDMzLjM3TDIxLjM2IDE2LjJBNjMuNiA2My42IDAgMCAxIDU5LjUuMTZ2MjQuM2EzOS41IDM5LjUgMCAwIDAtMjAuOTggOC45MnoiIGZpbGw9IiNjMGRjZWUiIGZpbGwtb3BhY2l0eT0iMC4yNSIgdHJhbnNmb3JtPSJyb3RhdGUoOTAgNjQgNjQpIi8+PHBhdGggZD0iTTM4LjUyIDMzLjM3TDIxLjM2IDE2LjJBNjMuNiA2My42IDAgMCAxIDU5LjUuMTZ2MjQuM2EzOS41IDM5LjUgMCAwIDAtMjAuOTggOC45MnoiIGZpbGw9IiNjMGRjZWUiIGZpbGwtb3BhY2l0eT0iMC4yNSIgdHJhbnNmb3JtPSJyb3RhdGUoMTM1IDY0IDY0KSIvPjxwYXRoIGQ9Ik0zOC41MiAzMy4zN0wyMS4zNiAxNi4yQTYzLjYgNjMuNiAwIDAgMSA1OS41LjE2djI0LjNhMzkuNSAzOS41IDAgMCAwLTIwLjk4IDguOTJ6IiBmaWxsPSIjYzBkY2VlIiBmaWxsLW9wYWNpdHk9IjAuMjUiIHRyYW5zZm9ybT0icm90YXRlKDE4MCA2NCA2NCkiLz48cGF0aCBkPSJNMzguNTIgMzMuMzdMMjEuMzYgMTYuMkE2My42IDYzLjYgMCAwIDEgNTkuNS4xNnYyNC4zYTM5LjUgMzkuNSAwIDAgMC0yMC45OCA4LjkyeiIgZmlsbD0iI2MwZGNlZSIgZmlsbC1vcGFjaXR5PSIwLjI1IiB0cmFuc2Zvcm09InJvdGF0ZSgyMjUgNjQgNjQpIi8+PHBhdGggZD0iTTM4LjUyIDMzLjM3TDIxLjM2IDE2LjJBNjMuNiA2My42IDAgMCAxIDU5LjUuMTZ2MjQuM2EzOS41IDM5LjUgMCAwIDAtMjAuOTggOC45MnoiIGZpbGw9IiNjMGRjZWUiIGZpbGwtb3BhY2l0eT0iMC4yNSIgdHJhbnNmb3JtPSJyb3RhdGUoMjcwIDY0IDY0KSIvPjxwYXRoIGQ9Ik0zOC41MiAzMy4zN0wyMS4zNiAxNi4yQTYzLjYgNjMuNiAwIDAgMSA1OS41LjE2djI0LjNhMzkuNSAzOS41IDAgMCAwLTIwLjk4IDguOTJ6IiBmaWxsPSIjYzBkY2VlIiBmaWxsLW9wYWNpdHk9IjAuMjUiIHRyYW5zZm9ybT0icm90YXRlKDMxNSA2NCA2NCkiLz48YW5pbWF0ZVRyYW5zZm9ybSBhdHRyaWJ1dGVOYW1lPSJ0cmFuc2Zvcm0iIHR5cGU9InJvdGF0ZSIgdmFsdWVzPSIwIDY0IDY0OzQ1IDY0IDY0OzkwIDY0IDY0OzEzNSA2NCA2NDsxODAgNjQgNjQ7MjI1IDY0IDY0OzI3MCA2NCA2NDszMTUgNjQgNjQiIGNhbGNNb2RlPSJkaXNjcmV0ZSIgZHVyPSIxMjgwbXMiIHJlcGVhdENvdW50PSJpbmRlZmluaXRlIj48L2FuaW1hdGVUcmFuc2Zvcm0+PC9nPjwvc3ZnPg==");
        spinner.setAttribute("id", "GlobalPayments-loader-" + randomId);
        spinner.style.left = "50%";
        spinner.style.position = "fixed";
        spinner.style.background = "#FFFFFF";
        spinner.style.borderRadius = "50%";
        spinner.style.width = "30px";
        spinner.style.zIndex = "200";
        spinner.style.marginLeft = "-15px";
        spinner.style.top = "120px";
        return spinner;
    }
    function getIFrameOnloadEventHandler(iFrame, spinner, overlayElement, options) {
        return function () {
            iFrame.style.opacity = "1";
            iFrame.style.transform = "scale(1)";
            iFrame.style.backgroundColor = "#ffffff";
            if (spinner.parentNode) {
                spinner.parentNode.removeChild(spinner);
            }
            var closeButton;
            closeButton = createCloseButton(options);
            if (closeButton) {
                overlayElement.appendChild(closeButton);
                closeButton.addEventListener("click", function () {
                    if (closeButton) {
                        closeModal();
                    }
                }, true);
            }
        };
    }

    function postToIframe(endpoint, fields, options) {
        return new Promise(function (resolve, reject) {
            var timeout;
            if (options.timeout) {
                timeout = setTimeout(function () {
                    ensureIframeClosed(timeout);
                    reject(new Error("timeout reached"));
                }, options.timeout);
            }
            var iframe = document.createElement("iframe");
            iframe.id = iframe.name = "GlobalPayments-3DSecure-" + randomId;
            iframe.style.display = options.hide ? "none" : "inherit";
            var form = createForm(endpoint, iframe.id, fields);
            switch (options.displayMode) {
                case "redirect":
                    // TODO: Add redirect support once sandbox environment respects configured
                    // challengeNotificationUrl instead of hardcoded value
                    ensureIframeClosed(timeout);
                    reject(new Error("Not implemented"));
                    return;
                case "lightbox":
                    createLightbox(iframe, options);
                    break;
                case "embedded":
                default:
                    if (!handleEmbeddedIframe(reject, { iframe: iframe, timeout: timeout }, options)) {
                        // rejected
                        return;
                    }
                    break;
            }
            window.addEventListener("message", getWindowMessageEventHandler(resolve, {
                origin: options.origin,
                timeout: timeout,
            }));
            document.body.appendChild(form);
            form.submit();
        });
    }
    function createForm(action, target, fields) {
        var form = document.createElement("form");
        form.setAttribute("method", "POST");
        form.setAttribute("action", action);
        form.setAttribute("target", target);
        for (var _i = 0, fields_1 = fields; _i < fields_1.length; _i++) {
            var field = fields_1[_i];
            var input = document.createElement("input");
            input.setAttribute("type", "hidden");
            input.setAttribute("name", field.name);
            input.setAttribute("value", field.value);
            form.appendChild(input);
        }
        return form;
    }
    function ensureIframeClosed(timeout) {
        if (timeout) {
            clearTimeout(timeout);
        }
        try {
            Array.prototype.slice.call(document
                .querySelectorAll("[target$=\"-" + randomId + "\"],[id$=\"-" + randomId + "\"]"))
                .forEach(function (element) {
                if (element.parentNode) {
                    element.parentNode.removeChild(element);
                }
            });
        }
        catch (e) {
            /** */
        }
    }
    function getWindowMessageEventHandler(resolve, data) {
        return function (e) {
            var origin = data.origin || window.location.origin;
            if (origin !== e.origin) {
                return;
            }
            ensureIframeClosed(data.timeout || 0);
            resolve(e.data);
        };
    }
    function handleEmbeddedIframe(reject, data, options) {
        var targetElement;
        if (options.hide) {
            targetElement = document.body;
        }
        else if (typeof options.target === "string") {
            targetElement = document.querySelector(options.target);
        }
        else {
            targetElement = options.target;
        }
        if (!targetElement) {
            ensureIframeClosed(data.timeout || 0);
            reject(new Error("Embed target not found"));
            return false;
        }
        var _a = dimensionsFromChallengeWindowSize(options), height = _a.height, width = _a.width;
        if (data.iframe) {
            data.iframe.setAttribute("height", height ? height + "px" : "100%");
            data.iframe.setAttribute("width", width ? width + "px" : "100%");
            targetElement.appendChild(data.iframe);
        }
        return true;
    }

    /**
     * Retrieves client browser runtime data.
     */
    function getBrowserData() {
        var now = new Date();
        return {
            colorDepth: screen && colorDepth(screen.colorDepth),
            javaEnabled: navigator && navigator.javaEnabled(),
            javascriptEnabled: true,
            language: navigator && navigator.language,
            screenHeight: screen && screen.height,
            screenWidth: screen && screen.width,
            time: now,
            timezoneOffset: now.getTimezoneOffset() / 60,
            userAgent: navigator && navigator.userAgent,
        };
    }
    /**
     * Facilitates backend request to merchant integration to check the enrolled 3DS version for the consumer's card.
     *
     * @param endpoint Merchant integration endpoint responsible for performing the version check
     * @param data Request data to aid in version check request
     * @throws When an error occurred during the request
     */
    function checkVersion(endpoint, data) {
        return __awaiter(this, void 0, void 0, function () {
            var response, e_1, reasons;
            return __generator(this, function (_a) {
                switch (_a.label) {
                    case 0:
                        data = data || {};
                        _a.label = 1;
                    case 1:
                        _a.trys.push([1, 4, , 5]);
                        return [4 /*yield*/, makeRequest(endpoint, data)];
                    case 2:
                        response = (_a.sent());
                        return [4 /*yield*/, handle3dsVersionCheck(response, data.methodWindow)];
                    case 3: return [2 /*return*/, _a.sent()];
                    case 4:
                        e_1 = _a.sent();
                        reasons = [{ code: e_1.name, message: e_1.message }];
                        if (e_1.reasons) {
                            reasons = reasons.concat(e_1.reasons);
                        }
                        throw new GPError(reasons);
                    case 5: return [2 /*return*/];
                }
            });
        });
    }
    /**
     * Facilitates backend request to merchant integration to initiate 3DS 2.0 authentication workflows with the consumer.
     *
     * @param endpoint Merchant integration endpoint responsible for initiating the authentication request
     * @param data Request data to aid in initiating authentication
     * @throws When an error occurred during the request
     */
    function initiateAuthentication(endpoint, data) {
        return __awaiter(this, void 0, void 0, function () {
            var response, e_2, reasons;
            return __generator(this, function (_a) {
                switch (_a.label) {
                    case 0:
                        _a.trys.push([0, 3, , 4]);
                        data.authenticationSource =
                            data.authenticationSource || exports.AuthenticationSource.Browser;
                        data.authenticationRequestType =
                            data.authenticationRequestType ||
                                exports.AuthenticationRequestType.PaymentTransaction;
                        data.messageCategory =
                            data.messageCategory ||
                                messageCategoryFromAuthenticationRequestType(data.authenticationRequestType);
                        data.challengeRequestIndicator =
                            data.challengeRequestIndicator || exports.ChallengeRequestIndicator.NoPreference;
                        // still needs ip address and accept header from server-side
                        data.browserData = data.browserData || getBrowserData();
                        return [4 /*yield*/, makeRequest(endpoint, data)];
                    case 1:
                        response = (_a.sent());
                        return [4 /*yield*/, handleInitiateAuthentication(response, data.challengeWindow)];
                    case 2: return [2 /*return*/, _a.sent()];
                    case 3:
                        e_2 = _a.sent();
                        reasons = [{ code: e_2.name, message: e_2.message }];
                        if (e_2.reasons) {
                            reasons = reasons.concat(e_2.reasons);
                        }
                        throw new GPError(reasons);
                    case 4: return [2 /*return*/];
                }
            });
        });
    }
    /**
     * Handles response from merchant integration endpoint for the version check request. When a card is enrolled and a
     * method URL is present, a hidden iframe to the method URL will be created to handle device fingerprinting
     * requirements.
     *
     * @param data Version check data from merchant integration endpoint
     * @param options Configuration options for the method window
     * @throws When a card is not enrolled
     */
    function handle3dsVersionCheck(data, options) {
        return __awaiter(this, void 0, void 0, function () {
            return __generator(this, function (_a) {
                switch (_a.label) {
                    case 0:
                        if (!data.enrolled) {
                            throw new Error("Card not enrolled");
                        }
                        options = options || {};
                        options.hide = typeof options.hide === "undefined" ? true : options.hide;
                        options.timeout =
                            typeof options.timeout === "undefined" ? 30 * 1000 : options.timeout;
                        if (!data.methodUrl) return [3 /*break*/, 2];
                        return [4 /*yield*/, postToIframe(data.methodUrl, [{ name: "threeDSMethodData", value: data.methodData }], options)];
                    case 1:
                        _a.sent();
                        _a.label = 2;
                    case 2: return [2 /*return*/, data];
                }
            });
        });
    }
    /**
     * Handles response from merchant integration endpoint for initiating 3DS 2.0 authentication flows with consumer. If a
     * challenge is mandated, an iframe will be created for the issuer's necessary challenge URL.
     *
     * @param data Initiate authentication data from merchant integration endpoint
     * @param options Configuration options for the challenge window
     * @throws When a challenge is mandated but no challenge URL was supplied
     * @throws When an error occurred during the challenge request
     */
    function handleInitiateAuthentication(data, options) {
        return __awaiter(this, void 0, void 0, function () {
            var response;
            return __generator(this, function (_a) {
                switch (_a.label) {
                    case 0:
                        if (!(data.challengeMandated || data.status === exports.TransactionStatus.ChallengeRequired)) return [3 /*break*/, 2];
                        data.challenge = data.challenge || {};
                        if (!data.challenge.requestUrl) {
                            throw new Error("Invalid challenge state. Missing challenge URL");
                        }
                        return [4 /*yield*/, postToIframe(data.challenge.requestUrl, [
                                { name: "creq", value: data.challenge.encodedChallengeRequest },
                                //{ name: "PaReq", value: data.challenge.encodedChallengeRequest },
                            ], options)];
                    case 1:
                        response = _a.sent();
                        data.challenge.response = response;
                        _a.label = 2;
                    case 2: return [2 /*return*/, data];
                }
            });
        });
    }
    /**
     * Assists with notifying the parent window of challenge status
     *
     * @param data Raw data from the challenge notification
     * @param origin Target origin for the message. Default is `window.location.origin`.
     */
    function handleChallengeNotification(data, origin) {
        handleNotificationMessageEvent("challengeNotification", data, origin);
    }
    /**
     * Assists with notifying the parent window of method status
     *
     * @param data Raw data from the method notification
     * @param origin Target origin for the message. Default is `window.location.origin`.
     */
    function handleMethodNotification(data, origin) {
        handleNotificationMessageEvent("methodNotification", data, origin);
    }

    exports.checkVersion = checkVersion;
    exports.colorDepth = colorDepth;
    exports.dimensionsFromChallengeWindowSize = dimensionsFromChallengeWindowSize;
    exports.getBrowserData = getBrowserData;
    exports.handle3dsVersionCheck = handle3dsVersionCheck;
    exports.handleChallengeNotification = handleChallengeNotification;
    exports.handleInitiateAuthentication = handleInitiateAuthentication;
    exports.handleMethodNotification = handleMethodNotification;
    exports.initiateAuthentication = initiateAuthentication;
    exports.messageCategoryFromAuthenticationRequestType = messageCategoryFromAuthenticationRequestType;

    Object.defineProperty(exports, '__esModule', { value: true });

    return exports;

}({}));
//# sourceMappingURL=globalpayments-3ds.js.map
