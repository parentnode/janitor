<?php
global $action;
global $model;
$IC = new Items();
$SC = new Shop();
include_once("classes/users/supermember.class.php");
$MC = new SuperMember();

$user_id = $action[2];

$user = $model->getUsers(array("user_id" => $user_id));
$member = $MC->getMembers(array("user_id" => $user_id));
$current_membership_price = $SC->getPrice($member["item_id"]);

$memberships = $IC->getItems(array("itemtype" => "membership", "status" => 1, "extend" => array("subscription_method" => true, "prices" => true)));

$membership_options = array();
foreach($memberships as $membership) {
	$price = $SC->getPrice($membership["item_id"]);
	if($current_membership_price["price"] < $price["price"]) {
		$membership_options[$membership["item_id"]] = strip_tags($membership["name"])." (".formatPrice($price).")";
	}
}

?>
<div class="scene i:scene defaultEdit userMember">
	<h1>Upgrade your existing membership</h1>
	<h2><?= $user["nickname"] ?> / <?= $member["item"]["name"] ?></h2>

	<ul class="actions">
		<?= $HTML->link("Back", "/janitor/admin/user/membership/view/".$user_id, array("class" => "button", "wrapper" => "li.membership")); ?>
	</ul>

<? if(count($membership_options)): ?>
	<div class="item">
		<h2>Upgrade your existing membership</h2>
		<?= $model->formStart("/janitor/admin/user/upgradeMembership/".$user_id, array("class" => "i:defaultNew labelstyle:inject")) ?>
			<fieldset>
				<?= $model->input("item_id", array(
					"label" => "Select a new membership",
					"type" => "select",
					"options" => $membership_options,
					"value" => $member["item_id"]
				)) ?>
			</fieldset>

			<p>
				This will update the existing membership - it adds an order with the price difference and maintains the current renewal cyclus. <br />
				The membership will be changed immediately.
			</p>

			<ul class="actions">
				<?= $model->link("Cancel", "/janitor/admin/user/membership/view/".$user_id, array("class" => "button key:esc", "wrapper" => "li.cancel")) ?>
				<?= $model->submit("Update", array("class" => "primary key:s", "wrapper" => "li.update")) ?>
			</ul>
		<?= $model->formEnd() ?>
	</div>
<? else: ?>

	<h3>Your membership cannot be upgraded.</h3>
	<p>(There are no upgrades available for <?= $member["item"]["name"] ?> members)</p>

<? endif; ?>

</div>