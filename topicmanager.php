<?php

use Xmf\Request;
use XoopsModules\Newbb;

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
//  Author: phppp (D.J., infomax@gmail.com)                                  //
//  URL: https://xoops.org                                                    //
//  Project: Article Project                                                 //
//  ------------------------------------------------------------------------ //
require_once __DIR__ . '/header.php';

if (Request::getString('submit', '', 'POST')) {
    foreach (['forum', 'newforum', 'newtopic'] as $getint) {
        ${$getint} = Request::getInt($getint, 0, 'POST');// (int)(@$_POST[$getint]);
    }
    foreach (['topic_id'] as $getint) {
        ${$getint} = Request::getInt($getint, 0, 'POST');// (int)(@$_POST[$getint]);
    }
    if (!is_array($topic_id)) {
        $topic_id = [$topic_id];
    }
} else {
    foreach (['forum', 'topic_id'] as $getint) {
        ${$getint} = Request::getInt($getint, 0, 'GET');// (int)(@$_GET[$getint]);
    }
}

if (empty($topic_id)) {
    $redirect = empty($forum_id) ? 'index.php' : 'viewforum.php?forum={$forum}';
    $redirect = XOOPS_URL . '/modules/newbb/' . $redirect;
    redirect_header($redirect, 2, _MD_NEWBB_ERRORTOPIC);
}

///** @var Newbb\TopicHandler $topicHandler */
//$topicHandler = Newbb\Helper::getInstance()->getHandler('Topic');
///** @var Newbb\ForumHandler $forumHandler */
//$forumHandler = Newbb\Helper::getInstance()->getHandler('Forum');

if (!$forum) {
    /** @var Newbb\Topic $topicObject */
    $topicObject = $topicHandler->get((int)$topic_id);
    if (is_object($topicObject)) {
        $forum = $topicObject->getVar('forum_id');
    } else {
        $redirect = XOOPS_URL . '/modules/newbb/viewtopic.php?topic_id=' . $topic_id;
        redirect_header($redirect, 2, _MD_NEWBB_FORUMNOEXIST);
    }
    unset($topicObject);
}

if ($GLOBALS['xoopsModuleConfig']['wol_enabled']) {
    //    /** @var Newbb\OnlineHandler $onlineHandler */
    //    $onlineHandler = Newbb\Helper::getInstance()->getHandler('Online');
    $onlineHandler->init($forum);
}
// irmtfan add restore to viewtopic
$action_array = [
    'merge',
    'delete',
    'restore',
    'move',
    'lock',
    'unlock',
    'sticky',
    'unsticky',
    'digest',
    'undigest'
];
foreach ($action_array as $_action) {
    $action[$_action] = [
        'name'   => $_action,
        'desc'   => constant(strtoupper("_MD_NEWBB_DESC_{$_action}")),
        'submit' => constant(strtoupper("_MD_NEWBB_{$_action}")),
        'sql'    => "topic_{$_action}=1",
        'msg'    => constant(strtoupper("_MD_NEWBB_TOPIC{$_action}"))
    ];
}
$action['lock']['sql']     = 'topic_status = 1';
$action['unlock']['sql']   = 'topic_status = 0';
$action['unsticky']['sql'] = 'topic_sticky = 0';
$action['undigest']['sql'] = 'topic_digest = 0';
$action['digest']['sql']   = 'topic_digest = 1, digest_time = ' . time();

// Disable cache
$GLOBALS['xoopsConfig']['module_cache'][$xoopsModule->getVar('mid')] = 0;
// irmtfan include header.php after defining $xoopsOption['template_main']
require_once $GLOBALS['xoops']->path('header.php');

if (Request::getString('submit', '', 'POST')) {
    $mode = Request::getString('mode', '', 'POST');// $_POST['mode'];

    if ('delete' === $mode) {
        foreach ($topic_id as $tid) {
            $topicObject = $topicHandler->get($tid);
            $topicHandler->delete($topicObject, false);
            // irmtfan - sync topic after delete
            $topicHandler->synchronization($topicObject);
            $forumHandler->synchronization($forum);
            //$topicObject->loadFilters("delete");
            //sync($topic_id, "topic");
            //xoops_notification_deletebyitem ($xoopsModule->getVar('mid'), 'thread', $topic_id);
        }
        // irmtfan full URL
        echo $action[$mode]['msg'] . "<p><a href='" . XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname') . "/viewforum.php?forum=$forum'>" . _MD_NEWBB_RETURNTOTHEFORUM . "</a></p><p><a href='index.php'>" . _MD_NEWBB_RETURNFORUMINDEX . '</a></p>';
    } elseif ('restore' === $mode) {
        //$topicHandler = Newbb\Helper::getInstance()->getHandler('Topic');
        $forums       = [];
        $topicsObject = $topicHandler->getAll(new \Criteria('topic_id', '(' . implode(',', $topic_id) . ')', 'IN'));
        foreach (array_keys($topicsObject) as $id) {
            $topicObject = $topicsObject[$id];
            $topicHandler->approve($topicObject);
            $topicHandler->synchronization($topicObject);
            $forums[$topicObject->getVar('forum_id')] = 1;
        }
        //irmtfan remove - no need to approve posts manually - see class/post.php approve function
        $criteria_forum = new \Criteria('forum_id', '(' . implode(',', array_keys($forums)) . ')', 'IN');
        $forumsObject   = $forumHandler->getAll($criteria_forum);
        foreach (array_keys($forumsObject) as $id) {
            $forumHandler->synchronization($forumsObject[$id]);
        }
        unset($topicsObject, $forumsObject);
        // irmtfan add restore to viewtopic
        $restoretopic_id = $topicObject->getVar('topic_id');
        // irmtfan / missing in URL
        echo $action[$mode]['msg']
             . "<p><a href='"
             . XOOPS_URL
             . '/modules/'
             . $xoopsModule->getVar('dirname')
             . "/viewtopic.php?topic_id=$restoretopic_id'>"
             . _MD_NEWBB_VIEWTHETOPIC
             . '</a></p>'
             . "<p><a href='"
             . XOOPS_URL
             . '/modules/'
             . $xoopsModule->getVar('dirname')
             . "/viewforum.php?forum=$forum'>"
             . _MD_NEWBB_RETURNTOTHEFORUM
             . '</a></p>'
             . "<p><a href='index.php'>"
             . _MD_NEWBB_RETURNFORUMINDEX
             . '</a></p>';
    } elseif ('merge' === $mode) {
        //        /** @var PostHandler $postHandler */
        //        $postHandler = Newbb\Helper::getInstance()->getHandler('Post');
        //        /** @var Newbb\RateHandler $rateHandler */
        //        $rateHandler = Newbb\Helper::getInstance()->getHandler('Rate');

        foreach ($topic_id as $tid) {
            $topicObject    = $topicHandler->get($tid);
            $newtopicObject = $topicHandler->get($newtopic);

            /* return false if destination topic is not existing */
            // irmtfan bug fix: the old topic will be deleted if user input a not exist new topic
            if (!is_object($newtopicObject)) {
                $redirect = XOOPS_URL . '/modules/newbb/viewtopic.php?topic_id=' . $tid;
                redirect_header($redirect, 2, _MD_NEWBB_ERRORTOPIC);
            }
            $criteria_topic = new \Criteria('topic_id', $tid);
            $criteria       = new \CriteriaCompo($criteria_topic);
            $criteria->add(new \Criteria('pid', 0));
            // irmtfan OR change to this for less query?:
            // $postHandler->updateAll("pid", $newtopicObject->getVar("topic_last_post_id"), $criteria, true);
            $postHandler->updateAll('pid', $topicHandler->getTopPostId($newtopic), $criteria, true);
            $postHandler->updateAll('topic_id', $newtopic, $criteria_topic, true);
            // irmtfan update vote data instead of deleting them
            $rateHandler->updateAll('topic_id', $newtopic, $criteria_topic, true);

            $topic_views = $topicObject->getVar('topic_views') + $newtopicObject->getVar('topic_views');
            // irmtfan better method to update topic_views in new topic
            //$criteria_newtopic = new \Criteria('topic_id', $newtopic);
            //$topicHandler->updateAll('topic_views', $topic_views, $criteria_newtopic, true);
            $newtopicObject->setVar('topic_views', $topic_views);
            // START irmtfan poll_module and rewrite the method
            // irmtfan only move poll in old topic to new topic if new topic has not a poll
            $poll_id = $topicObject->getVar('poll_id');
            if ($poll_id > 0 && (0 == $newtopicObject->getVar('poll_id'))) {
                $newtopicObject->setVar('topic_haspoll', 1);
                $newtopicObject->setVar('poll_id', $poll_id);
                $poll_id = 0;// set to not delete the poll
                $topicObject->setVar('topic_haspoll', 0); // set to not delete the poll
                $topicObject->setVar('poll_id', 0);// set to not delete the poll
            }
            //update and sync newtopic after merge
            //$topicHandler->insert($newtopicObject, true);
            $topicHandler->synchronization($newtopicObject); // very important: only use object
            //sync newforum after merge
            $newforum = $newtopicObject->getVar('forum_id');
            $forumHandler->synchronization($newforum);
            //hardcode remove force to delete old topic from database
            //$topicHandler->delete($topicObject,true); // cannot use this
            $topicHandler->deleteAll($criteria_topic, true); // $force = true
            //delete poll if old topic had a poll
            $topicObject->deletePoll($poll_id);
            //sync forum after delete old topic
            $forumHandler->synchronization($forum);
            // END irmtfan poll_module and rewrite the method
        }
        echo $action[$mode]['msg']
             . // irmtfan full URL
             "<p><a href='"
             . XOOPS_URL
             . '/modules/'
             . $xoopsModule->getVar('dirname')
             . "/viewtopic.php?topic_id=$newtopic'>"
             . _MD_NEWBB_VIEWTHETOPIC
             . '</a></p>'
             . "<p><a href='"
             . XOOPS_URL
             . '/modules/'
             . $xoopsModule->getVar('dirname')
             . "/viewforum.php?forum=$forum'>"
             . _MD_NEWBB_RETURNTOTHEFORUM
             . '</a></p>'
             . "<p><a href='"
             . XOOPS_URL
             . '/modules/'
             . $xoopsModule->getVar('dirname')
             . "/index.php'>"
             . _MD_NEWBB_RETURNFORUMINDEX
             . '</a></p>';
    } elseif ('move' === $mode) {
        if ($newforum > 0) {
            $topic_id    = $topic_id[0];
            $topicObject = $topicHandler->get($topic_id);
            $topicObject->loadFilters('update');
            $topicObject->setVar('forum_id', $newforum, true);
            $topicHandler->insert($topicObject, true);
            $topicObject->loadFilters('update');

            $sql = sprintf('UPDATE "%s" SET forum_id = "%u" WHERE topic_id = "%u"', $GLOBALS['xoopsDB']->prefix('newbb_posts'), $newforum, $topic_id);
            if (!$r = $GLOBALS['xoopsDB']->query($sql)) {
                return false;
            }
            $forumHandler->synchronization($forum);
            $forumHandler->synchronization($newforum);
            // irmtfan full URL
            echo $action[$mode]['msg']
                 . "<p><a href='"
                 . XOOPS_URL
                 . '/modules/'
                 . $xoopsModule->getVar('dirname')
                 . "/viewtopic.php?topic_id=$topic_id&amp;forum=$newforum'>"
                 . _MD_NEWBB_GOTONEWFORUM
                 . "</a></p><p><a href='"
                 . XOOPS_URL
                 . "/modules/newbb/index.php'>"
                 . _MD_NEWBB_RETURNFORUMINDEX
                 . '</a></p>';
        } else {
            // irmtfan - issue with javascript:history.go(-1)
            redirect_header(Request::getString('HTTP_REFERER', '', 'SERVER'), 2, _MD_NEWBB_ERRORFORUM);
        }
    } else {
        $topic_id  = $topic_id[0];
        $forum     = $topicHandler->get($topic_id, 'forum_id');
        $forum_new = !empty($newtopic) ? $topicHandler->get($newtopic, 'forum_id') : 0;

        if (!$forumHandler->getPermission($forum, 'moderate')
            || (!empty($forum_new)
                && !$forumHandler->getPermission($forum_new, 'reply'))        // The forum for the topic to be merged to
            || (!empty($newforum) && !$forumHandler->getPermission($newforum, 'post')) // The forum to be moved to
        ) {
            redirect_header(XOOPS_URL . "/modules/newbb/viewtopic.php?forum=$forum&amp;topic_id=$topic_id", 2, _NOPERM);
        }

        if (!empty($action[$mode]['sql'])) {
            $sql = sprintf('UPDATE `%s` SET ' . $action[$mode]['sql'] . ' WHERE topic_id = %u', $GLOBALS['xoopsDB']->prefix('newbb_topics'), $topic_id);
            if (!$r = $GLOBALS['xoopsDB']->query($sql)) {
                redirect_header(XOOPS_URL . "/modules/newbb/viewtopic.php?forum=$forum&amp;topic_id=$topic_id&amp;order=$order&amp;viewmode=$viewmode", 2, _MD_NEWBB_ERROR_BACK . '<br>sql: ' . $sql);
            }
        } else {
            redirect_header(XOOPS_URL . "/modules/newbb/viewtopic.php?forum=$forum&amp;topic_id=$topic_id", 2, _MD_NEWBB_ERROR_BACK);
        }
        if ('digest' === $mode && $GLOBALS['xoopsDB']->getAffectedRows()) {
            $topicObject = $topicHandler->get($topic_id);
            //            /** @var Newbb\StatsHandler $statsHandler */
            //            $statsHandler = Newbb\Helper::getInstance()->getHandler('Stats');
            $statsHandler->update($topicObject->getVar('forum_id'), 'digest');
            //            /** @var Newbb\UserstatsHandler $userstatsHandler */
            //            $userstatsHandler = Newbb\Helper::getInstance()->getHandler('Userstats');
            if ($user_stat = $userstatsHandler->get($topicObject->getVar('topic_poster'))) {
                $z = $user_stat->getVar('user_digests') + 1;
                $user_stat->setVar('user_digests', (int)$z);
                $userstatsHandler->insert($user_stat);
            }
        }
        // irmtfan full URL
        echo $action[$mode]['msg']
             . "<p><a href='"
             . XOOPS_URL
             . '/modules/'
             . $xoopsModule->getVar('dirname')
             . "/viewtopic.php?topic_id=$topic_id&amp;forum=$forum'>"
             . _MD_NEWBB_VIEWTHETOPIC
             . "</a></p><p><a href='"
             . XOOPS_URL
             . "/modules/newbb/viewforum.php?forum=$forum'>"
             . _MD_NEWBB_RETURNFORUMINDEX
             . '</a></p>';
    }
} else {  // No submit
    $mode = Request::getString('mode', '', 'GET'); //$_GET['mode'];
    echo "<form action='" . Request::getString('PHP_SELF', '', 'SERVER') . "' method='post'>";
    echo "<table border='0' cellpadding='1' cellspacing='0' align='center' width='95%'>";
    echo "<tr><td class='bg2'>";
    echo "<table border='0' cellpadding='1' cellspacing='1' width='100%'>";
    echo "<tr class='bg3' align='left'>";
    echo "<td colspan='2' align='center'>" . $action[$mode]['desc'] . '</td></tr>';

    if ('move' === $mode) {
        echo '<tr><td class="bg3">' . _MD_NEWBB_MOVETOPICTO . '</td><td class="bg1">';
        $box = '<select name="newforum" size="1">';

        //        /** @var Newbb\CategoryHandler $categoryHandler */
        //        $categoryHandler = Newbb\Helper::getInstance()->getHandler('Category');
        $categories = $categoryHandler->getByPermission('access');
        $forums     = $forumHandler->getForumsByCategory(array_keys($categories), 'post', false);

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
        unset($forums, $categories);

        echo $box;
        echo '</select></td></tr>';
    }
    if ('merge' === $mode) {
        echo '<tr><td class="bg3">' . _MD_NEWBB_MERGETOPICTO . '</td><td class="bg1">';
        echo _MD_NEWBB_TOPIC . "&nbsp;ID-$topic_id -> ID: <input name='newtopic' value='' />";
        echo '</td></tr>';
    }
    echo '<tr class="bg3"><td colspan="2" align="center">';
    echo "<input type='hidden' name='mode' value='" . $action[$mode]['name'] . "' />";
    echo "<input type='hidden' name='topic_id' value='" . $topic_id . "' />";
    echo "<input type='hidden' name='forum' value='" . $forum . "' />";
    echo "<input type='submit' name='submit' value='" . $action[$mode]['submit'] . "' />";
    echo '</td></tr></form></table></td></tr></table>';
}
// irmtfan move to footer.php
require_once __DIR__ . '/footer.php';
include $GLOBALS['xoops']->path('footer.php');
