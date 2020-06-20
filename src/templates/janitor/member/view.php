<?php
global $action;
global $model;
$SC = new Shop();
include_once("classes/users/superuser.class.php");
$UC = new SuperUser();
include_once("classes/shop/supersubscription.class.php");
$SubscriptionClass = new SuperSubscription();

$user_id = $action[1];


$user = $UC->getUsers(array("user_id" => $user_id));
$membership = $model->getMembers(array("user_id" => $user_id));


// Order history
$orders = false;
if(defined("SITE_SHOP") && SITE_SHOP) {
	include_once("classes/shop/supershop.class.php");
	$SC = new SuperShop();

	$orders = $SC->getOrders(array("user_id" => $user_id, "itemtype" => "membership"));
}


$price = $SC->getPrice($membership["item_id"]);
$subscription = $SubscriptionClass->getSubscriptions(["item_id" => $membership["item_id"], "user_id" => $membership["user_id"]]);

$payment_method = $model->getPaymentMethodForSubscription(["subscription_id" => $subscription["id"], "user_id" => $user_id]);

if($subscription["custom_price"]) {
	$custom_price = $price;
	$custom_price["price"] = $subscription["custom_price"];
}
else {
	$custom_price = false;
}


// FOR TESTING EMAIL SENDING
// $subscription = $SubscriptionClass->getSubscriptions(array("subscription_id" => $membership["subscription_id"]));
// $IC = new Items();
// $mem = $IC->typeObject("membership");
// $mem->subscribed($subscription);

?>
<div class="scene i:scene defaultView userMembership">
	<h1>Membership</h1>
	<h2><?= $user["nickname"] ?></h2>

	<ul class="actions">
		<?= $HTML->link("All users", "/janitor/admin/user/list/".$user["user_group_id"], array("class" => "button", "wrapper" => "li.cancel")) ?>
	<? if($membership): ?>
		<?= $HTML->link("Members", "/janitor/admin/member/list/".$membership["item_id"], array("class" => "button", "wrapper" => "li.members")); ?>
	<? endif; ?>
	</ul>


	<?= $JML->userTabs($user_id, "membership") ?>


<? if($membership): ?>
	<div class="item">
		<h2>Membership details</h2>

		<? if($membership["subscription_id"]):
			$subscription = $SubscriptionClass->getSubscriptions(array("subscription_id" => $membership["subscription_id"])); ?>
		<h3><span>#<?= $membership["id"] ?></span> - <?= $membership["item"]["name"] ?></h3>
		<? else: ?>
		<h3><span>#<?= $membership["id"] ?></span> - Inactive membership</h3>
		<? endif; ?>

		
		<dl class="info">
			<dt class="created_at">Created at</dt>
			<dd class="created_at"><?= date("d. F, Y", strtotime($membership["created_at"])) ?></dd>

		<? if($membership["modified_at"]): ?>
			<dt class="modified_at">Modified at</dt>
			<dd class="modified_at"><?= date("d. F, Y", strtotime($membership["modified_at"])) ?></dd>
		<? endif; ?>

		<? if($membership["renewed_at"]): ?>
			<dt class="renewed_at">Renewed at</dt>
			<dd class="renewed_at"><?= date("d. F, Y", strtotime($membership["renewed_at"])) ?></dd>
		<? endif; ?>

		<? if($membership["expires_at"]): ?>
			<dt class="expires_at">Expires at</dt>
			<dd class="expires_at"><?= date("d. F, Y", strtotime($membership["expires_at"])) ?></dd>
		<? endif; ?>

		<? if($membership["item"]["prices"]):
			$offer = arrayKeyValue($membership["item"]["prices"], "type", "offer");
			$default = arrayKeyValue($membership["item"]["prices"], "type", "default");
			?>

			<? if(isset($custom_price) && $custom_price !== false): ?>
			<dt class="price default">Normal price</dt>
			<dd class="price default"><?= formatPrice($membership["item"]["prices"][$default]).($membership["item"]["subscription_method"] ? " / " . $membership["item"]["subscription_method"]["name"] : "") ?></dd>
			<dt class="price custom">Your price</dt>
			<dd class="price custom"><span class="price"><?= formatPrice($custom_price) ?></span><?= ($membership["item"]["subscription_method"] ? " / " . $membership["item"]["subscription_method"]["name"] : "") ?></dd>
			<? elseif($offer !== false && $default !== false): ?>
			<dt class="price default">Normal price</dt>
			<dd class="price default"><?= formatPrice($membership["item"]["prices"][$default]).($membership["item"]["subscription_method"] ? " / " . $membership["item"]["subscription_method"]["name"] : "") ?></dd>
			<dt class="price offer">Special offer</dt>
			<dd class="price offer"><span class="price"><?= formatPrice($membership["item"]["prices"][$offer]) ?></span><?= ($membership["item"]["subscription_method"] ? " / " . $membership["item"]["subscription_method"]["name"] : "") ?></dd>
			<? elseif($default !== false): ?>
			<dt class="price">Price</dt>
			<dd class="price"><span class="price"><?= formatPrice($membership["item"]["prices"][$default]) ?></span><?= ($membership["item"]["subscription_method"] ? " / " . $membership["item"]["subscription_method"]["name"] : "") ?></dd>
			<? endif; ?>


			<? if($default !== false && $membership["item"]["prices"][$default]["price"] !== "0"): ?>

				<? if($payment_method): ?>
			<dt class="payment_method">Payment method</dt>
			<dd class="payment_method"><?= $payment_method["name"] . ($payment_method["card"] ? " ending in " . $payment_method["card"]["last4"] : "") ?></dd>
				<? endif; ?>

				<? if($membership["order_id"]): ?>
			<dt class="payment_status">Payment status</dt>
			<dd class="payment_status <?= ["unpaid", "partial", "paid"][$membership["order"]["payment_status"]] ?>"><?= $SC->payment_statuses[$membership["order"]["payment_status"]] ?></dd>
				<? endif; ?>

			<? endif; ?>

		<? endif; ?>


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
					<p class="description"><?= $order["comment"] ?></p>

					<dl class="info">
						<dt class="created_at">Created at</dt>
						<dd class="created_at"><?= date("d. F, Y", strtotime($order["created_at"])) ?></dd>

						<dt class="total_price">Total price</dt>
						<dd class="total_price"><?= formatPrice($total_price) ?></dd>

<?						if($order["status"] < 2): ?>
						<dt class="payment_status">Status</dt>
						<dd class="payment_status <?= ["unpaid", "partial", "paid"][$order["payment_status"]] ?>"><?= $SC->payment_statuses[$order["payment_status"]] ?></dd>
<?						else: ?>
						<dt class="status">Status</dt>
						<dd class="status <?= superNormalize($SC->order_statuses[$order["status"]]) ?>"><?= $SC->order_statuses[$order["status"]] ?></dd>
<?						endif; ?>
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
		<h2>Change membership</h2>
		<p>
			Changing the membership does not affect any
			existing orders.
		</p>


		<div class="option">
			<h3>Switch to a new membership</h3>
			<p>
				- cancel the existing membership subscription and add a new one, starting today.
			</p>
			<ul class="actions">
				<?= $HTML->link("New membership", "/janitor/admin/member/switch/".$user_id, array("class" => "button", "wrapper" => "li.edit")) ?>
			</ul>
		</div>

		<? if($membership["order"]): ?>
		<div class="option">
			<h3>Upgrade the existing membership</h3>
			<p>
				- adds an order for the price difference and maintains the current renewal cyclus.
			</p>
			<ul class="actions">
				<?= $HTML->link("Upgrade membership", "/janitor/admin/member/upgrade/".$user_id, array("class" => "button", "wrapper" => "li.edit")) ?>
			</ul>
		</div>
		<? endif; ?>

	</div>


	<? if($membership["order"]): ?>
	<div class="cancel i:collapseHeader">
		<h2>Cancel membership</h2>
		<p>
			Cancel the existing subscription, and leave membership inactive.
		</p>

		<ul class="actions">
			<?= $HTML->oneButtonForm("Cancel membership", "/janitor/admin/member/cancelMembership/".$user_id."/".$membership["id"], array(
				"confirm-value" => "Confirm cancellation",
				"wrapper" => "li.cancel",
				"class" => "secondary",
				"success-location" => "/janitor/admin/member/view/".$user_id
			)) ?>
		</ul>

	</div>
	<? endif; ?>


<? else: ?>

	<div class="item">
		<h2>Membership details</h2>
		<p>This user does not have a membership.</p>
	</div>

	<div class="add i:collapseHeader">
		<h2>Add membership</h2>
		<ul class="actions">
			<?= $HTML->link("Add membership", "/janitor/admin/member/add/".$user_id, array("class" => "button", "wrapper" => "li.edit")) ?>
		</ul>
	</div>

<? endif; ?>

</div>