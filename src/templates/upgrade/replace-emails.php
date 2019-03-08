<?php
global $model;
global $upgrade_model;

	
?>
<div class="scene i:scene">
	<h1>Replacing emails</h1>

	<h3>Result:</h3>
	<ul class="tasks">
		<? $upgrade_model->replaceEmails(); ?>
	</ul>

</div>