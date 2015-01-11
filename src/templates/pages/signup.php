<?php
global $action;
global $model;

// in case of signup failure, empty password field
$model->setProperty("password", "value", "");

?>
<div class="scene signup i:signup">

	<h1>Sign up</h1>
	<p>
		Enter your Name, Email and password below to complete sign up.
	</p>
	<?= $model->formStart("save", array("class" => "labelstyle:inject")) ?>

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
			<?= $model->input("nickname", array("required" => true)); ?>
			<?= $model->input("email", array("required" => true)); ?>
			<?= $model->input("password", array("required" => true)); ?>
		</fieldset>

		<ul class="actions">
			<li class="signup"><input type="submit" value="Sign up" class="button primary" /></li>
		</ul>
	<?= $model->formEnd() ?>

	<p>
		Right after sign up, you'll receive an email with an activation link.
	</p>

</div>
