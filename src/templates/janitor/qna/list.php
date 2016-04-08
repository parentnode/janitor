<?php
global $action;
global $IC;
global $model;
global $itemtype;

$items = $IC->getItems(array("itemtype" => $itemtype, "order" => "items.status DESC, qna.answer ASC", "extend" => array("tags" => true)));
?>
<div class="scene i:scene defaultList <?= $itemtype ?>List">
	<h1>Questions and Answers</h1>

	<ul class="actions">
		<?= $JML->listNew(array("label" => "New question")) ?>
	</ul>

	<div class="all_items i:defaultList taggable filters"<?= $JML->jsData() ?>>
<?		if($items): ?>
		<ul class="items">
<?			foreach($items as $item): ?>
			<li class="item item_id:<?= $item["id"] ?>">
				<h3><?= $item["name"] ?></h3>
<?				if(!$item["answer"]): ?>
				<p><?= "Not answered yet" ?></p>
<?				endif; ?>

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
