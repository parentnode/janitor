<?php
global $action;

global $model;

$items = $model->getTaglists();
print_r($items);
?>

<div class="scene defaultList taglistList">
	<h1>Taglists</h1>

	<ul class="actions">
		<?= $JML->listNew(array("label" => "New taglist")) ?>
	</ul>

	<div class="all_items i:defaultList filters"<?= $JML->jsData(["order", "search"]) ?>>
<?		if($items): ?>
		<ul class="items">
<?			foreach($items as $item): ?>
			<li class="item item_id:<?= $item["id"] ?>">
				<h3><?= strip_tags($item["name"]) ?></h3>

				<?= $JML->listActions($item,  ["modify"=>[
					"delete"=>[
						"url"=>"/janitor/admin/taglist/deleteTaglist/".$item["id"]
					]
				]]) ?>
			 </li>
<?			endforeach; ?>
		</ul>
<?		else: ?>
		<p>No articles.</p>
<?		endif; ?>
	</div>

</div>
