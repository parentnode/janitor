<?php
global $action;
global $model;


$navigation_id = $action[1];
$item = $model->getNavigations(array("navigation_id" => $navigation_id));

// global $indent;
// $indent = 0;


function recurseNodes($nodes) {
	global $HTML;
	global $JML;
//	global $indent;

	$IC = new Items();

	$_ = "";
	$_ .= '<ul class="items">';

	foreach($nodes as $node) {

		$att_class = $HTML->attribute("class", "item draggable node_id:".$node["id"], $node["classname"]);
		$_ .= '<li'.$att_class.'>';
		$_ .= '<div class="drag"></div>';
		$_ .= '<h3>'.$node["name"].'</h3>';

		if($node["link"]) {
			$_ .= '<span class="link">'.$node["link"].'</span>';
		}
// 		if($node["item_id"]) {
// 			$pageitem = $IC->getItem(array("id" => $node["item_id"], "extend" => true));
// //			$item_page = $IC->extendItem($item_page);
// 			$_ .= '<span class="page"><a href="/janitor/page/edit/'.$pageitem["item_id"].'">'.$pageitem["name"].'</a></span>,';
// 			$_ .= '<span class="controller">'.$node["controller"].'</span>';
// 		}

		if($node["classname"]) {
			$_ .= '<span class="class">"'.$node["classname"].'"</span>';
		}
		if($node["target"]) {
			$_ .= '<span class="target">'.$node["target"].'</span>';
		}
		if($node["fallback"]) {
			$_ .= '<span class="fallback">('.$node["fallback"].')</span>';
		}

		$_ .= '<ul class="actions">';
		$_ .= $HTML->link("Edit", "/janitor/admin/navigation/edit_node/".$node["id"], array("class" => "button", "wrapper" => "li.edit"));
		$_ .= $JML->oneButtonForm("Delete", "/janitor/admin/navigation/deleteNode/".$node["id"], array(
			"wrapper" => "li.delete"
		));
		$_ .= '</ul>';

		if($node["nodes"]) {
//			$indent++;
			$_ .= recurseNodes($node["nodes"]);
//			$indent--;
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

		<?= $JML->oneButtonForm("Delete me", "/janitor/admin/navigation/delete/".$navigation_id, array(
			"wrapper" => "li.delete",
			"success-location" => "/janitor/admin/navigation/list"
		)) ?>
	</ul>

	<div class="item">
		<h2>Handle</h2>
		<p><?= $item["handle"] ?></p>
	</div>

	<div class="all_items sortable i:navigationNodes"
		data-item-order="<?= $this->validPath("/janitor/admin/navigation/updateOrder/".$navigation_id) ?>" 
		data-csrf-token="<?= session()->value("csrf") ?>"
	>
		<h2>Navigation nodes</h2>

<?		if($item["nodes"]): ?>
		<p>Drag and drop nodes to reorder structure</p>
		<!--ul class="nodes"-->
<?= 		recurseNodes($item["nodes"]); ?>
		<!--/ul-->
<?		else: ?>

		<p>No navigation nodes exists.</p>

<?		endif; ?>
	</div>

</div>