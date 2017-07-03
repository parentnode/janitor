<?php
global $action;
global $model;
$query = new Query();

$users = $model->getUnconfirmedUsers();

?>
<div class="scene i:scene defaultList usersNotVerified">
	<h1>Unconfirmed accounts</h1>

	<ul class="actions i:unconfirmedAccountsAll">
		<?= $HTML->link("All users", "/janitor/admin/user/list", array("class" => "button", "wrapper" => "li.cancel")) ?>

		<?= $JML->oneButtonForm("Send reminder to All", "/janitor/admin/user/sendActivationReminder", array(
			"wrapper" => "li.remind",
			"confirm-value" => "This will send an activation reminder email to all unconfirmed accounts!",
			"success-function" => "reminded"
		)) ?>
	</ul>

	<p>
		These users did not click the activation link in the welcome email (or otherwise failed to complete verification).
	</p>

	<div class="all_items orders i:defaultList filters i:unconfirmedAccounts">
		<h2>Users</h2>
<?		if($users): ?>
		<ul class="items">
<?			foreach($users as $user): ?>
			<li class="item id:<?= $user["user_id"] ?>">
				<h3><?= $user["nickname"] ?></h3>

				<dl class="details">
					<dt class="email">Email</dt>
					<dd class="email"><?= $user["username"] ?></dd>
					<dt class="created_at">Created at</dt>
					<dd class="created_at"><?= $user["created_at"] ?></dd>
					<dt class="reminded_at">Last reminder sent at</dt>
					<? 
					// if last reminder is less than 5 days ago (indicate dont send yet)
					?>
					<dd class="reminded_at<?= strtotime($user["reminded_at"]) < time()-(60*60*24*5) ? "" : " system_warning" ?>"><?= $user["reminded_at"] ?><? if($user["reminded_at"]): ?> (<?= round((time() - strtotime($user["reminded_at"])) / (24 * 60 * 60)) ?> days ago) <? else: print "N/A"; endif; ?></dd>
					<? 
					// if total_reminders is 5 or more (indicate consider deleting user)
					?>
					<dt class="total_reminders">Total reminders sent</dt>
					<dd class="total_reminders<?= $user["total_reminders"] >= 5 ? " system_warning" : "" ?>"><?= $user["total_reminders"] ?></dd>
				</dl>

				<ul class="actions">
					<?= $JML->oneButtonForm("Send reminder", "/janitor/admin/user/sendActivationReminder/".$user["user_id"], array(
						"wrapper" => "li.remind",
						"confirm-value" => "This will send an activation reminder email!",
						"success-function" => "reminded"
					)) ?>
					<?= $HTML->link("Edit", "/janitor/admin/user/edit/".$user["user_id"], array("class" => "button", "wrapper" => "li.edit")) ?>
				</ul>

			 </li>
<?			endforeach; ?>
		</ul>
<?		else: ?>
		<p>All users are verified.</p>
<?		endif; ?>
	</div>

</div>