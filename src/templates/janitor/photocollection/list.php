<?php
global $action;
global $IC;
global $model;
global $itemtype;

$items = $IC->getItems(array("itemtype" => $itemtype, "order" => "position ASC", "extend" => true));
?>
<div class="scene i:scene defaultList <?= $itemtype ?>List">
	<h1>Photo collections</h1>

	<ul class="actions">
		<?= $JML->listNew(array("label" => "New collection")) ?>
	</ul>

	<div class="all_items i:defaultList sortable filters"<?= $HTML->jsData(["order", "search"]) ?>>
<?		if($items): ?>
		<ul class="items">
<?			foreach($items as $item): ?>
			<li class="item item_id:<?= $item["id"] ?>">
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
