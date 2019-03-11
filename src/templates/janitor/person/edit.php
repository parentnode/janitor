<?php
global $action;
global $IC;
global $model;
global $itemtype;

$item_id = $action[1];
$item = $IC->getItem(array("id" => $item_id, "extend" => array("tags" => true, "mediae" => true)));
?>
<div class="scene i:scene defaultEdit <?= $itemtype ?>Edit">
	<h1>Edit Person</h1>
	<h2><?= $item["name"] ?></h2>

	<?= $JML->editGlobalActions($item) ?>

	<?= $JML->editSingleMedia($item) ?>


	<div class="item i:defaultEdit">
		<h2>Person description</h2>
		<?= $model->formStart("update/".$item["item_id"], array("class" => "labelstyle:inject")) ?>

			<fieldset>
				<?= $model->input("name", array("value" => $item["name"])) ?>
				<?= $model->input("description", array("class" => "autoexpand short", "value" => $item["description"])) ?>

				<?= $model->inputHTML("html", array("value" => $item["html"])) ?>
 
				<?= $model->input("job_title", array("value" => $item["job_title"])) ?>
				<?= $model->input("email", array("value" => $item["email"])) ?>
				<?= $model->input("tel", array("value" => $item["tel"])) ?>
			</fieldset>

			<?= $JML->editActions($item) ?>

		<?= $model->formEnd() ?>
	</div>


	<?= $JML->editTags($item) ?>

</div>
