<?php
include_once("classes/system/filesystem.class.php");

class Audio {


	function convert($input_file, $output_file, $options = false) {

		$fs = new FileSystem();
		$output_format = false;
		$output_bitrate = 128;

		if($options !== false) {
			foreach($options as $option => $value) {
				switch($option) {
					case "format"      : $output_format = $value; break;
					case "bitrate"     : $output_bitrate = $value; break;
				}
			}
		}

		$ffmpeg_path = $this->ffmpegPath();


//		print "input_format:" . $input_format.", output_format:" . $output_format . "<br>";

		// START CONVERSION

		// keep proportions and input is bigger than output
		if(file_exists($input_file) && $ffmpeg_path) {

			// make sure output path exists
			$fs->makeDirRecursively(dirname($output_file));

			// read input file
			if($output_format == "mp3") {
				// print $ffmpeg_path . " -i ".$input_file." -acodec libmp3lame -ar 48000 -ab ".$output_bitrate."k ".$output_file . "<br>";
				system($ffmpeg_path . " -y -i ".$input_file." -acodec libmp3lame -ar 48000 -ab ".$output_bitrate."k ".$output_file);
			}
			else if($output_format == "ogg") {
				// print "ffmpeg -y -i ".$input_file." -acodec libvorbis -ar 48000 -ab ".$output_bitrate."k ".$output_file . "<br>";
				system($ffmpeg_path . " -y -i ".$input_file." -acodec libvorbis -ar 48000 -ab ".$output_bitrate."k ".$output_file);
			}


			if(file_exists($output_file)) {
				return true;
			}

		}

		global $page;
		$page->mail(array(
			"subject" => "ffmpeg failed", 
			"message" => "Could not output audio file (could be missing codec or filepermissions issue)", 
			"template" => "system"
		));
		return false;

	}


	function info($file) {
		$ffmpeg_path = $this->ffmpegPath();

		if($ffmpeg_path) {
			$video_info = array();

			$command = $ffmpeg_path . " -i " . escapeshellarg($file) . " 2>&1";
			exec($command, $output);

//			print_r($output) . "<br>";

			if(!preg_match("/Duration: (?P<hours>\d{1,3}):(?P<minutes>\d{2}):(?P<seconds>\d{2})(.(?P<fractions>\d{1,3}))?[^S]+Stream #(?P<number>\d+?\:\d+?): (?P<type>.+): (?P<codec>.*), (?P<frequency>.*), (?P<stereo>.*), (?P<module>.*), (?P<bitrate>\d+(\.\d+)?) (?P<bitrateunit>[\w\(\)]+)/", implode("\n", $output), $matches)) {
				if(!preg_match('/Stream #(?:[0-9\.]+)(?:.*)\: Audio: (?P<codec>.*), (?P<frequency>.*), (?P<stereo>.*), (?P<module>.*), (?P<bitrate>\d+(\.\d+)?) (?P<bitrateunit>[\w\(\)]+)/', implode("\n", $output), $matches)) {
					preg_match('/Could not find codec parameters \(Audio: (?P<codec>.*), (?P<frequency>.*), (?P<stereo>.*), (?P<module>.*), (?P<bitrate>\d+(\.\d+)?) (?P<bitrateunit>[\w\(\)]+)/',implode("\n", $output), $matches);
				}
			}

			// detect width and height, or fail
			if(isset($matches["bitrate"])) {

				// duration
				if(isset($matches["hours"]) && isset($matches["minutes"]) && isset($matches["seconds"]) && isset($matches["fractions"])) {
					$video_info["hours"] = $matches["hours"];
					$video_info["minutes"] = $matches["minutes"];
					$video_info["seconds"] = $matches["seconds"];
					$video_info["fractions"] = $matches["fractions"];
					$video_info["duration"] = ($matches["hours"] * 60 * 60 * 1000) + ($matches["minutes"] * 60 * 1000) + ($matches["seconds"] * 1000) + ($matches["fractions"] * 10);
				}

				// additional video info
				// experimental (return bitrate in KB always)
				if(isset($matches["bitrate"]) && isset($matches["bitrateunit"])) {
					if($matches["bitrateunit"] == "kb") {
						$video_info["bitrate"] = $matches["bitrate"];
					}
				}
				if(isset($matches["format"])) {
					$video_info["format"] = $matches["format"];
				}
				if(isset($matches["codec"])) {
					$video_info["codec"] = $matches["codec"];
				}

				return $video_info;
			}

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
