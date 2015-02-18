<?php
/**
 * newbb
 *
 * @copyright	The XOOPS project http://www.xoops.org/
 * @license		http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author		Taiwen Jiang (phppp or D.J.) <php_pp@hotmail.com>
 * @since		4.00
 * @version		$Id: admin_synchronization.php 62 2012-08-17 10:15:26Z alfred $
 * @package		module::newbb
 */
// irmtfan - TODO - should be changed completly with Newbb new function newbb_synchronization
include_once __DIR__ . '/admin_header.php';
xoops_cp_header();
mod_loadFunctions("recon", "newbb");
$form = '<fieldset>';

if ($newXoopsModuleGui) $form .= $indexAdmin->addNavigation('admin_synchronization.php');
//if (!$newXoopsModuleGui) loadModuleAdminMenu(12, _AM_NEWBB_SYNCFORUM);
//	else $form .= $indexAdmin->addNavigation('admin_synchronization.php');

//if (!empty($_GET['type'])) {
    $start = intval( @$_GET['start'] );

    switch (@$_GET['type']) {
    // irmtfan rewrite forum sync
    case "forum":
        $result = newbb_synchronization("forum");
        if (!empty($result)) {
            redirect_header("admin_synchronization.php", 2, _AM_NEWBB_SYNC_TYPE_FORUM . "<br />" . _AM_NEWBB_DATABASEUPDATED);
        }
        break;
    // irmtfan rewrite topic sync
    case "topic":
        $limit = empty($_GET['limit']) ? 1000 : intval($_GET['limit']);
        $topic_handler =& xoops_getmodulehandler('topic', 'newbb');
        $criteria = new Criteria("approved", 1);
        if ($start >= ($count = $topic_handler->getCount($criteria)) ) {
            break;
        }
        $criteria->setStart($start);
        $criteria->setLimit($limit);
        $topicObjs = $topic_handler->getAll($criteria);
        foreach ($topicObjs as $tObj) {
            $topic_handler->synchronization($tObj);
        }
        $result = newbb_synchronization("topic");
        redirect_header("admin_synchronization.php?type=topic&amp;start=".($start+$limit)."&amp;limit={$limit}", 2, _AM_NEWBB_SYNCHING." {$count}: {$start} - ".($start+$limit));
        break;
    // irmtfan rewrite post sync
    case "post":
        $result = newbb_synchronization("post");
        if (!empty($result)) {
            redirect_header("admin_synchronization.php", 2, _AM_NEWBB_SYNC_TYPE_POST . "<br />" . _AM_NEWBB_DATABASEUPDATED);
        }
        break;
    // irmtfan - user is not in recon functions - only here
    case "user":
        $limit = empty($_GET['limit']) ? 1000 : intval($_GET['limit']);
        $user_handler =& xoops_gethandler('user');
        if ($start >= ($count = $user_handler->getCount()) ) {
            break;
        }
        $sql =	"	SELECT uid".
                "	FROM " . $xoopsDB->prefix("users");
        $result = $xoopsDB->query($sql, $limit, $start);
        while ( list($uid) = $xoopsDB->fetchRow($result) ) {
            // irmtfan approved=1 AND
            $sql =	"	SELECT count(*)".
                    "	FROM " . $xoopsDB->prefix("bb_topics") .
                    "	WHERE topic_poster = {$uid}";
            $ret = $xoopsDB->query($sql);
            list($topics) = $xoopsDB->fetchRow($ret);
            // irmtfan approved=1 AND
            $sql =	"	SELECT count(*)".
                    "	FROM " . $xoopsDB->prefix("bb_topics") .
                    "	WHERE topic_digest > 0 AND topic_poster = {$uid}";
            $ret = $xoopsDB->query($sql);
            list($digests) = $xoopsDB->fetchRow($ret);
            // irmtfan approved=1 AND
            $sql =	"	SELECT count(*), MAX(post_time)".
                    "	FROM " . $xoopsDB->prefix("bb_posts") .
                    "	WHERE uid = {$uid}";
            $ret = $xoopsDB->query($sql);
            list($posts, $lastpost) = $xoopsDB->fetchRow($ret);

            $xoopsDB->queryF(
                    "	REPLACE INTO " . $xoopsDB->prefix("bb_user_stats") .
                    " 	SET uid = '{$uid}', user_topics = '{$topics}', user_posts = '{$posts}', user_digests = '{$digests}', user_lastpost = '{$lastpost}'"
                    );
        }

        redirect_header("admin_synchronization.php?type=user&amp;start=".($start+$limit)."&amp;limit={$limit}", 2, _AM_NEWBB_SYNCHING." {$count}: {$start} - ".($start+$limit));
        break;
    // irmtfan rewrite stats reset
    case "stats":
        $result = newbb_synchronization("stats");
        break;
    // START irmtfan add read sync
    case "read":
        $result = newbb_synchronization(array("readtopic","readforum"));
        if (!empty($result)) {
            redirect_header("admin_synchronization.php", 2, _AM_NEWBB_SYNC_TYPE_READ . "<br />" . _AM_NEWBB_DATABASEUPDATED);
        }
        exit();
    // END irmtfan add read sync
    case "misc":
    default:
        newbb_synchronization();
        break;
    }

// <legend style="font-weight: bold; color: #900;">' . _AM_NEWBB_SYNCFORUM . '</legend>';

$form .= '<form action="admin_synchronization.php" method="get">';
$form .= '<div style="padding: 10px 2px;">';
$form .= '<h2>'._AM_NEWBB_SYNC_TYPE_FORUM.'</h2>';
$form .= '<input type="hidden" name="type" value="forum">';
// $form .= _AM_NEWBB_SYNC_ITEMS.'<input type="text" name="limit" value="20">'; // irmtfan remove
$form .= '<input type="submit" name="submit" value=' . _SUBMIT . ' />';
$form .= '</div>';
$form .= '</form>';

$form .= '<form action="admin_synchronization.php" method="get">';
$form .= '<div style="padding: 10px 2px;">';
$form .= '<h2>'._AM_NEWBB_SYNC_TYPE_TOPIC.'</h2>';
$form .= '<input type="hidden" name="type" value="topic">';
$form .= _AM_NEWBB_SYNC_ITEMS.'<input type="text" name="limit" value="1000">';
$form .= '<input type="submit" name="submit" value=' . _SUBMIT . ' />';
$form .= '</div>';
$form .= '</form>';

$form .= '<form action="admin_synchronization.php" method="get">';
$form .= '<div style="padding: 10px 2px;">';
$form .= '<h2>'._AM_NEWBB_SYNC_TYPE_POST.'</h2>';
$form .= '<input type="hidden" name="type" value="post">';
// $form .= _AM_NEWBB_SYNC_ITEMS.'<input type="text" name="limit" value="1000">'; // irmtfan remove
$form .= '<input type="submit" name="submit" value=' . _SUBMIT . ' />';
$form .= '</div>';
$form .= '</form>';

$form .= '<form action="admin_synchronization.php" method="get">';
$form .= '<div style="padding: 10px 2px;">';
$form .= '<h2>'._AM_NEWBB_SYNC_TYPE_USER.'</h2>';
$form .= '<input type="hidden" name="type" value="user">';
$form .= _AM_NEWBB_SYNC_ITEMS.'<input type="text" name="limit" value="1000">';
$form .= '<input type="submit" name="submit" value=' . _SUBMIT . ' />';
$form .= '</div>';
$form .= '</form>';
// START irmtfan add read sync
$form .= '<form action="admin_synchronization.php" method="get">';
$form .= '<div style="padding: 10px 2px;">';
$form .= '<h2>'._AM_NEWBB_SYNC_TYPE_READ.'</h2>';
$form .= '<input type="hidden" name="type" value="read">';
$form .= '<input type="submit" name="submit" value=' . _SUBMIT . ' />';
$form .= '</div>';
$form .= '</form>';
// END irmtfan add read sync

$form .= '<form action="admin_synchronization.php" method="get">';
$form .= '<div style="padding: 10px 2px;">';
$form .= '<h2>'._AM_NEWBB_SYNC_TYPE_STATS.'</h2>';
$form .= '<input type="hidden" name="type" value="stats">';
//$form .= _AM_NEWBB_SYNC_ITEMS.'<input type="text" name="limit" value="1000">';
$form .= '<input type="submit" name="submit" value=' . _SUBMIT . ' />';
$form .= '</div>';
$form .= '</form>';

$form .= '<form action="admin_synchronization.php" method="get">';
$form .= '<div style="padding: 10px 2px;">';
$form .= '<h2>'._AM_NEWBB_SYNC_TYPE_MISC.'</h2>';
$form .= '<input type="hidden" name="type" value="misc">';
$form .= '<input type="submit" name="submit" value=' . _SUBMIT . ' />';
$form .= '</div>';
$form .= '</form>';

$form .= "</fieldset>";

echo $form;
xoops_cp_footer();
