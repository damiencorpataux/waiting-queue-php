<?php

$tpl->setFile('listQueues', 'listQueues.tpl.html');

$tpl->setBlock('listQueues', 'listItem', 'listItems');

$queueList = $queue->listUserQueues($session->getLoggedUser());
while ($queueList->fetchInto($row))
{
  $tpl->setVar('queueNameDisplay', $row->name);
  $tpl->setVar('queueName', $row->name);
  $tpl->parse('listItems', 'listItem', true);
}

?>
