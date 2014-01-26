<?php

$action = $this->actions();

$model = new User();
// check if custom function exists on cart class

$user_group = $model->getUserGroups(array("user_group_id" => $action[2]));
$access = $model->getAccessPoints(array("user_group_id" => $action[2]));

?>

<div class="scene defaultEdit">
	<h1>Access for <?= $user_group["user_group"] ?></h1>

	<ul class="actions">
		<li class="cancel"><a href="/admin/user/group/list" class="button">Back</a></li>
	</ul>

	<div class="item i:defaultEdit">
		<form action="/admin/user/updateAccess/<?= $action[2] ?>" class="labelstyle:inject" method="post" enctype="multipart/form-data">
			<fieldset>
				<ul class="points">
<? 			foreach($access["points"] as $point => $actions): 
				$short_point = str_replace(".php", "", str_replace(LOCAL_PATH."/www", "", $point)); ?>
					<li>
						<h3><?= $short_point ?></h3>
<?						foreach($actions as $action): 
							$access_granted = isset($access["permissions"][$short_point."/".$action]) ? true : false; ?>
						<?= $model->input("grant[".$short_point."/".$action."]", array("type" => "checkbox", "label" => $action, "value" => 1, "checked" => $access_granted)) ?>
<?						endforeach; ?>
					</li>
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