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

?>
<div class="scene i:scene defaultList memberList">
	<h1>Members</h1>

	<ul class="actions">
		<?//= $HTML->link("New member", "/janitor/admin/user/member/new", array("class" => "button primary", "wrapper" => "li.users")) ?>
		<?= $HTML->link("Users", "/janitor/admin/user/list", array("class" => "button", "wrapper" => "li.users")) ?>
	</ul>

<?	if($memberships): ?>
	<ul class="tabs">
<?		foreach($memberships as $membership): ?>
		<?= $HTML->link($membership["name"], "/janitor/admin/user/members/list/".$membership["id"], array("wrapper" => "li.".($membership["id"] == $membership_id ? "selected" : ""))) ?>
<?		endforeach; ?>
		<?= $HTML->link("All", "/janitor/admin/user/members/list/0", array("wrapper" => "li.".($options === false ? "selected" : ""))) ?>
	</ul>
<?	endif; ?>


	<div class="all_items i:defaultList filters">
<?		if($members): ?>
		<ul class="items">
<?			foreach($members as $member): ?>
			<li class="item item_id:<?= $member["id"] ?><?= !$member["subscription_id"] ? " cancelled" : "" ?>">
				<h3><?= $member["user"]["nickname"] ?><?= $member["user"]["id"] == session()->value("user_id") ? " (YOU)" : "" ?></h3>
				
				<dl class="info">
					<dt class="member_no">Member</dt>
					<dd class="member_no">#<?= $member["id"] ?></dd>

				<? if($membership_id === 0): ?>
					<dt class="membership">Membership</dt>
					<dd class="membership"><?= $member["item"] ? $member["item"]["name"] : "Cancelled" ?></dd>
				<? endif; ?>

				<? if($member["order"]): ?>
					<dt class="payment_status">Payment status</dt>
					<dd class="payment_status<?= $member["order"]["payment_status"] < 2 ? " missing" : "" ?>"><?= $SC->payment_statuses[$member["order"]["payment_status"]] ?></dd>
				<? endif; ?>

				<? if($member["created_at"]): ?>
					<dt class="created_at">Created at</dt>
					<dd class="created_at"><?= date("d. F, Y", strtotime($member["created_at"])) ?></dd>
				<? endif; ?>

				<? if($member["modified_at"]): ?>
					<dt class="modified_at">Modified at</dt>
					<dd class="modified_at"><?= date("d. F, Y", strtotime($member["modified_at"])) ?></dd>
				<? endif; ?>

				<? if($member["renewed_at"]): ?>
					<dt class="renewed_at">Last renewed at</dt>
					<dd class="renewed_at"><?= date("d. F, Y", strtotime($member["renewed_at"])) ?></dd>
				<? endif; ?>

				<? if($member["expires_at"]): ?>
					<dt class="expires_at">Expires at</dt>
					<dd class="expires_at"><?= date("d. F, Y", strtotime($member["expires_at"])) ?></dd>
				<? endif; ?>
				</dl>
				

				<ul class="actions">
				<? if($member["order_id"]): ?>
					<?= $HTML->link("View order", "/janitor/admin/shop/order/edit/".$member["order_id"], array("class" => "button", "wrapper" => "li.order")) ?>
					<?= $HTML->link("Edit", "/janitor/admin/user/members/edit/".$member["id"], array("class" => "button", "wrapper" => "li.edit")) ?>
				<? endif; ?>
				</ul>
			 </li>
<?			endforeach; ?>
		</ul>
<?		else: ?>
		<p>No members.</p>
<?		endif; ?>
	</div>

</div>