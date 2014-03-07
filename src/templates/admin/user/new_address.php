<?php

$action = $this->actions();

$model = new User();
$countries = array(array("dk","Denmark"));
?>
<div class="scene defaultNew userAddress">
	<h1>New address</h1>

	<ul class="actions">
		<li class="cancel"><a href="/admin/user/edit/<?= $action[1] ?>" class="button">Back to user</a></li>
	</ul>

	<h2>Address</h2>
	<p>Enter address information</p>

	<form action="/admin/user/addAddress/<?= $action[1] ?>" class="i:formDefaultNew labelstyle:inject" method="post" enctype="multipart/form-data">
		<input type="hidden" name="user_id" value="<?= $action[1] ?>" />
		<fieldset>
			<?= $model->input("address_label", array(
					"required" => true
				)) ?>
			<?= $model->input("address_name", array(
					"required" => true
				)) ?>
			<?= $model->input("att") ?>
			<?= $model->input("address1", array(
					"required" => true
				)) ?>
			<?= $model->input("address2") ?>
			<?= $model->input("city", array(
					"required" => true
				)) ?>
			<?= $model->input("postal", array(
					"required" => true
				)) ?>
			<?= $model->input("state") ?>
			<?= $model->input("country", array(
					"type" => "select",
					"required" => true,
					"options" => $countries
				)) ?>
		</fieldset>

		<ul class="actions">
			<li class="add"><input type="submit" value="Save address" class="button primary key:s" /></li>
			<li class="cancel"><a href="/admin/user/edit/<?= $action[1] ?>" class="button key:esc">Back</a></li>
		</ul>
	</form>

</div>