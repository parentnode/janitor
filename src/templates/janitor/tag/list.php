<?php
global $action;
global $model;

$tags = $model->getTags();

?>
<div class="scene i:scene defaultList tagList i:tagList">
	<h1>Tags</h1>
	<ul class="actions">
		<?= $JML->listNew(array("label" => "New tag")) ?>
	</ul>

	<? if(message()->hasMessages()): ?>
	<div class="messages">
	<?
	$all_messages = message()->getMessages();
	message()->resetMessages();
	foreach($all_messages as $type => $messages):
		foreach($messages as $message): ?>
		<p class="<?= $type ?>"><?= $message ?></p>
		<? endforeach;?>
	<? endforeach;?>
	</div>
	<? endif; ?>

	<p>
		Tags are used to index the content of the website in a very flexible way.
		A tag is made up of a <em>context</em> and a <em>value</em>, and you are 
		free to make up both contexts and values to fit your specific indexing purposes.
	</p>
	<p>
		When written out the context and value is separated by a kolon (:), ie. <em>category:Wormhole equipment</em>. 
		Typically the context is only used internally, while the value is shown to the user in specific contexts.
	</p>
	<p>
		It makes sense to devise a structured tag collection, with meaningful contexts for different use cases. Use coherent values 
		to make the information more easily available to the end users.
	</p>
	<p>
		Some tags are required for
		certain pages. You should NOT delete or edit tags, unless you know what you are doing.
	</p>

	<div class="all_items i:defaultList filters">
<?		if($tags): ?>
		<ul class="items">
<?			foreach($tags as $tag): ?>
			<li class="item tag_id:<?= $tag["id"] ?><?= !$tag["tag_count"] ? " unused" : "" ?>">
				<h3><?= $tag["context"] ?>:<?= $tag["value"] ?> <span class="count">(<?= pluralize($tag["tag_count"], "item", "items") ?>)</span></h3>
				
				<ul class="actions">
					<?= $HTML->link("Edit", "/janitor/admin/tag/edit/".$tag["id"], array("class" => "button", "wrapper" => "li.edit")) ?>
					<?= $HTML->oneButtonForm("Delete", "/janitor/admin/tag/deleteTag/".$tag["id"], array(
						"js" => true,
						"wrapper" => "li.delete",
						"static" => true
					)) ?>
				</ul>
			 </li>
<?			endforeach; ?>
		</ul>
<?		else: ?>
		<p>No tags.</p>
<?		endif; ?>
	</div>

</div>
