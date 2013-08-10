<?php
$access_item = false;

if(isset($read_access) && $read_access) {
	return;
}

include_once($_SERVER["LOCAL_PATH"]."/config/config.php");

$action = $page->access();



function import_database_tables($db_name, $names) {
	global $global_db_name;
	if($db_name) {
//		global $db_hostname, $username, $password;
		$data = null;
		$result = null;
		$status = 'not initialized';
		$command = null;
//		$conn = mysql_connect($db_hostname, $username, $password) OR DIE("No connection to MySQL");
		print '<div class="createdb">';

		if(!mysql_select_db($db_name)) {
			print '<h1>Creating database</h1>';
			mysql_query("GRANT ALL PRIVILEGES ON supersonic.* TO 'supersonic'@'localhost' IDENTIFIED BY 'su9ErS0n' WITH GRANT OPTION;");
			
			mysql_query("CREATE DATABASE `$db_name` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci");
		} 
		if(!mysql_select_db($db_name)) {
			 print '<p class="error">No connection to Database</p>';
		}

		print '</div>';

// 

		mysql_query("SET NAMES utf8");
		mysql_query("SET CHARACTER SET utf8");
	
		// For each database table
		for ($i = 0; $i < count($names); $i++) {

			// skip empty lines
			if($names[$i] && trim($names[$i]) && strpos("#", $names[$i]) !== 0) {
				
				$name = trim($names[$i]);
				
				print '<div class="group">';

				print "<h2>Processing: $db_name/$name</h2>";
				// find file
				if(file_exists($_SERVER["LOCAL_PATH"].'/config/db/' . $name . '.sql')) {
					$filename = $_SERVER["LOCAL_PATH"].'/config/db/' . $name . '.sql';
				}
				else if(file_exists($_SERVER["FRAMEWORK_PATH"].'/config/db/' . $name . '.sql')) {
					$filename = $_SERVER["FRAMEWORK_PATH"].'/config/db/' . $name . '.sql';
				}
				else {
					print "<p>Missing file: $name</p>";
					$filename = "";
				}

				if($filename) {
					
					print "<p>Importing: $filename</p>";

					// Load contents of file
					$data = file($filename);
					//$data = file($folder . $names[$i] . '.sql');
		
					// For each line in file collect commands in temp var
					for ($j = 0; $j < count($data); $j++) {
						if ($data != '') {
							$command .= $data[$j];
						}
					}
		
					// Remove new lines
					$command = str_replace("\n", "", $command);
					$command = str_replace("LOCAL_PATH", LOCAL_PATH, $command);
					$command = str_replace("FRAMEWORK_PATH", FRAMEWORK_PATH, $command);
		
					// Split command at ;
					$all_commands = explode(";", $command);
		
					// Execute commands
					for ($k = 0; $k < count($all_commands); $k++) {
			
						// Ignore comments
						if ($all_commands[$k] != '' && substr($all_commands[$k], 0, 2) != '/*') {
							$result = mysql_query($all_commands[$k]);
							$status = ($result != 1) ? 'bad' : 'ok';
							print '<div class="' . $status . '">' . $all_commands[$k];
							if ($status == 'bad') {
								print '<div class="bad_inner">- ' . mysql_error() . '</div>';
							}
							print '</div>';
						}
			
						$result = null;
						$status = null;
					}
		
					$data = null;
					$command = null;
		 		
				}
		

				print "</div>";

			}
		

		}
	
		//mysql_close();
	}
}

$sql_files = file(LOCAL_PATH."/config/setup/databases");

import_database_tables(SITE_DB, $sql_files);

//print_r($sql_files);

?>