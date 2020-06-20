<?php
global $action;
global $model;


$user_id = session()->value("user_id");
$IC = new Items();
$SC = new Shop();
include_once("classes/shop/subscription.class.php");
$SubscriptionClass = new Subscription();

// get current user
$item = $model->getUser();

$subscriptions = false;

if(defined("SITE_SUBSCRIPTIONS") && SITE_SUBSCRIPTIONS) {

	$subscriptions = $SubscriptionClass->getSubscriptions();

}

?>
<div class="scene i:scene defaultList userSubscriptions profileSubscriptions">
	<h1>Subscriptions</h1>
	<h2><?= $item["nickname"] ?></h2>

	<?= $JML->profileTabs("subscriptions") ?>


	<div class="subscriptions item">
		<h2>Subscriptions</h2>
		<div class="all_items i:defaultList filters">
			<? if($subscriptions): ?>

			<ul class="items subscriptions">
				<? foreach($subscriptions as $subscription):

					$price = $SC->getPrice($subscription["item_id"]);

					$payment_method = $model->getPaymentMethodForSubscription(["subscription_id" => $subscription["id"]]);

					if($subscription["custom_price"] || $subscription["custom_price"] === "0") {
						$custom_price = $price;
						$custom_price["price"] = $subscription["custom_price"];
					}
					else {
						$custom_price = false;
					}

				 ?>
				<li class="item subscription">
					<h3><?= $subscription["item"]["name"] ?></h3>
				
					<dl class="info">
						<dt class="created_at">Created at</dt>
						<dd class="created_at"><?= date("d. F, Y", strtotime($subscription["created_at"])) ?></dd>

					<? if($subscription["modified_at"]): ?>
						<dt class="modified_at">Modified at</dt>
						<dd class="modified_at"><?= date("d. F, Y", strtotime($subscription["modified_at"])) ?></dd>
					<? endif; ?>

					<? if($subscription["renewed_at"]): ?>
						<dt class="renewed_at">Renewed at</dt>
						<dd class="renewed_at"><?= date("d. F, Y", strtotime($subscription["renewed_at"])) ?></dd>
					<? endif; ?>

					<? if($subscription["expires_at"]): ?>
						<dt class="expires_at">Expires at</dt>
						<dd class="expires_at"><?= date("d. F, Y", strtotime($subscription["expires_at"])) ?></dd>
					<? endif; ?>


					<? if($subscription["item"]["prices"]):
						$offer = arrayKeyValue($subscription["item"]["prices"], "type", "offer");
						$default = arrayKeyValue($subscription["item"]["prices"], "type", "default");
						?>

						<? if(isset($custom_price) && $custom_price !== false): ?>
						<dt class="price default">Normal price</dt>
						<dd class="price default"><?= formatPrice($subscription["item"]["prices"][$default]).($subscription["item"]["subscription_method"] ? " / " . $subscription["item"]["subscription_method"]["name"] : "") ?></dd>
						<dt class="price custom">Your price</dt>
						<dd class="price custom"><span class="price"><?= formatPrice($custom_price) ?></span><?= ($subscription["item"]["subscription_method"] ? " / " . $subscription["item"]["subscription_method"]["name"] : "") ?></dd>
						<? elseif($offer !== false && $default !== false): ?>
						<dt class="price default">Normal price</dt>
						<dd class="price default"><?= formatPrice($subscription["item"]["prices"][$default]).($subscription["item"]["subscription_method"] ? " / " . $subscription["item"]["subscription_method"]["name"] : "") ?></dd>
						<dt class="price offer">Special offer</dt>
						<dd class="price offer"><span class="price"><?= formatPrice($subscription["item"]["prices"][$offer]) ?></span><?= ($subscription["item"]["subscription_method"] ? " / " . $subscription["item"]["subscription_method"]["name"] : "") ?></dd>
						<? elseif($default !== false): ?>
						<dt class="price">Price</dt>
						<dd class="price"><span class="price"><?= formatPrice($subscription["item"]["prices"][$default]) ?></span><?= ($subscription["item"]["subscription_method"] ? " / " . $subscription["item"]["subscription_method"]["name"] : "") ?></dd>
						<? endif; ?>

						<? if($payment_method): ?>
						<dt class="payment_method">Payment method</dt>
						<dd class="payment_method"><?= $payment_method["name"] . ($payment_method["card"] ? " ending in " . $payment_method["card"]["last4"] : "") ?></dd>
						<? endif;?>
					<? endif; ?>

					<? if($subscription["order_id"]): ?>
						<dt class="payment_status">Payment status</dt>
						<dd class="payment_status <?= ["unpaid", "partial", "paid"][$subscription["order"]["payment_status"]] ?>"><?= $SC->payment_statuses[$subscription["order"]["payment_status"]] ?></dd>
					<? endif; ?>


					<? if($subscription["membership"]): ?>
						<dt class="membership">Membership</dt>
						<dd class="membership">This subscription is used for membership.</dd>
					<? endif; ?>

					</dl>

					<ul class="actions">
					<? if(!$subscription["membership"]): ?>
						<? if($subscription["item"]["subscription_method"]): ?>
						<?= $HTML->link("Edit", "/janitor/admin/user/subscription/edit/".$user_id."/".$subscription["id"], array("class" => "button", "wrapper" => "li.cancel")) ?>
						<? endif; ?>

						<?= $HTML->oneButtonForm("Delete", "/janitor/admin/profile/deleteSubscription/".$subscription["id"], array(
							"js" => true,
							"wrapper" => "li.delete",
							"static" => true
						)) ?>
					<? else: ?>
						<?= $HTML->link("View", "/janitor/admin/profile/membership/view", array("class" => "button", "wrapper" => "li.cancel")) ?>


					<? endif; ?>
					</ul>

				 </li>
			 	<? endforeach; ?>
			</ul>

			<? else: ?>
			<p>No subscriptions.</p>
			<? endif; ?>
		</div>

	</div>

</div>