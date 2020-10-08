<?php
global $action;
global $IC;
global $model;
global $itemtype;


// additional parameter
$expired = false;
if(count($action) == 2 && $action[1] == "expired") {
	$expired = true;
}

// get expired tickets
if($expired) {

	$items = $IC->getItems(array("itemtype" => $itemtype, "where" => $itemtype.".sale_closes < NOW()", "order" => "status DESC, sale_opens ASC", "extend" => array("tags" => true, "mediae" => true, "prices" => true)));

}
else {

	$items = $IC->getItems(array("itemtype" => $itemtype,"where" => $itemtype.".sale_closes > NOW()",  "order" => "status DESC, sale_opens ASC", "extend" => array("tags" => true, "mediae" => true, "prices" => true)));

}


$SC = new Shop();
?>

<div class="scene i:scene defaultList <?= $itemtype ?>List">
	<h1>Tickets</h1>

	<ul class="actions">
		<?= $JML->listNew(array("label" => "New ticket")) ?>
	</ul>

	<ul class="tabs">
		<?= $HTML->link("Tickets for sale", "/janitor/admin/ticket/list", array("wrapper" => "li.".(!$expired ? "selected" : ""))) ?>
		<?= $HTML->link("Expired tickets", "/janitor/admin/ticket/list/expired", array("wrapper" => "li.".($expired ? "selected" : ""))) ?>
	</ul>

	<div class="all_items i:defaultList filters"<?= $HTML->jsData(["tags", "search"]) ?>>
<?		if($items): ?>
		<ul class="items">
<?			foreach($items as $item): ?>
			<li class="item item_id:<?= $item["id"] ?>">
				<h3><?= strip_tags($item["name"]) ?></h3>

				<dl class="info">
					<dt class="sale_opens">Sale opens</dt>
					<dd class="sale_opens"><?= date("Y-m-d H:i", strtotime($item["sale_opens"])) ?></dd>
					<dt class="sale_closes">Sale closes</dt>
					<dd class="sale_closes"><?= date("Y-m-d H:i", strtotime($item["sale_closes"])) ?></dd>

					<dt class="sale_closes">Total tickets</dt>
					<dd class="sale_closes"><?= $item["total_tickets"] ?></dd>
					<dt class="tickets_sold">Tickets sold</dt>
					<dd class="tickets_sold"><?= $model->getSoldTickets($item["item_id"]) ?></dd>
					<dt class="tickets_reserved">Tickets reserved</dt>
					<dd class="tickets_reserved"><?= $model->getReservedTickets($item["item_id"]) ?></dd>
				</dl>

<? 			if($expired): ?>
				<?= $JML->listActions($item, [
					"modify" => [
						"delete" => false,
						"status" => false,
						"edit" => [
							"label" => "View",
							"url" => "/janitor/admin/ticket/view/".$item["id"]
						]
					]
				]) ?>
<?			else: ?>
				<?= $JML->listActions($item) ?>
<?			endif; ?>


			 </li>
<?			endforeach; ?>
		</ul>
<?		else: ?>
		<p>No tickets.</p>
<?		endif; ?>
	</div>

</div>
