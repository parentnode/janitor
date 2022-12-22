<?php

/**
* This file contains definitions
*
* @package Config
*/
header("Content-type: text/html; charset=UTF-8");
error_reporting(E_ALL);


define("VERSION", "###CURRENT_JANITOR_VERSION###");
define("UI_BUILD", "###CURRENT_UI_BUILD###");


define("SITE_UID", "###SITE_UID###");
define("SITE_NAME", "###SITE_NAME###");
define("SITE_URL", (isset($_SERVER["HTTPS"]) ? "https" : "http")."://".$_SERVER["SERVER_NAME"]);
define("SITE_EMAIL", "###SITE_EMAIL###");

define("DEFAULT_PAGE_DESCRIPTION", "###DEFAULT_PAGE_DESCRIPTION###");
define("DEFAULT_PAGE_IMAGE", "/img/logo-large.png");

define("DEFAULT_LANGUAGE_ISO", "EN");
define("DEFAULT_COUNTRY_ISO", "DK");
define("DEFAULT_CURRENCY_ISO", "DKK");

define("SITE_LOGIN_URL",  "/janitor/admin/login");

define("SITE_SIGNUP", ###SITE_SIGNUP###);
define("SITE_SIGNUP_URL", "/signup");

define("SITE_ITEMS", ###SITE_ITEMS###);

define("SITE_SHOP", ###SITE_SHOP###);
define("SHOP_ORDER_NOTIFIES", "");

define("SITE_SUBSCRIPTIONS", ###SITE_SUBSCRIPTIONS###);

define("SITE_MEMBERS", ###SITE_MEMBERS###);

// send collection email after N rows
define("SITE_COLLECT_NOTIFICATIONS", 50);


// Enable session realtime writing for async session read/write
// Slower but necessary in application with high number of simultaneaous requests that updates sessions values
define("REALTIME_SESSION_WRITE", false);



// INSTALL MODE (DISABLES ALL SECURITY) – ONLY USE IN EMERGENCIES AND ONLY TEMPORARILY
// define("SITE_INSTALL", true);
