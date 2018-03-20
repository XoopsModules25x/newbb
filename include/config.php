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
 * @copyright       XOOPS Project (https://xoops.org)
 * @license         GPL 2.0 or later
 * @package         Publisher
 * @subpackage      Config
 * @since           1.03
 * @author          XOOPS Development Team - ( https://xoops.org )
 */

// defined('XOOPS_ROOT_PATH') || die('Restricted access');
require_once __DIR__ . '/common.php';

$moduleDirName = basename(dirname(__DIR__));
$uploadFolders = [
    NEWBB_UPLOAD_PATH,
    NEWBB_UPLOAD_PATH . '/thumbs'
];

function getConfig()
{
    $moduleDirName      = basename(dirname(__DIR__));
    $moduleDirNameUpper = strtoupper($moduleDirName);
    return (object)[
        'name'           => strtoupper($moduleDirName) . ' Module Configurator',
        'paths'          => [
            'dirname'    => $moduleDirName,
            'admin'      => XOOPS_ROOT_PATH . '/modules/' . $moduleDirName . '/admin',
            'modPath'    => XOOPS_ROOT_PATH . '/modules/' . $moduleDirName,
            'modUrl'     => XOOPS_URL . '/modules/' . $moduleDirName,
            'uploadPath' => XOOPS_UPLOAD_PATH . '/' . $moduleDirName,
            'uploadUrl'  => XOOPS_UPLOAD_URL . '/' . $moduleDirName,
        ],
        'uploadFolders'  => [
            constant($moduleDirNameUpper . '_UPLOAD_PATH'),
            constant($moduleDirNameUpper . '_UPLOAD_PATH') . '/thumbs',

            //XOOPS_UPLOAD_PATH . '/flags'
        ],
        'copyBlankFiles' => [
            constant($moduleDirNameUpper . '_UPLOAD_PATH'),
            constant($moduleDirNameUpper . '_UPLOAD_PATH') . '/thumbs',
            //XOOPS_UPLOAD_PATH . '/flags'
        ],

        'copyTestFolders' => [
            //        constant($moduleDirNameUpper . '_UPLOAD_PATH'),
            //[
            //    constant($moduleDirNameUpper . '_PATH') . '/testdata/images',
            //    constant($moduleDirNameUpper . '_UPLOAD_PATH') . '/images',
            //]
        ],

        'templateFolders' => [
            '/templates/',
            '/templates/blocks/',
            '/templates/admin/'

        ],
        'oldFiles'        => [
            '/class/request.php',
            '/class/registry.php',
            '/class/utilities.php',
            '/class/util.php',
            '/include/constants.php',
            '/include/functions.php',
            '/ajaxrating.txt',
        ],
        'oldFolders'      => [
            '/images',
            '/css',
            '/js',
            '/tcpdf',
            '/images',
        ],
        'modCopyright'    => "<a href='https://xoops.org' title='XOOPS Project' target='_blank'>
                     <img src='" . constant($moduleDirNameUpper . '_AUTHOR_LOGOIMG') . '\' alt=\'XOOPS Project\' /></a>',
    ];
}
