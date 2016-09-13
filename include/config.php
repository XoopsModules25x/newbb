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
 * animal module for xoops
 *
 * @copyright       XOOPS Project (http://xoops.org)
 * @license         GPL 2.0 or later
 * @package         Publisher
 * @subpackage      Config
 * @since           1.03
 * @author          XOOPS Development Team - ( http://xoops.org )
 */

// defined('XOOPS_ROOT_PATH') || exit('XOOPS root path not defined');
include_once __DIR__ . '/common.php';

$moduleDirName = basename(dirname(__DIR__));
$uploadFolders = array(
    NEWBB_UPLOAD_PATH,
    NEWBB_UPLOAD_PATH . '/thumbs'
);

//$copyFiles = array(
//    NEWBB_UPLOAD_PATH,
//    NEWBB_UPLOAD_PATH . '/thumbs');
