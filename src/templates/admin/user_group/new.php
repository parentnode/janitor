<?php
global $action;
global $model;
?>
<div class="scene defaultNew">
	<h1>New user group</h1>

	<ul class="actions">
		<?= $model->link("Back", "/admin/user/group/list", array("class" => "button", "wrapper" => "li.cancel")) ?>
	</ul>


	<?= $model->formStart("/admin/user/saveUserGroup", array("class" => "i:formDefaultNew labelstyle:inject")) ?>

		<fieldset>
			<?= $model->input("user_group") ?>
		</fieldset>

		<ul class="actions">
			<?= $model->link("Back", "/admin/user/group/list", array("class" => "button key:esc", "wrapper" => "li.cancel")) ?>
			<?= $model->submit("Save", array("class" => "primary key:s", "wrapper" => "li.save")) ?>
		</ul>

	<?= $model->formEnd() ?>
</div>

