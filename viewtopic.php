<?php
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
// irmtfan enhance include

use Xmf\Request;
use XoopsModules\Newbb;
use XoopsModules\Xoopspoll;

require_once __DIR__ . '/header.php';
$xoopsLogger->startTime('newBB_viewtopic');
require_once __DIR__ . '/include/functions.read.php';
require_once __DIR__ . '/include/functions.render.php';
xoops_loadLanguage('user');

/*Build the page query*/
$query_vars  = ['post_id', 'topic_id', 'status', 'order', 'start', 'move', 'mode'];
$query_array = [];
foreach ($query_vars as $var) {
    if (Request::getString($var, '', 'GET')) {
        $query_array[$var] = "{$var}=" . Request::getString($var, '', 'GET');
    }
}
$page_query = htmlspecialchars(implode('&', array_values($query_array)));
unset($query_array);

$forum_id = Request::getInt('forum', 0, 'GET');
$read     = (Request::getString('read', '', 'GET')
             && 'new' === Request::getString('read', '', 'GET')) ? Request::getString('read', '', 'GET') : '';
$topic_id = Request::getInt('topic_id', 0, 'GET'); // isset($_GET['topic_id']) ? (int)($_GET['topic_id']) : 0;
$post_id  = Request::getInt('post_id', 0, 'GET'); // !empty($_GET['post_id']) ? (int)($_GET['post_id']) : 0;
$move     = strtolower(Request::getString('move', '', 'GET')); // isset($_GET['move']) ? strtolower($_GET['move']) : '';
$start    = Request::getInt('start', 0, 'GET'); // !empty($_GET['start']) ? (int)($_GET['start']) : 0;
$status   = (Request::getString('status', '', 'GET')
             && in_array(Request::getString('status', '', 'GET'), ['active', 'pending', 'deleted'], true)) ? Request::getString('status', '', 'GET') : '';
$mode     = Request::getInt('mode', (!empty($status) ? 2 : 0), 'GET'); // !empty($_GET['mode']) ? (int)($_GET['mode']) : (!empty($status) ? 2 : 0);
$order    = (Request::getString('order', '', 'GET')
             && in_array(Request::getString('order', '', 'GET'), ['ASC', 'DESC'], true)) ? Request::getString('order', '', 'GET') : '';

if ('' === $order) {
    if (is_object($GLOBALS['xoopsUser']) && $GLOBALS['xoopsUser']->isActive()) {
        $order = (1 == $GLOBALS['xoopsUser']->getVar('uorder')) ? 'DESC' : 'ASC';
    } else {
        $order = (1 == $GLOBALS['xoopsConfig']['com_order']) ? 'DESC' : 'ASC';
    }
}

if (!$topic_id && !$post_id) {
    $redirect = empty($forum_id) ? XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname') . '/index.php' : XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname') . "/viewforum.php?forum={$forum_id}";
    redirect_header($redirect, 2, _MD_NEWBB_ERRORTOPIC);
}

///** @var Newbb\TopicHandler $topicHandler */
//$topicHandler = Newbb\Helper::getInstance()->getHandler('Topic');
if (!empty($post_id)) {
    $topicObject = $topicHandler->getByPost($post_id);
    $topic_id    = $topicObject->getVar('topic_id');
} elseif (!empty($move)) {
    $topicObject = $topicHandler->getByMove($topic_id, ('prev' === $move) ? -1 : 1, $forum_id);
    $topic_id    = $topicObject->getVar('topic_id');
} else {
    $topicObject = $topicHandler->get($topic_id);
}

if (!is_object($topicObject) || !$topic_id = $topicObject->getVar('topic_id')) {
    $redirect = empty($forum_id) ? XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname') . '/index.php' : XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname') . "/viewforum.php?forum={$forum_id}";
    redirect_header($redirect, 2, _MD_NEWBB_ERRORTOPIC);
}
$forum_id = $topicObject->getVar('forum_id');
///** @var Newbb\ForumHandler $forumHandler */
//$forumHandler = Newbb\Helper::getInstance()->getHandler('Forum');
$forumObject = $forumHandler->get($forum_id);

$isAdmin = newbbIsAdmin($forumObject);

if ((!$isAdmin && $topicObject->getVar('approved') < 0) || (!$forumHandler->getPermission($forumObject))
    || (!$topicHandler->getPermission($forumObject, $topicObject->getVar('topic_status'), 'view'))) {
    redirect_header(XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname') . '/viewforum.php?forum=' . $forum_id, 2, _MD_NEWBB_NORIGHTTOVIEW);
}

// START irmtfan - find if topic is read or unread - for all users (member and anon)
$topic_is_unread = true;
/* if $GLOBALS['xoopsModuleConfig']["read_mode"] === 0 ||
 * never read && $GLOBALS['xoopsModuleConfig']["read_mode"] === 1 ||
 * never read && $GLOBALS['xoopsModuleConfig']["read_mode"] === 2 ||
 * => $topic_last_post_time_or_id_read = NULL
 * if !$GLOBALS['xoopsUser'] && $GLOBALS['xoopsModuleConfig']["read_mode"] === 2
 * => $topic_last_post_time_or_id_read = false
 * if !$GLOBALS['xoopsUser'] && $GLOBALS['xoopsModuleConfig']["read_mode"] === 1
 * => $topic_last_post_time_or_id_read = lastview(newbb_IP{ip}LT)
*/
$topic_last_post_time_or_id_read = newbbGetRead('topic', $topic_id);
if (!empty($topic_last_post_time_or_id_read)) {
    if (1 == $GLOBALS['xoopsModuleConfig']['read_mode']) {
        //        $postHandler     = Newbb\Helper::getInstance()->getHandler('Post');
        $postObject      = $postHandler->get($topicObject->getVar('topic_last_post_id'));
        $topic_is_unread = ($topic_last_post_time_or_id_read < $postObject->getVar('post_time'));
    }
    if (2 == $GLOBALS['xoopsModuleConfig']['read_mode']) {
        $topic_is_unread = ($topic_last_post_time_or_id_read < $topicObject->getVar('topic_last_post_id'));
        // hack jump to last post read if post_id is empty - is there any better way?
        if (empty($post_id) && $topic_is_unread
            && !empty($GLOBALS['xoopsModuleConfig']['jump_to_topic_last_post_read_enabled'])) {
            header('Location: ' . Request::getString('REQUEST_URI', '', 'SERVER') . '&post_id=' . $topic_last_post_time_or_id_read);
        }
    }
}
// END irmtfan - find if topic is read or unread - for all users (member and anon)

/* Only admin has access to admin mode */
if (!$isAdmin) {
    $status = '';
    $mode   = 0;
}

if (!empty($GLOBALS['xoopsModuleConfig']['enable_karma'])) {
    //    /** @var Newbb\KarmaHandler $karmaHandler */
    //    $karmaHandler = Newbb\Helper::getInstance()->getHandler('Karma');
    $user_karma = $karmaHandler->getUserKarma();
}

//$viewmode = "flat";

$total_posts = $topicHandler->getPostCount($topicObject, $status);
$postsArray  = [];
$postsArray  = $topicHandler->getAllPosts($topicObject, $order, $GLOBALS['xoopsModuleConfig']['posts_per_page'], $start, $post_id, $status);

//irmtfan - increment topic_views only if the topic is unread
if ($topic_is_unread) {
    $topicObject->incrementCounter();
}
newbbSetRead('topic', $topic_id, $topicObject->getVar('topic_last_post_id'));

$GLOBALS['xoopsOption']['template_main'] = 'newbb_viewtopic.tpl';
// irmtfan remove and move to footer.php
//$xoopsOption['xoops_module_header']= $xoops_module_header;
// irmtfan include header.php after defining $xoopsOption['template_main']
require_once $GLOBALS['xoops']->path('header.php');
//$xoopsTpl->assign('xoops_module_header', $xoops_module_header);
// irmtfan new method
if (!empty($GLOBALS['xoopsModuleConfig']['rss_enable'])) {
    $xoopsTpl->assign('xoops_module_header', '
    <link rel="alternate" type="application/rss+xml" title="' . $xoopsModule->getVar('name') . '-' . $forumObject->getVar('forum_name') . '" href="' . XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname') . '/rss.php?f=' . $forumObject->getVar('forum_id') . '" />
    ' . @$xoopsTpl->get_template_vars('xoops_module_header'));
}

if ($GLOBALS['xoopsModuleConfig']['wol_enabled']) {
    /** @var Newbb\OnlineHandler $onlineHandler */
    $onlineHandler = Newbb\Helper::getInstance()->getHandler('Online');
    $onlineHandler->init($forumObject, $topicObject);
    $xoopsTpl->assign('online', $onlineHandler->showOnline());
}
$xoopsTpl->assign('parentforum', $forumHandler->getParents($forumObject));
// irmtfan - remove icon_path and use newbbDisplayImage
$xoopsTpl->assign('anonym_avatar', newbbDisplayImage('anonym'));

// START irmtfan improve infobox
$infobox         = [];
$infobox['show'] = (int)$GLOBALS['xoopsModuleConfig']['show_infobox']; //4.05
// irmtfan removed then define after array
//$xoopsTpl->assign('infobox', $infobox); //4.05
$iconHandler = newbbGetIconHandler(); // can be use in the follwing codes in this file

if ($infobox['show'] > 0) {
    // irmtfan - remove icon_path and use newbbDisplayImage
    $infobox['icon'] = [
        'expand'   => $iconHandler->getImageSource('less'),
        'collapse' => $iconHandler->getImageSource('more')
    ];
    if (1 == $infobox['show']) {
        $infobox['style'] = 'none';        //irmtfan move semicolon
        $infobox['alt']   = _MD_NEWBB_SEEUSERDATA;
        $infobox['src']   = 'more';
    } else {
        $infobox['style'] = 'block';        //irmtfan move semicolon
        $infobox['alt']   = _MD_NEWBB_HIDEUSERDATA;
        $infobox['src']   = 'less';
    }
    $infobox['displayImage'] = newbbDisplayImage($infobox['src'], $infobox['alt']);
}
$xoopsTpl->assign('infobox', $infobox);
// END irmtfan improve infobox

$xoopsTpl->assign([
                      'topic_title'    => '<a href="' . XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname', 'n') . '/viewtopic.php?topic_id=' . $topic_id . '">' . $topicObject->getFullTitle() . '</a>',
                      'forum_name'     => $forumObject->getVar('forum_name'),
                      'lang_nexttopic' => _MD_NEWBB_NEXTTOPIC,
                      'lang_prevtopic' => _MD_NEWBB_PREVTOPIC,
                      'topic_status'   => $topicObject->getVar('topic_status')
                  ]);

//$categoryHandler = Newbb\Helper::getInstance()->getHandler('Category');
$categoryObject = $categoryHandler->get($forumObject->getVar('cat_id'), ['cat_title']);
$xoopsTpl->assign('category', ['id' => $forumObject->getVar('cat_id'), 'title' => $categoryObject->getVar('cat_title')]);

$xoopsTpl->assign('post_id', $post_id);
$xoopsTpl->assign('topic_id', $topic_id);
$xoopsTpl->assign('forum_id', $forum_id);

$order_current = ('DESC' === $order) ? 'DESC' : 'ASC';
$xoopsTpl->assign('order_current', $order_current);

$t_new   = newbbDisplayImage('t_new', _MD_NEWBB_POSTNEW);
$t_reply = newbbDisplayImage('t_reply', _MD_NEWBB_REPLY);
// irmtfan show topic status if show reg is 0 and revise forum_post_or_register
if ($topicHandler->getPermission($forumObject, $topicObject->getVar('topic_status'), 'post')) {
    $xoopsTpl->assign('forum_post', '<a href="' . XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname', 'n') . '/newtopic.php?forum=' . $forum_id . '"> ' . $t_new . '</a>');
} else {
    if ($topicObject->getVar('topic_status')) {
        $xoopsTpl->assign('topic_lock', _MD_NEWBB_TOPICLOCKED);
    }
    if (!is_object($GLOBALS['xoopsUser']) && !empty($GLOBALS['xoopsModuleConfig']['show_reg'])) {
        $xoopsTpl->assign('forum_register', '<a href="' . XOOPS_URL . '/user.php?xoops_redirect=' . htmlspecialchars($xoopsRequestUri) . '">' . _MD_NEWBB_REGTOPOST . '</a>');
    }
}
// irmtfan for backward compatibility assign forum_post_or_register smarty again.
$xoopsTpl->assign('forum_post_or_register', @$xoopsTpl->get_template_vars('forum_post') . @$xoopsTpl->get_template_vars('forum_register') . @$xoopsTpl->get_template_vars('topic_lock'));

if ($topicHandler->getPermission($forumObject, $topicObject->getVar('topic_status'), 'reply')) {
    $xoopsTpl->assign('forum_reply', '<a href="' . XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname', 'n') . '/reply.php?topic_id=' . $topic_id . '"> ' . $t_reply . '</a>');
}

$poster_array  = [];
$require_reply = false;
/** @var Post $eachpost */
foreach ($postsArray as $eachpost) {
    if ($eachpost->getVar('uid') > 0) {
        $poster_array[$eachpost->getVar('uid')] = 1;
    }
    if ($eachpost->getVar('require_reply') > 0) {
        $require_reply = true;
    }
}

$userid_array = [];
$online       = [];
if (is_array($poster_array) && count($poster_array) > 0) {
    /** @var \XoopsMemberHandler $memberHandler */
    $memberHandler = xoops_getHandler('member');
    $userid_array  = array_keys($poster_array);
    $user_criteria = '(' . implode(',', $userid_array) . ')';
    $users         = $memberHandler->getUsers(new \Criteria('uid', $user_criteria, 'IN'), true);
} else {
    $users = [];
}

$viewtopic_users = [];
if (is_array($userid_array) && count($userid_array) > 0) {
    require $GLOBALS['xoops']->path('modules/' . $xoopsModule->getVar('dirname', 'n') . '/class/user.php');
    $userHandler         = new Newbb\UserHandler($GLOBALS['xoopsModuleConfig']['groupbar_enabled'], $GLOBALS['xoopsModuleConfig']['wol_enabled']);
    $userHandler->users  = $users;
    $userHandler->online = $online;
    $viewtopic_users     = $userHandler->getUsers();
}
unset($users);

if ($GLOBALS['xoopsModuleConfig']['allow_require_reply'] && $require_reply) {
    if (!empty($GLOBALS['xoopsModuleConfig']['cache_enabled'])) {
        $viewtopic_posters = newbbGetSession('t' . $topic_id, true);
        if (!is_array($viewtopic_posters) || 0 === count($viewtopic_posters)) {
            $viewtopic_posters = $topicHandler->getAllPosters($topicObject);
            newbbSetSession('t' . $topic_id, $viewtopic_posters);
        }
    } else {
        $viewtopic_posters = $topicHandler->getAllPosters($topicObject);
    }
} else {
    $viewtopic_posters = [];
}

if ($GLOBALS['xoopsModuleConfig']['show_advertising']) {
    $post_werbung = [
        'post_id'         => 0,
        'post_parent_id'  => 0,
        'post_date'       => 0,
        'post_image'      => '',
        'post_title'      => '',
        'post_text'       => '<div style="text-align: center;vertical-align: middle;"><br>' . xoops_getbanner() . '</div>',
        'post_attachment' => '',
        'post_edit'       => 0,
        'post_no'         => 0,
        'post_signature'  => _MD_NEWBB_ADVERTISING_BLOCK,
        'poster_ip'       => '',
        'thread_action'   => '',
        'thread_buttons'  => '',
        'mod_buttons'     => '',
        'poster'          => [
            'uid'        => -1,
            'link'       => _MD_NEWBB_ADVERTISING_USER,
            'avatar'     => 'avatars/blank.gif',
            'regdate'    => 0,
            'last_login' => 0,
            'rank'       => ['title' => '']
        ],
        // irmtfan add last_login
        'post_permalink'  => ''
    ];
}

$i = 0;
/** @var Post $eachpost */
foreach ($postsArray as $eachpost) {
    if ($GLOBALS['xoopsModuleConfig']['show_advertising']) {
        if (2 === $i) {
            $xoopsTpl->append('topic_posts', $post_werbung);
        }
        ++$i;
    }
    $xoopsTpl->append('topic_posts', $eachpost->showPost($isAdmin));
}

if ($total_posts > $GLOBALS['xoopsModuleConfig']['posts_per_page']) {
    include $GLOBALS['xoops']->path('class/pagenav.php');

    $nav = new \XoopsPageNav($total_posts, $GLOBALS['xoopsModuleConfig']['posts_per_page'], $start, 'start', 'topic_id=' . $topic_id . '&amp;order=' . $order . '&amp;status=' . $status . '&amp;mode=' . $mode);
    //if (isset($GLOBALS['xoopsModuleConfig']['do_rewrite']) && $GLOBALS['xoopsModuleConfig']['do_rewrite'] === 1) $nav->url = XOOPS_URL . $nav->url;
    if ('select' === $GLOBALS['xoopsModuleConfig']['pagenav_display']) {
        $navi = $nav->renderSelect();
    } elseif ('image' === $GLOBALS['xoopsModuleConfig']['pagenav_display']) {
        $navi = $nav->renderImageNav(4);
    } else {
        $navi = $nav->renderNav(4);
    }
    $xoopsTpl->assign('forum_page_nav', $navi);
} else {
    $xoopsTpl->assign('forum_page_nav', '');
}

if (empty($post_id)) {
    $first   = array_keys($postsArray);
    $post_id = !empty($first[0]) ? $first[0] : 0;
}

if (!empty($postsArray[$post_id])) {
    $xoops_pagetitle = $postsArray[$post_id]->getVar('subject') . ' [' . $forumObject->getVar('forum_name') . ']';
    $xoopsTpl->assign('xoops_pagetitle', $xoops_pagetitle);
    $xoopsOption['xoops_pagetitle'] = $xoops_pagetitle;
    $kw                             = array_unique(explode(' ', strip_tags($postsArray[$post_id]->getVar('post_text')), 150));
    asort($kw);
    $kwort = '';
    $z     = 0;
    foreach ($kw as $k) {
        if ($z < 30 && strlen(trim($k)) > 5) {
            $kwort .= trim($k) . ' ';
            ++$z;
        }
    }
    $xoTheme->addMeta('meta', 'keywords', $kwort);
    $xoTheme->addMeta('meta', 'description', substr(strip_tags($postsArray[$post_id]->getVar('post_text')), 0, 120));
}
unset($postsArray);

$xoopsTpl->assign('topic_print_link', "print.php?form=1&amp;{$page_query}");

$admin_actions = [];
$ad_merge      = '';
$ad_move       = '';
$ad_delete     = '';
// irmtfan add restore to viewtopic
$ad_restore  = '';
$ad_lock     = '';
$ad_unlock   = '';
$ad_sticky   = '';
$ad_unsticky = '';
$ad_digest   = '';
$ad_undigest = '';

// START irmtfan add restore to viewtopic
// if the topic is active
if ($topicObject->getVar('approved') > 0) {
    $admin_actions['merge']  = [
        'link'  => XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname', 'n') . '/topicmanager.php?mode=merge&amp;topic_id=' . $topic_id,
        'name'  => _MD_NEWBB_MERGETOPIC,
        'image' => $ad_merge
    ];
    $admin_actions['move']   = [
        'link'  => XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname', 'n') . '/topicmanager.php?mode=move&amp;topic_id=' . $topic_id,
        'name'  => _MD_NEWBB_MOVETOPIC,
        'image' => $ad_move
    ];
    $admin_actions['delete'] = [
        'link'  => XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname', 'n') . '/topicmanager.php?mode=delete&amp;topic_id=' . $topic_id,
        'name'  => _MD_NEWBB_DELETETOPIC,
        'image' => $ad_delete
    ];
    if (!$topicObject->getVar('topic_status')) {
        $admin_actions['lock'] = [
            'link'  => XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname', 'n') . '/topicmanager.php?mode=lock&amp;topic_id=' . $topic_id,
            'image' => $ad_lock,
            'name'  => _MD_NEWBB_LOCKTOPIC
        ];
    } else {
        $admin_actions['unlock'] = [
            'link'  => XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname', 'n') . '/topicmanager.php?mode=unlock&amp;topic_id=' . $topic_id,
            'image' => $ad_unlock,
            'name'  => _MD_NEWBB_UNLOCKTOPIC
        ];
    }
    if (!$topicObject->getVar('topic_sticky')) {
        $admin_actions['sticky'] = [
            'link'  => XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname', 'n') . '/topicmanager.php?mode=sticky&amp;topic_id=' . $topic_id,
            'image' => $ad_sticky,
            'name'  => _MD_NEWBB_STICKYTOPIC
        ];
    } else {
        $admin_actions['unsticky'] = [
            'link'  => XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname', 'n') . '/topicmanager.php?mode=unsticky&amp;topic_id=' . $topic_id,
            'image' => $ad_unsticky,
            'name'  => _MD_NEWBB_UNSTICKYTOPIC
        ];
    }
    if (!$topicObject->getVar('topic_digest')) {
        $admin_actions['digest'] = [
            'link'  => XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname', 'n') . '/topicmanager.php?mode=digest&amp;topic_id=' . $topic_id,
            'image' => $ad_digest,
            'name'  => _MD_NEWBB_DIGESTTOPIC
        ];
    } else {
        $admin_actions['undigest'] = [
            'link'  => XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname', 'n') . '/topicmanager.php?mode=undigest&amp;topic_id=' . $topic_id,
            'image' => $ad_undigest,
            'name'  => _MD_NEWBB_UNDIGESTTOPIC
        ];
    }
    // if the topic is pending/deleted then restore/approve
} else {
    $admin_actions['restore'] = [
        'link'  => XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname', 'n') . '/topicmanager.php?mode=restore&amp;topic_id=' . $topic_id,
        'name'  => _MD_NEWBB_RESTORETOPIC,
        'image' => $ad_restore
    ];
}
// END irmtfan add restore to viewtopic

$xoopsTpl->assign_by_ref('admin_actions', $admin_actions);
$xoopsTpl->assign('viewer_level', (int)($isAdmin ? 2 : is_object($GLOBALS['xoopsUser'])));

if ($GLOBALS['xoopsModuleConfig']['show_permissiontable']) {
    //    /** var Newbb\PermissionHandler $permHandler */
    //    $permHandler      = Newbb\Helper::getInstance()->getHandler('Permission');
    $permission_table = $permHandler->getPermissionTable($forumObject, $topicObject->getVar('topic_status'), $isAdmin);
    $xoopsTpl->assign_by_ref('permission_table', $permission_table);
}

///////////////////////////////
// show Poll
// START irmtfan poll_module
// irmtfan remove
/*
$pollmodul = false;
$moduleHandler = xoops_getHandler( 'module' );
$PollModule = $moduleHandler->getByDirname('xoopspoll');
if ($PollModule && $PollModule->getVar('isactive')) {
    $pollmodul = 'xoopspoll';
} else {
    $PollModule = $moduleHandler->getByDirname('umfrage');
    if ($PollModule && $PollModule->getVar('isactive')) {
        $pollmodul = 'umfrage';
    }
}
*/
//irmtfan remove
$pollModuleHandler = $moduleHandler->getByDirname($GLOBALS['xoopsModuleConfig']['poll_module']);
if (is_object($pollModuleHandler) && $pollModuleHandler->getVar('isactive')) {
    $poll_id = $topicObject->getVar('poll_id');
    // can vote in poll
    $pollVote = ($topicObject->getVar('topic_haspoll') && $poll_id > 0
                 && $topicHandler->getPermission($forumObject, $topicObject->getVar('topic_status'), 'vote'));
    // can add poll
    $pollAdd = $topicHandler->getPermission($forumObject, $topicObject->getVar('topic_status'), 'addpoll');
    if ($pollVote || $pollAdd) {
        $pollModuleHandler = $moduleHandler->getByDirname($GLOBALS['xoopsModuleConfig']['poll_module']);
        // new xoopspoll module
        if ($pollModuleHandler->getVar('version') >= 140) {
//            xoops_load('renderer', $GLOBALS['xoopsModuleConfig']['poll_module']);
            xoops_loadLanguage('main', $GLOBALS['xoopsModuleConfig']['poll_module']);
        // old xoopspoll or umfrage or any clone from them
        } else {
            $classPoll = $topicObject->loadOldPoll();
        }
    }
    // START can vote in poll
    if ($pollVote) {
        $xoopsTpl->assign('topic_poll', 1);
        $uid = is_object($GLOBALS['xoopsUser']) ? $GLOBALS['xoopsUser']->getVar('uid') : 0;
        // new xoopspoll module
        if ($pollModuleHandler->getVar('version') >= 140) {
            $xpollHandler = Xoopspoll\Helper::getInstance()->getHandler('Poll');
            /** @var \XoopsPoll $pollObject */
            $pollObject = $xpollHandler->get($poll_id);
            if (is_object($pollObject)) {

                /* check to see if user has rights to view the results */
                $vis_return = $pollObject->isResultVisible();
                $isVisible  = $vis_return;
                $visibleMsg = $isVisible ? '' : $vis_return;

                /* setup the module config handler */
                /** @var \XoopsConfigHandler $configHandler */
                $configHandler = xoops_getHandler('config');
                $xp_config     = $configHandler->getConfigsByCat(0, $pollModuleHandler->getVar('mid'));

                $GLOBALS['xoopsTpl']->assign([
                                                 'is_visible'      => $isVisible,
                                                 'visible_message' => $visibleMsg,
                                                 'disp_votes'      => $xp_config['disp_vote_nums'],
                                                 'lang_vote'       => constant('_MD_' . strtoupper($GLOBALS['xoopsModuleConfig']['poll_module']) . '_VOTE'),
                                                 'lang_results'    => constant('_MD_' . strtoupper($GLOBALS['xoopsModuleConfig']['poll_module']) . '_RESULTS'),
                                                 'back_link'       => ''
                                             ]);
                $classRenderer = ucfirst($GLOBALS['xoopsModuleConfig']['poll_module']) . 'Renderer';
                /** @var Xoopspoll\Renderer $renderer */
                $renderer = new $classRenderer($pollObject);
                // check to see if user has voted, show form if not, otherwise get results for form

                /** @var Xoopspoll\LogHandler $logHandler */
                $logHandler = Xoopspoll\Helper::getInstance()->getHandler('Log');
                if ($pollObject->isAllowedToVote()
                    && (!$logHandler->hasVoted($poll_id, xoops_getenv('REMOTE_ADDR'), $uid))) {
                    $myTpl = new \XoopsTpl();
                    $renderer->assignForm($myTpl);
                    $myTpl->assign('action', $GLOBALS['xoops']->url("modules/newbb/votepolls.php?topic_id={$topic_id}&amp;poll_id={$poll_id}"));
                    $topic_pollform = $myTpl->fetch($GLOBALS['xoops']->path('modules/' . $GLOBALS['xoopsModuleConfig']['poll_module'] . '/templates/' . $GLOBALS['xoopsModuleConfig']['poll_module'] . '_view.tpl'));
                    $GLOBALS['xoopsTpl']->assign('topic_pollform', $topic_pollform);
                } else {
                    $GLOBALS['xoopsTpl']->assign('can_vote', false);
                    $xoopsTpl->assign('topic_pollresult', 1);
                    $GLOBALS['xoopsTpl']->assign('topic_resultform', $renderer->renderResults());
                }
            }
            // old xoopspoll or umfrage or any clone from them
        } else {
            $pollObject    = new $classPoll($poll_id);
            $classRenderer = $classPoll . 'Renderer';
            $renderer      = new $classRenderer($pollObject);
            $xoopsTpl->assign('lang_alreadyvoted2', _PL_ALREADYVOTED2);
            $xoopsTpl->assign('has_ended', $pollObject->getVar('end_time') < time() ? 1 : 0);
            // umfrage has polltype
            $polltype = $pollObject->getVar('polltype');
            if (!empty($polltype)) {
                $xoopsTpl->assign('polltype', $polltype);
                switch ($polltype) {
                    case 1:
                        $xoopsTpl->assign('polltypecomment', '');
                        break;
                    case 2:
                        $xoopsTpl->assign('polltypecomment', _PL_FULLBLIND);
                        break;
                    case 3:
                        $xoopsTpl->assign('polltypecomment', _PL_HALFBLIND);
                        break;

                }
            }
            $classLog = $classPoll . 'Log';
            $hasvoted = 0;
            if ($GLOBALS['xoopsUser']) {
                if ($classLog::hasVoted($poll_id, Request::getString('REMOTE_ADDR', '', 'SERVER'), $uid)) {
                    $hasvoted = 1;
                }
            } else {
                $hasvoted = 1;
            }
            $xoopsTpl->assign('hasVoted', $hasvoted);
            $xoopsTpl->assign('lang_vote', _PL_VOTE);
            $xoopsTpl->assign('lang_results', $pollObject->getVar('end_time') < time() ? _PL_RESULTS : _PL_STANDINGS);
            // irmtfan - if the poll is expired show the result
            if ($hasvoted || $pollObject->hasExpired()) {
                $renderer->assignResults($xoopsTpl);
                $xoopsTpl->assign('topic_pollresult', 1);
                setcookie('newbb_polls[' . $poll_id . ']', 1);
            } else {
                $renderer->assignForm($xoopsTpl);
                $xoopsTpl->assign('lang_vote', _PL_VOTE);
                $xoopsTpl->assign('lang_results', _PL_RESULTS);
                setcookie('newbb_polls[' . $poll_id . ']', 1);
            }
        }
    }
    // END can vote in poll
    // START can add poll
    if ($pollAdd) {
        if (!$topicObject->getVar('topic_haspoll')) {
            if (is_object($GLOBALS['xoopsUser'])
                && $GLOBALS['xoopsUser']->getVar('uid') == $topicObject->getVar('topic_poster')) {
                $t_poll = newbbDisplayImage('t_poll', _MD_NEWBB_ADDPOLL);
                $xoopsTpl->assign('forum_addpoll', '<a href=\'' . XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname', 'n') . '/polls.php?op=add&amp;topic_id=' . $topic_id . '\'>' . $t_poll . '</a>');
            }
        } elseif ($isAdmin
                  || (is_object($pollObject) && is_object($GLOBALS['xoopsUser'])
                      && $GLOBALS['xoopsUser']->getVar('uid') == $pollObject->getVar('user_id'))) {
            $poll_edit    = '';
            $poll_delete  = '';
            $poll_restart = '';
            $poll_log     = '';

            $adminpoll_actions                = [];
            $adminpoll_actions['editpoll']    = [
                'link'  => XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname', 'n') . '/polls.php?op=edit&amp;poll_id=' . $topicObject->getVar('poll_id') . '&amp;topic_id=' . $topic_id,
                'image' => $poll_edit,
                'name'  => _MD_NEWBB_EDITPOLL
            ];
            $adminpoll_actions['deletepoll']  = [
                'link'  => XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname', 'n') . '/polls.php?op=delete&amp;poll_id=' . $topicObject->getVar('poll_id') . '&amp;topic_id=' . $topic_id,
                'image' => $poll_delete,
                'name'  => _MD_NEWBB_DELETEPOLL
            ];
            $adminpoll_actions['restartpoll'] = [
                'link'  => XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname', 'n') . '/polls.php?op=restart&amp;poll_id=' . $topicObject->getVar('poll_id') . '&amp;topic_id=' . $topic_id . '&amp;forum=' . $forum_id,
                'image' => $poll_restart,
                'name'  => _MD_NEWBB_RESTARTPOLL
            ];
            $adminpoll_actions['logpoll']     = [
                'link'  => XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname', 'n') . '/polls.php?op=log&amp;poll_id=' . $topicObject->getVar('poll_id') . '&amp;topic_id=' . $topic_id . '&amp;forum=' . $forum_id,
                'image' => $poll_log,
                'name'  => _MD_NEWBB_POLL_VIEWLOG
            ];

            $xoopsTpl->assign_by_ref('adminpoll_actions', $adminpoll_actions);
        }
    }
    // END can add poll
}
if (isset($pollObject)) {
    unset($pollObject);
}
// END irmtfan poll_module

$xoopsTpl->assign('p_up', newbbDisplayImage('up', _MD_NEWBB_TOP));
$xoopsTpl->assign('rating_enable', $GLOBALS['xoopsModuleConfig']['rating_enabled']);
$xoopsTpl->assign('groupbar_enable', $GLOBALS['xoopsModuleConfig']['groupbar_enabled']);
$xoopsTpl->assign('anonymous_prefix', $GLOBALS['xoopsModuleConfig']['anonymous_prefix']);
// irmtfan add alt for prev next and down icons.
$xoopsTpl->assign('previous', newbbDisplayImage('previous', _MD_NEWBB_PREVTOPIC));
$xoopsTpl->assign('next', newbbDisplayImage('next', _MD_NEWBB_NEXTTOPIC));
$xoopsTpl->assign('down', newbbDisplayImage('down', _MD_NEWBB_BOTTOM));
$xoopsTpl->assign('post_content', newbbDisplayImage('post'));

if (!empty($GLOBALS['xoopsModuleConfig']['rating_enabled'])) {
    $xoopsTpl->assign('votes', $topicObject->getVar('votes'));
    $rating = number_format($topicObject->getVar('rating') / 2, 0);
    if ($rating < 1) {
        $rating_img = newbbDisplayImage('blank');
    } else {
        // irmtfan - add alt key for rating
        $rating_img = newbbDisplayImage('rate' . $rating, constant('_MD_NEWBB_RATE' . $rating));
    }
    $xoopsTpl->assign('rating_img', $rating_img);
    $xoopsTpl->assign('rate1', newbbDisplayImage('rate1', _MD_NEWBB_RATE1));
    $xoopsTpl->assign('rate2', newbbDisplayImage('rate2', _MD_NEWBB_RATE2));
    $xoopsTpl->assign('rate3', newbbDisplayImage('rate3', _MD_NEWBB_RATE3));
    $xoopsTpl->assign('rate4', newbbDisplayImage('rate4', _MD_NEWBB_RATE4));
    $xoopsTpl->assign('rate5', newbbDisplayImage('rate5', _MD_NEWBB_RATE5));
}

// create jump box
if (!empty($GLOBALS['xoopsModuleConfig']['show_jump'])) {
    require_once __DIR__ . '/include/functions.forum.php';
    $xoopsTpl->assign('forum_jumpbox', newbbMakeJumpbox($forum_id));
}

$xoopsTpl->assign([
                      'lang_forum_index' => sprintf(_MD_NEWBB_FORUMINDEX, htmlspecialchars($GLOBALS['xoopsConfig']['sitename'], ENT_QUOTES)),
                      'lang_from'        => _MD_NEWBB_FROM,
                      'lang_joined'      => _MD_NEWBB_JOINED,
                      'lang_posts'       => _MD_NEWBB_POSTS,
                      'lang_poster'      => _MD_NEWBB_POSTER,
                      'lang_thread'      => _MD_NEWBB_THREAD,
                      'lang_edit'        => _EDIT,
                      'lang_delete'      => _DELETE,
                      'lang_reply'       => _REPLY,
                      'lang_postedon'    => _MD_NEWBB_POSTEDON,
                      'lang_groups'      => _MD_NEWBB_GROUPS
                  ]);

$viewmode_options = [];
if ('DESC' === $order) {
    $viewmode_options[] = [
        'link'  => XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname', 'n') . '/viewtopic.php?order=ASC&amp;status=$status&amp;topic_id=' . $topic_id,
        'title' => _OLDESTFIRST
    ];
} else {
    $viewmode_options[] = [
        'link'  => XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname', 'n') . '/viewtopic.php?order=DESC&amp;status=$status&amp;topic_id=' . $topic_id,
        'title' => _NEWESTFIRST
    ];
}

switch ($status) {
    case 'active':
        $current_status = '[' . _MD_NEWBB_TYPE_ADMIN . ']';
        break;
    case 'pending':
        $current_status = '[' . _MD_NEWBB_TYPE_PENDING . ']';
        break;
    case 'deleted':
        $current_status = '[' . _MD_NEWBB_TYPE_DELETED . ']';
        break;
    default:
        $current_status = '';
        break;
}
$xoopsTpl->assign('topicstatus', $current_status);

$xoopsTpl->assign('mode', $mode);
$xoopsTpl->assign('status', $status);
//$xoopsTpl->assign('viewmode_compact', ($viewmode=="compact")?1:0);
$xoopsTpl->assign_by_ref('viewmode_options', $viewmode_options);
unset($viewmode_options);
$xoopsTpl->assign('menumode', $menumode);
$xoopsTpl->assign('menumode_other', $menumode_other);

// START irmtfan add verifyUser to quick reply
//check banning
//$moderateHandler = Newbb\Helper::getInstance()->getHandler('Moderate');
if (!empty($GLOBALS['xoopsModuleConfig']['quickreply_enabled'])
    && $topicHandler->getPermission($forumObject, $topicObject->getVar('topic_status'), 'reply')
    && $moderateHandler->verifyUser(-1, '', $forumObject->getVar('forum_id'))) {
    // END irmtfan add verifyUser to quick reply
    $forum_form = new \XoopsThemeForm(_MD_NEWBB_POSTREPLY, 'quick_reply', XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname', 'n') . '/post.php', 'post', true);
    if (!is_object($GLOBALS['xoopsUser'])) {
        //$configHandler = xoops_getHandler('config');
        $user_tray = new \XoopsFormElementTray(_MD_NEWBB_ACCOUNT);
        $user_tray->addElement(new \XoopsFormText(_MD_NEWBB_NAME, 'uname', 26, 255));
        $user_tray->addElement(new \XoopsFormPassword(_MD_NEWBB_PASSWORD, 'pass', 10, 32));
        $login_checkbox = new \XoopsFormCheckBox('', 'login', 1);
        $login_checkbox->addOption(1, _MD_NEWBB_LOGIN);
        $user_tray->addElement($login_checkbox);
        $forum_form->addElement($user_tray);
        $captcha = new \XoopsFormCaptcha('', "topic_{$topic_id}_{$start}");
        $captcha->setConfig('mode', 'text');
        $forum_form->addElement($captcha);
    }

    //$quickform = ( !empty($GLOBALS['xoopsModuleConfig']["editor_default"]) ) ? $GLOBALS['xoopsModuleConfig']["editor_default"] : "textarea";
    $quickform               = !empty($GLOBALS['xoopsModuleConfig']['editor_quick_default']) ? $GLOBALS['xoopsModuleConfig']['editor_quick_default'] : 'textarea';
    $editor_configs          = [];
    $editor_configs ['name'] = 'message';
    //$editor_configs [ "value" ]     = $message ;
    $editor_configs ['rows']   = empty($GLOBALS['xoopsModuleConfig'] ['editor_rows']) ? 10 : $GLOBALS['xoopsModuleConfig'] ['editor_rows'];
    $editor_configs ['cols']   = empty($GLOBALS['xoopsModuleConfig'] ['editor_cols']) ? 30 : $GLOBALS['xoopsModuleConfig'] ['editor_cols'];
    $editor_configs ['width']  = empty($GLOBALS['xoopsModuleConfig'] ['editor_width']) ? '100%' : $GLOBALS['xoopsModuleConfig'] ['editor_width'];
    $editor_configs ['height'] = empty($GLOBALS['xoopsModuleConfig'] ['editor_height']) ? '400px' : $GLOBALS['xoopsModuleConfig'] ['editor_height'];
    $_editor                   = new \XoopsFormEditor(_MD_NEWBB_MESSAGEC, $quickform, $editor_configs, true);
    $forum_form->addElement($_editor, true);

    $forum_form->addElement(new \XoopsFormHidden('dohtml', 0));
    $forum_form->addElement(new \XoopsFormHidden('dosmiley', 1));
    $forum_form->addElement(new \XoopsFormHidden('doxcode', 1));
    $forum_form->addElement(new \XoopsFormHidden('dobr', 1));
    $forum_form->addElement(new \XoopsFormHidden('attachsig', 1));

    $forum_form->addElement(new \XoopsFormHidden('isreply', 1));

    $forum_form->addElement(new \XoopsFormHidden('subject', _MD_NEWBB_RE . ': ' . $topicObject->getVar('topic_title', 'e')));
    $forum_form->addElement(new \XoopsFormHidden('pid', empty($post_id) ? $topicHandler->getTopPostId($topic_id) : $post_id));
    $forum_form->addElement(new \XoopsFormHidden('topic_id', $topic_id));
    $forum_form->addElement(new \XoopsFormHidden('forum', $forum_id));
    //$forum_form->addElement(new \XoopsFormHidden('viewmode', $viewmode));
    $forum_form->addElement(new \XoopsFormHidden('order', $order));
    $forum_form->addElement(new \XoopsFormHidden('start', $start));

    $forum_form->addElement(new \XoopsFormHidden('notify', -1));
    $forum_form->addElement(new \XoopsFormHidden('contents_submit', 1));

    $submit_button = new \XoopsFormButton('', 'quick_submit', _SUBMIT, 'submit');
    $submit_button->setExtra('onclick="if (document.forms.quick_reply.message.value === \'RE\' || document.forms.quick_reply.message.value === \'\') { alert(\'' . _MD_NEWBB_QUICKREPLY_EMPTY . '\'); return false;} else { return true;}"');
    $forum_form->addElement($submit_button);

    $toggles = newbbGetCookie('G', true);
    // START irmtfan improve quickreply smarty variable - add alt key to quick reply button - change $display to $style for more comprehension - add toggle $quickreply['expand']
    $quickreply           = [];
    $qr_collapse          = 't_qr';
    $qr_expand            = 't_qr_expand'; // change this
    $quickreply['icon']   = [
        'expand'   => $iconHandler->getImageSource($qr_expand),
        'collapse' => $iconHandler->getImageSource($qr_collapse)
    ];
    $quickreply['show']   = 1; // = !empty($GLOBALS['xoopsModuleConfig']['quickreply_enabled']
    $quickreply['expand'] = (count($toggles) > 0) ? (in_array('qr', $toggles, true) ? false : true) : true;
    if ($quickreply['expand']) {
        $quickreply['style']     = 'block';        //irmtfan move semicolon
        $quickreply_icon_display = $qr_expand;
        $quickreply_alt          = _MD_NEWBB_HIDE . ' ' . _MD_NEWBB_QUICKREPLY;
    } else {
        $quickreply['style']     = 'none';        //irmtfan move semicolon
        $quickreply_icon_display = $qr_collapse;
        $quickreply_alt          = _MD_NEWBB_SEE . ' ' . _MD_NEWBB_QUICKREPLY;
    }
    $quickreply['displayImage'] = newbbDisplayImage($quickreply_icon_display, $quickreply_alt);
    $quickreply['form']         = $forum_form->render();
    $xoopsTpl->assign('quickreply', $quickreply);
    // END irmtfan improve quickreply smarty variable
    unset($forum_form);
} else {
    $xoopsTpl->assign('quickreply', ['show' => 0]);
}

if ($GLOBALS['xoopsModuleConfig']['do_tag']
    && @require_once $GLOBALS['xoops']->path('modules/tag/include/tagbar.php')) {
    $xoopsTpl->assign('tagbar', tagBar($topicObject->getVar('topic_tags', 'n')));
}
// irmtfan move to footer.php
require_once __DIR__ . '/footer.php';
include $GLOBALS['xoops']->path('footer.php');
$xoopsLogger->stopTime('newBB_viewtopic');
