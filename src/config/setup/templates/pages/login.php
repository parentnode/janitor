<?php
	
$model = new Model();
$username = stringOr(getPost("username"));
	
?>
<div class="scene login i:login">
	<h1>Login</h1>

	<?= $model->formStart("?login=true", array("class" => "labelstyle:inject")) ?>

<?	if(message()->hasMessages(array("type" => "error"))): ?>
		<p class="error">
<?		$messages = message()->getMessages(array("type" => "error"));
		message()->resetMessages();
		foreach($messages as $message): ?>
			<?= $message ?><br>
<?		endforeach;?>
		</p>
<?	endif; ?>

		<fieldset>
			<?= $model->input("username", array("type" => "string", "label" => "Email or mobile", "required" => true, "value" => $username, "pattern" => "[\w\.\-\_]+@[\w-\.]+\.\w{2,4}|([\+0-9\-\.\s\(\)]){5,18}", "hint_message" => "Use your emailaddress or mobilenumber to log in.", "error_message" => "The entered value is neither an email or a mobilenumber.")); ?>
			<?= $model->input("password", array("type" => "password", "label" => "password", "required" => true, "hint_message" => "Type your password", "error_message" => "Your password must be 8-20 characters")); ?>
		</fieldset>
		<p class="forgot"><a href="/login/forgot_password">Forgot your password?</a></p>

		<ul class="actions">
			<li class="login"><input type="submit" value="Log ind" class="button primary" /></li>
		</ul>
	<?= $model->formEnd() ?>

	<p>Not registered yet? <a href="/login/signup">Sign up now</a>.</p>
</div>