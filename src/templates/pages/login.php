<?php
global $action;
global $model;

$username = stringOr(getPost("username"));
?>
<div class="scene login i:login">
	<h1>Login</h1>

<?	if(defined("SITE_SIGNUP") && SITE_SIGNUP): ?>
	<p>Not registered yet? <a href="/signup">Create your account now</a>.</p>
<?	endif; ?>

	<?= $model->formStart("?login=true", array("class" => "labelstyle:inject")) ?>

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
			<?= $model->input("username", array("required" => true, "value" => $username)); ?>
			<?= $model->input("password", array("required" => true)); ?>
		</fieldset>

		<ul class="actions">
			<?= $model->submit("Log in", array("class" => "primary", "wrapper" => "li.login")) ?>
		</ul>
	<?= $model->formEnd() ?>

	<p>Did you <a href="/login/forgot">forget your password</a>?</p>

</div>