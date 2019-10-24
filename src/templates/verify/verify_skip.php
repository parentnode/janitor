<?php
global $action;
global $model;

$IC = new Items();
$page_item = $IC->getItem(array("tags" => "page:verify-skipped", "status" => 1, "extend" => array("user" => true, "tags" => true, "mediae" => true)));
if($page_item) {
	$this->sharingMetaData($page_item);
}


?>
<div class="scene signup i:scene">

<? if($page_item): 
	$media = $IC->sliceMediae($page_item, "single_media"); ?>
	<div class="article i:article id:<?= $page_item["item_id"] ?>" itemscope itemtype="http://schema.org/Article">

		<? if($media): ?>
		<div class="image item_id:<?= $page_item["item_id"] ?> format:<?= $media["format"] ?> variant:<?= $media["variant"] ?>"></div>
		<? endif; ?>


		<?= $HTML->articleTags($page_item, [
			"context" => false
		]) ?>


		<h1 itemprop="headline"><?= $page_item["name"] ?></h1>

		<? if($page_item["subheader"]): ?>
		<h2 itemprop="alternativeHeadline"><?= $page_item["subheader"] ?></h2>
		<? endif; ?>


		<?= $HTML->articleInfo($page_item, "/verify/confirm/receipt", [
			"media" => $media, 
		]) ?>


		<? if($page_item["html"]): ?>
		<div class="articlebody" itemprop="articleBody">
			<?= $page_item["html"] ?>
		</div>
		<? endif; ?>
	</div>

<? else:?>


	<h1>That's cool...</h1>
	<h2>but don't forget to verify later!</h2>
	<p>In order to access your account and subscribed services (like newsletters), you'll have to verify your email.</p>
	<p>If you lost your verification email, you can get a new one by trying to <a href="/login">log in</a>.</p>

<? endif ?>

</div>