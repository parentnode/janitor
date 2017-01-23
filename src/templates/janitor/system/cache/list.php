<?php
global $action;
global $model;


if(class_exists("Memcached")) {

	$memc = new Memcached();
	$con = $memc->addServer('localhost', 11211);
	print "con:".$con.";";
	print_r($memc->getStats());

	$entries = array();
	$keys = $memc->getAllKeys();

	foreach($keys as $key) {
		// only list cache entries matching current site
		if(preg_match("/^".preg_quote(SITE_URL, "/")."\-/", $key)) {

			$entry = $memc->get($key);
			$data = cache()->decodeSessionJSON($entry);

			$entries[preg_replace("/^".preg_quote(SITE_URL, "/")."\-/", "", $key)] = $data;

		}

	}

}
?>
<div class="scene i:scene defaultList cacheList">
	<h1>Cache entries</h1>
	<h2><?= SITE_URL ?></h2>

	<div class="all_items users i:defaultList i:cacheList filters"
		data-csrf-token="<?= session()->value("csrf") ?>"
		data-flush-url="<?= $this->validPath("/janitor/admin/system/flushFromCache") ?>"
		>
		<ul class="items">
			<? foreach($entries as $key => $data): ?>
			<li class="item" data-cache-key="<?= $key ?>">
				<h3><?= $key ?></h3>
				<div class="details">
					<? if(is_array($data)): ?>
					<pre><p><? print_r($data) ?></p></pre>
					<? else: ?>
					<p><?= $data ?></p>
					<? endif; ?>
				</div>
			</li>
			<? endforeach; ?>
		</ul>

	</div>

</div>