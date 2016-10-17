<?php
global $action;
global $model;
$SC = new Shop();

$user_id = session()->value("user_id");


$user = $model->getUser();
$membership = $model->getMembership();


// FOR TESTING EMAIL SENDING
// $subscription = $model->getSubscriptions(array("subscription_id" => $membership["subscription_id"]));
// $IC = new Items();
// $mem = $IC->typeObject("membership");
// $mem->subscribed($subscription);

?>
<div class="scene i:scene defaultView userMembership">
	<h1>Membership</h1>
	<h2><?= $user["nickname"] ?></h2>

	<ul class="actions">
		<?= $HTML->link("All users", "/janitor/admin/user/list/".$user["user_group_id"], array("class" => "button", "wrapper" => "li.cancel")) ?>
	</ul>


	<?= $JML->profileTabs("membership") ?>



	<div class="item">
	<? if($membership): ?>
			

		<? if($membership["subscription_id"]):
			$subscription = $model->getSubscriptions(array("subscription_id" => $membership["subscription_id"])); ?>
		<h3><?= $membership["item"]["name"] ?></h3>
		<? else: ?>
		<h3>Inactive membership</h3>
		<? endif; ?>
		
		<dl class="info">
			<dt class="created_at">Created at</dt>
			<dd class="created_at"><?= date("d. F, Y", strtotime($membership["created_at"])) ?></dd>

		<? if($membership["modified_at"]): ?>
			<dt class="modified_at">Last modified at</dt>
			<dd class="modified_at"><?= date("d. F, Y", strtotime($membership["modified_at"])) ?></dd>
		<? endif; ?>

		<? if($membership["renewed_at"]): ?>
			<dt class="renewed_at">Last renewed at</dt>
			<dd class="renewed_at"><?= date("d. F, Y", strtotime($membership["renewed_at"])) ?></dd>
		<? endif; ?>

		<? if($membership["expires_at"]): ?>
			<dt class="expires_at">Expires at</dt>
			<dd class="expires_at"><?= date("d. F, Y", strtotime($membership["expires_at"])) ?></dd>
		<? endif; ?>


		<? if($membership["item"] && $membership["item"]["prices"]):
			$offer = arrayKeyValue($membership["item"]["prices"], "type", "offer");
			$default = arrayKeyValue($membership["item"]["prices"], "type", "default");
			?>
		
			<? if($offer !== false && $default !== false): ?>
				<dt class="price default">Normal price</dt>
				<dd class="price default"><?= formatPrice($membership["item"]["prices"][$default]).($membership["item"]["subscription_method"] ? " / " . $membership["item"]["subscription_method"]["name"] : "") ?></dd>
				<dt class="price offer">Special offer</dt>
				<dd class="price offer"><?= formatPrice($membership["item"]["prices"][$offer]).($membership["item"]["subscription_method"] ? " / " . $membership["item"]["subscription_method"]["name"] : "") ?></dd>
			<? elseif($default !== false): ?>
				<dt class="price">Price</dt>
				<dd class="price"><?= formatPrice($membership["item"]["prices"][$default]).($membership["item"]["subscription_method"] ? " / " . $membership["item"]["subscription_method"]["name"] : "") ?></dd>
			<? endif; ?>

			<dt class="payment_method">Payment method</dt>
			<dd class="payment_method"><?= $subscription["payment_method"] ? $subscription["payment_method"]["name"] : "N/A" ?></dd>
		<? endif; ?>

		<? if($membership["order_id"]): ?>
			<dt class="payment_status">Payment status</dt>
			<dd class="payment_status<?= $membership["order"]["payment_status"] < 2 ? " missing" : "" ?>"><?= $SC->payment_statuses[$membership["order"]["payment_status"]] ?><?= $membership["order"]["payment_status"] < 2 ? ' - <a href="'.SITE_URL.'/shop/payment/'.$membership["order"]["order_no"].'">Pay now</a>' : "" ?></dd>
		<? endif; ?>

		</dl>

		<? if($membership["subscription_id"]): ?>
		<ul class="actions">
			<?= $HTML->link("Edit", "/janitor/admin/profile/membership/edit", array("class" => "button", "wrapper" => "li.edit")) ?>
		</ul>
		<? endif; ?>


	<? else: ?>
		<p>You do not have a membership.</p>
	<? endif; ?>
	</div>

</div>