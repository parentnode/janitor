<?php
global $action;
global $IC;
global $model;
global $itemtype;
// get todolists
//$todolists = $IC->getTags(array("context" => "todolist", "order" => "value"));
$todolists = $IC->getItems(array("itemtype" => "todolist", "order" => "position ASC", "extend" => array("tags" => true)));

// get all todos for complete overview
$todos = $IC->getItems(array("itemtype" => $itemtype, "order" => "items.status DESC, todo.state DESC, todo.deadline DESC, todo.priority DESC", "extend" => array("tags" => true, "user" => true)));

// reset "return to todolist" state
session()->reset("return_to_todolist");
?>
<div class="scene defaultList <?= $itemtype ?>List">
	<h1>TODOs</h1>

	<ul class="actions">
		<?= $JML->listNew(array("label" => "New task")) ?>
		<?= $HTML->link("New todolist", "/janitor/admin/todolist/new", array("class" => "button primary", "wrapper" => "li.todolist")) ?>
	</ul>

	<div class="todolists">
		<h2>Todolists</h2>
		<div class="all_items i:defaultList filters sortable"
			data-csrf-token="<?= session()->value("csrf") ?>"
			data-item-order="<?= $this->validPath("/janitor/admin/todolist/updateOrder") ?>"
		>
	<?		if($todolists): ?>
			<ul class="items ">
	<?			foreach($todolists as $todolist):
					$tag_index = arrayKeyValue($todolist["tags"], "context", "todolist");
					if($tag_index !== false) {
						$tag = "todolist:".addslashes($todolist["tags"][$tag_index]["value"]);
						$todolist_todos = $IC->getItems(array("itemtype" => "todo", "tags" => $tag));
					}
					// create tag if todolist doesnt have tag already
					else {
						$todolist_todos = array();
					}
	 ?>
				<li class="item draggable item_id:<?= $todolist["id"] ?>">
					<div class="drag"></div>
					<h3><?= $todolist["name"] ?> (<?= count($todolist_todos) ?> todos)</h3>
					<ul class="actions">
						<?= $model->link("View", "/janitor/admin/todolist/edit/".$todolist["id"], array("class" => "button primary", "wrapper" => "li.edit")); ?>
						<?= $JML->deleteButton("Delete", "/janitor/admin/todolist/delete/".$todolist["id"], array("js" => true)); ?>
						<?= $JML->statusButton("Enable", "Disable", "/janitor/admin/todolist/status", $todolist, array("js" => true)); ?>
					</ul>
				</li>
	<?			endforeach; ?>
			</ul>
	<?		else: ?>
			<p>No todos.</p>
	<?		endif; ?>
		</div>
	</div>


	<div class="todos">
		<h2>All todos</h2>
		<div class="all_items i:defaultList taggable filters"<?= $JML->jsData() ?>>
	<?		if($todos): ?>
			<ul class="items">
	<?			foreach($todos as $item): ?>
				<li class="item item_id:<?= $item["id"] ?>">
					<h3><?= $item["name"] ?></h3>

					<p class="description"><?= $item["description"] ?></p>
					<dl class="info">
						<dt class="state">State</dt>
						<dd class="state <?= strtolower($model->todo_state[$item["state"]]) ?>"><?= $model->todo_state[$item["state"]] ?></dd>
						<dt class="priority">Priority</dt>
						<dd class="priority <?= strtolower($model->todo_priority[$item["priority"]]) ?>"><?= $model->todo_priority[$item["priority"]] ?></dd>
						<? if(strtotime($item["deadline"]) > 0): ?>
						<dt class="deadline">Deadline</dt>
						<dd class="deadline<?= strtotime($item["deadline"]) < time() ? " overdue" : "" ?>"><?= date("Y-m-d", strtotime($item["deadline"])) ?></dd>
						<? endif; ?>
						<dt class="assigned_to">Assigned to</dt>
						<dd class="assigned_to"><?= $item["user_nickname"] ?></dd>
					</dl>

					<?= $JML->tagList($item["tags"]) ?>

					<?= $JML->listActions($item) ?>
				 </li>
	<?			endforeach; ?>
			</ul>
	<?		else: ?>
			<p>No tasks.</p>
	<?		endif; ?>
		</div>
	</div>
</div>
