<?php
global $action;
global $IC;
global $model;
global $itemtype;

$item_id = $action[1];
$item = $IC->getItem(array("id" => $item_id, "extend" => array("tags" => true, "user" => true, "comments" => true)));
?>
<div class="scene i:scene defaultEdit <?= $itemtype ?>Edit">
	<h1>Edit Answer</h1>
	<h2><?= $item["name"] ?></h2>

	<?= $JML->editGlobalActions($item) ?>

	<div class="item i:defaultEdit">
		<h2>Question and Answer</h2>
		<p>Question was asked by: <?= $item["user_nickname"] ?>
			<? if($item["about_item_id"]):
				$related_item = $IC->getItem(array("id" => $item["about_item_id"], "extend" => true)); ?>
				<br />Asked about: <?= strip_tags($related_item["name"]) ?>
			<? endif; ?>
		</p>
		
		<?= $model->formStart("update/".$item["id"], array("class" => "labelstyle:inject")) ?>

			<fieldset>
				<?= $model->input("question", array("value" => $item["question"])) ?>
				<?= $model->input("answer", array("class" => "autoexpand", "value" => $item["answer"])) ?>
			</fieldset>

			<?= $JML->editActions($item) ?>

		<?= $model->formEnd() ?>
	</div>

	<?= $JML->editComments($item) ?>

	<?= $JML->editTags($item) ?>

</div>
