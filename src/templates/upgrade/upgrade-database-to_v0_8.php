<?php
global $model;


$this->bodyClass("prices");	

$upgraded = $model->upgradeDatabaseTo_v0_8();
	
?>
<div class="scene i:scene">
	<h1>Upgrading database to v0.8</h1>

<?	if($upgraded): ?>
	
	<p><?= $upgraded ?></p>
	
<?	else: ?>	

	<p>Upgrade failed</p>

<?	endif; ?>

</div>