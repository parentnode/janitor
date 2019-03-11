<?php

global $action;
global $model;

$user_group_id = $action[2];
$user_group = $model->getUserGroups(array("user_group_id" => $user_group_id));
$access = $model->getAccessPoints(array("user_group_id" => $user_group_id));
?>
<div class="scene i:scene defaultEdit accessEdit">
	<h1>Group Access</h1>
	<h2><?= $user_group["user_group"] ?></h2>

	<ul class="actions">
		<?= $HTML->link("Groups", "/janitor/admin/user/group/list", array("class" => "button", "wrapper" => "li.cancel")) ?>
	</ul>

	<div class="access i:accessEdit">
		<p>Select which actions to allow for each controller.</p>
		<?= $model->formStart("/janitor/admin/user/updateAccess/".$user_group_id, array("class" => "labelstyle:inject")) ?>
			<fieldset>
				<ul class="points">
<?				foreach($access["points"] as $controller => $actions): ?>

<?					if($actions): ?>
					<li class="action">
						<h3><?= $controller ?></h3>
<?						foreach($actions as $access_action => $access_setting): ?>

<?							if($access_setting === true):
								$access_granted = isset($access["permissions"][$controller][$access_action]) ? true : false; ?>

						<?= $model->input("grant[$controller][$access_action]", array("id" => "input".preg_replace("/[\/]/", "_", $controller.$access_action), "type" => "checkbox", "label" => $access_action, "value" => $access_granted)) ?>

<?							endif; ?>

<?						endforeach; ?>
					</li>
<?					endif; ?>

<?				endforeach; ?>
				</ul>
			</fieldset>

			<ul class="actions">
				<?= $HTML->link("Back", "/janitor/admin/user/group/list", array("class" => "button key:esc", "wrapper" => "li.cancel")) ?>
				<?= $model->submit("Update", array("class" => "primary key:s", "wrapper" => "li.save")) ?>
			</ul>
		<?= $model->formEnd() ?>
	</div>

</div>