<?php
// Clear queues that are older than 10 hours
$queue->delOldQueues(10*60);

// Queue subscription
if (isset($_POST['queueName']) and isset($_POST['password']))
{
  $logged = $queue->auth($_POST['queueName'], $_POST['password']);
  if (!($logged and $queue->isAuth()))
  {
    $message->setError('QUEUE_LOGIN_FAILED');
  }
}

// Queue add (create a queue)
if (isset($_POST['addQueue']) && $session->getUserRole() == 'teacher')
{
  // TODO: Input tests
  $queueName = $_POST['queueName'];
  $queuePassword = $_POST['queuePassword'];
  $queueComment = $_POST['queueComment'];
  if ($queue->addQueue ($session->getLoggedUser(), $queueName, $queueComment, $queuePassword))
  {
    $message->setMessage('QUEUE_ADDED');
  }
  else
  {
    $message->setError('QUEUE_ADDING_FAILED');
  }

}

// Queue item add (take a ticket)
if (isset($_POST['addQueueItem']) && $queue->isAuth())
{
  // TODO: Input tests
  //$comment = $_POST['comment'];
  $comment = null;
  if ($queue->addQueueItem ($queue->getQueueName(), $queue->getStudentName(), $comment))
  {
    $message->setMessage('QUEUEITEM_ADDED');
  }
  else
  {
    $message->setError('QUEUEITEM_ADDING_FAILED');
  }
}

// Queue item delete (question answered)

if (isset($_POST['delQueueItem']) && (
  $session->getLoggedUser() // A logged user can delete a queueItem
  || $queue->queueItemBelongsToStudent($_POST['queueItemId'], $queue->getStudentName()) // A student can delete HIS queueItems
))
{
  // TODO: Input tests
  $id = $_POST['queueItemId'];
  if ($queue->delQueueItem ($id))
  {
    $message->setMessage('QUEUEITEM_DELETED');
  }
  else
  {
    $message->setError('QUEUEITEM_DELETION_FAILED');
  }
}

// User login
if (isset($_POST['userLogin']) && !$session->getLoggedUser())
{
  $session->login();
  if (!$session->getLoggedUser())
  {
    $message->setError('USERLOGIN_FAILED');
  }
}

// User logout
if (isset($_GET['logout']))
{
  $session->logout();
  $queue->logout();
}
?>
