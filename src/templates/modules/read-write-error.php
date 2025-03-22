<?php

$SetupClass = new Setup();

?>
<div class="scene module i:modules">
	<h1>Janitor modules</h1>
	<h2>Read/write error</h2>

	<p>You need to allow Apache to modify files in your project folder.<br />Run this command in your terminal to continue:</p>
	<code>sudo chown -R <?= $SetupClass->get("system", "apache_user") ?>:<?= $SetupClass->get("system", "deploy_user")?> <?= PROJECT_PATH ?></code>

</div>