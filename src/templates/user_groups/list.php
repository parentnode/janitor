<?php
$access_item = false;
if(isset($read_access) && $read_access) {
	return;
}

include_once($_SERVER["FRAMEWORK_PATH"]."/config/init.php");

$action = $page->actions();

$UGC = new UserGroup();
// check if custom function exists on cart class
$user_groups = $UGC->getUserGroups();

// print_r($carts);

?>
<? $page->header(array("type" => "admin")) ?>
<div class="scene i:defaultList defaultList usersList">
	<h1>User groups</h1>

	<ul class="actions">
		<li class="new"><a href="/admin/user_groups/new" class="button primary">New group</a></li>
	</ul>

	<div class="all_items">
<?		if($user_groups): ?>
		<ul class="items">
<?			foreach($user_groups as $user_group): 
				//$item = $IC->getCompleteItem($item["id"]); 
				?>
			<li class="item">
				<h2><?= $user_group["name"] ?>

				<!--ul class="actions">
					<li class="view"><a href="/admin/carts/view/<?= $user_group["id"] ?>" class="button">View</a></li>
					<li class="delete">
						<form action="/admin/cms/carts/delete/<?= $user_group["id"] ?>" class="i:formDefaultDelete" method="post" enctype="multipart/form-data">
							<input type="submit" value="Delete" class="button delete" />
						</form>
					</li>
				</ul-->
			 </li>
<?			endforeach; ?>
		</ul>
<?		else: ?>
		<p>No user groups.</p>
<?		endif; ?>
	</div>

</div>
<? $page->footer(array("type" => "admin")) ?>