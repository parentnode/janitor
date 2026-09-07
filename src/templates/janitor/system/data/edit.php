<?php
global $action;
global $model;

$id = $action[1];

$dataset = $model->getDataset($id);

// $data = array_merge($data_model, $data);
// debug([$dataset]);
$name = isset($dataset["name"]) ? "Edit ".$dataset["name"] : "Edit $id data";
$description = isset($dataset["description"]) ? $dataset["description"] : "";

$class = (isset($dataset["class"]) ? $dataset["class"] : $id);
?>
<div class="scene i:systemData defaultEdit dataEdit">
	<h1><?= $name ?></h1>

<?	if($description): ?>
	<p><?= $description ?></p>
<?	endif; ?>

<?	if(isset($dataset["pages"])): ?>
	<div class="pages all_items i:defaultList">
		<h2>Pages</h2>
		<ul class="items">
<?		foreach($dataset["pages"] as $page): ?>
			<li class="item page"><?= debug($page) ?></li>
<?		endforeach; ?>
		</ul>
	</div>
<?	endif; ?>

	<div class="item i:defaultEdit">
		<h2>Data, phrases and files</h2>

		<?= $model->formStart("updateData", ["class" => "labelstyle:inject ".$class]) ?>
			<?= $model->input("id", ["type" => "hidden", "value" => $id]) ?>

			<fieldset>
<?			foreach($dataset["model"] as $name => $properties): ?>
				<?= $model->input($name, $properties) ?>
<?			endforeach; ?>
			</fieldset>

			<ul class="actions">
				<?= $model->submit("Save", ["wrapper" => "li.save", "class" => "key:s button primary"]); ?>
			</ul>

		<?= $model->formEnd() ?>
	</div>

</div>
