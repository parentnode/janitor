<?php
global $action;
global $model;

$navigation_id = $action[1];
$item = $model->getNavigations(array("navigation_id" => $navigation_id));

global $indent;
$indent = 0;


function recurseNodes($nodes) {
	global $HTML;
	global $indent;

	$_ = "";
	$_ .= '<ul class="nodes">';

	foreach($nodes as $node) {

		$att_class = $HTML->attribute("class", "item draggable node_id:".$node["id"], $node["classname"]);
		$_ .= '<li'.$att_class.'>';
		$_ .= '<div class="drag"></div>';
		$_ .= '<h3>'.$node["name"].'</h3>';

		if($node["link"]) {
			$_ .= '<span class="link">'.$node["link"].'</span>';
		}

		$_ .= '<ul class="actions">';
		$_ .= $HTML->link("Edit", "/admin/navigation/edit_node/".$node["id"], array("class" => "button", "wrapper" => "li.edit"));
		$_ .= $HTML->deleteButton("Delete", "/admin/navigation/deleteNode/".$node["id"]);
		$_ .= '</ul>';

		if($node["nodes"]) {
			$indent++;
			$_ .= recurseNodes($node["nodes"]);
			$indent--;
		}
		$_ .= '</li>';
	}
	$_ .= '</ul>';

	return $_;
}

?>
<div class="scene defaultEdit navigationEdit">
	<h1>Edit navigation</h1>

	<ul class="actions i:defaultEditActions item_id:<?= $navigation_id ?>">
		<?= $HTML->link("List", "/admin/navigation/list", array("class" => "button", "wrapper" => "li.cancel")) ?>
		<?= $HTML->link("New node", "/admin/navigation/new_node/".$navigation_id, array("class" => "button primary", "wrapper" => "li.cancel")) ?>

		<?= $HTML->deleteButton("Delete", "/admin/navigation/delete/".$navigation_id) ?>
	</ul>

	<div class="item">
		<h2>Handle</h2>
		<p><?= $item["handle"] ?></p>
	</div>

	<div class="nodes i:navigationNodes"
		data-update-order="<?= $this->validAction("/admin/navigation/updateOrder/".$navigation_id) ?>" 
		data-csrf-token="<?= session()->value("csrf") ?>"
	>
		<h2>Navigation nodes</h2>
		<p>Drag and drop nodes to reorder structure</p>

<?		if($item["nodes"]): ?>
		<!--ul class="nodes"-->
<?= 		recurseNodes($item["nodes"]); ?>
		<!--/ul-->
<?		else: ?>

		<p>No navigation nodes exists.</p>

<?		endif; ?>
	</div>

</div>