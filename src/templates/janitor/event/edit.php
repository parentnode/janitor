<?php
global $action;
global $IC;
global $model;
global $itemtype;

$item_id = $action[1];
$item = $IC->getItem(array("id" => $item_id, "extend" => array("tags" => true, "mediae" => true)));

// $location_options = $model->toOptions($model->getLocations(), "id", "location");
$location_options = $model->toOptions($model->getLocations(), "id", "location", ["add" => ["" => "Select event location"]]);

?>
<div class="scene i:scene defaultEdit <?= $itemtype ?>Edit">
	<h1>Edit event</h1>
	<h2><?= strip_tags($item["name"]) ?></h2>

	<?= $JML->editGlobalActions($item) ?>

	<?= $JML->editSingleMedia($item) ?>

	<div class="item i:defaultEdit">
		<h2>Event</h2>
		<?= $model->formStart("update/".$item["id"], array("class" => "labelstyle:inject")) ?>

			<fieldset>
				<?= $model->input("name", array("value" => $item["name"])) ?>
				<?= $model->input("classname", array("value" => $item["classname"])) ?>
				<?= $model->input("description", array("class" => "autoexpand short", "value" => $item["description"])) ?>
			</fieldset>

			<fieldset>
				<h2>Time</h2>
				<?= $model->input("starting_at", array("value" => $item["starting_at"])) ?>
				<?= $model->input("ending_at", array("value" => $item["ending_at"])) ?>
			</fieldset>

			<fieldset>
				<h2>Location</h2>
				<?= $model->input("location", array("type" => "select", "options" => $location_options, "value" => $item["location"])) ?>
			</fieldset>

			<fieldset>
				<?= $model->input("html", array("value" => $item["html"])) ?>
			</fieldset>

			<?= $JML->editActions($item) ?>

		<?= $model->formEnd() ?>
	</div>

	<?= $JML->editPrices($item) ?>

	<?= $JML->editTags($item) ?>

</div>
