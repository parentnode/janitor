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
	$update_available = module()->updateAvailable($module["group_id"], $module["id"]);
?>
<div class="upgrade">
	<h2>Upgrade module</h2>

<?	if($digest_test !== true): ?>
	<div class="digest warning">
		<p>
			This module has been modified locally. <br />
			Changes to the following files will be saved in separate files with an _original_ marker added to the filename during updates,
			unless you check the <em>Overwrite local changes</em> option below.
		</p>
		<ul class="modified_files">
<?		foreach($digest_test as $modified_file): ?>
			<li class="file"><?= $modified_file ?></li>
<?		endforeach ?>
		</ul>
	</div>
<?	endif; ?>

<?	if($update_available): ?>
	<?= HTML()->formStart("/janitor/admin/setup/modules/upgrade/".$module["group_id"]."/".$module["id"], [
		"class" => "upgrade"
	]) ?>
		<fieldset>
<? if($digest_test !== true): ?>
			<?= HTML()->input("delete_modified_files", [
				"type" => "checkbox",
				"label" => "Overwrite local changes",
				"hint_message" => "This action cannot be undone.",
			]) ?>
<? endif; ?>
		</fieldset>
		<ul class="actions">
			<?= HTML()->submit("Upgrade", [
				"wrapper" => "li.upgrade",
				"class" => "primary",
				"confirm" => "Are you sure you want to upgrade?",
			]) ?>
		</ul>
	<?= HTML()->formEnd() ?>

<? /*
	<ul class="actions">
<?		if($digest_test !== true): ?>
		<?= HTML()->oneButtonForm("Upgrade and overwrite local changes", "/janitor/admin/setup/modules/upgrade/".$module["group_id"]."/".$module["id"], [
			"wrapper" => "li.upgrade",
			"input" => [
				"force_overwrite" => true
			],
			"confirm-value" => "Are you sure you want to overwrite local changes?",
			"success-function" => "upgraded",
		]); ?>
<?		else: ?>
		<?= HTML()->oneButtonForm("Upgrade", "/janitor/admin/setup/modules/upgrade/".$module["group_id"]."/".$module["id"], [
			"wrapper" => "li.upgrade",
			"confirm-value" => "Are you sure you want to upgrade?",
			"class" => "primary",
			"success-function" => "upgraded",
		]); ?>
<?		endif; ?>
	</ul>
<?	
	*/
else: ?>
	<p>You have the latest version.</p>
<?	endif; ?>
</div>
<?
endif;
