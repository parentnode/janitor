<?php
global $module_model;
global $action;

$module_list = $module_model->getAvailableModules();

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
		Janitor modules are indenpendent code modules that extend and connect Janitor with third party features, 
		such as mail or sms services, maps, payment gateways and others.
	</p>

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

	<div class="modules all_items">
		<h2>Available modules</h2>
		<p>
			Here is the list of the currently available modules for Janitor.
		</p>

		<? foreach($module_list as $module_group_id => $module_group): ?>
		<h3><?= $module_group["name"] ?></h3>
		<ul class="items modules">
			<? foreach($module_group["modules"] as $module_id => $module): ?>
			<li class="item module <?= $module_id ?>">
				<h4><?= $module["name"] ?></h4>
				<p>Read more on: 
					<a href="<?= $module["info_link"] ?>"><?= $module["info_link"] ?></a><br />
					Module source code: <a href="<?= $module["repos"] ?>"><?= $module["repos"] ?></a>
				</p>

				<?
				$installed_version = $module_model->getLocalVersion($module_group_id, $module_id);
				if($installed_version): ?>

				<p>Version <?= $installed_version ?> is currently installed</p>
				<ul class="actions">
					<?= $HTML->link("Settings", "/janitor/admin/setup/modules/$module_group_id/$module_id", ["wrapper" => "li.settings", "class" => "button"]) ?>

					<? if($module_model->updateAvailable($module_group_id, $module_id)): ?>
					<?//= $HTML->link("Upgrade", "/janitor/admin/setup/modules/upgrade/$module_group_id/$module_id", ["wrapper" => "li.upgrade", "class" => "button"]) ?>
					<?= $HTML->oneButtonForm("Upgrade", "/janitor/admin/setup/modules/upgrade/$module_group_id/$module_id", array(
						"wrapper" => "li.upgrade",
						"confirm-value" => "Are you sure you want to upgrade",
						"success-function" => "upgrade",
					)) ?>

					<? endif; ?>

					<?//= $HTML->link("Uninstall", "/janitor/admin/setup/modules/uninstall/$module_group_id/$module_id", ["wrapper" => "li.uninstall", "class" => "button"]) ?>
					<?= $HTML->oneButtonForm("Uninstall", "/janitor/admin/setup/modules/uninstall/$module_group_id/$module_id", array(
						"wrapper" => "li.uninstall",
						"confirm-value" => "Are you sure you want to uninstall?",
						"success-function" => "uninstalled",
					)) ?>
				</ul>

				<? else:?>

				<p>This module is not installed</p>
				<ul class="actions">
					<? //= $HTML->link("Install", "/janitor/admin/setup/modules/install/$module_group_id/$module_id", ["wrapper" => "li.install", "class" => "button"]) ?>
					<?= $HTML->oneButtonForm("Install", "/janitor/admin/setup/modules/install/$module_group_id/$module_id", array(
						"wrapper" => "li.install",
						"confirm-value" => "Are you sure you want to install?",
						"success-function" => "installed",
					)) ?>
				</ul>

				<? endif; ?>
			</li>
			<? endforeach; ?>
		</ul>
		<? endforeach; ?>

	</div>

</div>