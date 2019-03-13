<?php
global $action;
global $model;

$can_unsubscribe = false;

if(count($action) == 4) {

	$maillist_id = $action[1];
	$username = $action[2];
	$verification_code = $action[3];

	if($maillist_id) {
		$maillist = $this->maillists($maillist_id);

		if($maillist) {

			$query = new Query();
			$sql = "SELECT user_id FROM ".SITE_DB.".user_usernames WHERE username = '$username' AND verification_code = '$verification_code'";
			// debug([$sql]);
			if($query->sql($sql)) {
				$can_unsubscribe = true;
			}
		
		}
	}
}

?>
<div class="scene unsubscribe i:unsubscribe">

<? if($can_unsubscribe): ?>
	<h1>Unsubscribe</h1>
	<h2>You are about to unsubscribe from our <em><?= $maillist["name"] ?></em> mailing list.</h2>
	<p>If you click the <em>unsubscribe</em> button below, <em><?= $username ?></em> will be permanently removed from the mailing list.</p>


<?	if(message()->hasMessages(array("type" => "error"))): ?>
		<p class="errormessage">
<?		$messages = message()->getMessages(array("type" => "error"));
		message()->resetMessages();
		foreach($messages as $message): ?>
			<?= $message ?><br>
<?		endforeach;?>
		</p>
<?	endif; ?>

	<ul class="actions">
		<?= $JML->oneButtonForm("Unsubscribe from ".$maillist["name"], "/signup/unsubscribe", array(
			"class" => "button primary",
			"confirm-value" => "Are you sure?",
			"wrapper" => "li.unsubscribe",
			"dom-submit" => true,
			"inputs" => array("maillist_id" => $maillist["id"], "username" => $username, "verification_code" => $verification_code)
		)) ?>
	</ul>

	<p>
		You can always re-join the mailing list, by visiting the <em>maillists</em>-tab on your <a href="/janitor/admin/profile/maillists">profile</a> page. We hope to see you again.
	</p>
	<p>
		If you want to delete your account on <?= SITE_NAME ?> completely, please do so on your <a href="/janitor/admin/profile">profile</a> page.
	</p>

<? else: ?>

	<h1>Unsubscribe?</h1>
	<p>
		It appears as if you wanted to unsubscribe from a mailing list – but the information you 
		provided does not match any subscribers in our system.
	</p>
	<p>
		If you think this is a mistake, please visit the <em>maillists</em>-tab on your <a href="/janitor/admin/profile/maillists">profile</a> page
		to get a full overview of your current mailing list subscriptions.
	</p>

<? endif; ?>


</div>
