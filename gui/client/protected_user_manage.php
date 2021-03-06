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

$tpl = ispCP_TemplateEngine::getInstance();
$template = 'puser_manage.tpl';

// static page messages
gen_logged_from($tpl);
check_permissions($tpl);

$dmn_id = get_user_domain_id($sql, $_SESSION['user_id']);

gen_pusres($tpl, $sql, $dmn_id);

gen_pgroups($tpl, $sql, $dmn_id);

$tpl->assign(
	array(
		'TR_PAGE_TITLE'	=> tr('ispCP - Client/Webtools'),
		'TR_HTACCESS'			=> tr('Protected areas'),
		'TR_ACTION'				=> tr('Action'),
		'TR_USER_MANAGE'		=> tr('Manage user'),
		'TR_USERS'				=> tr('User'),
		'TR_USERNAME'			=> tr('Username'),
		'TR_ADD_USER'			=> tr('Add user'),
		'TR_GROUPNAME'			=> tr('Group name'),
		'TR_GROUP_MEMBERS'		=> tr('Group members'),
		'TR_ADD_GROUP'			=> tr('Add group'),
		'TR_GROUP'				=> tr('Group'),
		'TR_GROUPS'				=> tr('Groups'),
		'TR_PASSWORD'			=> tr('Password'),
		'TR_STATUS'				=> tr('Status'),
		'TR_PASSWORD_REPEAT'	=> tr('Repeat password'),
		'TR_MESSAGE_DELETE'		=> tr('Are you sure you want to delete %s?', true, '%s')
	)
);

gen_client_mainmenu($tpl, 'main_menu_webtools.tpl');
gen_client_menu($tpl, 'menu_webtools.tpl');

gen_page_message($tpl);

if ($cfg->DUMP_GUI_DEBUG) {
	dump_gui_debug($tpl);
}

$tpl->display($template);

unset_messages();

function gen_user_action($id, $status) {

	$cfg = ispCP_Registry::get('Config');

	if ($status === $cfg->ITEM_OK_STATUS) {
		return array(tr('Delete'), "action_delete('protected_user_delete.php?uname={USER_ID}', '{UNAME}')", tr('Edit'), "protected_user_edit.php?uname={USER_ID}");
	} else {
		return array(tr('N/A'), '', tr('N/A'), '#');
	}
}

function gen_group_action($id, $status, $group) {

	$cfg = ispCP_Registry::get('Config');

	if ($status === $cfg->ITEM_OK_STATUS
		&& $group != $cfg->AWSTATS_GROUP_AUTH) {
		return array(tr('Delete'), "action_delete('protected_group_delete.php?gname={GROUP_ID}', '{GNAME}')");
	} else {
		return array(tr('N/A'), '');
	}
}

/**
 * @param ispCP_TemplateEngine $tpl
 * @param ispCP_Database $sql
 * @param int $dmn_id
 */
function gen_pusres($tpl, $sql, &$dmn_id) {
	$query = "
		SELECT
			*
		FROM
			`htaccess_users`
		WHERE
			`dmn_id` = ?
		ORDER BY
			`dmn_id` DESC
	";

	$rs = exec_query($sql, $query, $dmn_id);

	if ($rs->recordCount() == 0) {
		$tpl->assign(
				array(
					'PUSRES'		=>	'',
					'USER_MESSAGE'	=>	tr('You have no users!'),
					'TABLE_LIST'	=>	''
				)
			);
	} else {
		$tpl->assign('USR_MSG', '');
		while (!$rs->EOF) {
			list($user_delete, $user_delete_script, $user_edit, $user_edit_script) = gen_user_action($rs->fields['id'], $rs->fields['status']);
			$tpl->assign(
				array(
					'UNAME'					=> tohtml($rs->fields['uname']),
					'USTATUS'				=> translate_dmn_status($rs->fields['status']),
					'USER_ID'				=> $rs->fields['id'],
					'USER_DELETE'			=> $user_delete,
					'USER_DELETE_SCRIPT'	=> $user_delete_script,
					'USER_EDIT'				=> $user_edit,
					'USER_EDIT_SCRIPT'		=> $user_edit_script
				)
			);

			$rs->moveNext();

		}
	}
}

/**
 * @param ispCP_TemplateEngine $tpl
 * @param ispCP_Database $sql
 * @param int $dmn_id
 */
function gen_pgroups($tpl, $sql, &$dmn_id) {
	$query = "
		SELECT
			*
		FROM
			`htaccess_groups`
		WHERE
			`dmn_id` = ?
		ORDER BY
			`dmn_id` DESC
	";

	$rs = exec_query($sql, $query, $dmn_id);

	if ($rs->recordCount() == 0) {
		$tpl->assign('GROUP_MESSAGE', tr('You have no groups!'));
		$tpl->assign('PGROUPS', '');
	} else {
		$tpl->assign('GRP_MSG', '');
		while (!$rs->EOF) {

			list($group_delete, $group_delete_script) = gen_group_action($rs->fields['id'], $rs->fields['status'], $rs->fields['ugroup']);
			$tpl->assign(
				array(
					'GNAME'					=> tohtml($rs->fields['ugroup']),
					'GSTATUS'				=> translate_dmn_status($rs->fields['status']),
					'GROUP_ID'				=> $rs->fields['id'],
					'GROUP_DELETE'			=> $group_delete,
					'GROUP_DELETE_SCRIPT'	=> $group_delete_script
				)
			);

			if ($rs->fields['members'] == '') {
				$tpl->assign('GROUP_MEMBERS', '');
			} else {
				$members = explode(',', $rs->fields['members']);

				for ($i = 0, $cnt_members = count($members); $i < $cnt_members; $i++) {
					$query = "
						SELECT
							`uname`
						FROM
							`htaccess_users`
						WHERE
							`id` = ?
					";

					$rs_members = exec_query($sql, $query, $members[$i]);

					if ($cnt_members == 1 || $cnt_members == $i + 1) {
						$tpl->assign('MEMBER', tohtml($rs_members->fields['uname']));
					} else {
						$tpl->assign('MEMBER', tohtml($rs_members->fields['uname']) . ", ");
					}

				}
			}

			$tpl->assign('GROUP_MEMBERS', '');
			$rs->moveNext();
		}
	}
}
?>
