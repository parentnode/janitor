<?php
global $action;
global $model;
?>
<div class="scene defaultNew">
	<h1>New user group</h1>

	<ul class="actions">
		<?= $model->link("Groups", "/janitor/admin/user/group/list", array("class" => "button", "wrapper" => "li.cancel")) ?>
	</ul>

	<?= $model->formStart("/janitor/admin/user/saveUserGroup", array("class" => "i:defaultNew labelstyle:inject")) ?>
		<fieldset>
			<?= $model->input("user_group") ?>
		</fieldset>

		<ul class="actions">
			<?= $model->link("Back", "/janitor/admin/user/group/list", array("class" => "button key:esc", "wrapper" => "li.cancel")) ?>
			<?= $model->submit("Save", array("class" => "primary key:s", "wrapper" => "li.save")) ?>
		</ul>
	<?= $model->formEnd() ?>
</div>

