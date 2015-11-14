<!DOCTYPE html>
<html lang="en">
<head>
	<!-- (c) & (p) parentnode.dk 2009-2016 //-->
	<!-- All material protected by copyrightlaws, as if you didnt know //-->
	<!-- If you want to help build the ultimate frontend-centered platform, visit parentnode.dk -->
	<title>Janitor login</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta name="keywords" content="" />
	<meta name="description" content="" />
	<meta name="viewport" content="initial-scale=1, user-scalable=no" />

	<meta name="apple-mobile-web-app-capable" content="yes" />
	<meta name="apple-mobile-web-app-status-bar-style" content="black" />

<? if(session()->value("dev")) { ?>
	<link type="text/css" rel="stylesheet" media="all" href="/janitor/css/lib/seg_<?= $this->segment(array("type" => "login")) ?>_include.css" />
	<script type="text/javascript" src="/janitor/js/lib/seg_<?= $this->segment(array("type" => "login")) ?>_include.js"></script>
<? } else { ?>
	<link type="text/css" rel="stylesheet" media="all" href="/janitor/css/seg_<?= $this->segment(array("type" => "login")) ?>.css" />
	<script type="text/javascript" src="/janitor/js/seg_<?= $this->segment(array("type" => "login")) ?>.js"></script>
<? } ?>
</head>

<body<?= $HTML->attribute("class", $this->bodyClass()) ?>>

<div id="page" class="i:page">

	<div id="header">
		<ul class="servicenavigation">
			<li class="keynav front"><span class="janitor">Janitor</span></li>
		</ul>
		
	</div>

	<div id="content">
