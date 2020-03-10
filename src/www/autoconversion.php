<?php
$access_item = false;
if(isset($read_access) && $read_access) {
	return;
}

include_once($_SERVER["FRAMEWORK_PATH"]."/config/init.php");

// script needs to be able to handle following extensions:

// IMAGE
// jpg
// png
// gif

// VIDEO
// mp4
// webm
// ogv
// mov
// 3gp

// AUDIO
// mp3
// ogg


// error handling
function conversionFailed($reason) {
//	global $page;

	global $id;
	global $variant;
	global $request_type;
	global $format;

	global $width;
	global $height;

	global $bitrate;

	global $page;

	// get into the detail to make debugging as easy as possible
	$reason .= "<br />".$_SERVER["REQUEST_URI"];
	$reason .= "<br /><br />Request type: ".$request_type;
	$reason .= "<br />Format: ".$format;

	if($request_type == "audios") {
		if(!$bitrate) {
			$reason .= "<br />Bitrate: MISSING";
		}
		else {
			$reason .= "<br />Bitrate: ".$bitrate;
		}
	}
	else {
		if(!$width && !$height) {
			$reason .= "<br />Width and Height: MISSING";
		}
		else if($width) {
			$reason .= "<br />Width: ".$width;
		}
		else if($height) {
			$reason .= "<br />Height: ".$height;
		}
	}

	// specify reason
	if($id && !file_exists(PRIVATE_FILE_PATH."/$id")) {
		$reason .= "<br /><br />ID does not exist: ".$id;
	}
	else if($id && $variant && !file_exists(PRIVATE_FILE_PATH."/$id/$variant")) {
		$reason .= "<br /><br />Variant does not exist: ".$variant;
	}
	
	// show what we got
	if($id && file_exists(PRIVATE_FILE_PATH."/$id")) {
		$fs = new FileSystem();
		$files = $fs->files(PRIVATE_FILE_PATH."/$id");
		$reason .= "<br /><br />Private files:<br />";
		foreach($files as $file) {
			$reason .= str_replace(PRIVATE_FILE_PATH, "", $file)."<br />";
		}
		$files = $fs->files(PUBLIC_FILE_PATH."/$id");
		$reason .= "<br />Public files:<br />";
		foreach($files as $file) {
			$reason .= str_replace(PUBLIC_FILE_PATH, "", $file)."<br />";
		}
	}

	$segment = $page->segment();
	debug([$segment]);

	// TODO: implement fallback for audio and video
	// TODO: implement constraints to avoid media generation abuse
	// TODO: investigate use-cases and figure out if returning 404/410 is better

	// Clearly tell robots that this image is gone
	if($segment === "seo") {
		// facebookexternalhit/1.1 (+http://www.facebook.com/externalhit_uatext.php)

		mailer()->send([
			"subject" => "Autoconversion failed SEO - 410 Test ($request_type)",
			"message" => $reason,
			"template" => "system"
		]);

		header("Location: /images/0/missing/".$width."x".$height.".png", true, 410);

	}

	// return missing image if it exists (and request is for image)
	// print $request_type . ", " . file_exists(PRIVATE_FILE_PATH."/0/missing/png")." && ". $width ."&&". $height;
	else if($request_type == "images" && file_exists(PRIVATE_FILE_PATH."/0/missing/png") && ($width || $height)) {

		mailer()->send([
			"subject" => "Autoconversion failed ($request_type)",
			"message" => $reason,
			"template" => "system"
		]);

		// 404 does not return "missing image"
		header("Location: /images/0/missing/".$width."x".$height.".png", true, 307);

	}

	// dangerous to return HTML - receiving JS will expect media, not HTML
	else {
//		header("Location: /janitor/admin/404");
	}
	exit();
}


function checkVariantRedirects($id, $variant) {
	// debug(["checkVariantRedirects", $id, $variant]);

	if(file_exists(LOCAL_PATH."/config/mediae_variant_redirects.php")) {
		include("config/mediae_variant_redirects.php");
		// debug([$mediae_redirects]);

		if(
			isset($mediae_redirects) && 
			is_array($mediae_redirects) && 
			isset($mediae_redirects[$id]) && 
			is_array($mediae_redirects[$id]) && 
			isset($mediae_redirects[$id][$variant]) &&
			$mediae_redirects[$id][$variant]
		) {
			$redirect_url = str_replace($variant, $mediae_redirects[$id][$variant], $_SERVER["REQUEST_URI"]);

			// debug([$redirect_url]);
			header("Location: " . $redirect_url, true, 301);
			exit();
		}
	}

}


// Starting values
$id = false;
$variant = "";

// Get conversion details
// parse file info from path

// IMAGE WITH VARIANT
// /images/{id}/{variant}/{width}x.{format}
// /images/{id}/{variant}/x{height}.{format}
// VIDEO WITH VARIANT
// /videos/{id}/{variant}/{width}x{height}.{format}
if(preg_match("/\/(?P<request_type>\w+)\/(?P<id>[^\/]+)\/(?P<variant>[^\/]+)\/(?P<width>\d*)x(?P<height>\d*)\.(?P<format>\w{3,4})/i", $_SERVER["REQUEST_URI"], $matches)) {
	$request_type = $matches["request_type"];

	if($request_type == "images" || $request_type == "videos") {

		$id = $matches["id"];
		$width = $matches["width"];
		$height = $matches["height"];
		$format = $matches["format"];
		$variant = $matches["variant"];

		//	print "request:" . $request_type . " id:" . $id . " width:" . $width . " height:" . $height ." format:". $format ." variant:".$variant."<br>";


		// max size detection (2000x2000 or similar amount of pixels)
		$max_pixels = 4000000;
		
	}
}
// DEPRECATED – ALL MEDIA HAS VARIANT NOW
// IMAGE (without variant)
// /images/{id}/{width}x.{format}
// /images/{id}/x{height}.{format}
// VIDEO
// /videos/{id}/{width}x{height}.{format}
// else if(preg_match("/\/(?P<request_type>\w+)\/(?P<id>[^\/]+)\/(?P<width>\d*)x(?P<height>\d*)\.(?P<format>\w{3,4})/i", $_SERVER["REQUEST_URI"], $matches)) {
// 	$request_type = $matches["request_type"];
//
// 	if($request_type == "images" || $request_type == "videos") {
//
// 		$id = $matches["id"];
// 		$width = $matches["width"];
// 		$height = $matches["height"];
// 		$format = $matches["format"];
//
// 		//	print "request:" . $request_type . " id:" . $id . " width:" . $width . " height:" . $height ." format:". $format ." variant:".$variant."<br>";
//
//
// 		// max size detection (2000x2000 or similar amount of pixels)
// 		$max_pixels = 4000000;
//
// 	}
// }

// AUDIO
// AUDIO WITH VARIANT
// /audios/{id}/{variant}/{bitrate}.{format}
else if(preg_match("/\/(?P<request_type>\w+)\/(?P<id>[^\/]+)\/(?P<variant>[^\/]+)\/(?P<bitrate>\d+)\.(?P<format>\w{3})/i", $_SERVER["REQUEST_URI"], $matches)) {
	$request_type = $matches["request_type"];

	if($request_type == "audios") {

		$id = $matches["id"];
		$bitrate = $matches["bitrate"];
		$format = $matches["format"];
		$variant = $matches["variant"];

		//	print "request:" . $request_type . " id:" . $id . " bitrate:" . $bitrate ." format:". $format ." variant:".$variant."<br>";
	}
}
// DEPRECATED – ALL MEDIA HAS VARIANT NOW
// // /audios/{id}/{bitrate}.{format}
// else if(preg_match("/\/(?P<request_type>\w+)\/(?P<id>[^\/]+)\/(?P<bitrate>\d+)\.(?P<format>\w{3})/i", $_SERVER["REQUEST_URI"], $matches)) {
// 	$request_type = $matches["request_type"];
//
// 	if($request_type == "audios") {
//
// 		$id = $matches["id"];
// 		$bitrate = $matches["bitrate"];
// 		$format = $matches["format"];
//
// 		// TODO: implement bitrate control in audio class first
// 		// $max_bitrate = 320;
//
// 		//	print "request:" . $request_type . " id:" . $id . " bitrate:" . $bitrate ." format:". $format ." variant:".$variant."<br>";
// 	}
// }


// Check for potential variant redirects
if($id === false || !file_exists(PRIVATE_FILE_PATH."/$id/$variant")) {

	checkVariantRedirects($id, $variant);

}

// ERROR - MISSING ID/Variant - stop
// id can be 0, but not false
if($id === false || !file_exists(PRIVATE_FILE_PATH."/$id/$variant")) {
//	print "missing info";

	conversionFailed("Missing or bad path info - request ignored");
}



// images
if($request_type == "images" && ($width || $height) && ($format == "jpg" || $format == "png" || $format == "gif")) {
	include_once("classes/helpers/image.class.php");

	$Image = new Image();

	// check for sources

	// jpg, and source is available
	if($format == "jpg" && file_exists(PRIVATE_FILE_PATH."/$id$variant/jpg")) {
		$input_file = PRIVATE_FILE_PATH."/$id/$variant/jpg";
	}
	// png, and source is available
	else if($format == "png" && file_exists(PRIVATE_FILE_PATH."/$id/$variant/png")) {
		$input_file = PRIVATE_FILE_PATH."/$id/$variant/png";
	}
	// gif, and source is available
	else if($format == "gif" && file_exists(PRIVATE_FILE_PATH."/$id/$variant/gif")) {
		$input_file = PRIVATE_FILE_PATH."/$id/$variant/gif";
	}
	// jpg available
	else if(file_exists(PRIVATE_FILE_PATH."/$id/$variant/jpg")) {
		$input_file = PRIVATE_FILE_PATH."/$id/$variant/jpg";
	}
	// png available
	else if(file_exists(PRIVATE_FILE_PATH."/$id/$variant/png")) {
		$input_file = PRIVATE_FILE_PATH."/$id/$variant/png";
	}
	// gif available
	else if(file_exists(PRIVATE_FILE_PATH."/$id/$variant/gif")) {
		$input_file = PRIVATE_FILE_PATH."/$id/$variant/gif";
	}
	// no valid source available
	else {
		conversionFailed("no valid source available");
	}

	$output_file = PUBLIC_FILE_PATH."/".$id."/".$variant."/".$width."x".$height.".".$format;

//	print $input_file . ":" . $output_file . "<br>";

	// scale image (will autoconvert)
	if($Image->convert($input_file, $output_file, array("compression" => 93, "allow_cropping" => true, "width" => $width, "height" => $height, "format" => $format, "max_pixels" => $max_pixels))) {

		// collect log autoconvertion for bundled notification
		$page->collectNotification($_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"], "autoconversion");

		// redirect to new image
		header("Location: /".$request_type."/".$id."/".$variant."/".$width."x".$height.".".$format, true, 307);
		exit();

	}
	else {
		conversionFailed("Image->convert failed");
	}


}

// video
else if($request_type == "videos" && ($width || $height) && ($format == "mp4" || $format == "webm" || $format == "ogv" || $format == "mov" || $format == "3gp")) {
	include_once("classes/helpers/video.class.php");

	$Video = new Video();

	// check for sources

	// mov, and source is available
	if($format == "mov" && file_exists(PRIVATE_FILE_PATH."/$id/$variant/mov")) {
		$input_file = PRIVATE_FILE_PATH."/$id/$variant/mov";
	}
	// mp4, and source is available
	else if($format == "mp4" && file_exists(PRIVATE_FILE_PATH."/$id/$variant/mp4")) {
		$input_file = PRIVATE_FILE_PATH."/$id/$variant/mp4";
	}
	// webm, and source is available
	else if($format == "webm" && file_exists(PRIVATE_FILE_PATH."/$id/$variant/webm")) {
		$input_file = PRIVATE_FILE_PATH."/$id/$variant/webm";
	}
	// ogv, and source is available
	else if($format == "ogv" && file_exists(PRIVATE_FILE_PATH."/$id/$variant/ogv")) {
		$input_file = PRIVATE_FILE_PATH."/$id/$variant/ogv";
	}
	// 3gp, and source is available
	else if($format == "3gp" && file_exists(PRIVATE_FILE_PATH."/$id/$variant/3gp")) {
		$input_file = PRIVATE_FILE_PATH."/$id/$variant/3gp";
	}
	// mov available
	else if(file_exists(PRIVATE_FILE_PATH."/$id/$variant/mov")) {
		$input_file = PRIVATE_FILE_PATH."/$id/$variant/mov";
	}
	// mp4 available
	else if(file_exists(PRIVATE_FILE_PATH."/$id/$variant/mp4")) {
		$input_file = PRIVATE_FILE_PATH."/$id/$variant/mp4";
	}
	// webm available
	else if(file_exists(PRIVATE_FILE_PATH."/$id/$variant/webm")) {
		$input_file = PRIVATE_FILE_PATH."/$id/$variant/webm";
	}
	// ogv available
	else if(file_exists(PRIVATE_FILE_PATH."/$id/$variant/ogv")) {
		$input_file = PRIVATE_FILE_PATH."/$id/$variant/ogv";
	}
	// 3gp available
	else if(file_exists(PRIVATE_FILE_PATH."/$id/$variant/3gp")) {
		$input_file = PRIVATE_FILE_PATH."/$id/$variant/3gp";
	}
	// no valid source available
	else {
		conversionFailed("no valid source available");
	}

	$output_file = PUBLIC_FILE_PATH."/".$id."/".$variant."/".$width."x".$height.".".$format;


	// scale image (will autoconvert)
	if($Video->convert($input_file, $output_file, array("allow_cropping" => true, "width" => $width, "height" => $height, "format" => $format, "max_pixels" => $max_pixels))) {

		// collect log autoconvertion for bundled notification
		$page->collectNotification($_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"], "autoconversion");

		// redirect to new image
		header("Location: /".$request_type."/".$id."/".$variant."/".$width."x".$height.".".$format, true, 307);
		exit();

	}
	else {
		conversionFailed("Video->convert failed");
	}

}

// audio
else if($request_type == "audios" && $bitrate && ($format == "mp3" || $format == "ogg" || $format == "wav")) {
	include_once("classes/helpers/audio.class.php");

	$Audio = new Audio();

	// mp3, and source is available
	if($format == "mp3" && file_exists(PRIVATE_FILE_PATH."/$id/$variant/mp3")) {
		$input_file = PRIVATE_FILE_PATH."/$id/$variant/mp3";
	}
	// ogg, and source is available
	else if($format == "ogg" && file_exists(PRIVATE_FILE_PATH."/$id/$variant/ogg")) {
		$input_file = PRIVATE_FILE_PATH."/$id/$variant/ogg";
	}
	// wav, and source is available
	else if($format == "wav" && file_exists(PRIVATE_FILE_PATH."/$id/$variant/wav")) {
		$input_file = PRIVATE_FILE_PATH."/$id/$variant/wav";
	}
	else if(file_exists(PRIVATE_FILE_PATH."/$id/$variant/mp3")) {
		$input_file = PRIVATE_FILE_PATH."/$id/$variant/mp3";
	}
	else if(file_exists(PRIVATE_FILE_PATH."/$id/$variant/ogg")) {
		$input_file = PRIVATE_FILE_PATH."/$id/$variant/ogg";
	}
	else if(file_exists(PRIVATE_FILE_PATH."/$id/$variant/wav")) {
		$input_file = PRIVATE_FILE_PATH."/$id/$variant/wav";
	}
	else {
		conversionFailed("no valid source available");
	}

	$output_file = PUBLIC_FILE_PATH."/".$id."/".$variant."/".$bitrate.".".$format;


	// scale image (will autoconvert)
	if($Audio->convert($input_file, $output_file, array("bitrate" => $bitrate, "format" => $format))) {
		// TODO: implement bit rate control in audio class first, "max_bitrate" => $max_bitrate
		// redirect to new image

		// collect log autoconvertion for bundled notification
		$page->collectNotification($_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"], "autoconversion");

		header("Location: /".$request_type."/".$id."/".$variant."/".$bitrate.".".$format, true, 307);
		exit();

	}
	else {
		conversionFailed("Audio->convert failed");
	}

}

// something weren't as expected
conversionFailed("Missing or bad path info - request not completed");

?>
