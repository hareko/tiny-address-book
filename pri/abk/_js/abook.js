/**
 * editor/browser class
 *
 * @package     Application
 * @author      Vallo Reima
 * @copyright   (C)2013
 */

function Abook(fid)
  /*
   *  in: fid - form id
   */
  {
    var frm;        /* form object */
    var evs = [];   /* events stack */
    var rst = [];   /* reset values */
    var xhr;        /* xhr params */

    var Init = function() {
      /*
       * set events and view status
       */
      frm = new Forms(fid);
      frm.Init({fname: CnvName, lname: CnvName});
      var a = $$('command', 'button');
      for (var i = 0; i < a.length; i++) {
        evs.push([a[i], 'click', Command]);
      }
      evs.push([$('finish'), 'click', Command]);
      Events(true);
      Switch('');
      $('command').className = 'command';
      xhr = {asn: true, fnc: Response, mtr: $('msgraw'), txt: ts.Get('prpmt')};
    };

    var Command = function(event)
      /*
       * process the command
       * in:  button event
       */
      {
        var trg = Target(event);
        StopEvent(event);
        var c = trg.name;
        var id = frm.Get('id');
        if (ts.bsy) {
        } else if (c === 'A') { /* Add */
          var a = frm.Gets();
          for (c in a) {
            frm.Set(c, '');
          }
          Switch('addg');
        } else if (c === 'M') { /* Edit */
          if (Empty(id)) {
            s = 'addg';
          } else if (Number(id) > 0) {
            s = 'mdfg';
          } else {
            s = 'undg';
          }
          Switch(s);
        } else if (c === 'D') { /* Delete */
          if (confirm(ts.Get('delcfm'))) {
            Edit(c);
          }
        } else if (c === 'R') { /* Reset */
          for (var c in rst) {
            frm.Set(c, rst[c]);
          }
        } else if (c === 'CC') {  /* Cancel */
          Switch('');
        } else if (c === 'S') { /* Save */
          if (Empty(id)) {
            c = 'A';
          } else if (Number(id) > 0) {
            c = 'M';
          } else {
            c = 'U';
          }
          Edit(c);
        } else if (c === 'B') { /* Browse */
          var a = {lng: ts.Get('lng'), act: 'brw', cmd: 'B'};
          ts.bsy = true;
          c = XHRJSON(ts.Get('url'), a, xhr);
        } else if (c === 'O') { /* Output */
          var a = {lng: ts.Get('lng'), act: 'brw', cmd: 'O'};
          ts.bsy = true;
          c = XHRJSON(ts.Get('url'), a, xhr);
        } else if (c === 'CL') {  /* Close */
          Close();
        } else if (c === 'E') { /* Finish */
          Finish();
        }
      };

    var Edit = function(cmd) {
      /* 
       * process the change command
       * in:  cmd -- command - A,D,M,U
       */
      var f = '';
      var a = frm.Gets();
      var fld = {};
      for (var c in a) {
        if (c !== 'id' && IsBlank(a[c].val)) {
          f = ts.Get('msd');
          frm.SetError(c, f);
        } else {
          frm.SetError(c, '');
        }
        fld[c] = frm.Get(c);
      }
      $('msgraw').innerHTML = f;
      if (f === '') {
        var a = {lng: ts.Get('lng'), srv: 'edt', cmd: cmd, fld: fld};
        ts.bsy = true;
        XHRJSON(ts.Get('url'), a, xhr);
      }
    };

    var Browse = function(htm) {
      /*  
       * browse the contacts
       * in:  htm -- table
       */
      $('browse').innerHTML = htm;
      $$('browse','div')[0].style.height = $(fid).scrollHeight + 'px';
      var evt = [$('tabbody'), 'dblclick', Select];
      evs.push(evt);
      Events(true, [evt]);
      $(fid).style.display = 'none';
      $('browse').className = 'browse';
      $('browse').style.display = '';
      Switch('brng', 'B');
    };

    var Select = function(event) {
      /*  
       * row doubleclick
       */
      var trg = Target(event).parentNode;
      StopEvent((event));
      if (trg.tagName.toLowerCase() === 'tr') {
        var id = trg.id.replace('id', '');
        var a = {lng: ts.Get('lng'), srv: 'edt', cmd: 'S', fld: {id: id}};
        ts.bsy = true;
        XHRJSON(ts.Get('url'), a, xhr);
      }
    };

    var Response = function(rlt, par) {
      /*  
       * async request return
       * in:  rlt -- result object
       *      par -- calling parameters
       */
      ts.bsy = false;
      var c = '';
      if (!rlt || !rlt.code) {
        c = ts.Get('noxhr');
      } else if (rlt.code !== 'ok') {
        c = rlt.string;
      } else if (par.srv === 'edt') {
        c = rlt.string;
        if (par.cmd === 'S') {
          Close(rlt.factor);
        } else {
          frm.Set('id', rlt.factor);
          Switch('');
        }
      } else if (par.cmd === 'B') {
        c = '';
        Browse(rlt.string);
      } else if (par.cmd === 'O') {
        c = rlt.string;
        $('filename').value = rlt.factor;
        $('transit').submit();
      }
      $('msgraw').innerHTML = c;
    };

    var Close = function(row) {
      /*  
       * close browsing, update fields
       * in:  row -- selected row
       */
      var evt = [evs.pop()];
      Events(false, evt);
      $('browse').style.display = 'none';
      $(fid).style.display = '';
      if (row) {
        for (var c in row) {
          frm.Set(c, row[c]);
          frm.SetError(c, '');
        }
      }
      Switch('');
    };

    var Switch = function(pmt, cmd) {
      /* 
       * switch edit/view/browse
       * in:  pmt -- status text
       *             '' - exit edit
       */

      var dsa = [];
      if (pmt === '') {
        var shw = ['A', 'D', 'M', 'B'];
        var id = frm.Get('id');
        if (id === '' || Number(id) < 0) {
          dsa = ['D'];
        }
        ts.sts = 'V';
      } else if (cmd === 'B') {
        shw = ['O', 'CL'];
        ts.sts = 'B';
      } else {
        var a = frm.Gets();
        for (var c in a) {
          rst[c] = frm.Get(c);
        }
        shw = ['R', 'CC', 'S'];
        ts.sts = 'E';
      }
      $('section').innerHTML = pmt === '' ? '' : ts.Get(pmt);
      $('msgraw').innerHTML = '';
      frm.Enable(pmt !== '');
      var a = $$('command', 'button');
      for (var i = 0; i < a.length; i++) {
        a[i].disabled = ArraySearch(a[i].name, dsa) !== false;
        a[i].style.display = ArraySearch(a[i].name, shw) === false ? 'none' : '';
      }
      if (ts.sts === 'E') {
        $('fname').focus();
      }
    };

    var Finish = function() {
      /*  
       * terminate
       */
      var a = {lng: ts.Get('lng'), act: 'end'};
      xhr.asn = false;
      ts.bsy = true;
      var r = XHRJSON(ts.Get('url'), a, xhr);
      if (r && r.htm) {
        Events(false);
        document.body.innerHTML = r.htm;
      } else {
        $('msgraw').innerHTML = ts.Get('noxhr');
        ts.bsy = false;
      }
    };

    var Events = function(flg, evt) {
      /*  att/detach events
       * in:  flg -- true - att
       *             false - det
       *      evt -- specific events
       */
      var f = flg ? AttachEventListener : DetachEventListener;
      var e = evt ? evt : evs;
      for (var i in e) {
        f(e[i][0], e[i][1], e[i][2]);
      }
    };

    var CnvName = function(id)
      /*
       * normalise person name
       * in:  id - name id
       */ {
        var c = NormName(frm.Get(id));
        frm.Set(id, c);
      };

    Init();
  }
