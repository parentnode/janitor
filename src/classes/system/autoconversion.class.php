<?php
/**
* @package janitor.system
*
* This class holds autoconversion functionallity.
*
*
* needs to be able to handle following extensions:
*
* IMAGE
* jpg
* png
* gif
*
* VIDEO
* mp4
* webm
* ogv
* mov
* 3gp
*
* AUDIO
* mp3
* ogg
*
*/


class AutoConversion {


	public $db_autoconversions;

	private $id;
	private $variant;

	private $request_type;

	private $width;
	private $height;
	private $format;

	private $max_pixels;


	/**
	* Init, set varnames, validation rules
	*/
	function __construct() {

		$this->db_autoconversions = SITE_DB.".system_autoconversions";

		$this->id = false;
		$this->variant = "";

		$this->request_type = false;

		$this->width = false;
		$this->height = false;
		$this->format = false;

		$this->max_pixels = 4000000;

	}


	function parseRequest() {
		
		// Get conversion details
		// parse file info from path

		// IMAGE WITH VARIANT
		// /images/{id}/{variant}/{width}x.{format}
		// /images/{id}/{variant}/x{height}.{format}
		// VIDEO WITH VARIANT
		// /videos/{id}/{variant}/{width}x{height}.{format}
		if(preg_match("/\/(?P<request_type>\w+)\/(?P<id>[^\/]+)\/(?P<variant>[^\/]+)\/(?P<width>\d*)x(?P<height>\d*)\.(?P<format>\w{3,4})/i", $_SERVER["REQUEST_URI"], $matches)) {
			$this->request_type = $matches["request_type"];

			if($this->request_type == "images" || $this->request_type == "videos") {

				$this->id = $matches["id"];

				$this->width = $matches["width"];
				$this->height = $matches["height"];
				$this->format = $matches["format"];
				$this->variant = $matches["variant"];

				// max size detection (2000x2000 or similar amount of pixels)
				$this->max_pixels = 4000000;
		

				// debug([
				// 	"request:".$this->request_type,
				// 	"id:" . $this->id,
				// 	"variant:" . $this->variant,
				// 	"width:" . $this->width,
				// 	"height:" . $this->height,
				// 	"format:" . $this->format
				// ]);


			}
		}

		// AUDIO
		// AUDIO WITH VARIANT
		// /audios/{id}/{variant}/{bitrate}.{format}
		else if(preg_match("/\/(?P<request_type>\w+)\/(?P<id>[^\/]+)\/(?P<variant>[^\/]+)\/(?P<bitrate>\d+)\.(?P<format>\w{3})/i", $_SERVER["REQUEST_URI"], $matches)) {
			$this->request_type = $matches["request_type"];

			if($this->request_type == "audios") {

				$this->id = $matches["id"];
				$this->variant = $matches["variant"];

				$this->bitrate = $matches["bitrate"];
				$this->format = $matches["format"];


				// debug([
				// 	"request:".$this->request_type,
				// 	"id:" . $this->id,
				// 	"variant:" . $this->variant,
				// 	"bitrate:" . $this->bitrate,
				// 	"format:" . $this->format
				// ]);


			}
		}

	}

	// Process the data made available via parseRequest
	function processRequest() {


		// Check for potential variant redirects
		if($this->id === false || !file_exists(PRIVATE_FILE_PATH."/".$this->id."/".$this->variant)) {

			$this->checkVariantRedirects($this->id, $this->variant);

			// exits

		}


		// ERROR - MISSING ID/Variant - stop
		// id can be 0, but not false
		if($this->id === false || !file_exists(PRIVATE_FILE_PATH."/".$this->id."/".$this->variant)) {

			$this->conversionFailed(["Missing or bad conversion info - request ignored"]);

			// exits

		}


		// Is autoconverion threshold surpassed
		if(!$this->isBelowAutoconversionThreshold()) {

			if($this->request_type == "images") {

				header("Content-Type: image/png", true, 307);
				print base64_decode("iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVQYV2NgYAAAAAMAAWgmWQ0AAAAASUVORK5CYII");
			}
			else if($this->request_type == "videos") {
				header("Content-Type: video/mp4", true, 307);
				
			}
			else if($this->request_type == "audios") {
				header("Content-Type: audio/mp3", true, 307);

			}
			exit();

		}


		// images
		if($this->request_type == "images" && ($this->width || $this->height) && preg_match("/^(avif|webp|jpg|png|gif)$/", $this->format)) {
			include_once("classes/helpers/image.class.php");

			$Image = new Image();

			// default compression
			$compression = 93;


			// check for sources

			// Newest web image formats

			// avif
			if($this->format === "avif") {
				$input_file = $this->bestInputVariant(PRIVATE_FILE_PATH."/".$this->id."/".$this->variant);
				$compression = 60;
			}
			// webp
			else if($this->format === "webp") {
				$input_file = $this->bestInputVariant(PRIVATE_FILE_PATH."/".$this->id."/".$this->variant);
				$compression = 75;
			}

			// Older web image formats

			// jpg, and source is available
			else if($this->format === "jpg" && file_exists(PRIVATE_FILE_PATH."/".$this->id."/".$this->variant."/jpg")) {
				$input_file = PRIVATE_FILE_PATH."/".$this->id."/".$this->variant."/jpg";
			}
			// png, and source is available
			else if($this->format === "png" && file_exists(PRIVATE_FILE_PATH."/".$this->id."/".$this->variant."/png")) {
				$input_file = PRIVATE_FILE_PATH."/".$this->id."/".$this->variant."/png";
			}
			// gif, and source is available
			else if($this->format === "gif" && file_exists(PRIVATE_FILE_PATH."/".$this->id."/".$this->variant."/gif")) {
				$input_file = PRIVATE_FILE_PATH."/".$this->id."/".$this->variant."/gif";
			}
			// jpg available
			else if(file_exists(PRIVATE_FILE_PATH."/".$this->id."/".$this->variant."/jpg")) {
				$input_file = PRIVATE_FILE_PATH."/".$this->id."/".$this->variant."/jpg";
			}
			// png available
			else if(file_exists(PRIVATE_FILE_PATH."/".$this->id."/".$this->variant."/png")) {
				$input_file = PRIVATE_FILE_PATH."/".$this->id."/".$this->variant."/png";
			}
			// gif available
			else if(file_exists(PRIVATE_FILE_PATH."/".$this->id."/".$this->variant."/gif")) {
				$input_file = PRIVATE_FILE_PATH."/".$this->id."/".$this->variant."/gif";
			}
			// no valid source available
			else {
				$this->conversionFailed(["no valid source available"]);
			}

			$output_file = PUBLIC_FILE_PATH."/".$this->id."/".$this->variant."/".$this->width."x".$this->height.".".$this->format;
			// debug([$input_file, $output_file]);

			// scale image (will autoconvert)
			if($Image->convert($input_file, $output_file, array(
				"compression" => $compression, 
				"allow_cropping" => true, 
				"width" => $this->width, 
				"height" => $this->height, 
				"format" => $this->format, 
				"max_pixels" => $this->max_pixels
			))) {

				// collect log autoconvertion for bundled notification
				logger()->collectNotification($_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"], "autoconversion");

				// Add log
				logger()->addLog("Image converted:" . $_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"], "autoconversion");


				// redirect to new image
				header("Location: /".$this->request_type."/".$this->id."/".$this->variant."/".$this->width."x".$this->height.".".$this->format, true, 307);
				exit();

			}
			else {

				$this->conversionFailed(["Image->convert failed"]);

			}


		}

		// video
		else if($this->request_type === "videos" && ($this->width || $this->height) && preg_match("/^(mp4|webm|ogv|mov|3gp)$/", $this->format)) {
			include_once("classes/helpers/video.class.php");

			$Video = new Video();

			// check for sources

			// mov, and source is available
			if($this->format === "mov" && file_exists(PRIVATE_FILE_PATH."/".$this->id."/".$this->variant."/mov")) {
				$input_file = PRIVATE_FILE_PATH."/".$this->id."/".$this->variant."/mov";
			}
			// mp4, and source is available
			else if($this->format === "mp4" && file_exists(PRIVATE_FILE_PATH."/".$this->id."/".$this->variant."/mp4")) {
				$input_file = PRIVATE_FILE_PATH."/".$this->id."/".$this->variant."/mp4";
			}
			// webm, and source is available
			else if($this->format === "webm" && file_exists(PRIVATE_FILE_PATH."/".$this->id."/".$this->variant."/webm")) {
				$input_file = PRIVATE_FILE_PATH."/".$this->id."/".$this->variant."/webm";
			}
			// ogv, and source is available
			else if($this->format === "ogv" && file_exists(PRIVATE_FILE_PATH."/".$this->id."/".$this->variant."/ogv")) {
				$input_file = PRIVATE_FILE_PATH."/".$this->id."/".$this->variant."/ogv";
			}
			// 3gp, and source is available
			else if($this->format === "3gp" && file_exists(PRIVATE_FILE_PATH."/".$this->id."/".$this->variant."/3gp")) {
				$input_file = PRIVATE_FILE_PATH."/".$this->id."/".$this->variant."/3gp";
			}
			// mov available
			else if(file_exists(PRIVATE_FILE_PATH."/".$this->id."/".$this->variant."/mov")) {
				$input_file = PRIVATE_FILE_PATH."/".$this->id."/".$this->variant."/mov";
			}
			// mp4 available
			else if(file_exists(PRIVATE_FILE_PATH."/".$this->id."/".$this->variant."/mp4")) {
				$input_file = PRIVATE_FILE_PATH."/".$this->id."/".$this->variant."/mp4";
			}
			// webm available
			else if(file_exists(PRIVATE_FILE_PATH."/".$this->id."/".$this->variant."/webm")) {
				$input_file = PRIVATE_FILE_PATH."/".$this->id."/".$this->variant."/webm";
			}
			// ogv available
			else if(file_exists(PRIVATE_FILE_PATH."/".$this->id."/".$this->variant."/ogv")) {
				$input_file = PRIVATE_FILE_PATH."/".$this->id."/".$this->variant."/ogv";
			}
			// 3gp available
			else if(file_exists(PRIVATE_FILE_PATH."/".$this->id."/".$this->variant."/3gp")) {
				$input_file = PRIVATE_FILE_PATH."/".$this->id."/".$this->variant."/3gp";
			}
			// no valid source available
			else {
				$this->conversionFailed(["no valid source available"]);
			}

			$output_file = PUBLIC_FILE_PATH."/".$this->id."/".$this->variant."/".$this->width."x".$this->height.".".$this->format;


			// scale image (will autoconvert)
			if($Video->convert($input_file, $output_file, array(
				"allow_cropping" => true, 
				"width" => $this->width, 
				"height" => $this->height, 
				"format" => $this->format, 
				"max_pixels" => $this->max_pixels
			))) {

				// collect log autoconvertion for bundled notification
				logger()->collectNotification($_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"], "autoconversion");

				// Add log
				logger()->addLog("Video converted:" . $_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"], "autoconversion");


				// redirect to new video
				header("Location: /".$this->request_type."/".$this->id."/".$this->variant."/".$this->width."x".$this->height.".".$this->format, true, 307);
				exit();

			}
			else {

				$this->conversionFailed(["Video->convert failed"]);

			}

		}

		// audio
		else if($this->request_type === "audios" && $bitrate && preg_match("/^(mp3|ogg|wav)$/", $this->format)) {
			include_once("classes/helpers/audio.class.php");

			$Audio = new Audio();

			// mp3, and source is available
			if($this->format === "mp3" && file_exists(PRIVATE_FILE_PATH."/".$this->id."/".$this->variant."/mp3")) {
				$input_file = PRIVATE_FILE_PATH."/".$this->id."/".$this->variant."/mp3";
			}
			// ogg, and source is available
			else if($this->format === "ogg" && file_exists(PRIVATE_FILE_PATH."/".$this->id."/".$this->variant."/ogg")) {
				$input_file = PRIVATE_FILE_PATH."/".$this->id."/".$this->variant."/ogg";
			}
			// wav, and source is available
			else if($this->format === "wav" && file_exists(PRIVATE_FILE_PATH."/".$this->id."/".$this->variant."/wav")) {
				$input_file = PRIVATE_FILE_PATH."/".$this->id."/".$this->variant."/wav";
			}
			else if(file_exists(PRIVATE_FILE_PATH."/".$this->id."/".$this->variant."/mp3")) {
				$input_file = PRIVATE_FILE_PATH."/".$this->id."/".$this->variant."/mp3";
			}
			else if(file_exists(PRIVATE_FILE_PATH."/".$this->id."/".$this->variant."/ogg")) {
				$input_file = PRIVATE_FILE_PATH."/".$this->id."/".$this->variant."/ogg";
			}
			else if(file_exists(PRIVATE_FILE_PATH."/".$this->id."/".$this->variant."/wav")) {
				$input_file = PRIVATE_FILE_PATH."/".$this->id."/".$this->variant."/wav";
			}
			else {
				$this->conversionFailed(["no valid source available"]);
			}


			$output_file = PUBLIC_FILE_PATH."/".$this->id."/".$this->variant."/".$this->bitrate.".".$this->format;


			// scale image (will autoconvert)
			if($Audio->convert($input_file, $output_file, array(
				"bitrate" => $this->bitrate, 
				"format" => $this->format
			))) {
				// TODO: implement bit rate control in audio class first, "max_bitrate" => $max_bitrate

				// collect log autoconvertion for bundled notification
				logger()->collectNotification($_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"], "autoconversion");

				// Add log
				logger()->addLog("Audio converted:" . $_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"], "autoconversion");


				// redirect to new audio file
				header("Location: /".$this->request_type."/".$this->id."/".$this->variant."/".$this->bitrate.".".$this->format, true, 307);
				exit();

			}
			else {

				$this->conversionFailed(["Audio->convert failed"]);

			}

		}


		// something weren't as expected
		$this->conversionFailed(["Missing or bad conversion info - request not completed"]);

	}


	// Check if autoconversions are below threshold 
	// and if disk space is ok
	function isBelowAutoconversionThreshold() {


		$halted_conversions = cache()->value("autoconversions-halted");
		// debug(["halted-state", $halted_conversions]);

		// Open for notifications
		if(!$halted_conversions) {


			// Disk check
			$total_diskspace = disk_total_space("/");
			$available_diskspace = disk_free_space("/");


			// Check autoconversions repetition threshold to avoid data overload (with potential disk failure)
			$query = new Query();
			$query->checkDbExistence($this->db_autoconversions);

			// Clean up old entries (older than one minute)
			$query->sql("DELETE FROM ".$this->db_autoconversions." WHERE converted_at < '".date("Y-m-d H:i:s", strtotime("- 1 minute"))."'");

			// How many messages has been sent within the last minute then
			$query->sql("SELECT count(*) AS conversions FROM ".$this->db_autoconversions);
			$conversions = $query->result(0, "conversions");

			$threshold = (defined("SITE_AUTOCONVERSION_THRESHOLD") && is_integer(SITE_AUTOCONVERSION_THRESHOLD) ? SITE_AUTOCONVERSION_THRESHOLD : 100);

			if($available_diskspace / $total_diskspace < 0.05) {

				// debug(["halt the autoconversions due to available space"]);
				cache()->value("autoconversions-halted", "true", 60);

				$_halted_options = [
					"subject" => "Autoconversions from ".SITE_URL." halted due to disk overload risk",
					"message" => "Available diskspace on ".SITE_URL." is below the specified threshold of 5%. Autoconversions have been halted to avoid disk exhaustion."
				];

				admin()->notify($_halted_options);

			}
			// Threshold repetition exceeded
			else if($conversions >= $threshold) {

				// debug(["halt the autoconversions due to repetition"]);
				cache()->value("autoconversions-halted", "true", 60);

				$_halted_options = [
					"subject" => "Autoconversions on ".SITE_URL." halted due to repetition overload risk",
					"message" => "Autoconversions on ".SITE_URL." exceeded the specified threshold of $threshold pr. minute. Autoconversions have been halted to avoid server exhaustion"
				];

				admin()->notify($_halted_options);

			}
			// Autoconversions are ok
			else {

				// debug(["do the autoconversion"]);
				$query->sql("INSERT INTO ".$this->db_autoconversions." SET invoked_by_ip = '".security()->getRequestIp()."'");

				return true;

			}

		}
		// extend halt period on continuous errors
		else {

			cache()->value("autoconversions-halted", "true", 60);

		}

		return false;
	}


	// Find best input option (looking for format most suitable for conversion)
	function bestInputVariant($variant_path) {

		if(file_exists($variant_path."/avif")) {
			return $variant_path."/avif";
		}
		else if(file_exists($variant_path."/webp")) {
			return $variant_path."/webp";
		}
		else if(file_exists($variant_path."/jpg")) {
			return $variant_path."/jpg";
		}
		else if(file_exists($variant_path."/png")) {
			return $variant_path."/png";
		}
		else if(file_exists($variant_path."/gif")) {
			return $variant_path."/gif";
		}

	}


	// Look for variant redirect (might occur after mediae re-organization)
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


	// error handling
	function conversionFailed($reason) {
		// debug(["conversionFailed", $reason]);


		global $page;

		// get into the detail to make debugging as easy as possible
		$reason[] = $_SERVER["REQUEST_URI"];
		$reason[] = "Request type: ".$this->request_type;
		$reason[] = "Format: ".$this->format;

		if($this->request_type == "audios") {
			if(!$this->bitrate) {
				$reason[] = "Bitrate: MISSING";
			}
			else {
				$reason[] = "Bitrate: ".$this->bitrate;
			}
		}
		else {
			if(!$this->width && !$this->height) {
				$reason[] = "Width and Height: MISSING";
			}
			else if($this->width) {
				$reason[] = "Width: ".$this->width;
			}
			else if($this->height) {
				$reason[] = "Height: ".$this->height;
			}
		}

		// specify reason
		if($this->id && !file_exists(PRIVATE_FILE_PATH."/".$this->id)) {
			$reason[] = "ID does not exist: ".$this->id;
		}
		else if($this->id && $this->variant && !file_exists(PRIVATE_FILE_PATH."/".$this->id."/".$this->variant)) {
			$reason[] = "Variant does not exist: ".$this->variant;
		}


		$segment = $page->segment();
		// debug([$segment]);

		// TODO: implement fallback for audio and video
		// TODO: implement constraints to avoid media generation abuse
		// TODO: investigate use-cases and figure out if returning 404/410 is better

		// Clearly tell robots that this image is gone
		if($segment === "seo") {
			// facebookexternalhit/1.1 (+http://www.facebook.com/externalhit_uatext.php)

			// admin()->notify([
			// 	"subject" => "Autoconversion failed SEO - 410 Test ($request_type)",
			// 	"message" => $reason,
			// 	"template" => "system"
			// ]);

			header("Location: /images/0/missing/".$this->width."x".$this->height.".png", true, 410);

		}

		// return missing image if it exists (and request is for image)
		// print $request_type . ", " . file_exists(PRIVATE_FILE_PATH."/0/missing/png")." && ". $width ."&&". $height;
		else if($this->request_type == "images" && file_exists(PRIVATE_FILE_PATH."/0/missing/png") && ($this->width || $this->height)) {


			// Add log
			logger()->addLog("Autoconversion failed:" . $_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"] . " - " . implode(", ", $reason), "autoconversion-errors");


			// Only send error message if needed
			if(!defined("SITE_AUTOCONVERSION_ERROR_NOTIFICATIONS") || SITE_AUTOCONVERSION_ERROR_NOTIFICATIONS) {

				admin()->notify([
					"subject" => "Autoconversion failed ($this->request_type)",
					"message" => implode("<br>", $reason),
					"template" => "system",
					// "debug" => true,
				]);

			}

			// 404 does not return "missing image", so use 307
			header("Location: /images/0/missing/".$this->width."x".$this->height.".png", true, 307);
		}

		// dangerous to return HTML - receiving JS will expect media, not HTML
		else {
	//		header("Location: /janitor/admin/404");
		}
		exit();
	}


}