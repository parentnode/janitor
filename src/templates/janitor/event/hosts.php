<?php
global $action;
global $IC;
global $model;
global $itemtype;

$items = $model->getHosts();

?>
<div class="scene i:scene defaultList <?= $itemtype ?>HostList">
	<h1>Event hosts</h1>

	<ul class="actions">
		<?= $JML->listNew(array("label" => "New host", "action" => "new_host")) ?>
		<?= $HTML->link("Events", "/janitor/admin/event/list", array("class" => "button", "wrapper" => "li.events")) ?>
	</ul>

	<div class="all_items i:defaultList filters"<?= $JML->jsData() ?>>
<?		if($items): ?>
		<ul class="items">

<?			foreach($items as $item): ?>
			<li class="item item_id:<?= $item["id"] ?>">
				<h3><?= strip_tags($item["host"]) ?></h3>

				<?= $JML->listActions($item, array("modify" => array(
					"status" => false,
					"edit" => array(
						"url" => $JML->path."/edit_host/".$item["id"]
					),
					"delete" => array(
						"url" => $JML->path."/deleteHost/".$item["id"]
					)
				))) ?>
			 </li>
<?			endforeach; ?>

		</ul>
<?		else: ?>
		<p>No hosts.</p>
<?		endif; ?>
	</div>

</div>
