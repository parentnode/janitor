<?php
global $action;
global $IC;
global $model;
global $itemtype;

$items = $IC->getItems(array("order" => "itemtype", "extend" => array("subscription_method" => true, "prices" => true)));
$item_options = $model->toOptions($items, "id", "name", ["add" => ["" => "Select an item"]]);

?>
<div class="scene i:scene defaultNew">
	<h1>New question</h1>

	<ul class="actions">
		<?= $JML->newList(array("label" => "List")) ?>
	</ul>

	<?= $model->formStart("save", array("class" => "i:defaultNew labelstyle:inject")) ?>
		<?= $model->input("status", array("type" => "hidden", "value" => 1)) ?>
		<?= $model->input("name", array("type" => "hidden", "value" => time())) ?>
		<fieldset>
			<?= $model->input("about_item_id", array(
				"type" => "select",
				"options" => $item_options,
			)) ?>
			<?= $model->input("question") ?>
		</fieldset>

		<?= $JML->newActions() ?>
	<?= $model->formEnd() ?>

</div>
