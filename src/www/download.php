<?php
$access_item = false;
if(isset($read_access) && $read_access) {
	return;
}

include_once($_SERVER["FRAMEWORK_PATH"]."/config/init.php");

// pure
$action = $page->actions();


// File download
if(count($action) == 3 && isset($_SERVER["HTTP_REFERER"]) && strpos($_SERVER["HTTP_REFERER"], SITE_URL) === 0 && session()->value("useragent") === $_SERVER["HTTP_USER_AGENT"]) {

	$item_id = $action[0];
	$variant = $action[1];
	$file_name = $action[2];

	$file = PUBLIC_FILE_PATH."/".$item_id."/".$variant."/".$file_name;

	// Public file wasn't found
	if(!file_exists($file)) {

		// Check for Private file
		if(file_exists(PRIVATE_FILE_PATH."/".$item_id."/".$variant)) {

			$query = new Query();
			$sql = "SELECT format FROM ".UT_ITEMS_MEDIAE." WHERE variant = '$variant' AND item_id = $item_id";
			if($query->sql($sql)) {
				$format = $query->result(0, "format");
				$file = PRIVATE_FILE_PATH."/".$item_id."/".$variant."/".$format;
			}

		}

	}

	// file exists and is of valid format
	if(file_exists($file) && preg_match("/(\.png|\.jpg|\.gif|\.zip|\.pdf)$/", $file_name)) {

		header('Content-Description: File download');

		// Set correct mimetype
		if(substr($file_name, -3) === "jpg") {
			header('Content-Type: image/jpeg');
		}
		else if(substr($file_name, -3) === "png") {
			header('Content-Type: image/png');
		}
		else if(substr($file_name, -3) === "gif") {
			header('Content-Type: image/gif');
		}
		else if(substr($file_name, -3) === "pdf") {
			header('Content-Type: application/pdf');
		}
		else {
			header('Content-Type: application/octet-stream');
		}

		header("Content-Type: application/force-download");
		header('Content-Disposition: attachment; filename=' . $file_name);
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');
		header('Content-Length: ' . filesize($file));

		ob_clean();
		// enable downloading large file without memory issues
		ob_end_flush();
		readfile($file);

		mailer()->send([
			"subject" => "File downloaded on ".SITE_URL." ($file_name)", 
			"message" => "File was downloaded:<br>$file",
			"template" => "system"
		]);

		exit();
	}

}

mailer()->send([
	"subject" => "File download FAILED on ".SITE_URL, 
	"message" => "File download was attempted but failed:<br>".implode("/", $action),
	"template" => "system"
]);

// no valid actions
$page->page(array(
	"templates" => "pages/404.php"
));


?>