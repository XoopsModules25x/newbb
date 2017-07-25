<?php
/*
 You may not change or alter any portion of this comment or credits
 of supporting developers from this source code or any supporting source code
 which is considered copyrighted (c) material of the original comment or credit authors.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
*/
/**
 * NewBB module for xoops
 *
 * @copyright       XOOPS Project (https://xoops.org)
 * @license         GPL 2.0 or later
 * @package         newbb
 * @since           4.33
 * @min_xoops       2.5.8
 * @author          XOOPS Development Team - Email:<name@site.com> - Website:<https://xoops.org>
 */

// defined('XOOPS_ROOT_PATH') || exit('XOOPS root path not defined');

define('NEWBB_DIRNAME', basename(dirname(__DIR__)));
define('NEWBB_URL', XOOPS_URL . '/modules/' . NEWBB_DIRNAME);
define('NEWBB_PATH', XOOPS_ROOT_PATH . '/modules/' . NEWBB_DIRNAME);
define('NEWBB_IMAGES_URL', NEWBB_URL . '/assets/images');
define('NEWBB_ADMIN_URL', NEWBB_URL . '/admin');
define('NEWBB_ADMIN_PATH', NEWBB_PATH . '/admin/index.php');
define('NEWBB_ROOT_PATH', $GLOBALS['xoops']->path('modules/' . NEWBB_DIRNAME));
define('NEWBB_AUTHOR_LOGOIMG', NEWBB_URL . '/assets/images/logo_module.png');
define('NEWBB_UPLOAD_URL', XOOPS_UPLOAD_URL . '/' . NEWBB_DIRNAME); // WITHOUT Trailing slash
define('NEWBB_UPLOAD_PATH', XOOPS_UPLOAD_PATH . '/' . NEWBB_DIRNAME); // WITHOUT Trailing slash

// module information
$mod_copyright = "<a href='https://xoops.org' title='XOOPS Project' target='_blank'>
                     <img src='" . NEWBB_AUTHOR_LOGOIMG . "' alt='XOOPS Project' /></a>";

xoops_loadLanguage('common', NEWBB_DIRNAME);

xoops_load('constants', NEWBB_DIRNAME);
xoops_load('utility', NEWBB_DIRNAME);
xoops_load('XoopsRequest');
xoops_load('XoopsFilterInput');
