<?php
/**
 * NewBB 5.0x,  the forum module for XOOPS project
 *
 * @copyright      XOOPS Project (https://xoops.org)
 * @license        GNU GPL 2 or later (http://www.gnu.org/licenses/gpl-2.0.html)
 * @author         Taiwen Jiang (phppp or D.J.) <phppp@users.sourceforge.net>
 * @since          4.00
 * @package        module::newbb
 */

use Xmf\Request;
use XoopsModules\Newbb;

require_once __DIR__ . '/header.php';

$forum_id = Request::getInt('forum_id', 0, 'POST');
$topic_id = Request::getArray('topic_id', null, 'POST');

$op = Request::getCmd('op', '', 'POST');
$op = in_array($op, ['approve', 'delete', 'restore', 'move'], true) ? $op : '';

if (0 === count($topic_id) || 0 === count($op)) {
    // irmtfan - issue with javascript:history.go(-1)
    redirect_header(Request::getString('HTTP_REFERER', '', 'SERVER'), 2, _MD_NEWBB_NO_SELECTION);
}

$topic_id = array_values($topic_id);
///** @var Newbb\TopicHandler|\XoopsPersistableObjectHandler $topicHandler */
//$topicHandler = Newbb\Helper::getInstance()->getHandler('Topic');
///** @var Newbb\ForumHandler|\XoopsPersistableObjectHandler $forumHandler */
//$forumHandler = Newbb\Helper::getInstance()->getHandler('Forum');

$isAdmin = newbbIsAdmin($forum_id);

if (!$isAdmin) {
    redirect_header(XOOPS_URL . '/index.php', 2, _MD_NEWBB_NORIGHTTOACCESS);
}
switch ($op) {
    case 'restore':
        $forums       = [];
        $topicsObject = $topicHandler->getAll(new \Criteria('topic_id', '(' . implode(',', $topic_id) . ')', 'IN'));
        foreach (array_keys($topicsObject) as $id) {
            /** @var Newbb\Topic $topicObject */
            $topicObject = $topicsObject[$id];
            $topicHandler->approve($topicObject);
            $topicHandler->synchronization($topicObject);
            $forums[$topicObject->getVar('forum_id')] = 1;
        }
        $criteria_forum = new \Criteria('forum_id', '(' . implode(',', array_keys($forums)) . ')', 'IN');
        $forumsObject   = $forumHandler->getAll($criteria_forum);
        foreach (array_keys($forumsObject) as $id) {
            $forumHandler->synchronization($forumsObject[$id]);
        }
        unset($topicsObject, $forumsObject);
        break;
    case 'approve':
        $forums       = [];
        $topicsObject = $topicHandler->getAll(new \Criteria('topic_id', '(' . implode(',', $topic_id) . ')', 'IN'));
        foreach (array_keys($topicsObject) as $id) {
            /** @var Newbb\Topic $topicObject */
            $topicObject = $topicsObject[$id];
            $topicHandler->approve($topicObject);
            $topicHandler->synchronization($topicObject);
            $forums[$topicObject->getVar('forum_id')] = 1;
        }

        $criteria_forum = new \Criteria('forum_id', '(' . implode(',', array_keys($forums)) . ')', 'IN');
        $forumsObject   = $forumHandler->getAll($criteria_forum);
        foreach (array_keys($forumsObject) as $id) {
            $forumHandler->synchronization($forumsObject[$id]);
        }

        if (empty($GLOBALS['xoopsModuleConfig']['notification_enabled'])) {
            break;
        }

        require_once __DIR__ . '/include/notification.inc.php';
        /** @var \XoopsNotificationHandler $notificationHandler */
        $notificationHandler = xoops_getHandler('notification');
        foreach (array_keys($topicsObject) as $id) {
            $topicObject         = $topicsObject[$id];
            $tags                = [];
            $tags['THREAD_NAME'] = $topicObject->getVar('topic_title');
            $tags['THREAD_URL']  = XOOPS_URL . '/modules/' . $moduleDirName . '/viewtopic.php?topic_id=' . $id . '&amp;forum=' . $topicObject->getVar('forum_id');
            /** @var Newbb\Forum[] $forumsObject */
            $tags['FORUM_NAME'] = $forumsObject[$topicObject->getVar('forum_id')]->getVar('forum_name');
            $tags['FORUM_URL']  = XOOPS_URL . '/modules/' . $moduleDirName . '/viewforum.php?forum=' . $topicObject->getVar('forum_id');
            $notificationHandler->triggerEvent('global', 0, 'new_thread', $tags);
            $notificationHandler->triggerEvent('forum', $topicObject->getVar('forum_id'), 'new_thread', $tags);
            $postObject       = $topicHandler->getTopPost($id);
            $tags['POST_URL'] = $tags['THREAD_URL'] . '#forumpost' . $postObject->getVar('post_id');
            $notificationHandler->triggerEvent('thread', $id, 'new_post', $tags);
            $notificationHandler->triggerEvent('forum', $topicObject->getVar('forum_id'), 'new_post', $tags);
            $notificationHandler->triggerEvent('global', 0, 'new_post', $tags);
            $tags['POST_CONTENT'] = $postObject->getVar('post_text');
            $tags['POST_NAME']    = $postObject->getVar('subject');
            $notificationHandler->triggerEvent('global', 0, 'new_fullpost', $tags);
            $notificationHandler->triggerEvent('forum', $topicObject->getVar('forum_id'), 'new_fullpost', $tags);
            unset($postObject);
        }
        unset($topicsObject, $forumsObject);
        break;
    case 'delete':
        $forums = [];
        /** @var Newbb\TopicHandler|\XoopsPersistableObjectHandler $topicHandler */
        $topicsObject = $topicHandler->getAll(new \Criteria('topic_id', '(' . implode(',', $topic_id) . ')', 'IN'));
        foreach (array_keys($topicsObject) as $id) {
            /** @var Newbb\Topic $topicObject */
            $topicObject = $topicsObject[$id];
            // irmtfan should be set to false to not delete topic from database
            $topicHandler->delete($topicObject, false);
            $topicHandler->synchronization($topicObject);
            $forums[$topicObject->getVar('forum_id')] = 1;
        }

        $criteria_forum = new \Criteria('forum_id', '(' . implode(',', array_keys($forums)) . ')', 'IN');
        $forumsObject   = $forumHandler->getAll($criteria_forum);
        foreach (array_keys($forumsObject) as $id) {
            $forumHandler->synchronization($forumsObject[$id]);
        }
        unset($topicsObject, $forumsObject);
        break;
    case 'move':
        if (Request::getInt('newforum', 0, 'POST')
            && Request::getInt('newforum', 0, 'POST') !== $forum_id
            && $forumHandler->getPermission(Request::getInt('newforum', 0, 'POST'), 'post')) {
            $criteria = new \Criteria('topic_id', '(' . implode(',', $topic_id) . ')', 'IN');
            //            /** @var Newbb\PostHandler $postHandler */
            //            $postHandler = Newbb\Helper::getInstance()->getHandler('Post');
            $postHandler->updateAll('forum_id', Request::getInt('newforum', 0, 'POST'), $criteria, true);
            $topicHandler->updateAll('forum_id', Request::getInt('newforum', 0, 'POST'), $criteria, true);
            $forumHandler->synchronization(Request::getInt('newforum', 0, 'POST'));
            $forumHandler->synchronization($forum_id);
        } else {
            include $GLOBALS['xoops']->path('header.php');
            //            /** @var Newbb\CategoryHandler $categoryHandler */
            //            $categoryHandler = Newbb\Helper::getInstance()->getHandler('Category');
            $categories = $categoryHandler->getByPermission('access');
            $forums     = $forumHandler->getForumsByCategory(array_keys($categories), 'post', false);

            $box = '<select name="newforum" size="1">';
            if (count($categories) > 0 && count($forums) > 0) {
                foreach (array_keys($forums) as $key) {

                    /** @var Newbb\Category[] $categories */
                    $box .= "<option value='-1'>[" . $categories[$key]->getVar('cat_title') . ']</option>';
                    foreach ($forums[$key] as $forumid => $_forum) {
                        $box .= "<option value='" . $forumid . "'>-- " . $_forum['title'] . '</option>';
                        if (!isset($_forum['sub'])) {
                            continue;
                        }
                        foreach (array_keys($_forum['sub']) as $fid) {
                            $box .= "<option value='" . $fid . "'>---- " . $_forum['sub'][$fid]['title'] . '</option>';
                        }
                    }
                }
            } else {
                $box .= "<option value='-1'>" . _MD_NEWBB_NOFORUMINDB . '</option>';
            }
            $box .= '</select>';
            unset($forums, $categories);

            echo "<form action='" . Request::getString('PHP_SELF', '', 'SERVER') . "' method='post'>";
            echo "<table border='0' cellpadding='1' cellspacing='0' align='center' width='95%'>";
            echo "<tr><td class='bg2'>";
            echo "<table border='0' cellpadding='1' cellspacing='1' width='100%'>";
            echo '<tr><td class="bg3">' . _MD_NEWBB_MOVETOPICTO . '</td><td class="bg1">';
            echo $box;
            echo '</td></tr>';
            echo '<tr class="bg3"><td colspan="2" align="center">';
            echo "<input type='hidden' name='op' value='move' />";
            echo "<input type='hidden' name='forum_id' value='{$forum_id}' />";
            foreach ($topic_id as $id) {
                echo "<input type='hidden' name='topic_id[]' value='" . $id . "' />";
            }
            echo "<input type='submit' name='submit' value='" . _SUBMIT . "' />";
            echo '</td></tr></table></td></tr></table>';
            echo '</form>';
            include $GLOBALS['xoops']->path('footer.php');
            exit();
        }
        break;
}
///** @var Newbb\StatsHandler $statsHandler */
//$statsHandler = Newbb\Helper::getInstance()->getHandler('Stats');
$statsHandler->reset();
if (empty($forum_id)) {
    redirect_header(XOOPS_URL . '/modules/newbb/list.topic.php', 2, _MD_NEWBB_DBUPDATED);
} else {
    redirect_header(XOOPS_URL . "/modules/newbb/viewforum.php?forum={$forum_id}", 2, _MD_NEWBB_DBUPDATED);
}
// irmtfan move to footer.php
require_once __DIR__ . '/footer.php';
include $GLOBALS['xoops']->path('footer.php');
