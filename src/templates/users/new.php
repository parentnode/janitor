<?php

$model = new User();
?>

	<div class="scene defaultNew">
		<h1>New user</h1>

		<ul class="actions">
			<li class="cancel"><a href="/admin/users/list" class="button">Back</a></li>
		</ul>

		<form action="/admin/users/save" class="i:formDefaultNew labelstyle:inject" method="post" enctype="multipart/form-data">

			<fieldset>
				<?= $model->input("firstname") ?>
				<?= $model->input("lastname") ?>
				<?= $model->input("nickname") ?>
				<?= $model->input("user_group_id") ?>
				<?= $model->input("language") ?>
			</fieldset>

			<ul class="actions">
				<li class="cancel"><a href="/admin/users/list" class="button">Back</a></li>
				<li class="save"><input type="submit" value="Save" class="button primary" /></li>
			</ul>

		</form>
	</div>

