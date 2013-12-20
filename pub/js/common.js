/**
 * common functions/classes
 *
 * @package     Application
 * @author      Vallo Reima
 * @copyright   (C)2013
 */

function Forms(fid)
  /*
   *  form fields processing class
   *  in: fid - form id
   */
  {
    var fields = {};  /* field properties */
    var that = this;

    /* constructor */
    that.Init = function(flds) {
      var dfe = $(fid).elements;
      for (var i = 0; i < dfe.length; i++) {
        var id = dfe[i].id;
        if (id) {
          fields[id] = {
            obj: dfe[i],
            tpe: dfe[i].type.toLowerCase(),
            err: '',
            val: dfe[i].value
          };
          var lbl = $('l_' + id);
          if (lbl) {
            fields[id].lbl = lbl;
          }
          if (IsSet(flds[id])) {
            fields[id].fnc = flds[id];
          }
        }
      }
      Events(true);
    };

    that.Term = function() {
      Events(false);
    };

    Events = function(flg) {
      var fnc = flg ? AttachEventListener : DetachEventListener;
      for (var id in fields) {
        if (fields[id].tpe === 'button') {
          var evt = 'click';
        } else if (fields[id].tpe !== 'hidden') {
          evt = 'change';
        } else {
          evt = '';
        }
        if (evt !== '') {
          fnc(fields[id].obj, evt, GetEvent);
        }
      }
    };

    that.Gets = function() {
      return fields;
    };

    that.Get = function(id) {
      return fields[id].val;
    };

    that.Set = function(id, val) {
      SetValue(id, val);
    };

    that.SetError = function(id, flg) {
      fields[id].err = flg ? flg : '';
      ErrFlag(id, flg);
    };

    that.Enable = function(flg)
      /*
       * enable/disable form element
       * in:  flg -- true - enable
       */
      {
        for (var c in fields) {
          EnaDisa(fields[c].obj, flg);
        }
      };

    var EnaDisa = function(obj, flg)
      /*
       * enable/disable form element
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
      };

    var GetEvent = function(event)
      /*
       * process field chage/click
       * in: event object
       */
      {
        var trg = Target(event);
        if (trg.tagName.toLowerCase() === 'img') {
          trg = trg.parentNode; /* Chrome, Safari */
        }
        var id = trg.id;
        if (fields[id].tpe !== 'button') {
          SetValue(id);
        }
        if (fields[id].lbl) {
          ErrFlag(id);
        }
        if (fields[id].fnc) {
          fields[id].fnc(id);
        }
        StopEvent(event);
      };

    var SetValue = function(id, val)
      /*
       * Setting field value
       * in: id - field id
       *      val - value (if not set, take from obj)
       */
      {
        if (fields[id].tpe.indexOf('select') + 1) {
          var c = IsSet(val) ? val : fields[id].obj.options[fields[id].obj.selectedIndex].value;
          var k = -1;
          fields[id].idx = -1;
          for (var i = 0; i < fields[id].obj.options.length; i++) {
            if (fields[id].obj.options[i].value === c) {
              fields[id].idx = i;
              fields[id].obj.options[i].setAttribute('selected', 'selected');
            } else if (fields[id].obj.options[i].value === fields[id].val) {
              fields[id].obj.options[i].removeAttribute('selected');
            }
            if (fields[id].obj.options[i].value === '') {
              k = i;
            }
          }
          if (fields[id].idx === -1) {
            c = '';
            fields[id].idx = k === -1 ? 0 : k;
          }
          fields[id].obj.selectedIndex = 0;  /* FF mess */
          fields[id].obj.selectedIndex = fields[id].idx;
          /* setTimeout(function(){
           fields[id].obj.selectedIndex = fields[id].idx;
           },100); */
          fields[id].val = c;
          fields[id].txt = fields[id].obj.options[fields[id].idx].text;
        } else if (fields[id].tpe !== 'button') {
          fields[id].val = Trim(IsSet(val) ? val + '' : fields[id].obj.value);
          if (IsBlank(fields[id].val)) {
            fields[id].val = '';
          }
          fields[id].obj.setAttribute('value', fields[id].val);
          fields[id].obj.value = fields[id].val;
        }
      };

    var ErrFlag = function(id, flg)
      /* set field error flag
       * in: id - field id
       *     flg -- empty - ok
       *            else error
       */
      {
        if (fields[id].lbl) {
          if (Empty(flg)) {
            fields[id].lbl.style.color = '';
          } else {
            fields[id].lbl.style.color = 'red';
          }
        }
      };
  }

function NormName(nme)
  /*
   * normalise person name
   * in:  nme - name entered
   * out: normalized
   */ {
    var c = '';
    if (!IsBlank(nme)) {
      var s = nme.indexOf('-') + 1 ? '-' : ' ';
      var a = Trim(nme).split(s);
      for (var i = 0; i < a.length; i++) {
        c += s + ucfirst(a[i].toLowerCase());
      }
      c = c.substr(1);
    }
    return c;
  }

function XHRJSON(url, par, ops)
  /*
   * read data thru XHR
   * in:  url -- request URL
   *      par -- parameters object
   *      ops -- fnc - callback
   *             mid - meters id
   * out: null -- error
   *               array - response
   */
  {
    var obj = GetHTTPObject();
    var Response = function() {
      if (obj.status === 200 || obj.status === 304) {
        var rlt = obj.responseText;
        try {
          var r = JSON.parse(rlt);
        } catch (e) {
          if (Empty(rlt)) {
            r = null;
          } else {
            document.body.innerHTML = rlt;
          }
        }
        return r;
      }
    };
    var dts = '';
    var MeterOn = function() {
      dts += '.';
      if (dts.length > 3) {
        dts = '';
      }
      ops.mtr.innerHTML = ops.txt + Pad(dts, 3, '&nbsp;');
    };
    var MeterOff = function() {
      clearInterval(mtr);
      ops.mtr.innerHTML = '';
    };
    var mtr = setInterval(MeterOn, 200);
    if (ops.asn) {
      obj.onreadystatechange = function() {
        if (obj.readyState === 4) {
          MeterOff();
          ops.fnc(Response(), par);
        }
      };
    }
    obj.open('POST', url, ops.asn);
    obj.setRequestHeader("Content-Type", "application/json");
    obj.send(JSON.stringify(par));
    if (!ops.asn) {
      MeterOff();
      return Response();
    }
  }
