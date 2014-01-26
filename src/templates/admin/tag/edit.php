<?php

$action = $this->actions();

$IC = new Item();
$model = new Model();

$tag = $IC->getTags(array("tag_id" => $action[1]));

?>
<div class="scene defaultEdit tagEdit">
	<h1>Edit tag</h1>

	<ul class="actions">
		<li class="cancel"><a href="/admin/tag/list" class="button">Back</a></li>
	</ul>

	<div class="item i:defaultEdit">
		<form action="/admin/cms/tag/update/<?= $tag["id"] ?>" class="labelstyle:inject" method="post" enctype="multipart/form-data">

			<fieldset>
				<?= $model->input("context", array(
						"type" => "string", 
						"label" => "Tag context",
						"required" => true, 
						"value" => $tag["context"],
						"hint_message" => "Tag context is the scope/category/relation of the tag",
						"error_message" => "Tag context is always required"
				)) ?>
				<?= $model->input("value", array(
						"type" => "string", 
						"label" => "Tag value",
						"required" => true, 
						"value" => $tag["value"],
						"hint_message" => "Tag value is the actual value of the tag",
						"error_message" => "Tag context is always required"
				)) ?>
				<?= $model->input("description", array(
						"type" => "text", 
						"label" => "Optional description",
						"value" => $tag["description"],
						"class" => "autoexpand",
						"hint_message" => "If tag requires any kind of explanation, write it here"
				)) ?>
			</fieldset>

			<ul class="actions">
				<li class="cancel"><a href="/admin/tag/list" class="button key:esc">Back</a></li>
				<li class="save"><input type="submit" value="Update" class="button primary key:s" /></li>
			</ul>

		</form>
	</div>

	<h2>Items with tag</h2>
	<div class="tag_items">
<? 		if($tag["items"]): ?>
		<ul class="tag_items">
<? 			foreach($tag["items"] as $item):
				$item = $IC->getCompleteItem($item["item_id"]); ?>
			<li>
				<dl>
					<dt class="name">Name</dt>
					<dd class="name"><a href="/admin/<?= $item["itemtype"] ?>/edit/<?= $item["item_id"] ?>"><?= $item["name"] ?></a></dd>
					<dt class="itemtype">Itemtype</dt>
					<dd class="itemtype"><?= $item["itemtype"] ?></dd>
					<dt class="status">Status</dt>
					<dd class="status"><?= $item["status"] ? "enabled" : "disabled" ?></dd>
				</dl>
			</li>
<? 			endforeach; ?>
		</ul>
<? 		else: ?>
		<p>No items are using this tag.</p>
<? 		endif; ?>

	</div>

</div>