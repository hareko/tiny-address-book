<?php

/*
 * main panel creator
 *
 * @package Application
 * @author Vallo Reima
 * @copyright (C)2013
 */
¤::$_->ts = ['lng' => ¤::_('lng'),
    'url' => ¤::_('url'),
    'prpmt' => ¤::_('txt.prpmt'),
    'noxhr' => ¤::_('txt.noxhr'),
    'addg' => ¤::_('txt.addg'),
    'mdfg' => ¤::_('txt.mdfg'),
    'undg' => ¤::_('txt.undg'),
    'brng' => ¤::_('txt.brng'),
    'delcfm' => ¤::_('txt.delcfm'),
    'msd' => ¤::_('txt.msd'),
    'tst' => ¤::_('txt.msd'),
    'wrd' => ¤::_('txt.wrd')
];
$towns = ¤::_('db')->Fetch('towns','id,name','','id',['ord' => 'name']);
$nme = basename(__FILE__, EXT);
include TPLD . $nme . TPL;
?>