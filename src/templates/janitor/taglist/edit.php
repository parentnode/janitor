<?php
global $action;

global $model;
global $itemtype;

$taglist_id = $action[1];
$taglist = $model->getTaglist(array("taglist_id" => $taglist_id));
//$taglist_tags = $model->getTaglistTags(["taglist_id" => $taglist_id]);
//print_r($taglist);
?>
<div class="scene defaultEdit taglistList <?= $itemtype ?>Edit">
	<h1>Edit taglist</h1>
	<h2 ><?=strip_tags($taglist["name"]) ?></h2>

	<?= $JML->editGlobalActions($taglist, ["modify"=>[
		"delete"=>[
			"url"=>"/janitor/admin/taglist/deleteTaglist/".$taglist_id
		],
		"duplicate"=>[
			"url"=>"/janitor/admin/taglist/duplicateTaglist/".$taglist_id
		],
		"status"=>false
	]]) ?>


	<div class="item i:defaultEdit">
		<h2>Taglist content</h2>
		<?= $model->formStart("updateTaglist/".$taglist["id"], array("class" => "labelstyle:inject")) ?>

			<fieldset>

				<?= $model->input("name", array("value" => $taglist["name"])) ?>
				<p class="handle">Handles are being used programmatically. Don't edit it if you don't know what you are doing </p>
				<?= $model->input("handle", array("value" => $taglist["handle"])) ?>

			</fieldset>

			<?= $JML->editActions($taglist) ?>

		<?= $model->formEnd() ?>
	</div>

	<div class="all_items taglist_tags i:defaultList sortable"
			data-csrf-token="<?= session()->value("csrf") ?>"
			data-item-order="<?= security()->validPath("/janitor/admin/taglist/updateOrder/".$taglist_id) ?>"
		>

		<h2>Added tags</h2>

		<? if($taglist["tags"]): ?>
		<ul class="items">
			<? foreach($taglist["tags"] as $taglist_tag): ?>
				<li class="item item_id:<?= $taglist_tag["id"] ?>">
					<h3><?= strip_tags($taglist_tag["context"]) ?>:<?= strip_tags($taglist_tag["value"]) ?></h3>
				</li>
			<? endforeach; ?>
		</ul>

		<? else: ?>
			<p>No tag has been added yet</p>

		<? endif; ?>

		<ul class="actions">
			<?= $model->link("Add/Remove Tags", "/janitor/admin/taglist/add/".$taglist_id, ["class"=>"button", "wrapper"=>"li.add" ]);?>
		</ul>

	</div>

</div>
