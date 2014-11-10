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
define("SITE_UID", "JNT");
define("SITE_NAME", "Janitor");
define("SITE_URL", "domain.com");
define("SITE_EMAIL", "mail@domain.com");

/**
* Optional constants
*/
define("DEFAULT_PAGE_DESCRIPTION", "");
define("DEFAULT_LANGUAGE_ISO", "en"); // Reginal language English
define("DEFAULT_COUNTRY_ISO", "dk"); // Regional country Denmark


// ENABLE ITEMS MODEL
define("SITE_ITEMS", false);

// Enable notifications (send collection email after N notifications)
define("SITE_COLLECT_NOTIFICATIONS", 50);

?>
