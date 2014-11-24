<?php
global $action;

$IC = new Items();
$itemtype = "post";


// get post tags for listing
$post_tags = $IC->getTags(array("context" => $itemtype));


// get content pagination
include_once("classes/items/pagination.class.php");
$PC = new Pagination();

$limit = stringOr(getVar("limit"), 5);
$sindex = isset($action[0]) ? $action[0] : false;
$direction = isset($action[1]) ? $action[1] : false; 

$pattern = array("itemtype" => $itemtype, "status" => 1);
$pagination = $PC->paginate(array("pattern" => $pattern, "sindex" => $sindex, "limit" => $limit, "direction" => $direction));

?>

<div class="scene posts i:generic">
	<h1>Janitor is alive</h1>

<?	if($post_tags): ?>
	<div class="categories">
		<h2>Categories</h2>
		<ul class="tags">
<?		foreach($post_tags as $tag): ?>
			<li><a href="/index/tag/<?= urlencode($tag["value"]) ?>"><?= $tag["value"] ?></a></li>
<?		endforeach; ?>
		</ul>
	</div>
<?	endif; ?>

<? if($pagination["range_items"]): ?>
	<ul class="postings">
<?		foreach($pagination["range_items"] as $item):
			$item = $IC->extendItem($item, array("tags" => true));
			$hardlink = (isset($_SERVER["HTTPS"]) ? "https" : "http")."://".$_SERVER["SERVER_NAME"]."/blog/".$item["sindex"];
			$media = $item["mediae"] ? array_shift($item["mediae"]) : false; ?>
		<li class="item post id:<?= $item["item_id"] ?>" itemscope itemtype="http://schema.org/Article">

<?			if($media): ?>
			<div class="image image_id:<?= $item["item_id"] ?> format:<?= $media["format"] ?> variant:<?= $media["variant"] ?>"></div>
<?			endif; ?>

			<ul class="tags">
				<li><a href="/">Posts</a></li>
<?			if($item["tags"]): ?>
<?				foreach($item["tags"] as $item_tag): ?>
<?	 				if($item_tag["context"] == $itemtype): ?>
				<li><a href="/blog/tag/<?= urlencode($item_tag["value"]) ?>" itemprop="articleSection"><?= $item_tag["value"] ?></a></li>
<?					endif; ?>
<?				endforeach; ?>
<?			endif; ?>
			</ul>

			<h2 itemprop="name"><?= $item["name"] ?></h2>

			<dl class="info">
				<dt class="published_at">Date published</dt>
				<dd class="published_at" itemprop="datePublished" content="2015-07-27"><?= date("Y-m-d, H:i", strtotime($item["published_at"])) ?></dd>
				<dt class="author">Author</dt>
				<dd class="author" itemprop="author">Martin Kæstel Nielsen</dd>
				<dt class="hardlink">Hardlink</dt>
				<dd class="hardlink" itemprop="url"><a href="<?= $hardlink ?>" target="_blank"><?= $hardlink ?></a></dd>
			</dl>

			<div class="description" itemprop="articleBody">
				<?= $item["html"] ?>
			</div>

<?			if($item["mediae"]):
				foreach($item["mediae"] as $media): ?>
			<div class="image image_id:<?= $item["item_id"] ?> format:<?= $media["format"] ?> variant:<?= $media["variant"] ?>"></div>
<? 				endforeach;
			endif; ?>

		</li>
<?		endforeach; ?>
	</ul>
<? endif; ?>


<? if($pagination["next"] || $pagination["prev"]): ?>
	<div class="pagination">
		<ul class="actions">
<? if($pagination["prev"]): ?><li class="previous"><a href="/index/<?= $pagination["first_sindex"] ?>/prev">Previous page</a></li><? endif; ?>
<? if($pagination["next"]): ?><li class="next"><a href="/index/<?= $pagination["last_sindex"] ?>/next">Next page</a></li><? endif; ?>
		</ul>
	</div>
<? endif; ?>


</div>
