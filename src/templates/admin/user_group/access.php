<?php

global $action;
global $model;

$user_group = $model->getUserGroups(array("user_group_id" => $action[2]));
$access = $model->getAccessPoints(array("user_group_id" => $action[2]));
?>
<div class="scene defaultEdit accessEdit">
	<h1>Access for <?= $user_group["user_group"] ?></h1>

	<ul class="actions">
		<?= $HTML->link("Groups", "/admin/user/group/list", array("class" => "button", "wrapper" => "li.cancel")) ?>
	</ul>

	<div class="access i:defaultEdit">

		<p>Select which actions to allow for each controller.</p>

		<?= $model->formStart("/admin/user/updateAccess/".$action[2], array("class" => "labelstyle:inject")) ?>
			<fieldset>
				<ul class="points">
<?			foreach($access["points"] as $point => $actions): ?>

<?					if($actions): ?>
					<li class="action">
						<h3><?= $point ?></h3>
<?						foreach($actions as $access_action):
							$access_granted = isset($access["permissions"][$point.$access_action]) ? true : false; ?>
						<?= $model->input("grant[".$point.$access_action."]", array("id" => "input".preg_replace("/[\/]/", "_", $point.$access_action), "type" => "checkbox", "label" => $access_action, "value" => 1, "checked" => $access_granted)) ?>
<?						endforeach; ?>
					</li>
<?					else: ?>
					<li>
						<?= $model->input("grant[".$point."/]", array("id" => "input".preg_replace("/[\/]/", "_", $point), "type" => "hidden", "value" => 1)) ?>
					</li>
<?					endif; ?>

<?			endforeach; ?>
				</ul>
			</fieldset>

			<ul class="actions">
				<?= $HTML->link("Back", "/admin/user/group/list", array("class" => "button key:esc", "wrapper" => "li.cancel")) ?>
				<?= $model->submit("Update", array("class" => "primary key:s", "wrapper" => "li.save")) ?>
			</ul>
		<?= $model->formEnd() ?>
	</div>

</div>