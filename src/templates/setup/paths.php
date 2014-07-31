<?php
$model = new Model();

$framework_path = isset($_SESSION["FRAMEWORK_PATH"]) ? $_SESSION["FRAMEWORK_PATH"] : FRAMEWORK_PATH;
$local_path = isset($_SESSION["LOCAL_PATH"]) ? $_SESSION["LOCAL_PATH"] : "";

?>
<div class="scene paths i:paths">
	
	<h1>Verify project paths</h1>

	<p>Please verify the project paths.</p>

	<?= $model->formStart("/setup/paths", array("class" => "labelstyle:inject")) ?>
	<fieldset>
		<?= $model->input("framework_path", array("type" => "string", "required" => "true", "value" => $framework_path, "label" => "Janitor framework path", "hint_message" => "Absolute path to your Janitor source folder")) ?>
		<?= $model->input("local_path", array("type" => "string", "required" => "true", "value" => $local_path, "label" => "Janitor local path", "hint_message" => "Absolute path to your project source folder")) ?>
	</fieldset>
	
	<ul class="actions">
		<?= $model->submit("Continue", array("wrapper" => "li.save", "class" => "primary")) ?>
	</ul>

	<?= $model->formEnd() ?>
</div>