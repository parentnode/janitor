<?php
global $action;
global $model;
$IC = new Items();
$SC = new Shop();

$user_id = session()->value("user_id");

$user = $model->getUser();
$member = $model->getMembership();

$memberships = $IC->getItems(array("itemtype" => "membership", "extend" => array("subscription_method" => true, "prices" => true)));

$membership_options = array();
foreach($memberships as $membership) {
	$price = $SC->getPrice($membership["item_id"]);
	$membership_options[$membership["item_id"]] = strip_tags($membership["name"])." (".formatPrice($price).")";
}

?>
<div class="scene i:scene defaultEdit userMember">
	<h1>Edit membership</h1>
	<h2><?= $user["nickname"] ?> / <?= $member["item"]["name"] ?></h2>

	<ul class="actions">
		<?= $HTML->link("Back", "/janitor/admin/profile/membership/view", array("class" => "button", "wrapper" => "li.membership")); ?>
	</ul>

	<div class="item">
		<h2>Change membership</h2>
		<?= $model->formStart("/janitor/admin/profile/changeMembership", array("class" => "i:defaultNew labelstyle:inject")) ?>
			<fieldset>
				<?= $model->input("item_id", array(
					"label" => "Select a new membership",
					"type" => "select",
					"options" => $membership_options,
					"value" => $member["item_id"]
				)) ?>
			</fieldset>

			<p>The membership will be changed immediately. <br />We will contact you regarding refunds/additional payment.</p>

			<ul class="actions">
				<?= $model->link("Cancel", "/janitor/admin/profile/membership/view", array("class" => "button key:esc", "wrapper" => "li.cancel")) ?>
				<?= $model->submit("Update", array("class" => "primary key:s", "wrapper" => "li.update")) ?>
			</ul>
		<?= $model->formEnd() ?>
	</div>

	<? /*<div class="item">
		<h2>Cancel your membership</h2>

		<p>You membership will be cancelled immediately. <br />We will contact you regarding refunds/additional payment.</p>
		<ul class="actions">
			<?= $JML->oneButtonForm("Cancel your membership", "/janitor/admin/shop/cancelMembership/".$member["id"], array(
				"wrapper" => "li.cancelmembership",
				"class" => "secondary"
			)) ?>
		</ul>

	</div> */ ?>

</div>