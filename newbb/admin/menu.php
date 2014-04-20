<?php 
// $Id: menu.php 62 2012-08-17 10:15:26Z alfred $
// ------------------------------------------------------------------------ //
// XOOPS - PHP Content Management System                      //
// Copyright (c) 2000 XOOPS.org                           //
// <http://www.xoops.org/>                             //
// ------------------------------------------------------------------------ //
// This program is free software; you can redistribute it and/or modify     //
// it under the terms of the GNU General Public License as published by     //
// the Free Software Foundation; either version 2 of the License, or        //
// (at your option) any later version.                                      //
// //
// You may not change or alter any portion of this comment or credits       //
// of supporting developers from this source code or any supporting         //
// source code which is considered copyrighted (c) material of the          //
// original comment or credit authors.                                      //
// //
// This program is distributed in the hope that it will be useful,          //
// but WITHOUT ANY WARRANTY; without even the implied warranty of           //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            //
// GNU General Public License for more details.                             //
// //
// You should have received a copy of the GNU General Public License        //
// along with this program; if not, write to the Free Software              //
// Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA //
// ------------------------------------------------------------------------ //
// Author: Kazumi Ono (AKA onokazu)                                          //
// URL: http://www.myweb.ne.jp/, http://www.xoops.org/, http://jp.xoops.org/ //
// Project: The XOOPS Project                                                //
// ------------------------------------------------------------------------- //

$newModuleGui = false;
if ( file_exists($GLOBALS['xoops']->path('/Frameworks/moduleclasses/moduleadmin/moduleadmin.php'))){
    $module_handler =& xoops_gethandler('module');
	$xoopsModule =& XoopsModule::getByDirname('newbb');
	$moduleInfo =& $module_handler->get($xoopsModule->getVar('mid'));
	$pathIcon32 = $moduleInfo->getInfo('icons32');
	$newModuleGui = true;
}

$i=0;
$adminmenu[$i]['title'] = _MI_NEWBB_ADMENU_INDEX;
$adminmenu[$i]['link'] = "admin/index.php";
$adminmenu[$i++]['icon'] = ($newModuleGui) ?  '../../'.$pathIcon32.'/home.png' : "images/menu/home.png";
$adminmenu[$i]['title'] = _MI_NEWBB_ADMENU_CATEGORY;
$adminmenu[$i]['link'] = "admin/admin_cat_manager.php";
$adminmenu[$i++]['icon'] = ($newModuleGui) ? '../../'.$pathIcon32.'/category.png' : "images/menu/cat.png";
$adminmenu[$i]['title'] = _MI_NEWBB_ADMENU_FORUM;
$adminmenu[$i]['link'] = "admin/admin_forum_manager.php";
$adminmenu[$i++]['icon'] = ($newModuleGui) ?  '../../'.$pathIcon32.'/forums.png' : "images/menu/forum.png";
$adminmenu[$i]['title'] = _MI_NEWBB_ADMENU_PERMISSION;
$adminmenu[$i]['link'] = "admin/admin_permissions.php";
$adminmenu[$i++]['icon'] = ($newModuleGui) ?  '../../'.$pathIcon32.'/permissions.png' : "images/menu/permissions.png";
$adminmenu[$i]['title'] = _MI_NEWBB_ADMENU_ORDER;
$adminmenu[$i]['link'] = "admin/admin_forum_reorder.php";
$adminmenu[$i++]['icon'] = ($newModuleGui) ? '../../'.$pathIcon32.'/compfile.png' : "images/menu/order.png";
$adminmenu[$i]['title'] = _MI_NEWBB_ADMENU_PRUNE;
$adminmenu[$i]['link'] = "admin/admin_forum_prune.php";
$adminmenu[$i++]['icon'] = ($newModuleGui) ? '../../'.$pathIcon32.'/update.png' : "images/menu/prune.png";
$adminmenu[$i]['title'] = _MI_NEWBB_ADMENU_REPORT;
$adminmenu[$i]['link'] = "admin/admin_report.php";
$adminmenu[$i++]['icon'] = ($newModuleGui) ? '../../'.$pathIcon32.'/content.png' : "images/menu/report.png";
$adminmenu[$i]['title'] = _MI_NEWBB_ADMENU_DIGEST;
$adminmenu[$i]['link'] = "admin/admin_digest.php";
$adminmenu[$i++]['icon'] = ($newModuleGui) ? '../../'.$pathIcon32.'/digest.png' : "images/menu/digest.png";
$adminmenu[$i]['title'] = _MI_NEWBB_ADMENU_VOTE;
$adminmenu[$i]['link'] = "admin/admin_votedata.php";
$adminmenu[$i++]['icon'] = ($newModuleGui) ? '../../'.$pathIcon32.'/button_ok.png' : "images/menu/votedata.png";
$adminmenu[$i]['title'] = _MI_NEWBB_ADMENU_TYPE;
$adminmenu[$i]['link'] = "admin/admin_type_manager.php";
$adminmenu[$i++]['icon'] = ($newModuleGui) ? '../../'.$pathIcon32.'/type.png' : "images/menu/type.png";
$adminmenu[$i]['title'] = _MI_NEWBB_ADMENU_GROUPMOD;
$adminmenu[$i]['link'] = "admin/admin_groupmod.php";
$adminmenu[$i++]['icon'] = ($newModuleGui) ? '../../'.$pathIcon32.'/groupmod.png' : "images/menu/groupmod.png";
if (!$newModuleGui) {
	$adminmenu[$i]['title'] = _MI_NEWBB_ADMENU_BLOCK;
	$adminmenu[$i]['link'] = "admin/admin_blocks.php";
	$adminmenu[$i++]['icon'] = "images/menu/blocks.png";
}
$adminmenu[$i]['title'] = _MI_NEWBB_ADMENU_SYNC;
$adminmenu[$i]['link'] = "admin/admin_synchronization.php";
$adminmenu[$i++]['icon'] = ($newModuleGui) ? '../../'.$pathIcon32.'/synchronized.png' : "images/menu/synchronization.png";
$adminmenu[$i]['title'] = _MI_NEWBB_ADMENU_ABOUT;
$adminmenu[$i]['link'] = "admin/about.php";
$adminmenu[$i++]['icon'] = ($newModuleGui) ? '../../'.$pathIcon32.'/about.png' : "images/menu/about.png";
?>