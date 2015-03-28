<?php
// $Id: reply.php 62 2012-08-17 10:15:26Z alfred $
//  ------------------------------------------------------------------------ //
//                XOOPS - PHP Content Management System                      //
//                    Copyright (c) 2000 XOOPS.org                           //
//                       <http://www.xoops.org/>                             //
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
//  URL: http://xoopsforge.com, http://xoops.org.cn                          //
//  Project: Article Project                                                 //
//  ------------------------------------------------------------------------ //

include_once __DIR__ . "/header.php";nt -nt snt s

foreach (array('forum', 'topic_id', 'post_id', 'order', 'start') as $getint) {
    ${$getint} = XoopsRequest::getInt($getint, 0, 'GET');
}

if (!$topic_id && !$post_id) {
    $redirect = empty($forum) ? "index.php" : "viewforum.php?forum={$forum}";
    redirect_header($redirect, 2, _MD_ERRORTOPIC);
}

$forum_handler =& xoops_getmodulehandler('forum', 'newbb');
$topic_handler =& xoops_getmodulehandler('topic', 'newbb');
$post_handler  =& xoops_getmodulehandler('post', 'newbb');

if (!$pid = $post_id) {
    $pid = $topic_handler->getTopPostId($topic_id);
}
$post_parent_obj =& $post_handler->get($pid);
$topic_id        = $post_parent_obj->getVar("topic_id");
$forum           = $post_parent_obj->getVar("forum_id");
$post_obj        =& $post_handler->create();
$post_obj->setVar("pid", $pid);
$post_obj->setVar("topic_id", $topic_id);
$post_obj->setVar("forum_id", $forum);

$forum_obj =& $forum_handler->get($forum);
if (!$forum_handler->getPermission($forum_obj)) {
    redirect_header("index.php", 2, _MD_NORIGHTTOACCESS);
}

$topic_obj    =& $topic_handler->get($topic_id);
$topic_status = $topic_obj->getVar('topic_status');
if (!$topic_handler->getPermission($forum_obj, $topic_status, 'reply')) {
    /*
     * Build the page query
     */
    $query_vars  = array("topic_id", "post_id", "status", "order", "mode", "viewmode");
    $query_array = array();
    foreach ($query_vars as $var) {
        if (XoopsRequest::getString($var, '', 'GET')) $query_array[$var] = "{$var}={XoopsRequest::getString($var, '', 'GET'}";
    }
    $page_query = htmlspecialchars(implode("&", array_values($query_array)));
    unset($query_array);

    redirect_header("viewtopic.php?{$page_query}", 2, _MD_NORIGHTTOREPLY);
}

if ($xoopsModuleConfig['wol_enabled']) {
    $online_handler =& xoops_getmodulehandler('online', 'newbb');
    $online_handler->init($forum_obj);
}

$xoopsOption['template_main']                             = 'newbb_edit_post.tpl';
$xoopsConfig["module_cache"][$xoopsModule->getVar("mid")] = 0;
// irmtfan remove and move to footer.php
//$xoopsOption['xoops_module_header']= $xoops_module_header;
// irmtfan include header.php after defining $xoopsOption['template_main']
include_once $GLOBALS['xoops']->path('header.php');
//$xoopsTpl->assign('xoops_module_header', $xoops_module_header);

/*
$xoopsTpl->assign('lang_forum_index', sprintf(_MD_FORUMINDEX, htmlspecialchars($xoopsConfig['sitename'], ENT_QUOTES)));

$category_handler =& xoops_getmodulehandler("category");
$category_obj =& $category_handler->get($forum_obj->getVar("cat_id"), array("cat_title"));
$xoopsTpl->assign('category', array("id" => $forum_obj->getVar("cat_id"), "title" => $category_obj->getVar('cat_title')));

$form_title = _MD_REPLY.": <a href=\"viewtopic.php?topic_id={$topic_id}\">".$topic_obj->getVar("topic_title");
$xoopsTpl->assign("form_title", $form_title);
*/

if ($xoopsModuleConfig['disc_show'] == 2 or $xoopsModuleConfig['disc_show'] == 3) {
    $xoopsTpl->assign("disclaimer", $xoopsModuleConfig['disclaimer']);
}

$xoopsTpl->assign("parentforum", $forum_handler->getParents($forum_obj));

$xoopsTpl->assign(array(
                      'forum_id'   => $forum_obj->getVar('forum_id'),
                      'forum_name' => $forum_obj->getVar('forum_name'),
                  ));

if ($post_parent_obj->getVar('uid')) {
    $r_name = newbb_getUnameFromId($post_parent_obj->getVar('uid'), $xoopsModuleConfig['show_realname']);
} else {
    $poster_name = $post_parent_obj->getVar('poster_name');
    $r_name      = (empty($poster_name)) ? $myts->htmlSpecialChars($xoopsConfig['anonymous']) : $poster_name;
}

$r_subject = $post_parent_obj->getVar('subject', "E");

if (!preg_match("/^(Re|" . _MD_RE . "):/i", $r_subject)) {
    $subject = _MD_RE . ': ' . $r_subject;
} else {
    $subject = $r_subject;
}

$q_message = $post_parent_obj->getVar('post_text', "e");
if ((!$xoopsModuleConfig['enable_karma'] || !$post_parent_obj->getVar('post_karma'))
    && (!$xoopsModuleConfig['allow_require_reply'] || !$post_parent_obj->getVar('require_reply'))
) {
    if (1 == XoopsRequest::getInt('quotedac', 0, 'GET')) {
        $message = "[quote]\n";
        $message .= sprintf(_MD_USERWROTE, $r_name);
        $message .= "\n" . $q_message . "[/quote]";
        $hidden = "";
    } else {
        $hidden = "[quote]\n";
        $hidden .= sprintf(_MD_USERWROTE, $r_name);
        $hidden .= "\n" . $q_message . "[/quote]";
        $message = "";
    }
} else {
    $hidden  = "";
    $message = "";
}

$isreply       = 1;
$istopic       = 0;
$dohtml        = 1;
$dosmiley      = 1;
$doxcode       = 1;
$dobr          = 1;
$icon          = '';
$attachsig     = (is_object($xoopsUser) && $xoopsUser->getVar('attachsig')) ? 1 : 0;
$post_karma    = 0;
$require_reply = 0;

include __DIR__ . '/include/form.post.php';

$karma_handler =& xoops_getmodulehandler('karma', 'newbb');
$user_karma    = $karma_handler->getUserKarma();

$posts_context     = array();
$posts_context_obj = $post_handler->getByLimit($topic_id, 5);
foreach ($posts_context_obj as $post_context_obj) {
    // Sorry, in order to save queries, we have to hide the non-open post_text even if you have replied or have adequate karma, even an admin.
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
