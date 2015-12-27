<?php
global $action;
global $IC;
global $model;
global $itemtype;

$return_to_wishlist = session()->value("return_to_wishlist");
?>
<div class="scene defaultNew">
	<h1>New wish</h1>

	<ul class="actions">
		<?
		// different "back"-links depending on where you came from
		if($return_to_wishlist):
			print $HTML->link("Back to wishlist", "/janitor/admin/wishlist/edit/".$return_to_wishlist, array("class" => "button", "wrapper" => "li.wishlist"));
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

		<?
		// different cancel links depending on context

		// default return link
		$options = false;

		// return to todolist view
		if($return_to_wishlist):
			$options = array("modify" => array(
				"cancel" => [
					"url" => "/janitor/admin/wishlist/edit/".$return_to_wishlist
				]
			));

		endif;

		print $JML->newActions($options); 
		?>
	<?= $model->formEnd() ?>

</div>
