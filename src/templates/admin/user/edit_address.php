<?php

$action = $this->actions();

print_r($action);

$model = new User();
// check if custom function exists on cart class
$item = $model->getUsers(array("user_id" => $action[1]));

// TODO: Create global function for this
$user_groups = $model->getUserGroups();
$user_groups_options = array();
foreach($user_groups as $user_group) {
	$option = array();
	$option[0] = $user_group["id"];
	$option[1] = $user_group["user_group"];
	$user_groups_options[] = $option;
}

$query = new Query();
$query->sql("SELECT * FROM ".UT_LANGUAGES);
$languages = $query->results();
$language_options = array();
foreach($languages as $language) {
	$option = array();
	$option[0] = $language["id"];
	$option[1] = $language["name"];
	$language_options[] = $option;
}

//$usernames = $model->getUsernames(array("user_id" => $action[1]));
$mobile = "";
$mobile = $model->getUsernames(array("user_id" => $action[1], "type" => "mobile"));
$email = $model->getUsernames(array("user_id" => $action[1], "type" => "email"));

$addresses = $model->getAddresses(array("user_id" => $action[1]));
$newsletters = $model->getNewsletters(array("user_id" => $action[1]));

?>

<div class="scene defaultEdit userEdit">
	<h1>Edit Address</h1>

	<ul class="actions">
		<li class="cancel"><a href="/admin/user/edit/<?= $item["user_id"] ?>" class="button">Back to user</a></li>
	</ul>

	<h2>Address</h2>
	<p></p>
	<div class="addresses">
		<?
		print_r($addresses);
		?>


		<fieldset>
			<?= $model->input("address_label", array("type" => "hidden", "value" => "delivery" )) ?>
			<?= $model->input("address1", array(
					"required" => true,
					"label" => "Adresse", 
					"value" => stringOr($address1),
					"hint_message" => "Adressen der skal leveres til", 
					"error_message" => ""
				)) ?>
			<?= $model->input("address2", array(
					"label" => "Adresse fortsat", 
					"value" => stringOr($address2),
					"hint_message" => "Skriv yderligere adresse oplysninger", 
					"error_message" => ""
				)) ?>
			<?= $model->input("city", array(
					"required" => true,
					"label" => "By", 
					"value" => stringOr($city),
					"hint_message" => "Skriv navnet på din by", 
					"error_message" => ""
				)) ?>
			<?= $model->input("postal", array(
					"required" => true,
					"label" => "Postnummer", 
					"value" => stringOr($postal),
					"hint_message" => "Skriv din bys postnummer", 
					"error_message" => ""
				)) ?>
			<?= $model->input("country", array(
					"required" => true,
					"label" => "Country", 
					"value" => stringOr($postal),
					"hint_message" => "Skriv din bys postnummer", 
					"error_message" => ""
				)) ?>

			<div class="pseudofield country">
				<?= $model->input("country", array("type" => "hidden", "value" => "dk" )) ?>
				<label>Land</label>
				<div class="value">Danmark</div>
			</div>
		</fieldset>

		<ul class="actions">
			<li class="add"><input type="submit" value="Add new address" class="button primary" /></li>
		</ul>
	</div>

	<h2>Newsletters</h2>
	<p>You are subscriped to these newsletters</p>
	<div class="newsletters">
		<?
		print_r($newsletters);
		?>
	</div>

</div>