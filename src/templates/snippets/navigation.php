<?php

$handle = false;
$levels = 1;

if($_options !== false) {
	foreach($_options as $_option => $_value) {
		switch($_option) {

			case "handle"                  : $handle                  = $_value; break;
			case "levels"                  : $levels                  = $_value; break;

		}
	}
}


if($handle):

	$navigation = navigation()->get($handle);
?>

	<div id="navigation">
		<ul class="navigation">
		<? if($navigation): ?>
			<? foreach($navigation["nodes"] as $node): ?>
			<?= HTML()->navigationLink($node, $levels); ?>
			<? endforeach; ?>
	 	<? endif; ?>
		</ul>
	</div>

<?
endif;

