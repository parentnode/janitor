<?php
global $action;
global $IC;
global $model;
global $itemtype;

$item_id = $action[1];
$item = $IC->getItem(array("id" => $item_id, "extend" => array("tags" => true, "mediae" => true, "comments" => true, "subscription_method" => true)));

$messages = $IC->getItems(array("itemtype" => "message", "extend" => true));
?>
<div class="scene i:scene defaultEdit <?= $itemtype ?>Edit">
	<h1>Edit membership type</h1>
	<h2><?= strip_tags($item["name"]) ?></h2>

	<?= $JML->editGlobalActions($item) ?>

	<?= $JML->editSingleMedia($item) ?>

	<div class="item i:defaultEdit">
		<h2>Membership details</h2>
		<?= $model->formStart("update/".$item["id"], array("class" => "labelstyle:inject")) ?>

			<fieldset>
				<?= $model->input("name", array("value" => $item["name"])) ?>
				<?= $model->input("classname", array("value" => $item["classname"])) ?>
				<?= $model->input("subscribed_message_id", array("type" => "select", "options" => $HTML->toOptions($messages, "id", "name", ["add" => ["" => "Choose message"]]), "value" => $item["subscribed_message_id"])) ?>
				<?= $model->input("description", array("class" => "autoexpand short", "value" => $item["description"])) ?>
				<?= $model->inputHTML("introduction", array("value" => $item["introduction"])) ?>
				<?= $model->inputHTML("html", array("value" => $item["html"])) ?>
			</fieldset>

			<?= $JML->editActions($item) ?>

		<?= $model->formEnd() ?>
	</div>

	<?= $JML->editPrices($item) ?>

	<?= $JML->editTags($item) ?>

	<?= $JML->editComments($item) ?>

	<?= $JML->editSubscriptionMethod($item) ?>

</div>
