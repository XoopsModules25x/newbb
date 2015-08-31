<?php
/**
 * NewBB 4.3x, the forum module for XOOPS project
 *
 * @copyright      XOOPS Project (http://xoops.org)
 * @license        http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author         Taiwen Jiang (phppp or D.J.) <phppp@users.sourceforge.net>
 * @since          4.00
 * @version        $Id$
 * @package        module::newbb
 */

include_once __DIR__ . '/header.php';

if (!XoopsRequest::getInt('forum', 0, 'GET')) {
    redirect_header(XOOPS_URL . '/index.php', 2, _MD_ERRORFORUM);
}
mod_loadFunctions('read');

/*
 * Build the page query
 */
$query_vars  = array('forum', 'type', 'status', 'sort', 'order', 'start', 'since');
$query_array = array();
foreach ($query_vars as $var) {
    if (XoopsRequest::getString($var, '', 'GET')) {
        $query_array[$var] = "{$var}=" . XoopsRequest::getString($var, '', 'GET');
    }
}
$page_query = implode('&amp;', array_values($query_array));

if (XoopsRequest::getInt('mark', 0, 'GET')) {
    if (1 === XoopsRequest::getInt('mark', 0, 'GET')) { // marked as read
        $markvalue  = 1;
        $markresult = _MD_MARK_READ;
    } else { // marked as unread
        $markvalue  = 0;
        $markresult = _MD_MARK_UNREAD;
    }
    newbb_setRead_topic($markvalue, XoopsRequest::getInt('forum', 0, 'GET'));
    $url = XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname') . '/viewforum.php?' . $page_query;
    redirect_header($url, 2, _MD_ALL_TOPIC_MARKED . ' ' . $markresult);
}

$forum_id = XoopsRequest::getInt('forum', 0, 'GET');
$type     = XoopsRequest::getInt('type', 0, 'GET');
$status   = (XoopsRequest::getString('status', '', 'GET') && in_array(XoopsRequest::getString('status', '', 'GET'), array(
        'active',
        'pending',
        'deleted',
        'digest',
        'unreplied',
        'unread'), true)) ? XoopsRequest::getString('status', '', 'GET') : ''; // (!empty($_GET['status']) && in_array($_GET['status'], array("active", "pending", "deleted", "digest", "unreplied", "unread"))) ? $_GET['status'] : '';

// irmtfan add mode
$mode = (XoopsRequest::getString('status', '', 'GET') && in_array(XoopsRequest::getString('status', '', 'GET'), array(
        'active',
        'pending',
        'deleted'), true)) ? 2 : (XoopsRequest::getInt('mode', 0, 'GET')); // (!empty($status) && in_array($status, array("active", "pending", "deleted"))) ? 2 : (!empty($_GET['mode']) ? (int)($_GET['mode']) : 0);

$forumHandler = &xoops_getmodulehandler('forum', 'newbb');
$forum_obj    = $forumHandler->get($forum_id);

if (!$forum_obj) {
    redirect_header(XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname') . '/index.php', 2, _MD_ERRORFORUM);
}

if (!$forumHandler->getPermission($forum_obj)) {
    redirect_header(XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname') . '/index.php', 2, _NOPERM);
}
newbb_setRead('forum', $forum_id, $forum_obj->getVar('forum_last_post_id'));

$xoops_pagetitle = $forum_obj->getVar('forum_name') . ' [' . $xoopsModule->getVar('name') . ']';

$xoopsOption['template_main']   = 'newbb_viewforum.tpl';
$xoopsOption['xoops_pagetitle'] = $xoops_pagetitle;
// irmtfan remove and move to footer.php
//$xoopsOption['xoops_module_header']= $xoops_module_header;
// irmtfan include header.php after defining $xoopsOption['template_main']
include_once $GLOBALS['xoops']->path('header.php');
mod_loadFunctions('render', 'newbb');
// irmtfan new method
if (!empty($GLOBALS['xoopsModuleConfig']['rss_enable'])) {
    $xoopsTpl->assign('xoops_module_header', '
    <link rel="alternate" type="application/xml+rss" title="' . $xoopsModule->getVar('name') . '-' . $forum_obj->getVar('forum_name') . '" href="' . XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname') . '/rss.php?f=' . $forum_id . '" />
    ' . @$xoopsTpl->get_template_vars('xoops_module_header'));
}
//$xoopsTpl->assign('xoops_module_header', $xoops_module_header);
$xoopsTpl->assign('forum_id', $forum_id);
$xoopsTpl->assign('version', $xoopsModule->getVar('version'));

$isadmin = newbb_isAdmin($forum_obj);
$xoopsTpl->assign('viewer_level', ($isadmin) ? 2 : is_object($GLOBALS['xoopsUser']));
/* Only admin has access to admin mode */
if (!$isadmin) {
    $status = (!empty($status) && in_array($status, array('active', 'pending', 'deleted'), true)) ? '' : $status;
    // irmtfan add mode
    $mode = 0;
}
// irmtfan add mode
$xoopsTpl->assign('mode', $mode);
$xoopsTpl->assign('status', $status);
if ($isadmin) {
    $xoopsTpl->assign('forum_index_cpanel', array('link' => 'admin/index.php', 'name' => _MD_ADMINCP));
}

if ($GLOBALS['xoopsModuleConfig']['wol_enabled']) {
    $onlineHandler =& xoops_getmodulehandler('online', 'newbb');
    $onlineHandler->init($forum_obj);
    $xoopsTpl->assign('online', $onlineHandler->show_online());
}

if ($forumHandler->getPermission($forum_obj, 'post')) {
    // irmtfan full URL
    $xoopsTpl->assign('forum_post_or_register', "<a href=\"" . XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname') . "/newtopic.php?forum={$forum_id}\">" . newbbDisplayImage('t_new', _MD_POSTNEW) . '</a>');
    if ($pollmodules && $forumHandler->getPermission($forum_obj, 'addpoll')) {
        $t_poll = newbbDisplayImage('t_poll', _MD_ADDPOLL);
        $xoopsTpl->assign('forum_addpoll', "<a href=\"" . XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname') . "/newtopic.php?op=add&amp;forum={$forum_id}\">{$t_poll}</a>");
    }
} else {
    if (!is_object($GLOBALS['xoopsUser']) && !empty($GLOBALS['xoopsModuleConfig']['show_reg'])) {
        $redirect = preg_replace("|(.*)\/modules\/newbb\/(.*)|", "\\1/modules/newbb/newtopic.php?forum=" . $forum_id, htmlspecialchars($xoopsRequestUri));
        $xoopsTpl->assign('forum_post_or_register', "<a href='" . XOOPS_URL . "/user.php?xoops_redirect={$redirect}'>" . _MD_REGTOPOST . '</a>');
        $xoopsTpl->assign('forum_addpoll', '');
    } else {
        $xoopsTpl->assign('forum_post_or_register', '');
        $xoopsTpl->assign('forum_addpoll', '');
    }
}
$parentforum = $forumHandler->getParents($forum_obj);
$xoopsTpl->assign_by_ref('parentforum', $parentforum);

$criteria = new CriteriaCompo(new Criteria('parent_forum', $forum_id));
$criteria->add(new Criteria('forum_id', '(' . implode(', ', $forumHandler->getIdsByPermission('access')) . ')', 'IN'));
$criteria->setSort('forum_order');

if ($forums = $forumHandler->getAll($criteria, null, false)) {
    $subforum_array = $forumHandler->display($forums, $GLOBALS['xoopsModuleConfig']['length_title_index'], $GLOBALS['xoopsModuleConfig']['count_subforum']);
    $subforum       = array_values($subforum_array[$forum_id]);
    unset($subforum_array);
    $xoopsTpl->assign_by_ref('subforum', $subforum);
}

$categoryHandler =& xoops_getmodulehandler('category');
$category_obj    =& $categoryHandler->get($forum_obj->getVar('cat_id'), array('cat_title'));
$xoopsTpl->assign('category', array('id' => $forum_obj->getVar('cat_id'), 'title' => $category_obj->getVar('cat_title')));

$xoopsTpl->assign('forum_index_title', sprintf(_MD_FORUMINDEX, htmlspecialchars($GLOBALS['xoopsConfig']['sitename'], ENT_QUOTES)));
$xoopsTpl->assign('forum_name', $forum_obj->getVar('forum_name'));
$xoopsTpl->assign('forum_moderators', $forum_obj->dispForumModerators());

// irmtfan - add and edit: u.uname => t.topic_poster | t.topic_time => t.topic_id | "t.rating"=>_MD_RATINGS, | p.post_time => t.topic_last_post_id
$sel_sort_array = array('t.topic_title' => _MD_TOPICTITLE, 't.topic_poster' => _MD_TOPICPOSTER, 't.topic_id' => _MD_TOPICTIME, 't.topic_replies' => _MD_NUMBERREPLIES, 't.topic_views' => _MD_VIEWS, 't.rating' => _MD_RATINGS, 't.topic_last_post_id' => _MD_LASTPOSTTIME);
if (!XoopsRequest::getString('sort', '', 'GET') || !array_key_exists(XoopsRequest::getString('sort', '', 'GET'), $sel_sort_array)) {
    $sort = 't.topic_last_post_id';
} else {
    $sort = XoopsRequest::getString('sort', '', 'GET');
}

$forum_selection_sort = '<select name="sort">';
foreach ($sel_sort_array as $sort_k => $sort_v) {
    $forum_selection_sort .= '<option value="' . $sort_k . '"' . (($sort === $sort_k) ? ' selected="selected"' : '') . '>' . $sort_v . '</option>';
}
$forum_selection_sort .= '</select>';

$xoopsTpl->assign_by_ref('forum_selection_sort', $forum_selection_sort);

$order                 = (!XoopsRequest::getString('order', '', 'GET') || XoopsRequest::getString('order', '', 'GET') !== 'ASC') ? 'DESC' : 'ASC';
$forum_selection_order = '<select name="order">';
$forum_selection_order .= '<option value="ASC"' . (($order === 'ASC') ? ' selected' : '') . '>' . _MD_ASCENDING . '</option>';
$forum_selection_order .= '<option value="DESC"' . (($order === 'DESC') ? ' selected' : '') . '>' . _MD_DESCENDING . '</option>';
$forum_selection_order .= '</select>';

$xoopsTpl->assign_by_ref('forum_selection_order', $forum_selection_order);

$since = XoopsRequest::getInt('since', $GLOBALS['xoopsModuleConfig']['since_default'], 'GET');
mod_loadFunctions('time', 'newbb');
$forum_selection_since = newbb_sinceSelectBox($since);
$xoopsTpl->assign_by_ref('forum_selection_since', $forum_selection_since);

$query_sort = $query_array;
unset($query_sort['sort'], $query_sort['order']);
$page_query_sort = implode('&amp;', array_values($query_sort));
unset($query_sort);
// irmtfan - edit: u.uname => t.topic_poster | t.topic_time => t.topic_id | p.post_time => t.topic_last_post_id
$xoopsTpl->assign('h_topic_link', XOOPS_URL . "/modules/newbb/viewforum.php?{$page_query_sort}&amp;sort=t.topic_title&amp;order=" . (($sort === 't.topic_title' && $order === 'DESC') ? 'ASC' : 'DESC'));
$xoopsTpl->assign('h_reply_link', XOOPS_URL . "/modules/newbb/viewforum.php?{$page_query_sort}&amp;sort=t.topic_replies&amp;order=" . (($sort === 't.topic_replies' && $order === 'DESC') ? 'ASC' : 'DESC'));
$xoopsTpl->assign('h_poster_link', XOOPS_URL . "/modules/newbb/viewforum.php?{$page_query_sort}&amp;sort=t.topic_poster&amp;order=" . (($sort === 't.topic_poster' && $order === 'DESC') ? 'ASC' : 'DESC'));
$xoopsTpl->assign('h_views_link', XOOPS_URL . "/modules/newbb/viewforum.php?{$page_query_sort}&amp;sort=t.topic_views&amp;order=" . (($sort === 't.topic_views' && $order === 'DESC') ? 'ASC' : 'DESC'));
$xoopsTpl->assign('h_rating_link', XOOPS_URL . "/modules/newbb/viewforum.php?{$page_query_sort}&amp;sort=t.rating&amp;order=" . (($sort === 't.rating' && $order === 'DESC') ? 'ASC' : 'DESC')); // irmtfan t.topic_ratings to t.rating
$xoopsTpl->assign('h_date_link', XOOPS_URL . "/modules/newbb/viewforum.php?{$page_query_sort}&amp;sort=t.topic_last_post_id&amp;order=" . (($sort === 't.topic_last_post_id' && $order === 'DESC') ? 'ASC' : 'DESC'));
$xoopsTpl->assign('h_publish_link', XOOPS_URL . "/modules/newbb/viewforum.php?{$page_query_sort}&amp;sort=t.topic_id&amp;order=" . (($sort === 't.topic_id' && $order === 'DESC') ? 'ASC' : 'DESC'));
$xoopsTpl->assign('forum_since', $since); // For $since in search.php

// irmtfan - if no since it should be 0
$since     = XoopsRequest::getInt('since', 0, 'GET');
$startdate = empty($since) ? 0 : (time() - newbb_getSinceTime($since));
$start     = XoopsRequest::getInt('start', 0, 'GET');

$criteria_vars = array('startdate', 'start', 'sort', 'order', 'type', 'status', 'excerpt');
foreach ($criteria_vars as $var) {
    $criteria_topic[$var] = @${$var};
}
$criteria_topic['excerpt'] = $GLOBALS['xoopsModuleConfig']['post_excerpt'];

list($allTopics, $sticky) = $forumHandler->getAllTopics($forum_obj, $criteria_topic);

$xoopsTpl->assign_by_ref('topics', $allTopics);
$xoopsTpl->assign('sticky', $sticky);
$xoopsTpl->assign('rating_enable', $GLOBALS['xoopsModuleConfig']['rating_enabled']);
$xoopsTpl->assign('img_newposts', newbbDisplayImage('topic_new', _MD_NEWPOSTS));
$xoopsTpl->assign('img_hotnewposts', newbbDisplayImage('topic_hot_new', _MD_MORETHAN));
$xoopsTpl->assign('img_folder', newbbDisplayImage('topic', _MD_NONEWPOSTS));
$xoopsTpl->assign('img_hotfolder', newbbDisplayImage('topic_hot', _MD_MORETHAN2));
$xoopsTpl->assign('img_locked', newbbDisplayImage('topic_locked', _MD_TOPICLOCKED));

$xoopsTpl->assign('img_sticky', newbbDisplayImage('topic_sticky', _MD_TOPICSTICKY));
$xoopsTpl->assign('img_digest', newbbDisplayImage('topic_digest', _MD_TOPICDIGEST));
$xoopsTpl->assign('img_poll', newbbDisplayImage('poll', _MD_TOPICHASPOLL));

$xoopsTpl->assign('mark_read', XOOPS_URL . "/modules/newbb/viewforum.php?mark=1&amp;{$page_query}");
$xoopsTpl->assign('mark_unread', XOOPS_URL . "/modules/newbb/viewforum.php?mark=2&amp;{$page_query}");

$xoopsTpl->assign('post_link', XOOPS_URL . '/modules/newbb/viewpost.php?forum=' . $forum_id);
$xoopsTpl->assign('newpost_link', XOOPS_URL . '/modules/newbb/viewpost.php?status=new&amp;forum=' . $forum_id);

$query_type = $query_array;
unset($query_type['type']);
$page_query_type = implode('&amp;', array_values($query_type));
unset($query_type);
$typeHandler =& xoops_getmodulehandler('type', 'newbb');
$typeOptions = null;
$types       = array();
if ($types = $typeHandler->getByForum($forum_id)) {
    $typeOptions[] = array('title' => _ALL, 'link' => XOOPS_URL . "/modules/newbb/viewforum.php?{$page_query_type}");
    foreach ($types as $key => $item) {
        $typeOptions[] = array('title' => $item['type_name'], 'link' => XOOPS_URL . "/modules/newbb/viewforum.php?{$page_query_type}&amp;type={$key}");
    }
}
if ($type > 0) {
    mod_loadFunctions('topic', 'newbb');
    $xoopsTpl->assign('forum_topictype', getTopicTitle('', $types[$type]['type_name'], $types[$type]['type_color']));
}
$xoopsTpl->assign_by_ref('typeOptions', $typeOptions);

$query_status = $query_array;
unset($query_status['status']);
$page_query_status = implode('&amp;', array_values($query_status));
unset($query_status);
$xoopsTpl->assign('newpost_link', XOOPS_URL . '/modules/newbb/viewpost.php?status=new&amp;forum=' . $forum_obj->getVar('forum_id'));
$xoopsTpl->assign('all_link', XOOPS_URL . "/modules/newbb/viewforum.php?{$page_query_status}");
$xoopsTpl->assign('digest_link', XOOPS_URL . "/modules/newbb/viewforum.php?{$page_query_status}&amp;status=digest");
$xoopsTpl->assign('unreplied_link', XOOPS_URL . "/modules/newbb/viewforum.php?{$page_query_status}&amp;status=unreplied");
$xoopsTpl->assign('unread_link', XOOPS_URL . "/modules/newbb/viewforum.php?{$page_query_status}&amp;status=unread");
switch ($status) {
    case 'digest':
        $current_status = _MD_DIGEST;
        break;
    case 'unreplied':
        $current_status = _MD_UNREPLIED;
        break;
    case 'unread':
        $current_status = _MD_UNREAD;
        break;
    case 'active':
        $current_status = _MD_TYPE_ADMIN;
        break;
    case 'pending':
        $current_status = _MD_TYPE_PENDING;
        break;
    case 'deleted':
        $current_status = _MD_TYPE_DELETED;
        break;
    default:
        $current_status = '';
        break;
}
$xoopsTpl->assign('forum_topicstatus', $current_status);

$all_topics = $forumHandler->getTopicCount($forum_obj, $startdate, $status);
if ($all_topics > $GLOBALS['xoopsModuleConfig']['topics_per_page']) {
    include_once $GLOBALS['xoops']->path('class/pagenav.php');
    $query_nav = $query_array;
    unset($query_nav['start']);
    $page_query_nav = implode('&amp;', array_values($query_nav));
    unset($query_nav);
    $nav = new XoopsPageNav($all_topics, $GLOBALS['xoopsModuleConfig']['topics_per_page'], $start, 'start', $page_query_nav);
    if ($GLOBALS['xoopsModuleConfig']['pagenav_display'] === 'select') {
        $navi = $nav->renderSelect();
    } elseif ($GLOBALS['xoopsModuleConfig']['pagenav_display'] === 'image') {
        $navi = $nav->renderImageNav(4);
    } else {
        $navi = $nav->renderNav(4);
    }

    $xoopsTpl->assign('forum_pagenav', $navi);
} else {
    $xoopsTpl->assign('forum_pagenav', '');
}

if (!empty($GLOBALS['xoopsModuleConfig']['show_jump'])) {
    mod_loadFunctions('forum', 'newbb');
    $xoopsTpl->assign('forum_jumpbox', newbb_make_jumpbox($forum_id));
}

if ($GLOBALS['xoopsModuleConfig']['show_permissiontable']) {
    $permHandler      = &xoops_getmodulehandler('permission', 'newbb');
    $permission_table = $permHandler->permission_table($forum_id, false, $isadmin);
    $xoopsTpl->assign_by_ref('permission_table', $permission_table);
    unset($permission_table);
}

if ($GLOBALS['xoopsModuleConfig']['rss_enable'] === 1) {
    $xoopsTpl->assign('rss_button', "<div align='right'><a href='" . XOOPS_URL . '/modules/' . $xoopsModule->dirname() . '/rss.php?f=' . $forum_id . "' title='RSS feed' target='_blank'>" . newbbDisplayImage('rss', 'RSS feed') . '</a></div>');
}
// irmtfan move to footer.php
include_once __DIR__ . '/footer.php';
include $GLOBALS['xoops']->path('footer.php');
