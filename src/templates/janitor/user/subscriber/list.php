<?php
global $action;
global $model;



$subscriptions = $model->getSubscriptions();
$subscribers = false;
$item_id = false;
	

// show subscriptions
if(count($action) > 2 && $action[2]) {
	
	$item_id = $action[2];

}

if(!$item_id && $subscriptions) {
	$item_id = $subscriptions[0]["item_id"];
}

if($item_id) {
	$subscribers = $model->getSubscriptions(array("item_id" => $item_id));
}

?>
<div class="scene i:scene defaultList subscriberList">
	<h1>Subscribers</h1>

	<!--ul class="actions">
		<?= $JML->listNew(array(
			"label" => "New subscriber", 
			"action" => "subscriber/new"
		)) ?>
	</ul-->

<?	if($subscriptions): ?>
	<ul class="tabs">
<?		foreach($subscriptions as $subscription): ?>
		<?= $HTML->link($subscription["name"], "/janitor/admin/user/subscriber/list/".$subscription["item_id"], array("wrapper" => "li.".($subscription["item_id"] == $item_id ? "selected" : ""))) ?>
<?		endforeach; ?>
	</ul>
<?	else: ?>
	<p>You have no subscriptions.</p>
<?	endif; ?>


	<div class="all_items i:defaultList taggable sortable filters"<?= $JML->jsData() ?>>
<?		if($subscribers): ?>
		<ul class="items">

<?			foreach($subscribers as $subscriber): ?>
			<li class="item item_id:<?= $subscriber["id"] ?>">
				<h3><?= preg_replace("/<br>|<br \/>/", "", $subscriber["nickname"]) ?></h3>

			 </li>
<?			endforeach; ?>

		</ul>
<?		else: ?>
		<p>No Subscribers.</p>
<?		endif; ?>
	</div>

</div>
