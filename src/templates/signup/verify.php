<?php
global $action;
global $model;

$IC = new Items();
$page_item = $IC->getItem(array("tags" => "page:signup-verify", "extend" => array("user" => true, "tags" => true, "mediae" => true)));
if($page_item) {
	$this->sharingMetaData($page_item);
}

?>
<div class="scene signup i:signup">
	
	<h1>Your account has been created!</h1>
	<h2>We've sent you a verification email</h2>
	<p>The email contains a verification code which you can use in the input field below.</p>
	<p>Alternatively the email also has a link you can use instead to verify.</p>
	<p>If you don't want to verify now, you can do so later through the link.</p>

	<?= $model->formStart("/verify/confirm", ["class" => "verify_code"]) ?>

<?	if(message()->hasMessages(array("type" => "error"))): ?>
		<p class="errormessage">
<?		$messages = message()->getMessages(array("type" => "error"));
		message()->resetMessages();
		foreach($messages as $message): ?>
			<?= $message ?><br>
<?		endforeach;?>
		</p>
<?	endif; ?>

		<fieldset>
			<?= $model->input("verification_code"); ?>
		</fieldset>

		<ul class="actions">
			<?= $model->submit("Verify email", array("class" => "primary", "wrapper" => "li.reset")) ?>
			<li class="skip"><a href="skip" class="button">Skip</a></li>
		</ul>
	<?= $model->formEnd() ?>

</div>
