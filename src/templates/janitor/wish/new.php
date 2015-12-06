<?php
global $action;
global $IC;
global $model;
global $itemtype;

$return_to_wishlist = "";
if(count($action) == 3 && $action[1] == "wishlist") {
	$return_to_wishlist = $action[2];
	session()->value("return_to_wishlist", $return_to_wishlist);
}
?>
<div class="scene defaultNew">
	<h1>New wish</h1>

	<ul class="actions">
		<?
		// different "back"-links depending on where you came from
		if($return_to_wishlist):
			print $HTML->link("Back", "/janitor/admin/wishlist/edit/".$return_to_wishlist, array("class" => "button", "wrapper" => "li.wishlist"));
		// standard return link
		else:
			print $JML->newList(array("label" => "Back to overview"));
		endif;
		?>
	</ul>

	<?= $model->formStart("save/".$itemtype, array("class" => "i:defaultNew labelstyle:inject")) ?>
		<fieldset>
			<?= $model->input("name") ?>
			<?= $model->input("price") ?>
			<?= $model->input("link") ?>
			<?= $model->input("description", array("class" => "autoexpand")) ?>
		</fieldset>

		<?= $JML->newActions() ?>
	<?= $model->formEnd() ?>

</div>
