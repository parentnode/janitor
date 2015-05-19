<?php
global $action;
global $model;

$tags = $model->getTags();

$IC = new Items();
$IC->getItems(array("tags" => "brand:Google"));

//print_r($tags);
?>
<div class="scene defaultList tagList">
	<h1>Tags</h1>
	<p>
		Tags are used to index the content of the website and some tags are required for
		certain pages. You should NOT delete or edit tags, unless you know what you are doing.
	</p>
	<p>
		New tags should be created when editing your items.
	</p>

	<div class="all_items i:defaultList filters">
<?		if($tags): ?>
		<ul class="items">
<?			foreach($tags as $tag): ?>
			<li class="item tag_id:<?= $tag["id"] ?><?= !$tag["tag_count"] ? " unused" : "" ?>">
				<h3><?= $tag["context"] ?>:<?= $tag["value"] ?> <span class="count">(<?= pluralize($tag["tag_count"], "item", "items") ?>)</span></h3>
				
				<ul class="actions">
					<?= $HTML->link("Edit", "/janitor/admin/tag/edit/".$tag["id"], array("class" => "button", "wrapper" => "li.edit")) ?>
					<?= $JML->deleteButton("Delete", "/janitor/admin/tag/deleteTag/".$tag["id"]) ?>
				</ul>
			 </li>
<?			endforeach; ?>
		</ul>
<?		else: ?>
		<p>No tags.</p>
<?		endif; ?>
	</div>

</div>
