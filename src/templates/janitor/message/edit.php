<?php
global $action;
global $IC;
global $model;
global $itemtype;


include_once("classes/users/superuser.class.php");
$UC = new SuperUser();

$item_id = $action[1];
$item = $IC->getItem(array("id" => $item_id, "extend" => array("tags" => true, "mediae" => true, "comments" => true)));

$maillists = $this->maillists();
// Extend maillist name with subscriber count
if($maillists) {
	foreach($maillists as $index => $maillist) {
		$maillists[$index]["name"] .= " (".count($UC->getMaillists(array("maillist_id" => $maillist["id"]))).")";
	}

	$maillist_options = $HTML->toOptions($maillists, "id", "name", ["add" => ["" => "Maillist"]]);
}
// no maillists
else {
	$maillist_options = ["" => "No maillists"];
}

// get available layouts
$layouts = $model->getLayouts();
$layout_options = $HTML->toOptions($layouts, "name", "subject", ["add" => ["" => "Choose layout"]]);
?>
<div class="scene i:scene defaultEdit <?= $itemtype ?>Edit">
	<h1>Selected message</h1>
	<h2><?= strip_tags($item["name"]) ?></h2>

	<?= $JML->editGlobalActions($item, ["modify" => [
		"list" => [
			"label" => "Messages",
			"url" => "/janitor/admin/message"
		]
	]]) ?>

	<div class="item recipients i:sendMessage i:collapseHeader">
		<h2>Send message</h2>
		<?= $model->formStart("userSendMessage", array("class" => "labelstyle:inject")) ?>
			<?= $model->input("item_id", array("type" => "hidden", "value" => $item_id)) ?>

			<fieldset>
				<h3>Type recipient email(s)</h3> 
				<?= $model->input("recipients") ?>
				<p>This will send the message as is directly to the stated email(s). It will not fill out personal variables in the email.</p>
			</fieldset>
			<fieldset>
				<h3>Send to maillist</h3>
				<?= $model->input("maillist_id", array("type" => "select", "options" => $maillist_options)) ?>
				<p>This will send the message to every user in the list, filling out as many variables in the email, using data from the user profile.</p>
			</fieldset>
			<fieldset>
				<h3>To specific user</h3>
				<?= $model->input("user_id", array("type" => "string", "label" => "User Id (yours is ".session()->value("user_id").")")) ?>
				<p>This will send the message to the specified user, filling out as many variables in the email, using data from the user profile.</p>
			</fieldset>
			<ul class="actions">
				<?= $model->submit("Send", array("class" => "primary", "wrapper" => "li.submit")) ?>
			</ul>

		<?= $model->formEnd() ?>
	</div>

	<div class="item message i:defaultEdit i:collapseHeader">
		<h2>Message content</h2>
		<?= $model->formStart("update/".$item["id"], array("class" => "labelstyle:inject")) ?>
		
			<fieldset>
				<?= $model->input("layout", array("type" => "select", "options" => $layout_options, "value" => $item["layout"])) ?>
				<?= $model->input("published_at", array("value" => $item["published_at"])) ?>

				<?= $model->input("name", array("value" => $item["name"])) ?>
				<?= $model->input("description", array("class" => "autoexpand short", "value" => $item["description"])) ?>
				<?= $model->input("html", array("value" => $item["html"])) ?>
			</fieldset>

			<?= $JML->editActions($item) ?>

		<?= $model->formEnd() ?>
	</div>

	<?= $JML->editTags($item) ?>

</div>
