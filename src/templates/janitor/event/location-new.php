<?php
global $action;
global $model;


$country_options = $model->toOptions($this->countries(), "id", "name");

?>

<div class="scene i:scene defaultNew">
	<h1>New location</h1>

	<ul class="actions">
		<?= $JML->newList(array("label" => "List", "action" => "locations")) ?>
	</ul>

	<?= $model->formStart("addLocation", array("class" => "i:defaultNew labelstyle:inject")) ?>
		<fieldset>
			<?= $model->input("location") ?>
			<?= $model->input("location_address1") ?>
			<?= $model->input("location_address2") ?>
			<?= $model->input("location_city") ?>
			<?= $model->input("location_postal") ?>
			<?= $model->input("location_country", array("type" => "select", "options" => $country_options)) ?>
			<?= $model->input("location_googlemaps") ?>
			<?= $model->input("location_comment", array("class" => "autoexpand short")) ?>
		</fieldset>

		<?= $JML->newActions(array(
			"modify" => array(
				"cancel" => array(
					"url" => $JML->path . "/locations"
				)
			)
		)) ?>
	<?= $model->formEnd() ?>

</div>