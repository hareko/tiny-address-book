<?php

/**
 * Front Controller 
 *
 * processes requests and invokes actions
 *
 * @package     Application
 * @author      Vallo Reima
 * @copyright   (C)2013
 */
class Frontal {

  private $rqm;              /* request mode  
   * 0 - startup
   * 1 - AJAX call
   * 2 - other
   */

  public function __construct()
  /*
   * in:  config -- configuration name
   */ {
    ¤::_('url', ¤::SiteURL());
    $jsn = ¤::Dec(file_get_contents('php://input'));
    ¤::_('rq', (object) array_merge($_REQUEST, $jsn));
    if (!¤::_('rq.act') && !¤::_('rq.srv')) {
      $this->rqm = 0;
    } else if (!empty($jsn)) {
      $this->rqm = 1;
    } else {
      $this->rqm = 2;
    }
  }

  public function Run()
  /*
   * executing the lifecycle of the application
   * in:  request -- act - E,T,<>
   */ {
    $txt = PRID . APP . DS . APP;
    ¤::_('txt', new Texts($txt, ¤::_('rq.lng')));
    $err = ¤::_('txt._err');
    if ($err == '') {
      ¤::_('lng', ¤::_('txt.lng'));
      $this->Config();
    } else {
      ¤::FatalError($err, 'Texts', '', '');  /* no texts */
    }
    ¤::_('head', ¤::_('txt.appl'));
    ¤::_('foot', ¤::GetSign());
    ¤::_('db', new Database(¤::XmlSubArray('db')));
    $mods = ¤::XmlSubArray('mod');
    $ext = EXT;
    if ($this->rqm == 0) {
      $act = 'shl';
    } else if (¤::_('rq.srv')) {
      $act = ¤::_('rq.srv');
      $ext = SRV;
    } else {
      $act = ¤::_('rq.act');
    }
    if (isset($mods[$act])) {
      $file = ($ext == SRV ? SRVD : ACTD) . $mods[$act] . $ext;
      if (¤::IsFile($file)) {
        $this->Action($file);  /* not solved */
      } else {
        ¤::AppError('nomod', basename($file, $ext));
      }
    } else {
      ¤::AppError('noact', $act);
    }
  }

  private function Config()
  /*
   * read configuration settings
   */ {
    $file = APP . CFG;
    $dom = new DOMDocument();
    $dom->preserveWhiteSpace = false;
    $dom->encoding = ¤::CharSet();
    if (@$dom->load(PRID . APP . DS . $file)) {
      ¤::_('cfg', $dom);
      ¤::_('xpt', new DomXPath(¤::_('cfg', $dom)));
    } else {
      ¤::AppError(array('noconf', $file));
    }
  }

  private function Action($mod)
  /*
   *  fetch module and echo response
   */ {
    ¤::_('hdr', ($this->rqm == 1 ? 'jsn' : 'htm'));
    ob_start();
    include($mod);
    $response = ob_get_clean();
    ¤::Finish();
    $c = ¤::CharSet();
    $hdr = ¤::_('hdr');
    if ($hdr == 'htm') {
      header("Content-Type: text/html; charset=$c");
    } else if ($hdr == 'xml') {
      header("Content-type: text/xml");
    } else if ($hdr == 'jsn') {
      header("Content-Type: text/json; charset=$c");
    }
    if (!empty($hdr)) {
      header("Cache-Control: no-cache");
    }
    echo $response;
  }

}

?>