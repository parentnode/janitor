<?php
global $action;
global $IC;
global $model;
global $itemtype;


$items = $IC->getItems(array("itemtype" => $itemtype, "order" => "status DESC", "extend" => array("tags" => true, "mediae" => true)));

?>
<div class="scene i:scene defaultList <?= $itemtype ?>List">
	<h1>Subscriptions</h1>

	<ul class="actions">
		<?= $JML->listNew(array("label" => "New subscription")) ?>
	</ul>

	<div class="all_items i:defaultList taggable filters"<?= $JML->jsData() ?>>
<?		if($items): ?>
		<ul class="items">

<?			foreach($items as $item): ?>
			<li class="item item_id:<?= $item["id"] ?>">
				<h3><?= preg_replace("/<br>|<br \/>/", "", $item["name"]) ?></h3>

				<?= $JML->listActions($item) ?>
			 </li>
<?			endforeach; ?>

		</ul>
<?		else: ?>
		<p>No subscriptions.</p>
<?		endif; ?>
	</div>

</div>
