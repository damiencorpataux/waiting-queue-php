<?php

$tpl->setFile('queueLogin', 'queueLogin.tpl.html');

$tpl->setBlock('queueLogin', 'queueNameOption', 'queueNameOptions');

$queueList = $queue->listQueues();
while ($queueList->fetchInto($row))
{
  $tpl->setVar('queueName', $row->name);
  $tpl->setVar('queueNameDisplay', $row->name);
  $tpl->parse('queueNameOptions', 'queueNameOption', true);
}

?>
