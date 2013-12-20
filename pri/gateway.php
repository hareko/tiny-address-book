<?php

/**
 * gateway to common methods & properties
 * requirements:  PHP 5.3+
 *                common class including:
 *                  common properties structure
 *                  GetProperty/SetProperty methods
 *                  customer methods & properties
 *
 * @package     System
 * @author      Vallo Reima
 * @copyright   2013
 * @license     BSD
 */
class ¤ {

  private static $cmi;                  /* common instance */
  private static $sep;                  /* path separator */
  public static $_;                     /* workarea */

  public static function _Init($class = 'Common', $sep = '.') {
    self::$cmi = new $class(self::$_);  /* instantiate the common class */
    self::$sep = $sep;                  /* save property path separator */
  }

  public static function __callStatic($func, $args) { /* access a method */
    return call_user_func_array(array(self::$cmi, $func), $args);
  }

  public static function _()
  /*
   * get/set a property value 
   * in:  arguments -- 1st - path string
   *                   2nd - value (set only)
   * out: get/set value
   */ {
    $path = trim(func_get_arg(0), self::$sep);  /* remove leading/trailing separators */
    $pth = explode(self::$sep, $path);          /* split the path */
    if (func_num_args() === 2) {
      $value = func_get_arg(1);                 /* value to set */
      self::$cmi->SetProperty($pth, $value);    /* set a value */
    } else {
      $value = self::$cmi->GetProperty($pth);   /* get a value */
    }
    return $value;
  }

}

?>