<?php
global $model;

$payment_check = $model->checkPaymentSettings();

?>
<div class="scene payment i:payment">

	<h1>Janitor configuration</h1>
	<h2>Payment gateway (optional)</h2>
	<ul class="actions">
		<?= $JML->oneButtonForm("Restart setup", "/janitor/admin/setup/reset", array(
			"confirm-value" => "Are you sure you want to start over?",
			"wrapper" => "li.delete",
			"success-location" => "/janitor/admin/setup"
		)); ?>
	</ul>

	<p>
		Janitor can be configured to connect to a payment gateway so you can receive payments on your website.
	</p>


<? if($model->get("payment", "passed")): ?>

		<h3>Payment status: <?= $model->get("payment", "skipped") ? "SKIPPED" : "OK" ?></h3>
<?		if($model->get("payment", "skipped")): ?>
		<p>The system will be configured without payment support.</p>
<?		else: ?>
		<p>Your payment system is already configured correctly.</p>
<?		endif; ?>
		<ul class="actions">
			<li class="continue"><a href="/janitor/admin/setup/finish" class="button primary">Continue</a></li>
		</ul>

<? else: ?>

	<?= $model->formStart("/janitor/admin/setup/payment/updatePaymentSettings", array("class" => "skip labelstyle:inject")) ?>

		<?= $model->input("skip_payment", array("type" => "hidden", "value" => "1")) ?>

		<ul class="actions">
			<?= $model->submit("Skip payment setup", array("wrapper" => "li.skip")) ?>
		</ul>

	<?= $model->formEnd() ?>


<? endif; ?>

	<h3>System payment settings</h3>

	<?= $model->formStart("/janitor/admin/setup/payment/updatePaymentSettings", array("class" => "payment labelstyle:inject")) ?>

		<p>
			Which payment gateway will be used?
		</p>
		<fieldset>
			<?= $model->input("payment_type", array("value" => $model->get("payment", "payment_type"))) ?>
		</fieldset>

		<div class="type_stripe">
			<p>Specify Stripe account information to enable receiving payments.</p>

			<fieldset>
				<?= $model->input("payment_stripe_private_key", array("value" => $model->get("payment", "payment_stripe_private_key"))) ?>
				<?= $model->input("payment_stripe_public_key", array("value" => $model->get("payment", "payment_stripe_public_key"))) ?>
			</fieldset>
		</div>


		<ul class="actions">
			<?= $model->submit("Update and continue", array("wrapper" => "li.save", "class" => "primary")) ?>
		</ul>

	<?= $model->formEnd() ?>

</div>