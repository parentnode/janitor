<?php
global $action;
global $IC;
global $model;
global $itemtype;
?>
<div class="scene defaultNew">
	<h1>New page</h1>

	<ul class="actions">
		<?= $model->link("List", "/janitor/".$itemtype."/list", array("class" => "button", "wrapper" => "li.cancel")) ?>
	</ul>

	<?= $model->formStart("/janitor/admin/items/save/".$itemtype, array("class" => "i:defaultNew labelstyle:inject")) ?>
		<fieldset>
			<?= $model->input("name") ?>
			<?= $model->input("published_at", array("value" => date("Y-m-d H:i", time()))) ?>
			<?= $model->input("description", array("class" => "autoexpand short")) ?>
			<?= $model->input("html", array("class" => "autoexpand short")) ?>
		</fieldset>

		<ul class="actions">
			<?= $model->link("Cancel", "/janitor/".$itemtype."/list", array("class" => "button key:esc", "wrapper" => "li.cancel")) ?>
			<?= $model->submit("Save", array("class" => "primary key:s", "wrapper" => "li.save")) ?>
		</ul>
	<?= $model->formEnd() ?>

</div>
