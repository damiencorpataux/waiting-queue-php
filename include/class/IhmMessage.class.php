<?php

/**
* Class Error
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
Class IhmMessage {

  /**
  * Indexed array for error msgs.
  * @var string array
  * @since 0.1
  */
  var $errors;

  /**
  * Indexed array for messages.
  * @var string array
  * @since 0.1
  */
  var $messages;

  /**
  * Constructor for IhmMessage.
  *
  * @access public
  * @param
  * @since 0.1
  */
  function IhmMessage() {
/*
    // English messages
    $this->errors = array (
      'QUEUE_LOGIN_FAILED' => "Wrong password entered for this queue... Please try again.",
      'QUEUE_ADDING_FAILED' => "The queue was not created... Please try again.",
      'QUEUEITEM_ADDING_FAILED' => "Ticket hasn't been taken... Please try again.",
      'QUEUEITEM_DELETION_FAILED' => "Answered ticket has not been deleted... Has it already been deleted?",
      'USERLOGIN_FAILED' => "Wrong username or password entered, please try again.",
    );
    $this->messages = array (
      'QUEUE_ADDED' => "Queue has been added and is open.",
      'QUEUEITEM_ADDED' => "Ticket has been taken. <a href=\"?\">OK!</a>",
      'QUEUEITEM_DELETED' => "Answered ticket has been deleted.",
    );
*/
    // French messages
    $this->errors = array (
      'QUEUE_LOGIN_FAILED' => "Mauvais mot de passe pour cette file d'attente. Veuillez réessayer.",
      'QUEUE_ADDING_FAILED' => "La file d'attente n'a pas été créée. Veuillez réessayer.",
      'QUEUEITEM_ADDING_FAILED' => "Le ticket n'a pas pu être pris. Veuillez réessayer.",
      'QUEUEITEM_DELETION_FAILED' => "Le ticket n'a pas pu être supprimé.",
      'USERLOGIN_FAILED' => "Mauvais utilisateur ou mot de passe.",
    );
    $this->messages = array (
      'QUEUE_ADDED' => "File d'attente créée.",
      'QUEUEITEM_ADDED' => "Ticket pris.",
      'QUEUEITEM_DELETED' => "Ticket supprimé.",
    );
  }
  /**
  *
  *
  * @access private
  * @since 0.1
  */
  function setError($errorIndex) {
    $_SESSION['errorIndex'] = $errorIndex;
  }
  /**
  *
  *
  * @access private
  * @since 0.1
  */
  function getErrorMessage($clearError = true) {
    $errorMessage = isset($_SESSION['errorIndex']) ? $this->errors[$_SESSION['errorIndex']] : null;
    if ($clearError == true)
    {
      $_SESSION['errorIndex'] = null;
      unset ($_SESSION['errorIndex']);
    }
    return $errorMessage;
  }

  /**
  *
  *
  * @access private
  * @since 0.1
  */
  function setMessage($messageIndex) {
    $_SESSION['messageIndex'] = $messageIndex;
  }
  /**
  *
  *
  * @access private
  * @since 0.1
  */
  function getMessage($clearMessage = true) {
    $message = isset($_SESSION['messageIndex']) ? $this->messages[$_SESSION['messageIndex']] : null;
    if ($clearMessage == true)
    {
      $_SESSION['messageIndex'] = null;
      unset ($_SESSION['messageIndex']);
    }
    return $message;
  }
}

?>
