<?php
//
// ------------------------------------------------------------------------ //
// XOOPS - PHP Content Management System                      //
// Copyright (c) 2000-2016 XOOPS.org                           //
// <http://xoops.org/>                             //
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
// URL: http://www.myweb.ne.jp/, http://xoops.org/, http://jp.xoops.org/ //
// Project: XOOPS Project                                                    //
// ------------------------------------------------------------------------- //

//include $GLOBALS['xoops']->path('include/cp_header.php');
include '../../../include/cp_header.php';
include_once $GLOBALS['xoops']->path('modules/' . $xoopsModule->getVar('dirname') . '/include/vars.php');
include_once $GLOBALS['xoops']->path('modules/' . $xoopsModule->getVar('dirname') . '/include/functions.user.php');
include_once $GLOBALS['xoops']->path('modules/' . $xoopsModule->getVar('dirname') . '/include/functions.render.php');
include_once $GLOBALS['xoops']->path('Frameworks/art/functions.php');
include_once $GLOBALS['xoops']->path('Frameworks/art/functions.admin.php');

include_once dirname(__DIR__) . '/include/config.php';

xoops_load('XoopsRequest');

$moduleDirName = basename(dirname(__DIR__));

xoops_loadLanguage('main', 'newbb');
xoops_loadLanguage('modinfo', 'newbb');

$moduleHandler = xoops_getHandler('module');
$module        = $moduleHandler->getByDirname($moduleDirName);
$pathIcon16    = XOOPS_URL . '/' . $module->getInfo('sysicons16');
$pathIcon32    = XOOPS_URL . '/' . $module->getInfo('sysicons32');
$pathModIcon32 = XOOPS_URL . '/' . $module->getInfo('modicons32');

$xoopsModuleAdminPath = $GLOBALS['xoops']->path('www/' . $GLOBALS['xoopsModule']->getInfo('dirmoduleadmin'));
require_once $xoopsModuleAdminPath . '/moduleadmin.php';

$indexAdmin = new ModuleAdmin();

$myts = MyTextSanitizer::getInstance();
