<?php

require_once ('DB.php');

/**
* Classe WaitingQueue
*
*
*
* (copyleft) 2005 damien corpataux <d@mien.ch>
*
* @access  public
* @version 0.1
* @author  damien corpataux <d@mien.ch>
* @package WaitingQueue
* @uses PEAR::DB
*/
Class WaitingQueue {

  /**
  * Link to database.
  * @var MySqli object
  * @since 0.1
  */
  var $database;

  /**
  * Indexed array of inner error messages.
  * @var string array
  * @since 0.1
  */
  var $errors;

  /**
  * Constructor for Utilisateur.
  *
  * @access public
  * @param string $bdHost Hostname for database service
  * @since 0.1
  */
  function WaitingQueue($dsn, $dontCreateTables = false) {
    $this->errors = array (
      1 => "Error connecting to database.",
      2 => "Specified queue already exists.",
      3 => "Specified queue does not exist."
    );
    
    @session_start();

    $this->database =& DB::connect($dsn);
    if (DB::isError($this->database))
    {
      trigger_error ($this->errors[1], E_USER_ERROR);
    }
    $this->database->setFetchMode(3); // Forces DB::fetchInto() to return an object

    if (!$dontCreateTables)
    {
      $this->createTables();
    }
  }

  /**
  * Creates the necessary table for the class to work.
  * This is currently only tested on MySql databases.
  *
  * @access private
  * @since 0.1
  */
  function createTables() {
    $resultQueue = $this->database->query('SELECT * FROM `queue`');
    $resultQueueItem = $this->database->query('SELECT * FROM `queueItem`');

    if (DB::isError($resultQueue)) { // If table 'queue' doesn't exist
      $this->database->query("
        CREATE TABLE `queue` (
          `creator` varchar( 128 ) NOT NULL ,
          `name` varchar( 128 ) NOT NULL ,
          `comment` text ,
          `password` varchar( 128 ) NOT NULL ,
          `creationDate` datetime NOT NULL ,
          `endDate` datetime NOT NULL ,
          PRIMARY KEY ( `name` )
        );
      ");
    }

    if (DB::isError($resultQueueItem)) { // If table 'queueItem' doesn't exist
      $this->database->query("
        CREATE TABLE `queueItem` (
          `id` int(11) NOT NULL auto_increment,
          `queueName` varchar(128) NOT NULL default '',
          `comment` text ,
          `submitDate` datetime NOT NULL default '0000-00-00 00:00:00',
          `studentName` varchar(128) NOT NULL default '',
          `studentSessionId` varchar(32) NOT NULL default '',
          PRIMARY KEY  (`id`)
        );
      ");
    }
  }

  /**
  * Checks if $password is correct for $queueName
  *
  * @access public
  * @since 0.1
  */
  function auth ($queueName, $queuePassword)
  {
    $result = $this->database->query("
      SELECT *
      FROM `queue`
      WHERE `password` = MD5('".$queuePassword."')
      AND `name` = '".$queueName."';
    ");
    if ($result->numRows())
    {
      $_SESSION['studentName'] = $_POST['studentName'];
      $_SESSION['queueName'] = $_POST['queueName'];
      return true;
    }
    else
    {
      return false;
    }
  }

  /**
  * Logs user out of the queue
  *
  * @access public
  * @since 0.1
  */
  function logout ()
  {
      $_SESSION['studentName'] = null;
      $_SESSION['queueName'] = null;
      unset($_SESSION['studentName']);
      unset($_SESSION['queueName']);
  }

  /**
  * Checks if $password is correct for $queueName
  *
  * @access public
  * @since 0.1
  */
  function isAuth()
  {
    return isset($_SESSION['studentName']);
  }

  /**
  * Returns ...
  *
  * @access public
  * @since 0.1
  */
  function getStudentName()
  {
    return isset($_SESSION['studentName']) ? $_SESSION['studentName'] : false;
  }

  /**
  * Returns ...
  *
  * @access public
  * @since 0.1
  */
  function getQueueName()
  {
    return isset($_SESSION['queueName']) ? $_SESSION['queueName'] : false;
  }

  /**
  * Returns a ... containing all the fields of all avaiable queues
  *
  * @access public
  * @since 0.1
  */
  function listQueues ()
  {
    return $this->database->query("
      SELECT *
      FROM `queue`
      WHERE 1;
    ");
  }

  /**
  * Returns a ... containing all the fields of all avaiable queues created by $username
  *
  * @access public
  * @since 0.1
  */
  function listUserQueues ($username)
  {
    return $this->database->query("
      SELECT *
      FROM `queue`
      WHERE `creator` = '".$username."';
    ");
  }

  /**
  * Creates a new queue in the database
  *
  * @access public
  * @since 0.1
  */
  function addQueue ($cretorUsername, $name, $comment, $password)
  {
    $result = $this->database->query("
      INSERT INTO `queue` ( `creator` , `name` , `comment` , `password` , `creationDate` , `endDate` )
      VALUES (
        '".$cretorUsername."', '".$name."', '".$comment."', MD5( '".$password."' ) , NOW( ) , NOW( )
      );
    ");
    return !DB::isError($result);
  }

  /**
  * Removes a queue in the database
  *
  * @access public
  * @since 0.1
  */
  function delQueue ($name)
  {
    $result = $this->database->query("
      DELETE FROM `queue` WHERE `name` = '".$name."';
    ");
    return DB::isError($result) ? false : $result;
  }

  /**
  * Returns a ... containing all the fields of queue items from queue $queueName, created by $username
  *
  * @access public
  * @since 0.1
  */
  function listQueueItems ($queueName)
  {
    return $this->database->query("
      SELECT *
      FROM `queueItem`, `queue`
      WHERE queueItem.queueName = queue.name
      AND queue.name = '".$queueName."'
      ORDER BY queueItem.submitDate ASC;
    ");
  }

  /**
  * Returns a ... containing all the fields of queue items from queue $queueName, created by $username
  *
  * @access public
  * @since 0.1
  */
  function listUserQueueItems ($queueName, $username)
  {
    return $this->database->query("
      SELECT *
      FROM `queueItem`, `queue`
      WHERE queueItem.queueName = queue.name
      AND queue.name = '".$queueName."'
      AND queue.creator = '".$username."'
      ORDER BY queueItem.submitDate ASC;
    ");
  }

  function listStudentQueueItems($studentName)
  {
    return $this->database->query("
      SELECT *
      FROM `queueItem`
      WHERE studentName = '".$studentName."'
      AND studentSessionId = '".session_id()."'
      ORDER BY submitDate ASC;
    ");
  }

  /**
  * Creates a new queue in the database
  *
  * @access public
  * @since 0.1
  */
  function addQueueItem ($queueName, $studentName, $comment)
  {
    $result = $this->database->query("
      INSERT INTO `queueItem` ( `id` , `queueName` , `comment` , `submitDate` , `studentName` , `studentSessionId`)
      VALUES (
        '', '".$queueName."', '".$comment."', NOW( ) , '".$studentName."' , '".session_id()."'
      );
    ");
    return !DB::isError($result);
  }

  /**
  * Removes a queue item in the database
  *
  * @access public
  * @since 0.1
  */
  function delQueueItem ($id)
  {
    $result = $this->database->query("
      DELETE FROM `queueItem` WHERE `id` = '".$id."'
      LIMIT 1;
    ");
    return DB::isError($result) ? false : $result;
  }

  function queueItemBelongsToStudent($queueItemId, $studentName)
  {
    $result = $this->database->query("
      SELECT *
      FROM `queueItem`
      WHERE studentName = '".$studentName."'
      AND studentSessionId = '".session_id()."'
      AND id = '".$queueItemId."';
    ");
    return $result->numRows();
  }

  // Returns the queue name for the $queueItemId
  function getQueueNameByQueueItemId($queueItemId)
  {
    $result = $this->database->query("
      SELECT queueName
      FROM `queueItem`
      WHERE id = ".$queueItemId."
      LIMIT 1;
    ");
    $result->fetchInto($result);
    return $result->queueName;
  }

  function getQueueItemPosition($queueItemId)
  {
    // Get the queueName
    $queueName = $this->getQueueNameByQueueItemId($queueItemId);
    // Get the first queueItem id
    $queueItems = $this->listQueueItems($queueName);
    $queueItems->fetchInto($firstQueueItem);
    $firstQueueItemId = $firstQueueItem->id;

    $result = $this->database->query("
      SELECT COUNT(id) AS position
      FROM `queueItem`
      WHERE queueName = '".$queueName."'
      AND id >= ".$firstQueueItemId."
      AND id <= ".$queueItemId."
      ORDER BY submitDate ASC;
    ");
    $result->fetchInto($result);
    return $result->position;
  }

  /**
  * Removes all the queues that are older than $minutesOld minutes, and all their items.
  *
  * @access public
  * @since 0.1
  */
  function delOldQueues($minutesOld)
  {
   /*
    * Delete all old queues' items
    */
    $date = date('Y-m-d H:i:s', time()-$minutesOld*60);
    $oldQueuesList = $this->database->query("
      SELECT name
      FROM `queue`
      WHERE creationDate < '"."$date"."';
    ");
    if ($oldQueuesList->numRows())
    {
      // For all old queues...
      while ($oldQueuesList->fetchInto($queueRow))
      {
        $oldQueueItemsList = $this->listQueueItems($queueRow->name);
        if ($oldQueueItemsList->numRows())
        {
          // ...delete all queue items...
          while ($oldQueueItemsList->fetchInto($queueItemRow))
          {
            $this->delQueueItem($queueItemRow->id);
          }
        }
        // ...and delete que queue.
        $this->delQueue($queueRow->name);
      }
    }

/*    $result = $this->database->query("
      DELETE FROM `queueItem` WHERE `id` = '".$id."'
      LIMIT 1;
    ");
    return DB::isError($result) ? false : $result;
*/
  }

}

?>
