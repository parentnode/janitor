<?php
global $action;
global $model;

$user_id = $action[1];

$item = $model->getUsers(array("user_id" => $user_id));

// get user_groups for select
$user_groups_options = $model->toOptions($model->getUserGroups(), "id", "user_group");

// get languages for select
$language_options = $model->toOptions($this->languages(), "id", "name");

// get usernames
$mobile = $model->getUsernames(array("user_id" => $user_id, "type" => "mobile"));
$email = $model->getUsernames(array("user_id" => $user_id, "type" => "email"));

// password state
$has_password = $model->hasPassword($user_id);


// get addresses
$addresses = $model->getAddresses(array("user_id" => $user_id));

// get newsletters
$newsletters = $model->getNewsletters(array("user_id" => $user_id));

?>
<div class="scene defaultEdit userEdit">
	<h1>Edit user</h1>

	<ul class="actions i:defaultEditActions item_id:<?= $user_id ?>"
		data-csrf-token="<?= session()->value("csrf") ?>"
		>
		<?= $HTML->link("All users", "/admin/user/list/".$item["user_group_id"], array("class" => "button", "wrapper" => "li.cancel")) ?>
		<?= $HTML->deleteButton("Delete", "/admin/user/delete/".$user_id) ?>
	</ul>

	<div class="status i:defaultEditStatus item_id:<?= $user_id ?>"
		data-csrf-token="<?= session()->value("csrf") ?>"
		>
		<ul class="actions">
			<?= $HTML->statusButton("Enable", "Disable", "/admin/user/status", $item) ?>
		</ul>
	</div>

	<ul class="views">
		<?= $HTML->link("Profile", "/admin/user/edit/".$user_id, array("wrapper" => "li.profile.selected")) ?>
<?		if(defined("SITE_SHOP") && SITE_SHOP): ?>
		<?= $HTML->link("Content and orders", "/admin/user/content/".$user_id, array("wrapper" => "li.content")) ?>
<?		else: ?>
		<?= $HTML->link("Content", "/admin/user/content/".$user_id, array("wrapper" => "li.content")) ?>
<?		endif; ?>
	</ul>

	<div class="item i:defaultEdit">
		<h2>Name, language and user group</h2>
		<?= $model->formStart("/admin/user/update/".$user_id, array("class" => "labelstyle:inject")) ?>
			<fieldset>
				<?= $model->input("nickname", array("value" => $item["nickname"])) ?>
				<?= $model->input("firstname", array("value" => $item["firstname"])) ?>
				<?= $model->input("lastname", array("value" => $item["lastname"])) ?>
				<?= $model->input("language", array("type" => "select", "value" => $item["language"], "options" => $language_options)) ?>
				<?= $model->input("user_group_id", array("type" => "select", "value" => $item["user_group_id"], "options" => $user_groups_options)) ?>
			</fieldset>

			<ul class="actions">
				<?= $model->link("Back", "/admin/user/list/".$item["user_group_id"], array("class" => "button key:esc", "wrapper" => "li.cancel")) ?>
				<?= $model->submit("Update", array("class" => "primary key:s", "wrapper" => "li.save")) ?>
			</ul>
		<?= $model->formEnd() ?>
	</div>

	<div class="usernames i:usernames">
		<h2>Email and Mobile number</h2>
		<p>Your email and mobilenumber are your unique usernames.</p> 

		<?= $model->formStart("/admin/user/updateUsernames/".$user_id, array("class" => "labelstyle:inject")) ?>
			<fieldset>
				<?= $model->input("email", array("value" => stringOr($email))) ?>
				<?= $model->input("mobile", array("value" => stringOr($mobile))) ?>
			</fieldset>
			<ul class="actions">
				<?= $model->submit("Update usernames", array("class" => "primary", "wrapper" => "li.save")) ?>
			</ul>
		<?= $model->formEnd() ?>
	</div>

	<div class="password i:password">
		<h2>Password</h2>
		<div class="password_state <?= $has_password ? "set" : "" ?> ">
			<p class="password_set">Your password is encrypted and cannot be shown here. <a>Change password</a></p>
			<p class="password_missing">Your password has not been created yet. <a>Create password</a></p>
		</div>
		<div class="new_password">
			<p>Type your new password to set or update your password</p>

			<?= $model->formStart("/admin/user/setPassword/".$user_id) ?>
				<fieldset>
					<?= $model->input("password") ?>
				</fieldset>
				<ul class="actions">
					<?= $model->submit("Update password", array("class" => "primary", "wrapper" => "li.save")) ?>
				</ul>
			<?= $model->formEnd() ?>
		</div>
	</div>

	<div class="addresses">
		<h2>Addresses</h2>
<?		if($addresses): ?>
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
				<div class="country"><?= $address["country_name"] ?></div>

				<ul class="actions">
					<?= $model->link("Edit", "/admin/user/edit_address/".$user_id."/".$address["id"], array("class" => "button", "wrapper" => "li.edit")) ?>
				</ul>
			</li>
<?			endforeach; ?>
		</ul>
<?		else: ?>
		<p>You don't have any addresses associated with your account</p>
<?		endif; ?>

		<ul class="actions">
			<?= $model->link("Add new address", "/admin/user/new_address/".$user_id, array("class" => "button primary", "wrapper" => "li.add")) ?>
		</ul>
	</div>

	<div class="newsletters">
		<h2>Newsletters</h2>
<?		if($newsletters): ?>
		<p>You are subscribed to these newsletters</p>

		<ul class="newsletters i:userNewsletters">
<?			foreach($newsletters as $newsletter): ?>
			<li><?= $newsletter["newsletter"] ?></li>
<?			endforeach; ?>
		</ul>
<?		else: ?>
		<p>You don't have any newsletters subscription for your account</p>
<?		endif; ?>
	</div>

</div>