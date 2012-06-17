<?php

// Définit les répertoires de travail
$includeDirectory = 'include/';
$phpDirectory = $includeDirectory.'php/';
$classDirectory = $includeDirectory.'class/';
$imageDirectory = 'image/';
$pearDirectory = './include/PEAR/';
$templateDirectory = $includeDirectory.'template';

// Définit les paramètres de connexion DB
$bdDriver = 'mysql';
$bdHostname = 'localhost';
$bdUsername = 'waitingqueue';
$bdPassword = 'respectplease';
$bdName = 'waitingqueue';
$bdPortNumber = null;
$dsn = array(
  'phptype'  => $bdDriver,
  'username' => $bdUsername,
  'password' => $bdPassword,
  'hostspec' => $bdHostname,
  'database' => $bdName,
	'port'    => $bdPortNumber
);

ini_set('include_path', $pearDirectory);
require_once ( 'DB.php' );
require_once ( 'HTML/Template/PHPLIB.php' );

$tpl =& new Template_PHPLIB($templateDirectory);

?>
