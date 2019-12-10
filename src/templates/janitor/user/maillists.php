<?php
global $action;
global $model;


$user_id = $action[1];
$IC = new Items();


$user = $model->getUsers(array("user_id" => $user_id));

// get maillists
$all_maillists = $this->maillists();
$user_maillists = $model->getMaillists(array("user_id" => $user_id));


?>
<div class="scene i:scene defaultList">
	<h1>Maillists</h1>
	<h2><?= $user["nickname"] ?></h2>

	<ul class="actions">
		<?= $HTML->link("All users", "/janitor/admin/user/list/".$user["user_group_id"], array("class" => "button", "wrapper" => "li.list")) ?>
	</ul>


	<?= $JML->userTabs($user_id, "maillists") ?>


	<div class="maillists i:maillists">
		<h2>Newsletters and maillists</h2>
		<p>
			The following newsletter- and maillists are available for subscription.
		 </p>
<?		if($all_maillists): ?>
		<ul class="maillists">
<?			foreach($all_maillists as $maillist): ?>
			<li class="<?= arrayKeyValue($user_maillists, "maillist_id", $maillist["id"]) !== false ? "subscribed" : "" ?>">
				<ul class="actions">
					<?= $HTML->oneButtonForm("Unsubscribe", "/janitor/admin/user/deleteMaillist/".$user_id."/".$maillist["id"], array(
						"confirm-value" => false,
						"wrapper" => "li.unsubscribe"
					)) ?>
					<?= $HTML->oneButtonForm("Subscribe", "/janitor/admin/user/addMaillist/".$user_id, array(
						"confirm-value" => false,
						"wrapper" => "li.subscribe",
						"class" => "primary",
						"inputs" => array("maillist_id" => $maillist["id"])
					)) ?>
				</ul>
				<h3><?= $maillist["name"] ?></h3>
			</li>
<?			endforeach; ?>
		</ul>
<?		else: ?>
		<p>You don't have any maillist subscriptions for your account</p>
<?		endif; ?>
	</div>


</div>