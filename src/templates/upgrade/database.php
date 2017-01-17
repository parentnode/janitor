<?php
global $model;

	
?>
<div class="scene i:scene">
	<h1>Upgrading database</h1>

	<h3>Upgrade process:</h3>
	<ul class="tasks">
		<? $model->upgradeDatabase(); ?>
	</ul>

</div>