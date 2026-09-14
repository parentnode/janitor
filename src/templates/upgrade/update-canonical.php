<?php
global $model;
global $upgrade_model;


$itemtype_controllers = [];

$generic_model = new Itemtype("generic");
$items = items()->getItems(["order" => "itemtype"]);

foreach($items as $item) {

	// Itemtype controller not found
	if(isset($itemtype_controllers[$item["itemtype"]])) {

		$itemtype_controllers[$item["itemtype"]] = module()->getItemControllers($item["itemtype"]);

	}

}

?>
<div class="scene i:scene">
	<h1>Update canonical urls</h1>
	<ul class="actions">
		<li class="update_tools"><a href="/janitor/admin/setup/upgrade" class="button">Update tools</a></li>
	</ul>


	

	<p>Update multiple canonical urls at once, can be a drag, so this tool is made to help with transitioning from a non-canonical version, or to update canonicals after changing a main itemtype controller.</p>

	<h3>Items</h3>
	<h1>THIS UPDATER IS NOT READY FOR USE</h1>

<? exit() ?>
	<?= HTML()->formStart("/janitor/admin/setup/upgrade/updateMediaeVariant", array("class" => "labelstyle:inject i:update_mediae_variant")) ?>
<?		foreach($items as $item):
			$options = $generic_model->getCanonicalOptions($item, ["safe_only" => true]);
 ?>
		<fieldset>
			<span class="name"><?= $item["sindex"] ?></span>
			<span class="itemtype"><?= $item["itemtype"] ?></span>
			<?= HTML()->input("canonical[".$item["id"]."]", [
				"label" => "Choose canonical url for this item", 
				"type" => "select", 
				"options" => $options,
				"value" => $item["canonical"],
				"required" => true, 
				"hint_message" => "Select the canonical url for item."
			]) ?>
		</fieldset>
<?		endforeach; ?>
		<ul class="actions">
			<?= HTML()->submit("Update canonical urls", array("class" => "primary", "wrapper" => "li.update")) ?>
		</ul>
	<?= HTML()->formEnd() ?>

</div>