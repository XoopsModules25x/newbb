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

use Xmf\Request;

require_once __DIR__ . '/admin_header.php';

$op = $op = Request::getCmd('op', Request::getCmd('op', '', 'POST'), 'GET'); //!empty($_GET['op'])? $_GET['op'] : (!empty($_POST['op'])?$_POST['op']:"");

switch ($op) {
    case 'delvotes':
        $rid      = Request::getInt('rid', 0, 'GET');
        $topic_id = Request::getInt('topic_id', 0, 'GET');
        $sql      = $GLOBALS['xoopsDB']->queryF('DELETE FROM ' . $GLOBALS['xoopsDB']->prefix('newbb_votedata') . " WHERE ratingid = $rid");
        $GLOBALS['xoopsDB']->query($sql);

        $query       = 'SELECT rating FROM ' . $GLOBALS['xoopsDB']->prefix('newbb_votedata') . ' WHERE topic_id = ' . $topic_id . ' ';
        $voteresult  = $GLOBALS['xoopsDB']->query($query);
        $votesDB     = $GLOBALS['xoopsDB']->getRowsNum($voteresult);
        $totalrating = 0;
        while (false !== (list($rating) = $GLOBALS['xoopsDB']->fetchRow($voteresult))) {
            $totalrating += $rating;
        }
        $finalrating = $totalrating / $votesDB;
        $finalrating = number_format($finalrating, 4);
        $sql         = sprintf('UPDATE `%s` SET rating = %u, votes = %u WHERE topic_id = %u', $GLOBALS['xoopsDB']->prefix('newbb_topics'), $finalrating, $votesDB, $topic_id);
        $GLOBALS['xoopsDB']->queryF($sql);

        redirect_header('admin_votedata.php', 1, _AM_NEWBB_VOTEDELETED);
        break;

    case 'main':
    default:
        $start         = Request::getInt('start', 0, 'POST');
        $useravgrating = '0';
        $uservotes     = '0';

        $sql     = 'SELECT * FROM ' . $GLOBALS['xoopsDB']->prefix('newbb_votedata') . ' ORDER BY ratingtimestamp DESC';
        $results = $GLOBALS['xoopsDB']->query($sql, 20, $start);
        $votes   = $GLOBALS['xoopsDB']->getRowsNum($results);

        $sql           = 'SELECT rating FROM ' . $GLOBALS['xoopsDB']->prefix('newbb_votedata') . ' ';
        $result2       = $GLOBALS['xoopsDB']->query($sql, 20, $start);
        $uservotes     = $GLOBALS['xoopsDB']->getRowsNum($result2);
        $useravgrating = 0;

        while (false !== (list($rating2) = $GLOBALS['xoopsDB']->fetchRow($result2))) {
            //            $useravgrating = $useravgrating + $rating2;
            $useravgrating += $rating2;
        }
        if ($useravgrating > 0) {
            //            $useravgrating = $useravgrating / $uservotes;
            $useravgrating /= $uservotes;
            $useravgrating = number_format($useravgrating, 2);
        }

        xoops_cp_header();
        $adminObject->displayNavigation(basename(__FILE__));

        echo "<div style='padding: 8px;'>\n
        <div><strong>" . _AM_NEWBB_VOTE_USERAVG . ": </strong>$useravgrating</div>\n
        <div><strong>" . _AM_NEWBB_VOTE_TOTALRATE . ": </strong>$uservotes</div>\n
        <div style='padding: 8px;'>\n
        <ul><li> " . _AM_NEWBB_VOTE_DELETEDSC . "</li></ul>
        <div>\n
        <br>\n
        <table width='100%' border='0' cellspacing='1' class='outer'>" . "<tr><td class='odd'>
        <table width='100%' cellspacing='1' cellpadding='2' class='outer'>\n
        <tr>\n
        <th align='center'>" . _AM_NEWBB_VOTE_ID . "</th>\n
        <th align='center'>" . _AM_NEWBB_VOTE_USER . "</th>\n
        <th align='center'>" . _AM_NEWBB_VOTE_IP . "</th>\n
        <th align='center'>" . _AM_NEWBB_VOTE_FILETITLE . "</th>\n
        <th align='center'>" . _AM_NEWBB_VOTE_RATING . "</th>\n
        <th align='center'>" . _AM_NEWBB_VOTE_DATE . "</th>\n
        <th align='center'>" . _AM_NEWBB_ACTION . "</th></tr>\n";

        if (0 == $votes) {
            echo "<tr><td align='center' colspan='7' class='head'>" . _AM_NEWBB_VOTE_NOVOTES . '</td></tr>';
        }
        while (false !== (list($ratingid, $topic_id, $ratinguser, $rating, $ratinghostname, $ratingtimestamp) = $GLOBALS['xoopsDB']->fetchRow($results))) {
            $sql        = 'SELECT topic_title FROM ' . $GLOBALS['xoopsDB']->prefix('newbb_topics') . ' WHERE topic_id=' . $topic_id . ' ';
            $down_array = $GLOBALS['xoopsDB']->fetchArray($GLOBALS['xoopsDB']->query($sql));

            $formatted_date = formatTimestamp($ratingtimestamp, _DATESTRING);
            $ratinguname    = newbbGetUnameFromId($ratinguser, $GLOBALS['xoopsModuleConfig']['show_realname']);
            echo "
        <tr>\n
        <td class='head' align='center'>$ratingid</td>\n
        <td class='even' align='center'>$ratinguname</td>\n
        <td class='even' align='center' >$ratinghostname</td>\n
        <td class='even' align='left'><a href='" . XOOPS_URL . '/modules/newbb/viewtopic.php?topic_id=' . $topic_id . "' target='topic'>" . $myts->htmlSpecialChars($down_array['topic_title']) . "</a></td>\n
        <td class='even' align='center'>$rating</td>\n
        <td class='even' align='center'>$formatted_date</td>\n
        <td class='even' align='center'><strong><a href='admin_votedata.php?op=delvotes&amp;topic_id=$topic_id&amp;rid=$ratingid'>" . newbbDisplayImage('p_delete', _DELETE) . "</a></strong></td>\n
        </tr>\n";
        }
        echo '</table>';
        echo '</td></tr></table>';
        //Include page navigation
        require_once $GLOBALS['xoops']->path('class/pagenav.php');
        $page    = ($votes > 10) ? _AM_NEWBB_INDEX_PAGE : '';
        $pagenav = new \XoopsPageNav($page, 20, $start, 'start');
        echo '<div align="right" style="padding: 8px;">' . $page . '' . $pagenav->renderImageNav(4) . '</div>';
        echo '<fieldset>';
        echo '<legend>&nbsp;' . _MI_NEWBB_ADMENU_VOTE . '&nbsp;</legend>';
        echo _AM_NEWBB_HELP_VOTE_TAB;
        echo '</fieldset>';
        break;
}
require_once __DIR__ . '/admin_footer.php';
