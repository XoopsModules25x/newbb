<?php
/**
 * NewBB 4.3x, the forum module for XOOPS project
 *
 * @copyright    XOOPS Project (http://xoops.org)
 * @license        http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author        Taiwen Jiang (phppp or D.J.) <phppp@users.sourceforge.net>
 * @since        4.00
 * @version        $Id $
 * @package        module::newbb
 */

include_once __DIR__ . '/header.php';

$ok = XoopsRequest::getInt('ok', 0, 'POST');

//foreach (array('forum', 'topic_id', 'post_id', 'order', 'pid', 'act') as $getint) {
//    ${$getint} = XoopsRequest::getInt($getint, 0, 'POST');
//}
foreach (array('forum', 'topic_id', 'post_id', 'order', 'pid', 'act') as $getint) {
    ${$getint} = !empty(${$getint}) ? ${$getint} : (XoopsRequest::getInt($getint, 0, 'GET'));
}
//$viewmode = (isset($_GET['viewmode']) && $_GET['viewmode'] !== 'flat') ? 'thread' : 'flat';
//$viewmode = ($viewmode) ? $viewmode: (isset($_POST['viewmode'])?$_POST['viewmode'] : 'flat');

$viewmode = (XoopsRequest::getString('viewmode', '', 'GET') && XoopsRequest::getString('viewmode', '', 'GET') !== 'flat') ? 'thread' : 'flat';
$viewmode = ($viewmode) ? : (XoopsRequest::getString('viewmode', '', 'POST') ? : 'flat');

$forumHandler =& xoops_getmodulehandler('forum', 'newbb');
$topicHandler =& xoops_getmodulehandler('topic', 'newbb');
$postHandler  =& xoops_getmodulehandler('post', 'newbb');

if (!empty($post_id)) {
    $topic =& $topicHandler->getByPost($post_id);
} else {
    $topic =& $topicHandler->get($topic_id);
}
$topic_id = $topic->getVar('topic_id');
if (!$topic_id) {
    $redirect = empty($forum) ? 'index.php' : 'viewforum.php?forum=' . $forum;
    $redirect = XOOPS_URL . '/modules/newbb/' . $redirect;
    redirect_header($redirect, 2, _MD_ERRORTOPIC);
}

$forum     = $topic->getVar('forum_id');
$forum_obj =& $forumHandler->get($forum);
if (!$forumHandler->getPermission($forum_obj)) {
    redirect_header(XOOPS_URL .'/index.php', 2, _MD_NORIGHTTOACCESS);
}

$isadmin = newbb_isAdmin($forum_obj);
$uid     = is_object($GLOBALS['xoopsUser']) ? $GLOBALS['xoopsUser']->getVar('uid') : 0;

$post_obj     =& $postHandler->get($post_id);
$topic_status = $topic->getVar('topic_status');
if ($topicHandler->getPermission($topic->getVar("forum_id"), $topic_status, 'delete')
    && ($isadmin || $post_obj->checkIdentity())
) {
} else {
    redirect_header(XOOPS_URL . "/modules/newbb/viewtopic.php?topic_id=$topic_id&amp;pid=$pid&amp;forum=$forum", 2, _MD_DELNOTALLOWED);
}

if (!$isadmin && !$post_obj->checkTimelimit('delete_timelimit')) {
    redirect_header(XOOPS_URL . "/modules/newbb/viewtopic.php?forum=$forum&amp;topic_id=$topic_id&amp;post_id=$post_id&amp;pid=$pid", 2, _MD_TIMEISUPDEL);
}

if ($GLOBALS['xoopsModuleConfig']['wol_enabled']) {
    $online_handler =& xoops_getmodulehandler('online', 'newbb');
    $online_handler->init($forum_obj);
}

if ($ok) {
    $isDeleteOne = (1 === $ok) ? true : false;
    if ($post_obj->isTopic() && $topic->getVar("topic_replies") === 0) {
        $isDeleteOne = false;
    }
    if ($isDeleteOne && $post_obj->isTopic() && $topic->getVar("topic_replies") > 0) {
        //$postHandler->emptyTopic($post_obj);
        redirect_header(XOOPS_URL . "/modules/newbb/viewtopic.php?topic_id=$topic_id&amp;pid=$pid&amp;forum=$forum", 2, _MD_POSTFIRSTWITHREPLYNODELETED);
    } else {
        if (XoopsRequest::getString('post_text', '', 'POST')) {
            //send a message
            $member_handler =& xoops_gethandler('member');
            $senduser       =& $member_handler->getUser($post_obj->getVar('uid'));
            if ($senduser->getVar('notify_method') > 0) {
                $xoopsMailer =& xoops_getMailer();
                $xoopsMailer->reset();
                if (1 === $senduser->getVar('notify_method')) {
                    $xoopsMailer->usePM();
                } else {
                    $xoopsMailer->useMail();
                }
                $xoopsMailer->setHTML(true);
                $xoopsMailer->setToUsers($senduser);
                $xoopsMailer->setFromName($GLOBALS['xoopsUser']->getVar('uname'));
                $xoopsMailer->setSubject(_MD_DELEDEDMSG_SUBJECT);
                $forenurl = "<a href=\"" . XOOPS_URL . "/modules/" . $xoopsModule->getVar('dirname') . "/viewtopic.php?topic_id=" . $post_obj->getVar('topic_id') . "\">" . $post_obj->getVar('subject') . "</a>";
                if (!empty($GLOBALS['xoopsModuleConfig']['do_rewrite'])) {
                    $forenurl = seo_urls($forenurl);
                }
                $body = sprintf(_MD_DELEDEDMSG_BODY, $senduser->getVar('uname'), $forenurl, XoopsRequest::getString('post_text', '', 'POST'), $GLOBALS['xoopsUser']->getVar('uname'), $GLOBALS['xoopsConfig']['sitename'], XOOPS_URL . "/");
                $body = $myts->nl2Br($body);
                $xoopsMailer->setBody($body);
                $xoopsMailer->send();
            }
        }
        $postHandler->delete($post_obj, $isDeleteOne);
        $forumHandler->synchronization($forum);
        $topicHandler->synchronization($topic_id);
        $statsHandler = &xoops_getmodulehandler('stats', 'newbb');
        $statsHandler->reset();
    }

    $post_obj->loadFilters('delete');
    if ($isDeleteOne) {
        redirect_header(XOOPS_URL . "/modules/newbb/viewtopic.php?topic_id=$topic_id&amp;order=$order&amp;viewmode=$viewmode&amp;pid=$pid&amp;forum=$forum", 2, _MD_POSTDELETED);
    } else {
        redirect_header(XOOPS_URL . "/modules/newbb/viewforum.php?forum=$forum", 2, _MD_POSTSDELETED);
    }
} else {
    include $GLOBALS['xoops']->path('header.php');
    //xoops_confirm(array('post_id' => $post_id, 'viewmode' => $viewmode, 'order' => $order, 'forum' => $forum, 'topic_id' => $topic_id, 'ok' => 1), 'delete.php', _MD_DEL_ONE);
    echo '<div class="confirmMsg">' . _MD_DEL_ONE . '<br />
          <form method="post" action="' . XOOPS_URL . '/modules/newbb/delete.php">';
    echo _MD_DELEDEDMSG . '<br />';
    echo '<textarea name="post_text" cols="50" rows="5"></textarea><br />';
    echo '<input type="hidden" name="post_id" value="' . htmlspecialchars($post_id) . '" />';
    echo '<input type="hidden" name="order" value="' . htmlspecialchars($order) . '" />';
    echo '<input type="hidden" name="forum" value="' . htmlspecialchars($forum) . '" />';
    echo '<input type="hidden" name="topic_id" value="' . htmlspecialchars($topic_id) . '" />';
    echo '<input type="hidden" name="ok" value="1" />';
    echo $GLOBALS['xoopsSecurity']->getTokenHTML();
    echo '<input type="submit" name="confirm_submit" value="' . _SUBMIT . '" title="' . _SUBMIT . '"/>
          <input type="button" name="confirm_back" value="' . _CANCEL . '" onclick="javascript:history.go(-1);" title="' . _CANCEL . '" />
          </form>
          </div>';
    if ($isadmin) {
        xoops_confirm(array('post_id' => $post_id, 'viewmode' => $viewmode, 'order' => $order, 'forum' => $forum, 'topic_id' => $topic_id, 'ok' => 99), 'delete.php', _MD_DEL_RELATED);
    }
    include $GLOBALS['xoops']->path('footer.php');
}
