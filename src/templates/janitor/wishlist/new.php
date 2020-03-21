<?php
global $action;
global $IC;
global $model;
global $itemtype;
?>
<div class="scene i:scene defaultNew">
	<h1>New wishlist</h1>

	<ul class="actions">
		<?= $model->link("Back to overview", "/janitor/admin/wish/list", array("class" => "button primary key:esc", "wrapper" => "li.back")); ?>
	</ul>

	<?= $model->formStart("save", array("class" => "i:defaultNew labelstyle:inject")) ?>
		<fieldset>
			<?= $model->input("name") ?>
		</fieldset>

		<?= $JML->newActions(
		array("modify" => array(
			"cancel" => array(
				"url" => "/janitor/admin/wish/list",
			)
		))) ?>

	<?= $model->formEnd() ?>

</div>
