	</div>
<?
global $model;
?>
	<div id="navigation">
		<ul class="navigation">
			<li class="front"><a href="/janitor/admin/setup">Introduction</a></li>
			<li class="setup">
				<h3>Setup</h3>
				<ul class="subjects">
					<li class="software<?= $model->get("software", "passed") ? " done" : "" ?>"><a href="/janitor/admin/setup/software">System and software</a></li>
					<li class="config<?= $model->get("config", "passed") ? " done" : "" ?>"><a href="/janitor/admin/setup/config">Basic configuration</a></li>
					<li class="database<?= $model->get("database", "passed") ? " done" : "" ?>"><a href="/janitor/admin/setup/database">Database connection</a></li>
					<li class="account<?= $model->get("account", "passed") ? " done" : "" ?>"><a href="/janitor/admin/setup/account">Admin account</a></li>
					<li class="mail<?= $model->get("mail", "passed") ? " done" : "" ?>"><a href="/janitor/admin/setup/mail">Mail gateway</a></li>
					<li class="payment<?= $model->get("payment", "passed") ? " done" : "" ?>"><a href="/janitor/admin/setup/payment">Payment gateway</a></li>
					<li class="finish"><a href="/janitor/admin/setup/finish">Finish installation</a></li>
				</ul>
			</li>
			<? if(defined("SETUP_TYPE") && SETUP_TYPE == "existing"): ?>
			<li class="upgrade">
				<h3>Upgrade</h3>
				<ul class="subjects">
					<li class="upgrade"><a href="/janitor/admin/setup/upgrade">Upgrades</a></li>
				</ul>
			</li>
			<? endif; ?>
			<li class="git">
				<h3>Git</h3>
				<ul class="subjects">
					<?= $HTML->link("Pull", "/janitor/admin/setup/pull", ["wrapper" => "li.pull"]); ?>
				</ul>
			</li>
		</ul>
	</div>

	<div id="footer">
		<ul class="servicenavigation">
			<li class="totop"><a href="#header">To top</a></li>
		</ul>

		<p class="copyright">Copyright 2017-2018, parentNode.dk</p>
	</div>

</div>

</body>
</html>