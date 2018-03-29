<?php
global $action;
$IC = new Items();

include_once("classes/users/superuser.class.php");
$UC = new SuperUser();

$maillists = $this->maillists();

$options = false;
$maillist_id = 0;

// show specific maillist tab?
if(count($action) > 2 && $action[2]) {
	$maillist_id = $action[2];
	$options = array("maillist_id" => $maillist_id);
}
// no maillist type passed - default to first maillist
else if(count($action) == 2 && $maillists) {
	$maillist_id = $maillists[0]["id"];
	$options = array("maillist_id" => $maillist_id);
}

// remember maillistlist to return to (from new view)
session()->value("return_to_maillistlist", $maillist_id);

if($maillist_id) {
	$maillist = $this->maillists($maillist_id);
}

$subscribers = $UC->getMaillists($options);
?>
<div class="scene i:scene defaultList maillistList">
	<h1>Maillists</h1>

	<ul class="actions">
		<?= $HTML->link("Messages", "/janitor/admin/message/", array("class" => "button", "wrapper" => "li.messages")) ?>
		<?= $HTML->link("New mailing list", "/janitor/admin/message/maillists/new", array("class" => "button primary", "wrapper" => "li.new")) ?>
		<? if(isset($maillist)): ?>
		<?= $HTML->link("Download list (".$maillist["name"].")", "/janitor/admin/message/maillists/download/".$maillist_id, array("class" => "button primary", "wrapper" => "li.download")) ?>
		<? endif; ?>
	</ul>

<?	if($maillists): ?>
	<ul class="tabs">
<?		foreach($maillists as $maillist): ?>
		<?= $HTML->link($maillist["name"].($maillist["id"] == $maillist_id ? " (" . ($subscribers ? count($subscribers) : "0") . ")" : ""), "/janitor/admin/message/maillists/list/".$maillist["id"], array("wrapper" => "li.".($maillist["id"] == $maillist_id ? "selected" : ""))) ?>
<?		endforeach; ?>
		<?= $HTML->link("All". (!$maillist_id ? " (".count($subscribers).")" : ""), "/janitor/admin/message/maillists/list/0", array("wrapper" => "li.".($options === false ? "selected" : ""))) ?>
	</ul>
<?	endif; ?>

	<div class="all_items i:defaultList filters">
<?		if($subscribers): ?>
		<ul class="items">
<?			foreach($subscribers as $subscriber): ?>
			<li class="item user_id_id:<?= $subscriber["user_id"] ?>">
				<h3><?= $subscriber["email"] ?></h3>
				<dl class="info">
					<dt class="nickname">Nickname</dt>
					<dd class="nickname"><?= $subscriber["nickname"] ?></dd>
				</dl>
				<ul class="actions">
					<?= $HTML->link("View", "/janitor/admin/user/edit/".$subscriber["user_id"], array("class" => "button", "wrapper" => "li.edit")) ?>
				</ul>
			 </li>
<?			endforeach; ?>
		</ul>
<?		else: ?>
		<p>No subscribers.</p>
<?		endif; ?>
	</div>

</div>