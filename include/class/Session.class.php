<?php

//require_once ('InterfaceMysqli.php');
require_once ('DB.php'); // PEAR::DB Class
require_once ('User.class.php');

/**
* Classe Session
*
* Session class enables to easily manage database driven user authentification and session variable persistence.
*
* (copyleft) 2005 damien corpataux <d@mien.ch>
*
* @access  public
* @version 0.1
* @author  damien corpataux <d@mien.ch>
* @package authentication
* @uses Utilisateur
* @uses Bdd_MySql
*/
Class Session {

  /**
  * Link to database.
  * @var MySqli object
  * @since 0.1
  */
  var $database;

  /**
  * Indexed array of inner error messages.
  * @var array
  * @since 0.1
  */
  var $errors;

  /**
  * Constructor for Utilisateur.
  *
  * @access public
  * @param string $dsn DSN for database service
  * @since 0.1
  */
  function Session($dsn, $dontCreateTables = false) {
    //@session_regenerate_id(); Don't use it with WaitingQueue.class.php
    @session_start();

    $this->dsn = $dsn;

    $this->database =& DB::connect($dsn);
    $this->database->setFetchMode(3); // Forces DB::fetchInto() to return an object
    if (DB::isError($this->database))
    {
      trigger_error ($this->errors[0], E_USER_ERROR);
    }

    if (!$dontCreateTables) {
      $this->createTables();
    }

    $this->errors = array (
      1 => "No identified user",
      2 => "Variable does not exist",
      3 => "Incorrect user password",
      4 => "Missing username/password for authentication",
    );
  }

  /**
  * Creates the necessary table for the class to work.
  * This is currently only tested on MySql databases.
  *
  * @access private
  * @since 0.1
  */
  function createTables() {
    $resultSession = $this->database->query('SELECT * FROM `sessionvariables`');

    if (DB::isError($resultSession)) { // If table 'sessionvariable' doesn't exist
      $this->database->query("
        CREATE TABLE `sessionvariables` (
          `user_username` varchar(64) NOT NULL ,
          `variableName` varchar(128) NOT NULL ,
          `variableValue` text,
          PRIMARY KEY  (`user_username`,`variableName`)
        );
      ");
    }
  }

  /**
  * Tries to login a user with the 'username' and 'password' parameters received via HTTP POST or GET methods.
  * If the required HTTP parameters aren't present or the user/password do not match, then the login form is printed out.
  *
  * @access public
  * @return bool Returns true if login succeeds, else returns false
  * @since 0.1
  */
  function login() {
    if (!$this->getLoggedUser()) {
      // Authentication process
      $user = new User($this->dsn);
      if (!empty($_REQUEST['username']) & !empty($_REQUEST['password'])
      && $user->auth($_REQUEST['username'], $_REQUEST['password'])) {
        // Log user by setting the 'loggedUserName' session variable
        $_SESSION['loggedUsername'] = $_REQUEST['username'];//$user->getId($_REQUEST['username']);
        return true;
      } else {
        return false;
        //trigger_error ($this->errors[3], E_USER_ERROR);
      }
    }
  }

  /**
  * Logs user out.
  *
  * @access public
  * @since 0.1
  */
  function logout() {
    unset ($_SESSION['loggedUsername']);
    @session_destroy();
  }

  /**
  * Returns the currently logged user's username.If no user is logged, false is returned.
  *
  * @access public
  * @return string Returns the username of the logged user, or false if no user is logged in.
  * @since 0.1
  */
  function getLoggedUser() {
    return !empty($_SESSION['loggedUsername']) ? $_SESSION['loggedUsername'] : false;
  }

  /**
  * Returns the currently logged user's role.If no user is logged, false is returned.
  *
  * @access public
  * @return string Returns the role of the logged user, or false if no user is logged in.
  * @since 0.1
  */
  function getUserRole() {
    if ($this->getLoggedUser())
    {
      $result = $this->database->query("
        SELECT role_name
        FROM `user`
        WHERE `username` = '".$this->getLoggedUser()."';
      ");
      $result->fetchInto($result);
      return $result->role_name;
    }
    else
    {
      return false;
    }
  }

  /**
  * Creates a new variable for the logged user.
  * A user must be logged in. If not, an error will be triggered.
  *
  * @access public
  * @param string $variableName Variable name to be created
  * @param string $variableValue Associated value to be set
  * @since 0.1
  */
  function createVar($variableName, $variableValue = null)
  {
    if ($this->getLoggedUser()) {
      $this->database->query("
        INSERT INTO `sessionvariables` ( `user_username` , `variableName` , `variableValue` )
        VALUES ('".($this->getLoggedUser())."', '".($variableName)."', null);
      ");
      if (!empty($variableValue)) {
        $this->setVarContent($variableName, $variableValue);
      }
    } else {
      trigger_error ($this->errors[1], E_USER_ERROR);
    }
  }

  /**
  * Removes an existing variable for the logged user.
  * A user must be logged in. If not, an error will be triggered.
  * The $variableName must exist. If not, an error will be triggered.
  *
  * @access public
  * @since 0.1
  * @param string $variableName Variable name to be removes
  * @todo Manage an "unexisting variable" error case.
  */
  function deleteVar($variableName) {
    if ($this->getLoggedUser()) {
      if ($this->varExists($variableName)) {
        $this->database->query("
          DELETE FROM `sessionvariables`
          WHERE `user_username` = '".($this->getLoggedUser())."'
            AND `variableName` = '".($variableName)."';
        ");
      } else {
        trigger_error ($this->errors[2], E_USER_ERROR);
      }
    } else {
      trigger_error ($this->errors[1], E_USER_ERROR);
    }
  }

  /**
  * Returns an existing variable's value for the logged user.
  * A user must be logged in. If not, an error will be triggered.
  * The $variableName must exist. If not, an error will be triggered.
  *
  * @access public
  * @since 0.1
  * @return string Return the
  * @param string $variableName Variable name of the associated value to be returned
  * @todo Manage an "unexisting variable" and a "null content" error case.
  */
  function getVarContent($variableName) {
    if ($this->getLoggedUser()) {
      if ($this->varExists($variableName)) {
        $result = $this->database->query("
          SELECT variableValue
          FROM `sessionvariables`
          WHERE `user_username` = '".($this->getLoggedUser())."'
            AND `variableName` = '".($variableName)."'
          LIMIT 1;
        ");
        $result = $this->database->getObject($result);
        return (unserialize($result->variableValue));
      } else {
        trigger_error ($this->errors[2], E_USER_ERROR);
      }
    } else {
      trigger_error ($this->errors[1], E_USER_ERROR);
    }
  }

  /**
  * Sets an existing variable's value for the logged user.
  * A user must be logged in. If not, an error will be triggered.
  * The $variableName must exist. If not, an error will be triggered.
  *
  * @access public
  * @since 0.1
  * @param string $variableName Variable name of the associated value to be set
  * @param string $variableValue Associated value to be set
  * @todo Manage an "unexisting variable" error case.
  */
  function setVarContent($variableName, $variableValue) {
    if ($this->getLoggedUser()) {
      if ($this->varExists($variableName)) {
        $variableValue = serialize($variableValue);
        $this->database->query ("
          UPDATE `sessionvariables`
          SET `variableValue` = '".($variableValue)."'
          WHERE CONVERT( `user_username` USING utf8 ) = '".($this->getLoggedUser())."'
            AND CONVERT( `variableName` USING utf8 ) = '".($variableName)."'
          LIMIT 1;
        ");
      } else {
        trigger_error ($this->errors[2], E_USER_ERROR);
      }
    } else {
      trigger_error ($this->errors[1], E_USER_ERROR);
    }
  }

  /**
  * Checks wether a variable name exists for the logged user.
  * A user must be logged in. If not, an error will be triggered.
  *
  * @access public
  * @param string $variableName Variable name to be checked
  * @since 0.1
  */
  function varExists($variableName) {
    if ($this->getLoggedUser()) {
      return $this->database->query ("
        SELECT variableValue
        FROM `sessionvariables`
        WHERE `user_username` = '".($this->getLoggedUser())."'
          AND `variableName` = '".($variableName)."';
      ");
    } else {
      trigger_error ($this->errors[1], E_USER_ERROR);
    }
  }


  /**
  * Returns all variable names for a logged user.
  * A user must be logged in. If not, an error will be triggered.
  *
  * @access public
  * @return MySqli
  * @since 0.1
  */
  function getVarNameList() {
    if ($this->getLoggedUser()) {
      $result = $this->database->query("
        SELECT variableName
        FROM `sessionvariables`
        WHERE `user_username` = '".($this->getLoggedUser())."';
      ");
      while ($result && $currentRow = Bdd_MySql::getArray($result)) {
        $variableNameArray[] = $currentRow;
      }
      return isset($variableNameArray) ? $variableNameArray : false;
    } else {
      trigger_error ($this->errors[1], E_USER_ERROR);
    }
  }

  function printVarList() {
    $varNameList = $this->getVarNameList();
    if ($varNameList) {
      foreach ($varNameList as $varName) {
        $varName = $varName[0];
        print "<b>$varName:</b> " . $this->getVarContent($varName) . '<br />';
      }
    } else {
      print "<b>No session variable to display...</b>";
    }
  }

}
