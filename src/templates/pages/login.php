<?php
global $action;
global $model;


$login_forward = getVar("login_forward");
if(!$login_forward && !session()->value("login_forward")) {
	$login_forward = "/janitor";
// 	session()->value("login_forward", $login_forward);
}


$username = stringOr(getPost("username"));
?>
<div class="scene login i:login">
	<h1>Login</h1>

<?	if(defined("SITE_SIGNUP") && SITE_SIGNUP): ?>
	<p>Not registered yet? <a href="<?= SITE_SIGNUP_URL ?>">Create your account now</a>.</p>
<?	endif; ?>

	<?= $model->formStart("?login=true", array("class" => "labelstyle:inject")) ?>
		<?= $model->input("login_forward", ["type" => "hidden", "value" => $login_forward]); ?>


		<?= $HTML->renderSnippet("snippets/server-messages.php")?>


		<fieldset>
			<?= $model->input("username", array("required" => true, "value" => $username)); ?>
			<?= $model->input("password", array("required" => true)); ?>
		</fieldset>

		<ul class="actions">
			<?= $model->submit("Log in", array("class" => "primary", "wrapper" => "li.login")) ?>
			<li class="forgot">Did you <a href="<?= SITE_LOGIN_URL ?>/forgot">forget your password</a>?</li>
		</ul>
	<?= $model->formEnd() ?>

</div>