<?php

/*
 * browse panel creator
 *
 * @package Application
 * @author Vallo Reima
 * @copyright (C)2013
 */
$cmd = ¤::_('rq.cmd');
$flds = ['contacts' => '*', 'towns' => ['name/town']];
$rsp = ['code' => 'ok', 'string' => '', 'factor' => ''];
if (!¤::_('db')->Query($flds, 'a.town_id=b.id', '', ['ord' => 'fname,lname'])) {
  $rsp['string'] = ¤::_('txt.norecs');
  $rsp['code'] = 'err';
} else if ($cmd == 'B') {
  $nme = basename(__FILE__, EXT);
  ob_start();
  include TPLD . $nme . TPL;
  $rsp['string'] = ob_get_clean();
} else if ($cmd == 'O') {
  Create($rsp);
}
echo json_encode($rsp);

function Create(&$rsp)
/*
 * create xml
 */ {
  $doc = new DOMDocument(¤::_('cfg.version'), ¤::_('cfg.encoding'));
//  $doc = new DomDocument('1.0');
  $doc->preserveWhiteSpace = false;
  $doc->formatOutput = true;
  $root = $doc->createElement('contacts');
  $root = $doc->appendChild($root);
  while ($row = ¤::_('db')->Record()) {
    $ctct = $doc->createElement('contact');
    $ctct = $root->appendChild($ctct);
    foreach ($row as $key => $val) {
      $chld = $doc->createElement($key);
      $chld = $ctct->appendChild($chld);
      //add data to the new element
      $txt = $doc->createTextNode($val);
      $txt = $chld->appendChild($txt);
    }
  }
  $wkf = ¤::WorkFile('xml');
  if ($wkf && $doc->save($wkf)) {
    $rsp['string'] = ¤::_('txt.xml');
    $rsp['factor'] = $wkf;
  } else {
    $rsp['code'] = 'err';
    $rsp['string'] = ¤::_('txt.nosd');
  }
}

?>
