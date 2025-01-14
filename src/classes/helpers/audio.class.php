<?php
include_once("classes/helpers/filesystem.class.php");

class Audio {


	function convert($input_file, $output_file, $_options = false) {

		$fs = new FileSystem();
		$output_format = false;
		$output_bitrate = 128;

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					case "format"      : $output_format = $_value; break;
					case "bitrate"     : $output_bitrate = $_value; break;
				}
			}
		}

		$ffmpeg_path = ffmpegPath();


//		print "input_format:" . $input_format.", output_format:" . $output_format . "<br>";

		// START CONVERSION

		// keep proportions and input is bigger than output
		if(file_exists($input_file) && $ffmpeg_path) {

			// make sure output path exists
			$fs->makeDirRecursively(dirname($output_file));

			// read input file
			if($output_format == "mp3") {
				$command = $ffmpeg_path . " -y -i ".$input_file." -c:a libmp3lame -ar 48000 -b:a ".$output_bitrate."k ".$output_file;
			}
			else if($output_format == "ogg") {
				$command = $ffmpeg_path . " -y -i ".$input_file." -c:a libvorbis -ar 48000 -b:a ".$output_bitrate."k ".$output_file;
			}
			// else if($output_format == "wav") {
			// 	$command = $ffmpeg_path . " -y -i ".$input_file." -c:a pcm_s32le ".$output_file;
			// }

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

		notify()->send(array(
			"subject" => "ffmpeg failed", 
			"message" => "Could not output audio file (could be missing codec or filepermissions issue)", 
			"template" => "system"
		));
		return false;

	}


	function info($file) {
		$audio_info = array();

		$ffmpeg_path = ffmpegPath();

		if($ffmpeg_path) {

			$command = $ffmpeg_path . " -i " . escapeshellarg($file) . " 2>&1";
			exec($command, $output);

			// print_r($output) . "<br>";

			if(!preg_match("/Duration: (?P<hours>\d{1,3}):(?P<minutes>\d{2}):(?P<seconds>\d{2})(.(?P<fractions>\d{1,3}))?[^S]+Stream #(?P<number>\d+?\:\d+?)(?P<lan>[\(\)a-z]*): (?P<type>.+): (?P<codec>.*), (?P<frequency>.*), (?P<stereo>.*), (?P<module>.*), (?P<bitrate>\d+(\.\d+)?) (?P<bitrateunit>[\w\(\)]+)/", implode("\n", $output), $matches)) {
				if(!preg_match('/Stream #(?:[0-9\.]+)(?:.*)\: Audio: (?P<codec>.*), (?P<frequency>.*), (?P<stereo>.*), (?P<module>.*), (?P<bitrate>\d+(\.\d+)?) (?P<bitrateunit>[\w\(\)]+)/', implode("\n", $output), $matches)) {
					preg_match('/Could not find codec parameters \(Audio: (?P<codec>.*), (?P<frequency>.*), (?P<stereo>.*), (?P<module>.*), (?P<bitrate>\d+(\.\d+)?) (?P<bitrateunit>[\w\(\)]+)/',implode("\n", $output), $matches);
				}
			}

			// detect bitrate, or fail
			if(isset($matches["bitrate"])) {

				// get filesize
				$audio_info["filesize"] = filesize($file);

				// duration
				if(isset($matches["hours"]) && isset($matches["minutes"]) && isset($matches["seconds"]) && isset($matches["fractions"])) {
					$audio_info["hours"] = parseInt($matches["hours"]);
					$audio_info["minutes"] = parseInt($matches["minutes"]);
					$audio_info["seconds"] = parseInt($matches["seconds"]);
					$audio_info["fractions"] = parseFloat("0.".$matches["fractions"])*1000;

					$audio_info["duration"] = ($audio_info["hours"] * 60 * 60 * 1000) + ($audio_info["minutes"] * 60 * 1000) + ($audio_info["seconds"] * 1000) + $audio_info["fractions"];
				}

				// additional video info
				// experimental (return bitrate in KB always)
				if(isset($matches["bitrate"]) && isset($matches["bitrateunit"])) {
					if($matches["bitrateunit"] == "kb") {
						$audio_info["bitrate"] = $matches["bitrate"];
					}
				}
				if(isset($matches["format"])) {
					$audio_info["format"] = $matches["format"];
				}
				if(isset($matches["codec"])) {
					$audio_info["codec"] = $matches["codec"];
				}

			}

		}

		return $audio_info;
	}


}

?>
