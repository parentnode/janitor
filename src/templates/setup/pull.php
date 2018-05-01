<?php
global $model;


print $model->get("system", "os");
?>
<div class="scene pull i:pull">

	<h1>Pull source code</h1>
	<h2><?= realpath(LOCAL_PATH."/..") ?></h2>

<? if($model->get("system", "os") == "unix"): ?>

	<p>Your are about to pull the latest source code from the Git repository.</p>

	<ul class="actions">
		<?= $JML->oneButtonForm("Pull now", "/janitor/admin/setup/pull", array(
			"confirm-value" => "Are you sure you want to pull?",
			"wrapper" => "li.pull",
			"inputs" => ["pull" => "ok"]
		)); ?>
	</ul>

	<div class="pull_result"></div>

<? else: ?>

	<p>Your cannot pull the latest sources in an development environment. Use your Git application to update your local code.</p>

<? endif; ?>

</div>
