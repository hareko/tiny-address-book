<?php

/*
 * data manipulation
 * in:  rq -- cmd - A,M,D,U,S
 *            fld - field values
 *
 * @package Application
 * @author Vallo Reima
 * @copyright (C)2013
 */

$cmd = ¤::_('rq.cmd');
$fld = ¤::_('rq.fld');
$tbl = 'contacts';
$r = 0;
if (¤::Inlist($cmd, 'A', 'U')) {
  unset($fld['id']);
  $r = ¤::_('db')->Insert($tbl, $fld);
  $fld['id'] = $r;
  $s = $cmd == 'U' ? 'undd' : 'addd';
} else if ($cmd == 'M') {
  $r = ¤::_('db')->Update($tbl, $fld, ['id' => $fld['id']]);
  $s = 'mdfd';
} else if ($cmd == 'D') {
  $r = ¤::_('db')->Delete($tbl, ['id' => $fld['id']]);
  $fld['id'] = -((int) $r);
  $s = 'deld';
} else if ($cmd == 'S') {
  $r = ¤::_('db')->Fetch('contacts', '*', ['id' => $fld['id']], '');
  $s = 'sltd';
}
$code = 'err';
$id = '';
if ($r) {
  $id = $cmd == 'S' ? $r[0] : $fld['id'];
  $s = ¤::_("txt.$s");
  $code = 'ok';
} else if ($r !== 0) {
  $s = ¤::_('db')->Error();
} else {
  $s = ¤::_('txt.nochn');
}

$rsp = ['code' => $code, 'string' => $s, 'factor' => $id];
echo json_encode($rsp);
?>
