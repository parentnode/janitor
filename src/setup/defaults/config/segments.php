<?php

// local segmentation
// setup default site runs only on desktop to minimize maintenance
$segments_config["www"] = array(
	
	"desktop_edge"  => "desktop",
	"desktop"       => "desktop",
	"desktop_ie11"  => "desktop",
	"desktop_ie10"  => "desktop",

	"smartphone"    => "desktop",

	"desktop_ie9"   => "desktop",
	"desktop_light" => "desktop",
	"tv"            => "desktop",

	"tablet"        => "desktop",
	"tablet_light"  => "desktop",

	"mobile"        => "desktop",
	"mobile_light"  => "desktop",

	"seo"           => "desktop"

);

?>