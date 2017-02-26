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

	foreach($keys as $key) {
		//print $key."<br><br>\n";
		if(preg_match("/sess\.key/", $key)) {
			$user = $memc->get($key);
//			print "session:" . $user."<br>\n";

			if($user) {

				$data = cache()->unserializeSession($user);

				// collect sessions users
				// skip current user
				if($data && isset($data["SV"]) && $data["SV"]["csrf"] != session()->value("csrf")) {
					// print $key . ", " . $user . "<br>";
					// print_r($data);

					$values = $data["SV"];

					if($values["site"] == SITE_URL) {
						$users[] = array(
							"user_id" => $values["user_id"],
							"user_group_id" => $values["user_group_id"],
							"nickname" => isset($values["user_nickname"]) ? $values["user_nickname"] : "Anonymous",
							"ip" => $values["ip"],
							"useragent" => $values["useragent"],
							"last_login_at" => $values["last_login_at"]
						);
					}

				}

			}
		}
	}

}
?>
<div class="scene i:scene defaultList userOnlineList">
	<h1>Current online users</h1>

	<ul class="actions">
		<?= $HTML->link("All users", "/janitor/admin/user/list", array("class" => "button", "wrapper" => "li.cancel")) ?>
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
					<dt class="last_login_at">Logged in at</dt>
					<dd class="last_login_at"><?= session()->value("last_login_at") ?></dd>
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
			<li class="item user_id:<?= $user["user_id"] ?>">
				<h3><?= $user["nickname"] ?>, <?= $user_groups[arrayKeyValue($user_groups, "id", $user["user_group_id"])]["user_group"] ?></h3>
				<dl class="info">
					<dt class="last_login_at">Logged in at</dt>
					<dd class="last_login_at"><?= $user["last_login_at"] ?></dd>
					<dt class="ip">IP</dt>
					<dd class="ip"><?= $user["ip"] ?></dd>
					<dt class="useragent">UserAgent</dt>
					<dd class="useragent"><?= $user["useragent"] ?></dd>
				</dl>
				<ul class="actions">
					<?= $HTML->link("Edit", "/janitor/admin/user/edit/".$user["user_id"], array("class"=> "button primary", "wrapper" => "li.edit")) ?>
				</ul>
			</li>
			<? endforeach; ?>
		</ul>

	</div>

</div>