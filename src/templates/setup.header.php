<!DOCTYPE html>
<html lang="en">
<head>
	<!-- (c) & (p) think.dk 2002-2016 -->
	<!-- For detailed copyright license, see /terms -->
	<!-- If you want to use or contribute to this code, visit http://parentnode.dk -->
	<title>Janitor setup</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta name="keywords" content="" />
	<meta name="description" content="" />
	<meta name="viewport" content="initial-scale=1, user-scalable=no" />

	<meta name="apple-mobile-web-app-capable" content="yes" />
	<meta name="apple-mobile-web-app-status-bar-style" content="black" />

<? if(session()->value("dev")) { ?>
	<link type="text/css" rel="stylesheet" media="all" href="/setup/css/lib/seg_<?= $this->segment(array("type" => "setup")) ?>_include.css" />
	<script type="text/javascript" src="/setup/js/lib/seg_<?= $this->segment(array("type" => "setup")) ?>_include.js"></script>
<? } else { ?>
	<link type="text/css" rel="stylesheet" media="all" href="/setup/css/seg_<?= $this->segment(array("type" => "setup")) ?>.css" />
	<script type="text/javascript" src="/setup/js/seg_<?= $this->segment(array("type" => "setup")) ?>.js"></script>
<? } ?>
</head>

<body<?= $HTML->attribute("class", $this->bodyClass()) ?>>

<div id="page" class="i:page">

	<div id="header">
		<ul class="servicenavigation">
			<li class="keynav front"><a href="/setup">Janitor</a></li>
		</ul>
		
	</div>

	<div id="content">
