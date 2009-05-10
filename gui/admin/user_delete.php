<?php
/**
 * ispCP ω (OMEGA) a Virtual Hosting Control System
 *
 * @copyright	2001-2006 by moleSoftware GmbH
 * @copyright	2006-2009 by ispCP | http://isp-control.net
 * @version		SVN: $Id: user_delete.php 1744 2009-05-07 03:21:47Z haeber $
 * @link		http://isp-control.net
 * @author		ispCP Team
 *
 * @license
 *   This program is free software; you can redistribute it and/or modify it under
 *   the terms of the MPL General Public License as published by the Free Software
 *   Foundation; either version 1.1 of the License, or (at your option) any later
 *   version.
 *   You should have received a copy of the MPL Mozilla Public License along with
 *   this program; if not, write to the Open Source Initiative (OSI)
 *   http://opensource.org | osi@opensource.org
 */

require '../include/ispcp-lib.php';

check_login(__FILE__);

/* Do we have a proper delete_id? */
if (UserIO::GET_Int('delete_id') == 0) {
	user_goto('manage_users.php');
}

$delete_id = UserIO::GET_Int('delete_id');

$query = "SELECT `admin_type` FROM `admin` WHERE `admin_id` = ?";

$rs = exec_query($sql, $query, array($delete_id));

$local_admin_type = $rs->fields['admin_type'];

if ($local_admin_type == 'admin' || $local_admin_type == 'reseller') {
	$query = "SELECT COUNT(`admin_id`) AS children FROM `admin` WHERE `created_by` = ?";
	$rs = exec_query($sql, $query, array($delete_id));
	if ($rs->fields['children'] > 0) {
		/* this user have domain ! */
		$hdomain = 1;
		$_SESSION['hdomain'] = 1;
		user_goto('manage_users.php');
	}
}

if ($local_admin_type == 'admin') {
	$query = "DELETE FROM `email_tpls` WHERE `owner_id` = ? AND `name` = 'add-user-auto-msg'";
	$rs = exec_query($sql, $query, array($delete_id));

	remove_users_common_properties($delete_id);

} else if ($local_admin_type == 'reseller') {
	$query = "DELETE FROM `email_tpls` WHERE `owner_id` = ? AND `name` = 'add-user-auto-msg'";
	$rs = exec_query($sql, $query, array($delete_id));

	$query = "DELETE FROM `reseller_props` WHERE `reseller_id` = ?";
	$rs = exec_query($sql, $query, array($delete_id));

	// delete orders
	$query = "DELETE FROM `orders` WHERE `user_id` = ?";
	$rs = exec_query($sql, $query, array($delete_id));

	// delete orders settings
	$query = "DELETE FROM `orders_settings` WHERE `user_id` = ?";
	$rs = exec_query($sql, $query, array($delete_id));

	$query = "DELETE FROM `hosting_plans` WHERE `reseller_id` = ?";
	$rs = exec_query($sql, $query, array($delete_id));

	remove_users_common_properties($delete_id);

} else if ($local_admin_type == 'user') {
	rm_rf_user_account($delete_id);
	check_for_lock_file();
	send_request();
}

$user_logged= $_SESSION['user_logged'];
$local_admin_name = UserIO::GET_String('delete_username');
write_log("$user_logged: deletes user $local_admin_name, $local_admin_type, $delete_id!");
$_SESSION['user_deleted'] = 1;

user_goto('manage_users.php');
