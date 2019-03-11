<?php
global $action;
global $model;
$IC = new Items();


include_once("classes/users/superuser.class.php");
$UC = new SuperUser();

$users = $UC->getUsers();
$user_options = $model->toOptions($users, "id", "nickname", array("add" => array("" => "Select user")));

?>
<div class="scene i:scene defaultNew newOrder">
	<h1>New cart</h1>

	<ul class="actions">
		<?= $HTML->link("Back to carts", "/janitor/admin/shop/cart/list", array("class" => "button", "wrapper" => "li.cancel")) ?>
	</ul>

	<?= $model->formStart("/janitor/admin/shop/addCart", array("class" => "i:defaultNew labelstyle:inject")) ?>
		<?= $model->input("return_to", ["type" => "hidden", "value" => "/janitor/admin/shop/cart/edit/"])?>
		<fieldset>
			<?= $model->input("user_id", array(
				"type" => "select",
				"options" => $user_options,
				"hint_message" => "Select user for this cart"
			)) ?>

			<?= $model->input("country", array(
				"type" => "select",
				"options" => $model->toOptions($this->countries(), "id", "name"),
			)) ?>
			<?= $model->input("currency", array(
				"type" => "select",
				"options" => $model->toOptions($this->currencies(), "id", "name"),
			)) ?>
		</fieldset>

		<ul class="actions">
			<?= $model->link("Back", "/janitor/admin/shop/cart/list", array("class" => "button key:esc", "wrapper" => "li.cancel")) ?>
			<?= $model->submit("Add cart", array("class" => "primary key:s", "wrapper" => "li.save")) ?>
		</ul>
	<?= $model->formEnd() ?>

</div>