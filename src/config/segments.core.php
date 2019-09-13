<?php

// segment translations
// fallback settings for sites without specific segments configuration
// override this config by placing your own statements in config/segments.php
// you can override setting individually or for a whole type group - it is just an Array :-)

$segments_config = array(
	"www" => array(
	
		// fallback to something similar to detector-v2
		"desktop"       => "desktop",

		"desktop_ie11"  => "desktop_ie",
		"desktop_ie10"  => "desktop_ie",
		"desktop_ie9"   => "desktop_ie",

		"smartphone"    => "smartphone",

		"desktop_light" => "desktop_light",
		"tv"            => "desktop_light",

		"tablet"        => "tablet",
		"tablet_light"  => "tablet",

		"mobile"        => "mobile",
		"mobile_light"  => "mobile_light",

		"seo"           => "seo"
	),
	"janitor" => array(

		"desktop"       => "desktop",
		"tablet"        => "desktop",
		"smartphone"    => "smartphone",

		"desktop_ie11"  => "unsupported",
		"desktop_ie10"  => "unsupported",
		"desktop_ie9"   => "unsupported",
		"desktop_light" => "unsupported",
		"tv"            => "unsupported",
		"tablet_light"  => "unsupported",
		"mobile"        => "unsupported",
		"mobile_light"  => "unsupported",
		"seo"           => "unsupported"

	),
	"login" => array(
	
		"desktop"       => "desktop",
		"tablet"        => "desktop",
		"smartphone"    => "smartphone",

		"desktop_ie11"  => "unsupported",
		"desktop_ie10"  => "unsupported",
		"desktop_ie9"   => "unsupported",
		"desktop_light" => "unsupported",
		"tv"            => "unsupported",
		"tablet_light"  => "unsupported",
		"mobile"        => "unsupported",
		"mobile_light"  => "unsupported",
		"seo"           => "unsupported"
	),
	"setup" => array(
	
		"desktop"       => "desktop",

		"desktop_ie11"  => "unsupported",
		"desktop_ie10"  => "unsupported",
		"desktop_ie9"   => "unsupported",
		"desktop_light" => "unsupported",
		"tv"            => "unsupported",
		"tablet"        => "unsupported",
		"tablet_light"  => "unsupported",
		"smartphone"    => "unsupported",
		"mobile"        => "unsupported",
		"mobile_light"  => "unsupported",
		"seo"           => "unsupported"
	)

);

?>
