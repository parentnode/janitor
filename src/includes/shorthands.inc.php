<?php


// Shorthand auto initializer for email access
$__email = false;
function email() {
	global $__email;
	if(!$__email) {
		include_once("classes/helpers/email.class.php");
		$__email = new EmailGateway();
	}
	return $__email;
}
// Deprecated, keep until it has been cleaned out
function mailer() {
	return email();
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
$__payment = false;
function payment() {
	global $__payment;
	if(!$__payment) {
		include_once("classes/helpers/payment.class.php");
		$__payment = new PaymentGateway();
	}
	return $__payment;
}
// Deprecated, keep until it has been cleaned out
function payments() {
	return payment();
}

// Shorthand auto initializer for qr code generator access
$__qrcode = false;
function qrcode() {
	global $__qrcode;
	if(!$__qrcode) {
		include_once("classes/helpers/qr_codes.class.php");
		$__qrcode = new QrCodesGateway();
	}
	return $__qrcode;
}
// Deprecated, keep until it has been cleaned out
function qr_codes() {
	return qrcode();
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

// Shorthand auto initializer for instantmessage gateway access
$__instantmessage = false;
function instantmessage() {
	global $__instantmessage;
	if(!$__instantmessage) {
		include_once("classes/helpers/instantmessage.class.php");
		$__instantmessage = new InstantMessageGateway();
	}
	return $__instantmessage;
}

// Shorthand auto initializer for fraudprotection gateway access
$__fraudprotection = false;
function fraudprotection() {
	global $__fraudprotection;
	if(!$__fraudprotection) {
		include_once("classes/helpers/fraudprotection.class.php");
		$__fraudprotection = new FraudProtectionGateway();
	}
	return $__fraudprotection;
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



// SYSTEM MODULES

// testing these for ease of use
function page() {
	global $page;
	return $page;
}

function HTML() {
	global $HTML;
	return $HTML;
}

$__module = false;
function module() {
	global $__module;
	if(!$__module) {
		include_once("classes/system/module.class.php");
		$__module = new Module();
	}
	return $__module;
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

// Shorthand auto initializer for admin access
$__admin = false;
function admin() {
	global $__admin;
	if(!$__admin) {
		include_once("classes/system/admin.class.php");
		$__admin = new Admin();
	}
	return $__admin;
}

// Shorthand auto initializer for Image class access
$__image = false;
function image() {
	global $__image;
	if(!$__image) {
		include_once("classes/helpers/image.class.php");
		$__image = new Image();
	}
	return $__image;
}
// Shorthand auto initializer for Video class access
$__video = false;
function video() {
	global $__video;
	if(!$__video) {
		include_once("classes/helpers/video.class.php");
		$__video = new Video();
	}
	return $__video;
}
// Shorthand auto initializer for Audio class access
$__audio = false;
function audio() {
	global $__audio;
	if(!$__audio) {
		include_once("classes/helpers/audio.class.php");
		$__audio = new Audio();
	}
	return $__audio;
}

