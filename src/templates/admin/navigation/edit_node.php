<?php
global $action;
global $model;
global $itemtype;

$item = $model->getNode($action[1]);

$item_id = $item["id"];
$navigation_id = $item["navigation_id"];

?>

<div class="scene defaultEdit">
	<h1>Edit navigation node</h1>

	<ul class="actions">
		<?= $model->link("List", "/admin/navigation/edit/".$navigation_id, array("class" => "button", "wrapper" => "li.cancel")) ?>
	</ul>

	<div class="item i:defaultEdit">
		<h2>Edit navigation node</h2>
		<?= $model->formStart("/admin/navigation/updateNode/".$item_id, array("class" => "i:defaultNew labelstyle:inject")) ?>
			<fieldset>

				<?= $model->input("node_name", array("value" => $item["node_name"])) ?>
				<?= $model->input("node_classname", array("value" => $item["node_classname"])) ?>

				<h3>Link options</h3>
				<p>
					A navigation node can contain a static link, a dynamic page reference or be a linkless folder
					for other navigation nodes.
				</p> 
				<?= $model->input("node_link", array("value" => $item["node_link"])) ?>

				

				<? //= $model->input("node_item_id", array("type" => "select", "options" => $pages)) ?>
			</fieldset>

			<ul class="actions">
				<?= $model->link("Back", "/admin/navigation/edit/".$navigation_id, array("class" => "button key:esc", "wrapper" => "li.cancel")) ?>
				<?= $model->submit("Save", array("class" => "primary key:s", "wrapper" => "li.save")) ?>
			</ul>
		<?= $model->formEnd() ?>
	</div>

</div>
