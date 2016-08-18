<?php
global $action;
global $IC;
global $model;
global $itemtype;
// get todolists
//$todolists = $IC->getTags(array("context" => "todolist", "order" => "value"));
$todolists = $IC->getItems(array("itemtype" => "todolist", "order" => "position ASC", "extend" => array("tags" => true)));


// todo states
$todo_states = $model->todo_state;
krsort($todo_states);

// show specific state listing
if(count($action) > 2 && $action[1] == "state") {
	$todo_state_view = $action[2];
	settype($todo_state_view, "int");
	session()->value("todo_state_view", $todo_state_view);

	// get all todos for complete overview
	$todos = $IC->getItems(array("itemtype" => $itemtype, "where" => "todo.state = ".$todo_state_view, "order" => "items.status DESC, todo.deadline DESC, todo.priority DESC", "extend" => array("tags" => true, "user" => true)));
}
// show all states
else {
	$todo_state_view = false;
	session()->reset("todo_state_view");

	// get all todos for complete overview
	$todos = $IC->getItems(array("itemtype" => $itemtype, "order" => "items.status DESC, todo.state DESC, todo.deadline DESC, todo.priority DESC", "extend" => array("tags" => true, "user" => true)));
}


// reset "return to todolist" state
session()->reset("return_to_todolist");
?>
<div class="scene i:scene defaultList <?= $itemtype ?>List">
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
						<?= $model->link("View", "/janitor/admin/todolist/edit/".$todolist["id"].($todo_state_view ? "/state/".$todo_state_view : ""), array("class" => "button primary", "wrapper" => "li.edit")); ?>
						<?= $JML->oneButtonForm("Delete", "/janitor/admin/todolist/delete/".$todolist["id"], array(
							"js" => true,
							"wrapper" => "li.delete",
							"static" => true
						)) ?>
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
		<h2>Todo overview</h2>

		<? if($todo_states): ?>
		<ul class="tabs">
			<? foreach($todo_states as $todo_state_id => $todo_state): ?>
				<?= $HTML->link($todo_state, "/janitor/admin/todo/list/state/".$todo_state_id, array("wrapper" => "li.".strtolower($todo_state) . ($todo_state_id === $todo_state_view ? ".selected" : ""))) ?>
			<? endforeach; ?>
			<?= $HTML->link("All", "/janitor/admin/todo/list", array("wrapper" => "li.all" . ($todo_state_view === false ? ".selected" : ""))) ?>
		</ul>
		<? endif; ?>

		<div class="all_items i:defaultList taggable filters"<?= $JML->jsData() ?>>
	<?		if($todos): ?>
			<ul class="items">
	<?			foreach($todos as $item): ?>
				<li class="item item_id:<?= $item["id"] ?>">
					<h3><?= $item["name"] ?></h3>

					<p class="description"><?= $item["description"] ?></p>

					<?= $JML->tagList($item["tags"]) ?>

					<?= $JML->listActions($item); ?>
				 </li>
	<?			endforeach; ?>
			</ul>
	<?		else: ?>
			<p>No tasks.</p>
	<?		endif; ?>
		</div>
	</div>
</div>
