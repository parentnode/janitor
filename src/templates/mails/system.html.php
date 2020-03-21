<html>
<head>
	<title>System template from {SITE_URL}</title>
</head>
<body>


<h3>{message}</h3>


<h2>------ ADDITIONAL SERVER INFO ------</h2>


<dl>
	<dt>Origin:</dt>
	<dd><?= ($_SERVER["HTTPS"] ? "https" : "http") ?>://<?= $_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"] ?></dd>

	<dt>From IP:</dt>
	<dd><?= (getenv("HTTP_X_FORWARDED_FOR") ? getenv("HTTP_X_FORWARDED_FOR") : getenv("REMOTE_ADDR")) ?></dd>

	<dt>Referer:</dt>
	<dd><?= $_SERVER["HTTP_REFERER"] ?></dd>

	<dt>UserAgent:</dt>
	<dd><?= $_SERVER["HTTP_USER_AGENT"] ?></dd>
</dl>


<h3>Segment:</h3>
<code style="white-space: pre-wrap;"><?= print_r(session()->value("segment"), true) ?></code>

<h3>Messages:</h3>
<code style="white-space: pre-wrap;"><?= print_r(message()->getMessages(), true) ?></code>

<h3>_SESSION:</h3>
<code style="white-space: pre-wrap;"><?= print_r($_SESSION, true) ?></code>
<?
// Remove passwords before sending
if(isset($_POST["password"])) {unset($_POST["password"]);}
if(isset($_POST["new_password"])) {unset($_POST["new_password"]);}
if(isset($_POST["confirm_password"])) {unset($_POST["confirm_password"]);}
if(isset($_POST["old_password"])) {unset($_POST["old_password"]);}
?>
<h3>_POST:</h3>
<code style="white-space: pre-wrap;"><?= print_r($_POST, true) ?></code>

<h3>_GET:</h3>
<code style="white-space: pre-wrap;"><?= print_r($_GET, true) ?></code>

<h3>_FILES:</h3>
<code style="white-space: pre-wrap;"><?= print_r($_FILES, true) ?></code>

<h3>_SERVER:</h3>
<code style="white-space: pre-wrap;"><?= print_r($_SERVER, true) ?></code>

</body>
</html>