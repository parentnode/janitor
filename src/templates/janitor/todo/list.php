<?php
global $action;
global $IC;
global $model;
global $itemtype;

$items = $IC->getItems(array("itemtype" => $itemtype, "order" => "items.status DESC, todo.deadline DESC, todo.priority DESC", "extend" => array("tags" => true, "user" => true)));
?>
<div class="scene defaultList <?= $itemtype ?>List">
	<h1>TODOs</h1>

	<ul class="actions">
		<?= $JML->listNew(array("label" => "New task")) ?>
		<?= $HTML->link("TODO Lists", "/janitor/admin/todolist/list", array("class" => "button", "wrapper" => "li.todolist")) ?>
	</ul>

	<div class="all_items i:defaultList taggable filters"<?= $JML->jsData() ?>>
<?		if($items): ?>
		<ul class="items">
<?			foreach($items as $item): ?>
			<li class="item item_id:<?= $item["id"] ?>">
				<h3><?= $item["name"] ?></h3>
				<dl class="info">
					<dt class="priority">Priority</dt>
					<dd class="priority <?= strtolower($model->todo_priority[$item["priority"]]) ?>"><?= $model->todo_priority[$item["priority"]] ?></dd>
					<dt class="deadline">Deadline</dt>
					<dd class="deadline<?= strtotime($item["deadline"]) < time() ? " overdue" : "" ?>"><?= date("Y-m-d", strtotime($item["deadline"])) ?></dd>
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
