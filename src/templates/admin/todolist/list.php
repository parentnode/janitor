<?php
global $action;
global $IC;
global $model;
global $itemtype;

/**
* IMPLEMENT SECURITY
* 
* wrapped in li or standalone
* - maybe leave wrapper out of function but how to avoid empty wrappers
*
* a href links with and without button class
* input type submit with/without form
* javascript enabled action with multiple class references like sortable
* disable/enable buttons (should js always read url from HTML, to avoid having url twice)
*
* How to access functions
* - through model or directly through HTML (should HTML always be available)
* - should validation function be in security class
*
*
* Conclusions/considarations
* - I want to have simple function calls
*
*
* EXAMPLES/TEXT SYNTAX
* HTML->link("back", action, array("wrap" => "li", "class" => "button primary"))
* <li class="back"><a href="action" class="button primary">back</a></li>
*
*/

$all_items = $IC->getItems(array("itemtype" => $itemtype, "order" => "position ASC"));
?>
<div class="scene defaultList <?= $itemtype ?>List">
	<h1>TODO lists</h1>

	<ul class="actions">
		<?= $model->actionslink("New list", "/admin/".$itemtype."/new", array("class" => "button primary key:n", "li_class" => "new")) ?>
		if($page->)
		<li class="back"><a href="action" class="button primary">back</a></li>

		<?= $model->li_a("New list", "/admin/".$itemtype."/new", array("key" => "key:n", "class" => "new")) ?>
		<?= $model->li_input("New list", "/admin/".$itemtype."/new", array("key" => "key:n", "class" => "new")) ?>

		<?= $model->li_primary_button("New list", "/admin/".$itemtype."/new", array("key" => "key:n", "class" => "new")) ?>
		<?= $model->li_input("New list", "/admin/".$itemtype."/new", array("key" => "key:n", "class" => "new")) ?>
		<?= $model->li_button("New list", "/admin/".$itemtype."/new", array("key" => "key:n", "class" => "new")) ?>

		<?= $model->actionPriA("New list", "/admin/".$itemtype."/new", array("key" => "key:n", "class" => "new")) ?>
		<?= $model->actionPriI("New list", "/admin/".$itemtype."/new", array("key" => "key:n", "class" => "new")) ?>

	</ul>


	<div class="all_items i:defaultList taggable filters sortable">
<?		if($all_items): ?>
		<ul class="items targets:draggable" data-save-order="/admin/<?= $itemtype ?>/updateOrder">
<?			foreach($all_items as $item): 
				$item = $IC->extendItem($item, array("tags" => true)); ?>
			<li class="item draggable id:<?= $item["item_id"] ?>">
				<div class="drag"></div>
				<h3><?= $item["name"] ?></h3>

<?				if($item["tags"]): ?>
				<ul class="tags">
<?					foreach($item["tags"] as $tag): ?>
					<li><span class="context"><?= $tag["context"] ?></span>:<span class="value"><?= $tag["value"] ?></span></li>
<?					endforeach; ?>
				</ul>
<?				endif; ?>

				<ul class="actions">
					<li class="edit"><a href="/admin/<?= $itemtype ?>/edit/<?= $item["id"] ?>" class="button">Edit</a></li>
					<li class="delete">
						<form action="/admin/cms/delete/<?= $item["id"] ?>" class="i:formDefaultDelete" method="post" enctype="multipart/form-data">
							<input type="submit" value="Delete" class="button delete" />
						</form>
					</li>
					<li class="status">
						<form action="/admin/cms/<?= ($item["status"] == 1 ? "disable" : "enable") ?>/<?= $item["id"] ?>" class="i:formDefaultStatus" method="post" enctype="multipart/form-data">
							<input type="submit" value="<?= ($item["status"] == 1 ? "Disable" : "Enable") ?>" class="button status" />
						</form>
					</li>
				</ul>
			 </li>
<?			endforeach; ?>
		</ul>
<?		else: ?>
		<p>No lists.</p>
<?		endif; ?>
	</div>

</div>
