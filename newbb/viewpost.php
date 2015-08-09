<?php
// $Id: viewpost.php 62 2012-08-17 10:15:26Z alfred $
//  ------------------------------------------------------------------------ //
//                XOOPS - PHP Content Management System                      //
//                    Copyright (c) 2000 XOOPS.org                           //
//                       <http://xoops.org/>                             //
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
//  URL: http://xoops.org                                                    //
//  Project: Article Project                                                 //
//  ------------------------------------------------------------------------ //
include_once __DIR__ . '/header.php';

$start    = XoopsRequest::getInt('start', 0, 'GET');
$forum_id = XoopsRequest::getInt('forum', 0, 'GET');
$order    = XoopsRequest::getString('order', 'DESC', 'GET');

$uid = XoopsRequest::getInt('uid', 0, 'GET');

$status = (XoopsRequest::getString('status', '', 'GET') && in_array(XoopsRequest::getString('status', '', 'GET'), array('active', 'pending', 'deleted', 'new', 'all', 'digest'), true)) ? XoopsRequest::getString('status', '', 'GET') : '';
$mode   = XoopsRequest::getInt('mode', 0, 'GET');
$mode   = (!empty($status) && in_array($status, array('active', 'pending', 'deleted'), true)) ? 2 : $mode;

$forumHandler =& xoops_getmodulehandler('forum', 'newbb');
$postHandler  =& xoops_getmodulehandler('post', 'newbb');

if (empty($forum_id)) {
    $forums        = $forumHandler->getByPermission(0, 'view');
    $accessForums = array_keys($forums);
    $isadmin       = $GLOBALS['xoopsUserIsAdmin'];
} else {
    $forum_obj         =& $forumHandler->get($forum_id);
    $forums[$forum_id] =& $forum_obj;
    $accessForums     = array($forum_id);
    $isadmin           = newbb_isAdmin($forum_obj);
}

/* Only admin has access to admin mode */
if (!$isadmin && $mode === 2) {
    $status = in_array($status, array('active', 'pending', 'deleted'), true) ? '' : $status;
    $mode   = 0;
}
if ($mode) {
    $_GET['viewmode'] = 'flat';
}
//echo $mode.' - '.$status;
$post_perpage = $GLOBALS['xoopsModuleConfig']['posts_per_page'];

$criteria_count = new CriteriaCompo(new Criteria('forum_id', '(' . implode(',', $accessForums) . ')', 'IN'));
$criteria_post  = new CriteriaCompo(new Criteria('p.forum_id', '(' . implode(',', $accessForums) . ')', 'IN'));
$criteria_post->setSort('p.post_id');
$criteria_post->setOrder($order);

if (!empty($uid)) {
    $criteria_count->add(new Criteria('uid', $uid));
    $criteria_post->add(new Criteria('p.uid', $uid));
}

$join = null;
// START irmtfan solve the status issues and specially status = new issue
switch ($status) {
    case 'pending':
        $criteria_count->add(new Criteria('approved', 0)); // irmtfan add new criteria
        $criteria_post->add(new Criteria('p.approved', 0)); // irmtfan add new criteria
        break;
    case 'deleted':
        $criteria_count->add(new Criteria('approved', -1)); // irmtfan add new criteria
        $criteria_post->add(new Criteria('p.approved', -1)); // irmtfan add new criteria
        break;
    case 'new':
        //$criteria_status_count = new CriteriaCompo(new Criteria("post_time", (int)($last_visit), ">"));// irmtfan commented and removed
        //$criteria_status_post = new CriteriaCompo(new Criteria("p.post_time", (int)($last_visit), ">"));// irmtfan commented and removed
        $criteria_count->add(new Criteria('approved', 1)); // irmtfan uncomment
        $criteria_post->add(new Criteria('p.approved', 1)); // irmtfan uncomment
        // following is for 'unread' -- not finished -- irmtfan Now it is finished!
        if (empty($GLOBALS['xoopsModuleConfig']['read_mode'])) {
            //$criteria_status_count->add(new Criteria('approved', 1));// irmtfan commented and removed
            //$criteria_status_post->add(new Criteria('p.approved', 1));// irmtfan commented and removed
        } elseif ($GLOBALS['xoopsModuleConfig']['read_mode'] === 2) {
            // START irmtfan use read_uid to find the unread posts when the user is logged in
            $read_uid = is_object($GLOBALS['xoopsUser']) ? $GLOBALS['xoopsUser']->getVar('uid') : 0;
            if (!empty($read_uid)) {
                $join                 = ' LEFT JOIN ' . $GLOBALS['xoopsDB']->prefix('bb_reads_topic') . ' AS r ON r.read_item = p.topic_id AND r.uid = ' . $read_uid . ' '; // irmtfan corrected add AS
                $criteria_status_post = new CriteriaCompo();// irmtfan new criteria
                $criteria_status_post->add(new Criteria('p.post_id', 'r.`post_id`', '>')); // irmtfan corrected - should use $value='r.``' to render in XOOPS/class/criteria.php
                $criteria_status_post->add(new Criteria('r.read_id', null, 'IS NULL'), 'OR');// irmtfan corrected - should use "IS NULL" to render in XOOPS/class/criteria.php
                $criteria_post->add($criteria_status_post); // irmtfan add the status criteria to post criteria - move here
                $criteria_count =& $criteria_post;// irmtfan criteria count is equal to criteria post - move here
            } else {
            }
            // END irmtfan use read_uid to find the unread posts when the user is logged in
            //$criteria_status_post->add(new Criteria("p.approved", 1)); // irmtfan commented and removed
            //$criteria_status_count =& $criteria_status_post;
        } elseif ($GLOBALS['xoopsModuleConfig']['read_mode'] === 1) {
            $criteria_count->add(new Criteria('post_time', (int)($last_visit), '>')); // irmtfan add new criteria
            $criteria_post->add(new Criteria('p.post_time', (int)($last_visit), '>')); // irmtfan add new criteria
            // START irmtfan fix read_mode = 1 bugs - for all users (member and anon)
            $topics         = array();
            $topic_lastread = newbb_getcookie('LT', true);
            if (count($topic_lastread) > 0) {
                foreach ($topic_lastread as $id => $time) {
                    if ($time > (int)($last_visit)) {
                        $topics[] = $id;
                    }
                }
            }
            if (count($topics) > 0) {
                $criteria_count->add(new Criteria('topic_id', '(' . implode(',', $topics) . ')', 'NOT IN'));
                $criteria_post->add(new Criteria('p.topic_id', '(' . implode(',', $topics) . ')', 'NOT IN'));
            }
            // END irmtfan fix read_mode = 1 bugs - for all users (member and anon)
            //$criteria_status_count->add(new Criteria("approved", 1));// irmtfan commented and removed
            //$criteria_status_post->add(new Criteria("p.approved", 1));// irmtfan commented and removed
        }
        break;
    default:
        $criteria_count->add(new Criteria('approved', 1)); // irmtfan add new criteria
        $criteria_post->add(new Criteria('p.approved', 1)); // irmtfan add new criteria
        break;
}
//$criteria_count->add($criteria_status_count); // irmtfan commented and removed
//$criteria_post->add($criteria_status_post); // irmtfan commented and removed
// END irmtfan solve the status issues and specially status = new issue
$karmaHandler =& xoops_getmodulehandler('karma', 'newbb');
$user_karma    = $karmaHandler->getUserKarma();

$valid_modes     = array('flat', 'compact');
$viewmode_cookie = newbb_getcookie('V');

if ('compact' === XoopsRequest::getString('viewmode', '', 'GET')) {
    newbb_setcookie('V', 'compact', $forumCookie['expire']);
}

$viewmode = XoopsRequest::getString('viewmode', (!empty($viewmode_cookie) ? $viewmode_cookie : (@$valid_modes[$GLOBALS['xoopsModuleConfig']['view_mode'] - 1])), 'GET');
$viewmode = in_array($viewmode, $valid_modes) ? $viewmode : $valid_modes[0];

$postCount = $postHandler->getPostCount($criteria_count, $join);// irmtfan add join for read_mode = 2
$posts     = $postHandler->getPostsByLimit($criteria_post, $post_perpage, $start, $join);// irmtfan add join for read_mode = 2

$poster_array = array();
if (count($posts) > 0) {
    foreach (array_keys($posts) as $id) {
        $poster_array[$posts[$id]->getVar('uid')] = 1;
    }
}

$xoops_pagetitle                = $xoopsModule->getVar('name') . ' - ' . _MD_VIEWALLPOSTS;
$xoopsOption['xoops_pagetitle'] = $xoops_pagetitle;
// irmtfan remove and move to footer.php
//$xoopsOption['xoops_module_header']= $xoops_module_header;
$xoopsOption['template_main'] = 'newbb_viewpost.tpl';
// irmtfan include header.php after defining $xoopsOption['template_main']
include_once $GLOBALS['xoops']->path('header.php');
mod_loadFunctions('time', 'newbb');
mod_loadFunctions('render', 'newbb');
// To enable image auto-resize by js
// irmtfan - new method
global $xoTheme;
$xoTheme->addScript('/Frameworks/textsanitizer/xoops.js');

if (!empty($forum_id)) {
    if (!$forumHandler->getPermission($forum_obj, 'view')) {
        redirect_header(XOOPS_URL . '/index.php', 2, _MD_NORIGHTTOACCESS);
    }
    if ($forum_obj->getVar('parent_forum')) {
        $parent_forum_obj =& $forumHandler->get($forum_obj->getVar('parent_forum'), array('forum_name'));
        $parentforum      = array('id' => $forum_obj->getVar('parent_forum'), 'name' => $parent_forum_obj->getVar('forum_name'));
        unset($parent_forum_obj);
        $xoopsTpl->assign_by_ref('parentforum', $parentforum);
    }
    $xoopsTpl->assign('forum_name', $forum_obj->getVar('forum_name'));
    $xoopsTpl->assign('forum_moderators', $forum_obj->dispForumModerators());

    $xoops_pagetitle = $forum_obj->getVar('forum_name') . ' - ' . _MD_VIEWALLPOSTS . ' [' . $xoopsModule->getVar('name') . ']';
    $xoopsTpl->assign('forum_id', $forum_obj->getVar('forum_id'));
    // irmtfan new method
    if (!empty($GLOBALS['xoopsModuleConfig']['rss_enable'])) {
        $xoopsTpl->assign('xoops_module_header', '
            <link rel="alternate" type="application/xml+rss" title="' . $xoopsModule->getVar('name') . '-' . $forum_obj->getVar('forum_name') . '" href="' . XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname') . '/rss.php?f=' . $forum_id . '" />
            ' . @$xoopsTpl->get_template_vars('xoops_module_header'));
    }
} elseif (!empty($GLOBALS['xoopsModuleConfig']['rss_enable'])) {
    $xoopsTpl->assign('xoops_module_header', '
        <link rel="alternate" type="application/xml+rss" title="' . $xoopsModule->getVar('name') . '" href="' . XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname') . '/rss.php" />
    ' . @$xoopsTpl->get_template_vars('xoops_module_header'));
}
// irmtfan remove and move to footer.php
//$xoopsTpl->assign('xoops_module_header', $xoops_module_header);
$xoopsTpl->assign('xoops_pagetitle', $xoops_pagetitle);
// irmtfan - remove icon_path and use newbbDisplayImage
$xoopsTpl->assign('anonym_avatar', newbbDisplayImage('anonym'));
$userid_array = array();
if (count($poster_array) > 0) {
    $memberHandler =& xoops_gethandler('member');
    $userid_array   = array_keys($poster_array);
    $user_criteria  = '(' . implode(',', $userid_array) . ')';
    $users          = $memberHandler->getUsers(new Criteria('uid', $user_criteria, 'IN'), true);
} else {
    $user_criteria = '';
    $users         = null;
}

$online = array();

if ($GLOBALS['xoopsModuleConfig']['wol_enabled']) {
    if (!empty($user_criteria)) {
        $onlineHandler =& xoops_getmodulehandler('online', 'newbb');
        $onlineHandler->init($forum_id);
    }
}

$viewtopic_users = array();

if (count($userid_array) > 0) {
    require $GLOBALS['xoops']->path('modules/' . $xoopsModule->getVar('dirname', 'n') . '/class/user.php');
    $userHandler         = new NewbbUserHandler($GLOBALS['xoopsModuleConfig']['groupbar_enabled'], $GLOBALS['xoopsModuleConfig']['wol_enabled']);
    $userHandler->users  = $users;
    $userHandler->online = $online;
    $viewtopic_users      = $userHandler->getUsers();
}

$pn            = 0;
$topicHandler = &xoops_getmodulehandler('topic', 'newbb');
static $suspension = array();
foreach (array_keys($posts) as $id) {
    ++$pn;

    $post       =& $posts[$id];
    $post_title = $post->getVar('subject');

    if ($posticon = $post->getVar('icon')) {
        $post_image = '<a name="' . $post->getVar('post_id') . '"><img src="' . XOOPS_URL . '/images/subject/' . htmlspecialchars($posticon) . '" alt="" /></a>';
    } else {
        $post_image = '<a name="' . $post->getVar('post_id') . '"><img src="' . XOOPS_URL . '/images/icons/no_posticon.gif" alt="" /></a>';
    }
    if ($post->getVar('uid') > 0 && isset($viewtopic_users[$post->getVar('uid')])) {
        $poster = $viewtopic_users[$post->getVar('uid')];
    } else {
        $poster = array(
        'uid'  => 0,
        'name' => $post->getVar('poster_name') ? : $myts->htmlSpecialChars($GLOBALS['xoopsConfig']['anonymous']),
        'link' => $post->getVar('poster_name') ? : $myts->htmlSpecialChars($GLOBALS['xoopsConfig']['anonymous'])
    );
    }
    if ($isadmin || $post->checkIdentity()) {
        $post_text       = $post->getVar('post_text');
        $post_attachment = $post->displayAttachment();
    } elseif ($GLOBALS['xoopsModuleConfig']['enable_karma'] && $post->getVar('post_karma') > $user_karma) {
        $post_text       = "<div class='karma'>" . sprintf(_MD_KARMA_REQUIREMENT, $user_karma, $post->getVar('post_karma')) . '</div>';
        $post_attachment = '';
    } elseif (
        $GLOBALS['xoopsModuleConfig']['allow_require_reply']
        && $post->getVar('require_reply')
    ) {
        $post_text       = "<div class='karma'>" . _MD_REPLY_REQUIREMENT . "</div>";
        $post_attachment = '';
    } else {
        $post_text       = $post->getVar('post_text');
        $post_attachment = $post->displayAttachment();
    }

    $thread_buttons = array();

    if ($GLOBALS["xoopsModuleConfig"]['enable_permcheck']) {
        if (!isset($suspension[$post->getVar('forum_id')])) {
            $moderateHandler                      =& xoops_getmodulehandler('moderate', 'newbb');
            $suspension[$post->getVar('forum_id')] = $moderateHandler->verifyUser(-1, '', $post->getVar('forum_id'));
        }

        if ($isadmin || (!$suspension[$post->getVar('forum_id')] && $post->checkIdentity() && $post->checkTimelimit('delete_timelimit'))
        ) {
            $thread_buttons['delete']['image'] = newbbDisplayImage('p_delete', _DELETE);
            $thread_buttons['delete']['link']  = XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname') . '/delete.php?forum=' . $post->getVar('forum_id') . '&amp;topic_id=' . $post->getVar('topic_id');
            $thread_buttons['delete']['name']  = _DELETE;
        }
        if ($isadmin || !$suspension[$post->getVar('forum_id')] && $post->checkIdentity() && $post->checkTimelimit('edit_timelimit')) {
            $thread_buttons['edit']['image'] = newbbDisplayImage('p_edit', _EDIT);
            $thread_buttons['edit']['link']  = XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname') . '/edit.php?forum=' . $post->getVar('forum_id') . '&amp;topic_id=' . $post->getVar('topic_id');
            $thread_buttons['edit']['name']  = _EDIT;
        }
        if (!$suspension[$post->getVar('forum_id')] && is_object($GLOBALS['xoopsUser'])) {
            $thread_buttons['reply']['image'] = newbbDisplayImage('p_reply', _MD_REPLY);
            $thread_buttons['reply']['link']  = XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname') . '/reply.php?forum=' . $post->getVar('forum_id') . '&amp;topic_id=' . $post->getVar('topic_id');
            $thread_buttons['reply']['name']  = _MD_REPLY;

            $thread_buttons['quote']['image'] = newbbDisplayImage('p_quote', _MD_QUOTE);
            $thread_buttons['quote']['link']  = XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname') . '/reply.php?forum=' . $post->getVar('forum_id') . '&amp;topic_id=' . $post->getVar('topic_id') . '&amp;quotedac=1';
            $thread_buttons['quote']['name']  = _MD_QUOTE;
        }
    } else {
        $thread_buttons['delete']['image'] = newbbDisplayImage('p_delete', _DELETE);
        $thread_buttons['delete']['link']  = XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname') . '/delete.php?forum=' . $post->getVar('forum_id') . '&amp;topic_id=' . $post->getVar('topic_id');
        $thread_buttons['delete']['name']  = _DELETE;
        $thread_buttons['edit']['image']   = newbbDisplayImage('p_edit', _EDIT);
        $thread_buttons['edit']['link']    = XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname') . '/edit.php?forum=' . $post->getVar('forum_id') . '&amp;topic_id=' . $post->getVar('topic_id');
        $thread_buttons['edit']['name']    = _EDIT;
        $thread_buttons['reply']['image']  = newbbDisplayImage('p_reply', _MD_REPLY);
        $thread_buttons['reply']['link']   = XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname') . '/reply.php?forum=' . $post->getVar('forum_id') . '&amp;topic_id=' . $post->getVar('topic_id');
        $thread_buttons['reply']['name']   = _MD_REPLY;
    }

    if (!$isadmin && $GLOBALS['xoopsModuleConfig']['reportmod_enabled']) {
        $thread_buttons['report']['image'] = newbbDisplayImage('p_report', _MD_REPORT);
        $thread_buttons['report']['link']  = XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname') . '/report.php?forum=' . $post->getVar('forum_id') . '&amp;topic_id=' . $post->getVar('topic_id');
        $thread_buttons['report']['name']  = _MD_REPORT;
    }
    $thread_action = array();

    $xoopsTpl->append('posts',
                      array(
                          'post_id'         => $post->getVar('post_id'),
                          'topic_id'        => $post->getVar('topic_id'),
                          'forum_id'        => $post->getVar('forum_id'),
                          'post_date'       => newbb_formatTimestamp($post->getVar('post_time')),
                          'post_image'      => $post_image,
                          'post_title'      => $post_title,
                          'post_text'       => $post_text,
                          'post_attachment' => $post_attachment,
                          'post_edit'       => $post->displayPostEdit(),
                          'post_no'         => $start + $pn,
                          'post_signature'  => ($post->getVar('attachsig')) ? @$poster['signature'] : '',
                          'poster_ip'       => ($isadmin && $GLOBALS['xoopsModuleConfig']['show_ip']) ? long2ip($post->getVar('poster_ip')) : '',
                          'thread_action'   => $thread_action,
                          'thread_buttons'  => $thread_buttons,
                          'poster'          => $poster
                      )
    );

    unset($thread_buttons, $poster);
}
unset($viewtopic_users, $forums);

if (!empty($GLOBALS['xoopsModuleConfig']['show_jump'])) {
    mod_loadFunctions('forum', 'newbb');
    $xoopsTpl->assign('forum_jumpbox', newbb_make_jumpbox($forum_id));
}

if ($postCount > $post_perpage) {
    include $GLOBALS['xoops']->path('class/pagenav.php');
    $nav = new XoopsPageNav($postCount, $post_perpage, $start, 'start', 'forum=' . $forum_id . '&amp;viewmode=' . $viewmode . '&amp;status=' . $status . '&amp;uid=' . $uid . '&amp;order=' . $order . '&amp;mode=' . $mode);
    //if (isset($GLOBALS['xoopsModuleConfig']['do_rewrite'])) $nav->url = formatURL($_SERVER['SERVER_NAME']) . $nav->url;
    if ($GLOBALS['xoopsModuleConfig']['pagenav_display'] === 'select') {
        $navi = $nav->renderSelect();
    } elseif ($GLOBALS['xoopsModuleConfig']['pagenav_display'] === 'image') {
        $navi = $nav->renderImageNav(4);
    } else {
        $navi = $nav->renderNav(4);
    }

    $xoopsTpl->assign('pagenav', $navi);
} else {
    $xoopsTpl->assign('pagenav', '');
}

$xoopsTpl->assign('lang_forum_index', sprintf(_MD_FORUMINDEX, htmlspecialchars($GLOBALS['xoopsConfig']['sitename'], ENT_QUOTES)));

switch ($status) {
    case 'active':
        $lang_title = _MD_VIEWALLPOSTS . ' [' . _MD_TYPE_ADMIN . ']';
        break;
    case 'pending':
        $lang_title = _MD_VIEWALLPOSTS . ' [' . _MD_TYPE_PENDING . ']';
        break;
    case 'deleted':
        $lang_title = _MD_VIEWALLPOSTS . ' [' . _MD_TYPE_DELETED . ']';
        break;
    case 'new':
        $lang_title = _MD_NEWPOSTS;
        break;
    default:
        $lang_title = _MD_VIEWALLPOSTS;
        break;
}
if ($uid > 0) {
    $lang_title .= ' (' . XoopsUser::getUnameFromId($uid) . ')';
}
$xoopsTpl->assign('lang_title', $lang_title);
// irmtfan up to p_up
$xoopsTpl->assign('p_up', newbbDisplayImage('up', _MD_TOP));
$xoopsTpl->assign('groupbar_enable', $GLOBALS['xoopsModuleConfig']['groupbar_enabled']);
$xoopsTpl->assign('anonymous_prefix', $GLOBALS['xoopsModuleConfig']['anonymous_prefix']);
$xoopsTpl->assign('down', newbbDisplayImage('down', _MD_BOTTOM));

$all_link       = XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname') . '/viewpost.php?forum=' . $forum_id . "&amp;start=$start";
$post_link      = XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname') . '/viewpost.php?forum=' . $forum_id;
$newpost_link   = XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname') . '/viewpost.php?forum=' . $forum_id . '&amp;status=new';
$digest_link    = XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname') . '/viewpost.php?forum=' . $forum_id . "&amp;start=$start&amp;status=digest";
$unreplied_link = XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname') . '/viewpost.php?forum=' . $forum_id . "&amp;start=$start&amp;status=unreplied";
$unread_link    = XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname') . '/viewpost.php?forum=' . $forum_id . "&amp;start=$start&amp;status=unread";

$xoopsTpl->assign('all_link', $all_link);
$xoopsTpl->assign('post_link', $post_link);
$xoopsTpl->assign('newpost_link', $newpost_link);
$xoopsTpl->assign('digest_link', $digest_link);
$xoopsTpl->assign('unreplied_link', $unreplied_link);
$xoopsTpl->assign('unread_link', $unread_link);

$viewmode_options = array();
if ($order === 'DESC') {
    $viewmode_options[] = array('link' => XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname') . '/viewpost.php?viewmode=flat&amp;order=ASC&amp;forum=' . $forum_id, 'title' => _OLDESTFIRST);
} else {
    $viewmode_options[] = array('link' => XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname') . '/viewpost.php?viewmode=flat&amp;order=DESC&amp;forum=' . $forum_id, 'title' => _NEWESTFIRST);
}

//$xoopsTpl->assign('viewmode_compact', ($viewmode=="compact")?1:0);
$xoopsTpl->assign_by_ref('viewmode_options', $viewmode_options);
$xoopsTpl->assign('menumode', $menumode);
$xoopsTpl->assign('menumode_other', $menumode_other);

$xoopsTpl->assign('viewer_level', ($isadmin) ? 2 : is_object($GLOBALS['xoopsUser']));
$xoopsTpl->assign('uid', $uid);
$xoopsTpl->assign('mode', $mode);
$xoopsTpl->assign('status', $status);
// irmtfan move to footer.php
include_once __DIR__ . '/footer.php';
include $GLOBALS['xoops']->path('footer.php');
