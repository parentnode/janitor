<?php
global $action;
global $model;
$IC = new Items();
$SC = new Shop();

$newsletters = $this->newsletters();
//$memberships = $IC->getItems(array("itemtype" => "membership", "extend" => true));
//print_r($memberships);

$options = false;
$newsletter_id = 0;

// show specific newsletter tab?
if(count($action) > 2 && $action[2]) {
	$newsletter_id = $action[2];
	$options = array("newsletter_id" => $newsletter_id);
}
// no newsletter type passed - default to first newsletter
else if(count($action) == 2 && $newsletters) {
	$newsletter_id = $newsletters[0]["id"];
	$options = array("newsletter_id" => $newsletter_id);
}

// remember newsletterlist to return to (from new view)
session()->value("return_to_newsletterlist", $newsletter_id);

if($newsletter_id) {
	$newsletter = $this->newsletters($newsletter_id);
}

$subscribers = $model->getNewsletters($options);
//print_r($subscribers);
?>
<div class="scene i:scene defaultList newsletterList">
	<h1>Newsletters</h1>

	<ul class="actions">
		<?= $HTML->link("New mailing list", "/janitor/admin/user/newsletters/new", array("class" => "button primary", "wrapper" => "li.new")) ?>
		<? if($newsletter): ?>
		<?= $HTML->link("Download list (".$newsletter["name"].")", "/janitor/admin/user/newsletters/download/".$newsletter_id, array("class" => "button primary", "wrapper" => "li.download")) ?>
		<? endif; ?>
	</ul>

<?	if($newsletters): ?>
	<ul class="tabs">
<?		foreach($newsletters as $newsletter): ?>
		<?= $HTML->link($newsletter["name"], "/janitor/admin/user/newsletters/list/".$newsletter["id"], array("wrapper" => "li.".($newsletter["id"] == $newsletter_id ? "selected" : ""))) ?>
<?		endforeach; ?>
		<?= $HTML->link("All", "/janitor/admin/user/newsletters/list/0", array("wrapper" => "li.".($options === false ? "selected" : ""))) ?>
	</ul>
<?	endif; ?>


	<div class="all_items i:defaultList filters">
<?		if($subscribers): ?>
		<ul class="items">
<?			foreach($subscribers as $subscriber):
				$email = $model->getUsernames(["user_id" => $subscriber["user_id"], "type" => "email"]);
				$user = $model->getUsers(["user_id" => $subscriber["user_id"]]);
?>
			<li class="item user_id_id:<?= $subscriber["user_id"] ?>">
				<h3><?= $email ?></h3>
				<dl class="info">
					<dt class="nickname">Nickname</dt>
					<dd class="nickname"><?= $user["nickname"] ?></dd>
				</dl>
			 </li>
<?			endforeach; ?>
		</ul>
<?		else: ?>
		<p>No subscribers.</p>
<?		endif; ?>
	</div>

</div>