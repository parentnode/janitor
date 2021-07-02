<!DOCTYPE html>
<html lang="<?= $this->language() ?>">
<head>
	<!-- (c) & (p) think.dk 2002-2019 -->
	<!-- For detailed copyright license, see /terms -->
	<!-- If you want to use or contribute to this code, Visit https://parentnode.dk -->
	<title><?= $this->pageTitle() ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta name="keywords" content="" />
	<meta name="description" content="<?= $this->pageDescription() ?>" />
	<meta name="viewport" content="initial-scale=1, user-scalable=no" />
	<meta name="apple-mobile-web-app-capable" content="yes" />
	<meta name="apple-mobile-web-app-status-bar-style" content="black" />

<? if(session()->value("dev")) { ?>
	<link type="text/css" rel="stylesheet" media="all" href="/janitor/css/lib/seg_<?= $this->segment(array("type" => "janitor")) ?>_include.css" />
	<script type="text/javascript" src="/janitor/js/lib/seg_<?= $this->segment(array("type" => "janitor")) ?>_include.js"></script>
<? } else { ?>
	<link type="text/css" rel="stylesheet" media="all" href="/janitor/css/seg_<?= $this->segment(array("type" => "janitor")) ?>.css?rev=0.7.9" />
	<script type="text/javascript" src="/janitor/js/seg_<?= $this->segment(array("type" => "janitor")) ?>.js?rev=0.7.9"></script>
<? } ?>

	<?= $this->headerIncludes() ?>
</head>

<body<?= $HTML->attribute("class", $this->bodyClass()) ?>>

<div id="page" class="i:page">
	<div id="header">
		<ul class="servicenavigation">
			<li class="keynav front"><a href="/janitor">Janitor</a></li>
<?			if(session()->value("user_id") && session()->value("user_group_id") > 1): ?>
			<li class="keynav web nofollow"><a href="/"><?= SITE_NAME ?></a></li>
			<li class="keynav profile nofollow"><a href="/janitor/admin/profile"><?= session()->value("user_nickname") ?></a></li>
			<li class="keynav user logoff nofollow"><a href="?logoff=true">Logoff</a></li>
<?			else: ?>
			<li class="keynav user login nofollow"><a href="<?= SITE_LOGIN_URL ?>">Login</a></li>
<?			endif; ?>
		</ul>
	</div>

	<div id="content"<?= $HTML->attribute("class", $this->contentClass()) ?>>
