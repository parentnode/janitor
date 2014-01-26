<?php

$action = $this->actions();

$model = new User();
// check if custom function exists on cart class
$item = $model->getUsers(array("user_id" => $action[1]));

// TODO: Create global function for this
$user_groups = $model->getUserGroups();
$user_groups_options = array();
foreach($user_groups as $user_group) {
	$option = array();
	$option[0] = $user_group["id"];
	$option[1] = $user_group["user_group"];
	$user_groups_options[] = $option;
}

$query = new Query();
$query->sql("SELECT * FROM ".UT_LANGUAGES);
$languages = $query->results();
$language_options = array();
foreach($languages as $language) {
	$option = array();
	$option[0] = $language["id"];
	$option[1] = $language["name"];
	$language_options[] = $option;
}

$usernames = $model->getUsernames(array("user_id" => $action[1]));
$addresses = $model->getAddresses(array("user_id" => $action[1]));
$newsletters = $model->getNewsletters(array("user_id" => $action[1]));
	
?>

<div class="scene defaultEdit userEdit">
	<h1>Edit user</h1>

	<ul class="actions">
		<li class="cancel"><a href="/admin/user/list" class="button">Back</a></li>
	</ul>

	<div class="item i:defaultEdit">
		<form action="/admin/user/update/<?= $action[1] ?>" class="labelstyle:inject" method="post" enctype="multipart/form-data">
			<fieldset>
				<?= $model->input("nickname", array("value" => $item["nickname"])) ?>
				<?= $model->input("firstname", array("value" => $item["firstname"])) ?>
				<?= $model->input("lastname", array("value" => $item["lastname"])) ?>
				<?= $model->input("language", array("type" => "select", "value" => $item["language"], "options" => $language_options)) ?>
				<?= $model->input("user_group_id", array("type" => "select", "value" => $item["user_group_id"], "options" => $user_groups_options)) ?>
			</fieldset>

			<ul class="actions">
				<li class="cancel"><a href="/admin/user/list" class="button">Back</a></li>
				<li class="save"><input type="submit" value="Update" class="button primary" /></li>
			</ul>
		</form>
	</div>

	<h2>Usernames (Email/Mobile number)</h2>
	<div class="usernames">
		<?
		print_r($usernames);
		?>
	</div>

	<h2>Password</h2>
	<div class="password i:defaultEdit">

		<form action="/admin/user/setPassword/<?= $action[1] ?>" class="" method="post" enctype="multipart/form-data">
			<fieldset>
				<?= $model->input("password") ?>
			</fieldset>
		</form>

		<ul class="actions">
			<li class="save"><input type="submit" value="Update password" class="button primary" /></li>
		</ul>
	</div>

	<h2>Newsletters</h2>
	<div class="newsletters">
		<?
		print_r($newsletters);
		?>
	</div>

	<h2>Addresses</h2>
	<div class="addresses">
		<?
		print_r($addresses);
		?>
	</div>

</div>