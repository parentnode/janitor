<?php

$title = "Search";
$label = "3 chars min.";
$button = "Search";

$class = "search";
$init_class = "i:search";
$form_class = "labelstyle:inject search";

$url = HTML()->path."/search";

$pattern = false;
$query = "";
$min_query = 3;
$tag = "";


if($_options !== false) {
	foreach($_options as $_option => $_value) {
		switch($_option) {
			case "title"              : $title                = $_value; break;
			case "label"              : $label                = $_value; break;
			case "button"             : $button               = $_value; break;

			case "class"              : $class                = $_value; break;
			case "init_class"         : $init_class           = $_value; break;

			case "form_class"         : $form_class           = $_value; break;

			case "url"                : $url                  = $_value; break;

			case "pattern"            : $pattern              = $_value; break;
			case "query"              : $query                = $_value; break;
			case "min_query"          : $min_query            = $_value; break;
			case "tag"                : $tag                  = $_value; break;

		}
	}
}

?>
	<div <?= HTML()->attribute("class", $class, $init_class) ?>>
		<h2><?= $title ?></h2>

		<?= HTML()->formStart($url, ["class" => $form_class]) ?>
			<?= HTML()->input("pattern", ["type" => "hidden", "value" => ($pattern ? json_encode($pattern) : "")]) ?>
			<?= HTML()->input("tag", ["type" => "hidden", "value" => $tag]) ?>

			<fieldset>
				<?= HTML()->input("query", ["type" => "string", "label" => $label, "min" => $min_query, "required" => true, "value" => $query]) ?>
			</fieldset>

			<ul class="actions">
				<?= HTML()->submit($button, ["wrapper" => "li.search"]) ?>
			</ul>
		<?= HTML()->formEnd() ?>
	</div>
