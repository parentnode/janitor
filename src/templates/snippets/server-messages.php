<?php

$type = false;
$reset = true;

$messages = false;

if($_options !== false) {
	foreach($_options as $_option => $_value) {
		switch($_option) {
			case "type"            : $type         = $_value; break;
			case "reset"           : $reset        = $_value; break;
		}
	}
}


if($type) {
	$messages = message()->getMessages(["type" => $type]);
}
else {
	$messages = message()->getMessages();
}


if($reset) {
	message()->resetMessages();
}


if($messages): 
	// debug([$messages]);
?>
	<div class="messages">
<?
	if($type):

		foreach($messages as $message):
?>
		<p class="<?= $type ?>"><?= $message ?></p>
<?
		endforeach;

	else:

		foreach($messages as $mesage_type => $messages_of_type):
			foreach($messages_of_type as $message):
?>
		<p class="<?= $mesage_type ?>"><?= $message ?></p>
<?
			endforeach;
		endforeach;

	endif;
?>
	</div>
<? endif;
