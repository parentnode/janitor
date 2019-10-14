<?php
global $action;
global $IC;
global $model;
global $itemtype;

$items = $model->getLocations();

?>
<div class="scene i:scene defaultList <?= $itemtype ?>LocationList">
	<h1>Event locations</h1>

	<ul class="actions">
		<?= $JML->listNew(array("label" => "New location", "action" => "location-new")) ?>
		<?= $HTML->link("Events", "/janitor/admin/event/list", array("class" => "button", "wrapper" => "li.events")) ?>
	</ul>

	<div class="all_items i:defaultList filters"<?= $JML->jsData(["search"]) ?>>
<?		if($items): ?>
		<ul class="items">

<?			foreach($items as $item): ?>
			<li class="item item_id:<?= $item["id"] ?>">
				<h3><?= strip_tags($item["location"]) ?></h3>

				<?= $JML->listActions($item, array("modify" => array(
					"status" => false,
					"edit" => array(
						"url" => $JML->path."/location-edit/".$item["id"]
					),
					"delete" => array(
						"url" => $JML->path."/deleteLocation/".$item["id"]
					)
				))) ?>
			 </li>
<?			endforeach; ?>

		</ul>
<?		else: ?>
		<p>No locations.</p>
<?		endif; ?>
	</div>

</div>
