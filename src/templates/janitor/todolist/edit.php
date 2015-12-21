<?php
global $action;
global $IC;
global $model;
global $itemtype;

$model_todo = $IC->typeObject("todo");

$item_id = $action[1];
$item = $IC->getItem(array("id" => $item_id, "extend" => array("tags" => true)));

// get todos order
$ordered_todos = $model->getOrderedTodos($item_id);


// reset "return to todolist" state
//session()->reset("return_to_todolist");
session()->value("return_to_todolist", $item_id);
?>
<div class="scene defaultEdit <?= $itemtype ?>Edit">
	<h1>Edit TODO list</h1>

	<ul class="actions i:defaultEditActions item_id:<?= $item["id"] ?>" data-csrf-token="<?= session()->value("csrf") ?>">
		<?= $model->link("Back", "/janitor/admin/todo/list", array("class" => "button", "wrapper" => "li.cancel")); ?>
		<?= $model->link("New task", "/janitor/admin/todo/new/todolist/".$item["id"], array("class" => "button primary", "wrapper" => "li.new")); ?>
		<?= $JML->deleteButton("Delete", "/janitor/admin/todolist/delete/".$item["id"], array("js" => true)); ?>
	</ul>

	<div class="status i:defaultEditStatus item_id:<?= $item["id"]?>" data-csrf-token="<?= session()->value("csrf") ?>">
		<ul class="actions">
			<?= $JML->statusButton("Enable", "Disable", "/janitor/admin/todolist/status", $item, array("js" => true)); ?>
		</ul>
	</div>


	<div class="item i:defaultEdit">
		<h2>Todolist</h2>
		<?= $model->formStart("update/".$item["id"], array("class" => "labelstyle:inject")) ?>

			<fieldset>
				<?= $model->input("name", array("value" => $item["name"])) ?>
				<?= $model->input("classname", array("value" => $item["classname"])) ?>
				<?= $model->input("description", array("value" => $item["description"])) ?>
			</fieldset>

			<ul class="actions">
				<?= $model->submit("Update", array("class" => "primary key:s", "wrapper" => "li.save")); ?>
			</ul>

		<?= $model->formEnd() ?>
	</div>


	<div class="todos">
		<h2>Todos</h2>
		<div class="all_items i:defaultList todolist_id:<?= $item["id"]?> taggable filters sortable"
			data-csrf-token="<?= session()->value("csrf") ?>"
			data-item-order="<?= $this->validPath("/janitor/admin/todolist/updateTodoOrder/".$item["id"]) ?>"
			data-tag-get="<?= $this->validPath("/janitor/admin/items/tags") ?>" 
			data-tag-delete="<?= $this->validPath("/janitor/admin/todo/deleteTag") ?>"
			data-tag-add="<?= $this->validPath("/janitor/admin/todo/addTag") ?>"
			>
	<?		if($ordered_todos): ?>
			<ul class="items">
				<? foreach($ordered_todos as $item): ?>
				<li class="item draggable item_id:<?= $item["id"] ?>">
					<div class="drag"></div>
					<h3><?= $item["name"] ?></h3>
					<p class="description"><?= $item["description"] ?></p>
					<dl class="info">
						<dt class="state">State</dt>
						<dd class="state <?= strtolower($model_todo->todo_state[$item["state"]]) ?>"><?= $model_todo->todo_state[$item["state"]] ?></dd>
						<dt class="priority">Priority</dt>
						<dd class="priority <?= strtolower($model_todo->todo_priority[$item["priority"]]) ?>"><?= $model_todo->todo_priority[$item["priority"]] ?></dd>
						<? if(strtotime($item["deadline"]) > 0): ?>
						<dt class="deadline">Deadline:</dt>
						<dd class="deadline<?= strtotime($item["deadline"]) < time() ? " overdue" : "" ?>"><?= date("Y-m-d", strtotime($item["deadline"])) ?></dd>
						<? endif; ?>
						<dt class="assigned_to">Assigned to</dt>
						<dd class="assigned_to"><?= $item["user_nickname"] ?></dd>
					</dl>

					<?= $JML->tagList($item["tags"]) ?>

					<ul class="actions">
						<?= $model->link("Edit", "/janitor/admin/todo/edit/".$item["id"], array("class" => "button", "wrapper" => "li.edit")); ?>
						<?= $JML->deleteButton("Delete", "/janitor/admin/todo/delete/".$item["id"], array("js" => true)); ?>
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

</div>
