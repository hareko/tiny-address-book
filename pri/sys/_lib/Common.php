<?php

/**
 * common methods & properties
 * instantiated by gateway class
 * 
 * @package     System
 * @author      Vallo Reima
 * @copyright   2013
 */
class Common extends Base {

  private $data = array(
      'titl' => SGN, /* site title */
      'head' => SGN, /* page heading */
      'foot' => SGN /* page footing */
  );

  public function __construct(&$tmp)
  /*
   * set up properties and temporary structures
   * in:  tmp -- gateway workarea
   */ {
    $this->data['txt'] = (object) ['syserr' => 'System Error'];
    parent::__construct($this->data);
    $this->temp = new stdClass();   /* init temporary data */
    $tmp = $this->temp;             /* point workarea to temporary data */
  }

  public function GetProperty($pth)
  /*
   * get common property value
   * in:  pth -- property path array
   * out: property's value
   */ {
    $ptr = & $this->prop;
    for ($i = 0; $i < count($pth) - 1; $i++) {/* move to terminal's parent */
      if (isset($ptr->$pth[$i])) {
        $ptr = & $ptr->$pth[$i];  /* get next node */
      } else {
        return null; /* undefined node */
      }
    }
    return isset($ptr->$pth[$i]) ? $ptr->$pth[$i] : null;     /* get a value */
  }

  public function SetProperty($pth, $val)
  /*
   * set common property value
   * in:  pth -- property path array
   *      val -- value to set
   */ {
    $ptr = & $this->prop;    /* point to properties structure */
    for ($i = 0; $i < count($pth) - 1; $i++) {/* move to terminal's parent */
      if (!isset($ptr->$pth[$i])) {
        $ptr->$pth[$i] = new stdClass();  /* set default if node does not exist */
      }
      $ptr = & $ptr->$pth[$i]; /* get next node */
    }
    $ptr->$pth[$i] = $val;     /* set a value */
  }

  public function GetSign()
  /*
   * form application signature
   */ {
    $prp = ¤::XmlSubArray('owr');
    $yr = date('Y');
    $year = $prp['year'] == $yr ? $yr : ($prp['year'] . ' - ' . $yr);
    $eml = $this->GetEmail($prp['mail']);
    $sgn = $prp['name'] . ' © ' . $year;
    $sgn .= ' <a href="mailto:' . $eml . '?subject=' . $prp['name'] . '">' . $prp['copr'] . '</a>';
    return $sgn;
  }

}

?>
