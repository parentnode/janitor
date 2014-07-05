<?php
global $action;
global $model;

$item = $model->getUsers(array("user_id" => $action[1]));
$user_groups_options = $model->toOptions($model->getUserGroups(), "id", "user_group");

// TODO: Create global function for languages (don't know if it is supposed to be in Page, Shop or User)
$query = new Query();
$query->sql("SELECT * FROM ".UT_LANGUAGES);
$languages = $query->results();
$language_options = $model->toOptions($languages, "id", "name");

// get usernames
$mobile = $model->getUsernames(array("user_id" => $item["id"], "type" => "mobile"));
$email = $model->getUsernames(array("user_id" => $item["id"], "type" => "email"));

// password state
$has_password = $model->hasPassword($item["id"]);


// get addresses
$addresses = $model->getAddresses(array("user_id" => $item["id"]));

// get newsletters
$newsletters = $model->getNewsletters(array("user_id" => $item["id"]));

?>
<div class="scene defaultEdit userEdit">
	<h1>Edit user</h1>

	<ul class="actions i:defaultEditActions item_id:<?= $item["id"] ?>">
		<?= $HTML->link("All users", "/admin/user/list/".$item["user_group_id"], array("class" => "button", "wrapper" => "li.cancel")) ?>
		<?= $HTML->deleteButton("Delete", "/admin/user/delete/".$item["id"]) ?>
	</ul>

	<div class="status i:defaultEditStatus item_id:<?= $item["id"] ?>">
		<ul class="actions">
			<?= $HTML->statusButton("Enable", "Disable", "/admin/user/status", $item) ?>
		</ul>
	</div>

	<ul class="views">
		<?= $HTML->link("Profile", "/admin/user/edit/".$item["id"], array("wrapper" => "li.profile.selected")) ?>
		<?= $HTML->link("Content and orders", "/admin/user/content/".$item["id"], array("wrapper" => "li.content")) ?>
	</ul>

	<div class="item i:defaultEdit">
		<?= $model->formStart("/admin/user/update/".$item["id"], array("class" => "labelstyle:inject")) ?>
			<h3>Name, language and user group</h3>
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

	<h2>Email and Mobile number</h2>
	<div class="usernames i:usernames">
		<p>Your email and mobilenumber are your unique usernames.</p> 

		<?= $model->formStart("/admin/user/updateUsernames/".$item["id"], array("class" => "labelstyle:inject")) ?>
			<fieldset>
				<?= $model->input("email", array("value" => stringOr($email))) ?>
				<?= $model->input("mobile", array("value" => stringOr($mobile))) ?>
			</fieldset>
			<ul class="actions">
				<?= $model->submit("Update usernames", array("class" => "primary", "wrapper" => "li.save")) ?>
			</ul>
		<?= $model->formEnd() ?>
	</div>

	<h2>Password</h2>
	<div class="password i:password">
		<div class="password_state <?= $has_password ? "set" : "" ?> ">
			<p class="password_set">Your password is encrypted and cannot be shown here. <a>Change password</a></p>
			<p class="password_missing">Your password has not been created yet. <a>Create password</a></p>
		</div>
		<div class="new_password">
			<p>Type your new password to set or update your password</p>

			<?= $model->formStart("/admin/user/setPassword/".$item["id"]) ?>
				<fieldset>
					<?= $model->input("password") ?>
				</fieldset>
				<ul class="actions">
					<?= $model->submit("Update password", array("class" => "primary", "wrapper" => "li.save")) ?>
				</ul>
			<?= $model->formEnd() ?>
		</div>
	</div>

	<h2>Addresses</h2>
	<div class="addresses">
<? if($addresses): ?>
		<p>These addresses are associated with your account</p>

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
					<?= $model->link("Edit", "/admin/user/edit_address/".$address["id"], array("class" => "button", "wrapper" => "li.edit")) ?>
				</ul>
			</li>
<?			endforeach; ?>
		</ul>
<? else: ?>
		<p>You don't have any addresses associated with your account</p>
<? endif; ?>

		<ul class="actions">
			<?= $model->link("Add new address", "/admin/user/new_address/".$item["id"], array("class" => "button primary", "wrapper" => "li.add")) ?>
		</ul>
	</div>

	<h2>Newsletters</h2>
	<div class="newsletters">
<? if($newsletters): ?>
		<p>You are subscribed to these newsletters</p>

		<ul class="newsletters i:userNewsletters">
<?			foreach($newsletters as $newsletter): ?>
			<li><?= $newsletter["newsletter"] ?></li>
<?			endforeach; ?>
		</ul>
<? else: ?>
	<p>You don't have any newsletters subscription for your account</p>
<? endif; ?>
	</div>

</div>