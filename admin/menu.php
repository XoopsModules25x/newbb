<?php
//
// ------------------------------------------------------------------------ //
// XOOPS - PHP Content Management System                      //
// Copyright (c) 2000-2016 XOOPS.org                           //
// <https://xoops.org/>                             //
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
// URL: http://www.myweb.ne.jp/, https://xoops.org/, http://jp.xoops.org/ //
// Project: XOOPS Project                                                    //
// ------------------------------------------------------------------------- //

use XoopsModules\Newbb;

// require_once __DIR__ . '/../class/Helper.php';
//require_once __DIR__ . '/../include/common.php';
$helper = Newbb\Helper::getInstance();

$pathIcon32    = \Xmf\Module\Admin::menuIconPath('');
$pathModIcon32 = $helper->getModule()->getInfo('modicons32');

$adminmenu[] = [
    'title' => _MI_NEWBB_ADMENU_INDEX,
    'link'  => 'admin/index.php',
    'icon'  => $pathIcon32 . 'home.png'
];

$adminmenu[] = [
    'title' => _MI_NEWBB_ADMENU_CATEGORY,
    'link'  => 'admin/admin_cat_manager.php',
    'icon'  => $pathIcon32 . 'category.png'
];

$adminmenu[] = [
    'title' => _MI_NEWBB_ADMENU_FORUM,
    'link'  => 'admin/admin_forum_manager.php',
    'icon'  => $pathIcon32 . 'forums.png'
];

$adminmenu[] = [
    'title' => _MI_NEWBB_ADMENU_PERMISSION,
    'link'  => 'admin/admin_permissions.php',
    'icon'  => $pathIcon32 . 'permissions.png'
];

$adminmenu[] = [
    'title' => _MI_NEWBB_ADMENU_ORDER,
    'link'  => 'admin/admin_forum_reorder.php',
    'icon'  => $pathIcon32 . 'compfile.png'
];

$adminmenu[] = [
    'title' => _MI_NEWBB_ADMENU_PRUNE,
    'link'  => 'admin/admin_forum_prune.php',
    'icon'  => $pathIcon32 . 'update.png'
];

$adminmenu[] = [
    'title' => _MI_NEWBB_ADMENU_REPORT,
    'link'  => 'admin/admin_report.php',
    'icon'  => $pathIcon32 . 'content.png'
];

$adminmenu[] = [
    'title' => _MI_NEWBB_ADMENU_DIGEST,
    'link'  => 'admin/admin_digest.php',
    'icon'  => $pathIcon32 . 'digest.png'
];

$adminmenu[] = [
    'title' => _MI_NEWBB_ADMENU_VOTE,
    'link'  => 'admin/admin_votedata.php',
    'icon'  => $pathIcon32 . 'button_ok.png'
];

$adminmenu[] = [
    'title' => _MI_NEWBB_ADMENU_TYPE,
    'link'  => 'admin/admin_type_manager.php',
    'icon'  => $pathIcon32 . 'type.png'
];

$adminmenu[] = [
    'title' => _MI_NEWBB_ADMENU_GROUPMOD,
    'link'  => 'admin/admin_groupmod.php',
    'icon'  => $pathIcon32 . 'groupmod.png'
];

$adminmenu[] = [
    'title' => _MI_NEWBB_ADMENU_SYNC,
    'link'  => 'admin/admin_synchronization.php',
    'icon'  => $pathIcon32 . 'synchronized.png'
];

$adminmenu[] = [
    'title' => _MI_NEWBB_ADMENU_ABOUT,
    'link'  => 'admin/about.php',
    'icon'  => $pathIcon32 . 'about.png'
];
