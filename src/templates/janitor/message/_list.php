<?php
global $action;
global $IC;
global $model;
global $itemtype;

$items = $IC->getItems(array("itemtype" => $itemtype, "order" => "status DESC, published_at DESC", "extend" => array("tags" => true)));

?>

<div class="scene i:scene defaultList <?= $itemtype ?>List">
	<h1>Messages</h1>

	<ul class="actions">
		<?= $JML->listNew(array("label" => "New message")) ?>
	</ul>

	<div class="all_items i:defaultList taggable filters"<?= $JML->jsData() ?>>
<?		if($items): ?>
		<ul class="items">
<?			foreach($items as $item): ?>
			<li class="item item_id:<?= $item["id"] ?><?= $JML->jsMedia($item) ?>">
				<h3><?= strip_tags($item["name"]) ?></h3>

				<?= $JML->tagList($item["tags"]) ?>

				<?= $JML->listActions($item) ?>
			 </li>
<?			endforeach; ?>
		</ul>
<?		else: ?>
		<p>No messages.</p>
<?		endif; ?>
	</div>

</div>
