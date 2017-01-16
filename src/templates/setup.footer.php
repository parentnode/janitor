	</div>
<?
# PROGRESS METER

$config_verified = false;
$software_verified = false;

$paths_verified = false;
$database_verified = false;
$mail_verified = false;

if(isset($_SESSION["SOFTWARE_INFO"]) && $_SESSION["SOFTWARE_INFO"]) {
	$software_verified = true;
}

if(isset($_SESSION["CONFIG_INFO"]) && $_SESSION["CONFIG_INFO"]) {
	$config_verified = true;
}

// if(isset($_SESSION["PATH_INFO"]) && $_SESSION["PATH_INFO"]) {
// 	$paths_verified = true;
// }

if(isset($_SESSION["DATABASE_INFO"]) && $_SESSION["DATABASE_INFO"]) {
	$database_verified = true;
}

if(isset($_SESSION["MAIL_INFO"]) && $_SESSION["MAIL_INFO"]) {
	$mail_verified = true;
}

?>
	<div id="navigation">
		<ul class="navigation">
			<li class="front"><a href="/janitor/admin/setup">Introduction</a></li>
			<li class="setup">
				<h3>Setup</h3>
				<ul class="subjects">
					<li class="check<?= $software_verified ? " done" : "" ?>"><a href="/janitor/admin/setup/check">Check system</a></li>
					<li class="config<?= $config_verified ? " done" : "" ?>"><a href="/janitor/admin/setup/config">Janitor configuration</a></li>
					<li class="database<?= $database_verified ? " done" : "" ?>"><a href="/janitor/admin/setup/database">Setup database</a></li>
					<li class="mail<?= $mail_verified ? " done" : "" ?>"><a href="/janitor/admin/setup/mail">Setup mail</a></li>
					<li class="finish"><a href="/janitor/admin/setup/finish">Finish installation</a></li>
				</ul>
			</li>
			<? if(defined("SETUP_TYPE") && SETUP_TYPE == "existing"): ?>
			<li class="upgrade">
				<h3>Upgrade</h3>
				<ul class="subjects">
					<li class="prices"><a href="/janitor/admin/setup/upgrade/upgrade-database">Upgrade Database</a></li>
				</ul>
			</li>
			<? endif; ?>
		</ul>
	</div>

	<div id="footer">
		<ul class="servicenavigation">
			<li class="totop"><a href="#header">To top</a></li>
		</ul>

		<p class="copyright">Copyright 2017, parentNode.dk</p>
	</div>

</div>

</body>
</html>