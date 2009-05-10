<?php
/**
 * ispCP ω (OMEGA) a Virtual Hosting Control System
 *
 * @copyright	2001-2006 by moleSoftware GmbH
 * @copyright	2006-2009 by ispCP | http://isp-control.net
 * @version		SVN: $Id: alias_order_delete.php 1744 2009-05-07 03:21:47Z haeber $
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

/**
 * @todo use db prepared statements
 */

require '../include/ispcp-lib.php';

check_login(__FILE__);

$theme_color = Config::get('USER_INITIAL_THEME');

if (UserIO::GET_Int('del_id') > 0) {
	$del_id = UserIO::GET_Int('del_id');
} else {
	$_SESSION['orderaldel'] = '_no_';
	user_goto('domains_manage.php');
}

$query = "DELETE FROM `domain_aliasses` WHERE `alias_id` = '" . $del_id . "'";
$rs = exec_query($sql, $query);

user_goto('domains_manage.php');
