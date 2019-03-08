<?php

// segment translations
// fallback settings for sites without specific segments configuration
// override this config by placing your own statements in config/segments.php
// you can override setting individually or for a whole type group - it is just an Array :-)

$segments_config = array(
	"www" => array(
	
		// fallback to something similar to detector-v2
		"desktop_edge"  => "desktop",
		"desktop"       => "desktop",

		"desktop_ie11"  => "desktop_ie",
		"desktop_ie10"  => "desktop_ie",
		"desktop_ie9"   => "desktop_ie",

		"smartphone"    => "mobile_touch",

		"desktop_light" => "desktop_light",
		"tv"            => "desktop_light",

		"tablet"        => "tablet",
		"tablet_light"  => "tablet",

		"mobile"        => "mobile",
		"mobile_light"  => "mobile_light",

		"seo"           => "basic"
	),
	"janitor" => array(

		"desktop_edge"  => "desktop",
		"desktop_ie11"  => "desktop",
		"desktop_ie10"  => "desktop",
		"desktop"       => "desktop",

		"smartphone"    => "smartphone",

		"desktop_light" => "unsupported",
		"desktop_ie9"   => "unsupported",
		"tv"            => "unsupported",
		"tablet"        => "desktop",
		"tablet_light"  => "desktop",
		"mobile"        => "unsupported",
		"mobile_light"  => "unsupported",
		"seo"           => "unsupported"

	),
	"login" => array(
	
		"desktop_edge"  => "desktop",
		"desktop_ie11"  => "desktop",
		"desktop_ie10"  => "desktop",
		"desktop"       => "desktop",

		"smartphone"    => "smartphone",

		"desktop_light" => "unsupported",
		"tv"            => "unsupported",
		"tablet"        => "desktop",
		"tablet_light"  => "desktop",
		"mobile"        => "unsupported",
		"mobile_light"  => "unsupported",
		"seo"           => "unsupported"
	),
	"setup" => array(
	
		"desktop_edge"  => "desktop",
		"desktop_ie11"  => "desktop",
		"desktop_ie10"  => "desktop",
		"desktop"       => "desktop",

		"smartphone"    => "unsupported",
		"desktop_light" => "unsupported",
		"tv"            => "unsupported",
		"tablet"        => "unsupported",
		"tablet_light"  => "unsupported",
		"mobile"        => "unsupported",
		"mobile_light"  => "unsupported",
		"seo"           => "unsupported"
	)

);

?>
