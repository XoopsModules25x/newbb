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

require_once __DIR__ . '/admin_header.php';
//require_once __DIR__ . '/../class/Utility.php';
require_once __DIR__ . '/../include/functions.stats.php';

$attach_path = $GLOBALS['xoops']->path($GLOBALS['xoopsModuleConfig']['dir_attachments'] . '/');
$thumb_path  = $attach_path . 'thumbs/';
$folder      = [$attach_path, $thumb_path];

/** @var Xmf\Module\Admin $adminObject */
$adminObject = Xmf\Module\Admin::getInstance();

/**
 * @param       $target
 * @param  int  $mode
 * @return bool
 */
function newbb_admin_mkdir($target, $mode = 0777)
{
    $target = str_replace('..', '', $target);

    // http://www.php.net/manual/en/function.mkdir.php
    return is_dir($target) || (newbb_admin_mkdir(dirname($target), $mode) && mkdir($target, $mode));
}

/**
 * @param       $target
 * @param  int  $mode
 * @return bool
 */
function newbb_admin_chmod($target, $mode = 0777)
{
    $target = str_replace('..', '', $target);

    return @chmod($target, $mode);
}

/**
 * @return array
 */
function newbb_getImageLibs()
{
    $imageLibs = [];
    unset($output, $status);
    if (1 == $GLOBALS['xoopsModuleConfig']['image_lib'] || 0 == $GLOBALS['xoopsModuleConfig']['image_lib']) {
        $path = empty($GLOBALS['xoopsModuleConfig']['path_magick']) ? '' : $GLOBALS['xoopsModuleConfig']['path_magick'] . '/';
        @exec($path . 'convert -version', $output, $status);
        if (empty($status) && !empty($output) && preg_match("/imagemagick[ \t]+([0-9\.]+)/i", $output[0], $matches)) {
            $imageLibs['imagemagick'] = $matches[0];
        }

        unset($output, $status);
    }
    if (2 == $GLOBALS['xoopsModuleConfig']['image_lib'] || 0 == $GLOBALS['xoopsModuleConfig']['image_lib']) {
        $path = empty($GLOBALS['xoopsModuleConfig']['path_netpbm']) ? '' : $GLOBALS['xoopsModuleConfig']['path_netpbm'] . '/';
        @exec($path . 'jpegtopnm -version 2>&1', $output, $status);
        if (empty($status) && !empty($output) && preg_match("/netpbm[ \t]+([0-9\.]+)/i", $output[0], $matches)) {
            $imageLibs['netpbm'] = $matches[0];
        }
        unset($output, $status);
    }

    if (function_exists('gd_info')) {
        $tmpInfo         = gd_info();
        $imageLibs['gd'] = $tmpInfo['GD Version'];
    }

    return $imageLibs;
}

xoops_cp_header();

$imageLibs = newbb_getImageLibs();
/** @var \XoopsModuleHandler $moduleHandler */
$moduleHandler = xoops_getHandler('module');
///** @var Newbb\ReportHandler $reportHandler */
//$reportHandler = Newbb\Helper::getInstance()->getHandler('Report');

$isOK = false;
// START irmtfan add a poll_module config
//XOOPS_POLL
$xoopspoll = $moduleHandler->getByDirname($GLOBALS['xoopsModuleConfig']['poll_module']);
if (is_object($xoopspoll)) {
    $isOK = $xoopspoll->getVar('isactive');
}
// END irmtfan add a poll_module config

$memlimit_iniphp    = return_bytes(@ini_get('memory_limit'));
$postmaxsize_iniphp = return_bytes(@ini_get('post_max_size'));
$uploadlimit        = _AM_NEWBB_MEMLIMITTOLARGE;
if ($postmaxsize_iniphp < $memlimit_iniphp) {
    $uploadlimit = sprintf(_AM_NEWBB_MEMLIMITOK, return_bytes($postmaxsize_iniphp, true));
}

$adminObject->addInfoBox(_AM_NEWBB_PREFERENCES);
// START irmtfan better poll module display link and version - check if xoops poll module is available
if ($isOK) {
    $pollLink = _AM_NEWBB_AVAILABLE . ': ';
    $pollLink .= '<a href="' . XOOPS_URL . '/modules/' . $xoopspoll->getVar('dirname') . '/admin/index.php"';
    $pollLink .= ' alt="' . $xoopspoll->getVar('name') . ' ' . _VERSION . ' (' . $xoopspoll->getInfo('version') . ') "';
    $pollLink .= ' title="' . $xoopspoll->getVar('name') . ' ' . _VERSION . ' (' . $xoopspoll->getInfo('version') . ') "';
    $pollLink .= '>' . '(' . $xoopspoll->getVar('name') . ')</a>';
} else {
    $pollLink = _AM_NEWBB_NOTAVAILABLE;
}
$adminObject->addInfoBoxLine(sprintf(_AM_NEWBB_POLLMODULE . ': %s', $pollLink));
// END irmtfan better poll module display link and version - check if xoops poll module is available
$adminObject->addInfoBoxLine(sprintf(_AM_NEWBB_IMAGEMAGICK . ' %s', array_key_exists('imagemagick', $imageLibs) ? _AM_NEWBB_AUTODETECTED . $imageLibs['imagemagick'] : _AM_NEWBB_NOTAVAILABLE));
$adminObject->addInfoBoxLine(sprintf('NetPBM' . ': %s', array_key_exists('netpbm', $imageLibs) ? _AM_NEWBB_AUTODETECTED . $imageLibs['netpbm'] : _AM_NEWBB_NOTAVAILABLE));
$adminObject->addInfoBoxLine(sprintf(_AM_NEWBB_GDLIB . ' %s', array_key_exists('gd', $imageLibs) ? _AM_NEWBB_AUTODETECTED . $imageLibs['gd'] : _AM_NEWBB_NOTAVAILABLE));
$adminObject->addInfoBoxLine(sprintf(_AM_NEWBB_UPLOAD . ' %s', $uploadlimit));
$adminObject->addInfoBoxLine(sprintf(_AM_NEWBB_INDEX_PDF_PAGE . '', ''));

$adminObject->addInfoBox(_AM_NEWBB_BOARDSUMMARY);
$adminObject->addInfoBoxLine(sprintf(_AM_NEWBB_TOTALTOPICS . ': %s', getTotalTopics()));
$adminObject->addInfoBoxLine(sprintf(_AM_NEWBB_TOTALPOSTS . ': %s', getTotalPosts()));
$adminObject->addInfoBoxLine(sprintf(_AM_NEWBB_TOTALVIEWS . ': %s', getTotalViews()));

$adminObject->addInfoBox(_AM_NEWBB_REPORT);
$adminObject->addInfoBoxLine(sprintf(_AM_NEWBB_REPORT_PENDING . ': %s', $reportHandler->getCount(new \Criteria('report_result', 0))));
$adminObject->addInfoBoxLine(sprintf(_AM_NEWBB_REPORT_PROCESSED . ': %s', $reportHandler->getCount(new \Criteria('report_result', 1))));

foreach (array_keys($GLOBALS['uploadFolders']) as $i) {
    Newbb\Utility::prepareFolder($uploadFolders[$i]);
    $adminObject->addConfigBoxLine($uploadFolders[$i], 'folder');
}

$adminObject->displayNavigation(basename(__FILE__));
$adminObject->displayIndex();

require_once __DIR__ . '/admin_footer.php';

$cacheHelper = Newbb\Utility::cleanCache();
//$cacheHelper = new \Xmf\Module\Helper\Cache('newbb');
//$cacheHelper->delete('config');
//$cacheHelper->delete('permission');

/**
 * @param             $sizeAsString
 * @param  bool       $b
 * @return int|string
 */
function return_bytes($sizeAsString, $b = false)
{
    if (false === $b) {
        switch (substr($sizeAsString, -1)) {
            case 'M':
            case 'm':
                return (int)$sizeAsString * 1048576;
            case 'K':
            case 'k':
                return (int)$sizeAsString * 1024;
            case 'G':
            case 'g':
                return (int)$sizeAsString * 1073741824;
            default:
                return $sizeAsString;
        }
    } else {
        $base   = log($sizeAsString) / log(1024);
        $suffix = ['', 'KB', 'MB', 'GB', 'TB'];

        return round(pow(1024, $base - floor($base))) . ' ' . $suffix[(int)floor($base)];
    }
}
