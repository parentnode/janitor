<?php
global $action;
global $IC;
global $model;
global $itemtype;

$model_todo = $IC->typeObject("todo");

$item_id = $action[1];
$item = $IC->getItem(array("id" => $item_id, "extend" => array("tags" => true)));

// get tag for todolist
$tag = $IC->getTags(array("item_id" => $item_id, "context" => "todolist"));


// todo states
$todo_states = $model_todo->todo_state;
krsort($todo_states);

// show specific state listing
if(count($action) > 3 && $action[2] == "state") {
	$todo_state_view = $action[3];
	settype($todo_state_view, "int");
 	session()->value("todo_state_view", $todo_state_view);

	$todos = $IC->getItems(array("itemtype" => "todo", "where" => "todo.state = ".$todo_state_view, "tags" => "todolist:".addslashes($tag[0]["value"]), "order" => "items.status DESC, todo.deadline DESC, todo.priority DESC", "extend" => array("tags" => true, "user" => true)));
}
// show all states
else {
	$todo_state_view = false;
	session()->reset("todo_state_view");

	$todos = $IC->getItems(array("itemtype" => "todo", "tags" => "todolist:".addslashes($tag[0]["value"]), "order" => "items.status DESC, todo.state DESC, todo.deadline DESC, todo.priority DESC", "extend" => array("tags" => true, "user" => true)));
}


// remember todolist to return to
session()->value("return_to_todolist", $item_id);
?>
<div class="scene i:scene defaultEdit <?= $itemtype ?>Edit">
	<h1>Edit TODO list</h1>
	<h2><?= $item["name"] ?></h2>

	<ul class="actions i:defaultEditActions">
		<?= $model->link("Back to overview", "/janitor/admin/todo/list" . ($todo_state_view ? "/state/".$todo_state_view : ""), array("class" => "button", "wrapper" => "li.cancel")); ?>
		<?= $model->link("New task", "/janitor/admin/todo/new", array("class" => "button primary", "wrapper" => "li.new")); ?>
		<?= $model->oneButtonForm("Delete", "/janitor/admin/todolist/delete/".$item["id"], array(
			"wrapper" => "li.delete",
			"success-location" => "/janitor/admin/todo/list" . ($todo_state_view ? "/state/".$todo_state_view : "")
		)) ?>
	</ul>

	<div class="status i:defaultEditStatus item_id:<?= $item["id"]?>" data-csrf-token="<?= session()->value("csrf") ?>">
		<ul class="actions">
			<?= $JML->statusButton("Enable", "Disable", "/janitor/admin/todolist/status", $item, array("js" => true)); ?>
		</ul>
	</div>


	<div class="item i:defaultEdit i:collapseHeader">
		<h2>Todolist</h2>
		<?= $model->formStart("update/".$item["id"], array("class" => "labelstyle:inject")) ?>

			<fieldset>
				<?= $model->input("name", array("value" => $item["name"])) ?>
				<?= $model->input("description", array("value" => $item["description"])) ?>
			</fieldset>

			<ul class="actions">
				<?= $model->submit("Update", array("class" => "primary key:s", "wrapper" => "li.save")); ?>
			</ul>

		<?= $model->formEnd() ?>
	</div>


	<div class="todos">
		<h2>Todos</h2>

		<? if($todo_states): ?>
		<ul class="tabs">
			<? foreach($todo_states as $todo_state_id => $todo_state): ?>
				<?= $HTML->link($todo_state, "/janitor/admin/todolist/edit/".$item["id"] . "/state/" . $todo_state_id, array("wrapper" => "li.".strtolower($todo_state) . ($todo_state_id === $todo_state_view ? ".selected" : ""))) ?>
			<? endforeach; ?>
			<?= $HTML->link("All", "/janitor/admin/todolist/edit/".$item["id"], array("wrapper" => "li.all" . ($todo_state_view === false ? ".selected" : ""))) ?>
		</ul>
		<? endif; ?>

		<div class="all_items i:defaultList todolist_id:<?= $item["id"]?> filters"
			data-csrf-token="<?= session()->value("csrf") ?>"
			data-tag-get="<?= security()->validPath("/janitor/admin/items/tags") ?>" 
			data-tag-delete="<?= security()->validPath("/janitor/admin/todo/deleteTag") ?>"
			data-tag-add="<?= security()->validPath("/janitor/admin/todo/addTag") ?>"
			>
	<?		if($todos): ?>
			<ul class="items">
				<? foreach($todos as $item): ?>
				<li class="item item_id:<?= $item["id"] ?>">
					<h3><?= $item["name"] ?></h3>
					<p class="description"><?= $item["description"] ?></p>
					<dl class="info">
<?
// don't show state if you're on specific state view
if(!$todo_state_view):
?>
						<dt class="state">State</dt>
						<dd class="state <?= strtolower($model_todo->todo_state[$item["state"]]) ?>"><?= $model_todo->todo_state[$item["state"]] ?></dd>
<? endif; ?>

<? 
// if todo is not done yet
if($item["state"] != 1): ?>
						<dt class="priority">Priority</dt>
						<dd class="priority <?= strtolower($model_todo->todo_priority[$item["priority"]]) ?>"><?= $model_todo->todo_priority[$item["priority"]] ?></dd>
						<? if(strtotime($item["deadline"]) > 0): ?>
						<dt class="deadline">Deadline</dt>
						<dd class="deadline<?= strtotime($item["deadline"]) < time() ? " overdue" : "" ?>"><?= date("Y-m-d", strtotime($item["deadline"])) ?></dd>
						<? endif; ?>
						<dt class="assigned_to">Assigned to</dt>
						<dd class="assigned_to"><?= $item["user_nickname"] ?></dd>
<? endif; ?>
					</dl>

					<?= $JML->tagList($item["tags"]) ?>

					<ul class="actions">
						<?= $model->link("Edit", "/janitor/admin/todo/edit/".$item["id"], array("class" => "button", "wrapper" => "li.edit")); ?>
						<?= $model->oneButtonForm("Delete", "/janitor/admin/todo/delete/".$item["id"], array(
							"js" => true,
							"wrapper" => "li.delete",
							"static" => true
						)) ?>
						<?= $JML->statusButton("Enable", "Disable", "/janitor/admin/todo/status", $item, array("js" => true)); ?>
					</ul>

				 </li>
			 	<? endforeach; ?>
			</ul>
	<?		else: ?>
			<p>No tasks.</p>
	<?		endif; ?>
		</div>

	</div>

	<?= $JML->editSindex($item) ?>

	<?= $JML->editDeveloperSettings($item) ?>

</div>
