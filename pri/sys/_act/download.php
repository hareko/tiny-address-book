<?php

/*
 * file download dialogue
 * in: rq  -- wkf - saved workfile name
 *            fnm - download filename
 *
 * out: data stream
 *
 * @package     System
 * @author      Vallo Reima
 * @copyright   (C)2010
 */
¤::_('hdr', '');
$workfile = ¤::_('rq.wkf');
$filename = ¤::_('rq.fnm');
$filetype = substr($filename, strrpos($filename, '.') + 1);
header("Pragma: no-cache");
//header('Pragma: public');
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Content-Type: application/octet-stream");
if ($filetype == 'csv') {
  header("Content-Type: application/vnd.ms-excel; charset=UTF-16LE");
} else if ($filetype == 'pdf') {
  header('Content-Type: application/pdf');
} else if ($filetype == 'php') {
  header('Content-Type: application/php');
} else if ($filetype == 'xml') {
  header('Content-Type: text/xml');
}
//header('Content-Type: application/x-download');
if (!¤::Inlist($filetype, 'pdf','xml')) {
  header("Content-Type: application/force-download");
  header("Content-Type: application/download");
}
header('Content-Length: ' . filesize($workfile));
header('Content-Disposition: attachment; filename=' . $filename);
header('Content-Transfer-Encoding: binary');
readfile($workfile);
unlink($workfile);
?>