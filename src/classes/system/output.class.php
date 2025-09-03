<?php
/**
* This file contains validation for output functionality
*/
class Output {

	/**
	* Construct reference to data object
	*/
	function __construct() {

		
	}




	/**
	* output object as type
	* default json
	* object can be nested php array
	* - option: type = error - outputs cms_status and cms_message as error. 
	*
	* Reset_messages is pr default true and all messages will be reset. If reset_messages is false, messages will not be reset but will be stored in the object. 
	*/
	function screen($object, $_options = false) {

		$format = "json";
		$type = false;
		$reset_messages = true;

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					case "format"             : $format           = $_value; break;
					case "type"               : $type             = $_value; break;
					case "reset_messages"     : $reset_messages   = $_value; break;

				}
			}
		}

		$output["cms_object"] = $object;

		if($type == "error" || !$object) {
			$output["cms_status"] = "error";
			$output["cms_message"] = message()->getMessages(array("type"=>"error"));
		}
		
		else {
//			print_r(message()->getMessages());
		//if($type == "success") {
			$output["cms_status"] = "success";
//			print_r(message()->getMessages());
			$output["cms_message"] = message()->getMessages();

			// Add possible return_to value
			$output["return_to"] = getPost("return_to");
			// $messages = message()->getMessages();
			// foreach($messages as $type => $message) {
			// 	$object["cms_"] = implode(", ", $message);
			// 	
			// }
			// 	
		}
	
		if($reset_messages) {
			message()->resetMessages();
		}

		// TODO: implement more output methods
		print json_encode($output);


	}

}