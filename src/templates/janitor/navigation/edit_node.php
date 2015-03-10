<?php
global $action;
global $model;
global $itemtype;

$item = $model->getNode($action[1]);

$item_id = $item["id"];
$navigation_id = $item["navigation_id"];

// get pages for page select
$IC = new Items();
$pages = $IC->getItems(array("itemtype" => "page", "status" => 1, "order" => "page.name ASC", "extend" => true));

// find controllers
$fs = new FileSystem();
$controllers = array();
$raw_controllers = $fs->files(LOCAL_PATH."/www", array("allow_extensions" => "php", "deny_folders" => "janitor"));
foreach($raw_controllers as $i => $raw_controller) {
	$clean_controller = preg_replace("/\.php$/", "", str_replace(LOCAL_PATH."/www", "", $raw_controller));
	$controller["id"] = $clean_controller;
	$controller["name"] = $clean_controller;
	$controllers[] = $controller;
}

?>

<div class="scene defaultEdit navigationNodeEdit">
	<h1>Edit navigation node</h1>

	<ul class="actions">
		<?= $model->link("List", "/janitor/admin/navigation/edit/".$navigation_id, array("class" => "button", "wrapper" => "li.cancel")) ?>
	</ul>

	<div class="item i:defaultEdit">
		<h2>Edit navigation node</h2>
		<?= $model->formStart("/janitor/admin/navigation/updateNode/".$item_id, array("class" => "i:defaultNew labelstyle:inject")) ?>

			<fieldset>
				<?= $model->input("node_name", array("value" => $item["node_name"])) ?>
				<?= $model->input("node_classname", array("value" => $item["node_classname"])) ?>
			</fieldset>

			<p>
				A navigation node can contain a static link, a dynamic page reference <br />or a linkless "folder"
				containing other "sub" navigation nodes.<br />Leave the following empty if you don't want any link for this node.
			</p>

			<fieldset>
				<h3>Link to a static url</h3>
				<?= $model->input("node_link", array("value" => $item["node_link"])) ?>
				<?= $model->input("node_target", array("type" => "checkbox", "value" => $item["node_target"])) ?>
			</fieldset>

			<fieldset>
				<h3>Link to a dynamic page</h3>
				<?= $model->input("node_item_id", array("type" => "select", "options" => $model->toOptions($pages, "id", "name", array("add" => array("" => "Select page"))), "value" => $item["node_item_id"])) ?>
				<?= $model->input("node_item_controller", array("type" => "select", "options" => $model->toOptions($controllers, "id", "name", array("add" => array("" => "Select controller"))), "value" => $item["node_item_controller"])) ?>
			</fieldset>

			<fieldset>
				<h3>Fallback link</h3>
				<?= $model->input("node_fallback", array("value" => $item["node_fallback"])) ?>
			</fieldset>

			<ul class="actions">
				<?= $model->link("Back", "/janitor/admin/navigation/edit/".$navigation_id, array("class" => "button key:esc", "wrapper" => "li.cancel")) ?>
				<?= $model->submit("Save", array("class" => "primary key:s", "wrapper" => "li.save")) ?>
			</ul>
		<?= $model->formEnd() ?>
	</div>

</div>
