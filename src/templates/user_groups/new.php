<?php

$action = $this->actions();

$model = new UserGroup();

?>

	<div class="scene defaultNew">
		<h1>New user group</h1>

		<ul class="actions">
			<li class="cancel"><a href="/admin/user_groups/list" class="button">Back</a></li>
		</ul>

		<form action="/admin/user_groups/save" class="i:formDefaultNew labelstyle:inject" method="post" enctype="multipart/form-data">

			<fieldset>
				<?= $model->input("name") ?>
			</fieldset>

			<ul class="actions">
				<li class="cancel"><a href="/admin/user_groups/list" class="button">Back</a></li>
				<li class="save"><input type="submit" value="Save" class="button primary" /></li>
			</ul>

		</form>
	</div>

