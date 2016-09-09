<?php
global $action;
global $IC;
global $model;
global $itemtype;


$items = $IC->getItems(array("itemtype" => $itemtype, "where" => $itemtype.".starting_at > NOW()", "order" => "status DESC, ".$itemtype.".starting_at ASC", "extend" => array("tags" => true, "mediae" => true)));
$past_items = $IC->getItems(array("itemtype" => $itemtype, "where" => $itemtype.".starting_at < NOW()", "order" => "status DESC, ".$itemtype.".starting_at ASC", "extend" => array("tags" => true, "mediae" => true)));

?>
<div class="scene i:scene defaultList <?= $itemtype ?>List">
	<h1>Events</h1>

	<ul class="actions">
		<?= $JML->listNew(array("label" => "New event")) ?>
		<?= $HTML->link("Event hosts", "/janitor/admin/event/hosts", array("class" => "button", "wrapper" => "li.hosts")) ?>
		<?= $HTML->link("Event performers", "/janitor/admin/event/performers", array("class" => "button", "wrapper" => "li.performers")) ?>
	</ul>

	<div class="all_items i:defaultList taggable filters"<?= $JML->jsData() ?>>
<?		if($items || $past_items): ?>
		<ul class="items">

<?			if($items): ?>
<?			foreach($items as $item): ?>
			<li class="item item_id:<?= $item["id"] ?>">
				<h3><?= preg_replace("/<br>|<br \/>/", "", $item["name"]) ?></h3>
				<dl class="info">
					<dt>Starting</dt>
					<dd class="starting_at"><?= date("Y-m-d @ H:i", strtotime($item["starting_at"])) ?></dd>
				</dl>

				<?= $JML->tagList($item["tags"]) ?>

				<?= $JML->listActions($item) ?>
			 </li>
<?			endforeach; ?>
<?			endif; ?>

<?			if($past_items): ?>
<?			foreach($past_items as $item): ?>
			<li class="item item_id:<?= $item["id"] ?> past">
				<h3><?= preg_replace("/<br>|<br \/>/", "", $item["name"]) ?></h3>
				<dl class="info">
					<dt>Ended</dt>
					<dd class="starting_at"><?= date("Y-m-d @ H:i", strtotime($item["starting_at"])) ?></dd>
				</dl>

				<?= $JML->tagList($item["tags"]) ?>

				<?= $JML->listActions($item) ?>
			 </li>
<?			endforeach; ?>
<?			endif; ?>

		</ul>
<?		else: ?>
		<p>No events.</p>
<?		endif; ?>
	</div>

</div>
