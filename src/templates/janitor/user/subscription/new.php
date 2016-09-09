<?php
global $action;
global $model;
$IC = new Items();


$user_id = $action[2];
$user = $model->getUsers(array("user_id" => $user_id));

$item_id = false;
$selected_item = false;

$items = $IC->getItems(array("order" => "itemtype", "extend" => array("subscription_method" => true, "prices" => true)));
$subscriptions = $model->getSubscriptions(array("user_id" => $user_id));

//print_r($items);
//print_r($subscriptions);
$item_options[""] = "Select item";

foreach($items as $item) {
	if(arrayKeyValue($subscriptions, "item_id", $item["item_id"]) === false) {
		if(!$item["prices"]) {
			$item_options[$item["item_id"]] = strip_tags($item["name"])." (".$item["itemtype"].")";
		}
		// if($item["prices"]) {
		//
		// 	if(arrayKeyValue($item["prices"], "type", "offer") !== false) {
		// 		$item_options[$item["item_id"]] .= " – " . formatPrice($item["prices"][arrayKeyValue($item["prices"], "type", "offer")]);
		// 	}
		// 	else if(arrayKeyValue($item["prices"], "type", "default") !== false) {
		// 		$item_options[$item["item_id"]] .= " – " . formatPrice($item["prices"][arrayKeyValue($item["prices"], "type", "default")]);
		// 	}
		//
		// 	if($item["subscription_method"]) {
		// 		$item_options[$item["item_id"]] .= " / ".$item["subscription_method"]["name"];
		// 	}
		//
		// }
	}
}


// did actions also contain item_id
if(count($action) > 3) {
	$item_id = $action[3];

	
	$selected_item = $IC->getItem(array("id" => $item_id, "extend" => array("prices" => true, "subscription_method" => true)));
	// $payment_methods = $this->paymentMethods();
	// $payment_method_options = $model->toOptions($payment_methods, "id", "name");

//	print_r($selected_item);
}


?>
<div class="scene i:scene defaultNew userSubscriptions">
	<h1>New subscription</h1>
	<h2><?= $user["nickname"] ?></h2>

	<ul class="actions">
		<?= $HTML->link("Back", "/janitor/admin/user/subscription/list/".$user_id, array("class" => "button", "wrapper" => "li.subscriptions")); ?>
	</ul>

	<?= $model->formStart("/janitor/admin/user/addSubscription", array("class" => "i:newSubscription labelstyle:inject")) ?>
		<?= $model->input("user_id", array("type" => "hidden", "value" => $user_id)) ?>
		<fieldset>
			<?= $model->input("item_id", array(
				"label" => "Select item to subscribe to",
				"type" => "select",
				"options" => $item_options,
				"value" => $item_id
			)) ?>
		<? if($selected_item && $selected_item["prices"] && $selected_item["subscription_method"]): ?>

			<div class="item">
				<h3>"<?= $selected_item["name"] ?>" is a paid reoccuring subscription</h3>
				<p>Please select a payment method for the reoccuring payment of this subscription:</p>
			</div>

			<? if($selected_item["prices"]): ?>
			<?= $model->input("payment_method", array(
				"label" => "Select payment method for paid subscriptions",
				"class" => "payment_method",
				"type" => "select",
				"options" => $payment_method_options
			)) ?>
			<? endif; ?>

		<? endif; ?>
		</fieldset>

		<? if($selected_item): ?>
		<ul class="actions">
			<?= $model->link("Cancel", "/janitor/admin/user/subscription/list/".$user_id, array("class" => "button key:esc", "wrapper" => "li.cancel")) ?>
			<?= $model->submit("Add subscription", array("class" => "primary key:s", "wrapper" => "li.save")) ?>
		</ul>
		<? endif; ?>

	<?= $model->formEnd() ?>

</div>