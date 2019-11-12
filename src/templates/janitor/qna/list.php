<?php
global $action;
global $IC;
global $model;
global $itemtype;

$items = $IC->getItems(array("itemtype" => $itemtype, "order" => "position ASC", "extend" => array("tags" => true)));
?>
<div class="scene i:scene defaultList <?= $itemtype ?>List">
	<h1>Questions and Answers</h1>

	<ul class="actions">
		<?= $JML->listNew(array("label" => "New question")) ?>
	</ul>

	<div class="all_items i:defaultList taggable sortable filters"<?= $JML->jsData(["order", "tags", "search"]) ?>>
<?		if($items): ?>
		<ul class="items">
<?			foreach($items as $item): ?>
			<li class="item item_id:<?= $item["id"] ?>">
				<h3><?= strip_tags($item["name"]) ?> <?= (!$item["answer"] ? "<span>(Not answered)</span>" : "") ?></h3>
				<? if($item["about_item_id"]):
	 				$related_item = $IC->getItem(array("id" => $item["about_item_id"], "extend" => true)); ?>
				<dl class="info">
					<dt>Asked about</dt>
					<dd><?= $related_item["name"] ?> (<?= $related_item["itemtype"] ?>)</dd>
				</dl>
				<? endif; ?>

				<?= $JML->tagList($item["tags"]) ?>

				<?= $JML->listActions($item) ?>
			 </li>
<?			endforeach; ?>
		</ul>
<?		else: ?>
		<p>No questions.</p>
<?		endif; ?>
	</div>

</div>
