<?php
global $action;
global $model;

$user_groups = $model->getUserGroups();

$options = [
	"limit" => 200,
	"pattern" => false
];

$query = getVar("query");
if($query) {
	$options["query"] = $query;
}
if(count($action) > 3) {
	if($action[2] === "page") {
		$options["page"] = $action[3];
	}
}


$user_group_id = 0;

// show specific group tab?
if(count($action) > 1 && $action[1]) {
	$user_group_id = $action[1];
	$options["pattern"] = [
		"user_group_id"	=> $user_group_id,
	];
}
// no user group passed - default to current users own group
else if(count($action) == 1) {
	$user_group_id = session()->value("user_group_id");
	$options["pattern"] = [
		"user_group_id"	=> $user_group_id,
	];
}


// $users = $model->getUsers($options);

$users = $model->paginate($options);
// debug(["users", $users]);

$current_user_id = session()->value("user_id");

?>
<div class="scene i:scene defaultList userList">
	<h1>Users</h1>

	<ul class="actions">
		<?= $HTML->link("New user", "/janitor/admin/user/new", array("class" => "button primary", "wrapper" => "li.new")) ?>
		<?= $HTML->link("User groups", "/janitor/admin/user/group/list", array("class" => "button", "wrapper" => "li.usergroup")) ?>
		<?= $HTML->link("Members", "/janitor/admin/member/list", array("class" => "button", "wrapper" => "li.member")) ?>
		<?= $HTML->link("Unverified usernames", "/janitor/admin/user/unverified-usernames", array("class" => "button", "wrapper" => "li.unverified_usernames")) ?>
		<?= $HTML->link("Online users", "/janitor/admin/user/online", array("class" => "button", "wrapper" => "li.online")) ?>
	</ul>


<?	if($user_groups): ?>
	<ul class="tabs">
<?		foreach($user_groups as $user_group):
			if($user_group["id"] != 1): ?>
		<?= $HTML->link($user_group["user_group"], "/janitor/admin/user/list/".$user_group["id"], array("wrapper" => "li".($user_group["id"] == $user_group_id ? ".selected" : ""))) ?>
<?			endif;
		endforeach; ?>
		<?= $HTML->link("All", "/janitor/admin/user/list/0", array("wrapper" => "li.".(!$user_group_id ? "selected" : ""))) ?>
	</ul>
<?	else: ?>
	<p>You have no user groups. Create at least one user group before you continue.</p>
<?	endif; ?>


	<div class="all_items i:defaultList filters" <?= $HTML->jsData(["search"], ["filter-search" => $HTML->path."/list/".$user_group_id]) ?>>


<?		if($users && $users["range_users"]): ?>

		<?= $HTML->pagination($users, [
			"base_url" => "/janitor/admin/user/list/".$user_group_id,
			"query" => $query,
		]) ?>

		<ul class="items">
<?			foreach($users["range_users"] as $item): ?>
			<li class="item item_id:<?= $item["id"] ?>">

				<h3><?= $item["nickname"] ?><?= ($item["id"] == $current_user_id ? " (YOU)" : "") ?></h3>
				<dl class="info">
<? if($item["status"] != -1): ?>
					<dt>Last login</dt>
					<dd><?= ($item["last_login_at"] ? $item["last_login_at"] : "Never") ?></dd>
<? else: ?>
					<dt>Cancelled</dt>
					<dd><?= ($item["modified_at"] ? $item["modified_at"] : "Never") ?></dd>
<? endif; ?>
				</dl>
				<ul class="actions">
<? if($item["status"] != -1): ?>
					<?= $HTML->link("Edit", "/janitor/admin/user/edit/".$item["id"], array("class" => "button", "wrapper" => "li.edit")) ?>
				<? 	if($item["id"] != 1): ?>
					<? //= $HTML->oneButtonForm("Delete", "/janitor/admin/user/delete/".$item["id"], array(
					//	"js" => true,
					//	"wrapper" => "li.delete",
					//	"static" => true
					// )) ?>
					<?= $JML->statusButton("Enable", "Disable", "/janitor/admin/user/status", $item) ?>
				<? 	endif; ?>
<? endif; ?>

				</ul>
			 </li>
<?			endforeach; ?>
		</ul>

		<?= $HTML->pagination($users, [
			"base_url" => "/janitor/admin/user/list/".$user_group_id,
			"query" => $query,
		]) ?>

<?		else: ?>
		<p>No users.</p>
<?		endif; ?>

	</div>


</div>