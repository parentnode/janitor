<?php

// BETA PDF CLASS
// CURRENTLY ONLY INCLUDED TO TEST wkhtmlto module
class PDF {


	function create($input_file, $output_file, $options = false) {

		$fs = new FileSystem();

		$output_format = "A4";
		$javascript_delay = 1000;

		if($options !== false) {
			foreach($options as $option => $value) {
				switch($option) {
					case "format"            : $output_format         = $value; break;
					case "delay"             : $javascript_delay      = $value; break;
				}
			}
		}

		$wkhtmlto_path = wkhtmltoPath();

//		$command = "$wkhtmlto_path -s $output_format $input_file $output_file";
		$command = "$wkhtmlto_path -s $output_format --javascript-delay $javascript_delay --no-stop-slow-scripts --enable-javascript $input_file $output_file";
		// print "command:".$command."<br>\n";

		// Generate the image
		$output = shell_exec($command." 2>&1");
		// print "output:" . $output."<br>\n";

//		return $output_file;
	}

}

?>
