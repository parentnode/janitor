<?php
global $action;
global $model;

?>

	<div class="scene defaultNew">
		<h1>New navigation</h1>

		<ul class="actions">
			<?= $model->link("List", "/admin/navigation/list", array("class" => "button", "wrapper" => "li.cancel")) ?>
		</ul>

		<?= $model->formStart("/admin/navigation/save", array("class" => "i:defaultNew labelstyle:inject")) ?>
			<fieldset>
				<h2>Create a new navigation</h2>
				<p>A navigation is a structured link list, which can be used in your templates.</p>

				<?= $model->input("name") ?>
			</fieldset>

			<ul class="actions">
				<?= $model->link("Cancel", "/admin/navigation/list", array("class" => "button key:esc", "wrapper" => "li.cancel")) ?>
				<?= $model->submit("Save", array("class" => "primary key:s", "wrapper" => "li.save")) ?>
			</ul>
		<?= $model->formEnd() ?>
	</div>

