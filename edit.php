<?php declare(strict_types=1);

/**
 * NewBB 5.0x,  the forum module for XOOPS project
 *
 * @copyright      XOOPS Project (https://xoops.org)
 * @license        GNU GPL 2.0 or later (https://www.gnu.org/licenses/gpl-2.0.html)
 * @author         Taiwen Jiang (phppp or D.J.) <phppp@users.sourceforge.net>
 * @since          4.00
 */

use Xmf\Request;
use XoopsModules\Newbb\{
    Category,
    CategoryHandler,
    Forum,
    ForumHandler,
    Helper,
    KarmaHandler,
    OnlineHandler,
    PostHandler,
    StatsHandler,
    Topic,
    TopicHandler
};
/** @var Category $categories */
/** @var CategoryHandler $categoryHandler */
/** @var Forum $forumsObject */
/** @var ForumHandler $forumHandler */
/** @var Helper $helper */
/** @var KarmaHandler $karmaHandler */
/** @var OnlineHandler $onlineHandler */
/** @var Post $postObject */
/** @var PostHandler $postHandler */
/** @var StatsHandler $statsHandler */
/** @var Topic $topicObject */
/** @var TopicHandler $topicHandler */
require_once __DIR__ . '/header.php';

foreach (['forum', 'topic_id', 'post_id', 'order'] as $getint) {
    ${$getint} = Request::getInt($getint, 0, 'GET');
}

if (!$topic_id || !$post_id) {
    $redirect = empty($forum) ? 'index.php' : "viewforum.php?forum={$forum}";
    redirect_header($redirect, 2, _MD_NEWBB_ERRORTOPIC);
}

$forumHandler = Helper::getInstance()->getHandler('Forum');
$topicHandler = Helper::getInstance()->getHandler('Topic');
$postHandler  = Helper::getInstance()->getHandler('Post');

$postObject  = $postHandler->get($post_id);
$topicObject = $topicHandler->get($postObject->getVar('topic_id'));
$forumObject = $forumHandler->get($postObject->getVar('forum_id'));
if (!$forumHandler->getPermission($forumObject)) {
    redirect_header('index.php', 2, _MD_NEWBB_NORIGHTTOACCESS);
}

if ($GLOBALS['xoopsModuleConfig']['wol_enabled']) {
    //    $onlineHandler = \XoopsModules\Newbb\Helper::getInstance()->getHandler('Online');
    $onlineHandler->init($forumObject);
}
$isAdmin = newbbIsAdmin($forumObject);
$uid     = is_object($GLOBALS['xoopsUser']) ? $GLOBALS['xoopsUser']->getVar('uid') : 0;

$topic_id     = $postObject->getVar('topic_id');
$topic_status = $topicObject->getVar('topic_status');
$error_msg    = null;

if (!$topicHandler->getPermission($forumObject, $topic_status, 'edit') || (!$isAdmin && !$postObject->checkIdentity())) {
    $error_msg = _MD_NEWBB_NORIGHTTOEDIT;
} elseif (!$isAdmin && !$postObject->checkTimelimit('edit_timelimit')) {
    $error_msg = _MD_NEWBB_TIMEISUP;
}

if (null !== $error_msg) {
    /*
     * Build the page query
     */
    $query_vars  = ['topic_id', 'post_id', 'forum', 'status', 'order', 'mode', 'viewmode'];
    $query_array = [];
    foreach ($query_vars as $var) {
        if (Request::getString($var, '', 'GET')) {
            $query_array[$var] = "{$var}=" . Request::getString($var, '', 'GET');
        }
    }
    $page_query = htmlspecialchars(implode('&', array_values($query_array)), ENT_QUOTES | ENT_HTML5);
    unset($query_array);
    redirect_header("viewtopic.php?{$page_query}", 2, $error_msg);
}

if ($GLOBALS['xoopsModuleConfig']['wol_enabled']) {
    //    $onlineHandler = \XoopsModules\Newbb\Helper::getInstance()->getHandler('Online');
    $onlineHandler->init($forumObject);
}

$xoopsOption['template_main']                                        = 'newbb_edit_post.tpl';
$GLOBALS['xoopsConfig']['module_cache'][$xoopsModule->getVar('mid')] = 0;
// irmtfan include header.php after defining $xoopsOption['template_main']
require_once $GLOBALS['xoops']->path('header.php');

/*
$xoopsTpl->assign('lang_forum_index', sprintf(_MD_NEWBB_FORUMINDEX, htmlspecialchars((string)$GLOBALS['xoopsConfig']['sitename'], ENT_QUOTES)));

$categoryHandler = \XoopsModules\Newbb\Helper::getInstance()->getHandler('Category');
$categoryObject = $categoryHandler->get($forumObject->getVar('cat_id'), array("cat_title"));
$xoopsTpl->assign('category', array("id" => $forumObject->getVar('cat_id'), "title" => $categoryObject->getVar('cat_title')));

$form_title = _EDIT.": <a href=\"viewtopic.php?post_id={$post_id}\">".$postObject->getVar('subject');
$xoopsTpl->assign("form_title", $form_title);

$xoopsTpl->assign("parentforum", $forumHandler->getParents($forumObject));

$xoopsTpl->assign(array(
    'forum_id'             => $forumObject->getVar('forum_id'),
    'forum_name'         => $forumObject->getVar('forum_name'),
    ));
*/

$dohtml        = $postObject->getVar('dohtml');
$dosmiley      = $postObject->getVar('dosmiley');
$doxcode       = $postObject->getVar('doxcode');
$dobr          = $postObject->getVar('dobr');
$icon          = $postObject->getVar('icon');
$attachsig     = $postObject->getVar('attachsig');
$istopic       = $postObject->isTopic() ? 1 : 0;
$isedit        = 1;
$subject       = $postObject->getVar('subject', 'E');
$message       = $postObject->getVar('post_text', 'E');
$poster_name   = $postObject->getVar('poster_name', 'E');
$attachments   = $postObject->getAttachment();
$post_karma    = $postObject->getVar('post_karma');
$require_reply = $postObject->getVar('require_reply');

$xoopsTpl->assign('error_message', _MD_NEWBB_EDITEDBY . ' ' . $GLOBALS['xoopsUser']->uname());
require_once __DIR__ . '/include/form.post.php';

///** @var Newbb\KarmaHandler $karmaHandler */
//$karmaHandler = \XoopsModules\Newbb\Helper::getInstance()->getHandler('Karma');
$user_karma = $karmaHandler->getUserKarma();

$posts_context       = [];
$posts_contextObject = $istopic ? [] : [$postHandler->get($postObject->getVar('pid'))];
foreach ($posts_contextObject as $post_contextObject) {
    if ($GLOBALS['xoopsModuleConfig']['enable_karma'] && $post_contextObject->getVar('post_karma') > 0) {
        $p_message = sprintf(_MD_NEWBB_KARMA_REQUIREMENT, '***', $post_contextObject->getVar('post_karma')) . '</div>';
    } elseif ($GLOBALS['xoopsModuleConfig']['allow_require_reply'] && $post_contextObject->getVar('require_reply')) {
        $p_message = _MD_NEWBB_REPLY_REQUIREMENT;
    } else {
        $p_message = $post_contextObject->getVar('post_text');
    }

    if ($post_contextObject->getVar('uid')) {
        $p_name = newbbGetUnameFromId($post_contextObject->getVar('uid'), $GLOBALS['xoopsModuleConfig']['show_realname']);
    } else {
        $poster_name = $post_contextObject->getVar('poster_name');
        $p_name      = empty($poster_name) ? htmlspecialchars((string)$GLOBALS['xoopsConfig']['anonymous'], ENT_QUOTES | ENT_HTML5) : $poster_name;
    }
    $p_date    = formatTimestamp($post_contextObject->getVar('post_time'));
    $p_subject = $post_contextObject->getVar('subject');

    $posts_context[] = [
        'subject' => $p_subject,
        'meta'    => _MD_NEWBB_BY . ' ' . $p_name . ' ' . _MD_NEWBB_ON . ' ' . $p_date,
        'content' => $p_message,
    ];
}
$xoopsTpl->assign_by_ref('posts_context', $posts_context);
// irmtfan move to footer.php
require_once __DIR__ . '/footer.php';
require_once $GLOBALS['xoops']->path('footer.php');
