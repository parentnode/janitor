<?php
$access_item = false;
if(isset($read_access) && $read_access) {
	return;
}

include_once($_SERVER["FRAMEWORK_PATH"]."/config/init.php");

$action = $this->actions();

$UC = new User();
// check if custom function exists on cart class
$item = $UC->getUsers(array("user_id" => $action[1]));
$model = $UC;

?>

<div class="scene defaultEdit">
	<h1>Edit user</h1>

	<ul class="actions">
		<li class="cancel"><a href="/admin/users/list" class="button">Back</a></li>
	</ul>

	<div class="item">
		<form action="/admin/users/update/<?= $action[1] ?>" class="i:formDefaultEdit labelstyle:inject" method="post" enctype="multipart/form-data">
			<fieldset>
				<?= $model->input("nickname", array("value" => $item["nickname"])) ?>
				<?= $model->input("firstname", array("value" => $item["firstname"])) ?>
				<?= $model->input("lastname", array("value" => $item["lastname"])) ?>
				<?= $model->input("status", array("value" => $item["status"])) ?>
				<?= $model->input("language", array("value" => $item["language"])) ?>
				<?= $model->input("user_group_id", array("value" => $item["user_group_id"])) ?>
			</fieldset>

			<ul class="actions">
				<li class="cancel"><a href="/admin/users/list" class="button">Back</a></li>
				<li class="save"><input type="submit" value="Update" class="button primary" /></li>
			</ul>
		</form>
	</div>

</div>