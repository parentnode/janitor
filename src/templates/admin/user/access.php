<?php

global $action;
global $model;

$user_group = $model->getUserGroups(array("user_group_id" => $action[2]));
$access = $model->getAccessPoints(array("user_group_id" => $action[2]));
?>
<div class="scene defaultEdit accessEdit">
	<h1>Access for <?= $user_group["user_group"] ?></h1>

	<ul class="actions">
		<li class="cancel"><a href="/admin/user/group/list" class="button">Back</a></li>
	</ul>

	<div class="access i:defaultEdit">

		<p>Select which actions to allow for each controller.</p>

		<form action="/admin/user/updateAccess/<?= $action[2] ?>" class="labelstyle:inject" method="post" enctype="multipart/form-data">
			<fieldset>
				<ul class="points">
<?			foreach($access["points"] as $point => $actions): ?>

<?					if($actions): ?>
					<li class="action">
						<h3><?= $point ?></h3>
<?						foreach($actions as $access_action):
							$access_granted = isset($access["permissions"][$point.$access_action]) ? true : false; ?>
						<?= $model->input("grant[".$point.$access_action."]", array("type" => "checkbox", "label" => $access_action, "value" => 1, "checked" => $access_granted)) ?>
<?						endforeach; ?>
					</li>
<?					else: ?>
					<li>
						<?= $model->input("grant[".$point."/]", array("type" => "hidden", "value" => 1)) ?>
					</li>
<?					endif; ?>

<?			endforeach; ?>
				</ul>
			</fieldset>

			<ul class="actions">
				<li class="cancel"><a href="/admin/user/group/list" class="button key:esc">Back</a></li>
				<li class="save"><input type="submit" value="Update" class="button primary key:s" /></li>
			</ul>
		</form>
	</div>

</div>