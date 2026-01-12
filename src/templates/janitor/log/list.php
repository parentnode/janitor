<?php
global $action;
global $LC;

$from = getPost("from");
$to = getPost("to");
$type = getPost("type");


$logs = $LC->getLogs([
	"from" => $from ? strtotime($from) : false,
	"to"   => $to ? strtotime($to) : false,
	"type" => $type
]);

// debug([$logs]);

$log_types = $LC->getLogTypes();
$log_options = ["" => "All types"];
foreach($log_types as $log_type) {
	$log_options[$log_type] = $log_type;
}


?>
<div class="scene i:scene defaultList logList">
	<h1>Logs</h1>

	<h3>Select data-range and log type</h3>
	<?= $HTML->formStart("list", [
		"method" => "post",
	]); ?>

		<fieldset>
			<?= $HTML->input("from", [
				"type" => "date",
				"label" => "From",
				"value" => $from,
			]) ?>
			<?= $HTML->input("to", [
				"type" => "date",
				"label" => "To",
				"value" => $to,
			]) ?>
			<?= $HTML->input("type", [
				"type" => "select",
				"options" => $log_options,
				"label" => "Type",
				"value" => $type,
			]) ?>
		</fieldset>

		<ul class="actions
			<?= $HTML->submit("Update", [
				"class" => "button primary",
				"wrapper" => "li.submit",
			]); ?>
		</ul>

	<?= $HTML->formEnd(); ?>


	<div class="all_items i:defaultList i:logList filters">
<?	if($logs): ?>
		<ul class="items">
<?		foreach($logs as $log_type => $log_files): ?>
			<li class="log_type"><h2><?= $log_type ?></h2></li>
<?			foreach($log_files as $log_file):
				$log_lines = file($log_file); ?>
			<li class="log_file"><h3><?= $log_file ?></h3></li>
<?				foreach($log_lines as $log_line): ?>
			<li class="item log_line"><p><?= trim($log_line) ?></p></li>
<?				endforeach; ?>
<?			endforeach; ?>
<?		endforeach; ?>
		</ul>
<?		else: ?>
		<p>No logs.</p>
<?		endif; ?>
	</div>

</div>
