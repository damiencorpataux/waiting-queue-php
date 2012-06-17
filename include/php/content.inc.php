<?php

if ($session->getLoggedUser() && $session->getUserRole() == 'teacher')
{
  $tpl->setVar('WelcomeTitle', 'Welcome, '.$session->getLoggedUser());
  $tpl->setVar('WelcomeMessage', 'You can now manage your queues');

  if (isset($_GET['showQueue']))
  {
    $action = 'showQueue';
  }
  elseif (isset($_GET['addQueue']))
  {
    $action = 'addQueue';
  }
  else
  {
    $action = 'listQueues';
  }
}
elseif ($queue->isAuth())
{
  $tpl->setVar('WelcomeTitle', 'Welcome, '.$queue->getStudentName());
  $tpl->setVar('WelcomeMessage', 'You can now take a ticket for queue <i>'.$queue->getQueueName().'</i>');

  $action = 'addQueueItem';
}
elseif (isset($_GET['login']))
{
  $tpl->setVar('WelcomeTitle', 'Welcome');
  $tpl->setVar('WelcomeMessage', 'Please logon...');

  $action = 'userLogin';
}
else
{
  $tpl->setVar('WelcomeTitle', 'Welcome');
  $tpl->setVar('WelcomeMessage', 'Please log into a queue...');

  $action = 'queueLogin';
}



switch ($action)
{
  case 'showQueue':
  break;
  case 'listQueues':
  break;
  case 'addQueue':
  break;
  case 'userLogin':
  break;
  case 'addQueueItem':
  break;
  case 'queueLogin':
  break;
}

include ($action . '.inc.php');

// Include teacherMenu if logged user has a 'teacher' role
$tpl->setBlock('frame', 'teacherMenu', 'teacherMenus');
$tpl->setBlock('frame', 'studentMenu', 'studentMenus');
if ($session->getUserRole() == 'teacher')
{
  $tpl->parse('teacherMenus', 'teacherMenu');
}
// Else, include studentMenu
elseif ($queue->isAuth())
{
  $tpl->parse('studentMenus', 'studentMenu');
}

// Show message if present
$tpl->setBlock('frame', 'errorMessageBlock', 'errorMessageBlocks');
$tpl->setBlock('frame', 'messageBlock', 'messageBlocks');
if ($message->getErrorMessage(false))
{
  $tpl->setVar('errorMessageDisplay', $message->getErrorMessage());
  $tpl->parse('errorMessageBlocks', 'errorMessageBlock');
}
elseif ($message->getMessage(false))
{
  $tpl->setVar('message', $message->getMessage());
  $tpl->parse('messageBlocks', 'messageBlock');
}

$tpl->parse('CONTENT', $action);


?>
