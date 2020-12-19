<?php
global $action;
global $IC;
global $model;
global $itemtype;



$items = $IC->getItems(array("itemtype" => $itemtype, "order" => "position ASC, status DESC", "extend" => array("tags" => true, "mediae" => true, "prices" => true, "subscription_method" => true)));

?>

<div class="scene i:scene defaultList <?= $itemtype ?>List">
	<h1>Donations</h1>

	<ul class="actions">
		<?= $JML->listNew(array("label" => "New donation")) ?>
	</ul>

	<div class="all_items i:defaultList sortable filters"<?= $HTML->jsData(["order", "search"]) ?>>
<?		if($items): ?>
		<ul class="items">
<?			foreach($items as $item): ?>
			<li class="item item_id:<?= $item["id"] ?>">
				<h3><?= strip_tags($item["name"]) ?></h3>

				<?= $JML->listActions($item) ?>
			 </li>
<?			endforeach; ?>
		</ul>
<?		else: ?>
		<p>No donations.</p>
<?		endif; ?>
	</div>

</div>
