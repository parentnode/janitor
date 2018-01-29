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
foreach($maillists as $index => $maillist) {
	$maillists[$index]["name"] .= " (".count($UC->getMaillists(array("maillist_id" => $maillist["id"]))).")";
}

// get available layouts
$layouts = $model->getLayouts();

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

	<div class="item recipients i:sendMessage">
		<h2>Send message</h2>
		<?= $model->formStart("sendMessage", array("class" => "labelstyle:inject")) ?>

			<fieldset>
				<?= $model->input("item_id", array("type" => "hidden", "value" => $item_id)) ?>
				<?= $model->input("recipients") ?>
				<?= $model->input("maillist_id", array("type" => "select", "options" => $HTML->toOptions($maillists, "id", "name", ["add" => ["" => "Maillist"]]))) ?>
				<?= $model->input("layout", array("type" => "select", "options" => $HTML->toOptions($layouts, "name", "subject"))) ?>
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
				<?= $model->input("published_at", array("value" => $item["published_at"])) ?>

				<?= $model->input("name", array("value" => $item["name"])) ?>
				<?= $model->input("description", array("class" => "autoexpand short", "value" => $item["description"])) ?>
				<?= $model->inputHTML("html", array("value" => $item["html"])) ?>
			</fieldset>

			<?= $JML->editActions($item) ?>

		<?= $model->formEnd() ?>
	</div>

	<?= $JML->editTags($item) ?>

</div>
