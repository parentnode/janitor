<?php
global $action;
global $model;

$IC = new Items();

$tag = $IC->getTags(array("tag_id" => $action[1]));
?>
<div class="scene i:scene defaultEdit tagEdit">
	<h1>Edit tag</h1>

	<ul class="actions">
		<?= $HTML->link("List", "/janitor/admin/tag/list", array("class" => "button", "wrapper" => "li.cancel")) ?>
	</ul>

	<div class="item i:defaultEdit">
		<h2>Tag info</h2>
		<?= $model->formStart("/janitor/admin/tag/updateTag/".$tag["id"], array("class" => "labelstyle:inject")) ?>
			<fieldset>
				<?= $model->input("context", array("value" => $tag["context"])) ?>
				<?= $model->input("value", array("value" => $tag["value"])) ?>
				<?= $model->input("description", array("value" => $tag["description"])) ?>
			</fieldset>

			<ul class="actions">
				<?= $model->link("Back", "/janitor/admin/tag/list", array("class" => "button key:esc", "wrapper" => "li.cancel")) ?>
				<?= $model->submit("Update", array("class" => "primary key:s", "wrapper" => "li.save")) ?>
			</ul>
		<?= $model->formEnd() ?>
	</div>


	<div class="all_items">
		<h2>Items with tag</h2>
<? 		if($tag["items"]): ?>
		<ul class="items">
<? 			foreach($tag["items"] as $item):
				$item = $IC->extendItem($item);
				
				// find path to itemtype
				// We don know whether it is an inherited controller or a local one
				// - look in the two most obvious places
				if(file_exists(LOCAL_PATH."/www/janitor/".$item["itemtype"].".php")) {
					$path = "/janitor/".$item["itemtype"];
				}
				else if(file_exists(FRAMEWORK_PATH."/www/".$item["itemtype"].".php")) {
					$path = "/janitor/admin/".$item["itemtype"];
				}
				else {
					$path = false;
				}
?>
			<li class="item">
				<h3><?= $item["name"] ?></h3>
				<dl class="info">
					<dt class="itemtype">Itemtype</dt>
					<dd class="itemtype"><?= $item["itemtype"] ?></dd>
					<dt class="status">Status</dt>
					<dd class="status"><?= $item["status"] ? "enabled" : "disabled" ?></dd>
				</dl>
			<ul class="actions">
<? if(security()->validatePath($path."/edit")): ?>
				<?= $model->link("Edit", $path."/edit/".$item["item_id"], array("class" => "button", "wrapper" => "li.edit")) ?>
<? endif; ?>
			</ul>
				
			</li>
<? 			endforeach; ?>
		</ul>
<? 		else: ?>
		<p>No items are using this tag.</p>
<? 		endif; ?>

	</div>

</div>