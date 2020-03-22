<?php
global $action;
global $IC;
global $model;
global $itemtype;

$item_id = $action[1];
$item = $IC->getItem(array("id" => $item_id, "extend" => array("tags" => true, "mediae" => true, "comments" => true, "subscription_method" => true)));
?>
<div class="scene i:scene defaultEdit <?= $itemtype ?>Edit">
	<h1>Edit blog</h1>
	<h2><?= strip_tags($item["name"]) ?></h2>

	<?= $JML->editGlobalActions($item) ?>

	<?= $JML->editSingleMedia($item, array("label" => "Main blog image")) ?>


	<div class="item i:defaultEdit">
		<h2>Blog details</h2>
		<?= $model->formStart("update/".$item_id, array("class" => "labelstyle:inject")) ?>

			<fieldset>
				<?= $model->input("name", array("value" => $item["name"])) ?>
				<?= $model->input("description", array("class" => "autoexpand short", "value" => $item["description"])) ?>

				<?= $model->input("author", array("value" => $item["author"])) ?>
				<?= $model->input("title", array("value" => $item["title"])) ?>
				<?= $model->input("bio", array("value" => $item["bio"])) ?>

				<?= $model->input("html", array("value" => $item["html"])) ?>
			</fieldset>

			<?= $JML->editActions($item) ?>

		<?= $model->formEnd() ?>
	</div>


	<?= $JML->editTags($item, ["context" => "post,blog"]) ?>

	<?= $JML->editSindex($item) ?>

	<?= $JML->editOwner($item) ?>

</div>
