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
<?
			foreach($navigations as $navigation):
				if($navigation["handle"] !== "main-janitor" || security()->validatePath("main-janitor")):
?>
			<li class="item item_id:<?= $navigation["id"] ?>">
				<h3><?= $navigation["name"] ?></h3>

				<?= $JML->listActions($navigation, [
					"modify" => [
						"delete" => [
							"inputs" => [
								"navigation_id" => $navigation["id"]
							]
						]
					]
				]);
?>
			 </li>
<?
				endif;
			endforeach; ?>
		</ul>
<?		else: ?>
		<p>No navigations.</p>
<?		endif; ?>
	</div>

</div>