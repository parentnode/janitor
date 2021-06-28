<?php
$access_item["/"] = true;
$access_item["/owner"] = true;
$access_item["/updateOwner"] = "/owner";

if(isset($read_access) && $read_access) {
	return;
}


include_once($_SERVER["FRAMEWORK_PATH"]."/config/init.php");


$action = $page->actions();
$IC = new Items();
$itemtype = "message";
$model = $IC->typeObject($itemtype);


$page->bodyClass($itemtype);
$page->pageTitle("Messages");


if(is_array($action) && count($action)) {

//	print_r($action);
	// MESSAGES
	if(preg_match("/^(maillists)$/", $action[0])) {

		// MAILLIST LIST/EDIT
		if(count($action) > 1 && preg_match("/^(list|new)$/", $action[1])) {

//			print "dd: janitor/admin/message/maillists/".$action[1].".php";
//			print file_exists(FRAMEWORK_PATH . "/templates/janitor/message/maillists/".$action[1].".php";

			$page->page(array(
				"type" => "janitor",
				"page_title" => "Maillists",
				"templates" => "janitor/message/maillists/".$action[1].".php"
			));
			exit();

		}
		// Temp maillist list download
		else if(preg_match("/^(download)$/", $action[1]) && count($action) == 3) {


			$maillist = $page->maillists($action[2]);
			if($maillist) {

				include_once("classes/users/superuser.class.php");
				$UC = new SuperUser();
				$subscribers = $UC->getMaillists(["maillist_id" => $action[2]]);
				if($subscribers) {

					$emails = [];
					foreach($subscribers as $subscriber) {
						array_push($emails, $subscriber["email"]);
					}

					if($emails) {

						header('Content-Description: File Transfer');
						header('Content-Type: text/text');
						header("Content-Type: application/force-download");
						header('Content-Disposition: attachment; filename='.date("Ymd-His_").$maillist["name"].".csv");
						header('Expires: 0');
						header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
						header('Pragma: public');
						header('Content-Length: ' . strlen(implode("\n", $emails)));
						ob_clean();
						flush();
						print implode("\n", $emails);
						exit();
					}
				}

			}

			header('Content-Description: File Transfer');
			header('Content-Type: text/text');
			header("Content-Type: application/force-download");
			header('Content-Disposition: attachment; filename='.date("Ymd-His_").stringOr($maillist["name"], "unknown").".csv");
			header('Expires: 0');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Pragma: public');
			header('Content-Length: 0');

			ob_clean();
			flush();
			print "";
			exit();

		}
	}


	// LIST/EDIT/NEW MESSAGE ITEM
	else if(preg_match("/^(system|edit|new)$/", $action[0])) {

		$page->page(array(
			"type" => "janitor",
			"templates" => "janitor/".$itemtype."/".$action[0].".php"
		));
		exit();
	}

	// Class interface
	else if($page->validateCsrfToken() && preg_match("/[a-zA-Z]+/", $action[0])) {

		// check if custom function exists on User class
		if($model && method_exists($model, $action[0])) {

			$output = new Output();
			$output->screen($model->{$action[0]}($action));
			exit();
		}
	}

}

$page->page(array(
	"type" => "janitor",
	"templates" => "janitor/message/index.php"
));

?>
