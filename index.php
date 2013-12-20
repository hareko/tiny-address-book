<?php

/**
 * the entry
 *
 * @package     System
 * @author      Vallo Reima
 * @copyright   (C)2013
 */
define('SGN', 'aBook');                     /* signature */
define('APP', 'abk');                       /* application token */
define('DEV', 1);                           /* development status */

if (version_compare(PHP_VERSION, '5.4', '<')) {
  die('System requires PHP 5.4 or newer version');
} else {
  error_reporting(-1);
  ini_set('display_errors', true);
  ini_set('log_errors', false);
}

define('DS', DIRECTORY_SEPARATOR);                  /* directory separator */
define('PRID', 'pri' . DS);                         /* system directory */
define('PUBD', 'pub' . DS);                         /* assets directory */
define('EXT', '.php');                              /* default extension */

require_once(PRID . 'startup' . EXT);               /* bootstrap */
$application = new Frontal();                       /* front controller */
$application->Run();                                /* perform action */
?>