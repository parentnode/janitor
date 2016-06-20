	</div>
<?
# PROGRESS METER

$paths_verified = false;
$database_verified = false;
$config_verified = false;
$mail_verified = false;

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
			<li class="setup">
				<h3>Setup</h3>
				<ul class="subjects">
					<li class="check"><a href="/setup/check">Check system</a></li>
		<?			if(SETUP_TYPE == "setup"): ?>
					<li class="config<?= $config_verified ? " done" : "" ?>"><a href="/setup/config">Janitor configuration</a></li>
		<?			endif; ?>
					<li class="database<?= $database_verified ? " done" : "" ?>"><a href="/setup/database">Setup database</a></li>
					<li class="mail<?= $mail_verified ? " done" : "" ?>"><a href="/setup/mail">Setup mail</a></li>

					<li class="finish"><a href="/setup/finish">Finish installation</a></li>
				</ul>
			</li>
			<li class="upgrade">
				<h3>Upgrade</h3>
				<ul class="subjects">
					<li class="prices"><a href="/upgrade/add-prices-and-vatrates">Add prices and vatrates</a></li>
				</ul>
			</li>
		</ul>
	</div>

	<div id="footer">
		<ul class="servicenavigation">
			<li class="totop"><a href="#header">To top</a></li>
		</ul>

		<p class="copyright">Copyright 2016, parentNode.dk</p>
	</div>

</div>

</body>
</html>