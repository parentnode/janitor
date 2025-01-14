<?php

global $action;
$IC = new Items();


$message_item = false;

if(count($action) == 1) {
	$token = $action[0];

	$query = new Query();
	$query->sql("SELECT * FROM ".SITE_DB.".user_log_messages WHERE token = '$token'");
	$message_details = $query->result(0);

	if($message_details && $message_details["item_id"]) {

		// init mailer to expose ADMIN_EMAIL
		email();

		// get message item
		$message_item = $IC->getItem(array("id" => $message_details["item_id"], "extend" => true));
		if($message_item) {

			$message_data = json_decode($message_details["data"]);

			// Replace global variables
			$message_item["html"] = preg_replace("/{SITE_URL}/", SITE_URL, $message_item["html"]);
			$message_item["html"] = preg_replace("/{SITE_NAME}/", SITE_NAME, $message_item["html"]);
			$message_item["html"] = preg_replace("/{SITE_EMAIL}/", SITE_EMAIL, $message_item["html"]);
			$message_item["html"] = preg_replace("/{ADMIN_EMAIL}/", ADMIN_EMAIL, $message_item["html"]);

			// Replace user variables
			foreach($message_data as $key => $value) {
				$message_item["html"] = preg_replace("/{".$key."}/", $value, $message_item["html"]);
			}

			$this->pageTitle($message_item["name"]);

		}

	}

}

?>
<div class="scene messages i:scene">

<? if($message_item && $message_item["status"]): ?>
	<div class="article i:article" itemscope itemtype="http://schema.org/Article">

		<h1 itemprop="headline"><?= $message_item["name"] ?></h1>

		<div class="articlebody" itemprop="articleBody">
			<?= $message_item["html"] ?>
		</div>

	</div>
	
<? else: ?>

	<h1>What?</h1>
	<p>There is no message for you today.</p>

<? endif; ?>

</div>
