<?php
global $action;
global $IC;
global $model;
global $itemtype;

$item = $IC->getCompleteItem(array("id" => $action[1]));
$item_id = $item["item_id"];

// set page description
$this->pageDescription($item["description"]);
?>
<div class="scene defaultEdit <?= $itemtype ?>Edit">
	<h1>Edit page</h1>

	<ul class="actions i:defaultEditActions item_id:<?= $item_id ?>"
		data-csrf-token="<?= session()->value("csrf") ?>"
		>
		<?= $HTML->link("List", "/admin/".$itemtype."/list", array("class" => "button", "wrapper" => "li.cancel")) ?>
		<?= $HTML->deleteButton("Delete", "/admin/cms/delete/".$item["id"], array("js" => true)) ?>
	</ul>

	<div class="status i:defaultEditStatus item_id:<?= $item["id"] ?>"
		data-csrf-token="<?= session()->value("csrf") ?>"
		>
		<ul class="actions">
			<?= $HTML->statusButton("Enable", "Disable", "/admin/cms/status", $item, array("js" => true)) ?>
		</ul>
	</div>

	<div class="media main_media i:addMediaSingle variant:main item_id:<?= $item_id ?>"
		data-delete-media="<?= $this->validAction("/admin/".$itemtype."/deleteMedia") ?>"
	>
		<h2>Main image</h2>
		<?= $model->formStart("/admin/".$itemtype."/addMain/".$item_id, array("class" => "upload labelstyle:inject")) ?>
			<fieldset>
				<?= $model->input("main_media") ?>
			</fieldset>
		<?= $model->formEnd() ?>

<?	if(isset($item["main_media"])): ?>
		<img src="/images/<?= $item_id ?>/main/500x.<?= $item["main_media"]["format"] ?>" />
<?	endif; ?>

	</div>

	<div class="item i:defaultEdit">
		<h2>Page text</h2>
		<?= $model->formStart("/admin/cms/update/".$item_id, array("class" => "labelstyle:inject")) ?>
			<fieldset>
				<?= $model->input("name", array("value" => $item["name"])) ?>
				<?= $model->input("published_at", array("value" => date("Y-m-d H:i", strtotime($item["published_at"])))) ?>
				<?= $model->input("description", array("class" => "autoexpand short", "value" => $item["description"])) ?>

				<?= $model->input("html", array("value" => $item["html"])) ?>
			</fieldset>

			<ul class="actions">
				<?= $model->link("Back", "/admin/".$itemtype."/list", array("class" => "button key:esc", "wrapper" => "li.cancel")) ?>
				<?= $model->submit("Update", array("class" => "primary key:s", "wrapper" => "li.save")) ?>
			</ul>
		<?= $model->formEnd() ?>
	</div>

	<div class="media i:addMedia sortable item_id:<?= $item_id ?>"
		data-save-order="/admin/<?= $itemtype ?>/updateMediaOrder" 
		data-delete-media="<?= $this->validAction("/admin/".$itemtype."/deleteMedia") ?>"
		>
		<h2>Media</h2>
		<?= $model->formStart("/admin/".$itemtype."/addMedia/".$item_id, array("class" => "upload labelstyle:inject")) ?>
			<fieldset>
				<?= $model->input("mediae") ?>
			</fieldset>

			<ul class="actions">
				<?= $model->submit("Add media here", array("class" => "primary", "wrapper" => "li.save")) ?>
			</ul>
		<?= $model->formEnd() ?>

		<ul class="mediae">
<?		if($item["mediae"]): ?>
<?			foreach($item["mediae"] as $index => $media): ?>
			<li class="media image variant:<?= $index ?> media_id:<?= $media["id"] ?>">
				<img src="/images/<?= $item_id ?><?= ($index ? "/".$index : "") ?>/x150.<?= $media["format"] ?>" />
			</li>
<?			endforeach; ?>
<?		endif; ?>
		</ul>

	</div>

	<div class="tags i:defaultTags item_id:<?= $item_id ?>"
		data-get-tags="<?= $this->validAction("/admin/cms/tags") ?>" 
		data-delete-tag="<?= $this->validAction("/admin/cms/tags/delete") ?>"
		>
		<h2>Tags</h2>
		<?= $model->formStart("/admin/cms/tags/add/".$item_id, array("class" => "labelstyle:inject")) ?>
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
