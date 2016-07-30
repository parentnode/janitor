<?php
global $action;
global $model;

$IC = new Items();
$carts = $model->getCarts();

?>
<div class="scene i:scene defaultList cartList">
	<h1>Carts</h1>

	<ul class="actions">
		<?= $HTML->link("New cart", "/janitor/admin/shop/cart/new", array("class" => "button primary", "wrapper" => "li.new")) ?>
		<?= $HTML->link("Orders", "/janitor/admin/shop/order/list", array("class" => "button", "wrapper" => "li.order")) ?>
		<?= $HTML->link("Payments", "/janitor/admin/shop/payment/list", array("class" => "button", "wrapper" => "li.payment")) ?>
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
					<?= $HTML->link("View", "/janitor/admin/shop/cart/view/".$cart["id"], array("class" => "button", "wrapper" => "li.view")) ?>
					<?= $HTML->deleteButton("Delete", "/janitor/admin/shop/deleteCart/".$cart["id"]) ?>
				</ul>
			 </li>
<?			endforeach; ?>
		</ul>
<?		else: ?>
		<p>No carts.</p>
<?		endif; ?>
	</div>

</div>
