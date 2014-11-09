<?php
global $action;
global $model;

$navigation_id = $action[1];

$IC = new Item();
$pages = $IC->getItems(array("itemtype" => "page", "status" => 1, "order" => "page.name ASC"));
// get additional info for pages select
foreach($pages as $i => $item_page) {
	$item_page = $IC->extendItem($item_page);
	$pages[$i]["name"] = $item_page["name"];
}
array_unshift($pages, array("id" => "", "name" => "Select page"));

?>
<div class="scene defaultNew">
	<h1>New navigation node</h1>

	<ul class="actions">
		<?= $model->link("Node list", "/janitor/admin/navigation/edit/".$navigation_id, array("class" => "button", "wrapper" => "li.cancel")) ?>
	</ul>

	<?= $model->formStart("/janitor/admin/navigation/saveNode/".$navigation_id, array("class" => "i:defaultNew labelstyle:inject")) ?>
		<fieldset>
			<h2>Create a new navigation node</h2>

			<?= $model->input("node_name") ?>
			<?= $model->input("node_classname") ?>

			<h3>Link options</h3>
			<p>
				A navigation node can contain a static link, a dynamic page reference or be a linkless folder
				for other navigation nodes.
			</p>
			<?= $model->input("node_link") ?>
			<?= $model->input("node_page_id", array("type" => "select", "options" => $model->toOptions($pages, "id", "name"))) ?>
		</fieldset>

		<ul class="actions">
			<?= $model->link("Back", "/janitor/admin/navigation/edit/".$navigation_id, array("class" => "button key:esc", "wrapper" => "li.cancel")) ?>
			<?= $model->submit("Save", array("class" => "primary key:s", "wrapper" => "li.save")) ?>
		</ul>
	<?= $model->formEnd() ?>
</div>

