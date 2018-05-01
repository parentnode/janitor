<?php
global $model;

?>
<div class="scene pull i:pull">

	<h1>Pull source code</h1>
	<h2><?= realpath(LOCAL_PATH."/..") ?></h2>

	<p>Your are about to pull the latest source code from the Git repository.</p>

	<ul class="actions">
		<?= $JML->oneButtonForm("Pull now", "/janitor/admin/setup/pull", array(
			"confirm-value" => "Are you sure you want to pull?",
			"wrapper" => "li.pull",
			"inputs" => ["pull" => "ok"]
		)); ?>
	</ul>

	<div class="pull_result"></div>

</div>
