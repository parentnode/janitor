<?php

$module = false;

$module_id = false;
$module_group_id = false;

if($_options !== false) {
	foreach($_options as $_option => $_value) {
		switch($_option) {
			case "module"                     : $module                     = $_value; break;

			case "module_id"                  : $module_id                  = $_value; break;
			case "module_group_id"            : $module_group_id            = $_value; break;
		}
	}
}

if(!$module && $module_group_id && $module_id) {
	$module = $module = module()->getModule($module_group_id, $module_id);
}

if($module):
	$digest_test = module()->checkDigest($module["group_id"], $module["id"]);
?>
<div class="uninstall i:collapseHeader">
	<h2>Uninstall module</h2>

	<p>
		Uninstalling this module will delete all original module files from the system.<br />
		<strong>Note:</strong> Data and configuration files will <strong>not</strong> be deleted, unless you check the <em>Also delete all data and configuration files</em> option below.</p>
<? if($digest_test !== true): ?>
	<div class="digest warning">
		<p>Some module files appear to have been modified after installation. These files will <strong>not</strong> be removed automatically, unless you check the <em>Also delete modified files</em> option below.</p>
		<ul class="modified_files">
<?		foreach($digest_test as $modified_file):?>
			<li class="file"><?= $modified_file ?></li>
<?		endforeach ?>
		</ul>
	</div>
<? endif; ?>

	<?= HTML()->formStart("/janitor/admin/setup/modules/uninstall/".$module["group_id"]."/".$module["id"], [
		"class" => "uninstall"
	]) ?>
		<fieldset>
<? if($digest_test !== true): ?>
			<?= HTML()->input("delete_modified_files", [
				"type" => "checkbox",
				"label" => "Also delete modified files",
				"hint_message" => "This action cannot be undone.",
			]) ?>
<? endif; ?>
			<?= HTML()->input("delete_data", [
				"type" => "checkbox",
				"label" => "Also delete all data and configuration files",
				"hint_message" => "This action cannot be undone.",
			]) ?>
		</fieldset>

		<ul class="actions">
			<?= HTML()->submit("Uninstall", [
				"wrapper" => "li.uninstall",
				"class" => "warning",
				"confirm" => "Are you sure you want to uninstall?",
			]) ?>
		</ul>
	<?= HTML()->formEnd() ?>
</div>
<?
endif;