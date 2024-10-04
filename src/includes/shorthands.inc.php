<?php

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

