<?php
global $action;
global $model;

$user_id = $action[2];
$address_id = $action[3];

$address = $model->getAddresses(array("address_id" => $address_id));

$country_options = $model->toOptions($this->countries(), "id", "name");

//$item = $model->getUsers(array("user_id" => $user_id));

?>

<div class="scene i:scene defaultEdit userEdit">
	<h1>Edit Address</h1>
	<h2><?= $address["address_label"] ? $address["address_label"] : $address["address1"] ?></h2>

	<ul class="actions i:defaultEditActions">
		<?= $model->link("Back to user", "/janitor/admin/user/edit/".$user_id, array("class" => "button", "wrapper" => "li.cancel")) ?>
		<?= $model->oneButtonForm("Delete address", "/janitor/admin/user/deleteAddress/".$address_id, array(
			"wrapper" => "li.delete",
			"success-location" => "/janitor/admin/user/edit/".$user_id
		)) ?>
	</ul>

	<div class="addresses">
		<h2>Address</h2>
		<?= $model->formStart("/janitor/admin/user/updateAddress/".$address_id, array("class" => "i:editAddress labelstyle:inject")) ?>
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