<?php

$title = "Search";
$label = "3 chars min.";
$button = "Search";

$url = HTML()->path."/search";

$pattern = false;
$query = "";
$tag = "";


if($_options !== false) {
	foreach($_options as $_option => $_value) {
		switch($_option) {
			case "title"              : $title                = $_value; break;
			case "label"              : $label                = $_value; break;
			case "button"             : $button               = $_value; break;

			case "url"                : $url                  = $_value; break;

			case "pattern"            : $pattern              = $_value; break;
			case "query"              : $query                = $_value; break;
			case "tag"                : $tag                  = $_value; break;

		}
	}
}

?>
	<div class="search i:search">
		<h2><?= $title ?></h2>

		<?= HTML()->formStart($url, ["class" => "labelstyle:inject"]) ?>
			<?= HTML()->input("pattern", ["type" => "hidden", "value" => ($pattern ? json_encode($pattern) : "")]) ?>
			<?= HTML()->input("tag", ["type" => "hidden", "value" => $tag]) ?>

			<fieldset>
				<?= HTML()->input("query", ["type" => "string", "label" => $label, "min" => 3, "required" => true, "value" => $query]) ?>
			</fieldset>

			<ul class="actions">
				<?= HTML()->submit($button, ["wrapper" => "li.search"]) ?>
			</ul>
		<?= HTML()->formEnd() ?>
	</div>
