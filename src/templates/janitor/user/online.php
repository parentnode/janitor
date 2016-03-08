<?php
global $action;
global $model;


if(class_exists("Memcached")) {

	$memc = new Memcached();
	$memc->addServer('localhost', 11211);

	// get userGroups to be able to show readable groups
	$user_groups = $model->getUserGroups();


	$users = array();

	$keys = $memc->getAllKeys();
//	print "keys:" . count($keys)."<br>\n";


	foreach($keys as $key) {
		//print $key."<br><br>\n";
		if(preg_match("/sess\.key/", $key)) {
			$user = $memc->get($key);
//			print "session:" . $user."<br>\n";

			if($user) {

				$data = cache()->unserializeSession($user);

				if(isset($data["SV"])) {
					$values = $data["SV"];

					if($values["site"] == SITE_URL) {
						$users[] = array(
							"user_id" => $values["user_id"],
							"user_group_id" => $values["user_group_id"],
							"nickname" => isset($values["user_nickname"]) ? $values["user_nickname"] : "Anonymous",
							"ip" => $values["ip"],
							"useragent" => $values["useragent"],
							"logged_in_at" => $values["logged_in_at"]
						);
					}

				}

			}
		}
	}

}

?>
<div class="scene defaultList userOnlineList">
	<h1>Current online users</h1>

	<ul class="actions">
		<?= $HTML->link("All users", "/janitor/admin/user/list/", array("class" => "button", "wrapper" => "li.cancel")) ?>
		<?= $HTML->link("User groups", "/janitor/admin/user/group/list", array("class" => "button", "wrapper" => "li.usergroup")) ?>
	</ul>

	<div class="all_items users i:defaultList i:flushUserSession filters"
		data-csrf-token="<?= session()->value("csrf") ?>"
		data-flush-url="<?= $this->validPath("/janitor/admin/user/flushUserSession") ?>"
		
		>
		<ul class="items">
			<li class="item user_id:<?= session()->value("user_id") ?> current_user">
				<h3><?= session()->value("user_nickname") ?>, <?= $user_groups[arrayKeyValue($user_groups, "id", session()->value("user_group_id"))]["user_group"] ?></h3>
				<dl class="info">
					<dt class="logged_in_at">Logged in at</dt>
					<dd class="logged_in_at"><?= session()->value("logged_in_at") ?></dd>
					<dt class="ip">IP</dt>
					<dd class="ip"><?= session()->value("ip") ?></dd>
					<dt class="useragent">UserAgent</dt>
					<dd class="useragent"><?= session()->value("useragent") ?></dd>
				</dl>
				<ul class="actions">
					<?= $HTML->link("Edit", "/janitor/admin/user/edit/".session()->value("user_id"), array("class"=> "button primary", "wrapper" => "li.edit")) ?>
				</ul>

			</li>
			<? foreach($users as $user): ?>
			<li class="item user_id:<?= $user["user_id"] ?><?= $user["user_id"] == session()->value("user_id") ? " current_user" : "" ?>">
				<h3><?= $user["nickname"] ?>, <?= $user_groups[arrayKeyValue($user_groups, "id", $user["user_group_id"])]["user_group"] ?></h3>
				<dl class="info">
					<dt class="logged_in_at">Logged in at</dt>
					<dd class="logged_in_at"><?= $user["logged_in_at"] ?></dd>
					<dt class="ip">IP</dt>
					<dd class="ip"><?= $user["ip"] ?></dd>
					<dt class="useragent">UserAgent</dt>
					<dd class="useragent"><?= $user["useragent"] ?></dd>
				</dl>
				<ul class="actions">
					<?= $HTML->link("Edit", "/janitor/admin/user/edit/".$user["user_id"], array("class"=> "button primary", "wrapper" => "li.edit")) ?>
					<?//= $HTML->link("Flush", "/janitor/admin/user/flushUserSession/".$user["user_id"], array("class"=> "button", "wrapper" => "li.edit")) ?>
				</ul>
			</li>
			<? endforeach; ?>
		</ul>

	</div>

</div>