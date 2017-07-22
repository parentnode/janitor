<?php
global $action;
global $model;
$IC = new Items();
$SC = new Shop();

// Member details
$member_id = $action[2];
$member = $model->getMembers(array("member_id" => $member_id));

$email = $model->getUsernames(["user_id" => $member["user"]["id"], "type" => "email"]);
//print_r($member);

// Order history
$orders = false;
if(defined("SITE_SHOP") && SITE_SHOP) {
	include_once("classes/shop/supershop.class.php");
	$SC = new SuperShop();

	$orders = $SC->getOrders(array("user_id" => $member["user"]["id"], "itemtype" => "membership"));
}


// Change membership
$memberships = $IC->getItems(array("itemtype" => "membership", "extend" => array("subscription_method" => true, "prices" => true)));
$membership_options = array();
foreach($memberships as $membership) {
	$price = $SC->getPrice($membership["item_id"]);
	$membership_options[$membership["item_id"]] = strip_tags($membership["name"])." (".formatPrice($price).")";
}

?>
<div class="scene i:scene defaultEdit userMember">
	<h1>Membership details</h1>
	<h2><span>#<?= $member["id"] ?></span> / <?= $member["user"]["nickname"] ?> / <?= $member["item"]["name"] ?></h2>

	<ul class="actions">
		<?= $HTML->link("Member list", "/janitor/admin/user/members/list/".$member["item_id"], array("class" => "button", "wrapper" => "li.members")); ?>
		<?= $HTML->link("View user", "/janitor/admin/user/edit/".$member["user_id"], array("class" => "button", "wrapper" => "li.user")) ?>
	</ul>

	<div class="item">
		<h2>Membership details</h2>

		<h3><?= $member["user"]["nickname"] ?><?= $member["user"]["id"] == session()->value("user_id") ? " (YOU)" : "" ?></h3>

		<dl class="info">
			<dt class="member_no">Member No</dt>
			<dd class="member_no"><?= $member["id"] ?></dd>

			<dt class="email">Email</dt>
			<dd class="email"><?= $email ?></dd>

			<dt class="membership">Membership</dt>
			<dd class="membership"><?= $member["item"] ? $member["item"]["name"] : "Cancelled" ?></dd>

			<dt class="payment_status">Payment status</dt>
			<dd class="payment_status<?= ($member["order"] ? ($member["order"]["payment_status"] < 2 ? " missing" : "") : "") ?>"><?= ($member["order"] ? $SC->payment_statuses[$member["order"]["payment_status"]] : "N/A") ?></dd>
		</dl>

		<dl class="info">
			<dt class="created_at">Created at</dt>
			<dd class="created_at"><?= ($member["created_at"] ? date("d. F, Y", strtotime($member["created_at"])) : "N/A") ?></dd>

			<dt class="modified_at">Modified at</dt>
			<dd class="modified_at"><?= ($member["modified_at"] ? date("d. F, Y", strtotime($member["modified_at"])) : "N/A") ?></dd>

			<dt class="renewed_at">Last renewed at</dt>
			<dd class="renewed_at"><?= ($member["renewed_at"] ? date("d. F, Y", strtotime($member["renewed_at"])) : "N/A") ?></dd>

			<dt class="expires_at">Expires at</dt>
			<dd class="expires_at"><?= $member["expires_at"] ? date("d. F, Y", strtotime($member["expires_at"])) : "N/A" ?></dd>
		</dl>

	</div>

	<div class="orders i:collapseHeader">
		<h2>Order history</h2>

		<div class="all_items filters i:defaultList">
			<? if($orders): ?>
			<ul class="items">
				<? foreach($orders as $order):
				$total_price = $SC->getTotalOrderPrice($order["id"]); ?>
				<li class="item item_id:<?= $order["id"] ?>">
					<h3><span>#<?= $order["order_no"] ?></span></h3>

					<dl class="info">
						<dt class="created_at">Created at</dt>
						<dd class="created_at"><?= date("d. F, Y", strtotime($order["created_at"])) ?></dd>

						<dt class="total_price">Total price</dt>
						<dd class="total_price"><?= formatPrice($total_price) ?></dd>

						<dt class="order_status">Order status</dt>
						<dd class="order_status"><?= $SC->order_statuses[$order["status"]] ?></dd>
					</dl>

					<ul class="actions">
						<?= $HTML->link("View order", "/janitor/admin/shop/order/edit/".$order["id"], array("class" => "button", "wrapper" => "li.order")) ?>
					</ul>
				</li>
				<? endforeach; ?>
			</ul>
			<? else: ?>
			<p>No orders.</p>
			<? endif; ?>
		</div>
	</div>

	<div class="change i:collapseHeader">
		<h2>Change the membership</h2>
		<p>The membership can be changed on the user page.</p>

		<ul class="actions">
			<?= $model->link("View user", "/janitor/admin/user/membership/view/".$member["user_id"], array("class" => "button key:esc", "wrapper" => "li.cancel")) ?>
		</ul>
	</div>

</div>