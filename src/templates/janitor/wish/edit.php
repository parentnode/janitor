<?php
global $action;
global $IC;
global $model;
global $itemtype;

$item_id = $action[1];
$item = $IC->getItem(array("id" => $item_id, "extend" => array("tags" => true, "mediae" => true)));

$return_to_wishlist = session()->value("return_to_wishlist");
?>
<div class="scene i:scene defaultEdit <?= $itemtype ?>Edit">
	<h1>Edit wish</h1>
	<h2><?= strip_tags($item["name"]) ?></h2>

	<?
	// return to specific wishlist
	if($return_to_wishlist):
		print $JML->editGlobalActions($item, array("modify" => array(
			"list" => [
				"label" => "Back to wishlist", 
				"url" => "/janitor/admin/wishlist/edit/".$return_to_wishlist
			])));
	// standard back button
	else:
		print $JML->editGlobalActions($item);
	endif;
	?>

	<div class="item i:defaultEdit">
		<h2>Wish</h2>
		<?= $model->formStart("update/".$item["id"], array("class" => "labelstyle:inject")) ?>

			<fieldset>
				<?= $model->input("name", array("value" => $item["name"])) ?>
				<?= $model->input("price", array("value" => $item["price"])) ?>
				<?= $model->input("link", array("value" => $item["link"])) ?>
				<?= $model->input("description", array("class" => "autoexpand", "value" => $item["description"])) ?>
				<?= $model->input("reserved", array("value" => $item["reserved"])) ?>
			</fieldset>

			<?= $JML->editActions($item) ?>

		<?= $model->formEnd() ?>
	</div>


	<?= $JML->editTags($item) ?>

	<?= $JML->editMedia($item) ?>

</div>
