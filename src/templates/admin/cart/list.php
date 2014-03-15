<?php
global $action;
global $model;

$IC = new Item();
$carts = $model->getCarts();

?>
<div class="scene defaultList cartList">
	<h1>Carts</h1>

	<ul class="actions">
		<li class="order"><a href="/admin/shop/order/list" class="button">Orders</a></li>
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
					<li class="view"><a href="/admin/shop/cart/view/<?= $cart["id"] ?>" class="button">View</a></li>
					<li class="delete">
						<form action="/admin/shop/deleteCart/<?= $cart["id"] ?>" class="i:formDefaultDelete" method="post" enctype="multipart/form-data">
							<input type="submit" value="Delete" class="button delete" />
						</form>
					</li>
					<!--li class="status">
						<form action="/admin/cms/cart/<?= ($cart["status"] == 1 ? "disable" : "enable") ?>/<?= $cart["id"] ?>" class="i:formDefaultStatus" method="post" enctype="multipart/form-data">
							<input type="submit" value="<?= ($cart["status"] == 1 ? "Disable" : "Enable") ?>" class="button status" />
						</form>
					</li-->
				</ul>
			 </li>
<?			endforeach; ?>
		</ul>
<?		else: ?>
		<p>No carts.</p>
<?		endif; ?>
	</div>

</div>
