<?php
global $action;
global $model;

$navigation_id = $action[1];

$IC = new Items();

// find controllers
$fs = new FileSystem();
$itemtype_classes = $fs->files(LOCAL_PATH."/classes/items", [
	"allow_extensions" => "php"
]);

$destinations = ["" => "Select item or items list"];

foreach($itemtype_classes as $itemtype_class) {

	if(!preg_match("/\.core\./", basename($itemtype_class))) {
		$itemtype = preg_replace("/type\.([a-z]+)\.class\.php/", "$1", basename($itemtype_class));

		$type_model = $IC->typeObject($itemtype);
		if($type_model && property_exists($type_model, "views")) {

			if($type_model->views["list"]) {
				$destinations[$itemtype] = $type_model->views["list"]["label"];
			}

			if($type_model->views["view"]) {
				$items = $IC->getItems(["itemtype" => $itemtype, "status" => 1, "order" => $itemtype.".name ASC", "extend" => true]);
				foreach($items as $item) {
					$destinations[$item["id"]] = $item["name"]. " (".$type_model->views["view"]["label"].")";
				}
				// debug(["items", $items, $model->toOptions($items, "id", "name")]);
				// $destinations = $destinations + $type_model->toOptions($items, "id", "name");
			}

		}
	}

}

?>
<div class="scene i:scene defaultNew navigationNodeNew">
	<h1>New navigation node</h1>

	<ul class="actions">
		<?= $model->link("Node list", "/janitor/admin/navigation/edit/".$navigation_id, ["class" => "button", "wrapper" => "li.cancel"]) ?>
	</ul>

	<div class="item">
		<h2>Create a new navigation node</h2>
		<?= $model->formStart("/janitor/admin/navigation/saveNode/".$navigation_id, ["class" => "i:newNavigationNode labelstyle:inject"]) ?>
			<fieldset>

				<?= $model->input("node_name") ?>
				<?= $model->input("node_classname") ?>
				<?= $model->input("node_target", array("type" => "checkbox")) ?>
			</fieldset>

			<fieldset>
				<h3>Link to an ItemType or list of ItemTypes</h3>
				<p>
					A navigation node can point to a specific ItemType (a post, page, event etc.), or to a list of ItemTypes (when allowed by your theme).
				</p>
				<?= $model->input("node_destination", ["options" => $destinations]) ?>
			</fieldset>

			<fieldset>
				<h3>Link to a static url</h3>
				<p>
					Or it can be a static internal or external link or work as a linkless "folder" containing other "sub" navigation nodes.<br />
					Leave the inputs empty to create folder.
				</p>
				<?= $model->input("node_link") ?>
			</fieldset>

			<fieldset>
				<h3>Fallback link</h3>
				<p>
					On rare occasions you may want to create a link to a page with access restrictions. As default the link will not be shown to users without permission, 
					but you can state a fallback url, which will instead be presented to unauthorised users.
				</p>
				<?= $model->input("node_fallback") ?>
			</fieldset>

			<ul class="actions">
				<?= $model->link("Back", "/janitor/admin/navigation/edit/".$navigation_id, ["class" => "button key:esc", "wrapper" => "li.cancel"]) ?>
				<?= $model->submit("Save", ["class" => "primary key:s", "wrapper" => "li.save"]) ?>
			</ul>
		<?= $model->formEnd() ?>
	</div>
</div>

