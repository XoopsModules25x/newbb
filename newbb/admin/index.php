<?php
// $Id: index.php 62 2012-08-17 10:15:26Z alfred $
// ------------------------------------------------------------------------ //
// XOOPS - PHP Content Management System                      //
// Copyright (c) 2000 XOOPS.org                           //
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
mod_loadFunctions('stats', 'newbb');

$attach_path = $GLOBALS['xoops']->path($GLOBALS['xoopsModuleConfig']['dir_attachments'] . '/');
$thumb_path  = $attach_path . 'thumbs/';
$folder      = array($attach_path, $thumb_path);

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
    $imageLibs = array();
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
        $msg  = ($res) ? _AM_NEWBB_DIRCREATED : _AM_NEWBB_DIRNOTCREATED;
        redirect_header('index.php', 2, $msg . ': ' . $path);
        break;

    case 'setperm':
        $path = XoopsRequest::getString('path', '', 'GET');// $_GET['path'];
        $res  = newbb_admin_chmod($path, 0777);
        $msg  = ($res) ? _AM_NEWBB_PERMSET : _AM_NEWBB_PERMNOTSET;
        redirect_header('index.php', 2, $msg . ': ' . $path);
        break;

    case 'senddigest':
        $digestHandler = &xoops_getmodulehandler('digest', 'newbb');
        $res           = $digestHandler->process(true);
        $msg           = ($res) ? _AM_NEWBB_DIGEST_FAILED : _AM_NEWBB_DIGEST_SENT;
        redirect_header('index.php', 2, $msg);
        break;

    case 'default':
    default:
        xoops_cp_header();
        echo '<fieldset>';
        $imageLibs      = newbb_getImageLibs();
        $module_handler = &xoops_gethandler('module');
        $reportHandler  = &xoops_getmodulehandler('report', 'newbb');

        $isOK = false;
        // START irmtfan add a poll_module config
        //XOOPS_POLL
        $xoopspoll = &$module_handler->getByDirname($GLOBALS['xoopsModuleConfig']['poll_module']);
        if (is_object($xoopspoll)) {
            $isOK = $xoopspoll->getVar('isactive');
        }
        /*
        else {
            //Umfrage
            $xoopspoll = &$module_handler->getByDirname('umfrage');
            if (is_object($xoopspoll)) $isOK = $xoopspoll->getVar('isactive');
        }
        */
        // END irmtfan add a poll_module config

        $memlimit_iniphp    = return_bytes(@ini_get('memory_limit'));
        $postmaxsize_iniphp = return_bytes(@ini_get('post_max_size'));
        if ($postmaxsize_iniphp < $memlimit_iniphp) {
            $uploadlimit = sprintf(_AM_NEWBB_MEMLIMITOK, return_bytes($postmaxsize_iniphp, true));
            $uploadfarbe = 'Green';
        } else {
            $uploadlimit = _AM_NEWBB_MEMLIMITTOLARGE;
            $uploadfarbe = 'Red';
        }

        if ($newXoopsModuleGui) {
            $indexAdmin->addInfoBox(_AM_NEWBB_PREFERENCES);
            // START irmtfan better poll module display link and version - check if xoops poll module is available
            if ($isOK) {
                $pollLink = _AM_NEWBB_AVAILABLE . ': ';
                $pollLink .= "<a href=\"" . XOOPS_URL . '/modules/' . $xoopspoll->getVar('dirname') . "/admin/index.php\"";
                $pollLink .= " alt=\"" . $xoopspoll->getVar('name') . ' ' . _VERSION . ' (' . $xoopspoll->getInfo('version') . ") \"";
                $pollLink .= " title=\"" . $xoopspoll->getVar('name') . ' ' . _VERSION . ' (' . $xoopspoll->getInfo('version') . ") \"";
                $pollLink .= '>' . '(' . $xoopspoll->getVar('name') . ')</a>';
            } else {
                $pollLink = _AM_NEWBB_NOTAVAILABLE;
            }
            $indexAdmin->addInfoBoxLine(_AM_NEWBB_PREFERENCES, '<infotext>' . _AM_NEWBB_POLLMODULE . ': %s' . '</infotext>', $pollLink, 'Green');
            // END irmtfan better poll module display link and version - check if xoops poll module is available
            $indexAdmin->addInfoBoxLine(_AM_NEWBB_PREFERENCES, '<infotext>' . _AM_NEWBB_IMAGEMAGICK . ' %s' . '</infotext>', (array_key_exists('imagemagick', $imageLibs)) ? _AM_NEWBB_AUTODETECTED . $imageLibs['imagemagick'] : _AM_NEWBB_NOTAVAILABLE, 'Green');
            $indexAdmin->addInfoBoxLine(_AM_NEWBB_PREFERENCES, '<infotext>' . 'NetPBM' . ': %s' . '</infotext>', array_key_exists('netpbm', $imageLibs) ? _AM_NEWBB_AUTODETECTED . $imageLibs['netpbm'] : _AM_NEWBB_NOTAVAILABLE, 'Green');
            $indexAdmin->addInfoBoxLine(_AM_NEWBB_PREFERENCES, '<infotext>' . _AM_NEWBB_GDLIB1 . ' %s' . '</infotext>', array_key_exists('gd1', $imageLibs) ? _AM_NEWBB_AUTODETECTED . $imageLibs['gd1'] : _AM_NEWBB_NOTAVAILABLE, 'Red');
            $indexAdmin->addInfoBoxLine(_AM_NEWBB_PREFERENCES, '<infotext>' . _AM_NEWBB_GDLIB2 . ' %s' . '</infotext>', array_key_exists('gd2', $imageLibs) ? _AM_NEWBB_AUTODETECTED . $imageLibs['gd2'] : _AM_NEWBB_NOTAVAILABLE, 'Green');
            $indexAdmin->addInfoBoxLine(_AM_NEWBB_PREFERENCES, '<infotext>' . _AM_NEWBB_UPLOAD . ' %s' . '</infotext>', $uploadlimit, $uploadfarbe);

            $indexAdmin->addInfoBox(_AM_NEWBB_BOARDSUMMARY);
            $indexAdmin->addInfoBoxLine(_AM_NEWBB_BOARDSUMMARY, '<infolabel>' . _AM_NEWBB_TOTALTOPICS . ': %s' . '</infolabel>', getTotalTopics(), 'Green');
            $indexAdmin->addInfoBoxLine(_AM_NEWBB_BOARDSUMMARY, '<infolabel>' . _AM_NEWBB_TOTALPOSTS . ': %s' . '</infolabel>', getTotalPosts(), 'Green');
            $indexAdmin->addInfoBoxLine(_AM_NEWBB_BOARDSUMMARY, '<infolabel>' . _AM_NEWBB_TOTALVIEWS . ': %s' . '</infolabel>', getTotalViews(), 'Green');

            $indexAdmin->addInfoBox(_AM_NEWBB_REPORT);
            $indexAdmin->addInfoBoxLine(_AM_NEWBB_REPORT, '<infolabel>' . _AM_NEWBB_REPORT_PENDING . ': %s' . '</infolabel>', $reportHandler->getCount(new Criteria('report_result', 0)), 'Green');
            $indexAdmin->addInfoBoxLine(_AM_NEWBB_REPORT, '<infolabel>' . _AM_NEWBB_REPORT_PROCESSED . ': %s' . '</infolabel>', $reportHandler->getCount(new Criteria('report_result', 1)), 'Green');

            foreach (array_keys($folder) as $i) {
                if (!(newbb_admin_getPathStatus($folder[$i])) === '') {
                    $indexAdmin->addConfigBoxLine($folder[$i] . ' ' . newbb_admin_getPathStatus($folder[$i]), 'folder');
                } else {
                    $indexAdmin->addConfigBoxLine($folder[$i], 'folder');
                }
                $indexAdmin->addConfigBoxLine(array($folder[$i], '755'), 'chmod');
            }

            echo $indexAdmin->addNavigation('index.php');
            echo $indexAdmin->renderIndex();
        } else {
            // loadModuleAdminMenu(0, _MI_NEWBB_ADMENU_INDEX);

            echo '<table><tr>';
            echo "<td style='width: 60%;'>";
            echo "<fieldset><legend style='font-weight: bold; color: #900;'>" . _AM_NEWBB_PREFERENCES . '</legend>';

            echo "<div style='padding: 12px;'>" . _AM_NEWBB_POLLMODULE . ': ';

            echo ($isOK) ? _AM_NEWBB_AVAILABLE . ': (Module: ' . $xoopspoll->getVar('name') . ')' : _AM_NEWBB_NOTAVAILABLE;
            echo '</div>';
            echo "<div style='padding: 8px;'>";
            echo "<a href='http://www.imagemagick.org' target='_blank'>" . _AM_NEWBB_IMAGEMAGICK . '&nbsp;</a>';
            if (array_key_exists('imagemagick', $imageLibs)) {
                echo "<strong><span style='color:green;'>" . _AM_NEWBB_AUTODETECTED . $imageLibs['imagemagick'] . '</span></strong>';
            } else {
                echo _AM_NEWBB_NOTAVAILABLE;
            }
            echo '<br />';
            echo "<a href='http://sourceforge.net/projects/netpbm' target='_blank'>NetPBM:&nbsp;</a>";
            if (array_key_exists('netpbm', $imageLibs)) {
                echo "<strong><span style='color:green;'>" . _AM_NEWBB_AUTODETECTED . $imageLibs['netpbm'] . '</span></strong>';
            } else {
                echo _AM_NEWBB_NOTAVAILABLE;
            }
            echo '<br />';
            echo _AM_NEWBB_GDLIB1 . '&nbsp;';
            if (array_key_exists('gd1', $imageLibs)) {
                echo "<strong><span style='color:green;'>" . _AM_NEWBB_AUTODETECTED . $imageLibs['gd1'] . '</span></strong>';
            } else {
                echo _AM_NEWBB_NOTAVAILABLE;
            }

            echo '<br />';
            echo _AM_NEWBB_GDLIB2 . '&nbsp;';
            if (array_key_exists('gd2', $imageLibs)) {
                echo "<strong><span style='color:green;'>" . _AM_NEWBB_AUTODETECTED . $imageLibs['gd2'] . '</span></strong>';
            } else {
                echo _AM_NEWBB_NOTAVAILABLE;
            }
            echo '</div>';

            echo "<div style='padding: 8px;'>" . _AM_NEWBB_ATTACHPATH . ': ';
            $path_status = newbb_admin_getPathStatus($attach_path);
            echo $attach_path . ' ( ' . $path_status . ' )';
            echo '<br />' . _AM_NEWBB_THUMBPATH . ': ';
            $path_status = newbb_admin_getPathStatus($thumb_path);
            echo $thumb_path . ' ( ' . $path_status . ' )';
            echo '</div>';
            echo '</fieldset><br />';
            echo "<fieldset><legend style='font-weight: bold; color: #900;'>" . _AM_NEWBB_BOARDSUMMARY . '</legend>';
            echo "<div style='padding: 12px;'>";
            echo _AM_NEWBB_TOTALTOPICS . ' <strong>' . getTotalTopics() . '</strong> | ';
            echo _AM_NEWBB_TOTALPOSTS . ' <strong>' . getTotalPosts() . '</strong> | ';
            echo _AM_NEWBB_TOTALVIEWS . ' <strong>' . getTotalViews() . '</strong></div>';
            echo '</fieldset><br />';
            echo "<fieldset><legend style='font-weight: bold; color: #900;'>" . _AM_NEWBB_REPORT . '</legend>';
            echo "<div style='padding: 12px;'><a href='admin_report.php'>" . _AM_NEWBB_REPORT_PENDING . '</a> <strong>' . $reportHandler->getCount(new Criteria('report_result', 0)) . '</strong> | ';
            echo _AM_NEWBB_REPORT_PROCESSED . " <strong>" . $reportHandler->getCount(new Criteria('report_result', 1)) . '</strong>';
            echo '</div>';
            echo '</fieldset><br />';

            if ($GLOBALS['xoopsModuleConfig']['email_digest'] > 0) {
                $digestHandler = &xoops_getmodulehandler('digest', 'newbb');
                echo "<fieldset><legend style='font-weight: bold; color: #900;'>" . _AM_NEWBB_DIGEST . '</legend>';
                $due    = ($digestHandler->checkStatus()) / 60; // minutes
                $prompt = ($due > 0) ? sprintf(_AM_NEWBB_DIGEST_PAST, $due) : sprintf(_AM_NEWBB_DIGEST_NEXT, abs($due));
                echo "<div style='padding: 12px;'><a href='index.php?op=senddigest'>" . $prompt . "</a> | ";
                echo "<a href='admin_digest.php'>" . _AM_NEWBB_DIGEST_ARCHIVE . '</a> <strong>' . $digestHandler->getDigestCount() . '</strong>';
                echo '</div>';
                echo '</fieldset><br />';
            }

            echo '</td>';
            echo "<td style='width: 38%;'>";
            echo "
            <style>
            #xo-newbb-icons {
                margin: 3px;
                font-family: tahoma, Helvetica, sans-serif;
                text-align: center;
            }
            #xo-newbb-icons a {
                display: block;
                float: left;
                height: 80px !important;
                width: 90px !important;
                vertical-align: middle;
                text-decoration: none;
                background-color: #f0f0f0;
                padding: 2px;
                margin: 3px;
                color: #666666;
                border: 1px solid #f9f9f9;
                -moz-border-radius: 9px;
                -webkit-border-radius: 9px;
                -khtml-border-radius: 9px;
                border-radius: 9px;
            }
            #xo-newbb-icons a:hover {
                color: #1E90FF;
                border-left: 1px solid #eee;
                border-top: 1px solid #eee;
                border-right: 1px solid #ccc;
                border-bottom: 1px solid #ccc;
                background: #f9f9f9;
                filter: alpha(opacity = 80);
                -moz-opacity: 0.8;
                -webkit-opacity: 0.8;
                -khtml-opacity: 0.8;
                opacity: 0.8;
            }

            #xo-newbb-icons  img {
                margin-top: 8px;
                margin-bottom: 8px;
            }
            #xo-newbb-icons span {
                font-size: 10px;
                font-weight: bold;
                display: block;
            }

            #xo-newbb-icons span.uno {
                font-size: 11px;
                font-weight: normal;
                text-decoration: underline;
                color: Blue;
            }

            #xo-newbb-icons span.unor {
                font-size: 11px;
                font-weight: normal;
                text-decoration: underline;
                color: #CC0000;
            }

        </style>
        <div id='xo-newbb-icons'>
            <a class='tooltip' href='index.php' title='" . _MI_NEWBB_ADMENU_INDEX . "'><img src='" . XOOPS_URL . "/modules/newbb/assets/images/menu/home.png' /><span>" . _MI_NEWBB_ADMENU_INDEX . "</span></a>
            <a class='tooltip' href='admin_cat_manager.php' title='" . _MI_NEWBB_ADMENU_CATEGORY . "'><img src='" . XOOPS_URL . "/modules/newbb/assets/images/menu/cat.png' /><span>" . _MI_NEWBB_ADMENU_CATEGORY . "</span></a>
            <a class='tooltip' href='admin_forum_manager.php' title='" . _MI_NEWBB_ADMENU_FORUM . "'><img src='" . XOOPS_URL . "/modules/newbb/assets/images/menu/forum.png' /><span>" . _MI_NEWBB_ADMENU_FORUM . "</span></a>
            <a class='tooltip' href='admin_permissions.php' title='" . _MI_NEWBB_ADMENU_PERMISSION . "'><img src='" . XOOPS_URL . "/modules/newbb/assets/images/menu/permissions.png' /><span>" . _MI_NEWBB_ADMENU_PERMISSION . "</span></a>
            <a class='tooltip' href='admin_forum_reorder.php' title='" . _MI_NEWBB_ADMENU_ORDER . "'><img src='" . XOOPS_URL . "/modules/newbb/assets/images/menu/order.png' /><span>" . _MI_NEWBB_ADMENU_ORDER . "</span></a>
            <a class='tooltip' href='admin_forum_prune.php' title='" . _MI_NEWBB_ADMENU_PRUNE . "'><img src='" . XOOPS_URL . "/modules/newbb/assets/images/menu/prune.png' /><span>" . _MI_NEWBB_ADMENU_PRUNE . "</span></a>
            <a class='tooltip' href='admin_report.php' title='" . _MI_NEWBB_ADMENU_REPORT . "'><img src='" . XOOPS_URL . "/modules/newbb/assets/images/menu/report.png' /><span>" . _MI_NEWBB_ADMENU_REPORT . "</span></a>
            <a class='tooltip' href='admin_digest.php' title='" . _MI_NEWBB_ADMENU_DIGEST . "'><img src='" . XOOPS_URL . "/modules/newbb/assets/images/menu/digest.png' /><span>" . _MI_NEWBB_ADMENU_DIGEST . "</span></a>
            <a class='tooltip' href='admin_votedata.php' title='" . _MI_NEWBB_ADMENU_VOTE . "'><img src='" . XOOPS_URL . "/modules/newbb/assets/images/menu/votedata.png' /><span>" . _MI_NEWBB_ADMENU_VOTE . "</span></a>
            <a class='tooltip' href='admin_type_manager.php' title='" . _MI_NEWBB_ADMENU_TYPE . "'><img src='" . XOOPS_URL . "/modules/newbb/assets/images/menu/type.png' /><span>" . _MI_NEWBB_ADMENU_TYPE . "</span></a>
            <a class='tooltip' href='admin_groupmod.php' title='" . _MI_NEWBB_ADMENU_GROUPMOD . "'><img src='" . XOOPS_URL . "/modules/newbb/assets/images/menu/groupmod.png' /><span>" . _MI_NEWBB_ADMENU_GROUPMOD . "</span></a>
            <a class='tooltip' href='admin_blocks.php' title='" . _MI_NEWBB_ADMENU_BLOCK . "'><img src='" . XOOPS_URL . "/modules/newbb/assets/images/menu/blocks.png' /><span>" . _MI_NEWBB_ADMENU_BLOCK . "</span></a>
            <a class='tooltip' href='admin_synchronization.php' title='" . _MI_NEWBB_ADMENU_SYNC . "'><img src='" . XOOPS_URL . "/modules/newbb/assets/images/menu/synchronization.png' /><span>" . _MI_NEWBB_ADMENU_SYNC . "</span></a>
            <a class='tooltip' href='about.php' title='" . _MI_NEWBB_ADMENU_ABOUT . "'><img src='" . XOOPS_URL . "/modules/newbb/assets/images/menu/about.png' /><span>" . _MI_NEWBB_ADMENU_ABOUT . "</span></a>
        </div>";
            echo '</td>';
            echo '</tr></table>';
            echo '<br /><br />';

            /* A trick to clear garbage for suspension management
                * Not good but works
            */
            if (!empty($GLOBALS['xoopsModuleConfig']['enable_usermoderate'])) {
                $moderateHandler =& xoops_getmodulehandler('moderate', 'newbb');
                $moderateHandler->clearGarbage();
            }
        }
        echo '</fieldset>';
        xoops_cp_footer();
        break;
}
mod_clearCacheFile('config', 'newbb');
mod_clearCacheFile('permission', 'newbb');

/**
 * @param             $sizeAsString
 * @param  bool       $b
 * @return int|string
 */
function return_bytes($sizeAsString, $b = false)
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
        $suffix = array('', 'KB', 'MB', 'GB', 'TB');

        return round(pow(1024, $base - floor($base))) . ' ' . $suffix[(int)floor($base)];
    }
}
