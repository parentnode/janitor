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

// api token
$apitoken = $model->getToken($user_id);

// get addresses
$addresses = $model->getAddresses(array("user_id" => $user_id));

// get newsletters
$all_newsletters = $model->getNewsletters();
$user_newsletters = $model->getNewsletters(array("user_id" => $user_id));

?>
<div class="scene i:scene defaultEdit userEdit">
	<h1>Edit user</h1>
	<h2><?= $item["nickname"] ?></h2>

	<ul class="actions i:defaultEditActions item_id:<?= $user_id ?>"
		data-csrf-token="<?= session()->value("csrf") ?>"
		>
		<?= $HTML->link("All users", "/janitor/admin/user/list/".$item["user_group_id"], array("class" => "button", "wrapper" => "li.cancel")) ?>
		<?= $HTML->link("User groups", "/janitor/admin/user/group/list", array("class" => "button", "wrapper" => "li.usergroup")) ?>
		<?= $JML->deleteButton("Delete", "/janitor/admin/user/delete/".$user_id) ?>
	</ul>

	<div class="status i:defaultEditStatus item_id:<?= $user_id ?>"
		data-csrf-token="<?= session()->value("csrf") ?>"
		>
		<ul class="actions">
			<?= $JML->statusButton("Enable", "Disable", "/janitor/admin/user/status", $item) ?>
		</ul>
	</div>

	<ul class="tabs">
		<?= $HTML->link("Profile", "/janitor/admin/user/edit/".$user_id, array("wrapper" => "li.profile.selected")) ?>
<?		if(defined("SITE_SHOP") && SITE_SHOP): ?>
		<?= $HTML->link("Content and orders", "/janitor/admin/user/content/".$user_id, array("wrapper" => "li.content")) ?>
<?		else: ?>
		<?= $HTML->link("Content", "/janitor/admin/user/content/".$user_id, array("wrapper" => "li.content")) ?>
<?		endif; ?>
	</ul>

	<div class="item i:defaultEdit">
		<h2>Name, language and user group</h2>
		<?= $model->formStart("/janitor/admin/user/update/".$user_id, array("class" => "labelstyle:inject")) ?>
			<fieldset>
				<?= $model->input("nickname", array("value" => $item["nickname"])) ?>
				<?= $model->input("firstname", array("value" => $item["firstname"])) ?>
				<?= $model->input("lastname", array("value" => $item["lastname"])) ?>
				<?= $model->input("language", array("type" => "select", "value" => $item["language"], "options" => $language_options)) ?>
				<?= $model->input("user_group_id", array("type" => "select", "value" => $item["user_group_id"], "options" => $user_groups_options)) ?>
			</fieldset>

			<ul class="actions">
				<?= $model->link("Back", "/janitor/admin/user/list/".$item["user_group_id"], array("class" => "button key:esc", "wrapper" => "li.cancel")) ?>
				<?= $model->submit("Update", array("class" => "primary key:s", "wrapper" => "li.save")) ?>
			</ul>
		<?= $model->formEnd() ?>
	</div>


	<div class="usernames i:usernames i:collapseHeader">
		<h2>Email and Mobile number</h2>
		<p>Your email and mobile number are your unique usernames and can be used for login.</p> 

		<?= $model->formStart("updateEmail/".$user_id, array("class" => "email labelstyle:inject")) ?>
			<fieldset>
				<?= $model->input("email", array("value" => stringOr($email))) ?>
			</fieldset>
			<ul class="actions">
				<?= $model->submit("Save", array("name" => "save", "wrapper" => "li.save")) ?>
			</ul>
		<?= $model->formEnd() ?>

		<?= $model->formStart("updateMobile/".$user_id, array("class" => "mobile labelstyle:inject")) ?>
			<fieldset>
				<?= $model->input("mobile", array("value" => stringOr($mobile))) ?>
			</fieldset>
			<ul class="actions">
				<?= $model->submit("Save", array("name" => "save", "wrapper" => "li.save")) ?>
			</ul>
		<?= $model->formEnd() ?>

	</div>


	<div class="password i:password i:collapseHeader">
		<h2>Password</h2>
		<div class="password_state <?= $has_password ? "set" : "" ?> ">
			<p class="password_set">Your password is encrypted and cannot be shown here. <a>Change password</a>.</p>
			<p class="password_missing">Your password has not been created yet. <a>Create password</a>.</p>
		</div>
		<div class="new_password">
			<p>Type your new password to set or update your password</p>

			<?= $model->formStart("/janitor/admin/user/setPassword/".$user_id) ?>
				<fieldset>
					<?= $model->input("password") ?>
				</fieldset>
				<ul class="actions">
					<?= $model->submit("Update password", array("class" => "primary", "wrapper" => "li.save")) ?>
					<?= $model->button("Cancel", array("wrapper" => "li.cancel")) ?>
				</ul>
			<?= $model->formEnd() ?>
		</div>
	</div>

	<div class="apitoken i:apitoken i:collapseHeader">
		<h2>API Token</h2>
		<p class="token"><?= stringOr($apitoken, "N/A") ?></p>

		<?= $model->formStart("renewToken/".$user_id, array("class" => "renew")) ?>
			<ul class="actions">
				<?= $model->submit(($apitoken ? "Renew API token" : "Create API token"), array("class" => "primary", "name" => "renew", "wrapper" => "li.renew")) ?>
			</ul>
		<?= $model->formEnd() ?>
		<?= $model->formStart("disableToken/".$user_id, array("class" => "disable")) ?>
			<ul class="actions">
				<?= $model->submit("Disable token", array("class" => "secondary".($apitoken ? "" : " disabled"), "name" => "disable", "wrapper" => "li.renew")) ?>
			</ul>
		<?= $model->formEnd() ?>
	</div>

	<div class="addresses i:collapseHeader">
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
					<?= $model->link("Edit", "/janitor/admin/user/edit_address/".$user_id."/".$address["id"], array("class" => "button", "wrapper" => "li.edit")) ?>
				</ul>
			</li>
<?			endforeach; ?>
		</ul>
<?		else: ?>
		<p>You don't have any addresses associated with your account</p>
<?		endif; ?>

		<ul class="actions">
			<?= $model->link("Add new address", "/janitor/admin/user/new_address/".$user_id, array("class" => "button primary", "wrapper" => "li.add")) ?>
		</ul>
	</div>

	<div class="newsletters i:newsletters i:collapseHeader">
		<h2>Newsletters</h2>
<?		if($all_newsletters): ?>
		<ul class="newsletters">
<?			foreach($all_newsletters as $newsletter): ?>
			<li class="<?= arrayKeyValue($user_newsletters, "newsletter", $newsletter["newsletter"]) !== false ? "subscribed" : "" ?>">
				<ul class="actions">
					<?= $JML->deleteButton("Unsubscribe", "/janitor/admin/user/deleteNewsletter/".$user_id."/".urlencode($newsletter["newsletter"])) ?>
					<li class="subscribe">
					<?= $model->formStart("/janitor/admin/user/addNewsletter/".$user_id) ?>
						<?= $model->input("newsletter", array("type" => "hidden", "value" => $newsletter["newsletter"]))?>
						<?= $model->submit("Subscribe", array("class" => "primary")) ?>
					<?= $model->formEnd() ?>
					</li>
				</ul>
				<h3><?= $newsletter["newsletter"] ?></h3>
			</li>
<?			endforeach; ?>
		</ul>
<?		else: ?>
		<p>You don't have any newsletter subscriptions for your account</p>
<?		endif; ?>

		<ul class="actions">
			<?= $model->link("Add newsletter subscription", "/janitor/admin/user/add_newsletter/".$user_id, array("class" => "button primary", "wrapper" => "li.newsletter")) ?>
		</ul>
	</div>

</div>