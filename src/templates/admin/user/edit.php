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

//$usernames = $model->getUsernames(array("user_id" => $action[1]));
$mobile = "";
$mobile = $model->getUsernames(array("user_id" => $action[1], "type" => "mobile"));
$email = $model->getUsernames(array("user_id" => $action[1], "type" => "email"));

$addresses = $model->getAddresses(array("user_id" => $action[1]));
$newsletters = $model->getNewsletters(array("user_id" => $action[1]));

?>

<div class="scene defaultEdit userEdit">
	<h1>Edit user</h1>

	<ul class="actions">
		<li class="cancel"><a href="/admin/user/list/<?= $item["user_group_id"] ?>" class="button">All users</a></li>
		<li class="delete">
			<form action="/admin/user/delete/<?= $item["id"] ?>" class="i:formDefaultDelete" method="post" enctype="multipart/form-data">
				<input type="submit" value="Delete" class="button delete" />
			</form>
		</li>
	</ul>


	<ul class="views">
		<li class="profile selected"><a href="/admin/user/<?= $item["id"] ?>">Profile</a></li>
		<li class="content"><a href="/admin/user/content/<?= $item["id"] ?>">Content and orders</a></li>
	</ul>

	<div class="status">
		<ul class="actions">
			<li class="status <?= ($item["status"] == 1 ? "enabled" : "disabled") ?>">
				<form action="/admin/user/disable/<?= $item["id"] ?>" class="disable i:formDefaultStatus" method="post" enctype="multipart/form-data">
					<h3>Enabled</h3>
					<input type="submit" value="Disable" class="button status disable" />
				</form>
				<form action="/admin/user/enable/<?= $item["id"] ?>" class="enable i:formDefaultStatus" method="post" enctype="multipart/form-data">
					<h3>Disabled</h3>
					<input type="submit" value="Enable" class="button status enable" />
				</form>
			</li>
		</ul>
	</div>

	<div class="item i:defaultEdit">
		<form action="/admin/user/update/<?= $action[1] ?>" class="labelstyle:inject" method="post" enctype="multipart/form-data">
			<h3>Name, language and user group</h3>
			<fieldset>
				<?= $model->input("nickname", array("value" => $item["nickname"])) ?>
				<?= $model->input("firstname", array("value" => $item["firstname"])) ?>
				<?= $model->input("lastname", array("value" => $item["lastname"])) ?>
				<?= $model->input("language", array("type" => "select", "value" => $item["language"], "options" => $language_options)) ?>
				<?= $model->input("user_group_id", array("type" => "select", "value" => $item["user_group_id"], "options" => $user_groups_options)) ?>
			</fieldset>

			<ul class="actions">
				<li class="cancel"><a href="/admin/user/list/<?= $item["user_group_id"] ?>" class="button">Back</a></li>
				<li class="save"><input type="submit" value="Update" class="button primary" /></li>
			</ul>
		</form>
	</div>

	<h2>Email and Mobile number</h2>
	<p>Your email and mobilenumber are your unique usernames</p> 
	<div class="usernames i:usernames">
		<form action="/admin/user/update/<?= $action[1] ?>" class="labelstyle:inject" method="post" enctype="multipart/form-data">
			<fieldset>
				<?= $model->input("email", array("value" => stringOr($email["username"]))) ?>
				<? if(isset($email["username"]) && !$email["verified"]): ?>
				<!--p>Verify email</p-->
				<? endif; ?>
				<?= $model->input("mobile", array("value" => stringOr($mobile["username"]))) ?>
				<? if(isset($mobile["username"]) && !$mobile["verified"]): ?>
				<!--p>Verify mobile</p-->
				<? endif; ?>
			</fieldset>
			<ul class="actions">
				<li class="save"><input type="submit" value="Update usernames" class="button primary" /></li>
			</ul>
		</form>
	</div>

	<h2>Password</h2>
	<p>Type your new password to set or update your password</p>
	<div class="password i:password">

		<form action="/admin/user/setPassword/<?= $action[1] ?>" class="" method="post" enctype="multipart/form-data">
			<fieldset>
				<?= $model->input("password") ?>
			</fieldset>
			<ul class="actions">
				<li class="save"><input type="submit" value="Update password" class="button primary" /></li>
			</ul>
		</form>
	</div>

	<h2>Addresses</h2>
	<p>These addresses are associated with your account</p>
	<div class="addresses">

		<ul class="addresses">
<?			foreach($addresses as $address): ?>
			<li>
				<h3 class="address_label"><?= $address["address_label"] ?></h3>
				<div class="address_name"><?= $address["address_name"] ?></div>
				<?= $address["att"] ? ('<div class="att">Att: ' . $address["att"] . '</div>') : '' ?>
				<div class="address1"><?= $address["address1"] ?></div>
				<?= $address["address2"] ? ('<div class="address2">' . $address["address2"] . '</div>') : '' ?>
				<div class="postal_city">
					<span class="postal"><?= $address["postal"] ?></span>
					<span class="city"><?= $address["city"] ?></span>
				</div>
				<?= $address["state"] ? ('<div class="state">' . $address["state"] . '</div>') : '' ?>
				<div class="country"><?= $address["country"] ?></div>

				<ul class="actions">
					<li class="edit"><a href="/admin/user/edit_address/" class="button">Edit</a></li>
				</ul>
<?			endforeach; ?>
			</li>
		</ul>



		<ul class="actions">
			<li class="add"><a href="/admin/user/new_address/<?= $action[1] ?>" class="button primary">Add new address</a></li>
		</ul>
	</div>

	<h2>Newsletters</h2>
	<p>You are subscriped to these newsletters</p>
	<div class="newsletters">
		<?
		print_r($newsletters);
		?>
	</div>

</div>