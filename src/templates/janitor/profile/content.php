<?php
global $action;
global $model;


$user_id = session()->value("user_id");
$IC = new Items();


// get current user
$item = $model->getUser();

//$user = $model->getUsers(array("user_id" => $user_id));
$readstates = $model->getReadstates();
$items = $IC->getItems(array("user_id" => $user_id, "extend" => true));
$comments = $IC->getComments(array("user_id" => $user_id));

?>
<div class="scene i:scene defaultList userContentList profileContentList">
	<h1>Readstates, Content and Comments</h1>
	<h2><?= $item["nickname"] ?></h2>

	<?= $JML->profileTabs("content") ?>


	<div class="all_items readstates i:defaultList filters"
		data-csrf-token="<?= session()->value("csrf") ?>"
		>
		<h2>Items you have marked as read:</h2>
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


	<div class="all_items content i:defaultList filters"
		data-csrf-token="<?= session()->value("csrf") ?>"
		>
		<h2>Items owned by you:</h2>
<? 		if($items): ?>
		<ul class="items">
<? 			foreach($items as $item):

				// find path to itemtype
				// We don know whether it is an inherited controller or a local one
				// - look in the two most obvious places
				if(file_exists(LOCAL_PATH."/www/janitor/".$item["itemtype"].".php")) {
					$path = "/janitor/".$item["itemtype"];
				}
				else if(file_exists(FRAMEWORK_PATH."/www/".$item["itemtype"].".php")) {
					$path = "/janitor/admin/".$item["itemtype"];
				}
				else {
					$path = false;
				}
?>
			<li class="item item_id:<?= $item["item_id"] ?>">
				<h3><?= strip_tags($item["name"]) ?> (<?= $item["itemtype"] ?>)</h3>

				<ul class="actions">
					<?= $path ? $HTML->link("Edit", $path."/edit/".$item["id"], array("class" => "button", "wrapper" => "li.edit")) : "" ?>
					<?= $path ? $JML->statusButton("Enable", "Disable", $path."/status", $item, array("js" => true)) : "" ?>
				</ul>
			</li>
<? 			endforeach; ?>
		</ul>
<? 		else: ?>
		<p>No items.</p>
<? 		endif; ?>
	</div>


	<div class="all_items comments i:defaultList filters"
		data-csrf-token="<?= session()->value("csrf") ?>"
		>
		<h2>Comments you have made:</h2>
<? 		if($comments): ?>
		<ul class="items">
<? 			foreach($comments as $comment): ?>
			<li class="item comment comment_id:<?= $comment["item_id"] ?>">
				<h3>Comment for: <?= $comment["item"]["name"] ?></h3>
				<ul class="info">
					<li class="created_at"><?= date("Y-m-d, H:i", strtotime($comment["created_at"])) ?></li>
				</ul>
				<p class="comment"><?= $comment["comment"] ?></p>
			</li>
<? 			endforeach; ?>
		</ul>
<? 		else: ?>
		<p>No comments.</p>
<? 		endif; ?>
	</div>

</div>