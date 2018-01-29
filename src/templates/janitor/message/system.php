<?php
global $action;
global $IC;
global $model;
global $itemtype;

$template = getVar("template");

$template_options = mailer()->getTemplate($template);
$values = [];
//print_r($template_options);

// HTML Version
if($template_options[0]) {

	// find variables in content
	preg_match_all("/\{([a-zA-Z0-9\-_]+)\}/", $template_options[0], $matches);
//	print_r($matches);
	foreach($matches[1] as $match) {
//		print $match."<br>\n";
		if(array_search($match, $values) === false) {
//			print "add1<br>\n";

			$values[] = $match;
		}

	}

}
// Text version
if($template_options[1]) {

	// find variables in content
	preg_match_all("/\{([a-zA-Z0-9\-_]+)\}/", $template_options[1], $matches);
	foreach($matches[1] as $match) {

///		print $match."<br>\n";
		if(array_search($match, $values) === false) {
//			print "add2<br>\n";
			
			$values[] = $match;
		}

	}

}


// TODO: javascript should duplicate variables fieldset for each additional user being added to recipients field
//print_r($variables);

?>
<div class="scene i:scene defaultNew newSystemMessage">
	<h1>Static mail</h1>
	<h2><?= $template ?></h2>

	<ul class="actions">
		<?= $JML->newList(array("label" => "Messages", "action" => "/janitor/admin/message")) ?>
	</ul>

<? if($template_options[0] || $template_options[1]): ?>
	<div class="item i:newSystemMessage">
		<h2>Send message</h2>
		<?= $model->formStart("sendSystemMessage", array("class" => "labelstyle:inject")) ?>
			<?= $model->input("template", array("type" => "hidden", "value" => $template)) ?>

			<fieldset class="recipients">
				<?= $model->input("recipients") ?>
			</fieldset>

			<fieldset class="values">
				<h3>Custom mail values for: <span class="recipient">{RECIPIENT}</span></h3>
<? foreach($values as $value): 
	if(!defined($value)): ?>
				<?= $model->input("values[0][".$value."]", ["type" => "string", "label" => $value]) ?>
<?  endif;
endforeach; ?>
			</fieldset>

			<ul class="actions">
				<?= $model->submit("Send", array("class" => "primary", "wrapper" => "li.submit")) ?>
			</ul>

		<?= $model->formEnd() ?>
	</div>
<? else: ?>
	<p>Template not found</p>
<? endif; ?>
</div>
