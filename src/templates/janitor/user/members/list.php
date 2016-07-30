<?php
global $action;
global $model;
$IC = new Items();


$memberships = $IC->getItems(array("itemtype" => "membership", "extend" => true));

$options = false;
$membership_id = 0;
// show membership tab?
if(count($action) > 2 && $action[2]) {
	$membership_id = $action[2];
	$options = array("item_id" => $membership_id);
}
else if(count($action) == 1 && $memberships) {
	$membership_id = $memberships[0]["id"];
	$options = array("item_id" => $membership_id);
}

// remember memberlist to return to (from new view)
session()->value("return_to_memberlist", $membership_id);


$members = $model->getMembers($options);
print_r($members);

?>
<div class="scene i:scene defaultList userList">
	<h1>Members</h1>

	<ul class="actions">
		<?= $HTML->link("New member", "/janitor/admin/user/member/new", array("class" => "button primary", "wrapper" => "li.users")) ?>
		<?= $HTML->link("Users", "/janitor/admin/user/list", array("class" => "button", "wrapper" => "li.users")) ?>
	</ul>

<?	if($memberships): ?>
	<ul class="tabs">
		<?= $HTML->link("All", "/janitor/admin/user/member/list/0", array("wrapper" => "li.".($options === false ? "selected" : ""))) ?>
<?		foreach($memberships as $membership): ?>
		<?= $HTML->link($membership["name"], "/janitor/admin/user/member/list/".$membership["id"], array("wrapper" => "li.".($membership["id"] == $membership_id ? "selected" : ""))) ?>
<?		endforeach; ?>
	</ul>
<?	endif; ?>


	<div class="all_items i:defaultList filters">
<?		if($members): ?>
		<ul class="items">
<?			foreach($members as $item): ?>
			<li class="item item_id:<?= $item["id"] ?>">
				<h3><?= $item["nickname"] ?><?= $item["id"] == session()->value("user_id") ? " (YOU)" : "" ?></h3>

				<ul class="actions">
					<?= $HTML->link("Edit", "/janitor/admin/user/member/edit/".$item["id"], array("class" => "button", "wrapper" => "li.edit")) ?>
					<?= $JML->statusButton("Enable", "Disable", "/janitor/admin/user/member/status", $item) ?>
				</ul>
			 </li>
<?			endforeach; ?>
		</ul>
<?		else: ?>
		<p>No members.</p>
<?		endif; ?>
	</div>

</div>