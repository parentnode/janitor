<?php
// TODO: This template should be moved to system
include_once("classes/system/system.class.php");


global $action;
global $model;
$IC = new Items();
$SC = new System();

$return_to_list = session()->value("return_to_list");
$back_url = "/janitor/admin/message/maillists/list" . ($return_to_list ? "/".$return_to_list : "");

?>
<div class="scene i:scene defaultNew">
	<h1>New mailing list</h1>

	<ul class="actions">
		<?= $HTML->link("Maillists", $back_url, array("class" => "button", "wrapper" => "li.lists")); ?>
	</ul>

	<?= $SC->formStart("/janitor/admin/system/addMaillist", array("class" => "i:defaultNew labelstyle:inject")) ?>
		<?= $SC->input("return_to", ["type" => "hidden", "value" => "/janitor/admin/message/maillists/list/"])?>
		<fieldset>
			<?= $SC->input("maillist") ?>
		</fieldset>

		<ul class="actions">
			<?= $SC->submit("Add list", array("class" => "primary key:s", "wrapper" => "li.save")) ?>
		</ul>
	<?= $SC->formEnd() ?>

</div>