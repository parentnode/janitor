<?php
global $action;
global $IC;
global $model;
global $itemtype;

include_once("classes/users/superuser.class.php");
$UC = new SuperUser();



$items = $IC->getItems(array("itemtype" => $itemtype, "order" => "status DESC, published_at DESC", "extend" => array("tags" => true)));

$fs = new FileSystem();
$templates = [];

$static_mail_templates = $fs->files(LOCAL_PATH."/templates/mails", ["deny_folders" => "layouts"]);
$static_mail_templates_framework = $fs->files(FRAMEWORK_PATH."/templates/mails", ["deny_folders" => "layouts"]);

foreach($static_mail_templates as $template_path) {
	$template = preg_replace("/\.[A-Za-z0-9]{2,4}/", "", str_replace(LOCAL_PATH."/templates/mails/", "", $template_path));
	if(array_search($template, $templates) === false) {
		$templates[] = $template;
	}
}

foreach($static_mail_templates_framework as $template_path) {
	$template = preg_replace("/\.[A-Za-z0-9]{2,4}/", "", str_replace(FRAMEWORK_PATH."/templates/mails/", "", $template_path));
	if(array_search($template, $templates) === false) {
		$templates[] = $template;
	}
}
?>

<div class="scene i:scene defaultList <?= $itemtype ?>List">
	<h1>Messages</h1>
	<h2>Control panel</h2>

	<ul class="actions">
		<?= $JML->listNew(array("label" => "New message")) ?>
		<?= $HTML->link("Maillists", "/janitor/admin/message/maillists/list", array("class" => "button", "wrapper" => "li.maillists")) ?>
	</ul>
	<p>Select the message you want to send.</p>

	<div class="all_items i:defaultList dynamic taggable filters"<?= $HTML->jsData(["tags", "search"]) ?>>
<?		if($items): ?>
		<ul class="items">
<?			foreach($items as $item): ?>
			<li class="item item_id:<?= $item["id"] ?><?= $HTML->jsMedia($item) ?>">
				<h3><?= strip_tags($item["name"]) ?></h3>

				<?= $JML->tagList($item["tags"]) ?>

				<?= $JML->listActions($item, ["modify" => [
					"edit" => [
						"label" => "Select"
					]
				]]) ?>
			 </li>
<?			endforeach; ?>
		</ul>
<?		else: ?>
		<p>No messages.</p>
<?		endif; ?>
	</div>


	<div class="all_items i:defaultList system i:collapseHeader">
		<h2>System emails</h2>
<?		if($templates): ?>
		<ul class="items">
<?			foreach($templates as $template): 
//				$template = preg_replace("/\.[A-Za-z0-9]{2,4}$/", "", str_replace(LOCAL_PATH."/templates/mails/", "", $template_path));
?>
			<li class="item">
				<h3><?= $template ?></h3>
				<ul class="actions">
					<?= $HTML->link("Select", "/janitor/admin/message/system?template=".$template, ["wrapper" => "li.select", "class" => "button"]) ?>
				</ul>
			 </li>
<?			endforeach; ?>
		</ul>
<?		else: ?>
		<p>No templates.</p>
<?		endif; ?>
	</div>


</div>
