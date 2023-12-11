<?php
global $action;
global $model;

// get current user
$item = $model->getUser();

// get languages for select
$language_options = $model->toOptions($this->languages(), "id", "name");

// api token
$apitoken = $model->getToken();

// payment methods
$user_payment_methods = $model->getPaymentMethods(["extend" => true]);
// debug(["user_payment_methods", $user_payment_methods]);
// get addresses
$addresses = $item["addresses"];

// check for unpaid orders
$unpaid_orders = false;
if(defined("SITE_SHOP") && SITE_SHOP) {
	include_once("classes/shop/shop.core.class.php");
	$SC = new Shop();
	$unpaid_orders = $SC->getUnpaidOrders();

}

?>
<div class="scene i:scene defaultEdit userEdit profileEdit">
	<h1>User profile</h1>
	<h2><?= $item["nickname"] ?></h2>

	<?= $JML->profileTabs("profile") ?>


	<div class="item i:editProfile">
		<h2>Name and default language</h2>
		<?= $model->formStart("update", array("class" => "labelstyle:inject")) ?>
			<fieldset>
				<?= $model->input("nickname", array("value" => $item["nickname"])) ?>
				<?= $model->input("firstname", array("value" => $item["firstname"])) ?>
				<?= $model->input("lastname", array("value" => $item["lastname"])) ?>
				<?= $model->input("language", array("type" => "select", "value" => $item["language"], "options" => $language_options)) ?>
			</fieldset>

			<ul class="actions">
				<?= $model->submit("Update", array("class" => "primary key:s", "wrapper" => "li.save")) ?>
			</ul>
		<?= $model->formEnd() ?>
	</div>

	<div class="usernames i:usernamesProfile i:collapseHeader">
		<h2>Email and Mobile number</h2>
		<p>Your email and mobile number are your unique usernames and can be used for login.</p> 

		<?= $model->formStart("updateEmail", array("class" => "email labelstyle:inject")) ?>
			<fieldset>
				<?= $model->input("email", array("value" => $item["email"])) ?>
			</fieldset>
			<ul class="actions">
				<?= $model->submit("Save", array("name" => "save", "wrapper" => "li.save")) ?>
			</ul>
		<?= $model->formEnd() ?>

		<?= $model->formStart("updateMobile", array("class" => "mobile labelstyle:inject")) ?>
			<fieldset>
				<?= $model->input("mobile", array("value" => $item["mobile"])) ?>
			</fieldset>
			<ul class="actions">
				<?= $model->submit("Save", array("name" => "save", "wrapper" => "li.save")) ?>
			</ul>
		<?= $model->formEnd() ?>

	</div>

	<div class="password i:passwordProfile i:collapseHeader">
		<h2>Password</h2>
		<div class="password_state set">
			<p class="password_set">Your password is encrypted and cannot be shown here. <a>Change password</a>.</p>
		</div>
		<div class="new_password">
			<p>Please type your existing password and your new password.</p>
			<?= $model->formStart("setPassword", array("class" => "password")) ?>
				<fieldset>
					<?= $model->input("old_password", array("required" => true)) ?>
					<?= $model->input("new_password", array("required" => true)) ?>
				</fieldset>
				<ul class="actions">
					<?= $model->submit("Update password", array("class" => "primary", "wrapper" => "li.save")) ?>
					<?= $model->button("Cancel", array("wrapper" => "li.cancel")) ?>
				</ul>
			<?= $model->formEnd() ?>
		</div>
	</div>

	<? if(defined("SITE_SHOP") && SITE_SHOP): ?>
	<div class="payment_methods i:paymentMethods i:collapseHeader">
		<h2>Payment methods</h2>

		<? if($user_payment_methods):
			$method_exists = false; ?>
		<ul class="payment_methods">

			<? foreach($user_payment_methods as $user_payment_method): ?>

				<? if(isset($user_payment_method["cards"]) && $user_payment_method["cards"]):
				$method_exists = true;
				?>

					<? foreach($user_payment_method["cards"] as $card): ?>
				<li class="payment_method user_payment_method<?= $user_payment_method["classname"] ? " ".$user_payment_method["classname"] : "" ?><?= $card["default"] ? " default" : "" ?>">
					<h3><?= $user_payment_method["name"] ?> – card ending in <?= $card["last4"] ?><?= $card["default"] ? ' <span class="default">(default)</span>' : '' ?></h3>
					<p><?= $user_payment_method["description"] ?></p>
					<ul class="actions">
						<?= $HTML->oneButtonForm(
						"Delete", 
						"deletePaymentMethod/card",
						array(
							"inputs" => array(
								"user_payment_method_id" => $user_payment_method["id"], 
								"gateway_payment_method_id" => $card["id"]
							),
							"confirm-value" => "Yes, I'm serious",
							"class" => "",
							"name" => "delete",
							"wrapper" => "li.delete.".$user_payment_method["classname"],
						)) ?>
					</ul>
				</li>
					<? endforeach; ?>

				<? elseif(!$user_payment_method["gateway"]):
					$method_exists = true;
				?>
				<li class="payment_method user_payment_method<?= $user_payment_method["classname"] ? " ".$user_payment_method["classname"] : "" ?><?= $user_payment_method["default_method"] ? " default" : "" ?>">
					<h3><?= $user_payment_method["name"] ?></h3>
					<p><?= $user_payment_method["description"] ?><?= $user_payment_method["default_method"] ? ' <span class="default">(default)</span>' : '' ?></p>
					<ul class="actions">
						<?= $HTML->oneButtonForm(
						"Delete", 
						"deletePaymentMethod",
						array(
							"inputs" => array(
								"user_payment_method_id" => $user_payment_method["id"], 
							),
							"confirm-value" => false,
							"static" => true,
							"class" => "",
							"name" => "continue",
							"wrapper" => "li.delete.".$user_payment_method["classname"],
						)) ?>
					</ul>
				</li>
				<? endif; ?>

			<? endforeach; ?>

		</ul>

		<? endif; ?>

		<? if(!isset($method_exists) || !$method_exists): ?>
		<p>You do not have any payment methods yet. Make a purchase to add your first payment method.</p>
		<? endif; ?>

	</div>
	<? endif; ?>


	<div class="apitoken i:apitokenProfile i:collapseHeader">
		<h2>API Token</h2>
		<p class="token"><?= stringOr($apitoken, "N/A") ?></p>

		<?= $model->formStart("renewToken", array("class" => "renew")) ?>
			<ul class="actions">
				<?= $model->submit(($apitoken ? "Renew API token" : "Create API token"), array("class" => "primary", "wrapper" => "li.renew")) ?>
			</ul>
		<?= $model->formEnd() ?>
		<?= $model->formStart("disableToken", array("class" => "disable")) ?>
			<ul class="actions">
				<?= $model->submit("Disable token", array("class" => "secondary".($apitoken ? "" : " disabled"), "wrapper" => "li.renew")) ?>
			</ul>
		<?= $model->formEnd() ?>
	</div>

	<div class="addresses i:collapseHeader">
		<h2>Addresses</h2>
<?		if($addresses): ?>
		<ul class="addresses">
<?			foreach($addresses as $address): ?>
			<li>
				<?= $address["address_label"] ? ('<div class="address_label">' . $address["address_label"] . '</div>') : '' ?>
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
					<?= $model->link("Edit", "/janitor/admin/profile/address/edit/".$address["id"], array("class" => "button", "wrapper" => "li.edit")) ?>
				</ul>
			</li>
<?			endforeach; ?>
		</ul>
<?		else: ?>
		<p>You don't have any addresses associated with your account</p>
<?		endif; ?>

		<ul class="actions">
			<?= $model->link("Add new address", "/janitor/admin/profile/address/new", array("class" => "button primary", "wrapper" => "li.add")) ?>
		</ul>
	</div>

	<div class="cancellation i:cancellationProfile i:collapseHeader">
		<h2>Cancellation</h2>
		<p>
			If you cancel your account, we'll delete your personal information and your 
			membership and subscriptions entirely from our system.
		</p>
		<p>
			To cancel your membership only, choose the "Membership" tab above.
		</p>
		<p>
			To unsubscribe from our maillists, choose the "Maillists" tab above.
		</p>

<? if($unpaid_orders): ?>
		<p class="note system_error">
			You have <?= pluralize(count($unpaid_orders), "unpaid order", "unpaid orders")?>. 
			Settle <?= (count($unpaid_orders) == 1 ? "it" : "them") ?> before you
			cancel your account.
		</p>

		<ul class="actions">
			<?= $HTML->link("Cancel account", "", array("class" => "button disabled", "wrapper" => "li.cancelaccount")) ?>
			<?= $HTML->link("Orders", "/janitor/admin/profile/orders/list", array("class" => "button primary", "wrapper" => "li.orders")) ?>
		</ul>
<? else: ?>
		<?= $model->formStart("cancel", array("class" => "cancelaccount")) ?>

			<fieldset>
				<?= $model->input("password", array("label" => "Please type your password to confirm cancellation", "required" => true)) ?>
			</fieldset>

			<ul class="actions">
				<?= $model->submit("Cancel account", array("class" => "secondary", "wrapper" => "li.cancelaccount")) ?>
			</ul>

		<?= $model->formEnd() ?>
<? endif; ?>


	</div>

</div>