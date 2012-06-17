<?php
@session_start();

/**
 *	Mise en place l'infrastructure nécessaire au fonctionnement
 */
require_once ('include/config.inc.php');

require_once ($classDirectory.'WaitingQueue.class.php');
$queue =& new WaitingQueue($dsn);

require_once ($classDirectory.'Session.class.php');
$session =& new Session($dsn);

require_once ($classDirectory.'IhmMessage.class.php');
$message =& new IhmMessage();

require_once ($classDirectory.'QueryString.class.php');


//$tpl->debug=1;
// Charge le template principal de la page HTML
$tpl->setFile('index', 'index.tpl.html');
// Définit les valeurs du <head> HTML
$tpl->setVar('Stylesheet', 'css/style.css');
$tpl->setVar('TitrePage', 'WaitingQueue - the easiest queue management system');

// Charge le template contenant le frame (header, footer, left menu) du site
$tpl->setFile('frame', 'frame.tpl.html');

// Actions
require_once ($phpDirectory.'actions.inc.php');

// Include content (aiguillage)
require_once ($phpDirectory.'content.inc.php');

// Parse le template contenant le frame (header, footer, left menu) du site
$tpl->parse('BODY', 'frame');


// Parse le template principal 'index' et le place dans HTML_OUT
$tpl->parse( 'HTML_OUT', 'index');
// Affiche la page complète
$tpl->p( 'HTML_OUT' );

?>
