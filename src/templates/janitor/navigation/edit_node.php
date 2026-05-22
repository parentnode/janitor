<?php
global $action;
global $model;
global $itemtype;

$navigation_node = $model->getNavigationNode($action[1]);

if($navigation_node) {

	$node_id = $navigation_node["id"];
	$navigation_id = $navigation_node["navigation_id"];

	$link_options = $model->getLinkOptions();
}

?>

<div class="scene i:scene defaultEdit navigationNodeEdit">
	<h1>Edit navigation node</h1>
<? if($navigation_node): ?>

	<h2><?= $navigation_node["node_name"] ?></h2>

	<ul class="actions">
		<?= $model->link("List", "/janitor/admin/navigation/edit/".$navigation_id, array("class" => "button", "wrapper" => "li.cancel")) ?>
	</ul>

	<div class="item i:editNavigationNode" data-validate-link="<?= security()->validPath("/janitor/admin/navigation/validateNodeLink") ?>">
		<h2>Edit navigation node</h2>
		<?= $model->formStart("/janitor/admin/navigation/updateNode", array("class" => "labelstyle:inject")) ?>
			<?= $model->input("node_id", array("type" => "hidden", "value" => $node_id)) ?>

			<fieldset>
				<?= $model->input("node_name", ["value" => $navigation_node["node_name"]]) ?>
				<?= $model->input("node_classname", ["value" => $navigation_node["node_classname"]]) ?>
				<?= $model->input("node_link", ["value" => $navigation_node["node_link"], "options" => $link_options]) ?>
				<?= $model->input("node_target", ["type" => "checkbox", "value" => $navigation_node["node_target"]]) ?>
			</fieldset>

			<fieldset>
				<h3>Fallback link</h3>
				<?= $model->input("node_fallback", ["value" => $navigation_node["node_fallback"]]) ?>
			</fieldset>

			<ul class="actions">
				<?= $model->link("Back", "/janitor/admin/navigation/edit/".$navigation_id, array("class" => "button key:esc", "wrapper" => "li.cancel")) ?>
				<?= $model->submit("Save", array("class" => "primary key:s", "wrapper" => "li.save")) ?>
			</ul>
		<?= $model->formEnd() ?>
	</div>

<? else: ?>

	<h2>Navigation node not found.</h2>

<? endif; ?>
</div>
