<?php

/**
 * PDO I/O layer
 *
 *
 * @package     System
 * @author      Vallo Reima
 * @copyright   (C)2010
 */
class Mypdo implements IData {

  public $dbDriver;  /* database driver */
  protected $dbLink;  /* handler object */
  protected $dbQuery;           /* result object */
  protected $record;            /* bind result record */

  public function __construct($srv) {
  // Setup the class
  // in:  srv -- database server
    $this->dbDriver = $srv;
  }

  public function Connect($nme, $hst, $prt, $usr, $psw) {
  // Create connection and select a database
  // in:  nme -- database
  //      hst -- host
  //      prt -- port
  //      usr -- user
  //      psw -- password
    $a = array(PDO::ATTR_ERRMODE => PDO::ERRMODE_SILENT,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC);
    if ($this->dbDriver == 'mysql') {
      $c = empty($prt) ? '' : ';port=' . $prt;
      $dns = 'mysql:host=' . $hst . $c . ';dbname=' . $nme . ';charset=' . ¤::CharSet(true);
      $a[PDO::ATTR_AUTOCOMMIT] = true;
      $a[PDO::MYSQL_ATTR_USE_BUFFERED_QUERY] = false;
    } else if ($this->dbDriver == 'sqlite') {
      $dns = 'sqlite:' . $nme;
    } else {
      return (array('nofeat', 'PDO::' . $this->dbDriver));
    }
    try {
      $this->dbLink = new PDO($dns, $usr, $psw, $a);
      $c = date_default_timezone_get();
      $this->dbLink->exec("SET time_zone = '{$c}'");
      $a = true;
    } catch (PDOException $e) {
      $c = (string) $e->getMessage();
      $e = strpos($c, '1049') ? 'nodb' : 'noserv';
      if (DEV) {
        $a = array(array($e, $dns), $c);
      } else {
        $c = $e == 'nodb' ? $nme : $this->dbDriver;
        $a = array($e, $c);
      }
    }
    return $a;
  }

  public function Begin()
  /*
   *  Start transaction
   */ {
    return $this->dbLink->beginTransaction();
  }

  public function Commit()
  /*
   *  Save changes
   */ {
    return $this->dbLink->commit();
  }

  public function Back()
  /*
   *  Undo changes
   */ {
    return $this->dbLink->rollBack();
  }

  public function FormCondValue($val)
  /*
   *  Get prepare value
   */ {
    return '?';
  }

  public function Quote($queryString, $queryArray) {
  // Prepare query/change statement
  // in: queryString - formed query string
    $this->dbQuery = $this->dbLink->prepare($queryString);
    return ($this->dbQuery ? true : false);
  }

  public function Count($queryArray) {
  // Count records meeting select conditions
  // in: queryArray - prepared statement array
    if ($this->dbQuery->execute($queryArray)) {
      return (int) $this->dbQuery->fetchColumn();
    } else {
      return false;
    }
  }

  public function Query($queryArray, $queryFields) {
  // Run prepared query (Select)
  // in: queryArray - prepared statement array
  //     queryFields - selected fields
    if ($this->dbQuery->execute($queryArray)) {
      return true;
    } else {
      return false;
    }
    /*    waiting for   php >= 5.3 */
  }

  public function FetchResult() {
  // Return: all records from result set
    return $this->dbQuery->fetchAll(PDO::FETCH_ASSOC);
    /*    waiting for   php >= 5.3 */
  }

  public function FetchRecord() {
  // Return: a record from result set
    return $this->dbQuery->fetch();
    /*    waiting for   php >= 5.3 */
  }

  public function Change($queryArray) {
  // Make prepared change (update/insert/delete)
  // in: queryArray - prepared statement array
    return $this->dbQuery->execute($queryArray);
  }

  public function InsertedId()
  /*
   *  Get Id of last inserted row
   */ {
    return (int) $this->dbLink->lastInsertId();
  }

  public function AffectedRows() {
  // Get number of last affected (changed) rows
    if ($this->dbQuery) {
      return $this->dbQuery->rowCount();
    } else {
      return 0;
    }
  }

  public function __destruct() {
  // Close a connection created earlier
    $this->FreeResult();
    $this->dbLink = null;
  }

  public function FreeResult() {
  // Free result set
    $this->dbQuery = null;
  }

  public function Error($dbh = false)
  /*
   *  Supply error information
   * in:  dbh -- true - database handle info
   */ {
    if ($dbh) {
      $h = $this->dbLink ? $this->dbLink : $this->dbQuery;
    } else {
      $h = $this->dbQuery ? $this->dbQuery : $this->dbLink;
    }
    return $h->errorInfo();
  }

}