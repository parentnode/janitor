<?php
global $action;
global $model;
$IC = new Items();
include_once("classes/shop/subscription.class.php");
$SubscriptionClass = new Subscription();


$user_id = $action[2];
$user = $model->getUsers(array("user_id" => $user_id));

$subscription_id = $action[3];
$subscription = $SubscriptionClass->getSubscriptions(array("subscription_id" => $subscription_id, "extend" => array("prices" => true, "subscription_method" => true)));

$payment_methods = $this->paymentMethods();
$payment_method_options = $model->toOptions($payment_methods, "id", "name");

?>
<div class="scene i:scene defaultEdit userSubscriptions">
	<h1>Edit subscription</h1>
	<h2><?= $user["nickname"] ?> / <?= $subscription["item"]["name"] ?></h2>

	<ul class="actions">
		<?= $HTML->link("Back", "/janitor/admin/user/subscription/list/".$user_id, array("class" => "button", "wrapper" => "li.subscriptions")); ?>
	</ul>

	<div class="item">
		<h2>Payment method</h2>
		<?= $model->formStart("/janitor/admin/user/updateSubscription/".$user_id."/".$subscription_id, array("class" => "i:defaultNew labelstyle:inject")) ?>
			<fieldset>
				<?= $model->input("payment_method", array(
					"label" => "Select payment method for paid subscriptions",
					"type" => "select",
					"options" => $payment_method_options,
					"value" => $subscription["payment_method"] ? $subscription["payment_method"]["id"] : false
				)) ?>
			</fieldset>

			<ul class="actions">
				<?= $model->link("Cancel", "/janitor/admin/user/subscription/list/".$user_id, array("class" => "button key:esc", "wrapper" => "li.cancel")) ?>
				<?= $model->submit("Update", array("class" => "primary key:s", "wrapper" => "li.update")) ?>
			</ul>
		<?= $model->formEnd() ?>
	</div>

</div>