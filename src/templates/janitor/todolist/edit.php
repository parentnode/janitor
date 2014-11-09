<?php
global $action;
global $IC;
global $model;
global $itemtype;

$item = $IC->getCompleteItem(array("id" => $action[1]));
$item_id = $item["item_id"];
?>
<div class="scene defaultEdit <?= $itemtype ?>Edit">
	<h1>Edit TODO list</h1>

	<ul class="actions i:defaultEditActions item_id:<?= $item_id ?>"
		data-csrf-token="<?= session()->value("csrf") ?>"
		>
		<?= $HTML->link("List", "/janitor/".$itemtype."/list", array("class" => "button", "wrapper" => "li.cancel")) ?>
		<?= $HTML->deleteButton("Delete", "/janitor/admin/items/delete/".$item["id"], array("js" => true)) ?>
	</ul>

	<div class="status i:defaultEditStatus item_id:<?= $item["id"] ?>"
		data-csrf-token="<?= session()->value("csrf") ?>"
		>
		<ul class="actions">
			<?= $HTML->statusButton("Enable", "Disable", "/janitor/admin/items/status", $item, array("js" => true)) ?>
		</ul>
	</div>

	<div class="item i:defaultEdit">
		<h2>Todo list</h2>
		<?= $model->formStart("/janitor/admin/items/update/".$item_id, array("class" => "labelstyle:inject")) ?>
			<fieldset>
				<?= $model->input("name", array("value" => $item["name"])) ?>
				<?= $model->input("class", array("value" => $item["class"])) ?>
			</fieldset>

			<ul class="actions">
				<?= $model->link("Back", "/janitor/".$itemtype."/list", array("class" => "button key:esc", "wrapper" => "li.cancel")) ?>
				<?= $model->submit("Update", array("class" => "primary key:s", "wrapper" => "li.save")) ?>
			</ul>
		<?= $model->formEnd() ?>
	</div>

	<div class="tags i:defaultTags item_id:<?= $item_id ?>"
		data-get-tags="<?= $this->validPath("/janitor/admin/items/tags") ?>" 
		data-delete-tag="<?= $this->validPath("/janitor/admin/items/tags/delete") ?>"
		>
		<h2>Tags</h2>
		<p>Add tag identifyer for this TODO list. TODOs with this/these tags will automatically be added to this list.</p>
		<?= $model->formStart("/janitor/admin/items/tags/add/".$item_id, array("class" => "labelstyle:inject")) ?>
			<fieldset>
				<?= $model->input("tags") ?>
			</fieldset>

			<ul class="actions">
				<?= $model->submit("Add new tag", array("class" => "primary", "wrapper" => "li.save")) ?>
			</ul>
		<?= $model->formEnd() ?>

		<ul class="tags">
<?		if($item["tags"]): ?>
<?			foreach($item["tags"] as $index => $tag): ?>
			<li class="tag">
				<span class="context"><?= $tag["context"] ?></span>:<span class="value"><?= $tag["value"] ?></span>
			</li>
<?			endforeach; ?>
<?		endif; ?>
		</ul>
	</div>

</div>
