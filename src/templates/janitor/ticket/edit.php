<?php
global $action;
global $IC;
global $model;
global $itemtype;

$item_id = $action[1];
$item = $IC->getItem(array("id" => $item_id, "extend" => array("tags" => true, "mediae" => true, "prices" => true, "comments" => true)));

$messages = $IC->getItems(array("itemtype" => "message", "tags" => "message:Ticket", "extend" => true));

$participants = $model->getParticipants($item_id);
?>
<div class="scene i:scene defaultEdit <?= $itemtype ?>Edit">
	<h1>Edit ticket</h1>
	<h2><?= strip_tags($item["name"]) ?></h2>

	<?= $JML->editGlobalActions($item) ?>

	<?= $JML->editSingleMedia($item, array("label" => "Main ticket image")) ?>

	<div class="item i:defaultEdit i:collapseHeader">
		<h2>Ticket content</h2>
		<?= $model->formStart("update/".$item["id"], array("class" => "labelstyle:inject")) ?>

			<fieldset>
				<?= $model->input("name", array("value" => $item["name"])) ?>
				<?= $model->input("description", array("class" => "autoexpand short", "value" => $item["description"])) ?>
				<?= $model->input("html", array("value" => $item["html"])) ?>
			</fieldset>

			<fieldset>
				<h3>Mail and additional information</h3>
				<?= $model->input("ordered_message_id", array("type" => "select", "options" => $HTML->toOptions($messages, "id", "name", ["add" => ["" => "Choose message"]]), "value" => $item["ordered_message_id"])) ?>
				<?= $model->input("mail_information", array("value" => $item["mail_information"])) ?>
				<?= $model->input("ticket_information", array("value" => $item["ticket_information"])) ?>
			</fieldset>

			<fieldset>
				<h3>Ticket sales</h3>
				<?= $model->input("sale_opens", array("value" => $item["sale_opens"])) ?>
				<?= $model->input("sale_closes", array("value" => $item["sale_closes"])) ?>
				<?= $model->input("total_tickets", array("value" => $item["total_tickets"])) ?>
			</fieldset>

			<?= $JML->editActions($item) ?>

		<?= $model->formEnd() ?>
	</div>

	<?= $JML->editPrices($item) ?>

	<?= $JML->editTicketEvent($item) ?>

	<?= $JML->editEditors($item) ?>


	<div class="participants i:defaultParticipants i:collapseHeader item_id:<?= $item["id"] ?>">
		<h2>Participants (<?= $participants && $participants["paid"] ? count($participants["paid"]) : "0" ?>)</h2>

		<ul class="actions">
			<?= $HTML->oneButtonForm("Download participant list", "downloadParticipantList/".$item["id"], [
				"confirm-value" => false,
				"dom-submit" => true,
				"download" => true,
			]) ?>
		</ul>

		<div class="all_items paid">
			<h3>Paid tickets</h3>
		<? if($participants && $participants["paid"]): ?>
			<ul class="participants paid items">
				<? foreach($participants["paid"] as $participant): ?>
					<li class="item participant">
						<span class="ticket_no"><?= $participant["ticket_no"] ?></span>
						<span class="unit_price"><?= formatPrice(["price" => $participant["unit_price"], "currency" => $participant["currency"]]) ?></span>
						<span class="name"><?= $participant["nickname"] ?>, <?= $participant["username"] ?></span>
						<ul class="actions">
							<?= $HTML->link("Order", "/janitor/admin/shop/order/edit/".$participant["order_id"], array("wrapper" => "li.order", "class" => "button")) ?>
							<?= $HTML->oneButtonForm("Re-send ticket", "reIssueTicket/".$participant["ticket_no"]) ?>
							<? //= $HTML->oneButtonForm("Refund ticket", "refundTicket/".$participant["ticket_no"], ["class" => "secondary"]) ?>
						</ul>
					</li>
				<? endforeach; ?>
			</ul>
		<? else: ?>
			<p>No tickets have been ordered and paid.</p>
		<? endif; ?>
		</div>

		<div class="all_items unpaid">
			<h3>Yet unpaid tickets</h3>
		<? if($participants && isset($participants["unpaid"])): ?>
			<ul class="participants unpaid items">
				<? foreach($participants["unpaid"] as $participant): ?>
					<li class="item participant">
						<span class="ticket_no"><?= $participant["ticket_no"] ?></span>
						<span class="unit_price"><?= formatPrice(["price" => $participant["unit_price"], "currency" => $participant["currency"]]) ?></span>
						<span class="name"><?= $participant["nickname"] ?>, <?= $participant["username"] ?></span>
						<ul class="actions">
							<?= $HTML->link("Order", "/janitor/admin/shop/order/edit/".$participant["order_id"], array("wrapper" => "li.order", "class" => "button")) ?>
						</ul>
					</li>
				<? endforeach; ?>
			</ul>
		<? else: ?>
			<p>No tickets have been ordered, but not paid yet.</p>
		<? endif; ?>
		</div>

	</div>

	<?= $JML->editComments($item) ?>

	<?= $JML->editSindex($item) ?>

	<?= $JML->editDeveloperSettings($item) ?>

	<?= $JML->editOwner($item) ?>

</div>
