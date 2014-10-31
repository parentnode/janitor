<?php
$access_item = false;
if(isset($read_access) && $read_access) {
	return;
}

include_once($_SERVER["FRAMEWORK_PATH"]."/config/init.php");


$action = $page->actions();


$page->bodyClass("post");
$page->pageTitle("Posts");



// news list for tags
// /blog/tag/#tag#
// /blog/tag/#tag#/#sindex#/prev|next
if(count($action) >= 2 && $action[0] == "tag") {

	$page->page(array(
		"templates" => "pages/posts_tag.php"
		)
	);
	exit();

}


$page->page(array(
	"templates" => "pages/posts.php"
	)
);
exit();


?>
 