<?php
global $action;
global $model;

$datasets = filesystem()->files(LOCAL_PATH."/templates/janitor/system/data", [
	"allow_extensions" => "php",
]);
?>
<div class="scene i:scene defaultList dataList">
	<h1>System datasets</h1>

	<div class="all_items i:defaultList filters">
<?		if($datasets): ?>
		<ul class="items">
<?
			foreach($datasets as $dataset):
				if($dataset):
					$filename = str_replace(".php", "", basename($dataset));
					if($filename && security()->validatePath("data/".$filename)):
?>
			<li class="item">
				<h3><?= $filename ?></h3>

				<?= $JML->listActions(["id" => ""], [
					"modify" => [
						"delete" => false,
						"status" => false,
						"edit" => [
							"url" => "data/".$filename,
						]
					]
				]);
?>
			 </li>
<?
					endif;
				endif;
			endforeach; ?>
		</ul>
<?		else: ?>
		<p>No datasets.</p>
<?		endif; ?>
	</div>

</div>