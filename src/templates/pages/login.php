<?php
global $action;
global $model;


$login_forward = getVar("login_forward");
// if($login_forward) {
// 	session()->value("login_forward", $login_forward);
// }


$username = stringOr(getPost("username"));
?>
<div class="scene login i:login">
	<h1>Login</h1>

<?	if(defined("SITE_SIGNUP") && SITE_SIGNUP): ?>
	<p>Not registered yet? <a href="<?= SITE_SIGNUP ?>">Create your account now</a>.</p>
<?	endif; ?>

	<?= $model->formStart("?login=true", array("class" => "labelstyle:inject")) ?>
		<?= $model->input("login_forward", ["type" => "hidden", "value" => $login_forward]); ?>
		<? if(message()->hasMessages()): ?>
		<div class="messages">
		<?
		$all_messages = message()->getMessages();
		message()->resetMessages();
		foreach($all_messages as $type => $messages):
			foreach($messages as $message): ?>
			<p class="<?= $type ?>"><?= $message ?></p>
			<? endforeach;?>
		<? endforeach;?>
		</div>
		<? endif; ?>

		<fieldset>
			<?= $model->input("username", array("required" => true, "value" => $username)); ?>
			<?= $model->input("password", array("required" => true)); ?>
		</fieldset>

		<ul class="actions">
			<?= $model->submit("Log in", array("class" => "primary", "wrapper" => "li.login")) ?>
			<li class="forgot">Did you <a href="/login/forgot">forget your password</a>?</li>
		</ul>
	<?= $model->formEnd() ?>

</div>