<?php

/*
 * multilingual texts class
 *
 * @package     System
 * @author      Vallo Reima
 * @copyright   (C)2012
 */

class Texts {

  const LNG = 'en';       /* default language */

  public $lng;            /* current language */
  private $txts = array(/* texts */
      'err' => array(
          'ext' => 'PDO extension is missing',
          'dbn' => 'Database is missing',
          'dbl' => "Can't connect the database",
          'sql' => "Can't import the texts",
          'dbq' => "Can't access the table",
          'rlt' => "Can't read the texts",
          'lng' => 'Unknown language'),
      'msg' => array('und' => 'Undefined')
  );

  public function __construct($dbn, $lng = self::LNG)
  /*
   * open vocabulary
   * in:  dbn -- database name
   *      lng -- text's language
   *      
   */ {
    list($dbl, $err) = $this->Open('sqlite', $dbn);
    if ($err == '') {
      $dbq = $dbl->prepare('SELECT ALL * FROM texts');
      if ($dbq && $dbq->execute()) {
        $rlt = $dbq->fetch();
        if ($rlt) {
          $err = $this->Read($dbq, $rlt, (string) $lng);
        } else {
          $err = 'rlt';
        }
      } else {
        $err = 'dbq';
      }
    }
    $this->txts['msg']['_err'] = empty($err) ? '' : $this->txts['err'][$err];
    $dbq = null;
    $dbl = null;
  }

  private function Open($dbk, $dbn)
  /*
   * open/create the texts database
   * in:  dbk -- database kind
   *      dbn -- database name
   * out: [database link, error token]
   */ {
    $lnk = null;
    $err = '';
    $f = file_exists("$dbn.db");
    if (!extension_loaded("pdo_$dbk")) {
      $err = 'ext';
    } else if (!$f && !file_exists("$dbn.sql")) {
      $err = 'dbn';
    } else {
      try {
        $lnk = new PDO("$dbk:$dbn.db", '', '', array(PDO::ATTR_ERRMODE => PDO::ERRMODE_SILENT,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC));
      } catch (PDOException $err) {
        $err = 'dbl';
      }
    }
    if ($err == '' && !$f) {
      $f = @file_get_contents("$dbn.sql");
      if (!$f || !$lnk->exec($f)) {
        $err = 'sql';
      }
    }
    return array($lnk, $err);
  }

  private function Read($dbq, $rlt, $lng)
  /*
   * load the language-dependent texts
   * in:  dbq -- query object
   *      rlt -- 1st row
   *      lng -- language token
   */ {
    if (empty($lng)) {
      $lng = self::LNG;
    }
    if (array_key_exists($lng, $rlt) && array_key_exists(self::LNG, $rlt)) {
      do {
        if (is_null($rlt[$lng])) {
          $rlt[$lng] = $rlt[self::LNG];
        }
        if (empty($rlt['type'])) {
          $this->txts['msg'][$rlt['code']] = $rlt[$lng];
        } else {
          $this->txts[$rlt['type']][$rlt['code']] = $rlt[$lng];
        }
        $rlt = $dbq->fetch();
      } while ($rlt);
      $this->lng = $lng;
    } else {
      $this->lng = null;
    }
    return $this->lng ? '' : 'lng';
  }

  public function __get($code)
  /*
   * get text by the code
   */ {
    if (isset($this->txts[$code])) {
      $c = $this->txts[$code];
    } else if (isset($this->txts['msg'][$code])) {
      $c = $this->txts['msg'][$code];
    } else {
      $c = $this->txts['msg']['und'];
    }
    return $c;
  }
  
  public function __isset($code) {
    return true;
  }

  public function __set($code, $text) {
    if (gettype($text) == 'string') {
      $this->txts['msg'][$code] = $text;
    } else {
      $this->{$code} = $text;
    }
  }

}

?>
