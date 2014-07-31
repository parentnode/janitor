<?php
global $IC;
global $action;

$itemtype = "post";

$count = stringOr(getVar("count"), 5);

// get post tags for listing
$post_tags = $IC->getTags(array("context" => $itemtype));


// get all items as base
$items = $IC->getItems(array("itemtype" => $itemtype, "status" => 1));

# /blog - lists the latest N posts and prev button
if(!isset($action[0])) {

	$range_items = $IC->getItems(array("itemtype" => $itemtype, "status" => 1, "limit" => $count));
}

# /blog/#sindex#[/prev|next]
else if(isset($action[0])) {

	$item_id = $IC->getIdFromSindex($action[0]);

	# /blog/#sindex#/next - Lists the next N posts after sindex
	if(isset($action[1]) && $action[1] == "next") {

		$range_items = $IC->getNext($item_id, array("items" => $items, "count" => $count));		
	}
	# /blog/#sindex#/prev - Lists the prev N posts before sindex
	else if(isset($action[1]) && $action[1] == "prev") {

		$range_items = $IC->getPrev($item_id, array("items" => $items, "count" => $count));
	}
	# /blog/#sindex# - Lists the next N posts starting with sindex
	else {

		$item = $IC->getItem(array("id" => $item_id));
		$range_items = $IC->getNext($item_id, array("items" => $items, "count" => $count-1));

		array_unshift($range_items, $item);
	}

}

// find indexes and ids for next/prev
$first_id = isset($range_items[0]) ? $range_items[0]["id"] : false;
$first_sindex = isset($range_items[0]) ? $range_items[0]["sindex"] : false;
$last_id = isset($range_items[count($range_items)-1]) ? $range_items[count($range_items)-1]["id"] : false;
$last_sindex = isset($range_items[count($range_items)-1]) ? $range_items[count($range_items)-1]["sindex"] : false;

// look for next/prev item availability
$next = $last_id ? $IC->getNext($last_id, array("items" => $items)) : false;
$prev = $first_id ? $IC->getPrev($first_id, array("items" => $items)) : false;

?>

<div class="scene posts i:generic">
	<h1>Janitor is alive</h1>

	<div class="categories">
<?	if($post_tags): ?>
		<h2>Categories</h2>
		<ul class="tags">
<?		foreach($post_tags as $tag): ?>
			<li><a href="/blog/tag/<?= urlencode($tag["value"]) ?>"><?= $tag["value"] ?></a></li>
<?		endforeach; ?>
		</ul>
<?	endif; ?>
	</div>

<? if($range_items): ?>
	<ul class="postings">
<?		foreach($range_items as $item):
			$item = $IC->extendItem($item, array("tags" => true)); ?>
		<li class="item post id:<?= $item["item_id"] ?>" itemscope itemtype="http://schema.org/Article">

<?			if($item["mediae"]): ?>
			<div class="image image_id:<?= $item["item_id"] ?> format:<?= $item["mediae"][0]["format"] ?> variant:<?= $item["mediae"][0]["variant"] ?>"></div>
<?			endif; ?>

			<h2 itemprop="name"><?= $item["name"] ?></h2>

			<dl class="info">
				<dt class="published_at">Date published</dt>
				<dd class="published_at" itemprop="datePublished" content="2015-07-27"><?= date("Y-m-d, H:i", strtotime($item["published_at"])) ?></dd>
				<dt class="author">Author</dt>
				<dd class="author" itemprop="author">Martin Kæstel Nielsen</dd>
			</dl>

			<div class="description" itemprop="articleBody">
				<?= $item["html"] ?>
			</div>

<?			if(count($item["mediae"]) > 1):
				array_shift($item["mediae"]);
				foreach($item["mediae"] as $media): ?>
			<div class="image image_id:<?= $item["item_id"] ?> format:<?= $media["format"] ?> variant:<?= $media["variant"] ?>"></div>
<? 				endforeach;
			endif; ?>

		</li>
<?		endforeach; ?>
	</ul>
<? endif; ?>


<? if($next || $prev): ?>
	<div class="pagination">
		<ul class="actions">
<? if($prev): ?><li class="previous"><a href="/blog/<?= $first_sindex ?>/prev">Previous page</a></li><? endif; ?>
<? if($next): ?><li class="next"><a href="/blog/<?= $last_sindex ?>/next">Next page</a></li><? endif; ?>
		</ul>
	</div>
<? endif; ?>


</div>
