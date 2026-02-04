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
?>
<div class="basic-info">
	<p class="description"><?= $module["description"] ?></p>
	<p class="readmore">Read more on: 
		<a href="<?= $module["info_link"] ?>"><?= $module["info_link"] ?></a><br />
		Module source code: <a href="<?= $module["repos"] ?>"><?= $module["repos"] ?></a>
	</p>
</div>
<?
endif;
