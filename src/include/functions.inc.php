<?php
/**
* This file contains generel functions available throughout the site
*
* @package Functions
*/

/**
* Include additional functions
*/
//include_once("functions_arrays.inc.php");
//include_once("functions_files.inc.php");

/**
* used by zip
*/

function utf8Encode($string) {
	$new_string = '';	
	for($i = 0; $i < strlen($string); $i++){ 
		//print "$i: ".$string{$i}.": ".mb_detect_encoding($string{$i});
		if(mb_detect_encoding($string{$i})) {
			$new_string .= $string{$i};
		}
	}
	$encoding = mb_detect_encoding($new_string);
	if($encoding != "UTF-8") {
		//$string = utf8_encode($new_string);
		$string = mb_convert_encoding($new_string, "UTF-8", $encoding);
	}
	return $string;	
}

/**
* Parse REST parameters from url
* returns array if no index is specified
* returns string if index is specified
* returns false if parameters (or specified index) does not exist
* 
* @param int $index Optional parameter index to return
* @return boolean|array|string
*/
function RESTParams($index=false) {
	// no path
	if(!isset($_SERVER["PATH_INFO"]) || $_SERVER["PATH_INFO"] == "/") {
		// TODO:Check if this works everywhere 

		// EXPERIMENTAL: Testing if returning empty array will suffice
//		return false;
		return array();
	}
	else {
		// get params
		$params = explode("/", substr($_SERVER["PATH_INFO"], 1));
		if($index !== false && isset($params[$index])) {
			return $params[$index];
		}
		else {
			return $params;
		}
	}
	return false;
}

/**
* Get a variable which
* Looking for var in $_SESSION, $_POST, $_GET
*
* @param string $which
* @return string|false
* @uses prepareForDB
*/
function getVar($which) {
	if(isset($_POST[$which])) {
		return prepareForDB($_POST[$which]);
	}
	else if(isset($_GET[$which])) {
		return prepareForDB($_GET[$which]);
	}
	else {
		return false;
	}
}


/**
* Get a variable which
*
* @param string $which
* @return string|false
* @uses prepareForDB
*/
function getPost($which) {
	if(isset($_POST[$which])) {
		return prepareForDB($_POST[$which]);
	}
	else {
		return false;
	}
}


function getPosts($which) {
	$posts = array();
	foreach($which as $name) {
		if(isset($_POST[$name])) {
			$posts[$name] = prepareForDB($_POST[$name]);
		}
	}
	return $posts;
}




/**
* Prepare variables to be returned to page (because of error or like)
*/
function prepareForHTML($string) {

	if(is_array($string)) {
		// loop through array
		foreach($string as $key => $array) {
			// if(is_array($array) && isset($array["value"])) {
			// 	$array["value"] = stripslashes($array["value"]);
			// 	$string[$key] = $array;
			// }
			// else {
				$array = stripslashes($array);
				$string[$key] = $array;
//			}
		}
	}
	else {
		$string = stripslashes($string);
	}
	return $string;
}

/**
* Prepare Correcting quotes and removes bad HTML tags and attributes
*
* @param string $string
* @return string
*/
function prepareForDB($string) {

	if(is_array($string)) {
		// loop through array
		foreach($string as $key => $array) {
			// if(is_array($array) && isset($array["value"])) {
			// 	$array["value"] = stripDisallowed($array["value"]);
			// 	$array["value"] = mysql_real_escape_string($array["value"]);
			// 	$string[$key] = $array;
			// }
			// else {
				$array = stripDisallowed($array);
				$array = mysql_real_escape_string($array);
				$string[$key] = $array;
//			}
		}
	}
	else {
		$string = stripDisallowed($string);
		$string = mysql_real_escape_string($string);
	}
	return $string;
}

/**
* Stripping string for unsafe elements, HTML and attributes
*
* @param string $string
* @return string
*/

function stripDisallowed($string) {
	// strip tags
	$allowed_tags = '<a><strong><em><h1><h2><h3><h4><h5><h6><p><label><br><hr><ul><li><dd><dl><dt><span><img><div><table><tr><td><th>';
	$string = strip_tags($string, $allowed_tags);

	// only look through attributes if any tags left
	if($string != strip_tags($string)) {

//		print "\nA:".$string."<br>";
		// create dom from string
		$dom = new DOMDocument('1.0', 'UTF-8');

		// some weird <br> issue in PHP DOM
		// I cannot load document with <br> tags and when I save HTML it automatically replaces all <br /> with <br> which I then again cannot load.
		$string = htmlspecialchars(preg_replace("/<br>/", "<br />", $string), 32, "UTF-8", false);

// 		print htmlentities($string) ."<br>";

		// loadHTML needs content definition for UTF-8 - it should be enough to state it in the constructor, but it does not work
		if($dom->loadHTML('<head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8" /></head><body>'.$string.'</body>')) {

			$nodes = $dom->getElementsByTagName('*');

			// loop nodes
			foreach($nodes as $node) {

				// remember what to remove and remove in the end of each iteration as removing alters the node and thus the loop
				$remove_attributes = array();

				// loop attributes
				foreach($node->attributes as $attribute => $attribute_node) {

					// check for allowed attribute
					if(preg_match("/href|class|width|height|alt/i", $attribute)) {

						// if href, only allow absolute http links (no javascript or other crap)
						if($attribute == "href" && strpos($attribute_node->value, "http://") !== 0) {
							$remove_attributes[] = $attribute;
						}
					}
					else {
						$remove_attributes[] = $attribute;
					}
				}
				// remove identified attributes
				foreach($remove_attributes as $remove_attribute) {
					$node->removeAttribute($remove_attribute);
				}
			}
			
			// remove <content> dummy tag and <br> to <br /> conversion
			$string = preg_replace("/<br>/", "<br />", strip_tags(trim($dom->saveHTML()), $allowed_tags));
//			$string = $dom->saveXML();

		}

		// saveHTML encodes entities
		$string = html_entity_decode($string, ENT_QUOTES, "UTF-8");
//		print "\nB:".$string."<br>";

	}

	return trim($string);
}







/**
* Generate ramdom key
*
* @param Integer $length (Optional) Length of key. Default is 8.
* @return String Random key
*/
function randomKey($length=false) {
	$pattern = "1234567890abcdefghijklmnopqrstuvwxyz";
	$length = $length ? $length : 8;
	$key = '';
	for($i = 0; $i < $length; $i++) {
		$key .= $pattern{rand(0,35)};
	}
	return $key;
}

/**
* Generate valid uuid v4
*
*/
function gen_uuid() {
	return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
		// 32 bits for "time_low"
		mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),

		// 16 bits for "time_mid"
		mt_rand( 0, 0xffff ),

		// 16 bits for "time_hi_and_version",
		// four most significant bits holds version number 4
		mt_rand( 0, 0x0fff ) | 0x4000,

		// 16 bits, 8 bits for "clk_seq_hi_res",
		// 8 bits for "clk_seq_low",
		// two most significant bits holds zero and one for variant DCE1.1
		mt_rand( 0, 0x3fff ) | 0x8000,

		// 48 bits for "node"
		mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
	);
}

/**
* String or ?
* Returns $string if $string is valid or $or (default "-")
*
* @param string $string String to check
* @param string $or Optional alternative return value
* @return string $string or $or
*/
function stringOr($string, $or=false) {
	return (isset($string) && $string !== false  && $string !== "") ? $string : $or;
}

/**
* Uppercase first letter of each word
* Shorthand multibyte ucwords function because ucwords does not support multibyte strings and mb_string does not have a ucwords
*
* @param String $string String to perform ucwords on
* @return String $string with first letter of each word in uppercase
*/
function mb_ucwords($string) {
	return mb_convert_case($string, MB_CASE_TITLE, "UTF-8");
}

/**
* Parses string and returns contained Integer
* Similar to Javascript:parseInt
*
* @param String $string String to parse
* @return Integer|false
*/
function parseInt($string) {
	preg_match_all('/\d+?\d*/', $string, $matches);
	//print_r($matches);
	return (isset($matches[0]) && isset($matches[0][0]) && is_numeric($matches[0][0])) ? $matches[0][0] : false;
}

/**
* Parses string and returns contained Float
* Similar to Javascript:parseFloat
*
* @param String $string String to parse
* @return Float|false
*/
function parseFloat($string) {
	preg_match_all('/\d+\.?\d*/', $string, $matches);
	return (isset($matches[0]) && isset($matches[0][0]) && is_numeric($matches[0][0])) ? $matches[0][0] : false;
}

/**
* Formats comma-based $float as point-based amount, with two decimals
*
* @param Float $float Float to format
* @return Float Wellformed as amount
*/
function toPointFloat($comma_float) {
	$float = str_replace(".", "", $comma_float);
	$float = str_replace(",", ".", $float);
	$point_float = round($float, 2);
	return $point_float;
}

/**
* Formats point-based $float as comma-based amount, with two decimals
*
* @param Float $float Float to format
* @return Float Wellformed as amount
*/
function toCommaFloat($point_float) {
	$comma_float = number_format($point_float, 2, ",", ".");
	return $comma_float;
}

/**
* Cut string nicely to max length, looking for newline or last word-spacing
*/
function cutString($string, $max_length) {

	$return_string = trim(strip_tags($string));

	// return by newline?
	if(strpos($return_string, "\n") !== false && strpos($return_string, "\n") < $max_length) {
		return substr($return_string, 0, strpos($return_string, "\n"));
	}

	// less than max, return it
	if(strlen($return_string) <= $max_length) {
		return $return_string;
	}

	// cut string
	$return_string = substr($return_string, 0, $max_length);
	
	// or look for last word-spacing
	if(strrpos($return_string, " ") !== false) {
		return substr($return_string, 0, strrpos($return_string, " ")) . "...";
	}

	// just cut it ...
	return $return_string . "...";
}


/**
* Normalize string, replace known specialchars with a-z equivalent
*
* @param string $string String to be normalized
* @return normalized string
*/
function normalize($string) {
	$table = array(
		'À'=>'A',  'à'=>'a',
		'Á'=>'A',  'á'=>'a', 
		'Â'=>'A',  'â'=>'a', 
		'Ã'=>'A',  'ã'=>'a', 
		'Ä'=>'A',  'ä'=>'a', 
		'Å'=>'Aa', 'å'=>'aa',
		'Æ'=>'Ae', 'æ'=>'ae',

		'Ç'=>'C',  'ç'=>'c',
		'Č'=>'C',  'ć'=>'c',
		'Ć'=>'C',  'č'=>'c',

		'Đ'=>'D',  'đ'=>'d',  'ð'=>'d',

  		'È'=>'E',  'è'=>'e',
		'É'=>'E',  'é'=>'e',
		'Ê'=>'E',  'ê'=>'e',
		'Ë'=>'E',  'ë'=>'e',

		'Ģ'=>'G',  'ģ'=>'g',
		'Ğ'=>'G',  'ğ'=>'g',

		'Ì'=>'I',  'ì'=>'i', 
		'Í'=>'I',  'í'=>'i',
		'Î'=>'I',  'î'=>'i',
		'Ï'=>'I',  'ï'=>'i',
		'Ī'=>'I',  'ī'=>'i',

		'Ķ'=>'K',  'ķ'=>'k',
		'Ļ'=>'L',  'ļ'=>'l',

		'Ñ'=>'N',  'ñ'=>'n',
		'Ņ'=>'N',  'ņ'=>'n',

		'Ò'=>'O',  'ò'=>'o', 
		'Ó'=>'O',  'ó'=>'o',
		'Ô'=>'O',  'ô'=>'o', 
		'Õ'=>'O',  'õ'=>'o', 
		'Ö'=>'O',  'ö'=>'o', 
		'Ō'=>'O',  'ō'=>'o', 
		'Ø'=>'Oe', 'ø'=>'oe',

		'Ŕ'=>'R',  'ŕ'=>'r',
		'Š'=>'S',  'š'=>'s',
		'Ş'=>'S',  'ş'=>'s',
		'Ṩ'=>'S',  'ṩ'=>'s',

		'Ù'=>'U',  'ù'=>'u',
		'Ú'=>'U',  'ú'=>'u',
		'Û'=>'U',  'û'=>'u',
		'Ü'=>'U',  'ü'=>'u',
		'Ū'=>'U',  'ū'=>'u',
		'Ų'=>'U',  'ų'=>'u',
		'Ŭ'=>'U',  'ŭ'=>'u',

		'Ý'=>'Y',  'ý'=>'y',
		'Ÿ'=>'Y',  'ÿ'=>'y',

		'Ž'=>'Z',  'ž'=>'z',

		'Þ'=>'B',  'þ'=>'b',

		'ß'=>'Ss',
		'@'=>' at ',
		'$'=>'USD',
		'¥'=>'JPY',
		'€'=>'EUR',
		'£'=>'GBP',
		'™'=>'trademark',
		'©'=>'copyright',
		'§'=>'s',
		'*'=>'x',
		'×'=>'x',
	);
	return strtr($string, $table);
}

/**
* Super normalizer
* Normalizes, lowercases and replaces unknown chars with _
*
* @param string $string String to be normalized
* @return normalized string
*/
function superNormalize($string) {
	$string = normalize($string);

	// lowercase
	$string = strtolower($string);

	// remove all remaining specialchars
	$string = preg_replace('/[^a-z0-9\-]/', '_', $string);

	// remove double underscores
	$string = preg_replace('/_+/', '_', $string);
	
	return $string;
}

// get extension for mimetype
function mimetypeToExtension($mimetype) {
	$extensions = array(
		"image/gif" => "gif", 
		"image/jpeg" => "jpg", 
		"image/png" => "png"
	);

	if(isset($extensions[$mimetype])) {
		return $extensions[$mimetype];
	}
	
	return false;
}


function toTimestamp($timestamp) {

	// $timestamp = $timestamp ? $timestamp : date("Y-n-j G-i-s");
	// print $timestamp;

	$parts = explode('-', preg_replace("/[\/\.\-\s\:]+/", '-', $timestamp));

	$year = isset($parts[0]) && $parts[0] ? $parts[0] : date("Y", time());
	$month = isset($parts[1]) && $parts[1] ? $parts[1] : date("n", time());
	$date = isset($parts[2]) && $parts[2] ? $parts[2] : date("j", time());

	$hours = isset($parts[3]) && $parts[3] ? $parts[3] : date("G", time());
	$minutes = isset($parts[4]) && $parts[4] ? $parts[4] : date("i", time());
	$seconds = isset($parts[5]) && $parts[5] ? $parts[5] : date("s", time());
	
//	print $date ."#" . $month . "#" . $year . "#" . $hours . "#" . $minutes . "#" . $seconds . "<br>";
	
	return date("Y-m-d H:i:s", mktime($hours, $minutes, $seconds, $month, $date, $year));
}


// select correct form, based on $count
function pluralize($count, $singular, $plural) {
	if($count != 1) {
		return $count . " " . $plural;
	}
	
	return $count . " " . $singular;
}


// price formatting - uses $page and currency db-table for information
function formatPrice($price, $currency = false) {
	global $page;

//	print "formatPrice currency".$currency;
	$default_currency = $page->currency();
//	print_r($default_currency);
//	print "default_decimals:" . $default_currency["decimals"];

	if(!$currency || (is_string($currency) && $currency == $default_currency["id"])) {
		$currency = $default_currency;
	}
//	print_r($default_currency);
	else if(is_string($currency)) {
		$query = new Query();
//		print "SELECT * FROM ".UT_CURRENCIES." WHERE id = '".$currency."'";
		if($query->sql("SELECT * FROM ".UT_CURRENCIES." WHERE id = '".$currency."'")) {
			$currency = $query->result(0);
		}
	}

	// // $currency_id = $currency_id ? $currency_id : 
	// // print "currency_id 2".$currency_id;
	// 
	// $query = new Query();
	// print "SELECT * FROM ".UT_CURRENCIES." WHERE id = '".$currency_id."'";
	// if($query->sql("SELECT * FROM ".UT_CURRENCIES." WHERE id = '".$currency_id."'")) {
	// 	print "true";
	// 	$currency = $query->result(0);
//		print_r($currency);
//		print "decimals:" . $currency["decimals"];
		$formatted_price = ($currency["abbreviation_position"] == "before" ? $currency["abbreviation"]." " : "");
		$formatted_price .= number_format($price, parseInt($currency["decimals"]), $currency["decimal_separator"], $currency["grouping_separator"]);
		$formatted_price .= ($currency["abbreviation_position"] == "after" ? " ".$currency["abbreviation"] : "");

//		print $formatted_price;
		return $formatted_price;
	// }
	// 
	// return $price;
}


// TODO: maybe send only Currency ISO and look up the rest
// price formatting - uses internal $price object for information
// function formatPrice($price, $currency) {
// 	return ($currency["abbreviation_position"] == "before" ? $currency["abbreviation"]." " : "") . number_format($price, $currency["decimals"], $currency["decimal_separator"], $currency["grouping_separator"]) . ($currency["abbreviation_position"] == "after" ? " ".$currency["abbreviation"] : "");
// }
// ;
// $prices[$index]["formatted_with_vat"] = ($price["abbreviation_position"] == "before" ? $price["abbreviation"]." " : "") . number_format($price["price"]* (1 + ($price["vatrate"]/100)), $price["decimals"], $price["decimal_separator"], $price["grouping_separator"]) . ($price["abbreviation_position"] == "after" ? " ".$price["abbreviation"] : "");
// $prices[$index]["price_with_vat"] = number_format($price["price"]* (1 + ($price["vatrate"]/100)), $price["decimals"], $price["decimal_separator"], $price["grouping_separator"]);




/**
* Converts dd:mm:yyyy hh:mm to yyyy:mm:dd hh:mm:ss
*/
function mTimestamp($timestamp) {
	list($date, $time) = explode(" ", $timestamp);
	list($date, $month, $year) = explode('-', $date);
	list($hours, $minutes) = explode(':', $time);

	return date("Y-m-d H:i:s", mktime($hours, $minutes, 0, $month, $date, $year));
}

?>