<!DOCTYPE html>
<html lang="en">
<head>
	<!-- (c) & (p) parentnode.dk 2009-2015 //-->
	<!-- All material protected by copyrightlaws, as if you didnt know //-->
	<!-- If you want to help build the ultimate frontend-centered platform, visit parentnode.dk -->
	<title>Janitor setup</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta name="keywords" content="" />
	<meta name="description" content="" />
	<meta name="viewport" content="initial-scale=1, user-scalable=no" />
	<meta name="apple-mobile-web-app-capable" content="yes" />
	<meta name="apple-mobile-web-app-status-bar-style" content="black" />
<? if(session()->value("dev")) { ?>
	<link type="text/css" rel="stylesheet" media="all" href="/setup/css/lib/seg_<?= $this->segment() ?>_include.css" />
	<script type="text/javascript" src="/setup/js/lib/seg_<?= $this->segment() ?>_include.js"></script>
<? } else { ?>
	<link type="text/css" rel="stylesheet" media="all" href="/setup/css/seg_<?= $this->segment() ?>.css" />
	<script type="text/javascript" src="/setup/js/seg_<?= $this->segment() ?>.js"></script>
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
