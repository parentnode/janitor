<?php
global $action;
global $model;

$entries = cache()->getAllDomainPairs();

?>
<div class="scene i:scene defaultList cacheList">
	<h1>Cache entries</h1>
	<h2><?= cache()->cache_type ?> cache</h2>

	<? if(cache()->cache_type == "pseudo"): ?>
	<p>No caching server available on your system. The following values should have been cached.</p>
	<? endif; ?>

	<? if($entries): ?>
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
	<? else: ?>
	<p>No entries in your cache.</p>
	<? endif; ?>
</div>
