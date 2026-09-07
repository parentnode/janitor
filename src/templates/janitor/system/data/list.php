<?php
global $action;
global $model;

systemdata()->getDatasets();

// $datasets = filesystem()->files(LOCAL_PATH."/templates/janitor/system/data", [
// 	"allow_extensions" => "php",
// ]);
?>
<div class="scene i:scene defaultList dataList">
	<h1>System datasets</h1>

	<div class="all_items i:defaultList filters">
<?	if($datasets): ?>
		<ul class="items">
<?
		foreach($datasets as $dataset):
			// to enable access permissions, create local system data controller with access items, 
			// and be sure to disable access to /janitor/admin/system controller
			if(security()->validatePath("data/".$dataset["filename"])):
?>
			<li class="item">
				<h3><?= (isset($dataset["name"]) ? $dataset["name"] : $dataset["filename"]) ?></h3>

				<?= $JML->listActions(["id" => ""], [
					"modify" => [
						"delete" => false,
						"status" => false,
						"edit" => [
							"url" => "data/".$dataset["filename"],
						]
					]
				]);
?>
			 </li>
<?
			endif;
		endforeach; ?>
		</ul>
<?	else: ?>
		<p>No datasets.</p>
<?	endif; ?>
	</div>

</div>