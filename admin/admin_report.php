<?php
//
//  ------------------------------------------------------------------------ //
//                XOOPS - PHP Content Management System                      //
//                  Copyright (c) 2000-2016 XOOPS.org                        //
//                       <https://xoops.org/>                             //
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
// URL: http://www.myweb.ne.jp/, https://xoops.org/, http://jp.xoops.org/ //
// Project: XOOPS Project                                                    //
// ------------------------------------------------------------------------- //

use Xmf\Request;

require_once __DIR__ . '/admin_header.php';
require_once $GLOBALS['xoops']->path('class/pagenav.php');

$op    = Request::getCmd('op', 'default');
$item  = Request::getString('item', 'process');
$start = Request::getInt('start', 0);

$op = Request::hasVar('submit', 'POST') ? 'save' : $op;
$op = Request::hasVar('delete', 'POST') ? 'delete' : $op;

///** @var Newbb\ReportHandler $reportHandler */
//$reportHandler = Newbb\Helper::getInstance()->getHandler('Report');

xoops_cp_header();

switch ($op) {
    case 'save':
        $report_ids = Request::getArray('report_id', '', 'POST');
        //        $report_ids = Request::getInt('report_id', 0, 'POST'); //$_POST['report_id'];
        // irmtfan add error redirect header
        if (0 === count($report_ids)) {
            redirect_header("admin_report.php?item={$item}" . (empty($start) ? '' : "&start={$start}"), 1, _AM_NEWBB_REPORTNOTSELECT);
        }
        $report_memos = Request::getArray('report_memo', [], 'POST'); // isset($_POST['report_memo']) ? $_POST['report_memo'] : array();
        foreach ($report_ids as $rid => $value) {
            if (!$value) {
                continue;
            }
            $reportObject = $reportHandler->get($rid);
            $reportObject->setVar('report_result', 1);
            $reportObject->setVar('report_memo', $report_memos[$rid]);
            $reportHandler->insert($reportObject);
        }
        // irmtfan add message
        redirect_header("admin_report.php?item={$item}" . (empty($start) ? '' : "&start={$start}"), 1, _AM_NEWBB_REPORTSAVE);

        break;

    case 'delete':
        $report_ids = Request::getArray('report_id', [], 'POST');// $_POST['report_id'];
        // irmtfan add error redirect header
        if (0 === count($report_ids)) {
            redirect_header("admin_report.php?item={$item}" . (empty($start) ? '' : "&start={$start}"), 1, _AM_NEWBB_REPORTNOTSELECT);
        }
        foreach ($report_ids as $rid => $value) {
            if (!$value) {
                continue;
            }
            if ($reportObject = $reportHandler->get($rid)) {
                $reportHandler->delete($reportObject);
            }
        }
        // irmtfan add message
        redirect_header("admin_report.php?item={$item}" . (empty($start) ? '' : "&start={$start}"), 1, _AM_NEWBB_REPORTDELETE);

        break;

    case 'default':
    default:
        require_once $GLOBALS['xoops']->path('class/xoopsformloader.php');
        require_once __DIR__ . '/../include/functions.user.php';

        if ('processed' !== $item) {
            $process_result = 0;
            $item_other     = 'admin_report.php?item=processed';
            $title_other    = _AM_NEWBB_PROCESSEDREPORT;
            $extra          = _AM_NEWBB_REPORTEXTRA;
        } else {
            $process_result = 1;
            $item_other     = 'admin_report.php?item=process';
            $title_other    = _AM_NEWBB_PROCESSREPORT;
            $extra          = _DELETE;
        }

        $limit = 10;

        $adminObject->displayNavigation(basename(__FILE__));

        //if (!$newXoopsModuleGui) loadModuleAdminMenu(6,_AM_NEWBB_REPORTADMIN);
        //    else $adminObject->displayNavigation(basename(__FILE__));
        $adminObject->addItemButton($title_other, $item_other, $icon = 'add');
        $adminObject->displayButton('left');
        echo _AM_NEWBB_REPORTADMIN_HELP;
        echo "<table width='100%' border='0' cellspacing='1' class='outer'>" . "<tr><td class='odd'>";
        echo '<form action="' . xoops_getenv('PHP_SELF') . '" method="post">';
        echo "<table border='0' cellpadding='4' cellspacing='1' width='100%' class='outer'>";
        echo "<tr align='center'>";
        echo "<th class='bg3' width='80%'>" . _AM_NEWBB_REPORTTITLE . '</th>';
        echo "<th class='bg3' width='10%'>" . $extra . '</th>';
        echo '</tr>';

        $reports = $reportHandler->getAllReports('report_id', 'ASC', $limit, $start, $process_result);
        foreach ($reports as $report) {
            $post_link = '<a href="'
                         . XOOPS_URL
                         . '/modules/'
                         . $xoopsModule->getVar('dirname')
                         . '/viewtopic.php?post_id='
                         . $report['post_id']
                         . '&amp;topic_id='
                         . $report['topic_id']
                         . '&amp;forum='
                         . $report['forum_id']
                         . '&amp;viewmode=thread" target="checkreport">'
                         . $myts->htmlSpecialChars($report['subject'])
                         . '</a>';
            $checkbox  = '<input type="checkbox" name="report_id[' . $report['report_id'] . ']" value="1" checked />';
            if ('processed' !== $item) {
                $memo = '<input type="text" name="report_memo[' . $report['report_id'] . ']" maxlength="255" size="80" />';
            } else {
                $memo = $myts->htmlSpecialChars($report['report_memo']);
            }
            echo "<tr class='odd' align='left'>";
            echo '<td>' . _AM_NEWBB_REPORTPOST . ': ' . $post_link . '</td>';
            echo "<td align='center'>" . $report['report_id'] . '</td>';
            echo '</tr>';
            echo "<tr class='odd' align='left'>";
            echo '<td>' . _AM_NEWBB_REPORTTEXT . ': ' . $myts->htmlSpecialChars($report['report_text']) . '</td>';
            $uid           = (int)$report['reporter_uid'];
            $reporter_name = newbbGetUnameFromId($uid, $GLOBALS['xoopsModuleConfig']['show_realname']);
            $reporter      = !empty($uid) ? "<a href='" . XOOPS_URL . '/userinfo.php?uid=' . $uid . "'>" . $reporter_name . '</a><br>' : '';
            echo "<td align='center'>" . $reporter . $report['reporter_ip'] . '</td>';
            echo '</tr>';
            echo "<tr class='odd' align='left'>";
            echo '<td>' . _AM_NEWBB_REPORTMEMO . ': ' . $memo . '</td>';
            echo "<td align='center' >" . $checkbox . '</td>';
            echo '</tr>';
            echo "<tr colspan='2'><td height='2'></td></tr>";
        }
        $buttons = '';
        if ('processed' !== $item) {
            $submit  = new \XoopsFormButton('', 'submit', _SUBMIT, 'submit');
            $buttons .= $submit->render() . ' ';
        }
        $delete  = new \XoopsFormButton('', 'delete', _DELETE, 'submit');
        $buttons .= $delete->render() . ' ';
        $cancel  = new \XoopsFormButton('', 'cancel', _CANCEL, 'reset');
        $buttons .= $cancel->render();
        echo "<tr colspan='2'><td align='center'>{$buttons}</td></tr>";
        $hidden = new \XoopsFormHidden('start', $start);
        echo $hidden->render();
        $hidden = new \XoopsFormHidden('item', $item);
        echo $hidden->render() . '</form>';

        echo '</table>';
        echo '</td></tr></table>';
        $nav = new \XoopsPageNav($reportHandler->getCount(new \Criteria('report_result', $process_result)), $limit, $start, 'start', 'item=' . $item);
        echo $nav->renderNav(4);
        echo '<fieldset>';
        echo '<legend>&nbsp;' . _MI_NEWBB_ADMENU_REPORT . '&nbsp;</legend>';
        echo _AM_NEWBB_HELP_REPORT_TAB;
        echo '</fieldset>';
        break;
}
require_once __DIR__ . '/admin_footer.php';
