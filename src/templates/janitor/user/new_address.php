<?php
global $action;
global $model;

$user_id = $action[1];
$item = $model->getUsers(array("user_id" => $user_id));

?>
<div class="scene i:scene defaultNew userAddress">
	<h1>New address</h1>
	<h2><?= $item["nickname"] ?></h2>

	<ul class="actions">
		<?= $HTML->link("Back to user", "/janitor/admin/user/edit/".$user_id, array("class" => "button", "wrapper" => "li.cancel")) ?>
	</ul>

	<?= $model->formStart("/janitor/admin/user/addAddress/".$user_id, array("class" => "i:defaultNew labelstyle:inject")) ?>
		<fieldset>
			<?= $model->input("address_label") ?>
			<?= $model->input("address_name") ?>
			<?= $model->input("att") ?>
			<?= $model->input("address1") ?>
			<?= $model->input("address2") ?>
			<?= $model->input("city") ?>
			<?= $model->input("postal") ?>
			<?= $model->input("state") ?>
			<?= $model->input("country", array(
				"type" => "select",
				"options" => $model->toOptions($this->countries(), "id", "name")
			)) ?>
		</fieldset>

		<ul class="actions">
			<?= $model->link("Back", "/janitor/admin/user/edit/".$user_id, array("class" => "button key:esc", "wrapper" => "li.cancel")) ?>
			<?= $model->submit("Save address", array("class" => "primary key:s", "wrapper" => "li.save")) ?>
		</ul>
	<?= $model->formEnd() ?>

</div>