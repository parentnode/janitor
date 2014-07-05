<?php
global $action;
global $IC;

$tags = $IC->getTags();
?>
<div class="scene defaultList tagList">
	<h1>Tags</h1>
	<p>
		Tags are used to index the content of the website and some tags are required for
		certain pages. You should NOT delete or edit tags, unless you know what you are doing.
	</p>

	<div class="all_items i:defaultList filters">
<?		if($tags): ?>
		<ul class="items">
<?			foreach($tags as $tag): ?>
			<li class="item tag_id:<?= $tag["id"] ?>">
				<h3><?= $tag["context"] ?>:<?= $tag["value"] ?></h3>
				
				<ul class="actions">
					<?= $HTML->link("Edit", "/admin/tag/edit/".$tag["id"], array("class" => "button", "wrapper" => "li.edit")) ?>
					<?= $HTML->deleteButton("Delete", "/admin/tag/globalDeleteTag/".$tag["id"]) ?>
				</ul>
			 </li>
<?			endforeach; ?>
		</ul>
<?		else: ?>
		<p>No tags.</p>
<?		endif; ?>
	</div>

</div>
