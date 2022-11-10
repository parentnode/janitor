<?php
global $action;
global $IC;
global $model;
global $itemtype;

$items = $IC->getItems(array("itemtype" => $itemtype, "order" => "position ASC", "extend" => array("tags" => true, "mediae" => true)));
?>
<div class="scene i:scene defaultList <?= $itemtype ?>List">
	<h1>Blogs</h1>

	<ul class="actions">
		<?= $JML->listNew(array("label" => "New blog")) ?>
	</ul>

	<div class="all_items i:defaultList taggable filters sortable images width:100"<?= $HTML->jsData(["tags", "order", "search"], ["filter-tag-contexts" => "post,blog,on"]) ?>>
<?		if($items): ?>
		<ul class="items">
<?			foreach($items as $item): ?>
			<li class="item item_id:<?= $item["id"] ?><?= $HTML->jsMedia($item) ?>">
				<h3><?= strip_tags($item["name"]) ?></h3>
				<dl class="info">
					<dt class="author">Author</dt>
					<dd class="author"><?= $item["author"] ?></dd>
				</dl>

				<?= $JML->tagList($item["tags"]) ?>

				<?= $JML->listActions($item) ?>
			 </li>
<?			endforeach; ?>
		</ul>
<?		else: ?>
		<p>No content.</p>
<?		endif; ?>
	</div>

</div>
