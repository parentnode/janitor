<?php
global $action;
global $model;

$user_id = $action[1];
$address_id = $action[2];

$item = $model->getUsers(array("user_id" => $user_id));

$address = $model->getAddresses(array("address_id" => $address_id));

$country_options = $model->toOptions($this->countries(), "id", "name");
?>

<div class="scene defaultEdit userEdit">
	<h1>Edit Address</h1>

	<ul class="actions i:defaultEditActions item_id:<?= $address_id ?>"
		data-csrf-token="<?= session()->value("csrf") ?>"
		>
		<?= $model->link("Back to user", "/janitor/admin/user/edit/".$user_id, array("class" => "button", "wrapper" => "li.cancel")) ?>
		<?= $JML->deleteButton("Delete address", "/janitor/admin/user/deleteAddress/".$address_id) ?>
	</ul>

	<div class="addresses">
		<h2>Address</h2>
		<?= $model->formStart("/janitor/admin/user/updateAddress/".$address_id, array("class" => "i:defaultNew labelstyle:inject")) ?>
			<fieldset>
				<?= $model->input("address_label", array("value" => $address["address_label"] )) ?>
				<?= $model->input("address_name", array("value" => $address["address_name"] )) ?>
				<?= $model->input("att", array("value" => $address["att"] )) ?>
				<?= $model->input("address1", array("value" => $address["address1"])) ?>
				<?= $model->input("address2", array("value" => $address["address2"])) ?>
				<?= $model->input("city", array("value" => $address["city"])) ?>
				<?= $model->input("postal", array("value" => $address["postal"])) ?>
				<?= $model->input("state", array("value" => $address["state"])) ?>
				<?= $model->input("country", array("type" => "select", "value" => $address["country"], "options" => $country_options)) ?>
			</fieldset>

			<ul class="actions">
				<?= $model->link("Back", "/janitor/admin/user/edit/".$user_id, array("class" => "button key:esc", "wrapper" => "li.cancel")) ?>
				<?= $model->submit("Update address", array("class" => "primary key:s", "wrapper" => "li.save")) ?>
			</ul>
		<?= $model->formEnd() ?>
	</div>

</div>