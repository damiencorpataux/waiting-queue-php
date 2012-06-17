<?php

header ("Refresh: 15;");

if ($message->getMessage(false))
{
  header("Refresh: 3; URL=".QueryString::getCurrentUrl());
}

$tpl->setFile('addQueueItem', 'addQueueItem.tpl.html');

$tpl->setVar('queueNameDisplay', $queue->getQueueName());

// Print queueInfo for user if a ticket has been taken
$tpl->setBlock('addQueueItem', 'queueInfoItem', 'queueInfoItems');
$tpl->setBlock('addQueueItem', 'queueInfo', 'queueInfos');
$studentQueueItemsList = $queue->listStudentQueueItems($queue->getStudentName());
if ($studentQueueItemsList->numRows())
{
  while ($studentQueueItemsList->fetchInto($row))
  {
    $tpl->setVar('ticketPosition', $queue->getQueueItemPosition($row->id));
    $submitDate = date('d-m-Y H:i', strtotime($row->submitDate));
    $tpl->setVar('ticketDate', $submitDate);
    $tpl->setVar('queueItemId', $row->id);
    $tpl->parse('queueInfoItems', 'queueInfoItem', true);
  }
  $tpl->parse('queueInfos', 'queueInfo');
}


?>
