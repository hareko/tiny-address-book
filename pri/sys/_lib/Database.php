<?php

/**
 * database abstraction layer class
 *
 * @package     Application
 * @author      Vallo Reima
 * @copyright   (C)2011
 */
class Database {
  /* Query modes */

  const Q_CNT = 1;           /* get count of records */
  const Q_RLT = 2;           /* get result set */
  const Q_ARR = 4;           /* get array of records */
  const Q_FLD = 8;           /* get fields info */
  const _AND = ' AND ';       /* and identifier */
  const _OR = ' OR ';        /* or identifier */
  const _NN = '<>';          /* ne null-default string */

  protected $jns = ['j' => 'JOIN', 'lj' => 'LEFT JOIN'];  /* join keywords */
  protected $dbp;      /* database properties object */
  protected $dbn;      /* database name */

  public function __construct($prps)
  /*
   * set database properties and connect
   * in:  prps -- configurations properties
   */ {
    $this->dbp = (object) ['srv' => 'mysql',
                'hst' => 'localhost',
                'prt' => '3306',
                'pfx' => '',
                'nme' => '',
                'prf' => '',
                'usr' => '',
                'pwd' => '',
                'lnk' => null,
                'cnt' => 0,
                'prp' => [],
                'err' => []
    ];
    foreach ($prps as $key => $val) {
      $this->dbp->$key = $val;
    }
    $this->dbn = $this->dbp->pfx . $this->dbp->nme;
    $this->Connect('mysql');
  }

  private function Connect($srv)
  /*
   *  connect database 
   *  in: srv -- server name
   *      chk -- check flag 
   *  out: link to database 
   */ {
    $dbm = 'pdo_' . $srv;
    $f = extension_loaded($dbm);
    if ($f) {
      $usr = $this->dbp->pfx . $this->dbp->usr;
      $this->dbp->lnk = new Mypdo($srv);
      $f = $this->dbp->lnk->Connect($this->dbn, $this->dbp->hst, $this->dbp->prt, $usr, $this->dbp->pwd);
    } else {
      $f = array(array('nofeat', $dbm), $srv);
    }
    if (is_array($f)) {
      $this->dbp->lnk = null;
      ¤::AppError($f[0], $f[1]);
    }
  }

  public function __call($mth, $arg) {
    // Call owner data mehod
    return call_user_func_array(array($this, "_$mth"), $arg);
  }

  protected function _Trans($act = null)
  /*
   *  transactions
   * in:  act -- null - begin
   *             true - commit
   *             false - rollback
   */ {
    $t = $this->dbp->lnk;
    if (is_null($act)) {
      $t = $t->Begin();
      if (!$t) {
        ¤::AppError('noacmt', $this->dbp->lnk->dbDriver);
      }
    } else if ($act) {
      $t = $t->Commit();
    } else {
      $t = $t->Back();
    }
    return $t;
  }

  protected function _Fields($tbn, $err = true)
  /*
   *  Get table fields info
   * in:  tbn -- table name
   *      err -- true - AppError
   */ {
    $b = $this->dbp->prp;
    $this->dbp->prp = array($this->dbp->prf . $tbn);
    $a = $this->Raw('SELECT ALL * FROM INFORMATION_SCHEMA.Columns WHERE TABLE_NAME=?', true);
    $this->dbp->prp = $b;
    if ($a) {
      return $this->Fields($a);
    } else if ($err) {
      ¤::AppError('nofdat', $tbn);
    } else {
      return false;
    }
  }

  private function Fields($flds)
  /*
   * convert fields info into array
   * in:  flds - fields array
   */ {
    $f = array();
    foreach ($flds as $fld) {
      if ($fld['TABLE_SCHEMA'] == $this->dbn) {
        $a = array('tpe' => $fld['DATA_TYPE']);
        if (empty($fld['NUMERIC_PRECISION'])) {
          $a['len'] = (int) $fld['CHARACTER_MAXIMUM_LENGTH'];
          $a['dec'] = null;
        } else {
          $a['len'] = (int) $fld['NUMERIC_PRECISION'];
          $a['dec'] = (int) $fld['NUMERIC_SCALE'];
        }
        $a['nul'] = ($fld['IS_NULLABLE'] == 'YES');
        $f[$fld['COLUMN_NAME']] = $a;
      }
    }
    return $f;
  }

  protected function _Tables($tbn = null)
  /*
   * get/check table list
   * in:  tbn -- table name
   * out: false -- err
   *        tbn - false/true
   *          else list
   */ {
    $this->dbp->prp = array();
    $a = $this->Raw('SHOW TABLES', true);
    if ($a) {
      $r = array();
      foreach ($a as $v) {
        $r[] = $v[key($v)];
      }
      if ($tbn) {
        $r = (array_search($this->dbp->prf . $tbn, $r) !== false);
      }
    } else {
      $r = array();
    }
    return $r;
  }

  protected function _Raw($qry, $rlt = false)
  /*
   * raw query
   * in:  qry -- query string
   * out: response
   */ {
    return $this->Raw($qry, $rlt);
  }

  protected function _Count($tbn, $slt = '', $cls = '', $opt = array())
  /*
   *  Get number of table records
   * in:  tbn -- table name(s)
   *      slt -- select condition
   *      cls - join tables condition
   *      opt -- options
   *        ids -- true - fetch id's
   */ {
    $c = empty($opt['ids']) ? 'COUNT(*)' : 'a.id';
    if (is_array($tbn)) {
      if (¤::IsAssoc($tbn)) {
        $a = array_keys($tbn);
      } else {
        $a = $tbn;
      }
      $t = array($a[0] => $c);
      for ($i = 1; $i < count($a); $i++) {
        $t[$a[$i]] = array();
      }
    } else {
      $t = $tbn;
      $cls = $c;
    }
    if (empty($opt['ids'])) {
      $a = $this->Select(self::Q_CNT, $t, $cls, $slt, $opt);
    } else {
      $a = $this->SelectArray(null, $t, $cls, $slt, '', $opt);
    }
    return $a;
  }

  protected function _Union($cmd, $cls, $qry, $opt = array())
  /*
   *  Select union
   * in:  cmd -- fetch/query
   *      cls -- columns list
   *      qry -- array of queries (table, condition,...)
   *      opt -- options
   */ {
    if (strtoupper(substr($cmd, 0, 1)) == 'F') {
      $k = strpos($cmd, ':');
      $k = (string) ($k ? substr($cmd, $k + 1) : '');
      return $this->SelectArray($qry, '', $cls, '', $k, $opt);
    } else {
      return $this->Select(array(self::Q_RLT, $qry), '', $cls, '', $opt);
    }
  }

  protected function _Query($tbn, $cls = '*', $slt = '', $opt = array())
  /*
   *  Select result set
   * in:  tbn -- table name
   *      cls -- select columns
   *      slt -- select conditions
   *      opt -- options
   */ {
    return $this->Select(self::Q_RLT, $tbn, $cls, $slt, $opt);
  }

  protected function _Fetch($tbn, $cls = '*', $slt = '', $key = '', $opt = array())
  /*
   *  Select data to array
   * in:  tbn -- table name
   *      cls -- select columns
   *      slt -- select conditions
   *      key -- key field name of associative array
   *              if empty then fill simple array
   *      opt -- options
   * out: arr - filled array
   */ {
    return $this->SelectArray(null, $tbn, $cls, $slt, $key, $opt);
  }

  protected function SelectArray($flag, $tbn, $cls, $slt, $key, $opt)
  /*
   *  Select data to array
   * in:  tbn -- table name
   *      cls -- select columns
   *      slt -- select conditions
   *      key -- key field name of associative array
   *              if empty then fill simple array
   *      opt -- options
   * out: arr - filled array
   */ {
    if (empty($key)) {
      $mode = 0;
      $k = self::Q_ARR;
    } else {
      $mode = 2;
      $k = self::Q_RLT;
      if (!is_array($tbn) && $cls != '*' && strpos($cls, $key) === false) {
        $cls .= ',' . $key;
      }
    }
    if (is_array($flag)) {
      $k = array($k, $flag);
    }
    $arr = $this->Select($k, $tbn, $cls, $slt, $opt);
    $f = ($arr === null || $arr === false);
    if (isset($arr[0]) && count($arr[0]) == 1) {
      $mode = 1;  /* 1 field only */
    }
    if ($f) {
      $arr = array();
    } else if ($mode == 2) {
      $arr = array();
      while ($row = $this->_Record()) {
        $cols = new ArrayIterator($row);
        $a = array();
        foreach ($cols as $fld => $val) {
          if ($fld == $key) {
            $k = $val;
          } else {
            $a[$fld] = $val;
          }
        }
        if (count($a) == 1) {
          $arr[$k] = $a[key($a)];
        } else {
          $arr[$k] = $a;
        }
      }
      $this->_Release();
    } else if ($mode == 1) {
      for ($i = 0; $i < count($arr); $i++) {
        $arr[$i] = reset($arr[$i]);
      }
    }
    $this->dbp->cnt = count($arr);
    return ($f ? null : $arr);
  }

  private function Select($flag, $table, $columns, $where, $options)
  /*
   *  Select data from the table
   * in:  flag -- Q_FLD - field info
   *              Q_CNT - count records
   *              Q_RLT - result set
   *              Q_ARR - fetch result
   *              array - union
   *      table -- table name
   *               array - table/fields list
   *      columns -- table columns list
   *                 array - join condition
   *      where -- conditions array or raw string
   *      options -- opr - operation (ALL, DISTINCT,...)
   *                 bin - BINARY:
   *                        true - yes
   *                 grp - GROUP BY
   *                 hvg - HAVING
   *                 ord - ORDER BY:
   *                       fields list
   *                       array - fields,true/false - ASC/DESC
   *                 lmt - LIMIT
   *                 prc - PROCEDURE
   *                 ito - INTO
   *                 lck - locking:
   *                          W - write lock
   *                          R - read/write lock
   */ {
    $this->dbp->prp = array();
    $this->dbp->cnt = 0;
    $this->dbp->err = array();
    $options['_ali'] = 0;   /* aliases counter */
    $options['_alio'] = 0;   /* order aliases counter */
    if (is_array($flag)) { /* union */
      $c = ' UNION ';
      $q = '';
      for ($i = 0; $i < count($flag[1]); $i = $i + 2) {
//        $cc = $this->Aliases($columns, $options['_ali']);
        $options['_ali'] = 0;
        $q .= '(' . $this->SelectClause($flag[1][$i], $columns, $flag[1][$i + 1], $options) . ')' . $c;
      }
      $q = substr($q, 0, strlen($q) - strlen($c));
      $flag = $flag[0];
    } else {
      if (isset($options['sbq'])) {
        $a = $this->SubQuery($options['sbq'], $where, $options);
      } else {
        $a = array();
      }
      $q = $this->SelectClause($table, $columns, $where, $options);
      $this->dbp->prp = array_merge($this->dbp->prp, $a);
    }
    if (isset($options['ord'])) {
      $q .= ' ORDER BY ' . $this->OrderBy($options);
    }
    if (isset($options['lmt'])) {
      $q .= ' LIMIT ' . $options['lmt'];
    }
    if (isset($options['lck']['W'])) {
      $q .= ' LOCK IN SHARE MODE';
    } else if (isset($options['lck']['R'])) {
      $q .= ' FOR UPDATE';
    }
    $dbh = $this->dbp->lnk;
    if (!$dbh->Quote($q, $this->dbp->prp)) {
      $this->BadQuery($q);
      return null;
    } else if ($flag == self::Q_CNT) {
      return $this->QueryCount($q, $dbh);
    } else {
      return $this->QueryResult($q, $columns, $flag, $dbh);
    }
  }

  private function SubQuery($sbq, &$slt, &$opt)
  /*
   * construct & add subquery condition
   * in: sbq -- subquery parameters:
   *            - all/any
   *            - field name
   *            - operator
   *            - query parameters (as select)
   *     slt -- select condition to modify
   *     opt -- modify alias counter
   * out: quote values
   */ {
    $sbq[4][3]['_ali'] = 0;
    $c = $this->SelectClause($sbq[4][0], $sbq[4][1], $sbq[4][2], $sbq[4][3]);
    if (isset($sbq[4][3]['lmt'])) {
      $c .= ' LIMIT ' . $sbq[4][3]['lmt'];
    }
    $sbq[1] = $this->Aliases($sbq[1], $sbq[4][3]['_ali']);
    $a = array($sbq[0] => array(array($sbq[1], 'sq', array($sbq[2], $sbq[3], $c))));
    $slt = $this->MergeCond($slt, $a);
    $a = $this->dbp->prp;
    $this->dbp->prp = array();
    $opt['_ali'] = $sbq[4][3]['_ali'];
    $opt['_alio'] = $opt['_ali'];
    return $a;
  }

  private function OrderBy($options)
  /*
   * construct ordr by clause
   * in: ord -- ordering conditions:
   *              string - asc list
   *              array:
   *                assoc - column/asc-desc-flag pairs
   *                  else list & asc/desc flag
   */ {
    if (¤::IsAssoc($options['ord'])) {
      $o = $options['ord'];
    } else {
      if (is_array($options['ord'])) {
        $a = $options['ord'];
      } else {
        $a = array($options['ord'], 1);
      }
      $b = mb_split(',', $a[0]);
      $o = array();
      foreach ($b as $c) {
        $o[$c] = $a[1];
      }
    }
    $c = '';
    foreach ($o as $key => $val) {
      $c .= ',' . $this->Aliases($key, $options['_alio']) . ' ' . ($val ? 'ASC' : 'DESC');
    }
    return substr($c, 1);
  }

  public function MergeCond($c1, $c2)
  /*
   * merge select conditions
   * in:  c1,c2 -- condition arrays
   */ {
    if (empty($c1) && empty($c2)) {
      $c0 = array();
    } else if (empty($c1)) {
      $c0 = $this->NormCond($c2);
    } else if (empty($c2)) {
      $c0 = $this->NormCond($c1);
    } else {
      $c1 = $this->NormCond($c1);
      $c2 = $this->NormCond($c2);
      $a = array_keys($c1);
      $b = array_keys($c2);
      if ($a[0] == $b[0]) {
        $c0 = array($a[0] => array_merge($c1[$a[0]], $c2[$a[0]]));
        /* $c0 = $c1;
          $c0[$a[0]][] = $c2[$a[0]]; */
      } else {
        $c0 = array('all' => array($c1, $c2));
      }
    }
    return $c0;
  }

  private function NormCond($c0)
  /*
   * normalize simple condition
   * in:  c0 -- condition array
   */ {
    if (!is_array(reset($c0))) {
      if (¤::IsAssoc($c0)) {
        $a = array();
        foreach ($c0 as $key => $val) {
          $a[] = array($key, 'eq', $val);
        }
      } else {
        $a = array($c0);
      }
      $a = array('all' => $a);
    } else {
      $a = $c0;
    }
    return $a;
  }

  private function SelectClause($table, $columns, $where, &$options)
  /*
   *  Form query clause
   */ {
    $cnt = $options['_ali'];
    if (isset($options['opr'])) {
      $c = $options['opr'];
    } else {
      $c = 'ALL';
    }
    $q = 'SELECT ' . $c . ' ';
    $tbp = $this->dbp->prf;
    if (!is_array($table)) { /* simple query */
      $w = '';
      if ($columns == '*') {
        $c = $columns;
      } else {
        list($table, $c) = $this->SelectTables(array($table => mb_split(',', $columns)), $tbp, $options['_ali']);
      }
    } else {
      if (is_array($columns) || empty($columns)) {
        $w = '';
      } else { /* inner join */
        $w = ' WHERE ' . $columns;
      }
      list($table, $c) = $this->SelectTables($table, $tbp, $options['_ali']);
    }
    $q .= $c;
    if (!empty($table)) {
      $q .= ' FROM ';
      if (is_array($columns)) { /* outer join */
        $c = mb_split(',', $table);
        $table = $c[0];
        for ($i = 0; $i < count($columns); $i = $i + 2) {
          $table .= ' ' . $this->jns[$columns[$i]] . ' ' . $c[$i / 2 + 1] . ' ON ' . $this->Aliases($columns[$i + 1], $cnt);
        }
      } else if (!strpos($table, '.') && !empty($tbp) && strpos($table, $tbp) === false) {
        $table = $tbp . $table;
      }
      $q .= $table;
    }
    if (!empty($where)) {
      if (empty($w)) {
        $w = ' WHERE ';
      } else {
        $w .= ' AND ';
      }
      $binary = !empty($options['bin']);
      $w .= $this->SetWhere($where, self::_AND, $binary, $cnt);
    }
    $q .= $w;
    return $q;
  }

  private function SelectTables($tbs, $tbp, &$cnt)
  /*
   *  Form query tables & selection fields list
   * in: tbs - table/field names array
   *     tbp - table name prefix
   *     cnt -- aliases counter
   * out: tables & filelds lists
   */ {
    $t = '';
    if (¤::IsAssoc($tbs)) {
      $c = '';
      $i = 0;
      foreach ($tbs as $key => $value) {
        $t .= $tbp . $key . ' ' . chr($cnt + 97) . ',';
        if ($value == '*') {
          $value = array_keys($this->_Fields($key));
        } else if (!is_array($value)) {
          $value = array($value);
        }
        for ($j = 0; $j < count($value); $j++) {
          if (strpos($value[$j], '"') === 0) {
            $value[$j] = ¤::mb_str_replace('"', '', $value[$j]);
          } else if (ctype_lower(substr($value[$j], 0, 1))) {
            $c .= chr($cnt + 97) . '.';
          } else if ($i < $cnt) { /* sbq's parent */
            $value[$j] = $this->Aliases($value[$j], $cnt - $i, $i);
//            $value[$j] = str_replace(chr($i + 97) . '.', chr($cnt + 97) . '.', $value[$j]); /* mysql function */
          }
          /* temporary until '/' -> '>' made */
          $c .= str_replace(array('/', '>'), array(' AS ', '/'), $value[$j]) . ',';
//            $c .= ¤::mb_str_replace('>', ' AS ', $value[$j]) . ',';
        }
        $cnt++;
        $i++;
      }
    } else {
      foreach ($tbs as $value) {
        $t .= $tbp . $value . ' ' . chr($cnt + 97) . ',';
        $cnt++;
      }
      $c = '*,';
    }
    return array(substr($t, 0, strlen($t) - 1), substr($c, 0, strlen($c) - 1));
  }

  private function QueryCount($qs, $qh)
  /*
   *  Count records meeting select conditions
   * in: qs - query string
   *     qh - handler
   */ {
    $n = $qh->Count($this->dbp->prp);
    if ($n === false) {
      $n = $this->SetError($qs);
    } else {
      $this->dbp->cnt = $n;
      $qh->FreeResult();
    }
    return $n;
  }

  private function QueryResult($qs, $qc, $qf, $qh)
  /*
   * Get query result set
   * in: qs - query string
   *     qc - guery columns
   *     qf - query flag
   *     qh - handler
   * out: ArrayIterator count
   * return: iterated result
   */ {
    if (!$qh->Query($this->dbp->prp, $qc)) {
      $r = $this->SetError($qs);
    } else if ($qf == self::Q_RLT) {
      $r = true;
    } else {
      $r = $qh->FetchResult();
      $qh->FreeResult();
    }
    return $r;
  }

  protected function _Record()
  /*
   *  return: current record from result set
   */ {
    return $this->dbp->lnk->FetchRecord();
  }

  protected function _Insert($table, $columns)
  /*
   * Add a record
   */ {
    $this->dbp->prp = array();
    $q = 'INSERT INTO ' . $this->dbp->prf . $table;
    $q .= ' SET ' . $this->SetWhere($columns, ',');
    $r = $this->Change($q);
    if (is_int($r)) {
      $dbh = $this->dbp->lnk;
      $r = $dbh->InsertedId();
    }
    return $r;
  }

  protected function _Update($table, $columns, $where, $binary = false)
  /*
   * Modify a record
   */ {
    $this->dbp->prp = array();
    $c = $this->dbp->prf;
    $q = 'UPDATE ' . $this->ChangeTables($table, $c);
    $q .= ' SET ' . $this->SetWhere($columns, ',');
    $q .= ' WHERE ' . $this->SetWhere($where, self::_AND, $binary);
    return $this->Change($q);
  }

  protected function _Delete($table, $where, $binary = false)
  /*
   * Erase a record
   */ {
    $this->dbp->prp = array();
    $c = $this->dbp->prf;
    list($c, $cc) = $this->ChangeTables($table, $c, true);
    $q = 'DELETE ' . $cc . ' FROM ' . $c;
    $q .= ' WHERE ' . $this->SetWhere($where, self::_AND, $binary);
    return $this->Change($q);
  }

  private function ChangeTables($tbs, $tbp, $flg = false)
  /*
   *  Form query tables list
   * in:  tbs - table name(s)
   *      tbp - table name prefix
   *      flg -- true - return delete fields
   * out: tables lists
   */ {
    $a = is_array($tbs) ? $tbs : array($tbs);
    $t = $c = '';
    $i = 0;
    for ($j = 0; $j < count($a); $j++) {
      $i++;
      $t .= $tbp . $a[$j] . ' ' . chr($i + 96) . ',';
      $c .= chr($i + 96) . '.*,';
    }
    $t = substr($t, 0, strlen($t) - 1);
    if ($flg) {
      return array($t, substr($c, 0, strlen($c) - 1));
    } else {
      return $t;
    }
  }

  private function Change($qs)
  /*
   * Execute Update/Insert/Delete
   * in:  qs -- query string
   */ {
    $this->dbp->cnt = 0;
    $this->dbp->err = array();
    $dbh = $this->dbp->lnk;
    if (!$dbh->Quote($qs, $this->dbp->prp)) {
      $this->BadQuery($qs);
      $r = null;
    } else if ($dbh->Change($this->dbp->prp)) {
      $this->dbp->cnt = $dbh->AffectedRows();
      $r = $this->dbp->cnt;
    } else {
      $r = $this->SetError($qs);
    }
    return $r;
  }

  private function Raw($qs, $rf)
  /*
   * make raw query
   * in:  qs -- query string
   *      rf -- true - fetch result
   */ {
    $this->dbp->err = array();
    $dbh = $this->dbp->lnk;
    if (!$dbh->Quote($qs, $this->dbp->prp)) {
      $this->BadQuery($qs);
      $r = null;
    } else if ($dbh->Query($this->dbp->prp, array())) {
      $r = $rf ? $dbh->FetchResult() : true;
      $dbh->FreeResult();
    } else {
      $r = $this->SetError($qs);
    }
    return $r;
  }

  private function SetWhere($w, $s, $b = false, $cnt = 0)
  /*
   *  Form set/where clause
   * in: $w -- conditions array or raw string
   *     $s -- separator
   *     $b -- binary flag
   * return: set/where string
   * 
   */ {
    $b = $b ? 'BINARY ' : '';
    if (empty($w)) {
      $c = '';
    } else if (!is_array($w)) {
      $c = $w;
    } else if (is_array(reset($w))) {
      $c = '';
      $this->FormWhere($w, $c, $b, $cnt);
    } else {
      $c = $this->FormCond($w, $s, $b);
    }
    return $c;
  }

  protected function FormWhere($cnds, &$cnd, $bin, $cnt)
  /*
   * Form multilevel where clause
   */ {
    foreach ($cnds as $key => $val) {
      $cnd .= '(';
      if ($key == 'raw') {
        $cnd .= $val;
      } else {
        for ($i = 0; $i < count($val); $i++) {
          if (¤::IsAssoc($val[$i])) {
            $this->FormWhere($val[$i], $cnd, $bin, $cnt);
          } else {
            $c = $val[$i][1];
            $this->GetWhere($val[$i]);
            if ($c == 'sq') { /* subquery */
              $cnd .= $val[$i][0] . $val[$i][1] . implode(' ', $val[$i][2]);
            } else if (empty($val[$i][0])) {
              $cnd .= ' ' . $val[$i][1] . ' ' . $val[$i][2];
            } else if (is_null($val[$i][2])) {
              $val[$i][0] = $this->Aliases($val[$i][0], $cnt);
              if ($val[$i][1] == self::_NN) {
                $cnd .= $val[$i][0] . ' is not null';
              } else {
                $cnd .= $val[$i][0] . ' is null';
              }
            } else if (is_array($val[$i][2])) { /* list */
              $c = '';
              for ($j = 0; $j < count($val[$i][2]); $j++) {
                $this->dbp->prp[] = $val[$i][2][$j];
                $c .= ',?';
              }
              $cnd .= $val[$i][0] . $val[$i][1] . '(' . substr($c, 1) . ')';
            } else {
              if (strpos($val[$i][0], '"') === false) {
                $v = $this->dbp->lnk->FormCondValue($val[$i][2]);
                $val[$i][0] = $this->Aliases($val[$i][0], $cnt);
              } else {
                $v = $val[$i][2];
                $val[$i][0] = ¤::mb_str_replace('"', '', $val[$i][0]);
              }
              $b = is_string($val[$i][2]) ? $bin : '';
              $c = $b . $val[$i][0] . $val[$i][1] . $v;
              if ($val[$i][1] == self::_NN) {
                $c = '(' . $c . ' || ' . $val[$i][0] . ' is null)';  /* null-default char field 'ne' */
              }
              $cnd .= $c;
              if ($v == '?') {
                $this->dbp->prp[] = $val[$i][2];
              }
            }
          }
          if (isset($val[$i + 1])) {
            $v = $key == 'all' ? self::_AND : self::_OR;
            $cnd .= ' ' . $v . ' ';
          }
        }
      }
    }
    $cnd .= ')';
  }

  protected function GetWhere(&$a)
  /*
   *  Get set/where clause component
   * in: $a -- component values - field,oper,value
   */ {
    switch ($a[1]) {
      case 'eq':
        $a[1] = '=';
        break;
      case 'ne':
        $a[1] = '!=';
        break;
      case 'nn':
        $a[1] = self::_NN;
        break;
      case 'gt':
        $a[1] = '>';
        break;
      case 'lt':
        $a[1] = '<';
        break;
      case 'ge':
        $a[1] = '>=';
        break;
      case 'le':
        $a[1] = '<=';
        break;
      case 'pf':
        $l = (string) strlen($a[2]);
        $a[0] = 'LEFT(' . $a[0] . ',' . $l . ')';
        $a[1] = '=';
        break;
      case 'sf':
        $l = (string) strlen($a[2]);
        $a[0] = 'RIGHT(' . $a[0] . ',' . $l . ')';
        $a[1] = '=';
        break;
      case 'in':
        $a[0] = "LOCATE('" . $a[2] . "'," . $a[0] . ')';
        $a[1] = '>';
        $a[2] = 0;
        break;
      case 'IN':
//        $a[0] = '"' . $a[0] . '"';
        $a[1] = ' IN ';
//        $a[2] = '(' . $a[2] . ')';
        break;
      case 'lk':
        $a[1] = ' LIKE ';
        break;
      case 'no':
        $a[1] = '!';
        break;
      case 'bw':
        $a[1] = ' BETWEEN ';
        $a[2] = $a[2][0] . ' AND ' . $a[2][1];
        break;
      case 'sq':
        if (empty($a[0])) {
          $a[1] = '';
        } else { /* $val[$i][0] . $val[$i][1] . implode(' ', $val[$i][2]); */
          $a[1] = $a[2][0];
        }
        $a[2] = array($a[2][1], '(' . $a[2][2] . ')');
        break;
    }
  }

  private function Aliases($str, $cnt, $frm = 0)
  /*
   *  replace aliases with current ones
   * in: str - query string
   *     cnt - current tables count
   *     frm - from alias
   */ {
    $a = array();
    $b = array();
    for ($i = $frm; $i < $cnt; $i++) {
      $a[] = chr($i + 97) . '.';
      $b[] = chr($i + $cnt + 97) . '.';
    }
    $c = str_replace($a, $b, $str);
    return $c;
  }

  protected function FormCond($cnd, $spr, $bin)
  /*
   *  Form set/where conditions fragment
   * in: $cnd -- conditions Array
   *              field, operand, value, separator
   *     $spr -- separator
   *     $bin -- binary keyword or ''
   * return: conditions string
   */ {
    $c = '';
    foreach ($cnd as $key => $val) {
      if (preg_match('/"(.*?)"/u', $key, $m) == 1) { /* in double quotes - don't prepare */
        $key = $m[1];
        $v = $val;
        $b = '';
      } else if ($key == 'raw') {
        $c .= $val . $spr;
        continue;
      } else {
        $v = $this->dbp->lnk->FormCondValue($val);
        $b = is_string($val) ? $bin : '';
      }
      if (is_null($val) && $spr != ',') {
        $c .= $key . ' is null' . $spr;
      } else {
        $c .= $b . $key . '=' . $v . $spr;
        if ($v == '?') {
          $this->dbp->prp[] = $val;
        }
      }
    }
    $c = substr($c, 0, strlen($c) - strlen($spr));
    return $c;
  }

  protected function _RowCount()
  /*
   *  Get number of last selected or affected rows
   */ {
    return $this->dbp->cnt;
  }

  protected function _Close() {
    $this->Disconnect();
  }

  public function __destruct()
  /*
   *  close a connection created earlier
   */ {
    $this->Disconnect();
  }

  private function Disconnect()
  /*
   *  close a connection created earlier
   */ {
    if (isset($this->dbp->lnk)) {
      $this->_Release();
      $this->dbp->lnk = null;
    }
  }

  protected function _Release()
  /*
   *  free query buffer
   */ {
    $this->dbp->cnt = 0;
    if (isset($this->dbp->lnk)) {
      $this->dbp->lnk->FreeResult();
    }
  }

  private function BadQuery($t = '')
  /*
   * Form error message and quit
   */ {
    $cc = 'badqry';
    if (empty($t)) {
      $c = $cc;
    } else {
      $c = array($cc, $t);
    }
    $a = $this->dbp->lnk->Error();
    $cc = '';
    if (isset($a[0])) {
      $cc = $a[0];
      if (isset($a[1])) {
        $cc .= '/' . $a[1];
      }
      if (isset($a[2])) {
        $cc .= ':' . $a[2];
      }
    }
    ¤::AppError($c, $cc, 1);
  }

  private function SetError($qs)
  /*
   * Analyze error code, save message
   */ {
    $e = null;
    $a = $this->dbp->lnk->Error();
    if ($a[0] == '23000' || $a[0] == 'HY000') { /* Integrity constraint violation */
      if ($a[1] == '1062') { /* Duplicate entry */
        $e = 'errdpl';
      } else if ($a[1] == '1451') { /* Restricted entry */
        $e = 'errdel';
      }
    } else if ($a[0] == '42S02' && $a[1] == '1146') { /* table not found */
    }
    $this->dbp->err['code'] = $a[1];
    if (is_null($e)) {
//      $this->dbp->err['code'] = $a[1];
      if (strpos($a[2], ':')) {
        $a[2] = substr($a[2], 0, strpos($a[2], ':'));
      }
      $this->dbp->err['text'] = $a[2];
    } else {
//      $this->dbp->err['code'] = $e;
      $this->dbp->err['text'] = ¤::_("txt.$e");
      $e = false;
    }
    return $e;
  }

  protected function _Error($tpe = 'text')
  /*
   * get last error info
   */ {
    if (empty($this->dbp->err)) {
      $a = array('code' => 0, 'text' => '');
    } else {
      $a = $this->dbp->err;
    }
    return is_null($tpe) ? $a : $a[$tpe];
  }

}