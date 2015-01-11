<?php
global $action;
global $model;

$user_info = $model->getUser();
	
?>
<div class="scene signup i:scene">

	<h1>Thank you!</h1>
	<p>You are almost home.</p>
	<p>
		We have sent a verification email to <em><?= $user_info["email"] ?></em> with an
		activation link. Check your inbox and click the link to activate your new account.
	</p>

	<p>See you again soon.</p>

</div>
