<?php
global $action;
global $model;


$navigation_id = $action[1];
$item = $model->getNavigations(array("navigation_id" => $navigation_id));


function recurseNodes($nodes) {
	global $HTML;
	global $JML;

	$IC = new Items();

	$_ = "";
	$_ .= '<ul class="items">';

	foreach($nodes as $node) {

		// Validate internal links
		$link_validation_class = "";
		$valid_link = navigation()->validateNodeLink($node["link"]);
		if($valid_link && isset($valid_link["status"])) {
			$link_validation_class = "link_".$valid_link["status"];
		}


		$att_class = $HTML->attribute("class", "item draggable item_id:".$node["id"], $node["classname"], $link_validation_class);
		$_ .= '<li'.$att_class.'>';
		$_ .= '<div class="drag"></div>';
		$_ .= '<h3>'.$node["name"].'</h3>';


		if($node["link"]) {
			$_ .= '<span class="link">link: '.$node["link"].'</span>';
		}

		if($node["classname"]) {
			$_ .= '<span class="class">classname: '.$node["classname"].'</span>';
		}
		if($node["target"]) {
			$_ .= '<span class="target">target: '.$node["target"].'</span>';
		}
		if($node["fallback"]) {
			$_ .= '<span class="fallback">fallback: '.$node["fallback"].'</span>';
		}

		$_ .= '<ul class="actions">';
		$_ .= $HTML->link("Edit", "/janitor/admin/navigation/edit_node/".$node["id"], array("class" => "button", "wrapper" => "li.edit"));
		$_ .= $HTML->oneButtonForm("Delete", "/janitor/admin/navigation/deleteNode", array(
			"inputs" => ["node_id" => $node["id"]],
			"wrapper" => "li.delete"
		));
		$_ .= '</ul>';

		if($node["nodes"]) {
			$_ .= recurseNodes($node["nodes"]);
		}
		$_ .= '</li>';
	}
	$_ .= '</ul>';

	return $_;
}

?>
<div class="scene i:scene defaultEdit navigationEdit">
	<h1>Edit navigation</h1>
	<h2><?= $item["name"] ?></h2>

	<ul class="actions i:defaultEditActions">
		<?= $HTML->link("Navigations list", "/janitor/admin/navigation/list", array("class" => "button", "wrapper" => "li.cancel")) ?>
		<?= $HTML->link("New node", "/janitor/admin/navigation/new_node/".$navigation_id, array("class" => "button primary", "wrapper" => "li.cancel")) ?>

		<?= $HTML->oneButtonForm("Delete navigation", "/janitor/admin/navigation/delete", array(
			"wrapper" => "li.delete",
			"success-location" => "/janitor/admin/navigation/list",
			"inputs" => ["navigation_id" => $navigation_id],
		)) ?>
	</ul>

	<div class="item">
		<h2>Handle</h2>
		<p><?= $item["handle"] ?></p>
	</div>

	<div class="all_items sortable i:navigationNodes"
		data-item-order="<?= security()->validPath("/janitor/admin/navigation/updateOrder") ?>" 
		data-csrf-token="<?= session()->value("csrf") ?>"
		data-navigation-id="<?= $navigation_id ?>"
	>
		<h2>Navigation nodes</h2>

<?		if($item["nodes"]): ?>
		<p>Drag and drop nodes to reorder structure</p>
<?= 		recurseNodes($item["nodes"]); ?>
<?		else: ?>
		<p>No navigation nodes exists.</p>
<?		endif; ?>
	</div>

</div>