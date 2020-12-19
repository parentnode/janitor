<?php
global $action;
global $IC;
global $model;
global $itemtype;

$item_id = $action[1];
$item = $IC->getItem(array("id" => $item_id, "extend" => array("tags" => true, "mediae" => true, "prices" => true, "subscription_method" => true, "comments" => true)));

$messages = $IC->getItems(array("itemtype" => "message", "tags" => "message:Donation", "extend" => true));

?>
<div class="scene i:scene defaultEdit <?= $itemtype ?>Edit">
	<h1>Edit donation</h1>
	<h2><?= strip_tags($item["name"]) ?></h2>

	<?= $JML->editGlobalActions($item) ?>

	<?= $JML->editSingleMedia($item, array("label" => "Main donation image")) ?>

	<div class="item i:defaultEdit i:collapseHeader">
		<h2>Donation details</h2>
		<?= $model->formStart("update/".$item["id"], array("class" => "labelstyle:inject")) ?>

			<fieldset>
				<?= $model->input("name", array("value" => $item["name"])) ?>
				<?= $model->input("description", array("class" => "autoexpand short", "value" => $item["description"])) ?>
				<?= $model->input("html", array("value" => $item["html"])) ?>
			</fieldset>

			<fieldset>
				<h3>Mail to send when ordered</h3>
				<?= $model->input("ordered_message_id", array("type" => "select", "options" => $HTML->toOptions($messages, "id", "name", ["add" => ["" => "Choose message"]]), "value" => $item["ordered_message_id"])) ?>
			</fieldset>

			<?= $JML->editActions($item) ?>

		<?= $model->formEnd() ?>
	</div>

	<?= $JML->editPrices($item) ?>

	<?= $JML->editTags($item) ?>

	<?= $JML->editComments($item) ?>

	<?= $JML->editSindex($item) ?>

	<?= $JML->editSubscriptionMethod($item) ?>

	<?= $JML->editDeveloperSettings($item) ?>

	<?= $JML->editOwner($item) ?>

</div>
