<?php

$action = $this->actions();

$CC = new Shop();
$orders = $CC->getOrders();

// print_r($carts);

?>
<div class="scene defaultList orderList">
	<h1>Orders</h1>

	<ul class="actions">
		<li class="cart"><a href="/admin/shop/cart/list" class="button">Carts</a></li>
	</ul>

	<div class="all_items i:defaultList filters">
<?		if($orders): ?>
		<ul class="items">
<?			foreach($orders as $order): 
				//$item = $IC->getCompleteItem($item["id"]); 
				?>
			<li class="item">
				<h3><?= $order["order_no"] ?> (<?= pluralize(count($order["items"]), "item", "items" ) ?>)</h3>
				<dl class="details">
					<dt class="name">Name</dt>
					<dd class="name"><?= $order["delivery_name"] ?></dd>
					<dt class="price">Total price</dt>
					<dd class="price"><?= formatPrice($CC->getTotalOrderPrice($order["id"]), $order["currency"]) ?></dd>
					<dt class="email">Email</dt>
					<dd class="email"><?= $order["email"] ?></dd>
					<dt class="mobile">Mobile</dt>
					<dd class="mobile"><?= $order["mobile"] ?></dd>
				</dl>

				<ul class="actions">
					<li class="view"><a href="/admin/shop/order/view/<?= $order["id"] ?>" class="button">View</a></li>
					<!--li class="delete">
						<form action="/admin/order/deleteOrder/<?= $order["id"] ?>" class="i:formDefaultDelete" method="post" enctype="multipart/form-data">
							<input type="submit" value="Delete" class="button delete" />
						</form>
					</li-->
					<!--li class="status">
						<form action="/admin/cms/orders/<?= ($order["status"] == 1 ? "disable" : "enable") ?>/<?= $order["id"] ?>" class="i:formDefaultStatus" method="post" enctype="multipart/form-data">
							<input type="submit" value="<?= ($order["status"] == 1 ? "Disable" : "Enable") ?>" class="button status" />
						</form>
					</li-->
				</ul>
			 </li>
<?			endforeach; ?>
		</ul>
<?		else: ?>
		<p>No orders.</p>
<?		endif; ?>
	</div>

</div>
