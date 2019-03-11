<?php
global $model;
global $upgrade_model;
	
?>
<div class="scene i:scene">
	<h1>Full upgrade</h1>

	<h3>Upgrade process:</h3>
	<ul class="tasks">
		<? $upgrade_model->fullUpgrade(); ?>
	</ul>

</div>