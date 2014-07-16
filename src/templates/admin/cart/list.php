<?php
global $action;
global $model;

$IC = new Item();
$carts = $model->getCarts(array("status" => 1));

?>
<div class="scene defaultList cartList">
	<h1>Carts</h1>

	<ul class="actions">
		<?= $HTML->link("Orders", "/admin/shop/order/list", array("class" => "button", "wrapper" => "li.order")) ?>
	</ul>

	<div class="all_items i:defaultList filters">
<?		if($carts): ?>
		<ul class="items">
<?			foreach($carts as $cart): ?>
			<li class="item">
				<h3><?= $cart["modified_at"] ?> (<?= pluralize(count($cart["items"]), "item", "items" ) ?>)</h3>

<?				if($cart["items"]): ?>
				<ul class="products">
<?					foreach($cart["items"] as $product): 
						$product = $IC->getCompleteItem(array("id" => $product["item_id"])); ?>
					<li class="product"><?= $product["name"] ?>, <?= $product["ean"] ?></li>
<?					endforeach; ?>
				</ul>
<?				endif; ?>

				<ul class="actions">
					<?= $HTML->link("View", "/admin/shop/cart/view/".$cart["id"], array("class" => "button", "wrapper" => "li.view")) ?>
					<?= $HTML->deleteButton("Delete", "/admin/shop/deleteCart/".$cart["id"]) ?>
				</ul>
			 </li>
<?			endforeach; ?>
		</ul>
<?		else: ?>
		<p>No carts.</p>
<?		endif; ?>
	</div>

</div>
