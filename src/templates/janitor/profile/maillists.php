<?php
global $action;
global $model;


$IC = new Items();


$item = $model->getUser();

// get maillists
$all_maillists = $this->maillists();
$user_maillists = $item["maillists"];


?>
<div class="scene i:scene defaultList">
	<h1>Maillists</h1>
	<h2><?= $item["nickname"] ?></h2>

	<ul class="actions">
		<?= $HTML->link("All users", "/janitor/admin/user/list/".$item["user_group_id"], array("class" => "button", "wrapper" => "li.list")) ?>
	</ul>


	<?= $JML->profileTabs("maillists") ?>


	<div class="maillists i:maillistsProfile i:collapseHeader">
		<h2>Newsletters and maillists</h2>

<?		if($all_maillists): ?>
		<p>You are signed up for the following maillists:</p>
		<ul class="maillists">
<?			foreach($all_maillists as $maillist): ?>
			<li class="<?= arrayKeyValue($user_maillists, "maillist_id", $maillist["id"]) !== false ? "subscribed" : "" ?>">
				<ul class="actions">
					<?= $JML->oneButtonForm("Unsubscribe", "/janitor/admin/profile/deleteMaillist/".$maillist["id"], array(
						"confirm-value" => false,
						"wrapper" => "li.unsubscribe"
					)) ?>
					<?= $JML->oneButtonForm("Subscribe", "/janitor/admin/profile/addMaillist", array(
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
		<p>You don't have any maillist subscriptions for your account.</p>
<?		endif; ?>
	</div>


</div>