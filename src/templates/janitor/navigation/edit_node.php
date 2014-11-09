<?php
global $action;
global $model;
global $itemtype;

$item = $model->getNode($action[1]);

$item_id = $item["id"];
$navigation_id = $item["navigation_id"];

// get pages for page select
$IC = new Item();
$pages = $IC->getItems(array("itemtype" => "page", "status" => 1, "order" => "page.name ASC"));
// get additional info for pages select
foreach($pages as $i => $item_page) {
	$item_page = $IC->extendItem($item_page);
	$pages[$i]["name"] = $item_page["name"];
}
array_unshift($pages, array("id" => "", "name" => "Select page"));

?>

<div class="scene defaultEdit">
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

				<h3>Link options</h3>
				<p>
					A navigation node can contain a static link, a dynamic page reference or be a linkless folder
					for other navigation nodes.
				</p> 
				<?= $model->input("node_link", array("value" => $item["node_link"])) ?>
				<?= $model->input("node_page_id", array("type" => "select", "options" => $model->toOptions($pages, "id", "name"), "value" => $item["node_page_id"])) ?>
			</fieldset>

			<ul class="actions">
				<?= $model->link("Back", "/janitor/admin/navigation/edit/".$navigation_id, array("class" => "button key:esc", "wrapper" => "li.cancel")) ?>
				<?= $model->submit("Save", array("class" => "primary key:s", "wrapper" => "li.save")) ?>
			</ul>
		<?= $model->formEnd() ?>
	</div>

</div>
