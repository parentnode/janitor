<!DOCTYPE html>
<html lang="en">
<head>
	<!-- (c) & (p) think.dk 2002-2019 -->
	<!-- For detailed copyright license, see /terms -->
	<!-- If you want to use or contribute to this code, Visit https://parentnode.dk -->
	<title>Janitor setup</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta name="keywords" content="" />
	<meta name="description" content="" />
	<meta name="viewport" content="initial-scale=1, user-scalable=no" />

	<meta name="apple-mobile-web-app-capable" content="yes" />
	<meta name="apple-mobile-web-app-status-bar-style" content="black" />

	<link type="text/css" rel="stylesheet" media="all" href="/janitor/admin/css/setup/seg_<?= $this->segment(array("type" => "setup")) ?>_include.css" />
	<script type="text/javascript" src="/janitor/admin/js/setup/seg_<?= $this->segment(array("type" => "setup")) ?>_include.js"></script>
</head>

<body<?= $HTML->attribute("class", $this->bodyClass()) ?>>

<div id="page" class="i:page">

	<div id="header">
		<ul class="servicenavigation">
			<li class="keynav front"><a href="<?= SETUP_TYPE == "new" ? "/janitor/admin/setup" : "/janitor" ?>">Janitor</a></li>
		</ul>
		
	</div>

	<div id="content">
