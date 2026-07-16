<?php

$handle = false;
$levels = 1;

$id = false;
$class = false;

if($_options !== false) {
	foreach($_options as $_option => $_value) {
		switch($_option) {

			case "handle"                  : $handle                  = $_value; break;
			case "levels"                  : $levels                  = $_value; break;

			case "id"                      : $id                      = $_value; break;
			case "class"                   : $class                   = $_value; break;

		}
	}
}


if($handle):

	$navigation = navigation()->get($handle, $levels);
?>

	<div<?= HTML()->attribute("id", $id) ?><?= HTML()->attribute("class", $class) ?>>
		<ul>
		<? if($navigation): ?>
			<? foreach($navigation["nodes"] as $node): ?>
			<?= HTML()->navigationLink($node, $levels); ?>
			<? endforeach; ?>
	 	<? endif; ?>
		</ul>
	</div>

<?
endif;

