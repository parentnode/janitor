<?php
$model = new Model();

$project_path = isset($_SESSION["project_path"]) ? $_SESSION["project_path"] : PROJECT_PATH;

$site_uid = isset($_SESSION["site_uid"]) ? $_SESSION["site_uid"] : "";
$site_name = isset($_SESSION["site_name"]) ? $_SESSION["site_name"] : "";
//$site_url = isset($_SESSION["site_url"]) ? $_SESSION["site_url"] : "";
$site_email = isset($_SESSION["site_email"]) ? $_SESSION["site_email"] : "";

?>
<div class="scene config i:config">
	
	<h1>Janitor configuration</h1>
	<?= $model->formStart("/setup/config", array("class" => "labelstyle:inject")) ?>


	<h2>Project settings</h2>

	<p>Verify the absolute path to your project.</p>
	<fieldset>
		<?= $model->input("project_path", array("type" => "string", "required" => "true", "value" => $project_path, "label" => "Project path", "hint_message" => "Absolute path to your project folder")) ?>
	</fieldset>


	<p>Specify unique ID, name and url for your project.</p>
	<fieldset>
		<?= $model->input("site_uid", array("type" => "string", "required" => true, "value" => $site_uid, "label" => "Unique ID", "hint_message" => "3-5 character ID used to identify your current project.")) ?>
		<?= $model->input("site_name", array("type" => "string", "required" => true, "value" => $site_name, "label" => "Site name", "hint_message" => "Userfriendly name of your project.")) ?>
		<? //= $model->input("site_url", array("type" => "string", "required" => true, "value" => $site_url, "label" => "Site url", "hint_message" => "Domain your site will be accessed through.")) ?>
		<?= $model->input("site_email", array("type" => "string", "required" => true, "value" => $site_email, "label" => "Site email", "hint_message" => "Email to use to communicate to your users.")) ?>
	</fieldset>

	<ul class="actions">
		<?= $model->submit("Continue", array("wrapper" => "li.save", "class" => "primary")) ?>
	</ul>

	<?= $model->formEnd() ?>
</div>