<?php
include_once("classes/system/filesystem.class.php");
include_once("includes/functions.inc.php");

class Video {


	function convert($input_file, $output_file, $options = false) {

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

		if($options !== false) {
			foreach($options as $option => $value) {
				switch($option) {
					case "width"             : $output_width       = $value; break;
					case "height"            : $output_height      = $value; break;
					case "format"            : $output_format      = $value; break;
					case "bitrate"           : $output_bitrate     = $value; break;

					case "allow_conversion"  : $allow_conversion   = $value; break;
					case "allow_cropping"    : $allow_cropping     = $value; break;
					case "allow_stretching"  : $allow_stretching   = $value; break;
					case "allow_padding"     : $allow_padding      = $value; break;

					case "max_pixels"        : $max_pixels         = $value; break;
				}
			}
		}

		$ffmpeg_path = $this->ffmpegPath();

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
			global $page;
			$page->mail(array(
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
			global $page;
			$page->mail(array(
				"subject" => "Video failed ($output_width x $output_height)", 
				"message" => "Video size too big", 
				"template" => "system"
			));
			return false;
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
//			$output_bitrate = ($output_width * $output_height * 25 * 2 * 0.07) / 1000;
			$output_bitrate = ($output_width * $output_height * 25 * 2 * 0.07) / 1000;

			// print $output_bitrate."<br>";
			// exit();
			// mostly ogv's needs higher bitrate when converting from compressed source
			if($output_format == "ogv") {
				$output_bitrate = $output_bitrate*1.5;
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

			$duration = "";
			if($info["hours"] && $info["minutes"] && $info["seconds"] && $info["fractions"]) {
				// TODO: cut off maybe 2/100 of a second more - calculate timestamp, deducts 0.02 and write out duration again ...
				$h_f = 60*60*1000;
				$m_f = 60*1000;
				$s_f = 1000;

				// print $info["fractions"] . ":" . parseFloat("0.".$info["fractions"]) . "<br>";
				// print (parseFloat("0.".$info["fractions"])*1000) ."<br>";
				$duration_ms = (parseInt($info["hours"])*$h_f) + (parseInt($info["minutes"])*$m_f) + (parseInt($info["seconds"])*$s_f) + parseFloat("0.".$info["fractions"])*1000;

				// shorten video by 20ms to avoid audio/video sync error noise
//				$duration_ms = $duration_ms - 20;

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


			// read input file
			// TODO: make fallback mov-version? (which does currently not work)
			if($output_format == "mov") {

				// print "mov:".$ffmpeg_path . " -y -i ".$input_file." -qscale 0 ".$crop.$pad." ".$output_file."<br>";
				system($ffmpeg_path . " -y -i ".$input_file." -qscale 0 ".$crop.$pad." ".$output_file);
			}
			else if($output_format == "mp4") {

				// -r 20 and -g 40 gives audio/video sync issues
				// added duration to avoid small noise bit in end of video


				// FOR VERSION 1.1.2
				//print $ffmpeg_path . " -y -i ".$input_file." ".$duration." -acodec libfaac -ab 128k -vcodec libx264 -b ".$output_bitrate."k -preset medium ".$crop.$pad." -s ".$canvas_width."x".$canvas_height." ".$output_file . "<br>";
//	latest			system($ffmpeg_path . " -y -i ".$input_file." ".$duration." -acodec libfaac -ab 128k -vcodec libx264 -b ".$output_bitrate."k -preset medium ".$crop.$pad." -s ".$canvas_width."x".$canvas_height." ".$output_file);


				// FOR VERSION 2.1.1
				system($ffmpeg_path . " -y -i ".$input_file." ".$duration." -acodec libfaac -ab 128k -qmin 10 -qmax 40 -crf 30 -vcodec libx264 -b ".$output_bitrate."k ".$crop.$pad." -s ".$canvas_width."x".$canvas_height." ".$output_file);


//	old			system($ffmpeg_path . " -y -i ".$input_file." -r 20 -g 40 -acodec libfaac -ar 48000 -ab 128k -vcodec libx264 -b ".$output_bitrate."k -preset medium ".$crop.$pad." -s ".$canvas_width."x".$canvas_height." ".$output_file);
			}
			else if($output_format == "ogv") {


				// print $ffmpeg_path . " -y -i ".$input_file." -r 20 -g 40 -acodec libvorbis -ar 48000 -ab 128k -vcodec libtheora -b ".$output_bitrate."k ".$crop.$pad." -s ".$canvas_width."x".$canvas_height." ".$output_file . "<br>";
				system($ffmpeg_path . " -y -i ".$input_file." ".$duration." -acodec libvorbis -ab 128k -vcodec libtheora -b ".$output_bitrate."k ".$crop.$pad." -s ".$canvas_width."x".$canvas_height." ".$output_file);
//				system($ffmpeg_path . " -y -i ".$input_file." -r 20 -g 40 -acodec libvorbis -ar 48000 -ab 128k -vcodec libtheora -b ".$output_bitrate."k ".$crop.$pad." -s ".$canvas_width."x".$canvas_height." ".$output_file);
			}
			else if($output_format == "3gp") {


				// print $ffmpeg_path . " -y -i ".$input_file." -r 20 -g 40 -acodec aac -ac 1 -ar 8000 -r 25 -ab 32 -vcodec h263 -b ".$output_bitrate."k ".$crop.$pad." -s ".$canvas_width."x".$canvas_height." ".$output_file . "<br>";
				system($ffmpeg_path . " -y -i ".$input_file." -acodec aac -ac 1 -ar 8000 -r 25 -ab 32 -vcodec h263 -b ".$output_bitrate."k ".$crop.$pad." -s ".$canvas_width."x".$canvas_height." ".$output_file);
//				system($ffmpeg_path . " -y -i ".$input_file." -r 20 -g 40 -acodec aac -ac 1 -ar 8000 -r 25 -ab 32 -vcodec h263 -b ".$output_bitrate."k ".$crop.$pad." -s ".$canvas_width."x".$canvas_height." ".$output_file);
			}


			if(file_exists($output_file)) {
				return true;
			}

		}

		global $page;
		$page->mail(array(
			"subject" => "ffmpeg failed", 
			"message" => "Could not output video file (could be missing codec or filepermissions issue)", 
			"template" => "system"
		));
		return false;

	}


	function info($file) {
		$ffmpeg_path = $this->ffmpegPath();

		if($ffmpeg_path) {
			$video_info = false;


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

				// parse Video line
				if(!preg_match("/Video: (?P<codec>.*), (?P<format>.*), (?P<width>\d+)x(?P<height>\d+)(.*) (?P<bitrate>\d+(\.\d+)?) (?P<bitrateunit>[\w\(\)]+)\/s(.*) (?P<fps>\d+(\.\d+)?) fps/", $stream_video[0], $matches)) {
					if(!preg_match("/Video: (?P<codec>.*), (?P<format>.*), (?P<width>\d+)x(?P<height>\d+)(.*) (?P<fps>\d+(\.\d+)?) fps/", $stream_video[0], $matches)) {

						// insert alternative expressions here
						return false;

					}
				}

				if($matches) {
					$video_info["width"] = $matches["width"];
					$video_info["height"] = $matches["height"];
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
					$video_info["hours"] = $matches["hours"];
					$video_info["minutes"] = $matches["minutes"];
					$video_info["seconds"] = $matches["seconds"];
					$video_info["fractions"] = $matches["fractions"];

					if(!$video_info["bitrate"] && isset($matches["bitrate"])) {
						$video_info["bitrate"] = $matches["bitrate"];
					}
				}
				 // print_r($matches);
				 // print "<br>";
			}

			return $video_info;

		}

		return false;
	}


	function ffmpegPath() {
		$ffmpeg_path = false;

		if(!preg_match("/command not found/i", exec("ffmpeg 2>&1"))) {
			$ffmpeg_path = "ffmpeg";
		}
		else if(!preg_match("/command not found/i", exec("/opt/local/bin/ffmpeg 2>&1"))) {
			$ffmpeg_path = "/opt/local/bin/ffmpeg";
		}
		else if(!preg_match("/command not found/i", exec("/usr/local/bin/ffmpeg 2>&1"))) {
			$ffmpeg_path = "/usr/local/bin/ffmpeg";
		}

		return $ffmpeg_path;
	}

}

?>
