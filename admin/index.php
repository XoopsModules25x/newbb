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
include_once __DIR__ . '/admin_header.php';
include_once __DIR__ . '/../class/utilities.php';
mod_loadFunctions('stats', 'newbb');

$attach_path = $GLOBALS['xoops']->path($GLOBALS['xoopsModuleConfig']['dir_attachments'] . '/');
$thumb_path  = $attach_path . 'thumbs/';
$folder      = [$attach_path, $thumb_path];

/**
 * @param $path
 * @return bool|string
 */
function newbb_admin_getPathStatus($path = '')
{
    if ('' === $path) {
        return false;
    }
    if (@is_writable($path)) {
        $path_status = '';
    } elseif (!@is_dir($path)) {
        $path_status = _AM_NEWBB_NOTAVAILABLE . " <a href=index.php?op=createdir&amp;path=$path>" . _AM_NEWBB_CREATETHEDIR . '</a>';
    } else {
        $path_status = _AM_NEWBB_NOTWRITABLE . " <a href=index.php?op=setperm&amp;path=$path>" . _AM_NEWBB_SETMPERM . '</a>';
    }

    return $path_status;
}

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
    if ($GLOBALS['xoopsModuleConfig']['image_lib'] == 1 || $GLOBALS['xoopsModuleConfig']['image_lib'] == 0) {
        $path = empty($GLOBALS['xoopsModuleConfig']['path_magick']) ? '' : $GLOBALS['xoopsModuleConfig']['path_magick'] . '/';
        @exec($path . 'convert -version', $output, $status);
        if (empty($status) && !empty($output) && preg_match("/imagemagick[ \t]+([0-9\.]+)/i", $output[0], $matches)) {
            $imageLibs['imagemagick'] = $matches[0];
        }

        unset($output, $status);
    }
    if ($GLOBALS['xoopsModuleConfig']['image_lib'] == 2 || $GLOBALS['xoopsModuleConfig']['image_lib'] == 0) {
        $path = empty($GLOBALS['xoopsModuleConfig']['path_netpbm']) ? '' : $GLOBALS['xoopsModuleConfig']['path_netpbm'] . '/';
        @exec($path . 'jpegtopnm -version 2>&1', $output, $status);
        if (empty($status) && !empty($output) && preg_match("/netpbm[ \t]+([0-9\.]+)/i", $output[0], $matches)) {
            $imageLibs['netpbm'] = $matches[0];
        }
        unset($output, $status);
    }

    $GDfuncList = get_extension_funcs('gd');
    ob_start();
    @phpinfo(INFO_MODULES);
    $output = ob_get_contents();
    ob_end_clean();
    $matches[1] = '';
    $gdversion  = '';
    if (preg_match("/GD Version[ \t]*(<[^>]+>[ \t]*)+([^<>]+)/s", $output, $matches)) {
        $gdversion = $matches[2];
    }
    if ($GDfuncList) {
        if (in_array('imagegd2', $GDfuncList, true)) {
            $imageLibs['gd2'] = $gdversion;
        } else {
            $imageLibs['gd1'] = $gdversion;
        }
    }

    return $imageLibs;
}

$op = XoopsRequest::getCmd('op', '', 'GET'); // (isset($_GET['op']))? $_GET['op'] : "";

switch ($op) {
    case 'createdir':
        $path = XoopsRequest::getString('path', '', 'GET');// $_GET['path'];
        $res  = newbb_admin_mkdir($path);
        $msg  = $res ? _AM_NEWBB_DIRCREATED : _AM_NEWBB_DIRNOTCREATED;
        redirect_header('index.php', 2, $msg . ': ' . $path);
        break;

    case 'setperm':
        $path = XoopsRequest::getString('path', '', 'GET');// $_GET['path'];
        $res  = newbb_admin_chmod($path, 0777);
        $msg  = $res ? _AM_NEWBB_PERMSET : _AM_NEWBB_PERMNOTSET;
        redirect_header('index.php', 2, $msg . ': ' . $path);
        break;

    case 'senddigest':
        $digestHandler = xoops_getModuleHandler('digest', 'newbb');
        $res           = $digestHandler->process(true);
        $msg           = $res ? _AM_NEWBB_DIGEST_FAILED : _AM_NEWBB_DIGEST_SENT;
        redirect_header('index.php', 2, $msg);
        break;

    case 'default':
    default:
        xoops_cp_header();

        echo '<fieldset>';
        $imageLibs     = newbb_getImageLibs();
    /** @var XoopsModuleHandler $moduleHandler */
    $moduleHandler = xoops_getHandler('module');
        $reportHandler = xoops_getModuleHandler('report', 'newbb');

        $isOK = false;
        // START irmtfan add a poll_module config
        //XOOPS_POLL
        $xoopspoll = $moduleHandler->getByDirname($GLOBALS['xoopsModuleConfig']['poll_module']);
        if (is_object($xoopspoll)) {
            $isOK = $xoopspoll->getVar('isactive');
        }
        /*
        else {
            //Umfrage
            $xoopspoll = &$moduleHandler->getByDirname('umfrage');
            if (is_object($xoopspoll)) $isOK = $xoopspoll->getVar('isactive');
        }
        */
        // END irmtfan add a poll_module config

        $memlimit_iniphp    = returnBytes(@ini_get('memory_limit'));
        $postmaxsize_iniphp = returnBytes(@ini_get('post_max_size'));
        if ($postmaxsize_iniphp < $memlimit_iniphp) {
            $uploadlimit = sprintf(_AM_NEWBB_MEMLIMITOK, returnBytes($postmaxsize_iniphp, true));
            $uploadfarbe = 'Green';
        } else {
            $uploadlimit = _AM_NEWBB_MEMLIMITTOLARGE;
            $uploadfarbe = 'Red';
        }

        $indexAdmin->addInfoBox(_AM_NEWBB_PREFERENCES);
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
        $indexAdmin->addInfoBoxLine(_AM_NEWBB_PREFERENCES, '<infotext>' . _AM_NEWBB_POLLMODULE . ': %s' . '</infotext>', $pollLink, 'Green');
        // END irmtfan better poll module display link and version - check if xoops poll module is available
        $indexAdmin->addInfoBoxLine(_AM_NEWBB_PREFERENCES, '<infotext>' . _AM_NEWBB_IMAGEMAGICK . ' %s' . '</infotext>',
                                    array_key_exists('imagemagick', $imageLibs) ? _AM_NEWBB_AUTODETECTED . $imageLibs['imagemagick'] : _AM_NEWBB_NOTAVAILABLE, 'Green');
        $indexAdmin->addInfoBoxLine(_AM_NEWBB_PREFERENCES, '<infotext>' . 'NetPBM' . ': %s' . '</infotext>',
                                    array_key_exists('netpbm', $imageLibs) ? _AM_NEWBB_AUTODETECTED . $imageLibs['netpbm'] : _AM_NEWBB_NOTAVAILABLE, 'Green');
        $indexAdmin->addInfoBoxLine(_AM_NEWBB_PREFERENCES, '<infotext>' . _AM_NEWBB_GDLIB1 . ' %s' . '</infotext>',
                                    array_key_exists('gd1', $imageLibs) ? _AM_NEWBB_AUTODETECTED . $imageLibs['gd1'] : _AM_NEWBB_NOTAVAILABLE, 'Red');
        $indexAdmin->addInfoBoxLine(_AM_NEWBB_PREFERENCES, '<infotext>' . _AM_NEWBB_GDLIB2 . ' %s' . '</infotext>',
                                    array_key_exists('gd2', $imageLibs) ? _AM_NEWBB_AUTODETECTED . $imageLibs['gd2'] : _AM_NEWBB_NOTAVAILABLE, 'Green');
        $indexAdmin->addInfoBoxLine(_AM_NEWBB_PREFERENCES, '<infotext>' . _AM_NEWBB_UPLOAD . ' %s' . '</infotext>', $uploadlimit, $uploadfarbe);

        $indexAdmin->addInfoBox(_AM_NEWBB_BOARDSUMMARY);
        $indexAdmin->addInfoBoxLine(_AM_NEWBB_BOARDSUMMARY, '<infolabel>' . _AM_NEWBB_TOTALTOPICS . ': %s' . '</infolabel>', getTotalTopics(), 'Green');
        $indexAdmin->addInfoBoxLine(_AM_NEWBB_BOARDSUMMARY, '<infolabel>' . _AM_NEWBB_TOTALPOSTS . ': %s' . '</infolabel>', getTotalPosts(), 'Green');
        $indexAdmin->addInfoBoxLine(_AM_NEWBB_BOARDSUMMARY, '<infolabel>' . _AM_NEWBB_TOTALVIEWS . ': %s' . '</infolabel>', getTotalViews(), 'Green');

        $indexAdmin->addInfoBox(_AM_NEWBB_REPORT);
        $indexAdmin->addInfoBoxLine(_AM_NEWBB_REPORT, '<infolabel>' . _AM_NEWBB_REPORT_PENDING . ': %s' . '</infolabel>', $reportHandler->getCount(new Criteria('report_result', 0)), 'Green');
        $indexAdmin->addInfoBoxLine(_AM_NEWBB_REPORT, '<infolabel>' . _AM_NEWBB_REPORT_PROCESSED . ': %s' . '</infolabel>', $reportHandler->getCount(new Criteria('report_result', 1)), 'Green');

        //        foreach (array_keys($folder) as $i) {
        //            if (!newbb_admin_getPathStatus($folder[$i]) == '') {
        //                $indexAdmin->addConfigBoxLine($folder[$i] . ' ' . newbb_admin_getPathStatus($folder[$i]), 'folder');
        //            } else {
        //                $indexAdmin->addConfigBoxLine($folder[$i], 'folder');
        //            }
        //            $indexAdmin->addConfigBoxLine(array($folder[$i], '755'), 'chmod');
        //        }

        //        $indexAdmin = new ModuleAdmin();
        foreach (array_keys($GLOBALS['uploadFolders']) as $i) {
            NewbbUtilities::prepareFolder($uploadFolders[$i]);
            $indexAdmin->addConfigBoxLine($uploadFolders[$i], 'folder');
            //    $indexAdmin->addConfigBoxLine(array($folder[$i], '777'), 'chmod');
        }

        echo $indexAdmin->addNavigation(basename(__FILE__));
        echo $indexAdmin->renderIndex();

        echo '</fieldset>';
        include_once __DIR__ . '/admin_footer.php';
        break;
}
mod_clearCacheFile('config', 'newbb');
mod_clearCacheFile('permission', 'newbb');

/**
 * @param             $sizeAsString
 * @param  bool       $b
 * @return int|string
 */
function returnBytes($sizeAsString, $b = false)
{
    if ($b === false) {
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
