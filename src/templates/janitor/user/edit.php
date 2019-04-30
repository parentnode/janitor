<?php
global $action;
global $model;

$user_id = $action[1];

$item = $model->getUsers(["user_id" => $user_id]);

// do not attempt to get cancelled or non-existent users
if($item && $item["status"] >= 0) {

	// get user_groups for select
	$user_groups_options = $model->toOptions($model->getUserGroups(), "id", "user_group");

	// get languages for select
	$language_options = $model->toOptions($this->languages(), "id", "name");

	// get existing usernames
	$mobile = $model->getUsernames(array("user_id" => $user_id, "type" => "mobile"));
	$email = $model->getUsernames(array("user_id" => $user_id, "type" => "email"));


	// get password state
	$has_password = $model->hasPassword(["user_id" => $user_id]);

	// get api token
	$apitoken = $model->getToken($user_id);

	// get addresses
	$addresses = $model->getAddresses(array("user_id" => $user_id));


	$unpaid_orders = false;
	if(defined("SITE_SHOP") && SITE_SHOP) {
		include_once("classes/shop/supershop.class.php");
		$SC = new SuperShop();

		$unpaid_orders = $SC->getUnpaidOrders(["user_id" => $user_id]);
	}
	
}


?>
<div class="scene i:scene defaultEdit userEdit">
	<h1>Edit user</h1>

<? if($item && $item["status"] >= 0): ?>
	<h2><?= $item["nickname"] ?></h2>

	<ul class="actions i:defaultEditActions">
		<?= $HTML->link("All users", "/janitor/admin/user/list/".$item["user_group_id"], array("class" => "button", "wrapper" => "li.list")) ?>
<? 
	// do not allow to delete Anonymous user
	if($user_id != 1): ?>
		<?= $JML->oneButtonForm("Delete account", "/janitor/admin/user/delete/".$user_id, array(
			"wrapper" => "li.delete",
			"success-location" => "/janitor/admin/user/list/".$item["user_group_id"]
		)) ?>
<? 	endif; ?>
<?
	// do not allow to cancel user with unpaid orders
	if($unpaid_orders): ?>
		<?= $HTML->link("Unpaid orders", "/janitor/admin/user/orders/".$user_id, array("class" => "button", "wrapper" => "li.unpaid")) ?>
<? 
	// or Anonymous user
	elseif($user_id != 1): ?>
		<?= $JML->oneButtonForm("Cancel account", "/janitor/admin/user/cancel/".$user_id, array(
			"wrapper" => "li.cancel",
			"confirm-value" => "This will anonymise the account. Permanently! Irreversibly!",
			"success-location" => "/janitor/admin/user/list/".$item["user_group_id"]
		)) ?>
			
<? 	endif; ?>
	</ul>

<? 
// do not allow to change status of Anonymous user
	if($user_id != 1): ?>
	<div class="status i:defaultEditStatus item_id:<?= $user_id ?>"
		data-csrf-token="<?= session()->value("csrf") ?>"
		>
		<ul class="actions">
			<?= $JML->statusButton("Enable", "Disable", "/janitor/admin/user/status", $item) ?>
		</ul>
	</div>
<? 	endif; ?>


	<?= $JML->userTabs($user_id, "profile") ?>


	<div class="item i:defaultEdit">
		<h2>Name, language and user group</h2>
		<?= $model->formStart("/janitor/admin/user/update/".$user_id, array("class" => "labelstyle:inject")) ?>
			<fieldset>
				<?= $model->input("nickname", array("value" => $item["nickname"])) ?>
				<?= $model->input("firstname", array("value" => $item["firstname"])) ?>
				<?= $model->input("lastname", array("value" => $item["lastname"])) ?>
				<?= $model->input("language", array("type" => "select", "value" => $item["language"], "options" => $language_options)) ?>
<? 
	// do not allow to change user group for Anonymous user
	if($user_id != 1): ?>
				<?= $model->input("user_group_id", array("type" => "select", "value" => $item["user_group_id"], "options" => $user_groups_options)) ?>
<? 	endif; ?>
			</fieldset>

			<ul class="actions">
				<?= $model->link("Back", "/janitor/admin/user/list/".$item["user_group_id"], array("class" => "button key:esc", "wrapper" => "li.cancel")) ?>
				<?= $model->submit("Update", array("class" => "primary key:s", "wrapper" => "li.save")) ?>
			</ul>
		<?= $model->formEnd() ?>
	</div>


<? 
	// do not allow to edit usernames for Anonymous user
	if($user_id != 1): ?>
	<div class="usernames i:usernames i:collapseHeader">
		<h2>Email and Mobile number</h2>  
		<p>Your email and mobile number are your unique usernames and can be used for login.</p> 

		<div class="email">
			<?= $model->formStart("updateEmail/".$user_id, array("class" => "email labelstyle:inject")) ?>
				<fieldset>
					<?	$username_id = $email["id"];?>
					<?	$current_verification_status = $username_id ? $model->getVerificationStatus($username_id, $user_id) : 0;
					// print_r ($current_verification_status);
					?>
					
					
					<?= $model->input("username_id", array("type" => "hidden", "value" => $username_id)) ?>
					<?= $model->input("email", array("value" => stringOr($email["username"]))) ?>
					<?= $model->input("verification_status", array(
						"value" => $current_verification_status["verified"]
						)) ?>
				</fieldset>
				<ul class="actions">
					<?= $model->submit("Save", array("name" => "save", "wrapper" => "li.save", "class" => "disabled")) ?>
				</ul>
			<?= $model->formEnd() ?>
			<div class="send_verification_link">
				<ul class="actions send_verification_link">
					<? if($current_verification_status["total_reminders"] == 0): ?>
						<?= $JML->oneButtonForm("Send invite", "/janitor/admin/user/sendVerificationLink/".$username_id, array(
							"wrapper" => "li.send_verification_link.invite",
							"class" => "send_verification_link invite",
							"inputs" => [
								"template" => "verify_new_email"
							]
						)) ?>
					<? else: ?>			
						<?= $JML->oneButtonForm("Send reminder", "/janitor/admin/user/sendVerificationLink/".$username_id, array(
							"wrapper" => "li.send_verification_link.reminder",
							"class" => "send_verification_link reminder",
							"inputs" => [
								"template" => "signup_reminder"
							]
						)) ?>
					<? endif;?>
				</ul>
				<? if($current_verification_status["reminded_at"]):?>
				<p class="reminded_at">Reminder sent: <span class="date_time"><?=$current_verification_status["reminded_at"]?></span></p>
				<? else:?>
				<p class="reminded_at">Reminder sent: <span class="date_time never">-</span></p>
				<? endif;?>
			</div>
		</div>

		<div class="mobile">
			<?= $model->formStart("updateMobile/".$user_id, array("class" => "mobile labelstyle:inject")) ?>
				<fieldset>
					<?= $model->input("mobile", array("value" => stringOr($mobile))) ?>
				</fieldset>
				<ul class="actions">
					<?= $model->submit("Save", array("name" => "save", "wrapper" => "li.save")) ?>
				</ul>
			<?= $model->formEnd() ?>
		</div>



	</div>
<? 	endif; ?>


<? 
	// do not allow to change password for Anonymous user
	if($user_id != 1): ?>
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
<? 	endif; ?>


<? 
	// do not allow to create api token for Anonymous user
	if($user_id != 1): ?>
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
<? 	endif; ?>


<? 
	// do not allow to edit addresses for Anonymous user
	if($user_id != 1): ?>
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
					<?= $model->link("Edit", "/janitor/admin/user/address/edit/".$user_id."/".$address["id"], array("class" => "button", "wrapper" => "li.edit")) ?>
				</ul>
			</li>
<?			endforeach; ?>
		</ul>
<?		else: ?>
		<p>You don't have any addresses associated with your account</p>
<?		endif; ?>

		<ul class="actions">
			<?= $model->link("Add new address", "/janitor/admin/user/address/new/".$user_id, array("class" => "button primary", "wrapper" => "li.add")) ?>
		</ul>
	</div>
<? 	endif; ?>



<? else: ?>

	<ul class="actions i:defaultEditActions">
		<?= $HTML->link("All users", "/janitor/admin/user/list/".$item["user_group_id"], array("class" => "button", "wrapper" => "li.list")) ?>
	</ul>

<? 	if($item && $item["status"] == -1): ?>


	<p>The user account has been cancelled.</p>


<? 	else: ?>

	<p>The user does not exist.</p>

<? 	endif; ?>

<? endif; ?>


</div>