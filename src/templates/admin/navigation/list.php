<?php
global $action;
global $model;

$navigations = $model->getNavigations(array("levels" => 0));

?>
<div class="scene defaultList navigationsList">
	<h1>Navigations</h1>

	<ul class="actions">
		<?= $HTML->link("New navigation", "/admin/navigation/new", array("class" => "button primary", "wrapper" => "li.new")) ?>
	</ul>


	<div class="all_items i:defaultList filters"
		data-csrf-token="<?= session()->value("csrf") ?>"
		>
<?		if($navigations): ?>
		<ul class="items">
<?			foreach($navigations as $item): ?>
			<li class="item item_id:<?= $item["id"] ?>">
				<h3><?= $item["name"] ?></h3>

				<ul class="actions">
					<?= $HTML->link("Edit", "/admin/navigation/edit/".$item["id"], array("class" => "button", "wrapper" => "li.edit")) ?>
					<?= $HTML->deleteButton("Delete", "/admin/navigation/delete/".$item["id"], array("js" => true)) ?>
				</ul>
			 </li>
<?			endforeach; ?>
		</ul>
<?		else: ?>
		<p>No navigations.</p>
<?		endif; ?>
	</div>

</div>