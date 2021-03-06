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
$template = 'error_edit.tpl';

// dynamic page data.

if (!isset($_GET['eid'])) {
	set_page_message(tr('Server error - please choose error page'), 'error');
	user_goto('error_pages.php');
} else {
	$eid = intval($_GET['eid']);
}

if ($eid == 401 || $eid == 403 || $eid == 404 || $eid == 500 || $eid == 503) {
	gen_error_page_data($tpl, $sql, $_GET['eid']);
} else {
	$tpl->assign(
		array(
			'ERROR' => tr('Server error - please choose error page'),
			'EID' => '0'
		)
	);
}

// static page messages.
gen_logged_from($tpl);

check_permissions($tpl);

$tpl->assign(
	array(
		'TR_PAGE_TITLE'			=> tr('ispCP - Client/Manage Error Custom Pages'),
		'TR_ERROR_EDIT_PAGE'	=> tr('Edit error page'),
		'TR_SAVE'				=> tr('Save'),
		'TR_CANCEL'				=> tr('Cancel'),
		'EID'					=> $eid
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

/**
 * @param ispCP_TemplateEngine $tpl
 * @param ispCP_Database $sql
 * @param int $user_id
 * @param string $eid
 */
function gen_error_page_data($tpl, $sql, $eid) {

	$domain = $_SESSION['user_logged'];

	// Check if we already have an error page
	$vfs = new ispCP_VirtualFileSystem($domain, $sql);
	$error = $vfs->get('/errors/' . $eid . '.html');

	if (false !== $error) {
		// We already have an error page, return it
		$tpl->assign(array('ERROR' => tohtml($error)));
		return;
	}
	// No error page
	$tpl->assign(array('ERROR' => ''));
}
?>