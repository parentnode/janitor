<?php
global $action;
global $model;
$IC = new Items();

$user_id = session()->value("user_id");


// get current user
$user = $model->getUser();
$readstates = $model->getReadstates();
?>
<div class="scene i:scene defaultList userReadstateList">
	<h1>Readstates</h1>
	<h2><?= $user["nickname"] ?></h2>


	<?= $JML->profileTabs($user_id, "readstates") ?>


	<div class="all_items readstates i:defaultList filters"
		data-csrf-token="<?= session()->value("csrf") ?>"
		>
		<h2>Items read</h2>
<? 		if($readstates): ?>
		<ul class="items">
<? 			foreach($readstates as $item):
				$item = $IC->getItem(array("id" => $item["item_id"], "extend" => true)); ?>
			<li class="item item_id:<?= $item["item_id"] ?>">
				<h3><?= $item["name"] ?> (<?= $item["itemtype"] ?>)</h3>
			</li>
<? 			endforeach; ?>
		</ul>
<? 		else: ?>
		<p>No items.</p>
<? 		endif; ?>
	</div>

</div>