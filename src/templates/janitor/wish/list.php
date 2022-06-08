<?php
global $action;
global $IC;
global $model;
global $itemtype;

// get wishlists
//$wishlists = $IC->getTags(array("context" => "wishlist", "order" => "value"));
$wishlists = $IC->getItems(array("itemtype" => "wishlist", "order" => "position ASC", "extend" => array("tags" => true)));

// get all wishes for complete overview
$wishes = $IC->getItems(array("itemtype" => $itemtype, "order" => "status DESC, wish.name ASC", "extend" => array("tags" => true, "mediae" => true)));

// reset "return to wishlist" state
session()->reset("return_to_wishlist");
?>
<div class="scene i:scene defaultList <?= $itemtype ?>List">
	<h1>Wishes and wishlists</h1>

	<ul class="actions">
		<?= $JML->listNew(array("label" => "New wish")) ?>
		<?= $HTML->link("New wishlist", "/janitor/admin/wishlist/new", array("class" => "button primary", "wrapper" => "li.wishlist")) ?>
	</ul>

	<div class="wishlists">
		<h2>Wishlists</h2>
		<div class="all_items i:defaultList filters sortable"
			data-csrf-token="<?= session()->value("csrf") ?>"
			data-item-order="<?= security()->validPath("/janitor/admin/wishlist/updateOrder") ?>"
		>
	<?		if($wishlists): ?>
			<ul class="items">
	<?			foreach($wishlists as $wishlist):
					$tag_index = arrayKeyValue($wishlist["tags"], "context", "wishlist");
					if($tag_index !== false) {
						$tag = "wishlist:".addslashes($wishlist["tags"][$tag_index]["value"]);
						$wishlist_wishes = $IC->getItems(array("itemtype" => "wish", "tags" => $tag));
					}
					// create tag if wishlist doesnt have tag already
					else {
						$wishlist_wishes = array();
					}
	 ?>
				<li class="item item_id:<?= $wishlist["id"] ?>">
					<h3><?= $wishlist["name"] ?> (<?= count($wishlist_wishes) ?> wishes)</h3>
					<ul class="actions">
						<?= $model->link("View", "/janitor/admin/wishlist/edit/".$wishlist["id"], array("class" => "button primary", "wrapper" => "li.edit")); ?>
						<?= $model->oneButtonForm("Delete", "/janitor/admin/wishlist/delete/".$wishlist["id"], array(
							"js" => true,
							"wrapper" => "li.delete",
							"static" => true
						)) ?>
						<?= $JML->statusButton("Enable", "Disable", "/janitor/admin/wishlist/status", $wishlist, array("js" => true)); ?>
					</ul>
				</li>
	<?			endforeach; ?>
			</ul>
	<?		else: ?>
			<p>No wishes.</p>
	<?		endif; ?>
		</div>
	</div>


	<div class="wishes">
		<h2>All wishes</h2>
		<div class="all_items i:defaultList taggable filters images width:100"<?= $HTML->jsData(["tags", "search"]) ?>>
	<?		if($wishes): ?>
			<ul class="items">
	<?			foreach($wishes as $item): ?>
				<li class="item item_id:<?= $item["id"] ?><?= $HTML->jsMedia($item) ?>">
					<h3><?= $item["name"] ?></h3>
					<dl class="info">
						<dt class="reserved">Reserved</dt>
						<dd class="reserved"><?= $item["reserved"] ? ($item["reserved"] == 1 ? "Yes" : $item["reserved"]) : "No" ?></dd>
					</dl>

					<?= $JML->tagList($item["tags"]) ?>

					<?= $JML->listActions($item) ?>
				 </li>
	<?			endforeach; ?>
			</ul>
	<?		else: ?>
			<p>No wishes.</p>
	<?		endif; ?>
		</div>
	</div>
</div>
