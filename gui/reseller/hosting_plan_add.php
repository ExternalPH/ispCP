<?php
/**
 * ispCP ω (OMEGA) a Virtual Hosting Control System
 *
 * @copyright 	2001-2006 by moleSoftware GmbH
 * @copyright 	2006-2011 by ispCP | http://isp-control.net
 * @version 	SVN: $Id$
 * @link 		http://isp-control.net
 * @author 		ispCP Team
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
 *
 * The Original Code is "VHCS - Virtual Hosting Control System".
 *
 * The Initial Developer of the Original Code is moleSoftware GmbH.
 * Portions created by Initial Developer are Copyright (C) 2001-2006
 * by moleSoftware GmbH. All Rights Reserved.
 * Portions created by the ispCP Team are Copyright (C) 2006-2011 by
 * isp Control Panel. All Rights Reserved.
 */

require '../include/ispcp-lib.php';

check_login(__FILE__);

$cfg = ispCP_Registry::get('Config');

if (isset($cfg->HOSTING_PLANS_LEVEL)
	&& $cfg->HOSTING_PLANS_LEVEL === 'admin') {
		user_goto('hosting_plan.php');
}

$tpl = ispCP_TemplateEngine::getInstance();
$template = 'hosting_plan_add.tpl';

// static page messages
gen_logged_from($tpl);

$tpl->assign(
	array(
		'TR_PAGE_TITLE'				=> tr('ispCP - Reseller/Add hosting plan'),
		'TR_ADD_HOSTING_PLAN'		=> tr('Add hosting plan'),
		'TR_HOSTING PLAN PROPS'		=> tr('Hosting plan properties'),
		'TR_TEMPLATE_NAME'			=> tr('Template name'),
		'TR_MAX_SUBDOMAINS'			=> tr('Max subdomains<br><em>(-1 disabled, 0 unlimited)</em>'),
		'TR_MAX_ALIASES'			=> tr('Max aliases<br><em>(-1 disabled, 0 unlimited)</em>'),
		'TR_MAX_MAILACCOUNTS'		=> tr('Mail accounts limit<br><em>(-1 disabled, 0 unlimited)</em>'),
		'TR_MAX_FTP'				=> tr('FTP accounts limit<br><em>(-1 disabled, 0 unlimited)</em>'),
		'TR_MAX_SQL'				=> tr('SQL databases limit<br><em>(-1 disabled, 0 unlimited)</em>'),
		'TR_MAX_SQL_USERS'			=> tr('SQL users limit<br><em>(-1 disabled, 0 unlimited)</em>'),
		'TR_MAX_TRAFFIC'			=> tr('Traffic limit [MB]<br><em>(0 unlimited)</em>'),
		'TR_DISK_LIMIT'				=> tr('Disk limit [MB]<br><em>(0 unlimited)</em>'),
		'TR_PHP'					=> tr('PHP'),
		'TR_CGI'					=> tr('CGI / Perl'),
		'TR_DNS'					=> tr('Allow adding records to DNS zone'),
		'TR_BACKUP'					=> tr('Backup'),
		'TR_BACKUP_DOMAIN'			=> tr('Domain'),
		'TR_BACKUP_SQL'				=> tr('SQL'),
		'TR_BACKUP_FULL'			=> tr('Full'),
		'TR_BACKUP_NO'				=> tr('No'),
		'TR_APACHE_LOGS'			=> tr('Apache logfiles'),
		'TR_AWSTATS'				=> tr('AwStats'),
		'TR_YES'					=> tr('yes'),
		'TR_NO'						=> tr('no'),
		'TR_BILLING_PROPS'			=> tr('Billing Settings'),
		'TR_PRICE'					=> tr('Price'),
		'TR_SETUP_FEE'				=> tr('Setup fee'),
		'TR_VALUE'					=> tr('Currency'),
		'TR_PAYMENT'				=> tr('Payment period'),
		'TR_STATUS'					=> tr('Available for purchasing'),
		'TR_TEMPLATE_DESCRIPTON'	=> tr('Description'),
		'TR_EXAMPLE'				=> tr('(e.g. EUR)'),
		// BEGIN TOS
		'TR_TOS_PROPS'				=> tr('Term Of Service'),
		'TR_TOS_NOTE'				=> tr('<strong>Optional:</strong> Leave this field empty if you do not want term of service for this hosting plan.'),
		'TR_TOS_DESCRIPTION'		=> tr('Text Only'),
		// END TOS
		'TR_ADD_PLAN'				=> tr('Add plan')
	)
);

gen_reseller_mainmenu($tpl, 'main_menu_hosting_plan.tpl');
gen_reseller_menu($tpl, 'menu_hosting_plan.tpl');

if (isset($_POST['uaction']) && ('add_plan' === $_POST['uaction'])) {
	// Process data
	if (check_data_correction($tpl)) {
		save_data_to_db($tpl, $_SESSION['user_id']);
	}

	gen_data_ahp_page($tpl);
} else {
	gen_empty_ahp_page($tpl);
}

gen_page_message($tpl);

list(
	$rsub_max,
	$rals_max,
	$rmail_max,
	$rftp_max,
	$rsql_db_max,
	$rsql_user_max
	) = check_reseller_permissions($_SESSION['user_id'], 'all_permissions');

if ($rsub_max == "-1") $tpl->assign('ALIAS_ADD', '');
if ($rals_max == "-1") $tpl->assign('SUBDOMAIN_ADD', '');
if ($rmail_max == "-1") $tpl->assign('MAIL_ADD', '');
if ($rftp_max == "-1") $tpl->assign('FTP_ADD', '');
if ($rsql_db_max == "-1") $tpl->assign('SQL_DB_ADD', '');
if ($rsql_user_max == "-1") $tpl->assign('SQL_USER_ADD', '');

if ($cfg->DUMP_GUI_DEBUG) {
	dump_gui_debug($tpl);
}

$tpl->display($template);

// Function definitions

/**
 * Generate empty form
 * @param ispCP_TemplateEngine $tpl
 */
function gen_empty_ahp_page($tpl) {
	$cfg = ispCP_Registry::get('Config');

	$tpl->assign(
		array(
			'HP_NAME_VALUE'			=> '',
			'TR_MAX_SUB_LIMITS'		=> '',
			'TR_MAX_ALS_VALUES'		=> '',
			'HP_MAIL_VALUE'			=> '',
			'HP_FTP_VALUE'			=> '',
			'HP_SQL_DB_VALUE'		=> '',
			'HP_SQL_USER_VALUE'		=> '',
			'HP_TRAFF_VALUE'		=> '',
			'HP_PRICE'			=> '',
			'HP_SETUPFEE'			=> '',
			'HP_VALUE'			=> '',
			'HP_PAYMENT'			=> '',
			'HP_DESCRIPTION_VALUE'		=> '',
			'TR_PHP_YES'			=> '',
			'TR_PHP_NO'			=> $cfg->HTML_CHECKED,
			'TR_CGI_YES'			=> '',
			'TR_CGI_NO'			=> $cfg->HTML_CHECKED,
			'VL_BACKUPD'			=> '',
			'VL_BACKUPS'			=> '',
			'VL_BACKUPF'			=> '',
			'VL_BACKUPN'			=> $cfg->HTML_CHECKED,
			'TR_DNS_YES'			=> '',
			'TR_DNS_NO'			=> $cfg->HTML_CHECKED,
			'HP_DISK_VALUE'			=> '',
			'TR_STATUS_YES'			=> $cfg->HTML_CHECKED,
			'TR_STATUS_NO'			=> '',
			'HP_TOS_VALUE'			=> ''
		)
	);

	$tpl->assign('MESSAGE', '');
} // end of gen_empty_hp_page()

/**
 * Show last entered data for new hp
 * @param ispCP_TemplateEngine $tpl
 */
function gen_data_ahp_page($tpl) {
	global $hp_name, $description, $hp_php, $hp_cgi;
	global $hp_sub, $hp_als, $hp_mail;
	global $hp_ftp, $hp_sql_db, $hp_sql_user;
	global $hp_traff, $hp_disk;
	global $price, $setup_fee, $value, $payment, $status;
	global $hp_backup, $hp_dns;
	global $tos;

	$cfg = ispCP_Registry::get('Config');

	$tpl->assign(
		array(
			'HP_NAME_VALUE'			=> tohtml($hp_name),
			'TR_MAX_SUB_LIMITS'		=> tohtml($hp_sub),
			'TR_MAX_ALS_VALUES'		=> tohtml($hp_als),
			'HP_MAIL_VALUE'			=> tohtml($hp_mail),
			'HP_FTP_VALUE'			=> tohtml($hp_ftp),
			'HP_SQL_DB_VALUE'		=> tohtml($hp_sql_db),
			'HP_SQL_USER_VALUE'		=> tohtml($hp_sql_user),
			'HP_TRAFF_VALUE'		=> tohtml($hp_traff),
			'HP_DISK_VALUE'			=> tohtml($hp_disk),
			'HP_DESCRIPTION_VALUE'	=> tohtml($description),
			'HP_PRICE'				=> tohtml($price),
			'HP_SETUPFEE'			=> tohtml($setup_fee),
			'HP_VALUE'				=> tohtml($value),
			'HP_PAYMENT'			=> tohtml($payment),
			'HP_TOS_VALUE'			=> tohtml($tos)
		)
	);

	$tpl->assign(
		array(
			'TR_PHP_YES'	=> ($hp_php == '_yes_') ? $cfg->HTML_CHECKED : '',
			'TR_PHP_NO'		=> ($hp_php == '_no_') ? $cfg->HTML_CHECKED : '',
			'TR_CGI_YES'	=> ($hp_cgi == '_yes_') ? $cfg->HTML_CHECKED : '',
			'TR_CGI_NO'		=> ($hp_cgi == '_no_') ? $cfg->HTML_CHECKED : '',
			'VL_BACKUPD'	=> ($hp_backup == '_dmn_') ? $cfg->HTML_CHECKED : '',
			'VL_BACKUPS'	=> ($hp_backup == '_sql_') ? $cfg->HTML_CHECKED : '',
			'VL_BACKUPF'	=> ($hp_backup == '_full_') ? $cfg->HTML_CHECKED : '',
			'VL_BACKUPN'	=> ($hp_backup == '_no_') ? $cfg->HTML_CHECKED : '',
			'TR_DNS_YES'	=> ($hp_dns == '_yes_') ? $cfg->HTML_CHECKED : '',
			'TR_DNS_NO'		=> ($hp_dns == '_no_') ? $cfg->HTML_CHECKED : '',
			'TR_STATUS_YES'	=> ($status) ? $cfg->HTML_CHECKED : '',
			'TR_STATUS_NO'	=> (!$status) ? $cfg->HTML_CHECKED : ''
		)
	);

} // end of gen_data_ahp_page()

/**
 * Check correction of input data
 * @param ispCP_TemplateEngine $tpl
 */
function check_data_correction($tpl) {
	global $hp_name, $description, $hp_php, $hp_cgi;
	global $hp_sub, $hp_als, $hp_mail;
	global $hp_ftp, $hp_sql_db, $hp_sql_user;
	global $hp_traff, $hp_disk;
	global $price, $setup_fee, $value, $payment, $status;
	global $hp_backup, $hp_dns;
	global $tos;

	$ahp_error 		= array();

	$hp_name		= clean_input($_POST['hp_name']);
	$hp_sub			= clean_input($_POST['hp_sub']);
	$hp_als			= clean_input($_POST['hp_als']);
	$hp_mail		= clean_input($_POST['hp_mail']);
	$hp_ftp			= clean_input($_POST['hp_ftp']);
	$hp_sql_db		= clean_input($_POST['hp_sql_db']);
	$hp_sql_user	= clean_input($_POST['hp_sql_user']);
	$hp_traff		= clean_input($_POST['hp_traff']);
	$hp_disk		= clean_input($_POST['hp_disk']);
	$value			= clean_input($_POST['hp_value']);
	$payment		= clean_input($_POST['hp_payment']);
	$status			= $_POST['status'];
	$description	= clean_input($_POST['hp_description']);
	$tos			= clean_input($_POST['hp_tos']);

	if (empty($_POST['hp_price'])) {
		$price = 0;
	} else {
		$price = clean_input($_POST['hp_price']);
	}

	if (empty($_POST['hp_setupfee'])) {
		$setup_fee = 0;
	} else {
		$setup_fee = clean_input($_POST['hp_setupfee']);
	}

	if (isset($_POST['php'])) {
		$hp_php = $_POST['php'];
	}

	if (isset($_POST['cgi'])) {
		$hp_cgi = $_POST['cgi'];
	}

	if (isset($_POST['dns'])) {
		$hp_dns = $_POST['dns'];
	}

	if (isset($_POST['backup'])) {
		$hp_backup = $_POST['backup'];
	}

	if ($hp_name == '') {
		$ahp_error[] = tr('Incorrect template name length!');
	}
	if ($description == '') {
		$ahp_error[] = tr('Incorrect template description length!');
	}
	if (!is_numeric($price)) {
		$ahp_error[] = tr('Price must be a number!');
	}
	if (!is_numeric($setup_fee)) {
		$ahp_error[] = tr('Setup fee must be a number!');
	}

	list(
		$rsub_max,
		$rals_max,
		$rmail_max,
		$rftp_max,
		$rsql_db_max,
		$rsql_user_max
		) = check_reseller_permissions($_SESSION['user_id'], 'all_permissions');

	if ($rsub_max == "-1") {
		$hp_sub = "-1";
	} elseif (!ispcp_limit_check($hp_sub, -1)) {
		$ahp_error[] = tr('Incorrect subdomains limit!');
	}

	if ($rals_max == "-1") {
		$hp_als = "-1";
	} elseif (!ispcp_limit_check($hp_als, -1)) {
		$ahp_error[] = tr('Incorrect aliases limit!');
	}

	if ($rmail_max == "-1") {
		$hp_mail = "-1";
	} elseif (!ispcp_limit_check($hp_mail, -1)) {
		$ahp_error[] = tr('Incorrect mail accounts limit!');
	}

	if ($rftp_max == "-1") {
		$hp_ftp = "-1";
	} elseif (!ispcp_limit_check($hp_ftp, -1)) {
		$ahp_error[] = tr('Incorrect FTP accounts limit!');
	}

	if ($rsql_db_max == "-1") {
		$hp_sql_db = "-1";
	} elseif (!ispcp_limit_check($hp_sql_db, -1)) {
		$ahp_error[] = tr('Incorrect SQL users limit!');
	} else if ($hp_sql_user != -1 && $hp_sql_db == -1) {
		$ahp_error[] = tr('SQL users limit is <em>disabled</em>!');
	}

	if ($rsql_user_max == "-1") {
		$hp_sql_user = "-1";
	} elseif (!ispcp_limit_check($hp_sql_user, -1)) {
		$ahp_error[] = tr('Incorrect SQL databases limit!');
	} else if ($hp_sql_user == -1 && $hp_sql_db != -1) {
		$ahp_error[] = tr('SQL databases limit is not <em>disabled</em>!');
	}

	if (!ispcp_limit_check($hp_traff, null)) {
		$ahp_error[] = tr('Incorrect traffic limit!');
	}
	if (!ispcp_limit_check($hp_disk, null)) {
		$ahp_error[] = tr('Incorrect disk quota limit!');
	}

	if (empty($ahp_error)) {
		$tpl->assign('MESSAGE', '');
		return true;
	} else {
		set_page_message(format_message($ahp_error), 'error');
		return false;
	}
} // end of check_data_correction()

/**
 * Add new host plan to DB
 * @param ispCP_TemplateEngine $tpl
 * @param int $admin_id
 */
function save_data_to_db($tpl, $admin_id) {
	global $hp_name, $description, $hp_php, $hp_cgi;
	global $hp_sub, $hp_als, $hp_mail;
	global $hp_ftp, $hp_sql_db, $hp_sql_user;
	global $hp_traff, $hp_disk;
	global $price, $setup_fee, $value, $payment, $status;
	global $hp_backup, $hp_dns;
	global $tos;

	$sql = ispCP_Registry::get('Db');
	$err_msg = '';

	$query = "SELECT `id` FROM `hosting_plans` WHERE `name` = ? AND `reseller_id` = ?";
	$res = exec_query($sql, $query, array($hp_name, $admin_id));

	if ($res->rowCount() == 1) {
		$tpl->assign('MESSAGE', tr('Hosting plan with entered name already exists!'));
	} else {
		$hp_props = "$hp_php;$hp_cgi;$hp_sub;$hp_als;$hp_mail;$hp_ftp;$hp_sql_db;$hp_sql_user;$hp_traff;$hp_disk;$hp_backup;$hp_dns";
		// this id is just for fake and is not used in reseller_limits_check.
		$hpid = 0;

		if (reseller_limits_check($sql, $err_msg, $admin_id, $hpid, $hp_props)) {
			if (!empty($err_msg)) {
				set_page_message($err_msg, 'error');
				return false;
			} else {
				$query = "
					INSERT INTO
						`hosting_plans`(
							`reseller_id`,
							`name`,
							`description`,
							`props`,
							`price`,
							`setup_fee`,
							`value`,
							`payment`,
							`status`,
							`tos`
						)
					VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
				";

				exec_query($sql, $query, array($admin_id, $hp_name, $description, $hp_props, $price, $setup_fee, $value, $payment, $status, $tos));

				$_SESSION['hp_added'] = '_yes_';
				user_goto('hosting_plan.php');
			}
		} else {
			set_page_message(
				tr("Hosting plan values exceed reseller maximum values!"),
				'warning'
			);
			return false;
		}
	}
} // end of save_data_to_db()
?>