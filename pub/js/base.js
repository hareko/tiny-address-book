/**
 * vRegistrator base functionality
 *
 * @package     System
 * @author      Vallo Reima
 * @copyright   (C)2010
 */
var ts;        /* transit data object */

function Transit()
  /*
   * transit data class
   * encode/decode & save data, get/set values
   */
  {
    var transmit = {};  /* data storage */
    var that = this;

    this.Enc = function(data, flag)
      /*
       *  convert data to transit format
       *  in: data -- array
       *      flag -- true - urlencode
       */
      {
        var d = JSON.stringify(data);
        if (flag === true) {
          d = urlencode(d);
        }
        return d;
      };

    this.Dec = function(data, flag)
      /*
       *  convert data from transit format
       *  in: data -- stringified string
       *      flag -- true - urldecode string
       */
      {
        var a;
        var d = data;
        try {
          if (flag === true) {
            d = urldecode(d);
          }
          a = JSON.parse(d);
        } catch (e) {
          a = {};
        }
        return a;
      };

    this.Keep = function(data)
      /*
       * in: data - array
       */
      {
        for (var c in data) {
          transmit[c] = data[c];
        }
      };

    var obj = $$('transit', 'div')[0];
    var data = that.Dec(obj.innerHTML, true);
    data.gap = '&nbsp;';  /* html space */
    data.br = '<br />';  /* html break */
    that.Keep(data);
    obj.parentNode.removeChild(obj);

    this.Get = function(name)
      /*
       * get value
       * in: name - data name
       */
      {
        if (typeof transmit[name] == "undefined") {
          return null;
        } else {
          return transmit[name];
        }
      };

    this.Set = function(name, value)
      /*
       * set value
       * in: name - data name
       *     value - data value
       */
      {
        transmit[name] = value;
      };
  }

function FindPos(obj)
  /* 
   * find object's real position
   * in: obj - element
   * out: left & top offsets
   */
  {
    var curleft = 0;
    var curtop = 0;
    if (obj.offsetParent) {
      var o = obj;
      do {
        curleft += o.offsetLeft;
        curtop += o.offsetTop;
        o = o.offsetParent
      } while (o);
    }
    return [curleft, curtop];
  }

function FindParent(obj, val, trg)
  /* 
   * find object's parent
   * in:  obj - element object
   *      val - target value to find
   *      trg - target to find (tag,id,...)
   */
  {
    var r = null;
    var v = val.toLowerCase();
    var t = IsSet(trg) ? trg : 'tagName';
    var o = obj.parentNode;
    do {
      if (o[t].toLowerCase() == v) {
        r = o;
        break;
      }
      o = o.parentNode;
    } while (o.tagName);
    return r;
  }

function Width()
{
  return document.documentElement && document.documentElement.clientWidth ? document.documentElement.clientWidth :
    window.innerWidth != null ? window.innerWidth : document.body != null ? document.body.clientWidth : null;
}

function Height()
{
  return document.documentElement && document.documentElement.clientHeight ? document.documentElement.clientHeight :
    window.innerHeight != null ? window.innerHeight : document.body != null ? document.body.clientHeight : null;
}
function LeftPos()
{
  return typeof window.pageXOffset != 'undefined' ? window.pageXOffset :
    document.documentElement && document.documentElement.scrollLeft ? document.documentElement.scrollLeft :
    document.body.scrollLeft ? document.body.scrollLeft : 0;
}

function TopPos()
{
  return typeof window.pageYOffset != 'undefined' ? window.pageYOffset :
    document.documentElement && document.documentElement.scrollTop ? document.documentElement.scrollTop :
    document.body.scrollTop ? document.body.scrollTop : 0;
}

function ScrollTop()
{
  return document.body.scrollTop ? document.body.scrollTop :
    document.documentElement.scrollTop;
}

function ScrollLeft()
{
  return document.body.scrollLeft ? document.body.scrollLeft :
    document.documentElement.scrollLeft;
}

function $(id, obj)
  /*
   *  Get element by Id
   */
  {
    if (typeof obj == 'undefined') {
      var o = document;
    } else {
      o = obj.document;
    }
    return o.getElementById(id);
  }

function $$(obj, tag)
  /*
   *  Get elements by object tag name
   */
  {
    var o = (typeof obj == 'string') ? document.getElementById(obj) : obj;
    return o.getElementsByTagName(tag);
  }

function AttachEventListener(target, eventType, functionRef, capture)
  /*
   * Cross-browser method
   * in: target - element object
   *     eventType - click, ...
   *     functionRef - handler
   *     capture -- false - bubble (default)
   *                true - propagation
   */
  {
    if (typeof capture == 'undefined') {
      capture = false;
    }
    if (target.addEventListener) {
      target.addEventListener(eventType, functionRef, capture);
    } else if (target.attachEvent) {
      target.attachEvent('on' + eventType, functionRef);
    } else {
      target['on' + eventType] = functionRef;
    }
  }

function DetachEventListener(target, eventType, functionRef, capture)
{
  if (typeof capture == 'undefined') {
    capture = false;
  }
  if (target.removeEventListener) {
    target.removeEventListener(eventType, functionRef, capture)
  } else if (target.detachEvent) {
    target.detachEvent('on' + eventType, functionRef);
  } else {
    target['on' + eventType] = null;
  }
}

function _AttachEventListener(target, eventType, functionRef, capture)
  /*
   * Cross-browser method
   * in: target - element id
   *     eventType - click, ...
   *     functionRef - handler
   *     capture -- false - bubble (default)
   *                true - propagation
   */
  {
    if (typeof capture == "undefined") {
      capture = false;
    }
    if (typeof target.addEventListener != "undefined") {
      target.addEventListener(eventType, functionRef, capture);
    } else if (typeof target.attachEvent != "undefined") {
      var functionString = eventType + functionRef;
      target["e" + functionString] = functionRef;
      target[functionString] = function(event)
      {
        if (typeof event == "undefined") {
          event = window.event;
        }
        target["e" + functionString](event);
      };
      target.attachEvent("on" + eventType, target[functionString]);
    } else {
      eventType = "on" + eventType;
      if (typeof target[eventType] == "function") {
        var oldListener = target[eventType];
        target[eventType] = function()
        {
          oldListener();
          return functionRef();
        }
      } else {
        target[eventType] = functionRef;
      }
    }
  }

function _DetachEventListener(target, eventType, functionRef, capture)
{
  if (typeof capture == "undefined") {
    capture = false;
  }
  if (typeof target.removeEventListener != "undefined") {
    target.removeEventListener(eventType, functionRef, capture)
  } else if (typeof target.detachEvent != "undefined") {
    var functionString = eventType + functionRef;
    target.detachEvent("on" + eventType, target[functionString]);
    target["e" + functionString] = null;
    target[functionString] = null;
  } else {
    target["on" + eventType] = null;
  }
}

function StopEvent(event, flag)
  /*
   * Prevent the Default Action for an Event
   * in: event - object
   *     flag -- true - don't cancel bubble
   */
  {
    var e = event ? event : window.event;
    e.returnValue = false;
    if (flag !== true) {
      e.cancelBubble = true;
      if (e.stopPropagation) {
        e.stopPropagation();
        e.preventDefault();
      }
    }
    return false;
    /*
     oEvent.returnValue = false;
     if (oEvent.preventDefault) {
     oEvent.preventDefault();
     }
     */
  }

function Target(e)
{
  return (window.event) ? e.srcElement : e.target;
}

function EventType(event)
{
  var e = event || window.event;
  return e.type.toLowerCase();
}

function KeyCode(event)
{
  var e = event || window.event;
  return (e.which || e.keyCode || e.charCode);
//  return (e.keyCode ? e.keyCode : e.charCode);
}

function IsSet(variable)
  /*
   *  Check variable is set
   */
  {
    return (typeof variable != 'undefined');
  }

function Empty(varMixed)
  /*
   * Check empty variable
   */
  {
    if (typeof varMixed == 'object') {
      for (var i in varMixed) {
        return false;
      }
      return true;
    } else {
      return (varMixed === ""
        || varMixed === 0
        || varMixed === "0"
        || varMixed === null
        || varMixed === false
        || varMixed === undefined
        )
    }
  }

function FrameDoc(obj)
  /*
   * get frame document object
   * in: obj -- frame object
   */
  {
    if (obj.contentDocument) {
      var doc = obj.contentDocument;
    } else if (obj.contentWindow) {
      doc = obj.contentWindow.document;
    } else {
      doc = null;
    }
    return doc;
  }

function SelfFocus()
  /*
   * focus window
   * in: obj -- frame object
   */
  {
    self.focus();
    if (BrowserName() == 'CR') {
      self.open('', '_self');
    }
  }

function BrowserName()
  /*
   * get a browser name
   */
  {
    var c = navigator.userAgent;
    if (c.indexOf('MSIE') != -1) {
      c = 'IE';
    } else if (c.indexOf('Firefox') != -1) {
      c = 'FF';
    } else if (c.indexOf('Chrome') != -1) {
      c = 'CR';
    } else if (c.indexOf('Safari') != -1) {
      c = 'SF';
    } else if (c.indexOf('Opera') != -1) {
      c = 'OP';
    } else {
      c = navigator.appName;
    }
    return c;
  }

function GetHTTPObject()
  /*
   * if returns an object, the browser is Ajax compatible
   * otherwise error string is returned
   */
  {
    var obj = false;//set to false, so if it fails, do nothing
    if (window.XMLHttpRequest) {//detect to see if browser allows this method
      obj = new XMLHttpRequest();//set var the new request
    } else if (window.ActiveXObject) {//detect to see if browser allows this method
      try {
        obj = new ActiveXObject("Msxml2.XMLHTTP");//try this method first
      } catch (e) {//if it fails move onto the next
        try {
          obj = new ActiveXObject("Microsoft.XMLHTTP");//try this method next
        } catch (e) {//if that also fails return false.
          obj = e.description;
        }
      }
    }
    return obj;
  }

function evil(code) {
  return eval(code);
}

function GetButton(event)
  /*
   * Get the current mouse button
   * in: e - event
   * out: L,M,R - button sign
   */
  {
    var e = event || window.event;
    var button;
    if (e.which == null) {
      button = (e.button < 2) ? "L" : ((e.button == 4) ? "M" : "R");
    } else {
      button = (e.which < 2) ? "L" : ((e.which == 2) ? "M" : "R");
    }
    return button;
  }


function GetButtonPos(event)
  /* Get current mouse position */
  {
    var e = event || window.event;
    var posx = 0;
    var posy = 0;
    if (e.pageX || e.pageY) {
      posx = e.pageX;
      posy = e.pageY;
    } else if (e.clientX || e.clientY) {
      posx = e.clientX + document.body.scrollLeft + document.documentElement.scrollLeft;
      posy = e.clientY + document.body.scrollTop + document.documentElement.scrollTop;
    }
    return [posx, posy];
  }

function SetHash(h)
// replace url hash
{
  location.hash = h;
}

function GetHash()
// read url hash
{
  var h = location.hash.split('#')[1] || '';
  return h;
}

function LocalTimeOffset()
// client timezone offset relative to UTC
{
  var date = new Date();
  return -date.getTimezoneOffset();
}

function TimeAdd(time, span)
  /*
   * calculate time
   * in:  time -- hh:ss
   *      span -- minutes to add
   */
  {
    var a = time.split(/:/);
    var b = [parseInt(a[0], 10), parseInt(a[1], 10) + parseInt(span, 10)];
    a = [b[0] + Math.floor(b[1] / 60), b[1] % 60];
    var c = Pad(a[0].toString(10), 2, '0', 1) + ':' + Pad(a[1].toString(10), 2, '0', 1);
    return c
  }

function DaysInMonth(date)
  /*
   * get number of days in month
   * in:  date -- any date in the month - object
   */
  {
    var m = date.getMonth() + 1;
    var y = date.getFullYear()
    var d = new Date(y, m, 0);
    return d.getDate();
  }

function GetMonday(date)
  /*
   * get the first day of the week
   * in:  date -- any date in the month - object
   */
  {
    var day = date.getDay();
    var diff = date.getDate() - day + (day == 0 ? -6 : 1); /* adjust when day is sunday */
    return new Date(date.setDate(diff));
  }

Date.prototype.getWeek = function() 
/*
 * http://tech-hacks.net/tech/19/get-the-weeknumber-with-javascript/
 */
{ 
    var determinedate = new Date(); 
    determinedate.setFullYear(this.getFullYear(), this.getMonth(), this.getDate()); 
    var D = determinedate.getDay(); 
    if(D == 0) D = 7; 
    determinedate.setDate(determinedate.getDate() + (4 - D)); 
    var YN = determinedate.getFullYear(); 
    var ZBDoCY = Math.floor((determinedate.getTime() - new Date(YN, 0, 1, -6)) / 86400000); 
    var WN = 1 + Math.floor(ZBDoCY / 7); 
    return WN; 
}


function IsEmail(val, flg) {
  var ptn = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/;
  return (flg && !val) || ptn.test(Trim(val, 'r'));
}

function IsPhone(phn, cry)
  /*
   * check phone number correctness
   * out: true -- correct or empty
   */
  {
    var v = phn.replace(/ |\-|\./g, '');
    if (Empty(cry) && v.substr(0, 1) == '+') {
      v = v.substr(1);
    }
    var l = v.length;
    return (l == 0 || (IsNumeric(v) && l > 5 && phn.length < 16));
  }

function IsArray(varMixed) {
  return (typeof varMixed == 'object') && (varMixed instanceof Array);
}

function ArrayKeys(h) {
  var i, a = [];
  for (i in h)
    a.push(i);
  return a;
}

function ArraySort(a) {
  var k = ArrayKeys(a);
  k.sort();
  var s = {};
  for (var i in k) {
    s[k[i]] = a[k[i]];
  }
  return s;
}

function ArrayMerge()
  /*
   * Merge associative arrays
   */
  {
    var destination = {};
    for (var i = 0; i < arguments.length; i++) {
      var source = arguments[i];
      for (var property in source) {
        destination[property] = source[property];
      }
    }
    return destination;
  }

function ArraySearch(needle, haystack, argStrict) {
  // Searches the array for a given value and returns the corresponding key if successful
  // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // *     example 1: array_search('zonneveld', {firstname: 'kevin', middle: 'van', surname: 'zonneveld'});
  // *     returns 1: 'surname'
  var strict = !!argStrict;
  var key = '';
  for (key in haystack) {
    if ((strict && haystack[key] === needle) || (!strict && haystack[key] == needle)) {
      return isNaN(Number(key)) ? key : parseInt(key);
    }
  }
  return false;
}
function ArrayFirst(varMixed)
  /*
   * Get 1st associative array element
   */
  {
    var c = '';
    if (typeof varMixed == 'object') {
      for (var o in varMixed) {
        if (varMixed.hasOwnProperty(o)) {
          c = o;
          break;
        }
      }
    }
    return c;
  }

function CloneObject(obj)
{
  if (typeof obj !== 'object' || obj === null) {
    return obj;
  }
  var c = obj instanceof Array ? [] : {};
  for (var i in obj) {
    if (obj.hasOwnProperty(i)) {
      c[i] = CloneObject(obj[i]);
    }
  }
  return c;
}

function Split(str, spr)
// split string to (empty) array
{
  if (!IsSet(str) || IsBlank(str)) {
    var a = [];
  } else {
    if (typeof spr == 'undefined') {
      spr = ',';
    }
    a = str.split(spr);
  }
  return a;
}

function Enable(obj, flg)
  /*
   * enable/disable foem element
   * in:  obj -- element object
   *      flg -- true - enable
   */
  {
    if (flg === true) {
      obj.removeAttribute('disabled');
      obj.disabled = false;
    } else {
      obj.setAttribute('disabled', 'disabled');
      obj.disabled = true;
    }
  }

function Sibling(obj, flg)
  /*
   * find sibling element
   * in:  obj -- current element object
   *      flg -- true - next
   *             false - previous
   */
  {
    var o = flg ? obj.nextSibling : obj.previousSibling;
    while (o && o.nodeType != 1)
    {
      o = flg ? o.nextSibling : o.previousSibling;
    }
    return o;
  }

function IsBlank(string)
  /*
   *  Check string blankness
   */
  {
    var blankRE = /^[\s]*$/;
    return blankRE.test(string);
  }

function Repeat(string, count)
  /*
   *  make repeating string 
   */
  {
    var c = [count + 1].join(string);
    return c;
  }

function Trim(string, flag)
  /*
   *  trim the string 
   *  flag: l -- left
   *        r -- right
   *          else both
   */
  {
    if (flag === undefined) {
      c = string.replace(/^\s+|\s+$/g, '');
    } else if (flag.toLowerCase() == 'l') {
      var c = string.replace(/^\s+/, '');
    } else if (flag.toLowerCase() == 'r') {
      c = string.replace(/\s+$/, '');
    }
    return c;
  }

function Pad(val, len, pad, dir)
  /**
   *
   *  Javascript string pad
   *  http://www.webtoolkit.info/
   *
   *  This functions returns the input string padded on the left, the right,
   *  or both sides to the specified padding length. If the optional argument "pad"
   *  is not supplied, the input is padded with spaces, otherwise it is padded with
   *  characters from "pad" up to the "len" length.
   **/
  {
    var STR_PAD_LEFT = 1;
    var STR_PAD_RIGHT = 2;
    var STR_PAD_BOTH = 3;
    if (typeof(len) == "undefined") {
      len = 0;
    }
    if (typeof(pad) == "undefined") {
      pad = ' ';
    }
    if (typeof(dir) == "undefined") {
      dir = STR_PAD_RIGHT;
    }
    var str = val.toString();
    if (len + 1 >= str.length) {
      switch (dir) {
        case STR_PAD_LEFT:
          str = Array(len + 1 - str.length).join(pad) + str;
          break;
        case STR_PAD_BOTH:
          var right = Math.ceil((padlen = len - str.length) / 2);
          var left = padlen - right;
          str = Array(left + 1).join(pad) + str + Array(right + 1).join(pad);
          break;
        default:
          str = str + Array(len + 1 - str.length).join(pad);
          break;
      } // switch
    }
    return str;
  }

function Left(str, n) {
  if (n <= 0)
    return "";
  else if (n > String(str).length)
    return str;
  else
    return String(str).substring(0, n);
}

function Right(str, n) {
  if (n <= 0)
    return "";
  else if (n > String(str).length)
    return str;
  else {
    var iLen = String(str).length;
    return String(str).substring(iLen, iLen - n);
  }
}

function ucfirst(str) {
  // Makes a string's first character uppercase
  str += '';
  var f = str.charAt(0).toUpperCase();
  return f + str.substr(1);
}

function GetType(val)
// Get php-style variable type
{
  var t = typeof val;
  switch (t) {
    case 'object':
      t = 'object';
      break;
    case 'string':
      t = 'string';
      break;
    case 'boolean':
      t = 'bool';
      break;
    case 'number':
      t = IsFloat(val) ? 'float' : 'int';
      break;
    default:
      t = 'null';
  }
  return t;
}

function IsInt(v) {
  var n = Number(v);
  return (n === ~~n);
}

function IsFloat(num)
// Detect whether num is floating-point
{
  return parseFloat(num * 1) != parseInt(num * 1, 10);
}

function IsNumber(val, tpe)
  /*
   * validate number
   * in:  val -- number value
   *      tpe -- type - nNzZ
   * 
   */
  {
    var f = !isNaN(val);
    if (f && typeof tpe != 'undefined') {
      var v = Number(val);
      var t = tpe.toUpperCase();
      if (v < 0 && t == tpe || v == 0 && tpe == 'n' || ('NZ'.indexOf(t) + 1 && v != parseInt(v))) {
        f = false;
      }
    }
    return f;
  }

function IsNumberType(tpe)
  /*
   * check if any number type
   * in:  tpe -- type - nNzZ
   */
  {
    return 'nNzZrR'.indexOf(tpe) + 1 ? true : false;
  }

function IsNumeric(num)
// Detect whether string is numeric
{
  return typeof num == 'string' && num.match(/^[0-9]+$/);
}

function CookieRead(name)
{
  if (document.cookie.length > 0) {
    var i = document.cookie.indexOf(name + "=");
    if (i != -1) {
      i = i + name.length + 1;
      var j = document.cookie.indexOf(";", i);
      if (j == -1) {
        j = document.cookie.length;
      }
      return document.cookie.substring(i, j);
    }
  }
  return "";
}

function CookieDelete(name) {
  CookieWrite(name, "", -1);
}

function DumpArray(arr)
  /*
   * Convert array contents to string
   */
  {
    var dump = 'Array:';
    for (var item in arr) {
      dump += "\r\n" + item + '=' + arr[item];
    }
    return dump;
  }
function CnvEnts(s, f)
  /*
   * replace entities with characters
   * in:  s - string
   *      f -- true - encode
   *           false - decode
   */
  {
    var ent = ['<br/>', '&amp;', '&gt;', '&lt;', '&quot;'];
    var cde = ["\n", '\x26', '\x3E', '\x3C', '\x22'];
    var c = s;
    if (f) {
      var e = ent;
      var r = cde;
    } else {
      e = cde;
      r = ent;
    }
    for (var i = 1; i < e.length; i++) {
      c = c.replace(new RegExp(e[i], 'g'), r[i]);
    }
    if (!f) {
      c = c.replace(new RegExp(e[0], 'g'), r[0]);
    }
    return c;
  }

function urlencode(str) {
  // @ http://kevin.vanzonneveld.net
  var histogram = {}, tmp_arr = [];
  var ret = str.toString();

  var replacer = function(search, replace, str) {
    var tmp_arr = [];
    tmp_arr = str.split(search);
    return tmp_arr.join(replace);
  };

  // The histogram is identical to the one in urldecode.
  histogram["'"] = '%27';
  histogram['('] = '%28';
  histogram[')'] = '%29';
  histogram['*'] = '%2A';
  histogram['~'] = '%7E';
  histogram['!'] = '%21';
  histogram['%20'] = '+';

  ret = encodeURIComponent(ret);

  for (search in histogram) {
    replace = histogram[search];
    ret = replacer(search, replace, ret) // Custom replace. No regexing
  }

  // Uppercase for full compatibility
  return ret.replace(/(\%([a-z0-9]{2}))/g, function(full, m1, m2) {
    return "%" + m2.toUpperCase();
  });

  return ret;
}
function urldecode(str) {
  // @ http://kevin.vanzonneveld.net

  var histogram = {};
  var ret = str.toString();

  var replacer = function(search, replace, str) {
    var tmp_arr = [];
    tmp_arr = str.split(search);
    return tmp_arr.join(replace);
  };

  // The histogram is identical to the one in urlencode.
  histogram["'"] = '%27';
  histogram['('] = '%28';
  histogram[')'] = '%29';
  histogram['*'] = '%2A';
  histogram['~'] = '%7E';
  histogram['!'] = '%21';
  histogram['%20'] = '+';

  for (replace in histogram) {
    search = histogram[replace]; // Switch order when decoding
    ret = replacer(search, replace, ret) // Custom replace. No regexing
  }

  ret = decodeURIComponent(ret);

  return ret;
}

function htmlspecialchars(str) {
  if (typeof(str) == "string") {
    str = str.replace(/&/g, "&amp;"); /* must do &amp; first */
    str = str.replace(/"/g, "&quot;");
    str = str.replace(/'/g, "&#039;");
    str = str.replace(/</g, "&lt;");
    str = str.replace(/>/g, "&gt;");
  }
  return str;
}

function htmlspecialchars_decode(str) {
  if (typeof(str) == "string") {
    str = str.replace(/&gt;/ig, ">");
    str = str.replace(/&lt;/ig, "<");
    str = str.replace(/&#039;/g, "'");
    str = str.replace(/&quot;/ig, '"');
    str = str.replace(/&amp;/ig, '&'); /* must do &amp; last */
  }
  return str;
}