<?php
global $action;
global $model;
$IC = new Items();
$SC = new Shop();

$memberships = $IC->getItems(array("itemtype" => "membership", "extend" => true));
//print_r($memberships);

$options = false;
$membership_id = 0;

// show specific membership tab?
if(count($action) > 2 && $action[2]) {
	$membership_id = $action[2];
	$options = array("item_id" => $membership_id);
}
// no membership type passed - default to first membership
else if(count($action) == 2 && $memberships) {
	$membership_id = $memberships[0]["id"];
	$options = array("item_id" => $membership_id);
}

// remember memberlist to return to (from new view)
// session()->value("return_to_memberlist", $membership_id);


$members = $model->getMembers($options);
//print_r($members);
?>
<div class="scene i:scene defaultList memberList">
	<h1>Members</h1>

	<ul class="actions">
		<?= $HTML->link("Users", "/janitor/admin/user/list", array("class" => "button", "wrapper" => "li.users")) ?>
		<?= $HTML->link("Orders", "/janitor/admin/shop/order/list", array("class" => "button", "wrapper" => "li.orders")) ?>
	</ul>

<?	if($memberships): ?>
	<ul class="tabs">
<?		foreach($memberships as $membership): ?>
		<?= $HTML->link($membership["name"]. " (".$model->getMemberCount(array("item_id" => $membership["id"])).")", "/janitor/admin/user/members/list/".$membership["id"], array("wrapper" => "li.".($membership["id"] == $membership_id ? "selected" : ""))) ?>
<?		endforeach; ?>
		<?= $HTML->link("All (".$model->getMemberCount().")", "/janitor/admin/user/members/list/0", array("wrapper" => "li.".($options === false ? "selected" : ""))) ?>
	</ul>
<?	endif; ?>


	<div class="all_items i:defaultList filters">
<?		if($members): ?>
		<ul class="items">
<?			foreach($members as $member):
				$username_email = $model->getUsernames(["user_id" => $member["user"]["id"], "type" => "email"]);
				if ($username_email) {
					$email = $username_email["username"]; 
				}
				else {
					$email = "Not available";
				}
				?>
			<li class="item item_id:<?= $member["id"] ?><?= !$member["subscription_id"] ? " cancelled" : "" ?>">
				<h3><span>#<?= $member["id"] ?></span> <?= $email . ($member["user"]["nickname"] != $email ? (", " . $member["user"]["nickname"]) : "") ?><?= $member["user"]["id"] == session()->value("user_id") ? " (YOU)" : "" ?></h3>
				
				<dl class="info">
				<? 
				// only on "wiew all", display membership type
				if($membership_id === 0): ?>
					<dt class="membership">Membership</dt>
					<dd class="membership"><?= $member["item"] ? $member["item"]["name"] : "Cancelled" ?></dd>
				<? endif; ?>

				<? if($member["order"]): ?>
					<dt class="payment_status">Payment status</dt>
					<dd class="payment_status <?= ["unpaid", "partial", "paid"][$member["order"]["payment_status"]] ?>"><?= $SC->payment_statuses[$member["order"]["payment_status"]] ?></dd>
				<? endif; ?>

				<? if($member["expires_at"]): ?>
					<dt class="expires_at">Expires at</dt>
					<dd class="expires_at"><?= date("d. F, Y", strtotime($member["expires_at"])) ?></dd>
				<? endif; ?>
				</dl>

				<ul class="actions">
					<?= $HTML->link("Edit", "/janitor/admin/user/membership/view/".$member["user_id"], array("class" => "button", "wrapper" => "li.edit")) ?>
				</ul>
			 </li>
<?			endforeach; ?>
		</ul>
<?		else: ?>
		<p>No members.</p>
<?		endif; ?>
	</div>

</div>