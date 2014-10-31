<div class="scene finish i:finish">
	
<?

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

if(
	(!$config_verified && SETUP_TYPE != "init") || 
	(!$database_verified && (!isset($_SESSION["db_ok"]) || !$_SESSION["db_ok"])) || 
	(!$mail_verified && (!isset($_SESSION["mail_ok"]) || !$_SESSION["mail_ok"]))
): ?>
	<h1>Can't finish until you are done</h1>
	
	<p>You need to provide more information</p>
</div>

<? else: ?>

	<h1>Janitor is almost ready!</h1>
	<h2>Installing</h2>
	<ul class="list tasks">
<?php

$fs = new FileSystem();



//$project_path = isset($_SESSION["project_path"]) ? $_SESSION["project_path"] : "";
$project_path = PROJECT_PATH;
$local_path =  $project_path."/src";
$framework_path = $project_path."/submodules/janitor/src";
//chmod($project_path, 0777);


$db_host = isset($_SESSION["db_host"]) ? $_SESSION["db_host"] : "";
$db_root_user = isset($_SESSION["db_root_user"]) ? $_SESSION["db_root_user"] : "";
$db_root_pass = isset($_SESSION["db_root_pass"]) ? $_SESSION["db_root_pass"] : "";
$db_janitor_db = isset($_SESSION["db_janitor_db"]) ? $_SESSION["db_janitor_db"] : "";
$db_janitor_user = isset($_SESSION["db_janitor_user"]) ? $_SESSION["db_janitor_user"] : "";
$db_janitor_pass = isset($_SESSION["db_janitor_pass"]) ? $_SESSION["db_janitor_pass"] : "";

$site_uid = isset($_SESSION["site_uid"]) ? $_SESSION["site_uid"] : "";
$site_name = isset($_SESSION["site_name"]) ? $_SESSION["site_name"] : "";
//$site_url = isset($_SESSION["site_url"]) ? $_SESSION["site_url"] : "";
$site_email = isset($_SESSION["site_email"]) ? $_SESSION["site_email"] : "";


$mail_host = isset($_SESSION["mail_host"]) ? $_SESSION["mail_host"] : "";
$mail_port = isset($_SESSION["mail_port"]) ? $_SESSION["mail_port"] : "";
$mail_username = isset($_SESSION["mail_username"]) ? $_SESSION["mail_username"] : "";
$mail_password = isset($_SESSION["mail_password"]) ? $_SESSION["mail_password"] : "";

// apache BIN path
$apache_path = isset($_SESSION["apache_path"]) ? $_SESSION["apache_path"] : "apachectl";

$apache_conf = file_get_contents($project_path."/apache/httpd-vhosts.conf");
if(preg_match("/ServerName (.+)\\n/", $apache_conf, $matches)) {
	$site_url = $matches[1];
}

//define("SITE_DB", $db_janitor_db);
define("SITE_UID", $site_uid);
define("SITE_NAME", $site_name);
define("SITE_URL", $site_url);
define("SITE_EMAIL", $site_email);


//
// CREATE FOLDER STRUCTURE
//
if(SETUP_TYPE == "setup" && !file_exists($project_path."/src")) {

	// create file structure
	print '<li>Creating folders</li>';

	$fs->makeDirRecursively($project_path."/src/www/img");
	$fs->makeDirRecursively($project_path."/src/www/js/lib/desktop");
	$fs->makeDirRecursively($project_path."/src/www/css/lib/desktop");
	$fs->makeDirRecursively($project_path."/src/www/admin/img");
	$fs->makeDirRecursively($project_path."/src/www/admin/js/lib");
	$fs->makeDirRecursively($project_path."/src/www/admin/css/lib");

	$fs->makeDirRecursively($project_path."/src/config/db");

	$fs->makeDirRecursively($project_path."/src/class/items");

	$fs->makeDirRecursively($project_path."/src/templates/admin/post");

	$fs->makeDirRecursively($project_path."/src/www/admin/img");
	$fs->makeDirRecursively($project_path."/src/www/admin/js/lib/desktop");
	$fs->makeDirRecursively($project_path."/src/www/admin/css/lib/desktop");


	// copy test files
	print '<li>Copying files</li>';

	$fs->copy($framework_path."/setup/defaults/www", $local_path."/www");
	$fs->copy($framework_path."/setup/defaults/templates", $local_path."/templates");

	copy($framework_path."/templates/admin/post/new.php", $local_path."/templates/admin/post/new.php");
	copy($framework_path."/templates/admin/post/edit.php", $local_path."/templates/admin/post/edit.php");
	copy($framework_path."/templates/admin/post/list.php", $local_path."/templates/admin/post/list.php");

	copy($framework_path."/config/db/items/item_post.sql", $local_path."/config/db/item_post.sql");
	copy($framework_path."/config/db/items/item_post_mediae.sql", $local_path."/config/db/item_post_mediae.sql");

	copy($framework_path."/class/items/type.post.class.php", $local_path."/class/items/type.post.class.php");

}


//
// LIBRARY
//
if(!file_exists($project_path."/src/library")) {

	// create library
	print '<li>Create library</li>';

	// copy library including dummy images in 0/
	$fs->copy($framework_path."/setup/defaults/library", $local_path."/library");
	
}
// always make sure public and private folder exists
$fs->makeDirRecursively($project_path."/src/library/private");
$fs->makeDirRecursively($project_path."/src/library/public");


//
// CREATE CONFIG FILES
//
if(SETUP_TYPE == "setup") {
	
	// create conf files
	print '<li>Creating config files</li>';

	// config
	$file_config = file_get_contents($framework_path."/setup/defaults/config.template.php");
	$file_config = preg_replace("/###SITE_UID###/", $site_uid, $file_config);
	$file_config = preg_replace("/###SITE_NAME###/", $site_name, $file_config);
	$file_config = preg_replace("/###SITE_URL###/", $site_url, $file_config);
	$file_config = preg_replace("/###SITE_EMAIL###/", $site_email, $file_config);
	file_put_contents($local_path."/config/config.php", $file_config);

	// apache
	$file_mail = file_get_contents($framework_path."/setup/defaults/httpd-vhosts.template.conf");
	$file_mail = preg_replace("/###LOCAL_PATH###/", $local_path, $file_mail);
	$file_mail = preg_replace("/###FRAMEWORK_PATH###/", $framework_path, $file_mail);
	$file_mail = preg_replace("/###PROJECT_PATH###/", $project_path, $file_mail);
	$file_mail = preg_replace("/###SITE_URL###/", $site_url, $file_mail);
	$file_mail = preg_replace("/###SITE_NAME###/", $site_name, $file_mail);
	file_put_contents($project_path."/apache/httpd-vhosts.conf", $file_mail);


}


//
// DATABASE SETUP
//
if(!isset($_SESSION["db_ok"]) || !$_SESSION["db_ok"]) {

	print '<li>Create database configuration</li>';

	// database
	$file_db = file_get_contents($framework_path."/setup/defaults/connect_db.template.php");
	$file_db = preg_replace("/###SITE_DB###/", $db_janitor_db, $file_db);
	$file_db = preg_replace("/###HOST###/", $db_host, $file_db);
	$file_db = preg_replace("/###USERNAME###/", $db_janitor_user, $file_db);
	$file_db = preg_replace("/###PASSWORD###/", $db_janitor_pass, $file_db);
	file_put_contents($local_path."/config/connect_db.php", $file_db);


	// create db
	print '<li>Creating database</li>';

	$mysqli = new mysqli($db_host, $db_root_user, $db_root_pass);
	$mysqli->query("SET NAMES utf8");
	$mysqli->query("SET CHARACTER SET utf8");
	$mysqli->set_charset("utf8");

	global $mysqli_global;
	$mysqli_global = $mysqli;

	$query = new Query();
	$query->sql("CREATE DATABASE $db_janitor_db");

	$query->sql("GRANT ALL PRIVILEGES ON ".$db_janitor_db.".* TO '".$db_janitor_user."'@'".$db_host."' IDENTIFIED BY '".$db_janitor_pass."' WITH GRANT OPTION;");

}


//
// MAIL SETUP
//
if(!isset($_SESSION["mail_ok"]) || !$_SESSION["mail_ok"]) {

	print '<li>Setup mail</li>';

	// mail
	$file_mail = file_get_contents($framework_path."/setup/defaults/connect_mail.template.php");
	$file_mail = preg_replace("/###HOST###/", $mail_host, $file_mail);
	$file_mail = preg_replace("/###PORT###/", $mail_port, $file_mail);
	$file_mail = preg_replace("/###USERNAME###/", $mail_username, $file_mail);
	$file_mail = preg_replace("/###PASSWORD###/", $mail_password, $file_mail);
	$file_mail = preg_replace("/###SITE_NAME###/", $site_name, $file_mail);
	$file_mail = preg_replace("/###SITE_EMAIL###/", $site_email, $file_mail);
	file_put_contents($local_path."/config/connect_mail.php", $file_mail);

}


// load database and mail configuration
include_once($local_path."/config/connect_db.php");
include_once($local_path."/config/connect_mail.php");


//
// VERIFY DATABASE TABLES
//
$query = new Query();
$query->checkDbExistance(SITE_DB.".user_groups");
$query->checkDbExistance(SITE_DB.".languages");
$query->checkDbExistance(SITE_DB.".currencies");
$query->checkDbExistance(SITE_DB.".countries");
$query->checkDbExistance(SITE_DB.".users");

$query->checkDbExistance(SITE_DB.".items");
$query->checkDbExistance(SITE_DB.".tags");
$query->checkDbExistance(SITE_DB.".taggings");



//
// CREATE LANGUAGE
//
$sql = "SELECT id FROM ".SITE_DB.".languages WHERE name = 'English'";
if(!$query->sql($sql)) {

	print '<li>Installing language: EN</li>';
	$sql = "INSERT INTO ".SITE_DB.".languages set id = 'EN', name = 'English'";
	print $sql."<br>";
	$query->sql($sql);

}
else {
	print '<li>Language: OK</li>';
}


//
// CREATE CURRENCY
//
$sql = "SELECT id FROM ".SITE_DB.".currencies WHERE name = 'DKK'";
if(!$query->sql($sql)) {

	print '<li>Installing currency: DKK</li>';
	$sql = "INSERT INTO ".SITE_DB.".currencies set id = 'DKK', name = 'Kroner (Denmark)', abbreviation = 'DKK', abbreviation_position = 'after', decimals = 2, decimal_separator = ',', grouping_separator = '.'";
//	print $sql."<br>";
	$query->sql($sql);

}
else {
	print '<li>Currency: OK</li>';
}


//
// CREATE COUNTRY
//
$sql = "SELECT id FROM ".SITE_DB.".countries WHERE name = 'DK'";
if(!$query->sql($sql)) {

	print '<li>Installing country: DK</li>';
	$sql = "INSERT INTO ".SITE_DB.".countries set id = 'DK', name = 'Danmark', phone_countrycode = '45', phone_format = '#### ####', language = 'DA', currency = 'DKK'";
//	print $sql."<br>";
	$query->sql($sql);

}
else {
	print '<li>Country: OK</li>';
}


//
// CREATE DEFAULT USER GROUPS AND USERS
//
include_once("class/users/user.class.php");
$UC = new User();

$user_groups = $UC->getUserGroups(array("user_group_id" => 1));
if(!$user_groups) {

	print '<li>Create default user groups</li>';

	// Create Guest user group
	unset($_POST);
	$_POST["user_group"] = "Guest";

	$UC->getPostedEntities();
	$user_group = $UC->saveUserGroup(array("saveUserGroup"));

	// Create Member user group
	unset($_POST);
	$_POST["user_group"] = "Member";

	$UC->getPostedEntities();
	$user_group = $UC->saveUserGroup(array("saveUserGroup"));

	// Create Developer user group
	unset($_POST);
	$_POST["user_group"] = "Developer";

	$UC->getPostedEntities();
	$user_group = $UC->saveUserGroup(array("saveUserGroup"));

}
else {
	print '<li>User groups: OK</li>';
}

//
// DEVELOPER PERMISSIONS
//
print '<li>Adding Developer permissions</li>';

// SET ACCESS PERMISSIONS
$access_points = $UC->getAccessPoints();
foreach($access_points["points"] as $path => $access_items) {
	if(!preg_match("/admin\/setup/", $path)) {
		if($access_items) {
			foreach($access_items as $access_item) {
				$grants[$path.$access_item] = 1;
			}
		}
		else {
			$grants[$path."/"] = 1;
		}
	}
}
unset($_POST);
$_POST["grant"] = $grants;
$UC->getPostedEntities();
$UC->updateAccess(array("updateAccess", 3));


// TEST if this allows anonymous user to login
unset($_POST);
$_POST["grant"] = array("/" => 1, "/admin/" => 0);
$UC->getPostedEntities();
$UC->updateAccess(array("updateAccess", 1));




// TODO: if connect_db exists - it will try to log in to DB, but that may not exists
// TODO: final file permissions are wrong

// TODO: copy library/private/0 - done, in test


//
// DEFAULT USERS
//
$email = SITE_EMAIL;

// check for anonymous user
$users = $UC->getUsers(array("user_id" => 1));
if(!$users) {

	print '<li>Create default users</li>';

	// create anonymous user
	unset($_POST);
	$_POST["nickname"] = "Anonymous";
	$_POST["user_group_id"] = 1;
	$_POST["status"] = 1;
	$_POST["language"] = "EN";

	$UC->getPostedEntities();
	$user = $UC->save(array("save"));


	// create developer user
	unset($_POST);
	$_POST["nickname"] = "Dummy developer";
	$_POST["user_group_id"] = 3;
	$_POST["status"] = 1;
	$_POST["language"] = "EN";

	$UC->getPostedEntities();
	$user = $UC->save(array("save"));
	
	if($user) {
		$user_id = $user["item_id"];


		$UC->status(array("status", $user_id, 1));

		// SET USERNAME
		unset($_POST);
		$_POST["email"] = ADMIN_MAIL;
		$UC->getPostedEntities();
		$UC->updateUsernames(array("updateUsernames", $user_id));

		// SET PASSWORD
		unset($_POST);
		$_POST["password"] = "123rotinaj";
		$UC->getPostedEntities();
		$UC->setPAssword(array("setPassword", $user_id));

		// store user_id for content creation
		session()->value("user_id", $user_id);
	}
}
else if($users["nickname"] == "Anonymous") {
	print '<li>Users: OK</li>';
}


include_once("class/items/item.core.class.php");
include_once("class/items/item.class.php");
$IC = new Item();


//
// CREATE TEST CONTENT
//
if(!$IC->getItems() && session()->value("user_id")) {

	print '<li>Creating test content</li>';

	unset($_POST);
	$_POST["name"] = "Welcome to the basement";
	$_POST["html"] = "<p>This is a test post made by the setup script. You can delete this post.</p>";
	$_POST["status"] = 1;
	$IC->saveItem("post");

}



//
// GIT SETTINGS
//
// create git ignore
if(!file_exists(PROJECT_PATH."/.gitignore")) {
	$handle = fopen(PROJECT_PATH."/.gitignore", "w+");
	fwrite($handle, "src/library/*\n.DS_Store\nsrc/config/connect_*.php");
	fclose($handle);
}

// Tell git to ignore file permission changes
exec("cd ".PROJECT_PATH." && git config core.filemode false");
exec("cd ".PROJECT_PATH."/submodules/janitor && git config core.filemode false");
exec("cd ".PROJECT_PATH."/submodules/js-merger && git config core.filemode false");
exec("cd ".PROJECT_PATH."/submodules/css-merger && git config core.filemode false");


// get apache user to set permissions
$current_user = get_current_user();
$apache_user = trim(shell_exec('whoami'));
$deploy_user = trim(shell_exec('egrep -i "^deploy" /etc/group')) ? "deploy" : $current_user;

//print "deploy:" . trim(shell_exec('egrep -i "^deploy" /etc/group')) . ", " . shell_exec('whoami');

//session_unset();

if(SETUP_TYPE == "setup") {
	$this->mail(array("subject" => "Welcome to janitor", "message" => "Your Janitor project is ready.\n\nLog in to your admin system: http://".SITE_URL."/admin\n\nUsername: ".SITE_EMAIL."\nPassword: 123rotinaj\n\nSee you soon,\n\nJanitor"));
}
?>
	</ul>

	<h2>Final touches</h2>
	<p>
		To finish setup you need to set file permissions on your project and restart Apache.
	</p>

	<h3>Production and development projects</h3>
	<p>Copy this into your terminal to set file permissions.</p>
	<code>sudo chown -R <?= $deploy_user ?>:<?= $apache_user ?> <?= $project_path ?> &&
sudo chmod -R 750 <?= $project_path ?> &&

sudo chown -R <?= $deploy_user ?>:<?= $apache_user ?> <?= $project_path ?>/src/library &&
sudo chmod -R 770 <?= $project_path ?>/src/library</code>

	<h3>Development project with JS+CSS merging</h3>
	<p>
		If you are setting up a development environment and need to merge JS+CSS you also need to
		set permissions for JS+CSS folders.
	</p>

	<code>sudo chown -R <?= $deploy_user ?>:<?= $apache_user ?> <?= $project_path ?>/src/www/js &&
sudo chmod -R 770 <?= $project_path ?>/src/www/js &&

sudo chown -R <?= $deploy_user ?>:<?= $apache_user ?> <?= $project_path ?>/src/www/admin/js &&
sudo chmod -R 770 <?= $project_path ?>/src/www/admin/js &&

sudo chown -R <?= $deploy_user ?>:<?= $apache_user ?> <?= $project_path ?>/src/www/css &&
sudo chmod -R 770 <?= $project_path ?>/src/www/css &&

sudo chown -R <?= $deploy_user ?>:<?= $apache_user ?> <?= $project_path ?>/src/www/admin/css &&
sudo chmod -R 770 <?= $project_path ?>/src/www/admin/css</code>

	<h3>Restart Apache</h3>
	<p>Finally, restart your apache by running the following command in Terminal.</p>
	<code>sudo <?= $apache_path ?> restart</code>

	<h2>Relaunch your Janitor project</h2>
	<p>When you are done you can click the bottom below to relaunch your Janitor project.</p>
	<ul class="actions">
		<li class="finalize"><a href="/setup/paths" class="button primary">Finalize setup</a></li>
	</ul>

</div>
<? endif; ?>