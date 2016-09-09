<?php
global $action;
global $model;


$user_id = $action[1];
$IC = new Items();


$user = $model->getUsers(array("user_id" => $user_id));
$readstates = $model->getReadstates(array("user_id" => $user_id));

?>
<div class="scene i:scene defaultList userReadstateList">
	<h1>Readstates</h1>
	<h2><?= $user["nickname"] ?></h2>

	<ul class="actions">
		<?= $HTML->link("All users", "/janitor/admin/user/list/".$user["user_group_id"], array("class" => "button", "wrapper" => "li.cancel")) ?>
	</ul>


	<?= $JML->userTabs($user_id, "readstates") ?>


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