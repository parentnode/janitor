<?php

/**
* PDF generation helper class
*
* Enables PDF generation based on WKHTMLTO module
*/

class PDF {


	function create($input_file, $output_file, $options = false) {

		$fs = new FileSystem();

		$output_format = "A4";
		$javascript_delay = 1000;
		$orientation = "portrait";

		$cookie = false;

		if($options !== false) {
			foreach($options as $option => $value) {
				switch($option) {
					case "format"            : $output_format         = $value; break;
					case "delay"             : $javascript_delay      = $value; break;
					case "orientation"       : $orientation           = $value; break;

					case "cookie"            : $cookie      = $value; break;
				}
			}
		}

		$wkhtmlto_path = wkhtmltoPath();

//		$command = "$wkhtmlto_path -s $output_format $input_file $output_file";
		$command = "$wkhtmlto_path";
		$command .= " -s $output_format";
		$command .= " -O $orientation";
		$command .= " --javascript-delay $javascript_delay";
		$command .= " --no-stop-slow-scripts";
		$command .= " --enable-javascript";
		if($cookie) {
			$command .= " --cookie " . $cookie["name"] . " " . $cookie["value"];
		}
		$command .= " $input_file $output_file";
		// print "command:".$command."<br>\n";

		// Generate the image
		$output = shell_exec($command." 2>&1");
		// print "output:" . $output."<br>\n";

//		return $output_file;
	}

}

?>
