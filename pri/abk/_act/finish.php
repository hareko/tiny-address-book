<?php

/*
 * bye panel creator
 *
 * Logout and show end page or login again
 *
 * @package     Application
 * @author      Vallo Reima
 * @copyright   (C)2010
 */
$msgs[0] = ¤::_('txt.tnk');
$msgs[1] = '';
$msgs[2] = ¤::_('txt.slo');
ob_start();
echo '<div class="wrapper">';
include(TPLD . 'exit' . TPL);
echo '</div>';
$htm = ob_get_clean();
$rsp = ['htm' => $htm];
//header('Content-Type: application/json; charset=utf-8');
echo json_encode($rsp);
?>