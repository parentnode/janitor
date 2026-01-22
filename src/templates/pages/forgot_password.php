<?php
	
$model = new Model();

$this->pageTitle("Forgot password?");
?>
<div class="scene login i:login">
	<h1>Forgot your password?</h1>
	<p>Type your email, and we'll send you a mail with information about how to reset your password.</p>

	<?= $model->formStart("requestReset", array("class" => "labelstyle:inject")) ?>


		<?= $HTML->renderSnippet("snippets/messaged.php", [
			"type" => "error",
		]) ?>


		<fieldset>
			<?= $model->input("username", array("type" => "string", "label" => "Email", "required" => true, "pattern" => "[\w\.\-_\+]+@[\w\-\.]+\.\w{2,10}", "hint_message" => "Type your email.", "error_message" => "Invalid email.")); ?>
		</fieldset>

		<ul class="actions">
			<?= $model->submit("Request password reset", array("class" => "primary", "wrapper" => "li.reset")) ?>
		</ul>
	<?= $model->formEnd() ?>

</div>
