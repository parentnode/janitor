<?php
$access_item = false;
if(isset($read_access) && $read_access) {
	return;
}

include_once($_SERVER["FRAMEWORK_PATH"]."/config/init.php");

$action = $page->actions();

$model = new UserGroup();

if($action && $action[0] == "save" && $model->save()) {
	print '{"cms_status":"success", "message":"something correct"}';
	exit();

}
?>
<? $page->header(array("type" => "admin")) ?>

	<div class="scene defaultNew">
		<h1>New user group</h1>

		<ul class="actions">
			<li class="cancel"><a href="/admin/user_groups/list" class="button">Back</a></li>
		</ul>

		<form action="/admin/user_groups/new/save" class="i:formDefaultNew labelstyle:inject" method="post" enctype="multipart/form-data">

			<fieldset>
				<?= $model->input("name") ?>
			</fieldset>

			<ul class="actions">
				<li class="cancel"><a href="/admin/user_groups/list" class="button">Back</a></li>
				<li class="save"><input type="submit" value="Save" class="button primary" /></li>
			</ul>

		</form>
	</div>

<? $page->footer(array("type" => "admin")) ?>
