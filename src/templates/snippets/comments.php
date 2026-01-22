<?php

// $item should be the item cemments relate to
// $add_url should be the url to submit new comments to for this item


$item = false;

$header = "Comments";
$date_format = "Y-m-d, H:i";
$no_comments_yet = "No comments yet";

$login_to_comment = '<a href="'.SITE_LOGIN_URL.'">Login</a>'.(SITE_SIGNUP ? ' or <a href="'.SITE_SIGNUP_URL.'">Sign up</a>' : '').' to comment';

$form_action = false;
$form_class = "add labelstyle:inject";
$form_comment_label = "Add your comment";
$form_comment_hint = "Add your comment";
$form_comment_error = "Your comment must contain text";

$form_bn_add = "Add comment";
$form_bn_cancel = "Cancel";


if($_options !== false) {
	foreach($_options as $_option => $_value) {
		switch($_option) {
			case "item"                  : $item                  = $_value; break;


			case "header"                : $header                = $_value; break;
			case "date_format"           : $date_format           = $_value; break;
			case "no_comments_yet"       : $no_comments_yet       = $_value; break;
			case "login_to_comment"      : $login_to_comment      = $_value; break;

			case "form_action"           : $form_action           = $_value; break;
			case "form_class"            : $form_class            = $_value; break;
			case "form_comment_label"    : $form_comment_label    = $_value; break;
			case "form_comment_hint"     : $form_comment_hint     = $_value; break;
			case "form_comment_error"    : $form_comment_error    = $_value; break;

			case "form_bn_add"           : $form_bn_add           = $_value; break;
			case "form_bn_cancel"        : $form_bn_cancel        = $_value; break;
		}
	}
}


if($item): ?>

<div class="comments i:comments item_id:<?= $item["item_id"] ?>">
	<h2 class="comments"><?= $header ?></h2>
<?	if(isset($item["comments"]) && $item["comments"]): ?>
	<ul class="comments">
<?		foreach($item["comments"] as $comment): ?>
		<li class="comment comment_id:<?= $comment["id"] ?>" itemprop="comment" itemscope itemtype="https://schema.org/Comment">
			<ul class="info">
				<li class="published_at" itemprop="datePublished" content="<?= date("Y-m-d", strtotime($comment["created_at"])) ?>"><?= date($date_format, strtotime($comment["created_at"])) ?></li>
				<li class="author" itemprop="author"><?= $comment["nickname"] ?></li>
			</ul>
			<p class="comment" itemprop="text"><?= $comment["comment"] ?></p>
		</li>
<?		endforeach; ?>
	</ul>
<?
	else:
?>
	<p>No comments yet</p>
<? 
	endif;

	if($form_action):
?>
	<?= HTML()->formStart($form_action."/". $item["item_id"], ["method" => "post", "class" => $form_class]); ?>
		<?= HTML()->input("item_id", ["type" => "hidden", "value" => $item["item_id"]]); ?>
		<fieldset>
			<?= HTML()->input("item_comment", [
				"type" => "text",
				"label" => $form_comment_label,
				"required" => true,
				"hint_message" => $form_comment_hint,
				"error_message" => $form_comment_error,
			]) ?>
		</fieldset>
		<ul class="actions">
			<?= HTML()->submit($form_bn_add, ["class" => "primary", "wrapper" => "li.submit"]); ?>
			<?= HTML()->button($form_bn_cancel, ["class" => "cancel", "wrapper" => "li.cancel"]); ?>
		</ul>
	<?= HTML()->formEnd(); ?>
<?	else: ?>
	<p><?= $login_to_comment ?></p>
<? 	endif; ?>
</div>
<?
endif;
