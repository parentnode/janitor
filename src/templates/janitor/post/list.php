<?php
global $action;
global $IC;
global $model;
global $itemtype;

$items = $IC->getItems(array("itemtype" => $itemtype, "order" => "status DESC, published_at DESC", "extend" => array("tags" => true, "mediae" => true)));

?>

<div class="scene i:scene defaultList <?= $itemtype ?>List">
	<h1>Posts</h1>

	<ul class="actions">
		<?= $JML->listNew(array("label" => "New post")) ?>
	</ul>

	<div class="all_items i:defaultList taggable filters images width:100"<?= $JML->jsData() ?>>
<?		if($items): ?>
		<ul class="items">
<?			foreach($items as $item): ?>
			<li class="item item_id:<?= $item["id"] ?><?= $JML->jsMedia($item) ?>">
				<h3><?= preg_replace("/<br>|<br \/>/", "", $item["name"]) ?></h3>

				<?= $JML->tagList($item["tags"]) ?>

				<?= $JML->listActions($item) ?>
			 </li>
<?			endforeach; ?>
		</ul>
<?		else: ?>
		<p>No posts.</p>
<?		endif; ?>
	</div>

</div>
