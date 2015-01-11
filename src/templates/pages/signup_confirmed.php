<?php
global $action;
global $model;

$type = $action[1];
$username = $action[2];

?>
<div class="scene confirmed i:scene">

	<h1>All done</h1>
	<p>Your <?= $type ?>, <?= $username ?>, has been confirmed.</p>

	<p>Go to <a href="/janitor/admin/profile">Janitor</a> to update your profile and/or change newsletter subscriptions.</p>

</div>