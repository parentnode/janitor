<?php
// TODO: This template should be moved to system
include_once("classes/system/system.class.php");


global $action;
global $model;
$IC = new Items();
$SC = new System();

$return_to_newsletterlist = session()->value("return_to_newsletterlist");

?>
<div class="scene i:scene defaultNew userMembers">
	<h1>New mailing list</h1>

	<ul class="actions">
		<?
		// return to memberlist view
		if($return_to_newsletterlist):
			print $HTML->link("Back to overview", "/janitor/admin/user/newsletters/list/".$return_to_newsletterlist, array("class" => "button", "wrapper" => "li.newsletterss"));

		// standard return link
		else:
			print $HTML->link("Back to overview", "/janitor/admin/user/newsletters/list", array("class" => "button", "wrapper" => "li.newsletterss"));

		endif;
		?>
	</ul>

	<?= $SC->formStart("/janitor/admin/system/addNewsletter", array("class" => "i:defaultNew labelstyle:inject")) ?>
		<?= $SC->input("return_to", ["type" => "hidden", "value" => "/janitor/admin/user/newsletters/list/"])?>
		<fieldset>
			<?= $SC->input("newsletter") ?>
		</fieldset>

		<ul class="actions">
			<?= $SC->link("Back", "/janitor/admin/user/newsletters/list", array("class" => "button key:esc", "wrapper" => "li.cancel")) ?>
			<?= $SC->submit("Add list", array("class" => "primary key:s", "wrapper" => "li.save")) ?>
		</ul>
	<?= $SC->formEnd() ?>

</div>