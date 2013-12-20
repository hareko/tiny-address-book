<?php

/*
 * layout creator
 *
 * @package Application
 * @author Vallo Reima
 * @copyright (C)2013
 */
$abk = mb_strtolower(SGN);
¤::$_->fid = $abk;
¤::$_->cln = ¤::mb_ucfirst($abk);

$base = 'base';
$mod = ACTD . $abk . EXT;
$nme = basename(__FILE__, EXT); /* example's filename */
include TPLD . $nme . TPL; /* fill template */
?>