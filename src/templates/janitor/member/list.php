<?php
global $action;
global $model;
$IC = new Items();
$SC = new Shop();
include_once("classes/users/supermember.class.php");
$MC = new SuperMember();
include_once("classes/users/superuser.class.php");
$UC = new SuperUser();

$memberships = $IC->getItems(array("itemtype" => "membership", "extend" => true));
//print_r($memberships);



$membership_id = 0;

$options = [
	"limit" => 200,
	"pattern" => false
];


$query = getVar("query");
if($query) {
	$options["query"] = $query;
}

if(count($action) > 3) {
	if($action[2] === "page") {
		$options["page"] = $action[3];
	}
}


// show specific order status
if(count($action) > 1) {
	$membership_id = $action[1];

	if($membership_id) {

		$options["pattern"] = [
			"item_id" => $membership_id
		];

	}
}
// show default = 0
else if($memberships) {
	$membership_id = $memberships[0]["id"];


	$options["pattern"] = [
		"item_id" => $membership_id
	];

}

$members = $MC->paginate($options);
// debug([$membership_id, "members" => $members]);

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
		<?= $HTML->link($membership["name"]. " (".$MC->getMemberCount(array("item_id" => $membership["id"])).")", "/janitor/admin/member/list/".$membership["id"], array("wrapper" => "li.".($membership["id"] == $membership_id ? "selected" : ""))) ?>
<?		endforeach; ?>
		<?= $HTML->link("All (".$MC->getMemberCount().")", "/janitor/admin/member/list/0", array("wrapper" => "li.".($membership_id == 0 ? "selected" : ""))) ?>
	</ul>
<?	else:?>
	<p>No memberships</p>
<?	endif; ?>


	<div class="all_items i:defaultList filters" <?= $HTML->jsData(["search"], ["filter-search" => $HTML->path."/list/$membership_id"]) ?>>
<?		if($members && $members["range_members"]): ?>

		<?= $HTML->pagination($members, [
			"base_url" => "/janitor/admin/member/list/$membership_id",
			"query" => $query,
		]) ?>

		<ul class="items">
<?			foreach($members["range_members"] as $member): ?>
			<li class="item item_id:<?= $member["id"] ?><?= !$member["subscription_id"] ? " cancelled" : "" ?>">
				<h3><span>#<?= $member["id"] ?></span> – <?= $member["nickname"] ?><?= $member["user_id"] == session()->value("user_id") ? " (YOU)" : "" ?></h3>
				
				<dl class="info">
				<? 
				// only on "wiew all", display membership type
				if($membership_id == 0): ?>
					<dt class="membership">Membership</dt>
					<dd class="membership"><?= $member["subscription_id"] ? $member["membership"] : "Cancelled" ?></dd>
				<? endif; ?>

				<? if($member["membership"] !== NULL): ?>

					<? if($member["payment_status"] !== NULL): ?>
					<dt class="payment_status">Payment status</dt>
					<dd class="payment_status <?= ["unpaid", "partial", "paid"][$member["payment_status"]] ?>"><?= $SC->payment_statuses[$member["payment_status"]] ?></dd>
					<? endif; ?>

					<? if($member["custom_price"] !== NULL): ?>
					<dt class="custom_price">Actual price</dt>
					<dd class="custom_price"><?= formatPrice(["price" => $member["custom_price"], "currency" => $member["currency"]]) ?></dd>
					<? endif; ?>

					<dt class="price">Listed price</dt>
					<dd class="price"><?= formatPrice(["price" => $member["price"], "currency" => $member["currency"]]) ?></dd>

				<? endif; ?>


				<? if($member["expires_at"]): ?>
					<dt class="expires_at">Expires at</dt>
					<dd class="expires_at"><?= date("d. F, Y", strtotime($member["expires_at"])) ?></dd>
				<? endif; ?>

				<? if($member["usernames"]): ?>
					<dt class="email">Usernames</dt>
					<dd class="email"><?= $member["usernames"] ?></dd>
				<? endif; ?>
				</dl>

				<ul class="actions">
					<?= $HTML->link("Edit", "/janitor/admin/member/view/".$member["user_id"], array("class" => "button", "wrapper" => "li.edit")) ?>
				</ul>
			 </li>
<?			endforeach; ?>
		</ul>

		<?= $HTML->pagination($members, [
			"base_url" => "/janitor/admin/member/list/$membership_id",
			"query" => $query,
		]) ?>

<?		else: ?>
		<p>No members.</p>
<?		endif; ?>
	</div>

</div>