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

if(!$config_verified || !$database_verified || !$mail_verified): ?>
	<h1>Can't finish until you are done</h1>
	<p>You need to provide more information</p>
</div>

<? else: ?>

	<h1>Janitor is almost ready!</h1>
	<h2>Installing</h2>
	<ul class="list tasks">
<?php

$fs = new FileSystem();

$project_path = isset($_SESSION["project_path"]) ? $_SESSION["project_path"] : "";
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


$apache_conf = file_get_contents($project_path."/apache/httpd-vhosts.conf");
if(preg_match("/ServerName (.+)\\n/", $apache_conf, $matches)) {
	$site_url = $matches[1];
}

//define("SITE_DB", $db_janitor_db);
define("SITE_UID", $site_uid);
define("SITE_NAME", $site_name);
define("SITE_URL", $site_url);
define("SITE_EMAIL", $site_email);


if(!file_exists($project_path."/src")) {

	// CREATE FILE STRUCTURE
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

	$fs->makeDirRecursively($project_path."/src/library/private");
	$fs->makeDirRecursively($project_path."/src/library/public");

	$fs->makeDirRecursively($project_path."/src/www/admin/img");
	$fs->makeDirRecursively($project_path."/src/www/admin/js/lib/desktop");
	$fs->makeDirRecursively($project_path."/src/www/admin/css/lib/desktop");


	// COPY TEST FILES
	print '<li>Copying test setup</li>';

	$fs->copy($framework_path."/config/setup/www", $local_path."/www");
	$fs->copy($framework_path."/config/setup/templates", $local_path."/templates");

	copy($framework_path."/templates/admin/post/new.php", $local_path."/templates/admin/post/new.php");
	copy($framework_path."/templates/admin/post/edit.php", $local_path."/templates/admin/post/edit.php");
	copy($framework_path."/templates/admin/post/list.php", $local_path."/templates/admin/post/list.php");

	copy($framework_path."/config/db/items/item_post.sql", $local_path."/config/db/item_post.sql");
	copy($framework_path."/config/db/items/item_post_mediae.sql", $local_path."/config/db/item_post_mediae.sql");

	copy($framework_path."/class/items/type.post.class.php", $local_path."/class/items/type.post.class.php");

}






//print $local_path;


// CREATE CONF FILES
print '<li>Creating config files</li>';

// CONFIG
$file_config = file_get_contents($framework_path."/config/setup/config/config.template.php");
$file_config = preg_replace("/###SITE_UID###/", $site_uid, $file_config);
$file_config = preg_replace("/###SITE_NAME###/", $site_name, $file_config);
$file_config = preg_replace("/###SITE_URL###/", $site_url, $file_config);
$file_config = preg_replace("/###SITE_EMAIL###/", $site_email, $file_config);
file_put_contents($local_path."/config/config.php", $file_config);

// DATABASE
$file_db = file_get_contents($framework_path."/config/setup/config/connect_db.template.php");
$file_db = preg_replace("/###SITE_DB###/", $db_janitor_db, $file_db);
$file_db = preg_replace("/###HOST###/", $db_host, $file_db);
$file_db = preg_replace("/###USERNAME###/", $db_janitor_user, $file_db);
$file_db = preg_replace("/###PASSWORD###/", $db_janitor_pass, $file_db);
file_put_contents($local_path."/config/connect_db.php", $file_db);

// MAIL
$file_mail = file_get_contents($framework_path."/config/setup/config/connect_mail.template.php");
$file_mail = preg_replace("/###HOST###/", $mail_host, $file_mail);
$file_mail = preg_replace("/###PORT###/", $mail_port, $file_mail);
$file_mail = preg_replace("/###USERNAME###/", $mail_username, $file_mail);
$file_mail = preg_replace("/###PASSWORD###/", $mail_password, $file_mail);
$file_mail = preg_replace("/###SITE_NAME###/", $site_name, $file_mail);
$file_mail = preg_replace("/###SITE_EMAIL###/", $site_email, $file_mail);
file_put_contents($local_path."/config/connect_mail.php", $file_mail);

// APACHE
$file_mail = file_get_contents($framework_path."/config/setup/config/httpd-vhosts.template.conf");
$file_mail = preg_replace("/###LOCAL_PATH###/", $local_path, $file_mail);
$file_mail = preg_replace("/###FRAMEWORK_PATH###/", $framework_path, $file_mail);
$file_mail = preg_replace("/###PROJECT_PATH###/", $project_path, $file_mail);
$file_mail = preg_replace("/###SITE_URL###/", $site_url, $file_mail);
$file_mail = preg_replace("/###SITE_NAME###/", $site_name, $file_mail);
file_put_contents($project_path."/apache/httpd-vhosts.conf", $file_mail);



// CREATE DB
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

//$query->sql("USE $db_janitor_db");


// load database and mail configuration
include_once($local_path."/config/connect_db.php");
include_once($local_path."/config/connect_mail.php");


$query->checkDbExistance($db_janitor_db.".items");
$query->checkDbExistance($db_janitor_db.".tags");
$query->checkDbExistance($db_janitor_db.".taggings");






// CREATE LANGUAGE
print '<li>Installing language EN</li>';
$query->checkDbExistance($db_janitor_db.".languages");
$sql = "SELECT id FROM $db_janitor_db.languages WHERE name = 'English'";
if(!$query->sql($sql)) {

	$sql = "INSERT INTO $db_janitor_db.languages set id = 'EN', name = 'English'";
//	print $sql."<br>";
	$query->sql($sql);

}


// CREATE TEST USER
print '<li>Setting up default user</li>';

include_once("class/users/user.class.php");
$UC = new User();

$user_groups = $UC->getUserGroups(array("user_group_id" => 1));
if(!$user_groups) {

	unset($_POST);
	$_POST["user_group"] = "Developer";

	$UC->getPostedEntities();
	$user_group = $UC->saveUserGroup();
}

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
$UC->updateAccess(array("updateAccess", 1));



$email = SITE_EMAIL;
// check for user with this email
$users = $UC->getUsers(array("email" => $email));
if(!$users) {

	unset($_POST);
	$_POST["nickname"] = "Dummy user";
	$_POST["user_group_id"] = 1;
	$_POST["status"] = 1;
	$_POST["language"] = "EN";

	$UC->getPostedEntities();
	$user = $UC->save();
	
	if($user) {
		$user_id = $user["item_id"];
	}
}
else {
	$user_id = $users[0]["user_id"];
}

if($user_id) {

	$UC->status(array("status", $user_id, 1));

	// SET USERNAME
	unset($_POST);
	$_POST["email"] = $email;
	$UC->getPostedEntities();
	$UC->updateUsernames(array("updateUsernames", $user_id));

	// SET PASSWORD
	unset($_POST);
	$_POST["password"] = "rotinaj";
	$UC->getPostedEntities();
	$UC->setPAssword(array("setPassword", $user_id));

	// store user_id for content creation
	session()->value("user_id", $user_id);
}


// CREATE TEST CONTENT
print '<li>Creating test content</li>';

include_once("class/items/item.core.class.php");
include_once("class/items/item.class.php");
$IC = new Item();

unset($_POST);
$_POST["name"] = "Welcome to the basement";
$_POST["html"] = "<p>This is a test post</p>";
$_POST["status"] = 1;
$IC->saveItem("post");


// get apache user to set permissions
$current_user = get_current_user();
$apache_user = trim(shell_exec('whoami'));

session_unset();

mail(array("subject" => "Welcome to janitor", "message" => "Your Janitor project is ready.\n\nLog in to your admin system: http://".SITE_URL."/admin\n\nUsername: ".SITE_EMAIL."\nPassword: rotinaj\n\nSee you soon,\n\nJanitor"));

?>
	</ul>
	<h2>Final touches</h2>
	<p>
		To finish setup you need to set file permissions on your project and restart Apache.
	</p>
	<p>Copy this into your terminal to set file permissions.</p>
	<code>sudo chown -R <?= $current_user ?> <?= $project_path ?> &&
sudo chmod -R 755 <?= $project_path ?> &&

sudo chown -R <?= $current_user ?>:<?= $apache_user ?> <?= $project_path ?>/src/www/js &&
sudo chmod -R 770 <?= $project_path ?>/src/www/js &&

sudo chown -R <?= $current_user ?>:<?= $apache_user ?> <?= $project_path ?>/src/www/admin/js &&
sudo chmod -R 770 <?= $project_path ?>/src/www/admin/js &&

sudo chown -R kaestel:_www /Users/kaestel/Sites/clients/janitor_test/src/www/css &&
sudo chmod -R 770 <?= $project_path ?>/src/www/css &&

sudo chown -R kaestel:_www /Users/kaestel/Sites/clients/janitor_test/src/www/admin/css &&
sudo chmod -R 770 <?= $project_path ?>/src/www/admin/css &&

sudo chown -R <?= $current_user ?>:<?= $apache_user ?> <?= $project_path ?>/src/library &&
sudo chmod -R 770 <?= $project_path ?>/src/library</code>

	<h2>Relaunch your Janitor project</h2>
	<p>When you are done you can click the bottom below to relaunch your Janitor project.</p>
	<ul class="actions">
		<li class="finalize"><a href="/setup/paths" class="button primary">Finalize setup</a></li>
	</ul>

</div>
<? endif; ?>