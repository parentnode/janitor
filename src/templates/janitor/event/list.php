<?php
global $action;
global $IC;
global $model;
global $itemtype;

// additional parameter
$past_events = false;
if(count($action) == 2 && $action[1] == "past") {
	$past_events = "past";
}


// get past events
if($past_events) {
	$items = $IC->getItems(array("itemtype" => $itemtype, "where" => $itemtype.".starting_at < NOW()", "order" => "status DESC, ".$itemtype.".starting_at DESC", "extend" => array("tags" => true, "mediae" => true)));	
}
// get upcoming events
else {
	$items = $IC->getItems(array("itemtype" => $itemtype, "where" => $itemtype.".starting_at > NOW()", "order" => "status DESC, ".$itemtype.".starting_at ASC", "extend" => array("tags" => true, "mediae" => true)));
}


?>
<div class="scene i:scene defaultList <?= $itemtype ?>List">
	<h1>Events</h1>

	<ul class="actions">
		<?= $JML->listNew(array("label" => "New event")) ?>
		<?= $HTML->link("Event hosts", "/janitor/admin/event/hosts", array("class" => "button", "wrapper" => "li.hosts")) ?>
		<?= $HTML->link("Event performers", "/janitor/admin/event/performers", array("class" => "button", "wrapper" => "li.performers")) ?>
	</ul>


	<ul class="tabs">
		<?= $HTML->link("Upcoming events", "/janitor/admin/event/list", array("wrapper" => "li.".(!$past_events ? "selected" : ""))) ?>
		<?= $HTML->link("Past events", "/janitor/admin/event/list/past", array("wrapper" => "li.".($past_events ? "selected" : ""))) ?>
	</ul>


	<div class="all_items i:defaultList taggable filters"<?= $JML->jsData() ?>>
<?		if($items): ?>
		<ul class="items">

<?			if($items): ?>
<?			foreach($items as $item): ?>
			<li class="item item_id:<?= $item["id"] ?>">
				<h3><?= strip_tags($item["name"]) ?></h3>
				<dl class="info">
					<dt>Starting</dt>
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
