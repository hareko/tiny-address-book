<?php

/**
 * application bootstrap
 *
 * @package     System
 * @author      Vallo Reima
 * @copyright   (C)2010
 */
/* Site global constants */
define('LF', "\n");            /* line feed */
define('CR', "\r");            /* carriage return */
define('FF', "\f");            /* form feed */
define('HT', "\t");            /* hor. tabulation */
define('VT', "\x0B");          /* ver. tabulation */
define('NUL', "\0");           /* NUL-byte */
define('BR', '<br />');        /* line break */
define('GAP', '&nbsp;');       /* html space */
define('SEN', 'sid');          /* session id name */
/* Directory relative paths */
define('ASSETS', str_replace(DS, '/', PUBD));       /* public directory */
define('PICPTH', ASSETS . 'pic/');      /* pictures path */
define('TMPDIR', PRID . 'tmp' . DS);    /* workfiles */
define('SYSDIR', 'sys' . DS);   /* system support */
define('LIBD', '_lib' . DS);   /* classes */
define('ACTD', '_act' . DS);   /* actions */
define('SRVD', '_srv' . DS);   /* services */
define('TPLD', '_tpl' . DS);   /* templates */
define('XSLD', '_xsl' . DS);   /* xsl stylesheets */
define('JSD', '_js' . DS);     /* javascripts */
define('CSSD', '_css' . DS);   /* styles */
define('HLPD', '_hlp' . DS);   /* help topics */
/* File extensions */
define('CFG', '.xml');         /* config */
define('SRV', '.inc');         /* service */
define('TPL', '.phtml');       /* template */
/* Return modes */
define('R_CHK', 0);           /* check existeness */
define('R_VAL', 1);           /* get result set */
define('R_ERR', 2);           /* return false (no finish) */
/* database table modes */
define('DB_SNG', '0');         /* single system/owner disabled */
define('DB_ONE', '1');         /* one database */
define('DB_MLT', '2');         /* separate databases */

date_default_timezone_set('UTC');
mb_internal_encoding('UTF-8');
/* load & activate core support */
spl_autoload_register('AutoLoad');
require(PRID . 'gateway' . EXT);
¤::_Init();
¤::SetAutoload();
$pth = PRID . APP . DS . PATH_SEPARATOR . PRID . SYSDIR;
set_include_path($pth);
/* runtime error reporting */
ini_set('display_errors', DEV);
ini_set('log_errors', !DEV);
ini_set('error_log', TMPDIR . 'error.txt');

function AutoLoad($class) {
  require(PRID . SYSDIR . LIBD . $class . EXT); /* core loading */
}

?>