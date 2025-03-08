<?php
include_once("classes/helpers/filesystem.class.php");
include_once("includes/functions.inc.php");

class Video {


	function convert($input_file, $output_file, $_options = false) {

		$fs = new FileSystem();

		$output_width = false;
		$output_height = false;
		$output_format = false;
		$output_bitrate = false;

		$allow_conversion = true;
		$allow_cropping = false;
		$allow_stretching = false;
		$allow_padding = false;

		$max_pixels = 0;

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					case "width"             : $output_width       = $_value; break;
					case "height"            : $output_height      = $_value; break;
					case "format"            : $output_format      = $_value; break;
					case "bitrate"           : $output_bitrate     = $_value; break;

					case "allow_conversion"  : $allow_conversion   = $_value; break;
					case "allow_cropping"    : $allow_cropping     = $_value; break;
					case "allow_stretching"  : $allow_stretching   = $_value; break;
					case "allow_padding"     : $allow_padding      = $_value; break;

					case "max_pixels"        : $max_pixels         = $_value; break;
				}
			}
		}

		$ffmpeg_path = ffmpegPath();

		$info = $this->info($input_file);
		if(is_array($info)) {
			$input_width = $info["width"];
			$input_height = $info["height"];
		}
		$input_format = substr($input_file, -3);


//		print "input_format:" . $input_format.", output_format:" . $output_format . "<br>";


		// is input format different from output format - AND conversion not allowed
		if($input_format != $output_format && !$allow_conversion) {
			// critical error - report to admin

			admin()->notify(array(
				"subject" => "ffmpeg failed", 
				"messsage" => "ffmpeg failed to read source video proporties", 
				"template" => "system"
			));

			return false;
		}


		// only width OR height stated
		// use source proportions to calculate other value
		if((!$output_width && $output_height) || ($output_width && !$output_height)) {

			// height defined by width
			if($output_width) {
				$output_height = round($output_width / ($input_width / $input_height));
			}
			// width defined by height
			else {
				$output_width = round($output_height / ($input_height / $input_width));
			}

		}

		// max pixels detection
		if($max_pixels && $output_width * $output_height > $max_pixels) {
			// critical error - report to admin

			admin()->notify(array(
				"subject" => "Video failed ($output_width x $output_height)", 
				"message" => "Video size too big", 
				"template" => "system"
			));
			return false;
		}


		// special size handling for 3gp files (codec restrictions)
		if($output_format == "3gp") {
			
			// allowed sizes are:
			// sqcif = 128x96
			// qcif = 176x144
			// cif = 352x288
			// 4cif = 704x576 ? ffmpeg issue
			// 16cif = 1408x1152 ? ffmpeg issue

			if($output_width <= 128) {
				$output_width = 128;
				$output_height = 96;
			}
			else if($output_width <= 176) {
				$output_width = 176;
				$output_height = 144;
			}
			else {
			// else if($output_width <= 352) {
				$output_width = 352;
				$output_height = 288;
			}

			// ffmpeg creates green screen for these sizes
			// it is considered an acceptable compromise to leave them out as 3gp is targeting old mobile devices
			// else if($output_width <= 704) {
			// 	$output_width = 704;
			// 	$output_height = 576;
			// }
			// else {
			// 	$output_width = 1408;
			// 	$output_height = 1152;
			// }
		}


		// remember actual canvas size
		// canvas width and height must be divisible with 2
		$canvas_width = $output_width%2 ? $output_width-1 : $output_width;
		$canvas_height = $output_height%2 ? $output_height-1 : $output_height;


		// image adjustment values
		$input_left = 0;
		$input_top = 0;
		$output_left = 0;
		$output_top = 0;


		$input_proportions = round($input_width/$input_height, 2);
		$output_proportions = round($output_width/$output_height, 2);

		// print "size:" . $input_width . "x" . $input_height . " => " . $output_width . "x" . $output_height . "<br>";
		// print "proportions: input:" . $input_proportions . ", output:" . $output_proportions . "<br>";


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

				}
				else if($allow_padding) {
//					print "allow padding<br>";


					// pad height
					if($input_proportions > $output_proportions) {
//						print "width is limit, pad height<br>";

						$output_top = round(($output_height - ($output_width / $input_proportions))/2);
						$input_height = round($output_width /  $input_proportions);
						$input_width = $output_width;
					}
					// pad width
					else {
//						print "height is limit, pad width<br>";

						$output_left = round(($output_width - ($output_height *  $input_proportions))/2);
						$input_width = round($output_height *  $input_proportions);
						$input_height = $output_height;
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
				}

				else if($allow_padding) {
//					print "allow padding<br>";

					// pad height
					if($input_proportions > $output_proportions) {
//						print "width is limit, pad height<br>";

						$output_top = round(($output_height - ($output_width / $input_proportions))/2);
						$input_height = round($output_width /  $input_proportions);
						$input_width = $output_width;
					}
					// pad width
					else {
//						print "height is limit, pad width<br>";

						$output_left = round(($output_width - ($output_height *  $input_proportions))/2);
						$input_width = round($output_height *  $input_proportions);
						$input_height = $output_height;
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


		// rough bitrate calculator
		// only if bitrate is not stated explicitly
		if(!$output_bitrate) {

			// should be adjusted for framerate (25), quality (2), and input_bitrate 
			$output_bitrate = ($output_width * $output_height * 25 * 2 * 0.07) / 1000;

			// print $output_bitrate."<br>";
			// exit();

			// ogv, 3gp and mov's needs higher bitrate when converting from compressed source
			if($output_format == "ogv") {
				$output_bitrate = $output_bitrate*2;
			}
			else if($output_format == "3gp") {
				$output_bitrate = $output_bitrate*1.5;
			}
			else if($output_format == "mov") {
				$output_bitrate = $output_bitrate*2;
			}

			$output_bitrate = round($output_bitrate);
		}

//		print "bitrate: input:" . (isset($info["bitrate"]) ? $info["bitrate"] : "N/A") . ": output:" . $output_bitrate . "<br>";



		// keep proportions and input is bigger than output
		if(file_exists($input_file) && $ffmpeg_path) {


			// make sure output path exists
			$fs->makeDirRecursively(dirname($output_file));


			// make crop/pad setting
			$crop = "";
			$pad = "";
			if($allow_cropping && ($input_left || $input_top)) {
				$crop = "-vf crop=".$input_width.":".$input_height.":".$input_left.":".$input_top;
			}
			else if($allow_padding && ($output_left || $output_top)) {
				$pad = "-vf scale=".$input_width.":".$input_height.",pad=".$output_width.":".$output_height.":".$output_left.":".$output_top;
			}

			// print_r($info);
			// print "<br>";


			// set specific duration
			$duration = "";
			if($info["hours"] && $info["minutes"] && $info["seconds"] && $info["fractions"]) {

				$h_f = 60*60*1000;
				$m_f = 60*1000;
				$s_f = 1000;

				// print $info["fractions"] . ":" . parseFloat("0.".$info["fractions"]) . "<br>";
				// print (parseFloat("0.".$info["fractions"])*1000) ."<br>";
				$duration_ms = ($info["hours"]*$h_f) + ($info["minutes"]*$m_f) + ($info["seconds"]*$s_f) + $info["fractions"];

				// EXPERIMENTAL
				// shorten video by 20ms to avoid audio/video sync error noise
				// $duration_ms = $duration_ms - 20;

				$hours = floor($duration_ms / ($h_f));
				$hours = $hours < 10 ? "0".$hours : $hours;
				$minutes = floor(($duration_ms - ($hours*$h_f)) / ($m_f));
				$minutes = $minutes < 10 ? "0".$minutes : $minutes;
				$seconds = floor(($duration_ms - ($hours*$h_f) - ($minutes*$m_f)) / ($s_f));
				$seconds = $seconds < 10 ? "0".$seconds : $seconds;
				$fractions = floor(($duration_ms - ($hours*$h_f) - ($minutes*$m_f) - ($seconds*$s_f)));
				$fractions = $fractions < 100 ? ("0".$fractions) : ($fractions < 10 ? ("00".$fractions) : $fractions);

				$duration = "-t ".$hours.":".$minutes.":".$seconds.".".$fractions;
			}



			// MOV output
			if($output_format == "mov") {
				$command = $ffmpeg_path . " -y -i ".$input_file." ".$duration." -c:v mpeg2video -b:v ".$output_bitrate."k -f mov -c:a libmp3lame -ar 48000 -b:a 128k ".$crop.$pad." -s ".$canvas_width."x".$canvas_height." ".$output_file;
			}

			// MP4 output
			else if($output_format == "mp4") {
				$command = $ffmpeg_path . " -y -i ".$input_file." ".$duration." -qmin 10 -qmax 40 -crf 30 -c:v libx264 -b:v ".$output_bitrate."k -c:a ".ffmpegAACCodec()." -ar 48000 -b:a 128k ".$crop.$pad." -s ".$canvas_width."x".$canvas_height." ".$output_file;
			}

			// WEBM output
			else if($output_format == "webm") {
				$command = $ffmpeg_path . " -y -i ".$input_file." ".$duration." -qmin 10 -qmax 40 -crf 30 -c:v libvpx -b:v ".$output_bitrate."k -c:a libvorbis -ar 48000 -b:a 128k ".$crop.$pad." -s ".$canvas_width."x".$canvas_height." ".$output_file;
			}

			// OGV output
			else if($output_format == "ogv") {
				$command = $ffmpeg_path . " -y -i ".$input_file." ".$duration." -c:v libtheora -b:v ".$output_bitrate."k -c:a libvorbis -ar 48000 -b:a 128k ".$crop.$pad." -s ".$canvas_width."x".$canvas_height." ".$output_file;
			}

			// 3GP output
			else if($output_format == "3gp") {
				$command = $ffmpeg_path . " -y -i ".$input_file." ".$duration." -c:v h263 -b:v ".$output_bitrate."k -c:a ".ffmpegAACCodec()." -ac 1 -ar 32000 -ab 32k ".$crop.$pad." -s ".$canvas_width."x".$canvas_height." ".$output_file;
			}


			// proper command available
			if($command) {
//				writeToFile($command);
				system($command);
			}

			// successful conversion
			if(file_exists($output_file)) {
				return true;
			}

		}

		admin()->notify(array(
			"subject" => "ffmpeg failed", 
			"message" => "Could not output video file (could be missing codec or filepermissions issue)", 
			"template" => "system"
		));
		return false;

	}


	function info($file) {
		$video_info = false;

		$ffmpeg_path = ffmpegPath();

		if($ffmpeg_path) {


			$command = $ffmpeg_path . " -i " . escapeshellarg($file) . " 2>&1";
			exec($command, $output);
			// combine output to one string
			$output = implode("\n", $output);

			preg_match("/Duration: [^\n]+/", $output, $duration);
			preg_match("/Stream #?.*\: Video: [^\n]+/", $output, $stream_video);
			preg_match("/Stream #?.*\: Audio: [^\n]+/", $output, $stream_audio);

			// print "<br>ffmpeg_out<br>";
			// print(nl2br($output));
			// print "<br>";

			// print "<br>Lines: ".$file."<br>";
			// print_r($duration);
			// print "<br>";
			// 
			// print "<br>Stream, video line<br>";
			// print_r($stream_video);
			// print "<br>";
			// 
			// print "<br>Stream, audio line<br>";
			// print_r($stream_audio);
			// print "<br>";


			if($stream_video && $duration) {

				// get filesize
				$video_info = [];
				$video_info["filesize"] = filesize($file);

				// parse Video line
				if(!preg_match("/Video: (?P<codec>.*), (?P<format>.*), (?P<width>\d+)x(?P<height>\d+)(.*) (?P<bitrate>\d+(\.\d+)?) (?P<bitrateunit>[\w\(\)]+)\/s(.*) (?P<fps>\d+(\.\d+)?) fps/", $stream_video[0], $matches)) {
					if(!preg_match("/Video: (?P<codec>.*), (?P<format>.*), (?P<width>\d+)x(?P<height>\d+)(.*) (?P<fps>\d+(\.\d+)?) fps/", $stream_video[0], $matches)) {

						// insert alternative expressions here
						return false;

					}
				}

				if($matches) {
					$video_info["width"] = intval($matches["width"]);
					$video_info["height"] = intval($matches["height"]);
					$video_info["codec"] = $matches["codec"];
					$video_info["format"] = $matches["format"];
					$video_info["fps"] = $matches["fps"];
					$video_info["bitrate"] = isset($matches["bitrate"]) ? $matches["bitrate"] : false;
				}
				// print_r($matches);
				// print "<br>";
				
				// parse duration line
				if(!preg_match("/Duration: (?P<hours>\d{1,3}):(?P<minutes>\d{2}):(?P<seconds>\d{2})(.(?P<fractions>\d{1,3}))(.*), bitrate: (?P<bitrate>\d+(\.\d+)?) (?P<bitrateunit>[\w\(\)]+)\/s/", $duration[0], $matches)) {
					if(!preg_match("/Duration: (?P<hours>\d{1,3}):(?P<minutes>\d{2}):(?P<seconds>\d{2})(.(?P<fractions>\d{1,3}))/", $duration[0], $matches)) {

						// insert alternative expressions here
						return false;

					}
				}

				if($matches) {
					$video_info["hours"] = parseInt($matches["hours"]);
					$video_info["minutes"] = parseInt($matches["minutes"]);
					$video_info["seconds"] = parseInt($matches["seconds"]);
					$video_info["fractions"] = parseFloat("0.".$matches["fractions"])*1000;

					$video_info["duration"] = ($video_info["hours"] * 60 * 60 * 1000) + ($video_info["minutes"] * 60 * 1000) + ($video_info["seconds"] * 1000) + $video_info["fractions"];

					if(!$video_info["bitrate"] && isset($matches["bitrate"])) {
						$video_info["bitrate"] = $matches["bitrate"];
					}
				}
				 // print_r($matches);
				 // print "<br>";
			}


		}

		return $video_info;
	}

}

?>
