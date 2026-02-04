<?php
global $action;


$modules_installed = module()->getInstalledModules();
$modules_available = module()->getAvailableModules();

$modules_locked = false;

$SetupClass = new Setup();
if(!$SetupClass->readWriteTest() || preg_match("/\.local$/", SITE_URL)) {
	$modules_locked = true;
}


?>
<div class="scene module i:modules">
	<h1>Janitor modules</h1>
	<h2>Extending the system</h2>

	<p>
		Janitor modules are indenpendent code modules that extend Janitor with new content models and functionality 
		or connect Janitor with third party features, such as mail or sms services, maps, payment gateways and others.
	</p>

	<?= HTML()->renderSnippet("snippets/messages.php") ?>

<? if(!$modules_locked): ?>

	<div class="modules open-system">
		<h3>You system is currently open</h3>
		<p>Remember to lock down your system when you have modified your modules. <br />Run these command in your terminal when you are done:</p>
		<code>sudo chown -R root:<?= $SetupClass->get("system", "deploy_user") ?> <?= PROJECT_PATH ?> && 
sudo chmod -R 755 <?= PROJECT_PATH ?> && 
sudo chown -R <?= $SetupClass->get("system", "apache_user") ?>:<?= $SetupClass->get("system", "deploy_user") ?> <?= LOCAL_PATH ?>/library && 
sudo chmod -R 770 <?= LOCAL_PATH ?>/library</code>
	</div>

<? endif; ?>

	<div class="modules modules_installed">
		<h2>Installed modules</h2>
<?	if($modules_installed): ?>
		<p>These modules are currently installed on your system.</p>
		<ul class="items modules">
<?
		foreach($modules_installed as $module_group_id => $modules):
			$module_group = module()->getModuleGroup($module_group_id);

			foreach($modules as $module):
				// Remove from available modules list
				if(isset($modules_available[$module_group_id])) {
					$i = arrayKeyValue($modules_available[$module_group_id], "id", $module["id"]);
					if($i !== false) {
						unset($modules_available[$module_group_id][$i]);
					}
				}
?>
			<li class="module <?= $module["id"] ?> <?= $module_group_id ?>">
				<h3><?= $module["name"] ?></h3>
				<h4><?= $module_group["name"] ?></h4>

				<?= HTML()->renderSnippet("snippets/modules/panel-info.php", [
					"module" => $module,
				]) ?>
				<?= HTML()->renderSnippet("snippets/modules/panel-version.php", [
					"module" => $module,
				]) ?>

				<ul class="actions">
					<?= HTML()->link("Settings", "/janitor/admin/setup/modules/".$module_group_id."/".$module["id"], ["wrapper" => "li.settings", "class" => "button primary"]) ?>
				</ul>

			</li>
			<? endforeach; ?>
		<? endforeach; ?>
		</ul>
<?	else: ?>
		<p class="no_modules">No modules are currently installed on your system.</p>
<?	endif; ?>	
	</div>

	<div class="modules modules_available i:collapseHeader">
		<h2>Available modules</h2>
		<p>These modules are currently available to be installed on your system.</p>
		<ul class="items modules">

<?
		foreach($modules_available as $module_group_id => $modules):
			if($modules):
				$module_group = module()->getModuleGroup($module_group_id);

				foreach($modules as $module):
?>
			<li class="module <?= $module["id"] ?> <?= $module_group_id ?>">
				<h3><?= $module["name"] ?></h3>
				<h4><?= $module_group["name"] ?></h4>

				<?= HTML()->renderSnippet("snippets/modules/panel-info.php", [
					"module" => $module,
				]) ?>

				<ul class="actions">
					<?= HTML()->oneButtonForm("Install", "/janitor/admin/setup/modules/install/$module_group_id/".$module["id"], array(
						"wrapper" => "li.install",
						"confirm-value" => "Are you sure you want to install?",
						"success-function" => "installed",
					)) ?>
				</ul>

			</li>
<?
				endforeach;
			endif;
		endforeach;
?>
		</ul>

	</div>

</div>