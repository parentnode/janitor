<?php
global $action;
global $model;
$IC = new Items();
$SC = new Shop();
include_once("classes/users/superuser.class.php");
$UC = new SuperUser();

$user_id = $action[1];


$user = $UC->getUsers(array("user_id" => $user_id));
$member = $model->getMembers(array("user_id" => $user_id));

$memberships = $IC->getItems(array("itemtype" => "membership", "status" => 1, "extend" => array("subscription_method" => true, "prices" => true)));

$membership_options = array();
foreach($memberships as $membership) {
	// do not include current membership
	if($membership["item_id"] != $member["item_id"])  {
		$price = $SC->getPrice($membership["item_id"]);
		$membership_options[$membership["item_id"]] = strip_tags($membership["name"])." (".formatPrice($price).")";
	}
}

?>
<div class="scene i:scene defaultEdit userMember">
	<h1>Switch to a new membership</h1>
	<h2><?= $user["nickname"] ?> / <?= $member["item"] ? $member["item"]["name"] : "Inactive member" ?></h2>

	<ul class="actions">
		<?= $HTML->link("Back", "/janitor/admin/member/view/".$user_id, array("class" => "button", "wrapper" => "li.membership")); ?>
	</ul>

	<div class="item">
		<h2>Switch to a new membership</h2>
		<?= $model->formStart("/janitor/admin/member/switchMembership/".$user_id, array("class" => "i:defaultNew labelstyle:inject")) ?>
			<fieldset>
				<?= $model->input("item_id", array(
					"label" => "Select a new membership",
					"type" => "select",
					"options" => $membership_options,
					"value" => $member["item_id"]
				)) ?>
			</fieldset>

			<p>
				This will cancel the existing membership and add a new one, starting today. <br />
				The membership will be changed immediately.
			</p>

			<ul class="actions">
				<?= $model->link("Cancel", "/janitor/admin/member/view/".$user_id, array("class" => "button key:esc", "wrapper" => "li.cancel")) ?>
				<?= $model->submit("Update", array("class" => "primary key:s", "wrapper" => "li.update")) ?>
			</ul>
		<?= $model->formEnd() ?>
	</div>

</div>