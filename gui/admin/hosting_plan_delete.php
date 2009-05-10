<?php
/**
 * ispCP ω (OMEGA) a Virtual Hosting Control System
 *
 * @copyright	2001-2006 by moleSoftware GmbH
 * @copyright	2006-2009 by ispCP | http://isp-control.net
 * @version		SVN: $Id: hosting_plan_delete.php 1744 2009-05-07 03:21:47Z haeber $
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

if (strtolower(Config::get('HOSTING_PLANS_LEVEL')) != 'admin') {
	user_goto('index.php');
}


if (UserIO::GET_Int('hpid') != 0) {
	$hpid = UserIO::GET_Int('hpid');
} else {
	$_SESSION['hp_deleted'] = '_no_';
	user_goto('hosting_plan.php');
}

// Check if there is no order for this plan
$res = exec_query($sql, "SELECT COUNT(`id`) FROM `orders` WHERE `plan_id` = ? AND `status` = 'new'", array($hpid));
$data = $res->FetchRow();
if ($data['0'] > 0) {
	$_SESSION['hp_deleted_ordererror'] = '_yes_';
	user_goto('hosting_plan.php');
}

// Try to delete hosting plan from db
$query = 'DELETE FROM `hosting_plans` WHERE `id` = ?';
$res = exec_query($sql, $query, array($hpid));

$_SESSION['hp_deleted'] = '_yes_';

user_goto('hosting_plan.php');
