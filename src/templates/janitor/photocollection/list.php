<?php
global $action;
global $IC;
global $model;
global $itemtype;

$items = $IC->getItems(array("itemtype" => $itemtype, "order" => "position ASC", "extend" => array("all" => true)));
?>
<div class="scene defaultList <?= $itemtype ?>List">
	<h1>Photo collections</h1>

	<ul class="actions">
		<?= $JML->listNew(array("label" => "New collection")) ?>
	</ul>

	<div class="all_items i:defaultList sortable filters"<?= $JML->jsData() ?>>
<?		if($items): ?>
		<ul class="items">
<?			foreach($items as $item): ?>
			<li class="item draggable item_id:<?= $item["id"] ?>">
				<div class="drag"></div>
				<h3><?= $item["name"] ?></h3>

				<?= $JML->listActions($item) ?>
			 </li>
<?			endforeach; ?>
		</ul>
<?		else: ?>
		<p>No collections.</p>
<?		endif; ?>
	</div>

</div>
