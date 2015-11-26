<?php
global $action;
global $IC;
global $model;
global $itemtype;

$items = $IC->getItems(array("itemtype" => $itemtype, "order" => "position ASC", "extend" => array("tags" => true)));
?>
<div class="scene defaultList <?= $itemtype ?>List">
	<h1>Wishlists</h1>

	<ul class="actions">
		<?= $JML->listNew(array("label" => "New wishlist")) ?>
		<?= $HTML->link("Wishes", "/janitor/wish/list", array("class" => "button", "wrapper" => "li.wish")) ?>
	</ul>

	<div class="all_items i:defaultList taggable filters sortable"<?= $JML->jsData() ?>>
<?		if($items): ?>
		<ul class="items">
<?			foreach($items as $item): ?>
			<li class="item draggable item_id:<?= $item["item_id"] ?>">
				<div class="drag"></div>
				<h3><?= $item["name"] ?></h3>

				<?= $JML->tagList($item["tags"]) ?>

				<?= $JML->listActions($item) ?>
			 </li>
<?			endforeach; ?>
		</ul>
<?		else: ?>
		<p>No wishlists.</p>
<?		endif; ?>
	</div>

</div>