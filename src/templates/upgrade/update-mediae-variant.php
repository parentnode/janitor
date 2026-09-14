<?php
global $model;
global $upgrade_model;


$query = new Query();
$sql = "SELECT itemtype FROM ".UT_ITEMS." GROUP BY itemtype";
$query->sql($sql);

$itemtypes = $query->results();
sort($itemtypes);

$itemtype_options = HTML()->toOptions($itemtypes, "itemtype", "itemtype", ["add" => ["" => "select itemtype"]]);
// debug([$itemtype_options]);

?>
<div class="scene i:scene">
	<h1>Update mediae variant</h1>
	<ul class="actions">
		<li class="update_tools"><a href="/janitor/admin/setup/upgrade" class="button">Update tools</a></li>
	</ul>

	<p>Updates mediae variant name for specified itemtype. Can be needed when itemtype model has been updated and new default variant is preferred.</p>
	<p>As default only the first occurrence of variant on each item will be updated. Check the box to update all occurrences.</p>

	<h3>Settings</h3>

	<?= HTML()->formStart("/janitor/admin/setup/upgrade/updateMediaeVariant", array("class" => "labelstyle:inject i:update_mediae_variant")) ?>
		<fieldset>
			<?= HTML()->input("itemtype", [
				"label" => "Itemtype to update variant for", 
				"type" => "select", 
				"options" => $itemtype_options,
				"required" => true, 
				"hint_message" => "Select the itemtype that has a new model / mediae variant."
			]) ?>
			<?= HTML()->input("current_variant", [
				"label" => "Current mediae variant",
				"type" => "string", 
				"required" => true, 
				"hint_message" => "The current mediae variant name.",
			]) ?>
			<?= HTML()->input("new_variant", [
				"label" => "New mediae variant",
				"type" => "string", 
				"required" => true, 
				"hint_message" => "The new mediae variant name."
			]) ?>
			<?= HTML()->input("update_all", [
				"label" => "Update all occurrences for each item.",
				"type" => "checkbox", 
				"hint_message" => "Check this box if you want all occurrences to be updated to the new variant name."
			]) ?>
		</fieldset>

		<ul class="actions">
			<?= HTML()->submit("Update mediae variant", array("class" => "primary", "wrapper" => "li.update")) ?>
		</ul>
	<?= HTML()->formEnd() ?>

</div>