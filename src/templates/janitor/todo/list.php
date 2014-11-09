<?php
global $action;
global $IC;
global $model;
global $itemtype;

$all_items = $IC->getItems(array("itemtype" => $itemtype, "order" => "items.status DESC, todo.deadline DESC, todo.priority DESC"));
?>
<div class="scene defaultList <?= $itemtype ?>List">
	<h1>TODOs</h1>

	<ul class="actions">
		<?= $HTML->link("New task", "/janitor/".$itemtype."/new", array("class" => "button primary key:n", "wrapper" => "li.new")) ?>
	</ul>

	<div class="all_items i:defaultList taggable filters"
		data-csrf-token="<?= session()->value("csrf") ?>"
		data-get-tags="<?= $this->validPath("/janitor/admin/items/tags") ?>" 
		data-delete-tag="<?= $this->validPath("/janitor/admin/items/tags/delete") ?>"
		data-add-tag="<?= $this->validPath("/janitor/admin/items/tags/add") ?>"
		>
<?		if($all_items): ?>
		<ul class="items">
<?			foreach($all_items as $item): 
				$item = $IC->extendItem($item, array("tags" => true)); ?>
			<li class="item item_id:<?= $item["id"] ?>">
				<h3><?= $item["name"] ?></h3>
				<dl>
					<dt class="priority">Priority</dt>
					<dd class="priority"><?= $model->todo_priority[$item["priority"]] ?></dd>
					<dt class="deadline">Deadline</dt>
					<dd class="deadline"><?= date("Y-m-d, h:i:s", strtotime($item["deadline"])) ?></dd>
				</dl>
<?				if($item["tags"]): ?>
				<ul class="tags">
<?					foreach($item["tags"] as $tag): ?>
					<li><span class="context"><?= $tag["context"] ?></span>:<span class="value"><?= $tag["value"] ?></span></li>
<?					endforeach; ?>
				</ul>
<?				endif; ?>

				<ul class="actions">
					<?= $HTML->link("Edit", "/janitor/".$itemtype."/edit/".$item["id"], array("class" => "button", "wrapper" => "li.edit")) ?>
					<?= $HTML->deleteButton("Delete", "/janitor/admin/items/delete/".$item["id"], array("js" => true)) ?>
					<?= $HTML->statusButton("Enable", "Disable", "/janitor/admin/items/status", $item, array("js" => true)) ?>
				</ul>
			 </li>
<?			endforeach; ?>
		</ul>
<?		else: ?>
		<p>No tasks.</p>
<?		endif; ?>
	</div>

</div>
