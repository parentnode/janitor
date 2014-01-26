<?php

$action = $this->actions();

$model = new User();

?>

	<div class="scene defaultNew">
		<h1>New user group</h1>

		<ul class="actions">
			<li class="cancel"><a href="/admin/user/group/list" class="button">Back</a></li>
		</ul>

		<form action="/admin/user/saveUserGroup" class="i:formDefaultNew labelstyle:inject" method="post" enctype="multipart/form-data">

			<fieldset>
				<?= $model->input("user_group") ?>
			</fieldset>

			<ul class="actions">
				<li class="cancel"><a href="/admin/user/group/list" class="button key:esc">Back</a></li>
				<li class="save"><input type="submit" value="Save" class="button primary key:s" /></li>
			</ul>

		</form>
	</div>

