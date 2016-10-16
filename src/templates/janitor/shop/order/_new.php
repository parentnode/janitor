<?php
global $action;
global $model;
$IC = new Items();


include_once("classes/users/superuser.class.php");
$UC = new SuperUser();

$users = $UC->getUsers();
$user_options = $model->toOptions($users, "id", "nickname", array("add" => array("" => "Select user")));

$user_id = false;


// did actions also contain user_id
if(count($action) > 2) {
	$user_id = $action[2];

	// get addresses for selected user
	$addresses = $UC->getAddresses(array("user_id" => $user_id));
	if($addresses) {
		$delivery_address_options = $model->toOptions($addresses, "id", "address_label", array("add" => array("" => "Select delivery address")));
		$billing_address_options = $model->toOptions($addresses, "id", "address_label", array("add" => array("" => "Select billing address")));
	}
	else {
		$delivery_address_options = array("" => "No addresses");
		$billing_address_options = array("" => "No addresses");
	}
}

$return_to_orderstatus = session()->value("return_to_orderstatus");
?>
<div class="scene i:scene defaultNew newOrder">
	<h1>New order</h1>

	<ul class="actions">
		<?= $HTML->link("Back to orders", "/janitor/admin/shop/order/list/".$return_to_orderstatus, array("class" => "button", "wrapper" => "li.cancel")) ?>
	</ul>

	<?= $model->formStart("/janitor/admin/shop/addOrder", array("class" => "i:defaultNew labelstyle:inject")) ?>
		<fieldset>
			<?= $model->input("user_id", array(
				"type" => "select",
				"required" => true,
				"options" => $user_options,
				"value" => $user_id,
				"hint_message" => "Select user for this order"
			)) ?>

			<? if($user_id): ?>

			<?= $model->input("country", array(
				"type" => "select",
				"options" => $model->toOptions($this->countries(), "id", "name"),
			)) ?>
			<?= $model->input("currency", array(
				"type" => "select",
				"options" => $model->toOptions($this->currencies(), "id", "name"),
			)) ?>
			<?= $model->input("delivery_address_id", array(
				"type" => "select",
				"options" => $delivery_address_options
			)) ?>
			<?= $model->input("billing_address_id", array(
				"type" => "select",
				"options" => $billing_address_options
			)) ?>

			<?= $model->input("order_comment", array(
				"class" => "autoexpand"
			)) ?>

			<? endif; ?>
		</fieldset>

		<? if($user_id): ?>
		<ul class="actions">
			<?= $model->link("Back", "/janitor/admin/shop/order/list", array("class" => "button key:esc", "wrapper" => "li.cancel")) ?>
			<?= $model->submit("Add order", array("class" => "primary key:s", "wrapper" => "li.save")) ?>
		</ul>
		<? endif; ?>
	<?= $model->formEnd() ?>

</div>