<?php
/**
 * ispCP ω (OMEGA) complete domain backup/restore tool
 * Restore application
 *
 * @copyright 	2010 Thomas Wacker
 * @author 		Thomas Wacker <zuhause@thomaswacker.de>
 *
 * @license
 * The contents of this file are subject to the Mozilla Public License
 * Version 1.1 (the "License"); you may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.mozilla.org/MPL/
 *
 * Software distributed under the License is distributed on an "AS IS"
 * basis, WITHOUT WARRANTY OF ANY KIND, either express or implied. See the
 * License for the specific language governing rights and limitations
 * under the License.
 */

require_once dirname(__FILE__).'/includes/boot.php';
require_once dirname(__FILE__).'/includes/RestorePackage_ispCP.php';

if ($argc < 3) {
	echo "Usage: php restore.php [OPTIONS] domain archive-password\n";
	echo "OPTIONS:\n";
	echo " -i IP ........ use IP for domain (default = first found)\n";
	echo " -r RES ....... use reseller for domain (default = first found)\n";
	echo " -v ........... verbose mode\n";
	echo "\n";
	echo "Please ensure, there is enough free disk space available for\n";
	echo "this operation (approx. triple size of htdocs and databases)!\n";
	exit(1);
}

$verbose = $option_ip = $option_res = false;
for ($i = 1; $i < $argc-1; $i++) {
	if ($argv[$i] == '-v') {
		$verbose = true;
	} elseif ($argv[$i] == '-i') {
		$option_ip = $argv[++$i];
	} elseif ($argv[$i] == '-r') {
		$option_res = $argv[++$i];
	}
}

$domain_name = $argv[$argc-2];
$password = $argv[$argc-1];

$exitcode = 0;
$handler = new RestorePackage_ispCP($domain_name, $password, $option_ip, $option_res);
$handler->verbose = $verbose;
if ($handler->runRestore() == false) {
	echo "Error executing restore: ";
	$msgs = $handler->getErrorMessages();
	foreach ($msgs as $msg) {
		echo $msg."\n";
	}
	$exitcode = 9;
}

// clean up temp path on exit
if (file_exists(BACKUP_TEMP_PATH)) {
	delTree(BACKUP_TEMP_PATH);
}

exit($exitcode);
