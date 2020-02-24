<?php

/**
* Performance class
* Enables performance testing – primarily process time-stamping
*
* TODO:
* Is intended to be extended with more benchmarking details as becomes needed
*/

class Performance {

	private $start_time;
	private $measurements;

	function __construct() {

		$this->start_time = microtime(true);
		$this->measurements = [];

	}

	function add($message) {

		array_push($this->measurements, ["time" => microtime(true), "message" => $message]);

	}


	function dump() {

		// debug([$this->start_time]);

		$progress_time = $this->start_time;
		$_ = '<div class="performance"><pre>';
		foreach($this->measurements as $measurement) {
			// debug([$measurement["message"], $measurement["time"], $progress_time, $this->start_time, round(($measurement["time"] - $this->start_time), 4)]);
			$_ .= number_format(round($measurement["time"] - $this->start_time, 5), 5) . ' (' . number_format(round($measurement["time"] - $progress_time, 5), 5) . '): ' . $measurement["message"] . "\n";
			$progress_time = $measurement["time"];

		}

		$_ .= '</pre></div>';

		print $_;
	}


	function reset() {

		$this->start_time = microtime(true);
		$this->$measurements = [];

	}

}

?>
