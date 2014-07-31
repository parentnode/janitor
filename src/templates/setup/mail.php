<?php
$model = new Model();

$mail_host = isset($_SESSION["mail_host"]) ? $_SESSION["mail_host"] : "";
$mail_port = isset($_SESSION["mail_port"]) ? $_SESSION["mail_port"] : "";
$mail_username = isset($_SESSION["mail_username"]) ? $_SESSION["mail_username"] : "";
$mail_password = isset($_SESSION["mail_password"]) ? $_SESSION["mail_password"] : "";

?>
<div class="scene mail i:mail">
	
	<h1>Setup Mail</h1>

	<p>Specify mail information to enable automatic mailing (response, errors).</p>

	<?= $model->formStart("/setup/mail", array("class" => "labelstyle:inject")) ?>
	<fieldset>
		<?= $model->input("mail_host", array("type" => "string", "required" => true, "value" => $mail_host, "label" => "Mail host", "hint_message" => "Mail host like smtp.gmail.com")) ?>
		<?= $model->input("mail_port", array("type" => "string", "required" => true, "value" => $mail_port, "label" => "Mail port", "hint_message" => "Mail connection port like 365")) ?>
		<?= $model->input("mail_username", array("type" => "string", "required" => true, "value" => $mail_username, "label" => "Mail username", "hint_message" => "Username for your mail account.")) ?>
		<?= $model->input("mail_password", array("type" => "string", "required" => true, "value" => $mail_password, "label" => "Mail password", "hint_message" => "Password for your mail account.")) ?>
	</fieldset>

	<ul class="actions">
		<?= $model->submit("Continue", array("wrapper" => "li.save", "class" => "primary")) ?>
	</ul>

	<?= $model->formEnd() ?>
</div>