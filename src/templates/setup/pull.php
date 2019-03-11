<?php
global $model;

// Get local path
$project_path = realpath(LOCAL_PATH."/..");

// Get git origin
$remote_origin = trim(shell_exec("cd '$project_path' && git config --get remote.origin.url"));


// Test origins
// $remote_origin = "https://kaestel:pass@github.com/kaestel/casepoint_dk";
// $remote_origin = "https://kaestel@github.com/kaestel/casepoint_dk";
// $remote_origin = "https://github.com/kaestel/casepoint_dk";

$remote_origin = preg_replace("/(http[s]?):\/\/(([^:]+)[:]?([^@]+)@)?/", "$1://", $remote_origin);


// Check that git has been set up with project credentials file
$credential_helper = trim(shell_exec("cd '$project_path' && git config --get credential.helper"));
$credential_helper_ready = false;
if(strpos("store --file ".PRIVATE_FILE_PATH."/.git_credentials", $credential_helper) === 0) {
	$credential_helper_ready = true;
}

?>
<div class="scene pull i:pull">

	<h1>Pull source code</h1>
	<h2><?= $project_path ?></h2>

<? if($model->get("system", "os") == "unix"): ?>

	<? if($credential_helper_ready): ?>

		<p>Your are about to pull the latest source code from:<br /><?= $remote_origin ?>.</p>

		<p class="note">
			If your repository is private, you need to enter your git username and password to 
			be able to pull, otherwise you can leave the fields empty.
		</p>

		<?= $model->formStart("/janitor/admin/setup/pull", array("class" => "pull labelstyle:inject")) ?>
			<?= $model->input("pull", array("type" => "hidden", "value" => "ok")); ?>

			<fieldset>
				<?= $model->input("git_username", array("label" => "Git username", "type" => "string", "hint_message" => "Enter your git username", "error_message" => "Not a valid username")); ?>
				<?= $model->input("git_password", array("label" => "Git password", "min" => 1, "type" => "password", "hint_message" => "Enter your git password", "error_message" => "Not a valid password")); ?>
			</fieldset>

			<ul class="actions">
				<?= $model->submit("Pull now", array("class" => "primary", "wrapper" => "li.pull")) ?>
			</ul>
		<?= $model->formEnd() ?>

		<p class="pull_result"></p>

	<? else: ?>

		<p>Git has not been set up for pulling through Janitor yet. Contact your server administrator to set it up.</p>

	<? endif; ?>


<? else: ?>

	<p>Your cannot pull the latest sources in an development environment. Use your Git application to update your local code.</p>

<? endif; ?>

</div>
