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
	$installed_version = module()->getLocalVersion($module["group_id"], $module["id"]);
	$update_available = module()->updateAvailable($module["group_id"], $module["id"]);
?>
<div class="version">
	<p>
<?	if($installed_version): ?>
		<span class="version">Version <?= $installed_version ?> is currently installed</span><br />
<?	else: ?>
		<span class="version">Unknown version</span><br />
<?	endif; ?>

<?	if($update_available): ?>
		<span class="update">Version <?= $update_available ?> is available.</span>
<?	else: ?>
		<span class="version">There are no updates available</span><br />
<?	endif; ?>
	</p>
</div>
<?
endif;
