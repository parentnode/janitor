<?php
global $action;
global $IC;
global $model;
global $itemtype;


$options = [
	"limit" => 200,
	"pattern" => [
		"itemtype" => $itemtype, 
		"order" => "status DESC, published_at DESC", 
		"extend" => [
			"tags" => true, 
			"mediae" => true
		]
	]
];


$query = getVar("query");
if($query) {
	$options["query"] = $query;
}
$tags = getVar("tags");
if($tags) {
	$options["tags"] = $tags;
}

if(count($action) > 2) {
	if($action[1] === "page") {
		$options["page"] = $action[2];
	}
}


$items = $IC->paginate($options);
// debug(["items", $items]);

?>

<div class="scene i:scene defaultList <?= $itemtype ?>List">
	<h1>Posts</h1>

	<ul class="actions">
		<?= $JML->listNew(array("label" => "New post")) ?>
	</ul>

	<div class="all_items i:defaultList taggable filters images width:100"<?= $HTML->jsData(["tags", "search"], ["filter-tag-contexts" => "post,on"]) ?>>
<?		if($items): ?>

		<?= $HTML->pagination($items, [
			"base_url" => $HTML->path."/list",
			"query" => $query,
			"tags" => $tags,
		]) ?>

		<ul class="items">
<?			foreach($items["range_items"] as $item): ?>
			<li class="item item_id:<?= $item["id"] ?><?= $HTML->jsMedia($item) ?>">
				<h3><?= strip_tags($item["name"]) ?></h3>

				<?= $JML->tagList($item["tags"], ["context" => "post,on,blog"]) ?>

				<?= $JML->listActions($item) ?>
			 </li>
<?			endforeach; ?>
		</ul>

		<?= $HTML->pagination($items, [
			"base_url" => $HTML->path."/list",
			"query" => $query,
			"tags" => $tags,
		]) ?>

<?		else: ?>
		<p>No posts.</p>
<?		endif; ?>
	</div>

</div>
