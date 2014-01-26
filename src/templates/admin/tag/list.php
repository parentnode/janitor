<?php

$action = $this->actions();

$IC = new Item();
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
					<li class="edit"><a href="/admin/tag/edit/<?= $tag["id"] ?>" class="button">Edit</a></li>
					<li class="delete">
						<form action="/admin/cms/tag/delete/<?= $tag["id"] ?>" class="i:formDefaultDelete" method="post" enctype="multipart/form-data">
							<input type="submit" value="Delete" class="button delete" />
						</form>
					</li>
				</ul>
			 </li>
<?			endforeach; ?>
		</ul>
<?		else: ?>
		<p>No tags.</p>
<?		endif; ?>
	</div>

</div>
