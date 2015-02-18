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
if ( file_exists($GLOBALS['xoops']->path('/Frameworks/moduleclasses/moduleadmin/moduleadmin.php'))) {
    $module_handler =& xoops_gethandler('module');
    $xoopsModule =& XoopsModule::getByDirname('newbb');
    $moduleInfo =& $module_handler->get($xoopsModule->getVar('mid'));
    $pathIcon32 = $moduleInfo->getInfo('icons32');
    $newModuleGui = true;
}


$adminmenu[] = array(
    'title' => _AM_MODULEADMIN_HOME,
    'link'  => 'admin/index.php',
    'icon'  => $pathIcon32 . '/home.png'
);

$adminmenu[] = array(
    'title' => _MI_NEWBB_ADMENU_INDEX,
    'link'  => "admin/index.php",
    'icon'  => ($newModuleGui) ? '../../' . $pathIcon32 . '/home.png' : "assets/images/menu/home.png"
);

$adminmenu[] = array(
    'title' => _MI_NEWBB_ADMENU_CATEGORY,
    'link'  => "admin/admin_cat_manager.php",
    'icon'  => ($newModuleGui) ? '../../' . $pathIcon32 . '/category.png' : "assets/images/menu/cat.png"
);

$adminmenu[] = array(
    'title' => _MI_NEWBB_ADMENU_FORUM,
    'link'  => "admin/admin_forum_manager.php",
    'icon'  => ($newModuleGui) ? '../../' . $pathIcon32 . '/forums.png' : "assets/images/menu/forum.png"
);

$adminmenu[] = array(
    'title' => _MI_NEWBB_ADMENU_PERMISSION,
    'link'  => "admin/admin_permissions.php",
    'icon'  => ($newModuleGui) ? '../../' . $pathIcon32 . '/permissions.png' : "assets/images/menu/permissions.png"
);

$adminmenu[] = array(
    'title' => _MI_NEWBB_ADMENU_ORDER,
    'link'  => "admin/admin_forum_reorder.php",
    'icon'  => ($newModuleGui) ? '../../' . $pathIcon32 . '/compfile.png' : "assets/images/menu/order.png"
);

$adminmenu[] = array(
    'title' => _MI_NEWBB_ADMENU_PRUNE,
    'link'  => "admin/admin_forum_prune.php",
    'icon'  => ($newModuleGui) ? '../../' . $pathIcon32 . '/update.png' : "assets/images/menu/prune.png"
);

$adminmenu[] = array(
    'title' => _MI_NEWBB_ADMENU_REPORT,
    'link'  => "admin/admin_report.php",
    'icon'  => ($newModuleGui) ? '../../' . $pathIcon32 . '/content.png' : "assets/images/menu/report.png"
);

$adminmenu[] = array(
    'title' => _MI_NEWBB_ADMENU_DIGEST,
    'link'  => "admin/admin_digest.php",
    'icon'  => ($newModuleGui) ? '../../' . $pathIcon32 . '/digest.png' : "assets/images/menu/digest.png",
);

$adminmenu[] = array(
    'title' => _MI_NEWBB_ADMENU_VOTE,
    'link'  => "admin/admin_votedata.php",
    'icon'  => ($newModuleGui) ? '../../' . $pathIcon32 . '/button_ok.png' : "assets/images/menu/votedata.png"
);

$adminmenu[] = array(
    'title' => _MI_NEWBB_ADMENU_TYPE,
    'link'  => "admin/admin_type_manager.php",
    'icon'  => ($newModuleGui) ? '../../' . $pathIcon32 . '/type.png' : "assets/images/menu/type.png"
);

$adminmenu[] = array(
    'title' => _MI_NEWBB_ADMENU_GROUPMOD,
    'link'  => "admin/admin_groupmod.php",
    'icon'  => ($newModuleGui) ? '../../' . $pathIcon32 . '/groupmod.png' : "assets/images/menu/groupmod.png"
);


if (!$newModuleGui) {
    $adminmenu[] = array(
        'title' => _MI_NEWBB_ADMENU_BLOCK,
        'link'  => "admin/admin_blocks.php",
        'icon'  => "assets/images/menu/blocks.png"
    );
}


$adminmenu[] = array(
    'title' => _MI_NEWBB_ADMENU_SYNC,
    'link'  => "admin/admin_synchronization.php",
    'icon'  => ($newModuleGui) ? '../../' . $pathIcon32 . '/synchronized.png' : "assets/images/menu/synchronization.png"
);

$adminmenu[] = array(
    'title' => _MI_NEWBB_ADMENU_ABOUT,
    'link'  => "admin/about.php",
    'icon'  => ($newModuleGui) ? '../../' . $pathIcon32 . '/about.png' : "assets/images/menu/about.png"
);
