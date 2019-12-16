<?php
global $action;
global $model;
$query = new Query();

$users = $model->getUnverifiedUsernames([
	"type" => "email"
	]);
?>
<div class="scene i:scene defaultList usersNotVerified">
	<h1>Unverified usernames</h1>

	<ul class="actions i:unverifiedUsernamesSelected">
		<?= $HTML->link("All users", "/janitor/admin/user/list", array("class" => "button", "wrapper" => "li.cancel")) ?>

		<?= $HTML->oneButtonForm("Send reminder to selected", "/janitor/admin/user/sendVerificationLinks", array(
			"wrapper" => "li.remind_selected",
			"confirm-value" => "This will send an activation reminder email to all selected unverified usernames!",
			"success-function" => "reminded",
			"class" => "disabled",
			"inputs" => [
				"selected_username_ids" => ""
			]
		)) ?>
	</ul>

	<p>
		These users did not click the activation link in the welcome email (or otherwise failed to complete verification).
	</p>

	<div class="all_items orders i:defaultList filters selectable i:unverifiedUsernames">
		<h2>Users</h2>
<?		if($users): ?>
		<ul class="items">
<?			foreach($users as $user): ?>
			<li class="item id:<?= $user["user_id"] ?> username_id:<?= $user["username_id"] ?>">
				<h3><?= $user["nickname"] ?></h3>

				<dl class="info">
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
					<?= $HTML->oneButtonForm("Send reminder", "/janitor/admin/user/sendVerificationLink/".$user["username_id"], array(
						"wrapper" => "li.remind",
						"confirm-value" => "This will send an activation reminder email!",
						"success-function" => "reminded",
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