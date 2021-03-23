<?php
global $model;
global $upgrade_model;

	
?>
<div class="scene i:scene">
	<h1>Bulk item removal</h1>

	<p>This can remove excess items from the database and will also remove any associated files. It will pick random items to keep.</p>
	<p>Due to performance issues, only 5000 items can be deleted at the time.</p>
	<h3>Removal criterias</h3>

	<?= $model->formStart("/janitor/admin/setup/upgrade/bulkItemRemoval", array("class" => "labelstyle:inject i:bulkremove")) ?>
		<fieldset>
			<?= $model->input("itemtype", array("label" => "Itemtype", "type" => "string", "hint_message" => "Which itemtype do you want to delete. Leave empty for any itemtype.")) ?>
			<?= $model->input("keep", array("label" => "Keep at least", "type" => "integer", "hint_message" => "If you want to keep some of these items, state how many you want to keep.")) ?>
			<?= $model->input("real", array("label" => "Real delete", "type" => "checkbox", "hint_message" => "Check this when you are sure you're deleting the expected items.")) ?>
		</fieldset>
		<ul class="actions">
			<?= $model->submit("Execute", array("class" => "primary", "wrapper" => "li.execute")) ?>
		</ul>
	<?= $model->formEnd() ?>

</div>