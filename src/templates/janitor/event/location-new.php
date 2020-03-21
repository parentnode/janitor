<?php
global $action;
global $model;


$country_options = $model->toOptions($this->countries(), "id", "name");

?>

<div class="scene i:scene eventLocationNew defaultNew">
	<h1>New location</h1>

	<ul class="actions">
		<?= $JML->newList(array("label" => "List", "action" => "locations")) ?>
	</ul>

	<?= $model->formStart("addLocation", array("class" => "i:defaultNew labelstyle:inject")) ?>
		<fieldset>
			<h3>Name and type</h3>
			<?= $model->input("location") ?>
			<?= $model->input("location_type") ?>
		</fieldset>

		<fieldset class="online">
			<h3>Online details</h3>
			<?= $model->input("location_url") ?>
		</fieldset>

		<fieldset class="offline">
			<h3>Physical details</h3>
			<?= $model->input("location_address1") ?>
			<?= $model->input("location_address2") ?>
			<?= $model->input("location_city") ?>
			<?= $model->input("location_postal") ?>
			<?= $model->input("location_country", array("type" => "select", "options" => $country_options)) ?>
			<?= $model->input("location_googlemaps") ?>
		</fieldset>

		<fieldset class="comment">
			<h3>Comment</h3>
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