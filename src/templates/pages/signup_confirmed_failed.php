<?php
global $action;
global $model;

$type = $action[1];
$username = $action[2];

?>
<div class="scene confirmed i:scene">

	<h1>Whuups?</h1>
	<p>Your <?= $type ?>, <?= $username ?>, could NOT be confirmed.</p>

	<p>
		Please make sure the link in the email is not broken and try again. If you still have problems please send an email
		to <a href="mailto:<?= ADMIN_EMAIL ?>?subject=Signup confirmation failed for <?= $username ?>"><?= ADMIN_EMAIL ?></a>
		and we will help you complete the signup.
	</p>

</div>