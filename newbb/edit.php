<?php
/**
 * CBB 4.0, or newbb, the forum module for XOOPS project
 *
 * @copyright    The XOOPS Project http://xoops.sf.net
 * @license        http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author        Taiwen Jiang (phppp or D.J.) <phppp@users.sourceforge.net>
 * @since        4.00
 * @version        $Id $
 * @package        module::newbb
 */

include_once __DIR__ . "/header.php";

foreach (array('forum', 'topic_id', 'post_id', 'order') as $getint) {
    ${$getint} = XoopsRequest::getInt('getint', 0, 'GET');
}

if (!$topic_id && !$post_id) {
    $redirect = empty($forum) ? "index.php" : "viewforum.php?forum={$forum}";
    redirect_header($redirect, 2, _MD_ERRORTOPIC);
}

$forum_handler =& xoops_getmodulehandler('forum', 'newbb');
$topic_handler =& xoops_getmodulehandler('topic', 'newbb');
$post_handler  =& xoops_getmodulehandler('post', 'newbb');

$post_obj  =& $post_handler->get($post_id);
$topic_obj =& $topic_handler->get($post_obj->getVar("topic_id"));
$forum_obj =& $forum_handler->get($post_obj->getVar("forum_id"));
if (!$forum_handler->getPermission($forum_obj)) {
    redirect_header("index.php", 2, _MD_NORIGHTTOACCESS);
}

if ($xoopsModuleConfig['wol_enabled']) {
    $online_handler =& xoops_getmodulehandler('online', 'newbb');
    $online_handler->init($forum_obj);
}
$isadmin = newbb_isAdmin($forum_obj);
$uid     = is_object($xoopsUser) ? $xoopsUser->getVar('uid') : 0;

$topic_id     = $post_obj->getVar("topic_id");
$topic_status = $topic_obj->getVar('topic_status');
$error_msg    = null;

if (!$topic_handler->getPermission($forum_obj, $topic_status, 'edit')
    || (!$isadmin && !$post_obj->checkIdentity())
) {
    $error_msg = _MD_NORIGHTTOEDIT;
} elseif (!$isadmin && !$post_obj->checkTimelimit('edit_timelimit')) {
    $error_msg = _MD_TIMEISUP;
}

if (!empty($error_msg)) {
    /*
     * Build the page query
     */
    $query_vars  = array("topic_id", "post_id", "forum", "status", "order", "mode", "viewmode");
    $query_array = array();
    foreach ($query_vars as $var) {
        if (XoopsRequest::getString($var, '', 'GET')) {
            $query_array[$var] = "{$var}={XoopsRequest::getString($var, '', 'GET')}";
        }
    }
    $page_query = htmlspecialchars(implode("&", array_values($query_array)));
    unset($query_array);
    redirect_header("viewtopic.php?{$page_query}", 2, $error_msg);
}

if ($xoopsModuleConfig['wol_enabled']) {
    $online_handler =& xoops_getmodulehandler('online', 'newbb');
    $online_handler->init($forum_obj);
}

$xoopsOption['template_main']                             = 'newbb_edit_post.tpl';
$xoopsConfig["module_cache"][$xoopsModule->getVar("mid")] = 0;
// irmtfan include header.php after defining $xoopsOption['template_main']
include_once $GLOBALS['xoops']->path('header.php');

/*
$xoopsTpl->assign('lang_forum_index', sprintf(_MD_FORUMINDEX, htmlspecialchars($xoopsConfig['sitename'], ENT_QUOTES)));

$category_handler =& xoops_getmodulehandler("category");
$category_obj =& $category_handler->get($forum_obj->getVar("cat_id"), array("cat_title"));
$xoopsTpl->assign('category', array("id" => $forum_obj->getVar("cat_id"), "title" => $category_obj->getVar('cat_title')));

$form_title = _EDIT.": <a href=\"viewtopic.php?post_id={$post_id}\">".$post_obj->getVar("subject");
$xoopsTpl->assign("form_title", $form_title);

$xoopsTpl->assign("parentforum", $forum_handler->getParents($forum_obj));

$xoopsTpl->assign(array(
    'forum_id' 			=> $forum_obj->getVar('forum_id'),
    'forum_name' 		=> $forum_obj->getVar('forum_name'),
    ));
*/

$dohtml        = $post_obj->getVar('dohtml');
$dosmiley      = $post_obj->getVar('dosmiley');
$doxcode       = $post_obj->getVar('doxcode');
$dobr          = $post_obj->getVar('dobr');
$icon          = $post_obj->getVar('icon');
$attachsig     = $post_obj->getVar('attachsig');
$istopic       = ($post_obj->istopic()) ? 1 : 0;
$isedit        = 1;
$subject       = $post_obj->getVar('subject', "E");
$message       = $post_obj->getVar('post_text', "E");
$poster_name   = $post_obj->getVar('poster_name', "E");
$attachments   = $post_obj->getAttachment();
$post_karma    = $post_obj->getVar('post_karma');
$require_reply = $post_obj->getVar('require_reply');

$xoopsTpl->assign("error_message", _MD_EDITEDBY . " " . $xoopsUser->uname());
include __DIR__ . '/include/form.post.php';

$karma_handler =& xoops_getmodulehandler('karma', 'newbb');
$user_karma    = $karma_handler->getUserKarma();

$posts_context     = array();
$posts_context_obj = ($istopic) ? array() : array($post_handler->get($post_obj->getVar('pid')));
foreach ($posts_context_obj as $post_context_obj) {
    if ($xoopsModuleConfig['enable_karma'] && $post_context_obj->getVar('post_karma') > 0) {
        $p_message = sprintf(_MD_KARMA_REQUIREMENT, "***", $post_context_obj->getVar('post_karma')) . "</div>";
    } elseif ($xoopsModuleConfig['allow_require_reply'] && $post_context_obj->getVar('require_reply')) {
        $p_message = _MD_REPLY_REQUIREMENT;
    } else {
        $p_message = $post_context_obj->getVar('post_text');
    }

    if ($post_context_obj->getVar('uid')) {
        $p_name = newbb_getUnameFromId($post_context_obj->getVar('uid'), $xoopsModuleConfig['show_realname']);
    } else {
        $poster_name = $post_context_obj->getVar('poster_name');
        $p_name      = (empty($poster_name)) ? htmlspecialchars($xoopsConfig['anonymous']) : $poster_name;
    }
    $p_date    = formatTimestamp($post_context_obj->getVar('post_time'));
    $p_subject = $post_context_obj->getVar('subject');

    $posts_context[] = array(
        "subject" => $p_subject,
        "meta"    => _MD_BY . " " . $p_name . " " . _MD_ON . " " . $p_date,
        "content" => $p_message,
    );
}
$xoopsTpl->assign_by_ref("posts_context", $posts_context);
// irmtfan move to footer.php
include_once __DIR__ . "/footer.php";
include $GLOBALS['xoops']->path('footer.php');
