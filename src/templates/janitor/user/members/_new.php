<?php
global $action;
global $model;
$IC = new Items();


$members = $model->getMembers();
$users = $model->getUsers();

$membership_options = $model->toOptions($IC->getItems(array("itemtype" => "membership", "extend" => true)), "item_id", "name");
$non_member_users = array();

foreach($users as $user) {
	if(arrayKeyValue($members, "id", $user["id"]) === false) {
		$non_member_users[] = $user;
	}
}
$user_options = $model->toOptions($non_member_users, "id", "nickname");


$return_to_memberlist = session()->value("return_to_memberlist");

?>
<div class="scene i:scene defaultNew userMembers">
	<h1>New member</h1>

	<ul class="actions">
		<?
		// return to memberlist view
		if($return_to_memberlist):
			print $HTML->link("Back to overview", "/janitor/admin/user/member/list/".$return_to_memberlist, array("class" => "button", "wrapper" => "li.members"));

		// standard return link
		else:
			print $HTML->link("Back to overview", "/janitor/admin/user/member/list", array("class" => "button", "wrapper" => "li.members"));

		endif;
		?>
	</ul>

	<?= $model->formStart("/janitor/admin/user/addMember", array("class" => "i:defaultNew labelstyle:inject")) ?>
		<fieldset>
			<?= $model->input("membership_id", array(
				"type" => "select",
				"options" => $membership_options,
				"value" => $return_to_memberlist
			)) ?>
			<?= $model->input("user_id", array(
				"type" => "select",
				"options" => $user_options,
				"hint_message" => "Select user to add as member"
			)) ?>
		</fieldset>

		<ul class="actions">
			<?= $model->link("Back", "/janitor/admin/user/member/list", array("class" => "button key:esc", "wrapper" => "li.cancel")) ?>
			<?= $model->submit("Add member", array("class" => "primary key:s", "wrapper" => "li.save")) ?>
		</ul>
	<?= $model->formEnd() ?>

</div>