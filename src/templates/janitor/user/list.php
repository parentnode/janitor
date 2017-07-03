<?php
global $action;
global $model;

$user_groups = $model->getUserGroups();

$options = false;
$user_group_id = 0;

// show specific group tab?
if(count($action) > 1 && $action[1]) {
	$user_group_id = $action[1];
	$options = array("user_group_id" => $user_group_id);
}
// no user group passed - default to current users own group
else if(count($action) == 1) {
	$user_group_id = session()->value("user_group_id");
	$options = array("user_group_id" => $user_group_id);
}

//
// // show user_group users
// if(count($action) > 1 && $action[1]) {
// 	$user_group_id = $action[1];
// }
// else {
// 	$user_group_id = session()->value("user_group_id");
// }

$users = $model->getUsers($options);
?>
<div class="scene i:scene defaultList userList">
	<h1>Users</h1>

	<ul class="actions">
		<?= $HTML->link("New user", "/janitor/admin/user/new", array("class" => "button primary", "wrapper" => "li.new")) ?>
		<?= $HTML->link("User groups", "/janitor/admin/user/group/list", array("class" => "button", "wrapper" => "li.usergroup")) ?>
		<?= $HTML->link("Members", "/janitor/admin/user/members/list", array("class" => "button", "wrapper" => "li.member")) ?>
		<?= $HTML->link("Unconfirmed accounts", "/janitor/admin/user/unconfirmed-accounts", array("class" => "button", "wrapper" => "li.unconfirmed_accounts")) ?>
		<?= $HTML->link("Online users", "/janitor/admin/user/online", array("class" => "button", "wrapper" => "li.online")) ?>
	</ul>

<?	if($user_groups): ?>
	<ul class="tabs">
<?		foreach($user_groups as $user_group): ?>
		<?= $HTML->link($user_group["user_group"], "/janitor/admin/user/list/".$user_group["id"], array("wrapper" => "li".($user_group["id"] == $user_group_id ? ".selected" : ""))) ?>
<?		endforeach; ?>
		<?= $HTML->link("All", "/janitor/admin/user/list/0", array("wrapper" => "li.".($options === false ? "selected" : ""))) ?>
	</ul>
<?	else: ?>
	<p>You have no user groups. Create at least one user group before you continue.</p>
<?	endif; ?>


	<div class="all_items i:defaultList filters">
<?		if($users): ?>
		<ul class="items">
<?			foreach($users as $item): ?>
			<li class="item item_id:<?= $item["id"] ?>">
				<h3><?= $item["nickname"] ?><?= $item["id"] == session()->value("user_id") ? " (YOU)" : "" ?></h3>
				<dl class="info">
					<dt>Last login</dt>
					<dd><?= $item["last_login_at"] ? $item["last_login_at"] : "Never" ?></dd>
				</dl>
				<ul class="actions">
					<?= $HTML->link("Edit", "/janitor/admin/user/edit/".$item["id"], array("class" => "button", "wrapper" => "li.edit")) ?>

<? if($item["id"] != 1): ?>
					<? //= $JML->oneButtonForm("Delete", "/janitor/admin/user/delete/".$item["id"], array(
					//	"js" => true,
					//	"wrapper" => "li.delete",
					//	"static" => true
					// )) ?>
					<?= $JML->statusButton("Enable", "Disable", "/janitor/admin/user/status", $item) ?>
<? endif; ?>
				</ul>
			 </li>
<?			endforeach; ?>
		</ul>
<?		else: ?>
		<p>No users.</p>
<?		endif; ?>
	</div>

</div>