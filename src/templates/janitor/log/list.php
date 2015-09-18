<?php
global $action;
global $LC;

$logs = $LC->getLogs();

?>
<div class="scene defaultList logList">
	<h1>Logs</h1>

	<div class="all_items i:defaultList filters">
<?		if($logs): ?>
		<ul class="items">
<?			foreach($logs as $log):
				$loglines = file($log); ?>
			<li class="item">
				<h3><?= $log ?></h3>
				
				<ul class="loglines">
<?				foreach($loglines as $logline): ?>
					<li><?= $logline ?></li>
<?				endforeach; ?>
				</ul>
			 </li>
<?			endforeach; ?>
		</ul>
<?		else: ?>
		<p>No logs.</p>
<?		endif; ?>
	</div>

</div>
