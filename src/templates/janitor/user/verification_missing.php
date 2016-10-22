<?php
global $action;
global $model;
$query = new Query();

$sql = "SELECT users.id as user_id, usernames.username as username, usernames.verification_code as verification_code, users.nickname as nickname, users.created_at as created_at FROM ".$model->db." as users, ".$model->db_usernames." as usernames WHERE users.id = usernames.user_id AND users.status = 0 AND usernames.verified = 0 AND usernames.type = 'email' GROUP BY users.id";
// print $sql;

$users = false;
if($query->sql($sql)) {

	$users = $query->results();
}

?>
<div class="scene i:scene defaultList usersNotVerified">
	<h1>Not verified users</h1>

	<ul class="actions">
		<?= $HTML->link("All users", "/janitor/admin/user/list", array("class" => "button", "wrapper" => "li.cancel")) ?>
	</ul>

	<p>
		These users did not click the activation link in the welcome email.
	</p>

	<div class="all_items orders i:defaultList filters">
		<h2>Users</h2>
<?		if($users): ?>
		<ul class="items">
<?			foreach($users as $user): ?>
			<li class="item">
				<h3><?= $user["nickname"] ?></h3>

				<dl class="details">
					<dt class="email">Email</dt>
					<dd class="email"><?= $user["username"] ?></dd>
					<dt class="created_at">Created at</dt>
					<dd class="created_at"><?= $user["created_at"] ?></dd>
					<dt class="verification_code">Verification code</dt>
					<dd class="verification_code"><?= $user["verification_code"] ?></dd>
					<dt class="verification_link">Verification link</dt>
					<dd class="verification_link"><?= SITE_URL."/signup/confirm/email/".$user["username"]."/".$user["verification_code"] ?></dd>
				</dl>
			 </li>
<?			endforeach; ?>
		</ul>
<?		else: ?>
		<p>All users are verified.</p>
<?		endif; ?>
	</div>

</div>