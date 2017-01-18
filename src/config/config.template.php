<?php

/**
* This file contains definitions
*
* @package Config Dummy file
*/
header("Content-type: text/html; charset=UTF-8");
error_reporting(E_ALL);

/**
* Site name
*/
define("SITE_UID", "###SITE_UID###");
define("SITE_NAME", "###SITE_NAME###");
define("SITE_URL", (isset($_SERVER["HTTPS"]) ? "https" : "http")."://".$_SERVER["SERVER_NAME"]);
define("SITE_EMAIL", "###SITE_EMAIL###");

/**
* Optional constants
*/
define("DEFAULT_PAGE_DESCRIPTION", "###DEFAULT_PAGE_DESCRIPTION###");
define("DEFAULT_LANGUAGE_ISO", "EN");
define("DEFAULT_COUNTRY_ISO", "DK");


// ENABLE ITEMS MODEL
define("SITE_ITEMS", true);

//define("SITE_SIGNUP", "/signup");
//define("SITE_SUBSCRIPTIONS", true);
//define("SITE_MEMBERS", true);

// Enable shop model
//define("SITE_SHOP", true);
//define("SHOP_ORDER_NOTIFIES", "email@domain.tld");

// Enable notifications (send collection email after N notifications)
define("SITE_COLLECT_NOTIFICATIONS", 50);

// INSTALL MODE (DISABLES ALL SECURITY)
//define("SITE_INSTALL", true);

?>
