<?php
global $action;
global $model;

$navigations = $model->getNavigations(array("levels" => 0));

?>
<div class="scene i:scene defaultList navigationsList">
	<h1>Navigations</h1>

	<ul class="actions">
		<?= $JML->listNew(array("label" => "New navigation")) ?>
	</ul>


	<div class="all_items i:defaultList filters"<?= $HTML->jsData(["search"]) ?>>
<?		if($navigations): ?>
		<ul class="items">
<?			foreach($navigations as $item): ?>
			<li class="item item_id:<?= $item["id"] ?>">
				<h3><?= $item["name"] ?></h3>

				<?= $JML->listActions($item) ?>
			 </li>
<?			endforeach; ?>
		</ul>
<?		else: ?>
		<p>No navigations.</p>
<?		endif; ?>
	</div>

</div>