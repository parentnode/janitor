<?php
global $action;
global $model;
?>
<div class="scene i:scene defaultNew">
	<h1>New user group</h1>

	<ul class="actions">
		<?= $model->link("Groups", "/janitor/admin/user/group/list", array("class" => "button", "wrapper" => "li.cancel")) ?>
	</ul>

	<?= $model->formStart("saveUserGroup", array("class" => "i:defaultNew labelstyle:inject")) ?>
		<fieldset>
			<?= $model->input("user_group") ?>
		</fieldset>

		<?= $JML->newActions(array(
			"modify" => array(
				"cancel" => array(
					"url" => "/janitor/admin/user/group/list"
				)
			)
		)) ?>
	<?= $model->formEnd() ?>
</div>

