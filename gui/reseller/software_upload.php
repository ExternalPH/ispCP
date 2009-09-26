<?php
/**
 * ispCP ω (OMEGA) a Virtual Hosting Control System
 *
 * @copyright 	2001-2006 by moleSoftware GmbH
 * @copyright 	2006-2007 by ispCP | http://isp-control.net
 * @link 		http://isp-control.net
 * @author 		ispCP Team (2007)
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

check_login(__FILE__, Config::get('PREVENT_EXTERNAL_LOGIN_RESELLER'));

$theme_color = Config::get('USER_INITIAL_THEME');

$tpl = new pTemplate();
$tpl->define_dynamic('page', Config::get('RESELLER_TEMPLATE_PATH') . '/software_upload.tpl');
$tpl->define_dynamic('page_message', 'page');
$tpl->define_dynamic('logged_from', 'page');
$tpl->define_dynamic('list_software', 'page');
$tpl->define_dynamic('no_software_list', 'page');
$tpl->define_dynamic('t_software_support', 'page');

// form data

function formatFilesize($byte) {
	$string = "Byte";
	if($byte>1024) {
		$byte/=1024;
		$string="KB";
	}
	if($byte>1024) {
		$byte/=1024;
		$string="MB";
	}
	if($byte>1024) {
		$byte/=1024;
		$string="GB";
	}
	if(number_format($byte,0)!=$byte) $byte=number_format($byte,2);
	return $byte." ".$string;
}

if (isset($_POST['Button'])) {
	$success = 1;
	if ($_FILES['sw_file']['name'] != '' AND !empty($_POST['sw_wget'])) {
		set_page_message(tr('You have to choose between file-upload and wget-function.'));
		$success = 0;
	} elseif ($_FILES['sw_file']['name'] == '' AND empty($_POST['sw_wget'])) {
		set_page_message(tr('You must select a file to upload/download.'));
		$success = 0;
	} else {
		if ($_FILES['sw_file']['name'] && $_FILES['sw_file']['name'] != "none") {
			if (substr($_FILES['sw_file']['name'], -7) != '.tar.gz') {
				set_page_message(tr('File needs to be a .tar.gz-archive'));
				$success = 0;
			}
			$file = 0;
		} else {
			if (substr($_POST['sw_wget'], -7) != '.tar.gz') {
				set_page_message(tr('File needs to be a .tar.gz-archive'));
				$success = 0;
			}
			$file = 1;
		}
	}
	if ($success == 1) {
		$user_id = $_SESSION['user_id'];
		$upload = 1;
		if($file == 0) {
			$fname = $_FILES['sw_file']['name'];
		} elseif($file == 1) {
			$fname = substr($_POST['sw_wget'], (strrpos($_POST['sw_wget'], '/')+1));
		}
		$filename = substr($fname, 0, -7);
		$extension = substr($fname, -7);
			$query=<<<SQL_QUERY
				INSERT INTO
					`web_software`
				(
					`reseller_id`, `software_name`, `software_version`, `software_type`, `software_db`,
					`software_archive`, `software_prefix`, `software_link`, `software_desc`, `software_status`
				) VALUES (
					?, ?, ?, ?, ?,
					?, ?, ?, ?, ?
				)
SQL_QUERY;
		$rs = exec_query($sql, $query, array($user_id, "waiting_for_input", "waiting_for_input", "waiting_for_input", "0", $filename, "waiting_for_input", "waiting_for_input", "waiting_for_input", "toadd"));
		$sw_id = $sql->Insert_ID();
		if ($file == 0) {
			$dest_dir = Config::get('GUI_SOFTWARE_DIR').'/'.$user_id.'/'.$filename.'-'.$sw_id.$extension;
			if (!is_dir(Config::get('GUI_SOFTWARE_DIR').'/'.$user_id)) {
				@mkdir(Config::get('GUI_SOFTWARE_DIR').'/'.$user_id,0755,true);
			}
			if (!move_uploaded_file($_FILES['sw_file']['tmp_name'], $dest_dir)) {
				// Delete software entry
				$query = "DELETE FROM `web_software` WHERE `software_id` = ?";
				exec_query($sql, $query, array($sw_id));
				$sw_wget = "";
				set_page_message(tr('ERROR: Could not upload file. Max. upload filesize ('.ini_get('upload_max_filesize').'B) reached?'));
				$upload = 0;
			}
		}
		if ($file == 1) {
			$sw_wget = $_POST['sw_wget'];
			$dest_dir = Config::get('GUI_SOFTWARE_DIR').'/'.$user_id.'/'.$filename.'-'.$sw_id.$extension;
			//Filegröße auslesen
   			$parts = parse_url($sw_wget);
   			$connection = fsockopen($parts['host'],80,$errno,$errstr,30);
   			if($connection) {
   				fputs($connection,"GET ".$sw_wget." HTTP/1.1\r\nHost: ".$parts['host']."\r\n\r\n");
   				$size = 0;
   				while(!isset($length) || ($size <= 500 && !feof($connection))) {
   					$tstr = fgets($connection,128);
   					$size += strlen($tstr);
   					if(substr($tstr,0,14) == 'Content-Length') {
   						$length = substr($tstr,15);
   					}
   				}
   				if($length) {
					$remote_file_size = $length;
				} else {
					$remote_file_size = 0;
				}
				$show_remote_file_size = formatFilesize($remote_file_size);
				if($remote_file_size < 1){
					// Delete software entry
					$query = "DELETE FROM `web_software` WHERE `software_id` = ?";
					exec_query($sql, $query, array($sw_id));
					$show_max_remote_filesize = formatFilesize(Config::get('MAX_REMOTE_FILESIZE'));
					set_page_message(tr('ERROR: Your remote filesize ('.$show_remote_file_size.') is lower than 1 Byte. Please check your URL!'));
					$upload = 0;
				} elseif($remote_file_size > Config::get('MAX_REMOTE_FILESIZE')) {
					// Delete software entry
					$query = "DELETE FROM `web_software` WHERE `software_id` = ?";
					exec_query($sql, $query, array($sw_id));
					$show_max_remote_filesize = formatFilesize(Config::get('MAX_REMOTE_FILESIZE'));
					set_page_message(tr('ERROR: Max. remote filesize ('.$show_max_remote_filesize.') is reached. Your remote file ist '.$show_remote_file_size.''));
					$upload = 0;
				} else {
					$remote_file = @file_get_contents($sw_wget);
					if($remote_file) {
						$output_file = fopen($dest_dir,'w+');
						fwrite($output_file,$remote_file);
						fclose($output_file);
					} else {
						// Delete software entry
						$query = "DELETE FROM `web_software` WHERE `software_id` = ?";
						exec_query($sql, $query, array($sw_id));
						set_page_message(tr('ERROR: Remote File not found!'));
						$upload = 0;
					}
				}
   			}else{
				// Delete software entry
				$query = "DELETE FROM `web_software` WHERE `software_id` = ?";
				exec_query($sql, $query, array($sw_id));
				set_page_message(tr('ERROR: Could not upload file. File not found!'));
				$upload = 0;
			}
		}
		if ($upload == 1) {
			$tpl->assign(
					array(
						'VAL_WGET' => ''
					)
				);
			send_request();
			set_page_message(tr('File was successfully uploaded.'));	
		} else {
			$tpl->assign(
					array(
						'VAL_WGET' => $sw_wget
					)
				);
		}
	} else {
		$tpl->assign(
				array(
					'VAL_WGET' => $_POST['sw_wget']
				)
			);

	}
} else {
	$tpl->assign(
			array(
				'VAL_WGET' => ''
			)
		);
}

// Begin function block
function get_avail_software (&$tpl, &$sql, $user_id) {
	$query = <<<SQL_QUERY
			SELECT
				`software_allowed`
			FROM
				`reseller_props`
			WHERE
				`reseller_id` = ?
SQL_QUERY;

    $rs = exec_query($sql, $query, array($user_id));
    $software_allowed = $rs->fields('software_allowed');
	
	if ($software_allowed == 'yes') {
		if (isset($_GET['sortby']) && isset($_GET['order'])) {
			if ($_GET['order'] === "asc" || $_GET['order'] === "desc") {
				if ($_GET['sortby'] === "name") {
					$ordertype = "`software_name` ".$_GET['order'];
				} elseif ($_GET['sortby'] === "status") {
					$ordertype = "`software_active` ".$_GET['order'];
				} elseif ($_GET['sortby'] === "type") {
					$ordertype = "`software_type` ".$_GET['order'];
				} else {
					$ordertype = "`software_active` ASC, `software_type` ASC";
				}
			} else {
				$ordertype = "`software_active` ASC, `software_type` ASC";
			}
		} else {
			$ordertype = "`software_active` ASC, `software_type` ASC";
		}
		$query="SELECT
				`software_id` as id,
				`reseller_id` as resellerid,
				`software_name` as name,
				`software_version` as version,
				`software_desc` as description,
				`software_type` as type,
				`software_active` as swactive,
				`software_archive` as filename,
				`software_status` as swstatus,
				`software_depot` as softwaredepot
			FROM
				`web_software`
			WHERE
				`reseller_id` = ?
			ORDER BY
				".$ordertype;
				
		$rs = exec_query($sql, $query, array($user_id));
		if ($rs->RecordCount() > 0) {
			while(!$rs->EOF) {
				if($rs->fields['swstatus'] == "ok" || $rs->fields['swstatus'] == "ready") {
					if($rs->fields['swstatus'] == "ready") {
						$updatequery = "UPDATE `web_software` set software_status = 'ok' WHERE `software_id` = ?";
						exec_query($sql, $updatequery, array($rs->fields['id']));
						send_new_sw_upload ($user_id,$rs->fields['filename'].".tar.gz",$rs->fields['id']);
						set_page_message(tr('Packet installed successfuly... Awaiting unblocking from admin!'));
					}
					$url = "delete_software.php?id=".$rs->fields['id'];
					
					$query2="SELECT
                                	`domain`.`domain_id` as did,
                                	`domain`.`domain_name` as domain,
                                	`web_software_inst`.`domain_id` as wdid,
                                	`web_software_inst`.`software_id` as sid,
                                	`web_software`.`software_id` as wsid
                        	FROM
                                	`domain`,
									`web_software`,
									`web_software_inst`
							WHERE
									`web_software_inst`.`software_id` = ?
							AND
									`web_software`.`software_id` = `web_software_inst`.`software_id`
							AND
									`domain`.`domain_id` = `web_software_inst`.`domain_id`
                        	";
					$rs2 = exec_query($sql, $query2, array($rs->fields['id']));
					if ($rs2->RecordCount() > 0) {
						$swinstalled_domain = tr('This software is installed on following domain(s):');
						$swinstalled_domain .= "<ul>";
						while(!$rs2->EOF) {
							$swinstalled_domain .= "<li>".$rs2->fields['domain']."</li>";
							$rs2->MoveNext();
						}
						$swinstalled_domain .= "</ul>";
						$tpl->assign(
									array(
										'SW_INSTALLED' => $swinstalled_domain
									)
								);
					} else {
						$tpl->assign(
									array(
										'SW_INSTALLED' => tr('This package was not installed yet')
									)
								);
					}
					
					$tpl->assign(
							array(
								'SW_NAME' => $rs->fields['name'],
								'LINK_COLOR' => '#000000',
								'SW_VERSION' => $rs->fields['version'],
								'SW_DESCRIPTION' => wordwrap($rs->fields['description'],56,"<br />", true),
								'SW_TYPE' => $rs->fields['type'],
								'DELETE' => $url,
								'TR_DELETE' => tr('Delete'),
								'WAITING_SOFTWARE_LIST' => '',
								'SOFTWARE_ICON' => 'delete'
							)
						);
					if ($rs->fields['swactive'] == "0"){
						$tpl->assign(
								array(
									'SW_STATUS' => tr('waiting for activation')
								)
							);
					} 
					elseif ($rs->fields['swactive'] == "1" && $rs->fields['softwaredepot'] == "yes"){
						$tpl->assign(
								array(
									'SW_STATUS' => tr('activated (Softwaredepot)')
								)
							);
					}
					else {
						$tpl->assign(
								array(
									'SW_STATUS' => tr('activated')
								)
							);
					}
				} else {
					if($rs->fields['swstatus'] == "toadd") {
						$tpl->assign(
								array(
									'SW_NAME' => tr('Installing your uploaded packet. Please refresh this site.'),
									'LINK_COLOR' => '#FF0000',
									'SW_VERSION' => '',
									'SW_DESCRIPTION' => tr('After your upload the packet will be installed on your systems.<br />Refresh your site to see the new status!'),
									'SW_TYPE' => '',
									'DELETE' => '',
									'TR_DELETE' => '',
									'SW_STATUS' => tr('installing'),
									'SOFTWARE_ICON' => 'disabled'
								)
							);
					} else {
						$tpl->assign(
								array(
									'SW_NAME' => tr('Failure in softwarepacket. Deleting!'),
									'LINK_COLOR' => '#FF0000',
									'SW_VERSION' => '',
									'SW_DESCRIPTION' => tr('Check your softwarepacket. There is an error inside!<br />Refresh your site to see the new status!'),
									'SW_TYPE' => '',
									'DELETE' => '',
									'TR_DELETE' => '',
									'SW_STATUS' => tr('deleting'),
									'SOFTWARE_ICON' => 'disabled'
								)
							);
						$del_path = Config::get('GUI_SOFTWARE_DIR')."/".$rs->fields['resellerid']."/".$rs->fields['filename']."-".$rs->fields['id'].".tar.gz";
						@unlink($del_path);
						$delete="DELETE FROM `web_software` WHERE `software_id` = ?";
						$res = exec_query($sql, $delete, array($rs->fields['id']));
						set_page_message(tr('Your softwarepacket is corrupt. Please correct it!'));
					}
				}
				$tpl->parse('LIST_SOFTWARE', '.list_software');
				$rs->MoveNext();
			}
			$tpl->assign('NO_SOFTWARE_LIST', '');
		} else {
			$tpl->assign(
					array(
						'NO_SOFTWARE' => tr('You do not have any software uploaded yet'),
						'LIST_SOFTWARE' => ''
					)
				);
			$tpl->parse('NO_SOFTWARE_LIST', '.no_software_list');
		}
		return $rs->RecordCount();
	} else {
		$tpl->assign(
				array(
					'NO_SOFTWARE' => tr('You do not have permissions to upload software yet'),
					'LIST_SOFTWARE' => ''
				)
			);
		$tpl->parse('NO_SOFTWARE_LIST', '.no_software_list');
		return 0;
	}
}

$theme_color = Config::get('USER_INITIAL_THEME');

$tpl->assign(
		array(
			'TR_MANAGE_SOFTWARE_PAGE_TITLE' => tr('ispCP - Software Management'),
			'THEME_COLOR_PATH' => '../themes/'.$theme_color,
			'THEME_CHARSET' => tr('encoding'),
			'ISP_LOGO' => get_logo($_SESSION['user_id'])
		)
	);

$sw_cnt = get_avail_software (&$tpl, &$sql, $_SESSION['user_id']);

$tpl->assign(
		array(
			'TR_UPLOADED_SOFTWARE' => tr('Software available'),
			'TR_SOFTWARE_NAME' => tr('Software-Synonym'),
			'TR_SOFTWARE_VERSION' => tr('Software-Version'),
			'TR_SOFTWARE_STATUS' => tr('Software status'),
			'TR_SOFTWARE_TYPE' => tr('Type'),
			'TR_SOFTWARE_DELETE' => tr('Action'),
			'TR_SOFTWARE_COUNT' => tr('Software total'),
			'TR_SOFTWARE_NUM' => $sw_cnt,
			'TR_UPLOAD_SOFTWARE' => tr('Software upload'),
			'TR_SOFTWARE_DB' => tr('Requires Database?'),
			'TR_SOFTWARE_DB_PREFIX' => tr('Database prefix'),
			'TR_SOFTWARE_HOME' => tr('Link to authors homepage'),
			'TR_SOFTWARE_DESC' => tr('Description'),
			'TR_SOFTWARE_FILE' => tr('Choose file (Max: '.ini_get('upload_max_filesize').'B)'),
			'TR_SOFTWARE_URL' => tr('or remote file (Max: '.formatFilesize(Config::get('MAX_REMOTE_FILESIZE')).')'),
			'TR_UPLOAD_SOFTWARE_BUTTON' => tr('Upload now'),
			'TR_UPLOAD_SOFTWARE_PAGE_TITLE'	=> tr('ispCP - Software management'),
			'TR_MESSAGE_DELETE' => tr('Are you sure you want to delete this package?', true),
			'TR_SOFTWARE_NAME_ASC' => 'software_upload.php?sortby=name&order=asc',
			'TR_SOFTWARE_NAME_DESC' => 'software_upload.php?sortby=name&order=desc',
			'TR_SOFTWARE_TYPE_ASC' => 'software_upload.php?sortby=type&order=asc',
			'TR_SOFTWARE_TYPE_DESC' => 'software_upload.php?sortby=type&order=desc',
			'TR_SOFTWARE_STATUS_ASC' => 'software_upload.php?sortby=status&order=asc',
			'TR_SOFTWARE_STATUS_DESC' => 'software_upload.php?sortby=status&order=desc'
		)
	);

gen_reseller_mainmenu($tpl, Config::get('RESELLER_TEMPLATE_PATH') . '/main_menu_general_information.tpl');
gen_reseller_menu($tpl, Config::get('RESELLER_TEMPLATE_PATH') . '/menu_general_information.tpl');

gen_logged_from($tpl);

get_reseller_software_permission (&$tpl,&$sql,$_SESSION['user_id']);

gen_page_message($tpl);

$tpl->assign('LAYOUT', '');
$tpl->parse('PAGE', 'page');
$tpl->prnt();

if (Config::get('DUMP_GUI_DEBUG')) {
	dump_gui_debug();
}

unset_messages();
?>