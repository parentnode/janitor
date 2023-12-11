<?php
/**
* This file contains generel functions available throughout the site
*
* @package Functions
*/


function debug($vars, $output = "print") {
	if(!is_array($vars)) {
		$vars = [$vars];
	}
	foreach($vars as $var) {

		if($output == "file") {
			writeToFile($var);
		}
		else {
			print "<pre>";
			print_r($var);
			print "</pre>";
			print "<br />\n";
		}
		
	}
}


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
		if(mb_detect_encoding($string[$i])) {
			$new_string .= $string[$i];
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
		return array();
	}
	else {
		// get params
		$params = explode("/", preg_replace("/^\/|\/$/", "", $_SERVER["PATH_INFO"]));


		// TODO: Consider introducing an additional check on actions here
		// to ensure not "evil" stuff gets pushed through url
		// EXPERIMENTAL: this method is in test phase
		// will add slashes and check for mysql stuff
		foreach($params as $i => $param) {
			$params[$i] = prepareForDB($param);
		}


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
* Looking for var in $_POST, $_GET
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
		// debug(["getPost($which)", $_POST[$which]]);
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
		else {
			$posts[$name] = false;
		}
	}
	return $posts;
}

// Special case for passwords - do not sanitize posted values for passwords
function getPostPassword($which) {
	if(isset($_POST[$which])) {
		// debug(["getPostPassword($which)", $_POST[$which]]);
		return $_POST[$which];
	}
	else {
		return false;
	}
}


/**
* Prepare variables to be returned to page (because of error or like)
*/
function prepareForHTML($string) {

	// is string an array, then iterate to check all strings within array
	if(is_array($string)) {
		// loop through array
		foreach($string as $key => $array) {
			$string[$key] = prepareForHTML($array);
		}
	}
	// prepare string
	else {
		$string = stripslashes($string);
	}

	return $string;
}


/**
* Prepare Correcting quotes and removes bad HTML tags and attributes
* Recursive function for arrays - actual stripping is handled by stripDisallowed
*
* @param string $string
* @return string
*/
function prepareForDB($string) {

	// is string an array, then iterate to check all strings within array
	if(is_array($string)) {
		// loop through array
		foreach($string as $key => $array) {
			$string[$key] = prepareForDB($array);
		}
	}
	// prepare string
	else {

		global $mysqli_global;
		$string = stripDisallowed($string);
		if($mysqli_global) {
			$string = $mysqli_global->escape_string($string);
		}
		else {
			$string = addslashes($string);
		}
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
	$allowed_tags = '<a><strong><em><sup><h1><h2><h3><h4><h5><h6><p><label><br><hr><ul><ol><li><dd><dl><dt><span><img><div><table><tr><td><th><code>';
	$string = strip_tags($string, $allowed_tags);

	// debug(["stripDisallowed", $string]);

	// only look through attributes if any tags are left after initial sanitizing
	// This time without any allowed tags
	if($string != strip_tags($string)) {

		$dom = DOM()->createDOM($string);
		if($dom) {

			// debug(["START SAVE", DOM()->saveHTML($dom), "END SAVE"]);
			// debug($dom->saveHTML());

			DOM()->stripAttributes($dom);

			// Export HTML and remove any mis-interpreted tags
			$string = strip_tags(trim(DOM()->saveHTML($dom)), $allowed_tags);

		}

	}

	return trim($string);
}


// check if value exists in Janitor content array structure
function arrayKeyValue($array, $key, $value) {
	if($array && is_array($array)) {
		foreach($array as $index => $sub_array) {
			if($sub_array[$key] == $value) {
				return $index;
			}
		}
	}
	return false;
}

/**
* Generate random key
*
* @param Integer $length (Optional) Length of key. Default is 8.
* @return String Random key
*/
function randomKey($length=false) {
	$pattern = "1234567890abcdefghijklmnopqrstuvwxyz";
	$length = $length ? $length : 8;
	$key = '';
	for($i = 0; $i < $length; $i++) {
		$key .= $pattern[rand(0,35)];
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

	$max_length = $max_length - 3;

	// cut string
	$return_string = substr($return_string, 0, $max_length);
	
	// or look for last word-spacing
	if(substr($string, $max_length, 1) !== " " && strrpos($return_string, " ") !== false) {
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
* Normalizes, lowercases and replaces unknown chars with - (hyphen)
*
* @param string $string String to be normalized
* @return normalized string
*/
function superNormalize($string) {
	$string = normalize($string);

	// lowercase
	$string = strtolower($string);

	// strip HTML
	$string = strip_tags($string);

	// remove all remaining specialchars
	$string = preg_replace('/[^a-z0-9\_]/', '-', $string);

	// remove double hyphens
	$string = preg_replace('/-+/', '-', $string);

	// remove leading and trailing underscores
	$string = preg_replace('/^-|-$/', '', $string);
	
	return $string;
}


function _uniord($c) {
    if (ord($c[0]) >=0 && ord($c[0]) <= 127)
        return ord($c[0]);
    if (ord($c[0]) >= 192 && ord($c[0]) <= 223)
        return (ord($c[0])-192)*64 + (ord($c[1])-128);
    if (ord($c[0]) >= 224 && ord($c[0]) <= 239)
        return (ord($c[0])-224)*4096 + (ord($c[1])-128)*64 + (ord($c[2])-128);
    if (ord($c[0]) >= 240 && ord($c[0]) <= 247)
        return (ord($c[0])-240)*262144 + (ord($c[1])-128)*4096 + (ord($c[2])-128)*64 + (ord($c[3])-128);
    if (ord($c[0]) >= 248 && ord($c[0]) <= 251)
        return (ord($c[0])-248)*16777216 + (ord($c[1])-128)*262144 + (ord($c[2])-128)*4096 + (ord($c[3])-128)*64 + (ord($c[4])-128);
    if (ord($c[0]) >= 252 && ord($c[0]) <= 253)
        return (ord($c[0])-252)*1073741824 + (ord($c[1])-128)*16777216 + (ord($c[2])-128)*262144 + (ord($c[3])-128)*4096 + (ord($c[4])-128)*64 + (ord($c[5])-128);
    if (ord($c[0]) >= 254 && ord($c[0]) <= 255)    //  error
        return FALSE;
    return 0;
}
function _unichr($o) {
    if (function_exists('mb_convert_encoding')) {
        return mb_convert_encoding('&#'.intval($o).';', 'UTF-8', 'HTML-ENTITIES');
    } else {
        return chr(intval($o));
    }
}


// POLYFILLS
// included from php72
if(!function_exists('mb_ord')):
	function mb_ord($string) {
		mb_language('Neutral');
		mb_internal_encoding('UTF-8');
		mb_detect_order(array('UTF-8', 'ISO-8859-15', 'ISO-8859-1', 'ASCII'));
		$result = unpack('N', mb_convert_encoding($string, 'UCS-4BE', 'UTF-8'));
		if(is_array($result) === true) {
			return $result[1];
		}
		return ord($string);
	}
endif;

// included from php72
if(!function_exists('mb_chr')):
	function mb_chr($string) {
		mb_language('Neutral');
		mb_internal_encoding('UTF-8');
		mb_detect_order(array('UTF-8', 'ISO-8859-15', 'ISO-8859-1', 'ASCII'));
		return mb_convert_encoding('&#' . intval($string) . ';', 'UTF-8', 'HTML-ENTITIES');
	}
endif;

if(!function_exists('str_starts_with')):
	function str_starts_with($haystack, $needle) {
		return ($needle !== '' && strncmp($haystack, $needle, strlen($needle)) === 0);
	}
endif;



// Emoji handling (unicode out of UTF range)
function decodeEmoji($string, $system) {
	global $__decode_emoji_system;
	$__decode_emoji_system = $system;
	return preg_replace_callback('/([0-9|#][\x{20E3}])|[\x{e022}|\x{00ae}|\x{00a9}|\x{203C}|\x{2047}|\x{2048}|\x{2049}|\x{3030}|\x{303D}|\x{2139}|\x{2122}|\x{3297}|\x{3299}][\x{FE00}-\x{FEFF}]?|[\x{2190}-\x{21FF}][\x{FE00}-\x{FEFF}]?|[\x{2300}-\x{23FF}][\x{FE00}-\x{FEFF}]?|[\x{2460}-\x{24FF}][\x{FE00}-\x{FEFF}]?|[\x{25A0}-\x{25FF}][\x{FE00}-\x{FEFF}]?|[\x{2600}-\x{27BF}][\x{FE00}-\x{FEFF}]?|[\x{2900}-\x{297F}][\x{FE00}-\x{FEFF}]?|[\x{2B00}-\x{2BF0}][\x{FE00}-\x{FEFF}]?|[\x{1F000}-\x{1F6FF}][\x{FE00}-\x{FEFF}]?/u', function($matches) {global $__decode_emoji_system; return "##".$__decode_emoji_system."-EMOJI".mb_ord($matches[0])."EMOJI##";}, $string);

	// simplified version (not covering all icons)
	//return preg_replace_callback("/([^\x{0000}-\x{FFFF}\x]+)/u", function($matches) {return "##EMOJI".mb_ord($matches[1])."EMOJI##";}, $string);
}
function encodeEmoji($string, $system) {
	return preg_replace_callback("/##".$system."-EMOJI([0-9]+)EMOJI##/", function($matches) {return '<span class="emoji sb-'.dechex($matches[1]).'">'.mb_chr($matches[1]).'</span>';}, $string);
}




// get extension for mimetype
function mimetypeToExtension($mimetype) {
	$extensions = array(
		"image/gif" => "gif", 
		"image/jpeg" => "jpg",
		"image/jpg" => "jpg",
		"image/png" => "png",

		"image/avif" => "avif",
		"image/webp" => "webp",

		"audio/mpeg" => "mp3",
		"audio/x-wav" => "wav",
		"audio/x-aac" => "aac",

		"video/mp4" => "mp4",
		"video/quicktime" => "mov",

		"application/pdf" => "pdf",
		"application/zip" => "zip",
		
		"text/plain" => "txt"
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
	$seconds = isset($parts[5]) && $parts[5] ? $parts[5] : 0;
	
	// print $timestamp . " = " . $date ."#" . $month . "#" . $year . "#" . $hours . "#" . $minutes . "#" . $seconds . " => " . date("Y-m-d H:i:s", mktime($hours, $minutes, $seconds, $month, $date, $year)) . "<br>";
	
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
// optionally add currency abbreviation and/or VAT
// decimals can optionally be shown only if they are not zero
function formatPrice($price, $_options=false) {
	global $page;

	$vat = false;
	$currency = true;
	$conditional_decimals = false;

	if($_options !== false) {
		foreach($_options as $_option => $_value) {
			switch($_option) {
				case "vat"                    : $vat                     = $_value; break;
				case "currency"               : $currency                = $_value; break;
				case "conditional_decimals"   : $conditional_decimals    = $_value; break;
			}
		}
	}

	if($price) {

		$_ = '';

		if(isset($price["currency"])) {
			$currency_details = $page->currencies($price["currency"]);
		}
		else {
			$currency_details = $page->currencies(defined("DEFAULT_CURRENCY_ISO") ? DEFAULT_CURRENCY_ISO : "DKK");
		}

		if($conditional_decimals && ctype_digit($price["price"])) {

			// price is an integer; omit decimals
			$formatted_price = number_format($price["price"], 0, $currency_details["decimal_separator"], $currency_details["grouping_separator"]);
		}
		else {

			$formatted_price = number_format($price["price"], $currency_details["decimals"], $currency_details["decimal_separator"], $currency_details["grouping_separator"]);
		}
	
	
		// show currency
		if($currency) {
			if($currency_details["abbreviation_position"] == "after") {
				$_ .= $formatted_price . " " . $currency_details["abbreviation"];
			}
			else {
				$_ .= $currency_details["abbreviation"] . " " . $formatted_price;
			}
		}
		else {
			$_ .= $formatted_price;
		}
	
		if($vat) {
			$_ .= " (".number_format($price["vat"], $currency_details["decimals"], $currency_details["decimal_separator"], $currency_details["grouping_separator"]).")";
		}
	
		return $_;
	}

	return false;

}

// Sessions open and close helpers
function sessionStart() {
	session_start();

	// Fix multiple Set Cookie headers from session_start
	fixDuplicateCookieHeaders();
}
function fixDuplicateCookieHeaders() {

	// Only if headers have not already been sent
	if(!headers_sent()) {

		$cookies = [];
		$headers = headers_list();

		// Loop through headers and collect cookies
		foreach($headers as $header) {
			// Find cookie headers
			if(str_starts_with($header, "Set-Cookie:")) {
				$cookies[] = $header;
			}
		}

		// Remove all cookie headers, including duplicates
		header_remove("Set-Cookie");

		// Restore one copy of each cookie
		$unique_cookies = array_unique($cookies);
		foreach($unique_cookies as $cookie) {
			header($cookie, false);
		}

	}

}
function sessionEnd($source = false) {
	session_write_close();
}


// Identify ffmpeg path (differs in different systems/installs)
function ffmpegPath() {

	$ffmpeg_path = cache()->value("ffmpeg_path");

	if(!$ffmpeg_path) {

		// Mac path
		if(!preg_match("/(No such file or directory|not found|not recognized|cannot find|ikke fundet)/i", shell_exec("/opt/local/bin/ffmpeg 2>&1"))) {
			$ffmpeg_path = "/opt/local/bin/ffmpeg";
		}
		// Linux path
		else if(!preg_match("/(No such file or directory|not found|not recognized|cannot find|ikke fundet)/i", shell_exec("/usr/local/bin/ffmpeg 2>&1"))) {
			$ffmpeg_path = "/usr/local/bin/ffmpeg";
		}
		// Windows path
		else if(!preg_match("/(No such file or directory|not found|not recognized|cannot find|ikke fundet)/i", shell_exec("/srv/installed-packages/ffmpeg/bin/ffmpeg 2>&1"))) {
			$ffmpeg_path = "/srv/installed-packages/ffmpeg/bin/ffmpeg";
		}

		// Alternative paths
		else if(!preg_match("/(No such file or directory|not found|not recognized|cannot find|ikke fundet)/i", shell_exec("/usr/bin/ffmpeg 2>&1"))) {
			$ffmpeg_path = "/usr/bin/ffmpeg";
		}
		else if(!preg_match("/(No such file or directory|not found|not recognized|cannot find|ikke fundet)/i", shell_exec("ffmpeg 2>&1"))) {
			$ffmpeg_path = "ffmpeg";
		}

		cache()->value("ffmpeg_path", $ffmpeg_path);
	}

	return $ffmpeg_path;
}

// Identify ffmpeg AAC codec (differs in different systems/installs)
function ffmpegAACCodec() {

	$ffmpeg_aac = cache()->value("ffmpeg_aac");

	if(!$ffmpeg_aac) {

		$ffmpeg_path = ffmpegPath();
		if(preg_match("/A\.\.\.\.\. libfdk_aac/i", shell_exec($ffmpeg_path . " -encoders 2>&1"))) {
			$ffmpeg_aac = "libfdk_aac";
		}
		else if(preg_match("/A\.\.\.\.\. aac/i", shell_exec($ffmpeg_path . " -encoders 2>&1"))) {
			$ffmpeg_aac = "aac";
		}
		else if(preg_match("/A\.\.\.\.\. libfaac/i", shell_exec($ffmpeg_path . " -encoders 2>&1"))) {
			$ffmpeg_aac = "libfaac";
		}

		cache()->value("ffmpeg_aac", $ffmpeg_aac);
	}

	return $ffmpeg_aac;
}



// Identify wkhtmlto path (differs in different systems/installs)
function wkhtmltoPath() {

	$wkhtmlto_path = cache()->value("wkhtmlto_path");
//	print $wkhtmlto_path;
	if(!$wkhtmlto_path) {


		# Current MAC / Linux
		if(!preg_match("/(No such file or directory|not found|not recognized|cannot find|ikke fundet)/i", shell_exec("/srv/tools/bin/wkhtmltopdf 2>&1"))) {
			$wkhtmlto_path = "/srv/tools/bin/wkhtmltopdf";
		}
		# Current Windows
		else if(!preg_match("/(No such file or directory|not found|not recognized|cannot find|ikke fundet)/i", shell_exec("/srv/installed-packages/wkhtmltopdf/bin/wkhtmltopdf.exe 2>&1"))) {
			$wkhtmlto_path = "/srv/installed-packages/wkhtmltopdf/bin/wkhtmltopdf.exe";
		}


		# Older versions MAC / Linux
		else if(!preg_match("/(No such file or directory|not found|not recognized|cannot find|ikke fundet)/i", shell_exec("static_wkhtmltopdf 2>&1"))) {
			$wkhtmlto_path = "static_wkhtmltopdf";
		}
		else if(!preg_match("/(No such file or directory|not found|not recognized|cannot find|ikke fundet)/i", shell_exec("/usr/bin/static_wkhtmltopdf 2>&1"))) {
			$wkhtmlto_path = "/usr/bin/static_wkhtmltopdf";
		}
		else if(!preg_match("/(No such file or directory|not found|not recognized|cannot find|ikke fundet)/i", shell_exec("/usr/local/bin/static_wkhtmltopdf 2>&1"))) {
			$wkhtmlto_path = "/usr/local/bin/static_wkhtmltopdf";
		}
		else if(!preg_match("/(No such file or directory|not found|not recognized|cannot find|ikke fundet)/i", shell_exec("/usr/bin/wkhtmltopdf 2>&1"))) {
			$wkhtmlto_path = "/usr/bin/wkhtmltopdf";
		}
		else if(!preg_match("/(No such file or directory|not found|not recognized|cannot find|ikke fundet)/i", shell_exec("/usr/local/bin/wkhtmltopdf 2>&1"))) {
			$wkhtmlto_path = "/usr/local/bin/wkhtmltopdf";
		}
		else if(!preg_match("/(No such file or directory|not found|not recognized|cannot find|ikke fundet)/i", shell_exec("wkhtmltopdf 2>&1"))) {
			$wkhtmlto_path = "wkhtmltopdf";
		}


		cache()->value("wkhtmlto_path", $wkhtmlto_path);

	}
	return $wkhtmlto_path;
}


// Enable dynamic message texts
function translate($id, $variables = false) {
	global $__translations;
	
	if(isset($__translations[$id])) {

		$string = $__translations[$id];

		if($variables) {
			foreach($variables as $key => $value) {
				$string = str_replace("{".$key."}", $value, $string);
			}
		}

		return $string;
	}

	// If translation does not exist, replace variables in string id
	if($variables) {
		foreach($variables as $key => $value) {
			$id = str_replace("{".$key."}", $value, $id);
		}
	}

	return $id;
}

// Shorthand auto initializer for mailer access
$__mail = false;
function mailer() {
	global $__mail;
	if(!$__mail) {
		include_once("classes/helpers/mailer.class.php");
		$__mail = new MailGateway();
	}
	return $__mail;
}


// Shorthand auto initializer for DOM
$__dom = false;
function DOM() {
	global $__dom;
	if(!$__dom) {
		include_once("classes/helpers/dom.class.php");
		$__dom = new DOM();
	}
	return $__dom;
}


// Shorthand auto initializer for payment access
$__pay = false;
function payments() {
	global $__pay;
	if(!$__pay) {
		include_once("classes/helpers/payments.class.php");
		$__pay = new PaymentGateway();
	}
	return $__pay;
}

// Shorthand auto initializer for qr code generator access
$__qr = false;
function qr_codes() {
	global $__qr;
	if(!$__qr) {
		include_once("classes/helpers/qr_codes.class.php");
		$__qr = new QrCodesGateway();
	}
	return $__qr;
}

// Shorthand auto initializer for sms gateway access
$__sms = false;
function sms() {
	global $__sms;
	if(!$__sms) {
		include_once("classes/helpers/sms.class.php");
		$__sms = new SMSGateway();
	}
	return $__sms;
}

// curl
$__curl = false;
function curl() {
	global $__curl;
	if(!$__curl) {
		include_once("classes/helpers/curl.class.php");
		$__curl = new CurlRequest();
	}
	return $__curl;
}

// Shorthand auto initializer for performance access
$__perf = false;
function perf() {
	global $__perf;
	if(!$__perf) {
		include_once("classes/helpers/performance.class.php");
		$__perf = new Performance();
	}
	return $__perf;
}

// Shorthand auto initializer for security access
$__security = false;
function security() {
	global $__security;
	if(!$__security) {
		include_once("classes/system/security.class.php");
		$__security = new Security();
	}
	return $__security;
}

// Shorthand auto initializer for log access
$__logger = false;
function logger() {
	global $__logger;
	if(!$__logger) {
		include_once("classes/system/log.class.php");
		$__logger = new Log();
	}
	return $__logger;
}

// Shorthand auto initializer for message access
$__message = false;
function message() {
	global $__message;
	if(!$__message) {
		include_once("classes/system/message.class.php");
		$__message = new Message();
	}
	return $__message;
}

// Shorthand auto initializer for session access
$__session = false;
function session() {
	global $__session;
	if(!$__session) {
		include_once("classes/system/session.class.php");
		$__session = new Session();
	}
	return $__session;
}


// Shorthand auto initializer for cache access
$__cache = false;
function cache() {
	global $__cache;
	if(!$__cache) {
		include_once("classes/system/cache.class.php");
		$__cache = new Cache();
	}
	return $__cache;
}



/**
* Converts dd:mm:yyyy hh:mm to yyyy:mm:dd hh:mm:ss
*/
// function mTimestamp($timestamp) {
// 	list($date, $time) = explode(" ", $timestamp);
// 	list($date, $month, $year) = explode('-', $date);
// 	list($hours, $minutes) = explode(':', $time);
//
// 	return date("Y-m-d H:i:s", mktime($hours, $minutes, 0, $month, $date, $year));
// }

?>