<?php

$items = false;
$title = "Related";

$base_path = HTML()->path;

$see_all_label = "(see all)";
$see_all_path = $base_path;

$list_class = "items articles articlePreviewList i:articlePreviewList";
$item_class = "item article";

$schema = "NewsArticle";


if($_options !== false) {
	foreach($_options as $_option => $_value) {
		switch($_option) {
			case "items"                 : $items                  = $_value; break;
			case "title"                 : $title                  = $_value; break;

			case "base_path"             : $base_path              = $_value; break;

			case "see_all_label"         : $see_all_label          = $_value; break;
			case "see_all_path"          : $see_all_path           = $_value; break;

			case "schema"                : $schema                 = $_value; break;

		}
	}
}


if($items):

?>
	<div class="related">
		<h2><?= $title ?> <a href="<?= $see_all_path ?>"><?= $see_all_label ?></a></h2>

		<ul class="<?= $list_class ?>">
<?		foreach($items as $item): 
			$media = items()->sliceMediae($item, "mediae"); ?>
			<li class="<?= $item_class ?>" itemscope itemtype="http://schema.org/<?= $schema ?>"<?= HTML()->jsData(["readstate"]) ?>>

				<?= HTML()->renderSnippet("snippets/media.php", [
					"item" => $item,
					"media" => $media,
				]) ?>


				<?= HTML()->renderSnippet("snippets/tags.php", [
					"item" => $item,
					"context" => [$item["itemtype"]],
					"default" => [HTML()->path, "Posts"]
				]) ?>


				<h3 itemprop="headline"><a href="<?= $base_path ?>/<?= $item["sindex"] ?>"><?= $item["name"] ?></a></h3>


				<?= HTML()->renderSnippet("snippets/info.php", [
					"item" => $item,
					"media" => $media,
				]) ?>


				<? if($item["description"]): ?>
				<div class="description" itemprop="description">
					<p><?= nl2br($item["description"]) ?></p>
				</div>
				<? endif; ?>

			</li>
	<?	endforeach; ?>
		</ul>
	</div>
<? endif; ?>