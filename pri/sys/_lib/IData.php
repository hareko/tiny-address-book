<?php
 /**
 * database I/O layer interface
 *
 * @package     System
 * @author      Vallo Reima
 * @copyright   (C)2010
*/

interface IData
{
  public function Connect($dbn,$hst,$prt,$usr,$psw);
  // Create connection and select a database
  // in:  dbn -- database
  //      hst -- host
  //      prt -- port
  //      usr -- user
  //      psw -- password

  public function Begin();
  /*
   *  Start transaction
  */

  public function Commit();
  /*
   *  Save changes
  */

  public function Back();
  /*
   *  Undo changes
  */
  
  public function FormCondValue($val);
  /*
   *  Get prepare value
  */
  
  public function Quote($queryArray,$queryFields);
  // Prepare query/change statement
  // in: queryArray - prepared statement array
  //     queryFields - selected fields

  public function Count($queryArray);
  // Count records meeting select conditions
  // in: queryArray - prepared statement array

  public function Query($queryArray,$queryFields);
  // Run prepared query (Select)
  // in: queryFields - selected fields
  //     queryArray - prepared statement array

  public function FetchResult();
  // Return: all records from result set

  public function FetchRecord();
  // Return: a record from result set

  public function Change($queryArray);
  // in: queryArray - prepared statement array
  // Make prepared change (update/insert/delete)
  public function InsertedId();
  // Get Id of last inserted row

  public  function AffectedRows();
  // Get number of last affected (changed) rows

  public  function __destruct();
  // Close a connection created earlier

  public function FreeResult();
  // Free result set

  public function Error($dbh=false);
  // Supply error information
}

?>
