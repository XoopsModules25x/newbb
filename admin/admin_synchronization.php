<?php
/**
 * newbb
 *
 * @copyright      XOOPS Project (https://xoops.org)
 * @license        GNU GPL 2 or later (http://www.gnu.org/licenses/gpl-2.0.html)
 * @author         Taiwen Jiang (phppp or D.J.) <php_pp@hotmail.com>
 * @since          4.00
 * @package        module::newbb
 */

use Xmf\Request;

// irmtfan - TODO - should be changed completly with Newbb new function newbbSynchronization
require_once __DIR__ . '/admin_header.php';
xoops_cp_header();
require_once __DIR__ . '/../include/functions.recon.php';
$form = '';
$form .= $adminObject->displayNavigation(basename(__FILE__));

//if (!empty($_GET['type'])) {
$start = Request::getInt('start', 0, 'GET'); //(int)( @$_GET['start'] );

switch (Request::getString('type', '', 'GET')) {// @$_GET['type'])
    // irmtfan rewrite forum sync
    case 'forum':
        $result = newbbSynchronization('forum');
        if (!empty($result)) {
            redirect_header('admin_synchronization.php', 2, _AM_NEWBB_SYNC_TYPE_FORUM . '<br>' . _AM_NEWBB_DATABASEUPDATED);
        }
        break;
    // irmtfan rewrite topic sync
    case 'topic':
        $limit = Request::getInt('limit', 1000, 'POST'); //empty($_GET['limit']) ? 1000 : (int)($_GET['limit']);
        //        /** @var Newbb\TopicHandler $topicHandler */
        //        $topicHandler = Newbb\Helper::getInstance()->getHandler('Topic');
        $criteria = new \Criteria('approved', 1);
        if ($start >= ($count = $topicHandler->getCount($criteria))) {
            break;
        }
        $criteria->setStart($start);
        $criteria->setLimit($limit);
        $topicObjs = [];
        $topicObjs = $topicHandler->getAll($criteria);
        foreach ($topicObjs as $tObj) {
            $topicHandler->synchronization($tObj);
        }
        $result = newbbSynchronization('topic');
        redirect_header('admin_synchronization.php?type=topic&amp;start=' . ($start + $limit) . "&amp;limit={$limit}", 2, _AM_NEWBB_SYNCHING . " {$count}: {$start} - " . ($start + $limit));
        break;
    // irmtfan rewrite post sync
    case 'post':
        $result = newbbSynchronization('post');
        if (!empty($result)) {
            redirect_header('admin_synchronization.php', 2, _AM_NEWBB_SYNC_TYPE_POST . '<br>' . _AM_NEWBB_DATABASEUPDATED);
        }
        break;
    // irmtfan - user is not in recon functions - only here
    case 'user':
        $limit = Request::getInt('limit', 1000, 'GET'); //empty($_GET['limit']) ? 1000 : (int)($_GET['limit']);
        /** @var \XoopsUserHandler $userHandler */
        $userHandler = xoops_getHandler('user');
        if ($start >= ($count = $userHandler->getCount())) {
            break;
        }
        $sql    = '    SELECT uid' . '    FROM ' . $GLOBALS['xoopsDB']->prefix('users');
        $result = $GLOBALS['xoopsDB']->query($sql, $limit, $start);
        while (false !== (list($uid) = $GLOBALS['xoopsDB']->fetchRow($result))) {
            // irmtfan approved=1 AND
            $sql = '    SELECT count(*)' . '    FROM ' . $GLOBALS['xoopsDB']->prefix('newbb_topics') . "    WHERE topic_poster = {$uid}";
            $ret = $GLOBALS['xoopsDB']->query($sql);
            list($topics) = $GLOBALS['xoopsDB']->fetchRow($ret);
            // irmtfan approved=1 AND
            $sql = '    SELECT count(*)' . '    FROM ' . $GLOBALS['xoopsDB']->prefix('newbb_topics') . "    WHERE topic_digest > 0 AND topic_poster = {$uid}";
            $ret = $GLOBALS['xoopsDB']->query($sql);
            list($digests) = $GLOBALS['xoopsDB']->fetchRow($ret);
            // irmtfan approved=1 AND
            $sql = '    SELECT count(*), MAX(post_time)' . '    FROM ' . $GLOBALS['xoopsDB']->prefix('newbb_posts') . "    WHERE uid = {$uid}";
            $ret = $GLOBALS['xoopsDB']->query($sql);
            list($posts, $lastpost) = $GLOBALS['xoopsDB']->fetchRow($ret);

            $GLOBALS['xoopsDB']->queryF('    REPLACE INTO ' . $GLOBALS['xoopsDB']->prefix('newbb_user_stats') . "    SET uid = '{$uid}', user_topics = '{$topics}', user_posts = '{$posts}', user_digests = '{$digests}', user_lastpost = '{$lastpost}'");
        }

        redirect_header('admin_synchronization.php?type=user&amp;start=' . ($start + $limit) . "&amp;limit={$limit}", 2, _AM_NEWBB_SYNCHING . " {$count}: {$start} - " . ($start + $limit));
        break;
    // irmtfan rewrite stats reset
    case 'stats':
        $result = newbbSynchronization('stats');
        break;
    // START irmtfan add read sync
    case 'read':
        $result = newbbSynchronization(['readtopic', 'readforum']);
        if (!empty($result)) {
            redirect_header('admin_synchronization.php', 2, _AM_NEWBB_SYNC_TYPE_READ . '<br>' . _AM_NEWBB_DATABASEUPDATED);
        }
        exit();
    // END irmtfan add read sync
    case 'misc':
    default:
        newbbSynchronization();
        break;
}

// <legend style="font-weight: bold; color: #900;">' . _AM_NEWBB_SYNCFORUM . '</legend>';

$form .= '<form action="admin_synchronization.php" method="get">';
$form .= '<div style="padding: 10px 2px;">';
$form .= '<h2>' . _AM_NEWBB_SYNC_TYPE_FORUM . '</h2>';
$form .= '<input type="hidden" name="type" value="forum">';
// $form .= _AM_NEWBB_SYNC_ITEMS.'<input type="text" name="limit" value="20">'; // irmtfan remove
$form .= '<input type="submit" name="submit" value=' . _SUBMIT . ' />';
$form .= '</div>';
$form .= '</form>';

$form .= '<form action="admin_synchronization.php" method="get">';
$form .= '<div style="padding: 10px 2px;">';
$form .= '<h2>' . _AM_NEWBB_SYNC_TYPE_TOPIC . '</h2>';
$form .= '<input type="hidden" name="type" value="topic">';
$form .= _AM_NEWBB_SYNC_ITEMS . '<input type="text" name="limit" value="1000">';
$form .= '<input type="submit" name="submit" value=' . _SUBMIT . ' />';
$form .= '</div>';
$form .= '</form>';

$form .= '<form action="admin_synchronization.php" method="get">';
$form .= '<div style="padding: 10px 2px;">';
$form .= '<h2>' . _AM_NEWBB_SYNC_TYPE_POST . '</h2>';
$form .= '<input type="hidden" name="type" value="post">';
// $form .= _AM_NEWBB_SYNC_ITEMS.'<input type="text" name="limit" value="1000">'; // irmtfan remove
$form .= '<input type="submit" name="submit" value=' . _SUBMIT . ' />';
$form .= '</div>';
$form .= '</form>';

$form .= '<form action="admin_synchronization.php" method="get">';
$form .= '<div style="padding: 10px 2px;">';
$form .= '<h2>' . _AM_NEWBB_SYNC_TYPE_USER . '</h2>';
$form .= '<input type="hidden" name="type" value="user">';
$form .= _AM_NEWBB_SYNC_ITEMS . '<input type="text" name="limit" value="1000">';
$form .= '<input type="submit" name="submit" value=' . _SUBMIT . ' />';
$form .= '</div>';
$form .= '</form>';
// START irmtfan add read sync
$form .= '<form action="admin_synchronization.php" method="get">';
$form .= '<div style="padding: 10px 2px;">';
$form .= '<h2>' . _AM_NEWBB_SYNC_TYPE_READ . '</h2>';
$form .= '<input type="hidden" name="type" value="read">';
$form .= '<input type="submit" name="submit" value=' . _SUBMIT . ' />';
$form .= '</div>';
$form .= '</form>';
// END irmtfan add read sync

$form .= '<form action="admin_synchronization.php" method="get">';
$form .= '<div style="padding: 10px 2px;">';
$form .= '<h2>' . _AM_NEWBB_SYNC_TYPE_STATS . '</h2>';
$form .= '<input type="hidden" name="type" value="stats">';
//$form .= _AM_NEWBB_SYNC_ITEMS.'<input type="text" name="limit" value="1000">';
$form .= '<input type="submit" name="submit" value=' . _SUBMIT . ' />';
$form .= '</div>';
$form .= '</form>';

$form .= '<form action="admin_synchronization.php" method="get">';
$form .= '<div style="padding: 10px 2px;">';
$form .= '<h2>' . _AM_NEWBB_SYNC_TYPE_MISC . '</h2>';
$form .= '<input type="hidden" name="type" value="misc">';
$form .= '<input type="submit" name="submit" value=' . _SUBMIT . ' />';
$form .= '</div>';
$form .= '</form>';
echo "<table width='100%' border='0' cellspacing='1' class='outer'>" . "<tr><td class='odd'>";
echo $form;
echo '</td></tr></table>';
echo '<fieldset>';
echo '<legend>&nbsp;' . _MI_NEWBB_ADMENU_SYNC . '&nbsp;</legend>';
echo _AM_NEWBB_HELP_SYNC_TAB;
echo '</fieldset>';
require_once __DIR__ . '/admin_footer.php';
