<?php declare(strict_types=1);
/*
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 */

/**
 * @copyright    XOOPS Project (https://xoops.org)/
 * @license      GNU GPL 2.0 or later (https://www.gnu.org/licenses/gpl-2.0.html)
 * @author       Kazumi Ono (AKA onokazu) http://www.myweb.ne.jp/, https://xoops.org/, http://jp.xoops.org/
 * @author       XOOPS Development Team
 */

use Xmf\Request;

require_once __DIR__ . '/admin_header.php';

$op = $op = Request::getString('op', Request::getCmd('op', '', 'POST'), 'GET'); //!empty($_GET['op'])? $_GET['op'] : (!empty($_POST['op'])?$_POST['op']:"");

switch ($op) {
    case 'delvotes':
        $rid      = Request::getInt('rid', 0, 'GET');
        $topic_id = Request::getInt('topic_id', 0, 'GET');
        $sql      = $GLOBALS['xoopsDB']->queryF('DELETE FROM ' . $GLOBALS['xoopsDB']->prefix('newbb_votedata') . " WHERE ratingid = $rid");
        $GLOBALS['xoopsDB']->query($sql);

        $query      = 'SELECT rating FROM ' . $GLOBALS['xoopsDB']->prefix('newbb_votedata') . ' WHERE topic_id = ' . $topic_id . ' ';
        $voteresult = $GLOBALS['xoopsDB']->query($query);
        if (!$GLOBALS['xoopsDB']->isResultSet($voteresult)) {
            \trigger_error("Query Failed! SQL: $query- Error: " . $GLOBALS['xoopsDB']->error(), E_USER_ERROR);
        }
        $votesDB     = $GLOBALS['xoopsDB']->getRowsNum($voteresult);
        $totalrating = 0;
        while ([$rating] = $GLOBALS['xoopsDB']->fetchRow($voteresult)) {
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
        if (!$GLOBALS['xoopsDB']->isResultSet($results)) {
            \trigger_error("Query Failed! SQL: $sql- Error: " . $GLOBALS['xoopsDB']->error(), E_USER_ERROR);
        }
        $votes = $GLOBALS['xoopsDB']->getRowsNum($results);

        $sql     = 'SELECT rating FROM ' . $GLOBALS['xoopsDB']->prefix('newbb_votedata') . ' ';
        $result2 = $GLOBALS['xoopsDB']->query($sql, 20, $start);
        if (!$GLOBALS['xoopsDB']->isResultSet($result2)) {
            \trigger_error("Query Failed! SQL: $sql- Error: " . $GLOBALS['xoopsDB']->error(), E_USER_ERROR);
        }
        $uservotes     = $GLOBALS['xoopsDB']->getRowsNum($result2);
        $useravgrating = 0;

        while ([$rating2] = $GLOBALS['xoopsDB']->fetchRow($result2)) {
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
        while ([$ratingid, $topic_id, $ratinguser, $rating, $ratinghostname, $ratingtimestamp] = $GLOBALS['xoopsDB']->fetchRow($results)) {
            $sql    = 'SELECT topic_title FROM ' . $GLOBALS['xoopsDB']->prefix('newbb_topics') . ' WHERE topic_id=' . $topic_id . ' ';
            $result = $GLOBALS['xoopsDB']->query($sql);
            if (!$GLOBALS['xoopsDB']->isResultSet($result)) {
                \trigger_error("Query Failed! SQL: $sql- Error: " . $GLOBALS['xoopsDB']->error(), E_USER_ERROR);
            }
            $down_array = $GLOBALS['xoopsDB']->fetchArray($result);

            $formatted_date = formatTimestamp($ratingtimestamp, _DATESTRING);
            $ratinguname    = newbbGetUnameFromId($ratinguser, $GLOBALS['xoopsModuleConfig']['show_realname']);
            echo "
        <tr>\n
        <td class='head' align='center'>$ratingid</td>\n
        <td class='even' align='center'>$ratinguname</td>\n
        <td class='even' align='center' >$ratinghostname</td>\n
        <td class='even' align='left'><a href='" . XOOPS_URL . '/modules/newbb/viewtopic.php?topic_id=' . $topic_id . "' target='topic'>" . htmlspecialchars((string)(isset($down_array['topic_title'])??''), ENT_QUOTES | ENT_HTML5) . "</a></td>\n
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
