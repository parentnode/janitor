<?php

$action = $this->actions();

$TC = new Tag();
$tags = $TC->getTags();
?>
<div class="scene defaultList tagsList">
	<h1>Tags</h1>

	<ul class="list">
	<?php foreach($tags as $tag) { 
		?>

		<li><?= $tag["context"] ?>:<?= $tag["value"] ?> - <!--a href="/admin/cms/tags/delete/<?= $tag["id"] ?>">delete</a--></li>
	<? } ?>
	</ul>
</div>
