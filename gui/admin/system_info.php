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
$template = 'system_info.tpl';

$sysinfo = new ispCP_SystemInfo();

$tpl->assign(
	array(
		'CPU_MODEL'				=> tohtml($sysinfo->cpu['model']),
		'CPU_COUNT'				=> tohtml($sysinfo->cpu['cpus']),
		'CPU_MHZ'				=> tohtml($sysinfo->cpu['cpuspeed']),
		'CPU_CACHE'				=> tohtml($sysinfo->cpu['cache']),
		'CPU_BOGOMIPS'			=> tohtml($sysinfo->cpu['bogomips']),
		'UPTIME'				=> tohtml($sysinfo->uptime),
		'KERNEL'				=> tohtml($sysinfo->kernel),
		'LOAD'					=> $sysinfo->load[0] .' '.
									$sysinfo->load[1] .' '.
									$sysinfo->load[2],
		'RAM_TOTAL'				=> sizeit($sysinfo->ram['total'], 'KB'),
		'RAM_USED'				=> sizeit($sysinfo->ram['used'], 'KB'),
		'RAM_FREE'				=> sizeit($sysinfo->ram['free'], 'KB'),
		'SWAP_TOTAL'			=> sizeit($sysinfo->swap['total'], 'KB'),
		'SWAP_USED'				=> sizeit($sysinfo->swap['used'], 'KB'),
		'SWAP_FREE'				=> sizeit($sysinfo->swap['free'], 'KB'),
	)
);

$mount_points = $sysinfo->filesystem;

foreach ($mount_points as $mountpoint) {
		$tpl->append(
			array(
				'MOUNT'		=> tohtml($mountpoint['mount']),
				'TYPE'		=> tohtml($mountpoint['fstype']),
				'PARTITION'	=> tohtml($mountpoint['disk']),
				'PERCENT'	=> $mountpoint['percent'],
				'FREE'		=> sizeit($mountpoint['free'], 'KB'),
				'USED'		=> sizeit($mountpoint['used'], 'KB'),
				'SIZE'		=> sizeit($mountpoint['size'], 'KB'),
			)
		);

}


// static page messages
$tpl->assign(
	array(
		'TR_PAGE_TITLE'			=> tr('ispCP - Virtual Hosting Control System'),
		'TR_CPU_BOGOMIPS'		=> tr('CPU bogomips'),
		'TR_CPU_CACHE'			=> tr('CPU cache'),
		'TR_CPU_COUNT'			=> tr('Number of CPU Cores'),
		'TR_CPU_MHZ'			=> tr('CPU MHz'),
		'TR_CPU_MODEL'			=> tr('CPU model'),
		'TR_CPU_SYSTEM_INFO'	=> tr('CPU system Info'),
		'TR_FILE_SYSTEM_INFO'	=> tr('Filesystem system Info'),
		'TR_FREE'				=> tr('Free'),
		'TR_KERNEL'				=> tr('Kernel Version'),
		'TR_LOAD'				=> tr('Load (1 Min, 5 Min, 15 Min)'),
		'TR_MEMRY_SYSTEM_INFO'	=> tr('Memory system info'),
		'TR_MOUNT'				=> tr('Mount'),
		'TR_RAM'				=> tr('RAM'),
		'TR_PARTITION'			=> tr('Partition'),
		'TR_PERCENT'			=> tr('Percent'),
		'TR_SIZE'				=> tr('Size'),
		'TR_SWAP'				=> tr('Swap'),
		'TR_SYSTEM_INFO_TITLE'	=> tr('System info'),
		'TR_SYSTEM_INFO'		=> tr('Vital system info'),
		'TR_TOTAL'				=> tr('Total'),
		'TR_TYPE'				=> tr('Type'),
		'TR_UPTIME'				=> tr('Up time'),
		'TR_USED'				=> tr('Used'),
	)
);

gen_admin_mainmenu($tpl, 'main_menu_system_tools.tpl');
gen_admin_menu($tpl, 'menu_system_tools.tpl');

gen_page_message($tpl);

if ($cfg->DUMP_GUI_DEBUG) {
	dump_gui_debug($tpl);
}

$tpl->display($template);

unset_messages();
?>
