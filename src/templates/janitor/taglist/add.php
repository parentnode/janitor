<?php
global $action;

global $model;

$taglist_id = $action[1];

$tags = $model->getAllTags();
$taglist_tags = $model->getTaglistTags(["taglist_id" => $taglist_id]);
$taglist_tag_ids = [];

if($taglist_tags){

	foreach($taglist_tags as $taglist_tag) {
		$taglist_tag_ids[$taglist_tag["id"]] = true; // produces a key value pair array like $taglist_tag_ids[2=>true, 4=>true] only with the tags' ids of a particular taglist.
	}
}

//print_r($taglist_tag_ids);
?>

<div class="scene defaultList selectable taglistList">
<h1>Selectable Tags</h1>


<ul class="actions">
		<?= $HTML->link("Back", "/janitor/admin/taglist/edit/".$taglist_id, array("class" => "button", "wrapper" => "li.back")) ?>
</ul>

<div class="all_items i:defaultList i:taglist_tags filters"<?= $HTML->jsData(["order", "search"]) ?>>

<? if($tags): ?>

	<? foreach($tags as $tag): ?>
		<? $tag_id = $tag["id"]?>
		<ul class="items">
			<li class="item<?= isset($taglist_tag_ids[$tag_id]) ? " added" : "" ?>">
				<h3><?= strip_tags($tag["context"]) ?>:<?= strip_tags($tag["value"]) ?></h3>
				<ul class="actions">
					<?= $HTML->oneButtonForm("Add", "/janitor/admin/taglist/addTaglistTag/".$taglist_id."/".$tag_id, array(
					"confirm-value" => false,
					"wrapper" => "li.add",
					"class" => "primary",
					"success-function" => "added" 
					));?>

					<?= $HTML->oneButtonForm("Remove", "/janitor/admin/taglist/removeTaglistTag/".$taglist_id."/".$tag_id, array(
					"confirm-value" => "Confirm Removal",
					"wrapper" => "li.remove",
					"class" => "secondary",
					"success-function" => "removed"
					)) ?>
				</ul>
			</li>

		</ul>
	<? endforeach; ?>

<? else: ?>
	<p>No tag.</p>

<? endif; ?>


</div>

</div>
