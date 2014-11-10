<!DOCTYPE html>
<html lang="<?= $this->language() ?>">
<head>
	<!-- (c) & (p) parentNode.dk 2009-2014 //-->
	<!-- All material protected by copyrightlaws, as if you didnt know //-->
	<!-- If you want to help build the ultimate frontend-centered platform, visit parentnode.dk -->
	<title><?= $this->pageTitle() ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta name="keywords" content="" />
	<meta name="description" content="<?= $this->pageDescription() ?>" />
	<meta name="viewport" content="initial-scale=1, user-scalable=no" />
	<meta name="apple-mobile-web-app-capable" content="yes" />
	<meta name="apple-mobile-web-app-status-bar-style" content="black" />
<? if(session()->value("dev")) { ?>
	<link type="text/css" rel="stylesheet" media="all" href="/janitor/css/lib/seg_<?= $this->segment() ?>_include.css" />
	<script type="text/javascript" src="/janitor/js/lib/seg_<?= $this->segment() ?>_include.js"></script>
<? } else { ?>
	<link type="text/css" rel="stylesheet" media="all" href="/janitor/css/seg_<?= $this->segment() ?>.css" />
	<script type="text/javascript" src="/janitor/js/seg_<?= $this->segment() ?>.js"></script>
<? } ?>

</head>

<body<?= $HTML->attribute("class", $this->bodyClass()) ?>>

<div id="page" class="i:page">
	<div id="header">
		<ul class="servicenavigation">
			<li class="keynav front"><a href="/janitor"><?= SITE_NAME ?></a></li>
<?			if(session()->value("user_id") && session()->value("user_group_id") > 1): ?>
			<li class="keynav user nofollow"><a href="?logoff=true">Logoff</a></li>
<?			else: ?>
			<li class="keynav user nofollow"><a href="/login">Login</a></li>
<?			endif; ?>
		</ul>
	</div>

	<div id="content"<?= $HTML->attribute("class", $this->contentClass()) ?>>
