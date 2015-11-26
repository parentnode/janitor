<?php
global $action;
global $IC;
global $model;
global $itemtype;

$items = $IC->getItems(array("itemtype" => $itemtype, "order" => "status DESC, wish.name ASC", "extend" => array("tags" => true, "mediae" => true)));
?>
<div class="scene defaultList <?= $itemtype ?>List">
	<h1>Wishes</h1>

	<ul class="actions">
		<?= $JML->listNew(array("label" => "New wish")) ?>
		<?= $HTML->link("Wishlists", "/janitor/wishlist/list", array("class" => "button", "wrapper" => "li.wishlist")) ?>
	</ul>

	<div class="all_items i:defaultList taggable filters"<?= $JML->jsData() ?>>
<?		if($items): ?>
		<ul class="items">
<?			foreach($items as $item): ?>
			<li class="item image item_id:<?= $item["id"] ?> width:160<?= $JML->jsMedia($item) ?>">
				<h3><?= $item["name"] ?></h3>
				<dl>
					<dt class="reserved">Reserved</dt>
					<dd class="reserved"><?= $model->wish_reserved[$item["reserved"]] ?></dd>
				</dl>


				<?= $JML->tagList($item["tags"]) ?>

				<?= $JML->listActions($item) ?>
			 </li>
<?			endforeach; ?>
		</ul>
<?		else: ?>
		<p>No wishes.</p>
<?		endif; ?>
	</div>

</div>
