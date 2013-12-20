<?php

/**
 * basic functionality class
 *
 * @package     System
 * @author      Vallo Reima
 * @copyright   (C)2010
 */
abstract class Base {

  protected $prop;        /* common properties */

  public function __construct($data)
  /*
   *
   */ {
    $this->prop = (object) $data;
    /* Set handler of PHP errors to Site function */
    set_error_handler(array($this, 'PhpError'), error_reporting());
    /* Set exception handlers to Site function */
    set_exception_handler(array($this, 'ExceptionHandler'));
  }

  public function SetAutoload()
  /*
   *  set default autoload
   */ {
    $a = spl_autoload_functions();
    foreach ($a as $b) {
      spl_autoload_unregister($b);
    }
    spl_autoload_register(array($this, '_AutoLoader'));
  }

  private function _AutoLoader($class)
  /*
   *  autoloader to be invoked by __autoload()
   */ {
    @include_once(LIBD . $class . EXT);
    if (!class_exists($class, false) && !interface_exists($class, false)) {
      $this->AppError('noclass', $class, 1);
    }
  }

  public function AppError($errorCode = "undef", $errorText = "", $backLevel = 0)
  /*
   *  system error handler
   */ {
    if (is_array($errorCode)) {
      $errtxt = $this->prop->txt->$errorCode[0] . ": " . $errorCode[1];
    } else {
      $errtxt = $this->prop->txt->$errorCode;
    }
    list($errorFile, $errorLine) = $this->ErrorFix($backLevel);
    $this->FatalError($errtxt, $errorText, $errorFile, $errorLine, 'apperr');
  }

  public function PhpError($errorLevel, $errorText, $errorFile, $errorLine, $errorContext)
  /*
   *  PHP error handler
   */ {
    if (error_reporting() != 0) {
      $errorTypes = array(
          E_ERROR => "Error",
          E_WARNING => "Warning",
          E_PARSE => "Parsing Error",
          E_NOTICE => "Notice",
          E_CORE_ERROR => "Core Error",
          E_CORE_WARNING => "Core Warning",
          E_COMPILE_ERROR => "Compile Error",
          E_COMPILE_WARNING => "Compile Warning",
          E_USER_ERROR => "User Error",
          E_USER_WARNING => "User Warning",
          E_USER_NOTICE => "User Notice",
          E_STRICT => "Runtime Notice"
      );
      $errtxt = $this->prop->txt->badphp;
      if (isset($errorTypes[$errorLevel])) {
        $errtxt .= ": " . strtolower($errorTypes[$errorLevel]);
      }
      $this->FatalError($errtxt, $errorText, $errorFile, $errorLine);
    }
  }

  public function ExceptionHandler($exception)
  /*
   *  Unknown exceptions handling
   */ {
    $errtxt = $this->prop->txt->badphp;  /* $exception->GetErrorPrompt(); */
    $errorText = $exception->getMessage();
    $errorFile = $exception->getFile();
    $errorLine = $exception->getLine();
    $this->FatalError($errtxt, $errorText, $errorFile, $errorLine);
  }

  public function FatalError($err1, $err2, $err3f, $err3l, $err0 = 'syserr')
  /*
   *  final error handler
   * in: err1 - message1
   *     err2 - message2
   *     err3f - file name
   *     errl - line number or function name
   */ {
    $msgs = array($err1, $err2);
    $this->prop->head = $this->prop->txt->$err0;
    if (empty($err3f)) {
      $c = '';
    } else {
      $c = $this->prop->txt->mod . ": " . basename($err3f, EXT);   /* extract error script name */
      $cc = is_numeric($err3l) ? $this->prop->txt->row : $this->prop->txt->sect;
      $c .= $this->Gaps(2) . $cc . ": " . $err3l;     /* script line number */
    }
    $msgs[] = $c;   /* error source */
    @ob_end_clean();
    ob_start();
    @include(TPLD . 'exits' . TPL);
    $c = ob_get_clean();
    if (empty($c)) {
      $c = implode(BR, $msgs); /* error page not found */
    }
    $this->Finish();
    @header("Content-type: text/html");
    @header("Cache-Control: no-cache");
    die($c);
  }

  public function HtmlDoc($ver = 5)
  /*
   *  set the document type line
   */ {
    if ($ver == 5) {
      $c = '<!DOCTYPE html>';
    } else {
      $c = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"> ';
    }
    return $c . PHP_EOL;
  }

  public function HtmlHtml($ver = 5)
  /*
   *  Set the html attribute
   */ {
    if ($ver == 5) {
      $c = 'lang="en"';
    } else {
      $c = 'xmlns="http://www.w3.org/1999/xhtml"';
    }
    return $c;
  }

  public function HtmlHead($css = array(), $base = null)
  /* Form page header
   * in:  css - css files
   *      dir - directory path
   */ {
    if ($this->IsIE()) {
      $css[] = 'msie';  /* MSIE-specific styles */
    }
    $h = '<meta name="generator" content="' . SGN . '"/>' . PHP_EOL;
    $h .= '<meta http-equiv="Content-Type" content="text/html; charset=' . $this->CharSet() . '"/>' . PHP_EOL;
    $h .= '<meta content="text/css" http-equiv="Content-Style-Type"/>' . PHP_EOL;
    $h .= '<title>' . $this->prop->titl . '</title>' . PHP_EOL;
    $dir = ASSETS;
    if (is_null($base)) {
      $base = $this->prop->url;
    } else {
      $dir = $this->prop->url . $dir;
    }
    if (!empty($base)) {
      $h .= '<base href="' . $base . '"/>' . PHP_EOL;
    }
    $h .= '<link href="' . $dir . 'pic/favicon.ico" rel="shortcut icon" type="image/x-icon" />' . PHP_EOL;
    foreach ($css as $val) {
      $val .= '.css';
      $val .= '?id=' . filemtime(PUBD . 'css' . DS . $val);
      $h .= '<link href="' . $dir . 'css/' . $val . '" rel="stylesheet" type="text/css" media="screen"/>';
      $h .= PHP_EOL;
    }
    return $h;
  }

  public function HtmlScript($js = array(), $url = false)
  /*
   * Form page header scripts
   * in:  js - javascript files
   *      url -- false - relative path
   */ {
    $dir = ASSETS . 'js/';
    if ($url !== false) {
      $dir = $this->prop->url . $dir;
    }
    $h = '';
    foreach ($js as $val) {
      $val .= '.js';
      $val .= '?id=' . filemtime(PUBD . 'js' . DS . $val);
      $h .= '<script src="' . $dir . $val . '" type="text/javascript"></script>' . PHP_EOL;
    }
    return $h;
  }

  public function IsIE()
  /*
   * Check if browser is MSIE
   */ {
    return (isset($_SERVER['HTTP_USER_AGENT']) &&
            (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false));
  }

  public function IsAssoc($a)
  /*
   * Check if the array is associative
   */ {
    return is_array($a) && count($a) > 0 && array_keys($a) !== range(0, count($a) - 1);
  }

  public function IsRewritten()
  /*
   *  Detect url rewriting
   */ {
    return isset($_SERVER['REDIRECT_PORT']);
  }

  public function SiteURL($f = 1)
  /*
   * Get site root url
   * f -- 0 - full url
   *      1 - path only
   *      2 - page name
   *      3 - host name
   */ {
    $c = 'http';
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') {
      $c .= "s";
    }
    $c .= "://" . $_SERVER['HTTP_HOST'];
    if (isset($_SERVER['REDIRECT_PORT']) && $_SERVER['REDIRECT_PORT'] != "80") {
      $c .= ":" . $_SERVER['REDIRECT_PORT'];
    } else if ($_SERVER['SERVER_PORT'] != "80") {
      $c .= ":" . $_SERVER['SERVER_PORT'];
    }
    $cc = $c . $_SERVER['SCRIPT_NAME'];
    if ($f == 2) {
      $c = basename($cc);
    } elseif ($f == 1) {
      $c = substr($cc, 0, strrpos($cc, '/') + 1);
    } elseif ($f == 0) {
      $c = $cc;
    } else {
      $c .= '/';
    }
    return $c;
  }

  public function GetCookie($ckn)
  /*
   * read the cookie into array or string
   * in:  ckn - name of cookie
   */ {
    if (isset($_COOKIE[$ckn])) {
      $c = $this->StripSlashes($_COOKIE[$ckn]);
      $a = $this->Dec($c);
    } else {
      $a = array();
    }
    return $a;
  }

  public function StripSlashes($string)
  /*
   * Un-quote a quoted string
   */ {
    if (get_magic_quotes_gpc()) {
      $c = stripslashes($string);
    } else {
      $c = $string;
    }
    return $c;
  }

  public function XmlValues($root, $nodes = null, $kind = null)
  /*
   * read values, set types
   * in:  root -- root name
   *      nodes -- subnodes
   *      kind -- node kind
   *      
   */ {
    $a = array();
    $obj = $this->prop->xpt->query("//$root");
    if ($obj->length > 0) {
      $obj = $obj->item(0);
      if (is_null($nodes)) {
        foreach ($obj->childNodes as $node) {
          $a = array_merge($a, $this->XmlSubArray($node->nodeName, $obj, $kind));
        }
      } else if (is_array($nodes)) {
        foreach ($nodes as $node) {
          $a = array_merge($a, $this->XmlSubArray($node, $obj, $kind));
        }
      } else {
        $a = $this->XmlSubArray($nodes, $obj, $kind);
      }
    }
    foreach ($a as $k => $c) {
      $c = $this->prop->xpt->query("//$k")->item(0)->getAttribute('type');
      if ($c != 'int' && $c != 'float' && $c != 'bool') {
        $c = 'string';
      }
      settype($a[$k], $c);
    }
    return $a;
  }

  public function XmlSubArray($name, $obj = null, $filt = null)
  /*
   * read xml subnodes into array
   * in:  name -- node name
   *      obj -- xquery object
   *      filt -- attribute filter
   * out: array
   */ {
    if (is_null($obj)) {
      $obj = $this->prop->cfg;
    }
    $a = array();
    $nodes = $obj->getElementsByTagName($name)->item(0);
    if (gettype($nodes) == 'object') {
      foreach ($nodes->childNodes as $node) {
        if ($node->nodeType == XML_ELEMENT_NODE && (!is_array($filt) || $this->XmlSubArrayFilt($node, $filt))) {
          $a[$node->nodeName] = trim(preg_replace(array('/LF/', '/CR/'), array('', ''), $node->nodeValue));
        }
      }
    }
    return $a;
  }

  private function XmlSubArrayFilt($node, $filt)
  /*
   * filter out proper attribute values
   * in: node - node object
   *     filt - array attr values
   * out: true -- matches
   */ {
    $flg = true;
    foreach ($filt as $key => $val) {
      if ($node->getAttribute($key) != $val) {
        $flg = false;
        break;
      }
    }
    return $flg;
  }

  private function XmlArray($xml, $arr, $atr = array())
  /*
   * read xml tree into array
   * in: xml - simplexml object
   *     arr - array to write
   *     atr - previous level attributes
   */ {
    foreach ($atr as $a => $b) { /* write previously saved attributes */
      $arr[$a] = $b;
    }
    foreach ($xml->children() as $node) {
      $c = array();
      foreach ($node[0]->attributes() as $a => $b) { /* save attributes */
        $c[$a] = (string) $b;
      }
      $key = $node->getName();
      if (!$node->children()) {
        $arr[$key] = trim($node);
        foreach ($c as $a => $b) { /* write saved attributes */
          $arr[$key][$a] = $b;
        }
      } else {
        $arr[$key] = array();
        $arr[$key] = $this->XmlArray($node, $arr[$key], $c);
      }
    }
    return $arr;
  }

  private function DomXmlArray($xml, $arr = null)
  /*
   * read xml tree without mixed nodes into array
   * in: xml - xml document
   *     arr - array to write
   */ {
    foreach ($xml as $node) {
      $key = $node->nodeName;
      if ($node->nodeType === XML_TEXT_NODE) {
        $val = trim($node->nodeValue);
        if ($val != "" && $key == '#text') {
          $arr = $val;
        }
      } else if ($node->hasChildNodes()) {
        $arr[$key] = array();
        $arr[$key] = $this->DomXmlArray($node->childNodes, $arr[$key]);
      } else { /* empty node */
        $arr[$key] = '';
      }
    }
    return $arr;
  }

  protected function XmlMerge($dst, $src, $mde = false)
  /*
   * merge 2 xml files 
   * adds missing and replaces existing nodes & attributes
   * (root nodes must be identical for replacement)
   * in:  dst -- xml file/object to modify
   *      src -- xml file/object to add
   *      mde -- true - return string
   *                      else object
   * out: merged xml string or object
   * (C) Vallo Reima
   */ {
    if (is_string($dst)) {
      $dom = new DOMDocument();
      $dom->preserveWhiteSpace = false;
      $dom->load($dst);
    } else {
      $dom = $dst;
    }
    $dom->formatOutput = true;
    $dxp = new DomXPath($dom);
    if (is_string($src)) {
      $dom2 = new DOMDocument();
      $dom2->preserveWhiteSpace = false;
      $dom2->load($src);
    } else {
      $dom2 = $src;
    }
    $root = $dom2->documentElement;
    if ($root->nodeName != $dom->documentElement->nodeName) {
      $tmp = $dom->documentElement->nodeName;
      $tmp = "<$tmp></$tmp>";
      $dom3 = new DOMDocument();
      $dom3->preserveWhiteSpace = false;
      $dom3->loadXML($tmp);
      $tmp = $dom3->importNode($root, true);
      $root = $dom3->documentElement;
      $root->appendChild($tmp);
    }
    $this->XmlMerge0($root, $dxp, $dom);
    return ($mde ? $dom->saveXML() : $dom);
  }

  private function XmlMerge0($src, $dtx, &$dst)
  /*
   * merge 2 dom objects
   * in:  src -- current source node object
   *      dtx -- destination xpath
   *      dst -- destination dom
   */ {
    foreach ($src->childNodes as $node) {
      $path = $node->getNodePath();
      if ($node->nodeType === XML_ELEMENT_NODE) {
        if ($dtx->query($path)->length == 0) {
          $tmp = $dst->importNode($node, true);
          $path = $node->parentNode->getNodePath();
          $obj = $dtx->query($path)->item(0);
          $obj->appendChild($tmp);
        } else if ($node->hasAttributes()) {
          $obj = $dtx->query($path)->item(0);
          foreach ($node->attributes as $attr) {
            $obj->setAttribute($attr->nodeName, $attr->nodeValue);
          }
        }
        if ($node->hasChildNodes()) {
          $this->XmlMerge0($node, $dtx, $dst);
        }
      } else if ($node->nodeType === XML_TEXT_NODE) {
        if ($dtx->query($path)->length == 1) {
          $dtx->query($path)->item(0)->nodeValue = $node->nodeValue;
        } else {
          $path = mb_substr($path, 0, mb_strrpos($path, '/')); /* empty node */
          $obj = $dtx->query($path)->item(0);
          $tmp = $dst->createTextNode($node->nodeValue);
          $obj->appendChild($tmp);
        }
      }
    }
  }

  public function GetSection($pth, $mde = null)
  /*
   * check/get section
   * in:  pth - separated path 
   */ {
    return $this->Config("sct/$pth", $mde);
  }

  public function Config($path, $mode = null)
  /*
   * check/get configuration value
   * in -- path - path to a value (optional @<name> is attribute)
   *       mode -- R_*
   */ {
    if ($mode === null) {
      $mode = R_VAL + R_ERR;
    }
    if (strpos($path, '@') === false) {
      $atr = '';
    } else { /* separate pth & attribute name */
      $atr = substr($path, strpos($path, '@') + 1, strlen($path) - strpos($path, '@') - 1);
      $path = substr($path, 0, strpos($path, '@'));
    }

    $rlt = @$this->prop->xpt->query($path);
    $err = ($mode < R_ERR);
    $mde = $err ? $mode : $mode - R_ERR;
    if ($rlt && $rlt->length > 0) {
      if ($mde == R_CHK) {
        $rlt = true;
      } else if (empty($atr)) {
        $rlt = trim((string) $rlt->item(0)->nodeValue);
      } else {
        $rlt = trim((string) $rlt->item(0)->getAttribute($atr));
      }
    } else if (!$err) {
      $this->AppError('badconf', $path);
    } else {
      $rlt = $mde == R_CHK ? false : '';
    }
    return $rlt;
  }

  public function GetEmail($mail = null, $flag = true)
  /*
   *  system contact email
   */ {
    $c = is_null($mail) ? $this->prop->set->crn->mail : $mail;
    if ($flag) {
      $c = preg_replace(array('/\(ätt\)/', '/\(att\)/', '/\(at\)/', '/\(dot\)/', '/\(dt\)/'), array('@', '@', '@', '.', '.'), $c);
    } else {
      $c = preg_replace(array('/\@/', '/\./'), array('(ätt)', '(dot)'), $c);
    }
    return $c;
  }

  public function IsEmail($email) {
    return (preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/iu", $email));
  }

  public function CharSet($style = false)
  /*
   * get default encoding
   * in: style -- true - convert
   */ {
    $c = mb_strtolower(mb_internal_encoding());
    if ($style) {
      if ($c == 'utf-8') {
        $c = 'utf8';
      }
    }
    return $c;
  }

  public function Gaps($n)
  /*
   *  make html spaces
   */ {
    return str_repeat(GAP, $n);
  }

  public function XmlDcl()
  /*
   *  Form xml declaration
   */ {
    $x = '<?xml version="' . $this->prop->cfg->version . '" encoding="' . $this->prop->cfg->encoding . '"?>';
    return $x;
  }

  public function Finish()
  /*
   * End action
   */ {
    if (class_exists('Session', false)) {
      session_write_close();  /* save session */
    }
  }

  public function EndSession()
  /*
   *  end the session
   */ {
    $_SESSION = array();    /* unset all of the session variables */
    if (isset($_COOKIE[session_name()])) {
      setcookie(session_name(), '', time() - 42000, '/');   /* Delete the cookie */
      unset($_COOKIE[session_name()]);    /* clear cookie */
    }
    session_destroy();  /* Destroy the session. */
  }

  public function WorkFile($pfx = 'sys')
  /*
   *  Create workfile
   * in:  pfx - name prefix
   */ {
    $w = tempnam(TMPDIR, $pfx);
    if ($w) {
      return $w;
    } else {
      $this->AppError(array('errio', TMPDIR));
    }
  }

  public function StringExtract($string, $start, $end)
  /*
   * get string between two strings
   */ {
    $string = " " . $string;
    $ini = strpos($string, $start);
    if ($ini == 0)
      return "";
    $ini += strlen($start);
    $len = strpos($string, $end, $ini) - $ini;
    return substr($string, $ini, $len);
  }

  public function StripTrailZero($val, $chk = true)
  /*
   * stripping the trailing zeros
   */ {
    $v = (string) ((float) $val);
    if ($chk && !strpos($v, '.')) {
      $v .= '.0';
    }
    return $v;
  }

  public function ArrayString($array)
  /*
   * convert array to string
   */ {
    $c = '';
    foreach ($array as $key => $value) {
      $c .= ",$key=$value";
    }
    return substr($c, 1);
  }

  public function Inlist()
  /*
   * FoxPro inlist
   */ {
    $f = false;
    $n = func_num_args();
    if ($n > 1) {
      $a = func_get_args();
      for ($i = 1; $i < $n; $i++) {
        if ($a[0] === $a[$i]) {
          $f = true;
          break;
        }
      }
    }
    return $f;
  }

  public function mb_str_replace($needle, $replacement, $haystack) {
    return str_replace($needle, $replacement, $haystack);
  }

  /*  public function mb_str_replace($needle, $replacement, $haystack) {
    return implode($replacement, mb_split($needle, $haystack));
    } */

  /* public function mb_str_replace($search, $replace, $subject, &$count = 0) {
    if (!is_array($subject)) {
    $searches = is_array($search) ? array_values($search) : array($search);
    $replacements = is_array($replace) ? array_values($replace) : array($replace);
    $replacements = array_pad($replacements, count($searches), '');
    foreach ($searches as $key => $search) {
    $parts = mb_split(preg_quote($search), $subject);
    $count += count($parts) - 1;
    $subject = implode($replacements[$key], $parts);
    }
    } else {
    foreach ($subject as $key => $value) {
    $subject[$key] = mb_str_replace($search, $replace, $value, $count);
    }
    }

    return $subject;
    } */

  public function mb_str_split($str, $length = 1) {
    $result = array();
    if ($length > 0) {
      for ($i = 0; $i < mb_strlen($str); $i += $length) {
        $result[] = mb_substr($str, $i, $length);
      }
    }
    return $result;
  }

  public function uniord($u) {
    $k = mb_convert_encoding($u, 'UCS-2LE', 'UTF-8');
    $k1 = ord(substr($k, 0, 1));
    $k2 = ord(substr($k, 1, 1));
    return $k2 * 256 + $k1;
  }

  public function unichr($intval) {
    return mb_convert_encoding(pack('n', $intval), 'UTF-8', 'UTF-16BE');
  }

  public function mb_ucfirst($string) {
    $strlen = mb_strlen($string);
    $firstChar = mb_substr($string, 0, 1);
    $then = mb_substr($string, 1, $strlen - 1);
    return mb_strtoupper($firstChar) . $then;
  }

  public function Enc($data, $flag = false)
  /*
   *  convert data to transit format
   *  in: data -- array
   *      flag -- true - urlencode
   */ {
    $d = json_encode($data);
    if ($flag === true) {
      $d = urlencode($d);
    }
    return $d;
  }

  public function Dec($data, $flag = false)
  /*
   *  convert data from transit format
   *  in: data -- string
   *      flag -- true - urldecode
   */ {
    $d = $data;
    if ($flag === true) {
      $d = urldecode($d);
    }
    $a = @json_decode($d, true);
    if (is_null($a)) {
      $a = array();
    }
    return $a;
  }

  public function DateTimeDiff($dtf, $dtt, $flg = 'd')
  /*
   *  DateTime difference
   *  in: dtf, dtt -- DateTime from & to
   *      flg -- d - days
   *             h - minutes
   *             m - minutes
   *             s - seconds
   */ {
    $d = $this->DateToStamp($dtt) - $this->DateToStamp($dtf);
    if ($flg == 'm') {
      $d = $d / 60;
    } else if ($flg == 'h') {
      $d = $d / (60 * 60);
    } else if ($flg == 'd') {
      $d = $d / (60 * 60 * 24);
    }
    return $d;
  }

  public function DateToStamp($str)
  /*
   * turn a mysql datetime (YYYY-MM-DD HH:MM:SS) into a unix timestamp
   * in:  str -- string to be converted
   */ {
    list($date, $time) = mb_split(' ', $str);
    list($year, $month, $day) = mb_split('-', $date);
    list($hour, $minute, $second) = mb_split(':', $time);
    $timestamp = mktime($hour, $minute, $second, $month, $day, $year);
    return $timestamp;
  }

  public function DateTimeAdd($tme, $mns, $fmt = 'dt')
  /*
   * add/substract minutes
   * in:  tme -- timestamp
   *      mns -- number of minutes
   *      fmt -- return format (d,t,dt)
   * out: timestamp
   */ {
    $t = $tme;
    $f = '';
    if (strpos($fmt, 'd') !== false) {
      $f = 'Y-m-d';
    } else {
      $f = '';
    }
    if (strpos($fmt, 't') !== false) {
      $f .= ' H:i:s';
      if (!strpos($t, ':')) {
        $t .= ' 00:00:00';
      }
    }
    $n = ((int) $mns) * 60;
    $t = date(trim($f), strtotime($t) + $n);
    return $t;
  }

  public function TimeOffsetString($mns)
  /*
   * compose time offset string
   * in:  mns -- offset in minutes
   */ {
    $n = ABS($mns);
    $h = floor($n / 60);
    $m = $n % 60;
    $c = $mns < 0 ? '-' : '+';
    $c .= str_pad($h, 2, '0', STR_PAD_LEFT) . ':' . str_pad($m, 2, '0', STR_PAD_LEFT);
    return $c;
  }

  private function UserTimeOffset($zne = null)
  /*
   * current timezone offset relative to UTC in minutes
   */ {
    $z = is_null($zne) ? $this->prop->set->tzne : $zne;
    if ($z == 'lcl') {
      $v = $_SESSION['tzne'];
    } else if ($z == 'utc') {
      $v = 0;
    } else {
      $a = mb_split(':', $z);
      $v = 60 * $a[0];
      $v = $v + abs($v) / $v * $a[1];
    }
    return $v;
  }

  public function DateTime($timeZone = 0, $timeStamp = null, $timeFormat = null)
  /*
   *  Date & Time
   *  in: timeZone -- client zone offset in minutes
   *  out: time in MySql DATETIME format
   */ {
    if (is_null($timeFormat)) {
      $f = 'Y-m-d H:i:s';
    } else {
      $f = str_replace('%', '', $this->GetDateForm($timeFormat));
    }
    $n = (int) $timeZone * 60;
    $t = is_null($timeStamp) ? time() : $timeStamp;
    return date($f, $t + $n);
  }

  public function DateTimeConvert($fld, $fmt, $ali = null)
  /*
   * compose mysql date conversion token
   *  in: fld -- field name
   *      fmt -- date/time flag
   *      ali -- field alias
   */ {
    $c = "CONVERT_TZ($fld,'+00:00','";
    $c .= $this->TimeOffsetString($this->prop->rq->tzo) . "')";
    $cc = $this->DateTimeAlias($fld, $ali);
    return $this->DateTimeFormat($c, $fmt, $cc);
  }

  /* DATE_FORMAT(CONVERT_TZ(time_utc,'+00:00','+03:00'),'%d.%m.%Y %H:%i:%s')/time_utc */

  public function DateTimeFormat($fld, $fmt, $ali = null)
  /*
   * compose mysql date formatting token
   *  in: fld -- field name
   *      fmt -- date/time flag
   *      ali -- field alias
   */ {
    $c = "DATE_FORMAT($fld,'" . $this->GetDateForm($fmt) . "')";
    $cc = $this->DateTimeAlias($fld, $ali);
    if ($cc) {
      $c .= '/' . $this->DateTimeAlias($fld, $ali);
    }
    return $c;
  }

  private function DateTimeAlias($fld, $ali)
  /*
   * set field alias name
   *  in: fld -- field name
   *      ali -- field alias
   */ {
    if (!is_null($ali)) {
      $c = $ali;
    } else if (strpos($fld, '.') === false) {
      $c = $fld;
    } else {
      $c = substr($fld, strpos($fld, '.') + 1);
    }
    return $c;
  }

  public function GetDateForm($flg = null)
  /*
   *  Get date format (MySql)
   *  in:  flg -- d - day
   *              t - time
   *              T - long time
   */ {
    $fmts = array('dmy' => '%d#%m#%y', 'dmyy' => '%d#%m#%Y', 'ymd' => '%y#%m#%d', 'yymd' => '%Y#%m#%d');
    $fmt = $this->prop->set->dats;
    $c = $fmts[substr($fmt, 1)];
    $d = substr($fmt, 0, 1);
    $d = str_replace('#', $d, $c);
    if (strpos($flg, 'd') !== false) {
      $c = $d;
    } else {
      $c = '';
    }
    if (strpos(strtolower($flg), 't') !== false) {
      $c .= ' %H:%i';
    }
    if (strpos($flg, 'T') !== false) {
      $c .= ':%s';
    }
    return trim($c);
  }

  public function Encrypt($text, $salt = 'monami') {
    return trim(base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $salt, $text, MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND))));
  }

  public function Decrypt($text, $salt = 'monami') {
    return trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $salt, base64_decode($text), MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND)));
  }

  public function MakeUrlSlugs($string, $maxlen = 0) {
    $cyrFrom = array('А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё', 'Ж', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Ъ', 'Ы', 'Ь', 'Э', 'Ю', 'Я', 'а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я');
    $cyrTo = array('A', 'B', 'W', 'G', 'D', 'Ie', 'Io', 'Z', 'Z', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'R', 'S', 'T', 'U', 'F', 'Ch', 'C', 'Tch', 'Sh', 'Shtch', '', 'Y', '', 'E', 'Iu', 'Ia', 'a', 'b', 'w', 'g', 'd', 'ie', 'io', 'z', 'z', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'ch', 'c', 'tch', 'sh', 'shtch', '', 'y', '', 'e', 'iu', 'ia');
    $from = array("Á", "À", "Â", "Ä", "Ă", "Ā", "Ã", "Å", "Ą", "Æ", "Ć", "Ċ", "Ĉ", "Č", "Ç", "Ď", "Đ", "Ð", "É", "È", "Ė", "Ê", "Ë", "Ě", "Ē", "Ę", "Ə", "Ġ", "Ĝ", "Ğ", "Ģ", "á", "à", "â", "ä", "ă", "ā", "ã", "å", "ą", "æ", "ć", "ċ", "ĉ", "č", "ç", "ď", "đ", "ð", "é", "è", "ė", "ê", "ë", "ě", "ē", "ę", "ə", "ġ", "ĝ", "ğ", "ģ", "Ĥ", "Ħ", "I", "Í", "Ì", "İ", "Î", "Ï", "Ī", "Į", "Ĳ", "Ĵ", "Ķ", "Ļ", "Ł", "Ń", "Ň", "Ñ", "Ņ", "Ó", "Ò", "Ô", "Ö", "Õ", "Ő", "Ø", "Ơ", "Œ", "ĥ", "ħ", "ı", "í", "ì", "i", "î", "ï", "ī", "į", "ĳ", "ĵ", "ķ", "ļ", "ł", "ń", "ň", "ñ", "ņ", "ó", "ò", "ô", "ö", "õ", "ő", "ø", "ơ", "œ", "Ŕ", "Ř", "Ś", "Ŝ", "Š", "Ş", "Ť", "Ţ", "Þ", "Ú", "Ù", "Û", "Ü", "Ŭ", "Ū", "Ů", "Ų", "Ű", "Ư", "Ŵ", "Ý", "Ŷ", "Ÿ", "Ź", "Ż", "Ž", "ŕ", "ř", "ś", "ŝ", "š", "ş", "ß", "ť", "ţ", "þ", "ú", "ù", "û", "ü", "ŭ", "ū", "ů", "ų", "ű", "ư", "ŵ", "ý", "ŷ", "ÿ", "ź", "ż", "ž");
    $to = array("A", "A", "A", "A", "A", "A", "A", "A", "A", "AE", "C", "C", "C", "C", "C", "D", "D", "D", "E", "E", "E", "E", "E", "E", "E", "E", "G", "G", "G", "G", "G", "a", "a", "a", "a", "a", "a", "a", "a", "a", "ae", "c", "c", "c", "c", "c", "d", "d", "d", "e", "e", "e", "e", "e", "e", "e", "e", "g", "g", "g", "g", "g", "H", "H", "I", "I", "I", "I", "I", "I", "I", "I", "IJ", "J", "K", "L", "L", "N", "N", "N", "N", "O", "O", "O", "O", "O", "O", "O", "O", "CE", "h", "h", "i", "i", "i", "i", "i", "i", "i", "i", "ij", "j", "k", "l", "l", "n", "n", "n", "n", "o", "o", "o", "o", "o", "o", "o", "o", "o", "R", "R", "S", "S", "S", "S", "T", "T", "T", "U", "U", "U", "U", "U", "U", "U", "U", "U", "U", "W", "Y", "Y", "Y", "Z", "Z", "Z", "r", "r", "s", "s", "s", "s", "B", "t", "t", "b", "u", "u", "u", "u", "u", "u", "u", "u", "u", "u", "w", "y", "y", "y", "z", "z", "z");
    $from = array_merge($from, $cyrFrom);
    $to = array_merge($to, $cyrTo);

    $newstring = $this->mb_str_replace($from, $to, $string);
    $newstring = mb_strtolower($newstring);
    $stringTab = $this->mb_str_split($newstring);
    $newStringTab = array();
    $numbers = array("0", "1", "2", "3", "4", "5", "6", "7", "8", "9", "-");
    //$numbers=array("0","1","2","3","4","5","6","7","8","9");

    foreach ($stringTab as $letter) {
      if (in_array($letter, range("a", "z")) || in_array($letter, $numbers)) {
        $newStringTab[] = $letter;
        //print($letter);
      } elseif ($letter == " ") {
        $newStringTab[] = "-";
      }
    }

    if (count($newStringTab)) {
      $newString = implode($newStringTab);
      if ($maxlen > 0) {
        $newString = mb_substr($newString, 0, $maxlen);
      }

      $i = 0;
      do {

        $newString = $this->mb_str_replace('--', '-', $newString);
        $pos = mb_strpos($newString, '--');

        $i++;
      } while ($pos !== false);
    } else {
      $newString = '';
    }

    return $newString;
  }

  public function CheckUrlSlug($sSlug) {
    if (preg_match("/^[a-zA-Z0-9]+[a-zA-Z0-9\_\-]*$/u", $sSlug)) {
      return true;
    }
    return false;
  }

  public function IsFile($file)
  /*
   * check file existeness on include path
   * in:  file -- relative path filename
   * out: true/false 
   */ {
    if (function_exists('stream_resolve_include_path')) {
      $f = stream_resolve_include_path($file);  /* from v5.3.2 */
    } else {
      $f = fopen($file, 'r', FILE_USE_INCLUDE_PATH);
      if ($f) {
        fclose($f);
      }
    }
    if ($f !== false) {
      $f = true;
    }
    return $f;
  }

  public function GetBrowser($flg = 0)
  /*
   * client browser info
   * in:  flg -- 0 - name & version
   *             1 - token
   */ {
    $version = 'Unknown x.y.z';
    $browsers = array("firefox", "msie", "opera", "chrome", "safari",
        "mozilla", "seamonkey", "konqueror", "netscape",
        "gecko", "navigator", "mosaic", "lynx", "amaya",
        "omniweb", "avant", "camino", "flock", "aol");
    $agent = strtolower($_SERVER['HTTP_USER_AGENT']);
    foreach ($browsers as $browser) {
      if (preg_match("#($browser)[/ ]?([0-9.]*)#", $agent, $match)) {
        if ($match[1] == 'msie' || $match[1] == 'aol') {
          $match[1] = strtoupper($match[1]);
        } else {
          $match[1] = ucfirst($match[1]);
        }
        $version = $match[1] . ' ' . $match[2];
        break;
      }
    }
    if ($flg == 1) {
      $version = mb_split(' ', $version);
      if ($version[0] == 'MSIE') {
        $version = 'IE';
      } else if ($version[0] == 'Firefox') {
        $version = 'FF';
      } else if ($version[0] == 'Chrome') {
        $version = 'CR';
      } else if ($version[0] == 'Safari') {
        $version = 'SF';
      } else if ($version[0] == 'Opera') {
        $version = 'OP';
      } else {
        $version = $version[0];
      }
    }
    return $version;
  }

  public function PackContent($file, $mode = FILE_USE_INCLUDE_PATH)
  /*
   * pack js/css files
   * in:  $file -- pathed filename
   *      mode -- inclusion mode
   * out: packed string 
   */ {
    $source = @file_get_contents($file, $mode);
    if (!$source) {
      $packed = '';
    } else if (mb_strpos($file, '.js')) { /* Dean Edwards method */
      $packed = new JavaScriptPacker($source, 'Normal', true, false);
      $packed = $packed->pack();
    } else if (mb_strpos($file, '.css')) { /* Reinhold Weber method */
      /* remove comments */
      $packed = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $source);
      /* remove tabs, spaces, newlines, etc. */
      $packed = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $packed);
    } else {
      $packed = null;
    }
    return $packed;
  }

  public function RandomString($len = 8) {
    $str = 'abcefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
    return substr(str_shuffle($str), 0, $len);
  }

  public function Debug($data = null)
  /*
   *  save debug data
   */ {
    $dbf = TMPDIR . 'debug.txt';
    $date = date('d.m.Y/H:i:s');
    list($file, $line) = $this->ErrorFix(0);
    $text = "$date $file $line\n";
    foreach ((array) $data as $key => $value) {
      $val = var_export($value, true);
      $text .= "$key=$val\n";
    }
    $text .= LF;
    $fp = fopen($dbf, 'a');
    fwrite($fp, $text);
    fclose($fp);
  }

  private function ErrorFix($lvl)
  /*
   * fix error file & line
   * in:  lvl -- backtrace level
   */ {
    $a = debug_backtrace();
    $n = count($a);
    $i = $lvl;
    while ((!isset($a[$i]['file']) || strpos($a[$i]['function'], 'call_user_func') === false) && $i < $n) {
      $i++;
    }
    if ($i == $n) {
      $i = $lvl;
    }
    while (!isset($a[$i]['file']) || strpos($a[$i]['function'], 'call_user_func') !== false) {
      $i++;
    }
    return array($a[$i]['file'], $a[$i]['line']);
  }

}

?>