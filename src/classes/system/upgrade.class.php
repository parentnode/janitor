<?php



class Upgrade {


	function __construct() {

		// global $page;
		// print "loading db config";
		// $page->loadDBConfiguration();
		//
		//
		//
		// include_once("classes/items/items.core.class.php");
		// include_once("classes/items/items.class.php");

	}



	// Check countries, prices and vatrates
	function upgradeDatabaseTo_v0_8() {
		
		$query = new Query();


		$query->checkDbExistance(UT_LANGUAGES);
		$query->checkDbExistance(UT_CURRENCIES);
		$query->checkDbExistance(UT_COUNTRIES);
		$query->checkDbExistance(UT_VATRATES);
		$query->checkDbExistance(UT_ITEMS_PRICES);

		// Languages
		$sql = "SELECT * FROM ".UT_LANGUAGES." WHERE id = 'DA'";
		if(!$query->sql($sql)) {
			$sql = "INSERT INTO ".UT_LANGUAGES." values('DA', 'Dansk')";
			if(!$query->sql($sql)) {
				print "Could not update DB (".UT_LANGUAGES.")";
				return false;
			}
		}

		// Countries
		$sql = "SELECT * FROM ".UT_CURRENCIES." WHERE id = 'DKK'";
		if(!$query->sql($sql)) {
			$sql = "INSERT INTO ".UT_CURRENCIES." values('DKK', 'Kroner (Denmark)', 'DKK', 'after', 2, ',', '.')";
			if(!$query->sql($sql)) {
				print "Could not update DB (".UT_CURRENCIES.")";
				return false;
			}
		}


		// Countries
		$sql = "SELECT * FROM ".UT_COUNTRIES." WHERE id = 'DK'";
		if(!$query->sql($sql)) {
			$sql = "INSERT INTO ".UT_COUNTRIES." values('DK', 'Danmark', '45', '#### ####', 'DA', 'DKK')";
			if(!$query->sql($sql)) {
				print "Could not update DB (".UT_COUNTRIES.")";
				return false;
			}
		}


		// VAT rates
		$sql = "SELECT * FROM ".UT_VATRATES." WHERE name = 'No VAT' AND country = 'DK'";
		if(!$query->sql($sql)) {
			// add default vatrate
			$sql = "INSERT INTO ".UT_VATRATES." values(DEFAULT, 'No VAT', 0, 'DK')";
			if(!$query->sql($sql)) {
				print "Could not update DB (".UT_VATRATES.")";
				return false;
			}
		}


		// Update users table
		$query->sql("ALTER TABLE ".SITE_DB.".users MODIFY COLUMN `language` varchar(2) DEFAULT NULL");
		$query->sql("UPDATE ".SITE_DB.".users SET language = NULL where language = ''");

		// Check users keys and constraints
		$query->sql("SHOW KEYS FROM ".SITE_DB.".users");
		$results = $query->results();
		if(!arrayKeyValue($results, "Column_name", "language")) {
			$query->sql("ALTER TABLE ".SITE_DB.".users ADD KEY (`language`)");
			$query->sql("ALTER TABLE ".SITE_DB.".users ADD CONSTRAINT FOREIGN KEY (`language`) REFERENCES ".SITE_DB.".languages(`id`) ON UPDATE CASCADE");
		}



		return "DB updated";
	}



	// 0.7 to v 0.9 UPGRADE HELPERS


	function moveFilesColumnToItems($itemtype, $variant = false, $column = "files") {

		$query = new Query();
		$IC = new Items();
		$fs = new FileSystem();

		$query->checkDbExistance(UT_ITEMS_MEDIAE);


		$sql = "SELECT * FROM ".SITE_DB.".item_".$itemtype;
		$query->sql($sql);
		$results = $query->results();

//		print_r($results);

		foreach($results as $result) {

		//	print $result["files"] . "<br>";
			if($result[$column]) {

				$item_id = $result["item_id"];
				$format = $result[$column];

				if(file_exists(PRIVATE_FILE_PATH."/".$item_id."/".$format)) {
					$file = PRIVATE_FILE_PATH."/".$item_id."/".$format;
				}
				else if(file_exists(PRIVATE_FILE_PATH."/".$item_id."/".$variant."/".$format)) {
					$file = PRIVATE_FILE_PATH."/".$item_id."/".$variant."/".$format;
				}
				else {
					continue;
				}

				$new_file = PRIVATE_FILE_PATH."/".$item_id."/".$variant."/".$format;

				if(preg_Match("/^(png|jpg)$/", $format)) {
					$image = new Imagick($file);

					// check if we can get relevant info about image
					$width = $image->getImageWidth();
					$height = $image->getImageHeight();
				}
				else if(preg_Match("/^(mov|mp4)$/", $format)) {
					include_once("classes/system/video.class.php");
					$VC = new Video();
					$info = $VC->info($file);
					$width = $info["width"];
					$height = $info["height"];
				}
				else {
					$width = 0;
					$height = 0;
				}

				$variant = $variant ? $variant : randomKey(8);
				$name = $format;
				$filesize = filesize($file);

				// insert into new table
				$sql = "INSERT INTO ".UT_ITEMS_MEDIAE." VALUES(DEFAULT, $item_id, '$name', '$format', '$variant', '$width', '$height', '$filesize', 0)";
//				print $sql."<br>";

				$query->sql($sql);

				// move image to new folder
				$fs->makeDirRecursively(dirname($new_file));

				if($file != $new_file) {
					copy($file, PRIVATE_FILE_PATH."/".$item_id."/".$variant."/".$format);
					unlink(PRIVATE_FILE_PATH."/".$item_id."/".$format);
				}
				$fs->removeDirRecursively(PUBLIC_FILE_PATH."/".$item_id);

			}
		}
		print "You can now delete '$column' column in ".SITE_DB.".item_".$itemtype."<br>";

	}



	// V 0.7 to v 0.8 UPGRADE HELPERS

	function moveMediaeToItems($itemtype) {

		$query = new Query();
		$IC = new Items();

		$query->checkDbExistance(UT_ITEMS_MEDIAE);

		$sql = "SELECT * FROM ".SITE_DB.".item_".$itemtype."_mediae";
		print $sql."<br>\n";

		$query->sql($sql);
		$mediae = $query->results();

		foreach($mediae as $media) {

			$item_id = $media["item_id"];
			$item_format = $media["format"];
			$item_variant = isset($media["variant"]) ? $media["variant"] : "";


			if(!$item_variant) {
				print "missing variant - create variant and move file??<br>\n";
			}


			$file = PRIVATE_FILE_PATH."/".$item_id.($item_variant ? "/".$item_variant : "")."/".$item_format;

//			print_r($media);

			if(file_exists($file)) {
				print "valid file: $file<br>\n";

				$item_name = (isset($media["name"]) && $media["name"]) ? $media["name"] : $item_format;
				$item_filesize = (isset($media["filesize"]) && $media["filesize"]) ? $media["filesize"] : filesize($file);
				$item_position = (isset($media["position"]) && $media["position"]) ? $media["position"] : 0;

				if(preg_match("/jpg|png/", $item_format)) {
					$image = new Imagick($file);
					$item_width = (isset($media["width"]) && $media["width"]) ? $media["width"] : $image->getImageWidth();
					$item_height = (isset($media["height"]) && $media["height"]) ? $media["height"] : $image->getImageHeight();
				}
				else {
					$item_width = (isset($media["width"]) && $media["width"]) ? $media["width"] : 0;
					$item_height = (isset($media["height"]) && $media["height"]) ? $media["height"] : 0;
			
				}



				$sql = "INSERT INTO ".UT_ITEMS_MEDIAE." SET item_id=$item_id, format='$item_format', variant='$item_variant', name='$item_name', filesize=$item_filesize, width='$item_width', height='$item_height', position='$item_position'";
				print $sql."<br>\n";
				$query->sql($sql);
			}
			else {
				print "invalid file:" . $file ."<br>\n";
			}



		}
		
	}

}

?>