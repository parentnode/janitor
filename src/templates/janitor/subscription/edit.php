<?php
global $action;
global $IC;
global $model;
global $itemtype;

$item_id = $action[1];
$item = $IC->getItem(array("id" => $item_id, "extend" => array("tags" => true, "mediae" => true, "prices" => true)));

?>
<div class="scene i:scene defaultEdit <?= $itemtype ?>Edit">
	<h1>Edit subscription</h1>
	<h2><?= $item["name"] ?></h2>

	<?= $JML->editGlobalActions($item) ?>

	<?= $JML->editSingleMedia($item) ?>

	<div class="item i:defaultEdit">
		<h2>Subscription details</h2>
		<?= $model->formStart("update/".$item["id"], array("class" => "labelstyle:inject")) ?>

			<fieldset>
				<?= $model->input("name", array("value" => $item["name"])) ?>
				<?= $model->input("classname", array("value" => $item["classname"])) ?>
				<?= $model->input("description", array("class" => "autoexpand short", "value" => $item["description"])) ?>
				<?= $model->input("renewal", array("value" => $item["renewal"])) ?>
				<?= $model->inputHTML("html", array("value" => $item["html"])) ?>
			</fieldset>

			<?= $JML->editActions($item) ?>

		<?= $model->formEnd() ?>
	</div>

	<?= $JML->editPrices($item) ?>

	<?= $JML->editTags($item) ?>

</div>
