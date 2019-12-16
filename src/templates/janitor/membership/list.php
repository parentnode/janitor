<?php
global $action;
global $IC;
global $model;
global $itemtype;


$items = $IC->getItems(array("itemtype" => $itemtype, "order" => "position ASC, status DESC", "extend" => array("tags" => true, "mediae" => true)));

?>
<div class="scene i:scene defaultList <?= $itemtype ?>List">
	<h1>Membership types</h1>

	<ul class="actions">
		<?= $JML->listNew(array("label" => "New membership type")) ?>
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
		<p>No membership types.</p>
<?		endif; ?>
	</div>

</div>
