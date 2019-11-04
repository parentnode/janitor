<?php
global $action;
global $model;
$IC = new Items();
include_once("classes/shop/supersubscription.class.php");
$SubscriptionClass = new SuperSubscription();


$user_id = $action[2];
$user = $model->getUsers(array("user_id" => $user_id));

$item_id = false;
$selected_item = false;

$items = $IC->getItems(array("order" => "itemtype", "extend" => array("subscription_method" => true, "prices" => true)));
$subscriptions = $SubscriptionClass->getSubscriptions(array("user_id" => $user_id));

//print_r($items);
//print_r($subscriptions);
$item_options[""] = "Select item";

// only show items without a price
foreach($items as $item) {
	if(arrayKeyValue($subscriptions, "item_id", $item["item_id"]) === false) {
		if(!$item["prices"]) {
			$item_options[$item["item_id"]] = strip_tags($item["name"])." (".$item["itemtype"].")";
		}
	}
}


?>
<div class="scene i:scene defaultNew userSubscriptions">
	<h1>New subscription</h1>
	<h2><?= $user["nickname"] ?></h2>

	<ul class="actions">
		<?= $HTML->link("Back", "/janitor/admin/user/subscription/list/".$user_id, array("class" => "button", "wrapper" => "li.subscriptions")); ?>
	</ul>

	<?= $model->formStart("/janitor/admin/user/addSubscription", array("class" => "i:defaultNew labelstyle:inject")) ?>
		<?= $model->input("user_id", array("type" => "hidden", "value" => $user_id)) ?>
		<fieldset>
			<?= $model->input("item_id", array(
				"label" => "Select item to subscribe to",
				"type" => "select",
				"options" => $item_options,
				"value" => $item_id
			)) ?>
		</fieldset>

		<p>Only items without price can be added here. Paid items requires an order.</p>

		<ul class="actions">
			<?= $model->link("Cancel", "/janitor/admin/user/subscription/list/".$user_id, array("class" => "button key:esc", "wrapper" => "li.cancel")) ?>
			<?= $model->submit("Add subscription", array("class" => "primary key:s", "wrapper" => "li.save")) ?>
		</ul>
	<?= $model->formEnd() ?>

</div>