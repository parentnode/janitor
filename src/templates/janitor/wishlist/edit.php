<?php
global $action;
global $IC;
global $model;
global $itemtype;

$model_wish = $IC->typeObject("wish");

$item_id = $action[1];
$item = $IC->getItem(array("id" => $item_id, "extend" => true));

// get wishes order
$ordered_wishes = $model->getOrderedWishes($item_id);


// reset "return to wishlist" state
//session()->reset("return_to_wishlist");

// remember wishlist to return to
session()->value("return_to_wishlist", $item_id);

?>
<div class="scene i:scene defaultEdit <?= $itemtype ?>Edit">
	<h1>Edit wishlist</h1>
	<h2><?= $item["name"] ?></h2>


	<ul class="actions i:defaultEditActions">
		<?= $model->link("Back to overview", "/janitor/admin/wish/list", array("class" => "button", "wrapper" => "li.cancel")); ?>
		<?= $model->link("New wish", "/janitor/admin/wish/new", array("class" => "button primary", "wrapper" => "li.new")); ?>
		<?= $JML->oneButtonForm("Delete wishlist", "/janitor/admin/wishlist/delete/".$item["id"], array(
			"wrapper" => "li.delete",
			"success-location" => "/janitor/admin/wish/list"
		)) ?>
	</ul>

	<div class="status i:defaultEditStatus item_id:<?= $item["id"]?>" data-csrf-token="<?= session()->value("csrf") ?>">
		<ul class="actions">
			<?= $JML->statusButton("Enable", "Disable", "/janitor/admin/wishlist/status", $item, array("js" => true)); ?>
		</ul>
	</div>


	<div class="item i:defaultEdit i:collapseHeader">
		<h2>Wishlist</h2>
		<?= $model->formStart("update/".$item["id"], array("class" => "labelstyle:inject")) ?>

			<fieldset>
				<?= $model->input("name", array("value" => $item["name"])) ?>
				<?= $model->input("classname", array("value" => $item["classname"])) ?>
			</fieldset>

			<ul class="actions">
				<?= $model->submit("Update", array("class" => "primary key:s", "wrapper" => "li.save")); ?>
			</ul>

		<?= $model->formEnd() ?>
	</div>


	<div class="wishes">
		<h2>Wishes</h2>
		<div class="all_items i:defaultList wishlist_id:<?= $item["id"]?> taggable filters sortable images width:100"
			data-csrf-token="<?= session()->value("csrf") ?>"
			data-item-order="<?= $this->validPath("/janitor/admin/wishlist/updateWishOrder/".$item["id"]) ?>"
			>
	<?		if($ordered_wishes): ?>
			<ul class="items">
				<? foreach($ordered_wishes as $item): ?>
				<li class="item item_id:<?= $item["id"] ?><?= $JML->jsMedia($item) ?>">
					<div class="drag"></div>
					<h3><?= $item["name"] ?></h3>
					<dl class="info">
						<dt class="reserved">Reserved</dt>
						<dd class="reserved"><?= $item["reserved"] ? ($item["reserved"] == 1 ? "Yes" : $item["reserved"]) : "No" ?></dd>
					</dl>

					<ul class="actions">
						<?= $model->link("Edit", "/janitor/admin/wish/edit/".$item["id"], array("class" => "button", "wrapper" => "li.edit")); ?>
						<?= $JML->oneButtonForm("Delete", "/janitor/admin/wish/delete/".$item["id"], array(
							"js" => true,
							"wrapper" => "li.delete",
							"static" => true
						)) ?>
						<?= $JML->statusButton("Enable", "Disable", "/janitor/admin/wish/status", $item, array("js" => true)); ?>
					</ul>

				 </li>
			 	<? endforeach; ?>
			</ul>
	<?		else: ?>
			<p>No wishes.</p>
	<?		endif; ?>
		</div>

	</div>


</div>
