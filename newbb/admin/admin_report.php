<?php
// $Id: admin_report.php 62 2012-08-17 10:15:26Z alfred $
//  ------------------------------------------------------------------------ //
//                XOOPS - PHP Content Management System                      //
//                    Copyright (c) 2000 XOOPS.org                           //
//                       <http://www.xoops.org/>                             //
//  ------------------------------------------------------------------------ //
//  This program is free software; you can redistribute it and/or modify     //
//  it under the terms of the GNU General Public License as published by     //
//  the Free Software Foundation; either version 2 of the License, or        //
//  (at your option) any later version.                                      //
//                                                                           //
//  You may not change or alter any portion of this comment or credits       //
//  of supporting developers from this source code or any supporting         //
//  source code which is considered copyrighted (c) material of the          //
//  original comment or credit authors.                                      //
//                                                                           //
//  This program is distributed in the hope that it will be useful,          //
//  but WITHOUT ANY WARRANTY; without even the implied warranty of           //
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            //
//  GNU General Public License for more details.                             //
//                                                                           //
//  You should have received a copy of the GNU General Public License        //
//  along with this program; if not, write to the Free Software              //
//  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA //
//  ------------------------------------------------------------------------ //
// Author: Kazumi Ono (AKA onokazu)                                          //
// URL: http://www.myweb.ne.jp/, http://www.xoops.org/, http://jp.xoops.org/ //
// Project: The XOOPS Project                                                //
// ------------------------------------------------------------------------- //

include_once __DIR__ . '/admin_header.php';
include_once $GLOBALS['xoops']->path('class/pagenav.php');

$op             = XoopsRequest::getCmd('op', XoopsRequest::getCmd('op', 'default', 'POST'), 'GET'); // !empty($_GET['op'])? $_GET['op'] : (!empty($_POST['op'])?$_POST['op']:"default");
$item           = XoopsRequest::getString('op', XoopsRequest::getInt('item', 'process', 'POST'), 'GET'); //!empty($_GET['op'])? $_GET['item'] : (!empty($_POST['item'])?$_POST['item']:"process");
$start          = XoopsRequest::getInt('start', XoopsRequest::getInt('start', 0, 'GET'), 'POST'); //$start = !empty($_POST['start']) ?  intval( $_POST['start'] ) : intval( @$_GET['start'] );
$report_handler =& xoops_getmodulehandler('report', 'newbb');

xoops_cp_header();
echo "<fieldset>";
switch ($op) {
    case "save":
        $report_ids = XoopsRequest::getArray('report_id','', 'POST');
//        $report_ids = XoopsRequest::getInt('report_id', 0, 'POST'); //$_POST['report_id'];
        // irmtfan add error redirect header
        if (empty($report_ids))
            redirect_header("admin_report.php?item={$item}" . (empty($start) ? "" : "&start={$start}"), 1, _AM_NEWBB_REPORTNOTSELECT);
        $report_memos = XoopsRequest::getArray('report_memo', array(), 'POST'); // isset($_POST['report_memo']) ? $_POST['report_memo'] : array();
        foreach ($report_ids as $rid => $value) {
            if (!$value) continue;
            $report_obj = $report_handler->get($rid);
            $report_obj->setVar("report_result", 1);
            $report_obj->setVar("report_memo", $report_memos[$rid]);
            $report_handler->insert($report_obj);
        }
        // irmtfan add message
        redirect_header("admin_report.php?item={$item}" . (empty($start) ? "" : "&start={$start}"), 1, _AM_NEWBB_REPORTSAVE);

        break;

    case "delete":
        $report_ids = XoopsRequest::getArray('report_id', array(), 'POST');// $_POST['report_id'];
        // irmtfan add error redirect header
        if (empty($report_ids))
            redirect_header("admin_report.php?item={$item}" . (empty($start) ? "" : "&start={$start}"), 1, _AM_NEWBB_REPORTNOTSELECT);
        foreach ($report_ids as $rid => $value) {
            if (!$value) continue;
            if ($report_obj = $report_handler->get($rid)) {
                $report_handler->delete($report_obj);
            }
        }
        // irmtfan add message
        redirect_header("admin_report.php?item={$item}" . (empty($start) ? "" : "&start={$start}"), 1, _AM_NEWBB_REPORTDELETE);

        break;

    case "default":
    default:
        include_once $GLOBALS['xoops']->path('modules/' . $xoopsModule->getVar("dirname", "n") . "/class/xoopsformloader.php");
        mod_loadFunctions("user", "newbb");

        if ($item != 'processed') {
            $process_result = 0;
            $item_other     = 'processed';
            $title_other    = _AM_NEWBB_PROCESSEDREPORT;
            $extra          = _AM_NEWBB_REPORTEXTRA;
        } else {
            $process_result = 1;
            $item_other     = 'process';
            $title_other    = _AM_NEWBB_PROCESSREPORT;
            $extra          = _DELETE;
        }

        $limit = 10;
        if ($newXoopsModuleGui) echo $indexAdmin->addNavigation('admin_report.php');
        //if (!$newXoopsModuleGui) loadModuleAdminMenu(6,_AM_NEWBB_REPORTADMIN);
        //	else echo $indexAdmin->addNavigation('admin_report.php') ;

        echo "<br /><a style='border: 1px solid #5E5D63; color: #000000; font-family: verdana, tahoma, arial, helvetica, sans-serif; font-size: 1em; padding: 4px 8px; text-align:center;' href=\"admin_report.php?item=$item_other\">" . $title_other . "</a><br /><br />";

        echo '<form action="' . xoops_getenv('PHP_SELF') . '" method="post">';
        echo "<table border='0' cellpadding='4' cellspacing='1' width='100%' class='outer'>";
        echo "<tr align='center'>";
        echo "<td class='bg3' width='80%'>" . _AM_NEWBB_REPORTTITLE . "</td>";
        echo "<td class='bg3' width='10%'>" . $extra . "</td>";
        echo "</tr>";

        $reports = $report_handler->getAllReports(0, "ASC", $limit, $start, $process_result);
        foreach ($reports as $report) {
            $post_link = "<a href=\"" . XOOPS_URL . "/modules/" . $xoopsModule->getVar('dirname') . "/viewtopic.php?post_id=" . $report['post_id'] . "&amp;topic_id=" . $report['topic_id'] . "&amp;forum=" . $report['forum_id'] . "&amp;viewmode=thread\" target=\"checkreport\">" . $myts->htmlSpecialChars($report['subject']) . "</a>";
            $checkbox  = '<input type="checkbox" name="report_id[' . $report['report_id'] . ']" value="1" checked="checked" />';
            if ($item != 'processed') {
                $memo = '<input type="text" name="report_memo[' . $report['report_id'] . ']" maxlength="255" size="80" />';
            } else {
                $memo = $myts->htmlSpecialChars($report['report_memo']);
            }

            echo "<tr class='odd' align='left'>";
            echo "<td>" . _AM_NEWBB_REPORTPOST . ': ' . $post_link . "</td>";
            echo "<td align='center'>" . $report['report_id'] . "</td>";
            echo "</tr>";
            echo "<tr class='odd' align='left'>";
            echo "<td>" . _AM_NEWBB_REPORTTEXT . ': ' . $myts->htmlSpecialChars($report['report_text']) . "</td>";
            $uid           = intval($report['reporter_uid']);
            $reporter_name = newbb_getUnameFromId($uid, $xoopsModuleConfig['show_realname']);
            $reporter      = (!empty($uid)) ? "<a href='" . XOOPS_URL . "/userinfo.php?uid=" . $uid . "'>" . $reporter_name . "</a><br />" : "";

            echo "<td align='center'>" . $reporter . long2ip($report['reporter_ip']) . "</td>";
            echo "</tr>";
            echo "<tr class='odd' align='left'>";
            echo "<td>" . _AM_NEWBB_REPORTMEMO . ': ' . $memo . "</td>";
            echo "<td align='center' >" . $checkbox . "</td>";
            echo "</tr>";
            echo "<tr colspan='2'><td height='2'></td></tr>";
        }
        $buttons = "";
        if ($item != 'processed') {
            $submit = new XoopsFormButton('', 'submit', _SUBMIT, 'submit');
            $buttons .= $submit->render() . " ";
        }
        $delete = new XoopsFormButton('', 'delete', _DELETE, 'submit');
        $buttons .= $delete->render() . " ";
        $cancel = new XoopsFormButton('', 'cancel', _CANCEL, 'reset');
        $buttons .= $cancel->render();
        echo "<tr colspan='2'><td align='center'>{$buttons}</td></tr>";
        $hidden = new XoopsFormHidden('start', $start);
        echo $hidden->render();
        $hidden = new XoopsFormHidden('item', $item);
        echo $hidden->render() . "</form>";

        echo "</table>";

        $nav = new XoopsPageNav($report_handler->getCount(new Criteria("report_result", $process_result)), $limit, $start, "start", "item=" . $item);
        echo $nav->renderNav(4);

        echo "</fieldset>";

        break;
}
xoops_cp_footer();
