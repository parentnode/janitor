<?php
global $action;
global $model;

$navigation_id = $action[1];

$link_options = $model->getLinkOptions();

?>
<div class="scene i:scene defaultNew navigationNodeNew">
	<h1>New navigation node</h1>

	<ul class="actions">
		<?= $model->link("Node list", "/janitor/admin/navigation/edit/".$navigation_id, ["class" => "button", "wrapper" => "li.cancel"]) ?>
	</ul>

	<div class="item i:newNavigationNode" data-validate-link="<?= security()->validPath("/janitor/admin/navigation/validateNodeLink") ?>">
		<h2>Create a new navigation node</h2>
		<?= $model->formStart("/janitor/admin/navigation/saveNode", ["class" => "labelstyle:inject"]) ?>
			<?= $model->input("navigation_id", ["type" => "hidden", "value" => $navigation_id]) ?>

			<p>
				A navigation node can point to a specific controller, that enables viewing or listing
				available Items (posts, pages, events etc.), or otherwise provides pages and functionality related to Modules.
				Options may vary depending on the ItemType and Theme.
			</p>
			<p>
				The controllers are able to render templates in accordance with your current Theme and
				they are created and maintained via the related Module settings panel.<br />
				Below you will find the list of availble Items, Item lists and other views that can be linked to.
			</p>
			<p>
				A navigation node can also be a static internal or external link or even work as a "folder" in your navigation structure.
				<br />
				Just type your link below or eave the Link field empty to create folder.
			</p>

			<fieldset>
				<?= $model->input("node_name") ?>
				<?= $model->input("node_classname") ?>
				<?= $model->input("node_link", ["options" => $link_options]) ?>
				<?= $model->input("node_target", array("type" => "checkbox")) ?>
			</fieldset>

			<fieldset>
				<h3>Fallback link</h3>
				<p>
					On rare occasions you may want to create a link to a page with access restrictions. As default the link will not be shown to users without permission, 
					but you can state a fallback url, which will instead be presented to unauthorised users.
				</p>
				<?= $model->input("node_fallback") ?>
			</fieldset>

			<ul class="actions">
				<?= $model->link("Back", "/janitor/admin/navigation/edit/".$navigation_id, ["class" => "button key:esc", "wrapper" => "li.cancel"]) ?>
				<?= $model->submit("Save", ["class" => "primary key:s", "wrapper" => "li.save"]) ?>
			</ul>
		<?= $model->formEnd() ?>
	</div>
</div>

