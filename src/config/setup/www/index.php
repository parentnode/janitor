<?php
$access_item = false;
if(isset($read_access) && $read_access) {
	return;
}

include_once($_SERVER["FRAMEWORK_PATH"]."/config/init.php");

// include the output class for output method support
include_once("class/system/output.class.php");

$action = $page->actions();

$IC = new Item();
$output = new Output();


$page->bodyClass("post");
$page->pageTitle("Post");


if(is_array($action) && count($action)) {

	if(preg_match("/[a-zA-Z\\-_]+/", $action[0])) {
		if(is_array($action) && count($action)) {

			# /blog/tag/#tag#[/#sindex#/prev|next]
			if(count($action) > 1 && $action[0] == "tag") {

				$page->header();
				$page->template("pages/posts_tag.php");
				$page->footer();
				exit();

			}
		}
	}

}

$page->header();
$page->template("pages/posts.php");
$page->footer();

?>
 