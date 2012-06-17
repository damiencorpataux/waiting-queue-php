<?php

header("Refresh: 15;");

$tpl->setFile('showQueue', 'showQueue.tpl.html');

$tpl->setVar('queueNameDisplay', $_GET['showQueue']);

$tpl->setBlock('showQueue', 'listItem', 'listItems');
$queueItemsList = $queue->listUserQueueItems($_GET['showQueue'], $session->getLoggedUser());
while ($queueItemsList->fetchInto($row))
{
  $submitDate = date('d-m-Y H:i', strtotime($row->submitDate));
  $tpl->setVar('studentNameDisplay', '<b>' . $row->studentName . '</b> @ ' . $submitDate);
  $tpl->setVar('queueItemId', $row->id);
  $tpl->parse('listItems', 'listItem', true);
}

?>
