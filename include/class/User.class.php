<?php

require_once ('DB.php');

/**
* Classe User
*
* User class enables to easily manage users stored in a mysql database.
*
* (copyleft) 2005 damien corpataux <d@mien.ch>
*
* @access  public
* @version 0.1
* @author  damien corpataux <d@mien.ch>
* @package authentication
* @uses Bdd_MySql
*/
Class User {

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
  * Constructor for User.
  *
  * @access public
  * @param string $bdDriver Driver for database service (e.g. mysql)
  * @param string $bdHost Hostname for database service
  * @param string $bdUser Username for database authentication
  * @param string $bdPassword Associated password for database authentication
  * @param string $bdName Name of the database
  * @since 0.1
  */
  function User($dsn, $dontCreateTables = false) {
    $this->errors = array (
      1 => "Specified user already exists.",
      2 => "Specified user does not exist.",
      3 => "..."
    );

    $this->database =& DB::connect($dsn);
    if (DB::isError($this->database))
    {
      trigger_error ($this->errors[1], E_USER_ERROR);
    }

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
    $resultUser = $this->database->query('SELECT * FROM `user`');
    $resultRole = $this->database->query('SELECT * FROM `role`');

    if (DB::isError($resultRole)) { // If table 'role' doesn't exist
      $this->database->query("
        CREATE TABLE `role` (
          `name` varchar( 128 ) NOT NULL ,
          `description` text ,
          PRIMARY KEY ( `name` )
        );
      ");
      $this->database->query("
        INSERT INTO `role` ( `name` , `description` )
        VALUES (
        'teacher', 'A teacher can edit queues and queueitems.'
        );
      ");
    }

    if (DB::isError($resultUser)) { // If table 'user' doesn't exist
      $this->database->query("
        CREATE TABLE `user` (
          `username` varchar(64) NOT NULL default '',
          `password` varchar(128) NOT NULL default '',
          `name` varchar(128) NOT NULL default '',
          `surname` varchar(128) NOT NULL default '',
          `email` text,
          `role_name` varchar(128) NOT NULL default '',
          PRIMARY KEY  (`username`)
        );
      ");
      $this->database->query("
        INSERT INTO `user` ( `username` , `password` , `name` , `surname` , `email` , `role_name` )
        VALUES (
          'teacher', MD5( 'kidsalone' ) , 'Generic teacher role account.', 'NA', NULL , 'teacher'
        );
      ");
    }
  }

  /**
  * Adds a user in the database.
  * The $username must not exist in the database. If it does, an error will be triggered.
  *
  * @access public
  * @param string $username Login name of the user (identifier)
  * @param string $password Password for the user (clear text, will be hashed inside th function)
  * @since 0.1
  */
  function add ($username, $password, $email = null) {
    var_dump ($this->usernameExists($username));
    if (!$this->usernameExists($username)) {
      $this->database->Query("
        INSERT INTO `user` ( `username` , `password` , `email` )
        VALUES ('".($username)."', MD5( '$password' ) , '".($email)."');
      ");
    } else {
      // Erreur: l'utilisateur existe deja
      trigger_error ($this->errors[1], E_USER_ERROR);
    }
  }

  /**
  * Removes a user in the database.
  * The $username must exist in the database. If it doesn't, an error will be triggered.
  *
  * @access public
  * @param string $username Login name of the user (identifier)
  * @param string $password Password for the user (clear text, will be hashed inside th function)
  * @since 0.1
  */
  function del($username) {
    if ($this->usernameExists($username)) {
      $this->database->Query("
        DELETE FROM `user`
        WHERE CONVERT( `username` USING utf8 ) = '".($username)."'
        LIMIT 1;
      ");
    } else {
      // Erreur: l'utilisateur n'existe pas
      trigger_error ($this->errors[2], E_USER_ERROR);
    }
  }

  /**
  * Changes the password password of an existing user.
  * The $username must exist in the database. If it doesn't, an error will be triggered.
  *
  * @access public
  * @param string $username Login name of the user whose password is to be changed
  * @param string $password New password for the user (clear text, will be hashed inside th function)
  * @since 0.1
  */
  function changePassword ($username, $newPassword) {
    if ($this->usernameExists($username)) {
      $this->database->Query("
        UPDATE `user`
        SET `password` = MD5( '$newPassword' )
        WHERE CONVERT( `username` USING utf8 ) = '".($username)."'
        LIMIT 1 ;
      ");
    } else {
      // Erreur: l'utilisateur n'existe pas
      trigger_error ($this->errors[2], 2);
    }
  }

  /**
  * Checks wheter a username exists or not in the database.
  *
  * @access public
  * @return bool Returns true if the specified $username exists in the user database, false otherwise
  * @param string $username Login name of the user whose password is to be changed
  * @since 0.1
  */
  function usernameExists ($username) {
    $result = $this->database->Query("
      SELECT username
      FROM `user`
      WHERE CONVERT( `username` USING utf8 ) = '".($username)."'
    ");
    return ($result);
  }

  /**
  * Checks whether the specified username and password match or not.
  *
  * @access public
  * @return bool Returns true if the specified $username matches the specified $password, false otherwise
  * @param string $username Login name of the user whose password is to be checked
  * @param string $password Password to be checked (clear text)
  * @since 0.1
  */
  function auth ($username, $password) {
    if ($this->usernameExists($username)) {
      return $this->database->Query("
        SELECT username
        FROM `user`
        WHERE `username` = '".($username)."'
          AND `password` = MD5('$password');
      ");
    } else {
      // Erreur: l'utilisateur n'existe pas
      trigger_error ($this->errors[2], E_USER_ERROR);
    }
  }

  /**
  * Returns the id of the specified username. In other words, this function return the username of the specified username :-).
  *
  * @access public
  * @return string Returns the specified $username
  * @param string $username Login name of the user
  * @since 0.1
  */
  function getId ($username){
    $result = $this->database->Query("
      SELECT username
      FROM `user`
      WHERE CONVERT( `username` USING utf8 ) = '".($username)."'
    ");
    return ($result->username);
  }
}

?>
