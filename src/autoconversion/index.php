<?php
$access_item = false;

if(isset($read_access) && $read_access) {
	return;
}

//include_once("../config/config.php");
include_once($_SERVER["FRAMEWORK_PATH"]."/config/init.php");


// script needs to be able to handle following extensions:

// IMAGE
// jpg
// png
// gif

// VIDEO
// mp4
// ogv
// mov
// 3gp

// AUDIO
// mp3
// ogg


//include_once("class/system/filesystem.class.php");


// error handling
function conversionFailed($reason) {
	global $page;

	// TODO: add missing image instead of 404
//	print "conversion failed:" . $reason;
	$page->mail("Autoconversion failed", $reason, array("template" => "system"));

//	header("Location: /404");
	exit();
}

// collect log autoconvertion for bundled notification
$page->collectNotification($_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"], "autoconversion");




// Get conversion details
// parse file info from path

// IMAGE WITH VARIANT
// /images/{id}/{variant}/{width}x.{format}
// /images/{id}/{variant}/x{height}.{format}
// VIDEO WITH VARIANT
// /videos/{id}/{variant}/{width}x{height}.{format}
if(preg_match("/\/(?P<request_type>\w+)\/(?P<id>[^\/]+)\/(?P<variant>\w*)\/(?P<width>\d*)x(?P<height>\d*).(?P<format>\w{3})/i", $_SERVER["REQUEST_URI"], $matches)) {
	$request_type = $matches["request_type"];

	$id = $matches["id"];
	$width = $matches["width"];
	$height = $matches["height"];
	$format = $matches["format"];
	$variant = "/".$matches["variant"];

	//	print $request_type . ":" . $id . ":" . $width . ":" . $height .":". $format .":".$variant."<br>";


	// max size detection (2000x1500 or similar amount of pixels)
	$max_pixels = 3000000;
}
// IMAGE
// /images/{id}/{width}x.{format}
// /images/{id}/x{height}.{format}
// VIDEO
// /videos/{id}/{width}x{height}.{format}
else if(preg_match("/\/(?P<request_type>\w+)\/(?P<id>[^\/]+)\/(?P<width>\d*)x(?P<height>\d*).(?P<format>\w{3})/i", $_SERVER["REQUEST_URI"], $matches)) {
	$request_type = $matches["request_type"];

	$id = $matches["id"];
	$width = $matches["width"];
	$height = $matches["height"];
	$format = $matches["format"];
	$variant = "";

	//	print $request_type . ":" . $id . ":" . $width . ":" . $height .":". $format .":".$variant."<br>";


	// max size detection (1500x1000 or similar amount of pixels)
	$max_pixels = 1500000;
}
// AUDIO
// /audios/{id}/{bitrate}.{format}
else if(preg_match("/\/(?P<request_type>\w+)\/(?P<id>[^\/]+)\/(?P<bitrate>\d*).(?P<format>\w{3})/i", $_SERVER["REQUEST_URI"], $matches)) {
	$request_type = $matches["request_type"];

	$id = $matches["id"];
	$bitrate = $matches["bitrate"];
	$format = $matches["format"];

	//	print $id . ":" . $bitrate .":". $format ."<br>";

	// TODO: implement bitrate control in audio class first
	// $max_bitrate = 320;
}
// ERROR - MISSING INFO
else {
//	print "missing info";
	conversionFailed("missing info");
}



// images
if($format == "jpg" || $format == "png" || $format == "gif") {
	include_once("class/system/image.class.php");

	$Image = new Image();

	// check for sources

	// jpg, and source is available
	if($format == "jpg" && file_exists(PRIVATE_FILE_PATH."/$id$variant/jpg")) {
		$input_file = PRIVATE_FILE_PATH."/$id$variant/jpg";
	}
	// png, and source is available
	else if($format == "png" && file_exists(PRIVATE_FILE_PATH."/$id$variant/png")) {
		$input_file = PRIVATE_FILE_PATH."/$id$variant/png";
	}
	// gif, and source is available
	else if($format == "png" && file_exists(PRIVATE_FILE_PATH."/$id$variant/gif")) {
		$input_file = PRIVATE_FILE_PATH."/$id$variant/gif";
	}
	// jpg available
	else if(file_exists(PRIVATE_FILE_PATH."/$id$variant/jpg")) {
		$input_file = PRIVATE_FILE_PATH."/$id$variant/jpg";
	}
	// png available
	else if(file_exists(PRIVATE_FILE_PATH."/$id$variant/png")) {
		$input_file = PRIVATE_FILE_PATH."/$id$variant/png";
	}
	// gif available
	else if(file_exists(PRIVATE_FILE_PATH."/$id$variant/gif")) {
		$input_file = PRIVATE_FILE_PATH."/$id$variant/gif";
	}
	// no valid source available
	else {
		conversionFailed("no valid source available");
	}

	$output_file = PUBLIC_FILE_PATH."/".$id.$variant."/".$width."x".$height.".".$format;

//	print $input_file . ":" . $output_file . "<br>";

	// scale image (will autoconvert)
	if($Image->convert($input_file, $output_file, array("allow_cropping" => true, "width" => $width, "height" => $height, "format" => $format, "compression" => 90, "max_pixels" => $max_pixels))) {

		// redirect to new image
		header("Location: /".$request_type."/".$id.$variant."/".$width."x".$height.".".$format);
		exit();

	}
	else {
		conversionFailed("Image->convert failed");
	}


}

// video
else if($format == "mp4" || $format == "ogv" || $format == "mov" || $format == "3gp") {
		include_once("class/system/video.class.php");

		$Video = new Video();

		// check for sources

		// mov, and source is available
		if($format == "mov" && file_exists(PRIVATE_FILE_PATH."/$id$variant/mov")) {
			$input_file = PRIVATE_FILE_PATH."/$id$variant/mov";
		}
		// mp4, and source is available
		else if($format == "mp4" && file_exists(PRIVATE_FILE_PATH."/$id$variant/mp4")) {
			$input_file = PRIVATE_FILE_PATH."/$id$variant/mp4";
		}
		// ogv, and source is available
		else if($format == "ogv" && file_exists(PRIVATE_FILE_PATH."/$id$variant/ogv")) {
			$input_file = PRIVATE_FILE_PATH."/$id$variant/ogv";
		}
		// 3gp, and source is available
		else if($format == "3gp" && file_exists(PRIVATE_FILE_PATH."/$id$variant/3gp")) {
			$input_file = PRIVATE_FILE_PATH."/$id$variant/3gp";
		}
		// mov available
		else if(file_exists(PRIVATE_FILE_PATH."/$id$variant/mov")) {
			$input_file = PRIVATE_FILE_PATH."/$id$variant/mov";
		}
		// mp4 available
		else if(file_exists(PRIVATE_FILE_PATH."/$id$variant/mp4")) {
			$input_file = PRIVATE_FILE_PATH."/$id$variant/mp4";
		}
		// ogv available
		else if(file_exists(PRIVATE_FILE_PATH."/$id$variant/ogv")) {
			$input_file = PRIVATE_FILE_PATH."/$id$variant/ogv";
		}
		// 3gp available
		else if(file_exists(PRIVATE_FILE_PATH."/$id$variant/3gp")) {
			$input_file = PRIVATE_FILE_PATH."/$id$variant/3gp";
		}
		// no valid source available
		else {
			conversionFailed("no valid source available");
		}

		$output_file = PUBLIC_FILE_PATH."/".$id.$variant."/".$width."x".$height.".".$format;


		// scale image (will autoconvert)
		if($Video->convert($input_file, $output_file, array("allow_cropping" => true, "width" => $width, "height" => $height, "format" => $format, "max_pixels" => $max_pixels))) {

			// redirect to new image
			header("Location: /".$request_type."/".$id.$variant."/".$width."x".$height.".".$format);
			exit();

		}
		else {
			conversionFailed("Video->convert failed");
		}

}

// audio
else if($format == "mp3" || $format == "ogg") {
	include_once("class/system/audio.class.php");

	$Audio = new Audio();

	if($format == "mp3" && file_exists(PRIVATE_FILE_PATH."/$id/mp3")) {
		$input_file = PRIVATE_FILE_PATH."/$id/mp3";
	}
	else if($format == "ogg" && file_exists(PRIVATE_FILE_PATH."/$id/ogg")) {
		$input_file = PRIVATE_FILE_PATH."/$id/ogg";
	}
	else if(file_exists(PRIVATE_FILE_PATH."/$id/mp3")) {
		$input_file = PRIVATE_FILE_PATH."/$id/mp3";
	}
	else if(file_exists(PRIVATE_FILE_PATH."/$id/ogg")) {
		$input_file = PRIVATE_FILE_PATH."/$id/ogg";
	}
	else {
		conversionFailed("no valid source available");
	}

	$output_file = PUBLIC_FILE_PATH."/".$id."/".$bitrate.".".$format;


	// scale image (will autoconvert)
	if($Audio->convert($input_file, $output_file, array("bitrate" => $bitrate, "format" => $format))) {
		// TODO: implement bit rate control in audio class first, "max_bitrate" => $max_bitrate
		// redirect to new image
		header("Location: /".$request_type."/".$id."/".$bitrate.".".$format);
		exit();

	}
	else {
		conversionFailed("Audio->convert failed");
	}

}

?>
