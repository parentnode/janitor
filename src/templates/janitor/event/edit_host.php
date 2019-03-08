<?php
global $action;
global $model;

$host_id = $action[1];
$host = $model->getHosts(array("id" => $host_id));

$country_options = $model->toOptions($this->countries(), "id", "name");

?>

<div class="scene i:scene defaultEdit eventHostEdit">
	<h1>Edit host</h1>
	<h2><?= $host["host"] ?></h2>

	<?= $JML->editGlobalActions($host, array("modify" => array(
		"status" => false,
		"list" => [
			"url" => $JML->path."/hosts"
		],
		"delete" => [
			"url" => $JML->path."/deleteHost/".$host_id
		]
	))); ?>

	<div class="item i:defaultEdit">
		<h2>Host</h2>
		<?= $model->formStart("updateHost/".$host_id, array("class" => "labelstyle:inject")) ?>
			<fieldset>
				<?= $model->input("host", array("value" => $host["host"] )) ?>
				<?= $model->input("host_address1", array("value" => $host["host_address1"])) ?>
				<?= $model->input("host_address2", array("value" => $host["host_address2"])) ?>
				<?= $model->input("host_city", array("value" => $host["host_city"])) ?>
				<?= $model->input("host_postal", array("value" => $host["host_postal"])) ?>
				<?= $model->input("host_country", array("type" => "select", "value" => $host["host_country"], "options" => $country_options)) ?>
				<?= $model->input("host_googlemaps", array("value" => $host["host_googlemaps"])) ?>
				<?= $model->input("host_comment", array("class" => "autoexpand short", "value" => $host["host_comment"])) ?>
			</fieldset>

			<?= $JML->editActions($host) ?>
		<?= $model->formEnd() ?>
	</div>

</div>