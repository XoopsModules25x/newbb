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

$topic_id = Request::getInt('topic_id', 0, 'POST');
$post_id  = Request::getArray('post_id', Request::getArray('post_id', [], 'POST'), 'GET');
$uid      = Request::getInt('uid', 0, 'GET');

$op   = Request::getCmd('op', Request::getCmd('op', '', 'POST'), 'GET');
$op   = in_array($op, ['approve', 'delete', 'restore', 'split'], true) ? $op : '';
$mode = Request::getInt('mode', 1, 'GET');

if (0 === count($post_id) || 0 === count($op)) {
    // irmtfan - issue with javascript:history.go(-1)
    redirect_header(Request::getString('HTTP_REFERER', '', 'SERVER'), 2, _MD_NEWBB_NO_SELECTION);
}
///** @var PostHandler $postHandler */
//$postHandler = Newbb\Helper::getInstance()->getHandler('Post');
///** @var TopicHandler $topicHandler */
//$topicHandler = Newbb\Helper::getInstance()->getHandler('Topic');
///** @var NewbbForumHandler $forumHandler */
//$forumHandler = Newbb\Helper::getInstance()->getHandler('Forum');
if (empty($topic_id)) {
    $forumObject = null;
} else {
    $topicObject = $topicHandler->get($topic_id);
    $forum_id    = $topicObject->getVar('forum_id');
    $forumObject = $forumHandler->get($forum_id);
}
$isAdmin = newbbIsAdmin($forumObject);

if (!$isAdmin) {
    redirect_header(XOOPS_URL . '/index.php', 2, _MD_NEWBB_NORIGHTTOACCESS);
}

switch ($op) {
    case 'restore':
        $post_id = array_values($post_id);
        sort($post_id);
        $topics = [];
        $forums = [];
        foreach ($post_id as $post) {
            $postObject = $postHandler->get($post);
            if ($postObject->getVar('topic_id') < 1) {
                continue;
            }

            $postHandler->approve($postObject, true);
            $topics[$postObject->getVar('topic_id')] = 1;
            $forums[$postObject->getVar('forum_id')] = 1;
            unset($postObject);
        }
        foreach (array_keys($topics) as $topic) {
            $topicHandler->synchronization($topic);
        }
        foreach (array_keys($forums) as $forum) {
            $forumHandler->synchronization($forum);
        }
        break;
    case 'approve':
        $post_id = array_values($post_id);
        sort($post_id);
        $topics      = [];
        $forums      = [];
        $criteria    = new \Criteria('post_id', '(' . implode(',', $post_id) . ')', 'IN');
        $postsObject = $postHandler->getObjects($criteria, true);
        foreach ($post_id as $post) {
            /** @var Newbb\Post $postObject */
            $postObject = $postsObject[$post];
            if (!empty($topic_id) && $topic_id !== $postObject->getVar('topic_id')) {
                continue;
            }
            $postHandler->approve($postObject);
            $topics[$postObject->getVar('topic_id')] = $post;
            $forums[$postObject->getVar('forum_id')] = 1;
        }
        foreach (array_keys($topics) as $topic) {
            $topicHandler->synchronization($topic);
        }
        foreach (array_keys($forums) as $forum) {
            $forumHandler->synchronization($forum);
        }

        if (empty($GLOBALS['xoopsModuleConfig']['notification_enabled'])) {
            break;
        }

        $criteria_topic = new \Criteria('topic_id', '(' . implode(',', array_keys($topics)) . ')', 'IN');
        $topic_list     = $topicHandler->getList($criteria_topic, true);

        $criteria_forum = new \Criteria('forum_id', '(' . implode(',', array_keys($forums)) . ')', 'IN');
        $forum_list     = $forumHandler->getList($criteria_forum);

        require_once __DIR__ . '/include/notification.inc.php';
        /** @var \XoopsNotificationHandler $notificationHandler */
        $notificationHandler = xoops_getHandler('notification');
        foreach ($post_id as $post) {
            $tags = [];
            /** @var Newbb\Post[] $postsObject [$post] */
            $tags['THREAD_NAME'] = $topic_list[$postsObject[$post]->getVar('topic_id')];
            $tags['THREAD_URL']  = XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname') . '/viewtopic.php?topic_id=' . $postsObject[$post]->getVar('topic_id') . '&amp;forum=' . $postsObject[$post]->getVar('forum_id');
            $tags['FORUM_NAME']  = $forum_list[$postsObject[$post]->getVar('forum_id')];
            $tags['FORUM_URL']   = XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname') . '/viewforum.php?forum=' . $postsObject[$post]->getVar('forum_id');
            $tags['POST_URL']    = $tags['THREAD_URL'] . '#forumpost' . $post;
            $notificationHandler->triggerEvent('thread', $postsObject[$post]->getVar('topic_id'), 'new_post', $tags);
            $notificationHandler->triggerEvent('forum', $postsObject[$post]->getVar('forum_id'), 'new_post', $tags);
            $notificationHandler->triggerEvent('global', 0, 'new_post', $tags);
            $tags['POST_CONTENT'] = $postsObject[$post]->getVar('post_text');
            $tags['POST_NAME']    = $postsObject[$post]->getVar('subject');
            $notificationHandler->triggerEvent('global', 0, 'new_fullpost', $tags);
            $notificationHandler->triggerEvent('forum', $postsObject[$post]->getVar('forum_id'), 'new_fullpost', $tags);
        }
        break;
    case 'delete':
        $post_id = array_values($post_id);
        rsort($post_id);
        $topics = [];
        $forums = [];
        foreach ($post_id as $post) {
            $postObject = $postHandler->get($post);
            if (!empty($topic_id) && $topic_id !== $postObject->getVar('topic_id')) {
                continue;
            }
            $topics[$postObject->getVar('topic_id')] = 1;
            $forums[$postObject->getVar('forum_id')] = 1;
            $postHandler->delete($postObject, true);
            unset($postObject);
        }
        foreach (array_keys($topics) as $topic) {
            $topicHandler->synchronization($topic);
        }
        foreach (array_keys($forums) as $forum) {
            $forumHandler->synchronization($forum);
        }
        break;
    case 'split':
        /** @var Newbb\Post $postObject */
        $postObject = $postHandler->get($post_id);
        if (0 === count($post_id) || $postObject->isTopic()) {
            break;
        }
        $topic_id = $postObject->getVar('topic_id');

        $newtopic = $topicHandler->create();
        $newtopic->setVar('topic_title', $postObject->getVar('subject'), true);
        $newtopic->setVar('topic_poster', $postObject->getVar('uid'), true);
        $newtopic->setVar('forum_id', $postObject->getVar('forum_id'), true);
        $newtopic->setVar('topic_time', $postObject->getVar('post_time'), true);
        $newtopic->setVar('poster_name', $postObject->getVar('poster_name'), true);
        $newtopic->setVar('approved', 1, true);
        $topicHandler->insert($newtopic, true);
        $new_topic_id = $newtopic->getVar('topic_id');

        $pid = $postObject->getVar('pid');

        $postObject->setVar('topic_id', $new_topic_id, true);
        $postObject->setVar('pid', 0, true);
        $postHandler->insert($postObject);

        /* split a single post */
        if (1 === $mode) {
            $criteria = new \CriteriaCompo(new \Criteria('topic_id', $topic_id));
            $criteria->add(new \Criteria('pid', $post_id));
            $postHandler->updateAll('pid', $pid, $criteria, true);
        /* split a post and its children posts */
        } elseif (2 === $mode) {
            require_once $GLOBALS['xoops']->path('class/xoopstree.php');
            $mytree = new \XoopsTree($GLOBALS['xoopsDB']->prefix('newbb_posts'), 'post_id', 'pid');
            $posts  = $mytree->getAllChildId($post_id);
            if (count($posts) > 0) {
                $criteria = new \Criteria('post_id', '(' . implode(',', $posts) . ')', 'IN');
                $postHandler->updateAll('topic_id', $new_topic_id, $criteria, true);
            }
            /* split a post and all posts coming after */
        } elseif (3 === $mode) {
            $criteria = new \CriteriaCompo(new \Criteria('topic_id', $topic_id));
            $criteria->add(new \Criteria('post_id', $post_id, '>'));
            $postHandler->updateAll('topic_id', $new_topic_id, $criteria, true);

            unset($criteria);
            $criteria = new \CriteriaCompo(new \Criteria('topic_id', $new_topic_id));
            $criteria->add(new \Criteria('post_id', $post_id, '>'));
            $postHandler->identifierName = 'pid';
            $posts                       = $postHandler->getList($criteria);

            unset($criteria);
            $post_update = [];
            foreach ($posts as $postid => $pid) {
                //                if (!in_array($pid, array_keys($posts))) {
                if (!array_key_exists($pid, $posts)) {
                    $post_update[] = $pid;
                }
                if (!array_key_exists($pid, $posts)) {
                    $post_update2[] = $pid;
                }
            }
            if (count($post_update)) {
                $criteria = new \Criteria('post_id', '(' . implode(',', $post_update) . ')', 'IN');
                $postHandler->updateAll('pid', $post_id, $criteria, true);
            }
        }

        $forum_id = $postObject->getVar('forum_id');
        $topicHandler->synchronization($topic_id);
        $topicHandler->synchronization($new_topic_id);
        $sql    = sprintf('UPDATE "%s" SET forum_topics = forum_topics+1 WHERE forum_id = "%u"', $GLOBALS['xoopsDB']->prefix('newbb_forums'), $forum_id);
        $result = $GLOBALS['xoopsDB']->queryF($sql);

        break;
}
if (!empty($topic_id)) {
    redirect_header(XOOPS_URL . "/modules/newbb/viewtopic.php?topic_id=$topic_id", 2, _MD_NEWBB_DBUPDATED);
} elseif (!empty($forum_id)) {
    redirect_header(XOOPS_URL . "/modules/newbb/viewforum.php?forum=$forum_id", 2, _MD_NEWBB_DBUPDATED);
} else {
    redirect_header(XOOPS_URL . "/modules/newbb/viewpost.php?uid=$uid", 2, _MD_NEWBB_DBUPDATED);
}
// irmtfan move to footer.php
require_once __DIR__ . '/footer.php';
include $GLOBALS['xoops']->path('footer.php');
