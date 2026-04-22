<?php
global $action;
//global $model;

$model = new User();
$username = stringOr(getPost("username"));

?>
<div class="scene login i:login">
	<h1>Re-authenticate</h1>

	<?= $model->formStart("?login=true", array("class" => "labelstyle:inject")) ?>
		<?= $model->input("login_forward", ["type" => "hidden", "value" => $this->url]); ?>

		<p class="reautenticate">
			You are currently authenticated by your access token. This is insufficient to access the selected area. You must re-autenticate to continue.
		</p>


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