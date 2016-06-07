<?php
global $action;
global $model;


$country_options = $model->toOptions($this->countries(), "id", "name");

?>

<div class="scene i:scene defaultNew">
	<h1>New host</h1>

	<ul class="actions">
		<?= $JML->newList(array("label" => "List", "action" => "hosts")) ?>
	</ul>

	<?= $model->formStart("addHost", array("class" => "i:defaultNew labelstyle:inject")) ?>
		<fieldset>
			<?= $model->input("host") ?>
			<?= $model->input("host_address1") ?>
			<?= $model->input("host_address2") ?>
			<?= $model->input("host_city") ?>
			<?= $model->input("host_postal") ?>
			<?= $model->input("host_country", array("type" => "select", "options" => $country_options)) ?>
			<?= $model->input("host_googlemaps") ?>
			<?= $model->input("host_comment", array("class" => "autoexpand short")) ?>
		</fieldset>

		<?= $JML->newActions(array(
			"modify" => array(
				"cancel" => array(
					"url" => $JML->path . "/hosts"
				)
			)
		)) ?>
	<?= $model->formEnd() ?>

</div>