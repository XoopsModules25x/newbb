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
//  ------------------------------------------------------------------------ //
//  Author: phppp (D.J., infomax@gmail.com)                                  //
//  URL: https://xoops.org                                                    //
//  Project: Article Project                                                 //
//  ------------------------------------------------------------------------ //

use Xmf\Request;
use XoopsModules\Newbb\{
    Helper,
    ForumHandler,
    Post,
    PostHandler,
    TopicHandler
};
/** @var Post $post_contextObject */
/** @var PostHandler $post_contextObject */
/** @var ForumHandler $forumHandler */
/** @var TopicHandler $topicHandler */
/** @var PostHandler $postHandler */
require_once __DIR__ . '/header.php';

$forum    = Request::getInt('forum', 0, 'GET');
$topic_id = Request::getInt('topic_id', 0, 'GET');
$post_id  = Request::getInt('post_id', 0, 'GET');
$order    = Request::getInt('order', 0, 'GET');
$start    = Request::getInt('start', 0, 'GET');

if (!$topic_id && !$post_id) {
    $redirect = empty($forum) ? 'index.php' : "viewforum.php?forum={$forum}";
    redirect_header($redirect, 2, _MD_NEWBB_ERRORTOPIC);
}

$forumHandler = Helper::getInstance()->getHandler('Forum');
$topicHandler = Helper::getInstance()->getHandler('Topic');
$postHandler  = Helper::getInstance()->getHandler('Post');

if (!$pid = $post_id) {
    $pid = $topicHandler->getTopPostId($topic_id);
}
$postParentObject = $postHandler->get($pid);
$topic_id         = $postParentObject->getVar('topic_id');
$forum            = $postParentObject->getVar('forum_id');
$postObject       = $postHandler->create();
$postObject->setVar('pid', $pid);
$postObject->setVar('topic_id', $topic_id);
$postObject->setVar('forum_id', $forum);

$forumObject = $forumHandler->get($forum);
if (!$forumHandler->getPermission($forumObject)) {
    redirect_header(XOOPS_URL . '/index.php', 2, _MD_NEWBB_NORIGHTTOACCESS);
}

$topicObject  = $topicHandler->get($topic_id);
$topic_status = $topicObject->getVar('topic_status');
if (!$topicHandler->getPermission($forumObject, $topic_status, 'reply')) {
    /*
     * Build the page query
     */
    $query_vars  = ['topic_id', 'post_id', 'status', 'order', 'mode', 'viewmode'];
    $query_array = [];
    foreach ($query_vars as $var) {
        if (Request::getString($var, '', 'GET')) {
            $query_array[$var] = "{$var}=" . Request::getString($var, '', 'GET');
        }
    }
    $page_query = htmlspecialchars(implode('&', array_values($query_array)), ENT_QUOTES | ENT_HTML5);
    unset($query_array);

    redirect_header("viewtopic.php?{$page_query}", 2, _MD_NEWBB_NORIGHTTOREPLY);
}

if ($GLOBALS['xoopsModuleConfig']['wol_enabled']) {
    // /** @var OnlineHandler $onlineHandler */
    //    $onlineHandler = \XoopsModules\Newbb\Helper::getInstance()->getHandler('Online');
    $onlineHandler->init($forumObject);
}

$xoopsOption['template_main']                                        = 'newbb_edit_post.tpl';
$GLOBALS['xoopsConfig']['module_cache'][$xoopsModule->getVar('mid')] = 0;
// irmtfan remove and move to footer.php
//$xoopsOption['xoops_module_header']= $xoops_module_header;
// irmtfan include header.php after defining $xoopsOption['template_main']
require_once $GLOBALS['xoops']->path('header.php');
//$xoopsTpl->assign('xoops_module_header', $xoops_module_header);

/*
$xoopsTpl->assign('lang_forum_index', sprintf(_MD_NEWBB_FORUMINDEX, htmlspecialchars((string)$GLOBALS['xoopsConfig']['sitename'], ENT_QUOTES)));

$categoryHandler = \XoopsModules\Newbb\Helper::getInstance()->getHandler('Category');
$categoryObject = $categoryHandler->get($forumObject->getVar("cat_id"), array("cat_title"));
$xoopsTpl->assign('category', array("id" => $forumObject->getVar("cat_id"), "title" => $categoryObject->getVar('cat_title')));

$form_title = _MD_NEWBB_REPLY.": <a href=\"viewtopic.php?topic_id={$topic_id}\">".$topicObject->getVar("topic_title");
$xoopsTpl->assign("form_title", $form_title);
*/

if ((2 == $GLOBALS['xoopsModuleConfig']['disc_show']) || (3 == $GLOBALS['xoopsModuleConfig']['disc_show'])) {
    $xoopsTpl->assign('disclaimer', $GLOBALS['xoopsModuleConfig']['disclaimer']);
}

$xoopsTpl->assign('parentforum', $forumHandler->getParents($forumObject));

$xoopsTpl->assign(
    [
        'forum_id'   => $forumObject->getVar('forum_id'),
        'forum_name' => $forumObject->getVar('forum_name'),
    ]
);

if ($postParentObject->getVar('uid')) {
    $r_name = newbbGetUnameFromId($postParentObject->getVar('uid'), $GLOBALS['xoopsModuleConfig']['show_realname']);
} else {
    $poster_name = $postParentObject->getVar('poster_name');
    $r_name      = empty($poster_name) ? htmlspecialchars((string)$GLOBALS['xoopsConfig']['anonymous'], ENT_QUOTES | ENT_HTML5) : $poster_name;
}

$r_subject = $postParentObject->getVar('subject', 'E');

$subject = $r_subject;
if (!preg_match('/^(Re|' . _MD_NEWBB_RE . '):/i', $r_subject)) {
    $subject = _MD_NEWBB_RE . ': ' . $r_subject;
}

$q_message = $postParentObject->getVar('post_text', 'e');
if ((!$GLOBALS['xoopsModuleConfig']['enable_karma'] || !$postParentObject->getVar('post_karma'))
    && (!$GLOBALS['xoopsModuleConfig']['allow_require_reply'] || !$postParentObject->getVar('require_reply'))) {
    if (1 === Request::getInt('quotedac', 0, 'GET')) {
        $message = "[quote]\n";
        $message .= sprintf(_MD_NEWBB_USERWROTE, $r_name);
        $message .= "\n" . $q_message . '[/quote]';
        $hidden  = '';
    } else {
        $hidden  = "[quote]\n";
        $hidden  .= sprintf(_MD_NEWBB_USERWROTE, $r_name);
        $hidden  .= "\n" . $q_message . '[/quote]';
        $message = '';
    }
} else {
    $hidden  = '';
    $message = '';
}

$isreply       = 1;
$istopic       = 0;
$dohtml        = 1;
$dosmiley      = 1;
$doxcode       = 1;
$dobr          = 1;
$icon          = '';
$attachsig     = (is_object($GLOBALS['xoopsUser']) && $GLOBALS['xoopsUser']->getVar('attachsig')) ? 1 : 0;
$post_karma    = 0;
$require_reply = 0;

require_once __DIR__ . '/include/form.post.php';

///** @var Newbb\KarmaHandler $karmaHandler */
//$karmaHandler = \XoopsModules\Newbb\Helper::getInstance()->getHandler('Karma');
$user_karma = $karmaHandler->getUserKarma();

$posts_context = [];
//$posts_contextObject = $postHandler->getByLimit($topic_id, 5); //mb
$posts_contextObject = $postHandler->getByLimit(5, 0, null, null, true, $topic_id, 1);
foreach ($posts_contextObject as $post_contextObject) {
    // Sorry, in order to save queries, we have to hide the non-open post_text even if you have replied or have adequate karma, even an admin.
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
