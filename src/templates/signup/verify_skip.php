<?php
global $action;
global $model;

$IC = new Items();
$page_item = $IC->getItem(array("tags" => "page:signup-verification-skipped", "extend" => array("user" => true, "tags" => true, "mediae" => true)));
if($page_item) {
	$this->sharingMetaData($page_item);
}


?>
<div class="scene signup i:scene">
	<h1>That's cool...</h1>
	<h2>but don't forget to verify later!</h2>
	<p>In order to access your account and subsribed services (like newsletters), you'll have to verify your email.</p>
	<p>If you lost your verification email, you can get a new one by trying to log in.</p>
</div>