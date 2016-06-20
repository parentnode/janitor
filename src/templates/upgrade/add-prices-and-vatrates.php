<?php
global $model;


$this->bodyClass("prices");	

$upgraded = $model->addPricesAndVatrates();
	
?>
<div class="scene i:scene">
	<h1>Adding prices and vatrates</h1>

<?	if($upgraded): ?>
	
	<p><?= $upgraded ?></p>
	
<?	else: ?>	

	<p>Upgrade failed</p>

<?	endif; ?>

</div>