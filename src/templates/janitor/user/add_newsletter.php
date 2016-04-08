<?php
global $action;
global $model;

$user_id = $action[1];

?>
<div class="scene i:scene defaultNew userNewsletter">
	<h1>Add newsletter subscription</h1>

	<ul class="actions">
		<?= $HTML->link("Back to user", "/janitor/admin/user/edit/".$user_id, array("class" => "button", "wrapper" => "li.cancel")) ?>
	</ul>

	<?= $model->formStart("/janitor/admin/user/addNewsletter/".$user_id, array("class" => "i:defaultNew labelstyle:inject")) ?>

		<p>Newsletters are still free form - just type the name of the newsletter.</p>
		<fieldset>
			<?= $model->input("newsletter") ?>
		</fieldset>

		<ul class="actions">
			<?= $model->link("Back", "/janitor/admin/user/edit/".$user_id, array("class" => "button key:esc", "wrapper" => "li.cancel")) ?>
			<?= $model->submit("Add newsletter", array("class" => "primary key:s", "wrapper" => "li.save")) ?>
		</ul>
	<?= $model->formEnd() ?>

</div>