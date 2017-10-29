<?php
include_once("classes/system/filesystem.class.php");
include_once("includes/functions.inc.php");


// ImageMagick interface
class Image {


	// allow conversion false = only scale
	function convert($input_file, $output_file, $options = false) {

		$fs = new FileSystem();

		$output_width = false;
		$output_height = false;
		$output_format = false;
		$output_compression = 100;

		$allow_conversion = true;
		$allow_cropping = false;
		$allow_stretching = false;
		$allow_padding = false;
		$background = false;

		$max_pixels = 0;

		if($options !== false) {
			foreach($options as $option => $value) {
				switch($option) {
					case "width"            : $output_width           = $value; break;
					case "height"           : $output_height          = $value; break;
					case "format"           : $output_format          = preg_replace("/JPG/", "JPEG", strtoupper($value)); break;
					case "compression"      : $output_compression     = $value; break;

					case "allow_conversion" : $allow_conversion       = $value; break;
					case "allow_cropping"   : $allow_cropping         = $value; break;
					case "allow_stretching" : $allow_stretching       = $value; break;
					case "allow_padding"    : $allow_padding          = $value; break;
					case "background"       : $background             = $value; break;

					case "max_pixels"       : $max_pixels             = $value; break;
				}
			}
		}

		// create imagemagick object
		$image = new Imagick($input_file);

		// get input file info
		$info = $image->getImageFormat();

		if($info) {
			$input_width = $image->getImageWidth();
			$input_height = $image->getImageHeight();
			$input_format = $info;
		}

		// not able to read input_file properly
		if(!$input_width || !$input_height || !$input_format) {
			// critical error - report to admin
			mailer()->send([
				"subject" => "getImageFormat, getImageWidth or getImageHeight failed", 
				"message" => "ImageMagick failed to read source image proporties", 
				"template" => "system"
			]);
			return false;
		}

		if(!$output_format) {
			$output_format = $info;
		}

//		print "input_format:" . $input_format.", output_format:" . $output_format . "<br>";


		// is input format different from output format - AND conversion not allowed
		if($input_format != $output_format && !$allow_conversion) {
			return false;
		}

		// SET IMAGE FORMAT
		$image->setImageFormat($output_format);


		// only width OR height stated
		// use source proportions to calculate other value
		if((!$output_width && $output_height) || ($output_width && !$output_height)) {

			// height defined by width
			if($output_width) {
				$output_height = $output_width / ($input_width / $input_height);
			}
			// width defined by height
			else {
				$output_width = $output_height / ($input_height / $input_width);
			}

		}

		// max pixels detection
		if($max_pixels && $output_width * $output_height > $max_pixels) {
			// critical error - report to admin
			mailer()->send(array(
				"subject" => "Image failed ($output_width x $output_height)", 
				"message" => "Image size too big", 
				"template" => "system"
			));
			return false;
		}

		// remember actual canvas size
		$canvas_width = $output_width;
		$canvas_height = $output_height;



		// image adjustment values
		$input_left = 0;
		$input_top = 0;
		$output_left = 0;
		$output_top = 0;


		$input_proportions = round($input_width/$input_height, 2);
		$output_proportions = round($output_width/$output_height, 2);

//		print "size:" . $input_width . "x" . $input_height . " => " . $output_width . "x" . $output_height . "<br>";
//		print "proportions: input:" . $input_proportions . ", output:" . $output_proportions . "<br>";

	 	// input and output values are adjusted
		// source is bigger
		if($input_width >= $output_width && $input_height >= $output_height) {
//			print "source is sufficient<br>";

			// different proportion than source
			// check for padding, stretching or cropping 
			if($input_proportions != $output_proportions) {
//				print "proportions are OFF<br>";

				if($allow_cropping) {
//					print "allow cropping<br>";

					// crop width
					if($input_proportions > $output_proportions) {
//						print "height is limit, crop width<br>";

						$input_left = round(($input_width - ($input_height *  $output_proportions))/2);
						$input_width = round($input_height *  $output_proportions);
					}
					// crop height
					else {
//						print "height is limit, crop height<br>";

						$input_top = round(($input_height - ($input_width /  $output_proportions))/2);
						$input_height = round($input_width /  $output_proportions);
					}

					// CROP IMAGE
					$image->cropImage($input_width, $input_height, $input_left, $input_top);

				}
				else if($allow_padding) {
//					print "allow padding<br>";

					mailer()->send(array(
						"subject" => "Image failed ($output_width x $output_height)", 
						"message" => "Image padding attempted - not supported yet", 
						"template" => "system"
					));

					return false;

					if($background) {
						$image->setImageBackgroundColor($background);
					}
					$image->setImageGravity("center");

					// pad height
					if($input_proportions > $output_proportions) {
//						print "width is limit, pad height<br>";

						$output_top = round(($output_height - ($output_width / $input_proportions))/2);
						$input_height = round($input_width /  $output_proportions);
					}

					// pad width
					else {
//						print "height is limit, pad width<br>";

						$output_left = round(($output_width - ($output_height *  $input_proportions))/2);
						$input_width = round($input_height *  $output_proportions);
					}
				}
				// if stretching allowed we do not need to do anything
				// off proportions and no extra manipulation allowed
				else if(!$allow_stretching) {
					return false;
				}

			}

		}

		// source is smaller
		else {
//			print "source is INsufficient<br>";

			// different proportion than source
			// check for padding, stretching or cropping 
			if($input_proportions != $output_proportions) {
//				print "proportions are OFF<br>";

				if($allow_cropping) {
//					print "allow cropping<br>";

					// crop width
					if($input_proportions > $output_proportions) {
//						print "height is limit, crop width<br>";

						$input_left = round(($input_width - ($input_height *  $output_proportions))/2);
						$input_width = round($input_height *  $output_proportions);
					}
					// crop height
					else {
//						print "height is limit, crop height<br>";

						$input_top = round(($input_height - ($input_width /  $output_proportions))/2);
						$input_height = round($input_width /  $output_proportions);
					}

					// CROP IMAGE
					$image->cropImage($input_width, $input_height, $input_left, $input_top);
				}

				else if($allow_padding) {
//					print "allow padding<br>";

					mailer()->send(array(
						"subject" => "Image failed ($output_width x $output_height)", 
						"message" => "Image padding attempted - not supported yet", 
						"template" => "system"
					));

					return false;

					// pad height
					if($input_height < $output_height) {
//						print "height is too big, pad height<br>";

						$output_top = round(($output_height - $input_height)/2);
						$output_height = $input_height;
					}
					// pad width
					if($input_width < $output_width) {
//						print "height is limit, pad width<br>";

						$output_left = round(($output_width - $input_width)/2);
						$output_width = $input_width;
					}
				}
				// if stretching allowed we do not need to do anything
				// off proportions and no extra manipulation allowed
				else if(!$allow_stretching) {
					return false;
				}

			}

		}

		// print "input:input_left:" . $input_left . ":input_top:" . $input_top . "<br>";
		// print "output:output_left:" . $output_left . ":output_top:" . $output_top . "<br>";
		// print "size:" . $input_width . "x" . $input_height . " => " . $output_width . "x" . $output_height . "<br>";



//		exit();


		// START CONVERSION

		// output image canvas
//		$output_image = imagecreatetruecolor($canvas_width, $canvas_height);

		// make sure output path exists
		$fs->makeDirRecursively(dirname($output_file));





		// // read input file
		// if($input_format == "jpg") {
		// 
		// 	$input_image = imagecreatefromjpeg($input_file);
		// }
		// else if($input_format == "png") {
		// 	$input_image = imagecreatefrompng($input_file);
		// 	// add aplha state (required for transparent png's)
		// 	imageAlphaBlending($output_image, false);
		// }
		// else if($input_format == "gif") {
		// 	$input_image = imagecreatefromgif($input_file);
		// }
		// // unknown input format
		// else {
		// 	return false;
		// }

		// resample image
//		imagecopyresampled($output_image, $input_image, 0, 0, 0, 0, $output_width, $output_height, $input_width, $input_height);
//		imagecopyresampled($output_image, $input_image, $output_left, $output_top, $input_left, $input_top, $output_width, $output_height, $input_width, $input_height);


		// output image
		if($output_format == "JPEG") {

			$image->scaleImage($output_width, $output_height);

			$image->sharpenImage(1.5, 1);

			$image->setImageCompressionQuality($output_compression);
			$image->stripImage();
			return $image->writeImage($output_file);
		}
		else if($output_format == "PNG") {

			$image->scaleImage($output_width, $output_height);

//			$image->sharpenImage(0);

			$image->stripImage();
			return $image->writeImage($output_file);
		}
		else if($output_format == "GIF") {

			$image->scaleImage($output_width, $output_height);

//			$image->sharpenImage(0);

			$image->stripImage();
			return $image->writeImage($output_file);
		}
		else {
			return false;
		}

	}


	function info($file) {
		$image_info = false;

		$image = new Imagick($file);

		// get image size
		$geometry = $image->getImageGeometry();
		$image_info["width"] = $geometry['width'];
		$image_info["height"] = $geometry['height'];

		// get image format
		$image_info["format"] = preg_replace("/jpeg/", "jpg", strtolower($image->getImageFormat()));

		// get filesize
		$image_info["filesize"] = filesize($file);

		return $image_info;
	}
}

?>
