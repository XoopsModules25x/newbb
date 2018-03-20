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

require_once __DIR__ . '/header.php';

if (!Request::getInt('forum', 0, 'GET')) {
    redirect_header(XOOPS_URL . '/index.php', 2, _MD_NEWBB_ERRORFORUM);
}
require_once __DIR__ . '/include/functions.read.php';

/*
 * Build the page query
 */
$query_vars  = ['forum', 'type', 'status', 'sort', 'order', 'start', 'since'];
$query_array = [];
foreach ($query_vars as $var) {
    if (Request::getString($var, '', 'GET')) {
        $query_array[$var] = "{$var}=" . Request::getString($var, '', 'GET');
    }
}
$page_query = implode('&amp;', array_values($query_array));

if (Request::getInt('mark', 0, 'GET')) {
    if (1 === Request::getInt('mark', 0, 'GET')) { // marked as read
        $markvalue  = 1;
        $markresult = _MD_NEWBB_MARK_READ;
    } else { // marked as unread
        $markvalue  = 0;
        $markresult = _MD_NEWBB_MARK_UNREAD;
    }
    newbbSetReadTopic($markvalue, Request::getInt('forum', 0, 'GET'));
    $url = XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname') . '/viewforum.php?' . $page_query;
    redirect_header($url, 2, _MD_NEWBB_ALL_TOPIC_MARKED . ' ' . $markresult);
}

$forum_id = Request::getInt('forum', 0, 'GET');
$type     = Request::getInt('type', 0, 'GET');
$status   = (Request::getString('status', '', 'GET')
             && in_array(Request::getString('status', '', 'GET'), [
        'active',
        'pending',
        'deleted',
        'digest',
        'unreplied',
        'unread'
    ], true)) ? Request::getString('status', '', 'GET') : '';

$mode = (Request::getString('status', '', 'GET')
         && in_array(Request::getString('status', '', 'GET'), [
        'active',
        'pending',
        'deleted'
    ], true)) ? 2 : Request::getInt('mode', 0, 'GET');

///** @var Newbb\ForumHandler $forumHandler */
//$forumHandler = Newbb\Helper::getInstance()->getHandler('Forum');
$forumObject = $forumHandler->get($forum_id);

if (!$forumObject) {
    redirect_header(XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname') . '/index.php', 2, _MD_NEWBB_ERRORFORUM);
}

if (!$forumHandler->getPermission($forumObject)) {
    redirect_header(XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname') . '/index.php', 2, _NOPERM);
}
newbbSetRead('forum', $forum_id, $forumObject->getVar('forum_last_post_id'));

$xoops_pagetitle = $forumObject->getVar('forum_name') . ' [' . $xoopsModule->getVar('name') . ']';

$xoopsOption['template_main']   = 'newbb_viewforum.tpl';
$xoopsOption['xoops_pagetitle'] = $xoops_pagetitle;

require_once $GLOBALS['xoops']->path('header.php');
require_once __DIR__ . '/include/functions.render.php';

if (!empty($GLOBALS['xoopsModuleConfig']['rss_enable'])) {
    $xoopsTpl->assign('xoops_module_header', '
    <link rel="alternate" type="application/xml+rss" title="' . $xoopsModule->getVar('name') . '-' . $forumObject->getVar('forum_name') . '" href="' . XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname') . '/rss.php?f=' . $forum_id . '" />
    ' . @$xoopsTpl->get_template_vars('xoops_module_header'));
}
$forumDescription = $forumObject->getVar('forum_desc');
$xoopsTpl->assign('forumDescription', $forumDescription);

//$xoopsTpl->assign('xoops_module_header', $xoops_module_header);
$xoopsTpl->assign('forum_id', $forum_id);
$xoopsTpl->assign('version', $xoopsModule->getVar('version'));

$isAdmin = newbbIsAdmin($forumObject);
$xoopsTpl->assign('viewer_level', $isAdmin ? 2 : 0);
/* Only admin has access to admin mode */
if (!$isAdmin) {
    $status = (!empty($status) && in_array($status, ['active', 'pending', 'deleted'], true)) ? '' : $status;
    // irmtfan add mode
    $mode = 0;
}
// irmtfan add mode
$xoopsTpl->assign('mode', $mode);
$xoopsTpl->assign('status', $status);
if ($isAdmin) {
    $xoopsTpl->assign('forum_index_cpanel', ['link' => 'admin/index.php', 'name' => _MD_NEWBB_ADMINCP]);
}

if ($GLOBALS['xoopsModuleConfig']['wol_enabled']) {
    //    /** @var Newbb\OnlineHandler $onlineHandler */
    //    $onlineHandler = Newbb\Helper::getInstance()->getHandler('Online');
    $onlineHandler->init($forumObject);
    $xoopsTpl->assign('online', $onlineHandler->showOnline());
}

if ($forumHandler->getPermission($forumObject, 'post')) {
    $xoopsTpl->assign('viewer_level', $isAdmin ? 2 : 1);
    $xoopsTpl->assign('forum_post_or_register', '<a href="' . XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname') . "/newtopic.php?forum={$forum_id}\">" . newbbDisplayImage('t_new', _MD_NEWBB_POSTNEW) . '</a>');
    if ($pollmodules && $forumHandler->getPermission($forumObject, 'addpoll')) {
        $t_poll = newbbDisplayImage('t_poll', _MD_NEWBB_ADDPOLL);
        $xoopsTpl->assign('forum_addpoll', '<a href="' . XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname') . "/polls.php?op=add&amp;forum={$forum_id}\">{$t_poll}</a>");
    }
} else {
    $xoopsTpl->assign('viewer_level', 0);
    if (!is_object($GLOBALS['xoopsUser']) && !empty($GLOBALS['xoopsModuleConfig']['show_reg'])) {
        $redirect = preg_replace("|(.*)\/modules\/Newbb\/(.*)|", "\\1/modules/newbb/newtopic.php?forum=" . $forum_id, htmlspecialchars($xoopsRequestUri));
        $xoopsTpl->assign('forum_post_or_register', "<a href='" . XOOPS_URL . "/user.php?xoops_redirect={$redirect}'>" . _MD_NEWBB_REGTOPOST . '</a>');
        $xoopsTpl->assign('forum_addpoll', '');
    } else {
        $xoopsTpl->assign('forum_post_or_register', '');
        $xoopsTpl->assign('forum_addpoll', '');
    }
}
$parentforum = $forumHandler->getParents($forumObject);
$xoopsTpl->assign_by_ref('parentforum', $parentforum);

$criteria = new \CriteriaCompo(new \Criteria('parent_forum', $forum_id));
$criteria->add(new \Criteria('forum_id', '(' . implode(', ', $forumHandler->getIdsByPermission('access')) . ')', 'IN'));
$criteria->setSort('forum_order');

if ($forums = $forumHandler->getAll($criteria, null, false)) {
    $subforum_array = $forumHandler->display($forums, $GLOBALS['xoopsModuleConfig']['length_title_index'], $GLOBALS['xoopsModuleConfig']['count_subforum']);
    $subforum       = array_values($subforum_array[$forum_id]);
    unset($subforum_array);
    $xoopsTpl->assign_by_ref('subforum', $subforum);
}

//$categoryHandler = Newbb\Helper::getInstance()->getHandler('Category');
$categoryObject = $categoryHandler->get($forumObject->getVar('cat_id'), ['cat_title']);
$xoopsTpl->assign('category', ['id' => $forumObject->getVar('cat_id'), 'title' => $categoryObject->getVar('cat_title')]);

$xoopsTpl->assign('forum_index_title', sprintf(_MD_NEWBB_FORUMINDEX, htmlspecialchars($GLOBALS['xoopsConfig']['sitename'], ENT_QUOTES)));
$xoopsTpl->assign('forum_name', $forumObject->getVar('forum_name'));
$xoopsTpl->assign('forum_moderators', $forumObject->dispForumModerators());

// irmtfan - add and edit: u.uname => t.topic_poster | t.topic_time => t.topic_id | "t.rating"=>_MD_NEWBB_RATINGS, | p.post_time => t.topic_last_post_id
$sel_sort_array = [
    't.topic_title'        => _MD_NEWBB_TOPICTITLE,
    't.topic_poster'       => _MD_NEWBB_TOPICPOSTER,
    't.topic_id'           => _MD_NEWBB_TOPICTIME,
    't.topic_replies'      => _MD_NEWBB_NUMBERREPLIES,
    't.topic_views'        => _MD_NEWBB_VIEWS,
    't.rating'             => _MD_NEWBB_RATINGS,
    't.topic_last_post_id' => _MD_NEWBB_LASTPOSTTIME
];
if (!Request::getString('sort', '', 'GET') || !array_key_exists(Request::getString('sort', '', 'GET'), $sel_sort_array)) {
    $sort = 't.topic_last_post_id';
} else {
    $sort = Request::getString('sort', '', 'GET');
}

$forum_selection_sort = '<select name="sort">';
foreach ($sel_sort_array as $sort_k => $sort_v) {
    $forum_selection_sort .= '<option value="' . $sort_k . '"' . (($sort == $sort_k) ? ' selected="selected"' : '') . '>' . $sort_v . '</option>';
}
$forum_selection_sort .= '</select>';

$xoopsTpl->assign_by_ref('forum_selection_sort', $forum_selection_sort);

$order                 = (!Request::getString('order', '', 'GET')
                          || 'ASC' !== Request::getString('order', '', 'GET')) ? 'DESC' : 'ASC';
$forum_selection_order = '<select name="order">';
$forum_selection_order .= '<option value="ASC"' . (('ASC' === $order) ? ' selected' : '') . '>' . _MD_NEWBB_ASCENDING . '</option>';
$forum_selection_order .= '<option value="DESC"' . (('DESC' === $order) ? ' selected' : '') . '>' . _MD_NEWBB_DESCENDING . '</option>';
$forum_selection_order .= '</select>';

$xoopsTpl->assign_by_ref('forum_selection_order', $forum_selection_order);

$since = Request::getInt('since', $GLOBALS['xoopsModuleConfig']['since_default'], 'GET');
require_once __DIR__ . '/include/functions.time.php';
$forum_selection_since = newbbSinceSelectBox($since);
$xoopsTpl->assign_by_ref('forum_selection_since', $forum_selection_since);

$query_sort = $query_array;
unset($query_sort['sort'], $query_sort['order']);
$page_query_sort = implode('&amp;', array_values($query_sort));
unset($query_sort);
// irmtfan - edit: u.uname => t.topic_poster | t.topic_time => t.topic_id | p.post_time => t.topic_last_post_id
$xoopsTpl->assign('h_topic_link', XOOPS_URL . "/modules/newbb/viewforum.php?{$page_query_sort}&amp;sort=t.topic_title&amp;order=" . (('t.topic_title' === $sort && 'DESC' === $order) ? 'ASC' : 'DESC'));
$xoopsTpl->assign('h_reply_link', XOOPS_URL . "/modules/newbb/viewforum.php?{$page_query_sort}&amp;sort=t.topic_replies&amp;order=" . (('t.topic_replies' === $sort && 'DESC' === $order) ? 'ASC' : 'DESC'));
$xoopsTpl->assign('h_poster_link', XOOPS_URL . "/modules/newbb/viewforum.php?{$page_query_sort}&amp;sort=t.topic_poster&amp;order=" . (('t.topic_poster' === $sort && 'DESC' === $order) ? 'ASC' : 'DESC'));
$xoopsTpl->assign('h_views_link', XOOPS_URL . "/modules/newbb/viewforum.php?{$page_query_sort}&amp;sort=t.topic_views&amp;order=" . (('t.topic_views' === $sort && 'DESC' === $order) ? 'ASC' : 'DESC'));
$xoopsTpl->assign('h_rating_link', XOOPS_URL . "/modules/newbb/viewforum.php?{$page_query_sort}&amp;sort=t.rating&amp;order=" . (('t.rating' === $sort
                                                                                                                                  && 'DESC' === $order) ? 'ASC' : 'DESC')); // irmtfan t.topic_ratings to t.rating
$xoopsTpl->assign('h_date_link', XOOPS_URL . "/modules/newbb/viewforum.php?{$page_query_sort}&amp;sort=t.topic_last_post_id&amp;order=" . (('t.topic_last_post_id' === $sort && 'DESC' === $order) ? 'ASC' : 'DESC'));
$xoopsTpl->assign('h_publish_link', XOOPS_URL . "/modules/newbb/viewforum.php?{$page_query_sort}&amp;sort=t.topic_id&amp;order=" . (('t.topic_id' === $sort
                                                                                                                                     && 'DESC' === $order) ? 'ASC' : 'DESC'));
$xoopsTpl->assign('forum_since', $since); // For $since in search.php

// irmtfan - if no since it should be 0
$since     = Request::getInt('since', 0, 'GET');
$startdate = empty($since) ? 0 : (time() - newbbGetSinceTime($since));
$start     = Request::getInt('start', 0, 'GET');

$criteria_vars = ['startdate', 'start', 'sort', 'order', 'type', 'status', 'excerpt'];
foreach ($criteria_vars as $var) {
    $criteria_topic[$var] = @${$var};
}
$criteria_topic['excerpt'] = $GLOBALS['xoopsModuleConfig']['post_excerpt'];

list($allTopics, $sticky) = $forumHandler->getAllTopics($forumObject, $criteria_topic);

$xoopsTpl->assign_by_ref('topics', $allTopics);
$xoopsTpl->assign('sticky', $sticky);
$xoopsTpl->assign('rating_enable', $GLOBALS['xoopsModuleConfig']['rating_enabled']);
$xoopsTpl->assign('img_newposts', newbbDisplayImage('topic_new', _MD_NEWBB_NEWPOSTS));
$xoopsTpl->assign('img_hotnewposts', newbbDisplayImage('topic_hot_new', _MD_NEWBB_MORETHAN));
$xoopsTpl->assign('img_folder', newbbDisplayImage('topic', _MD_NEWBB_NONEWPOSTS));
$xoopsTpl->assign('img_hotfolder', newbbDisplayImage('topic_hot', _MD_NEWBB_MORETHAN2));
$xoopsTpl->assign('img_locked', newbbDisplayImage('topic_locked', _MD_NEWBB_TOPICLOCKED));

$xoopsTpl->assign('img_sticky', newbbDisplayImage('topic_sticky', _MD_NEWBB_TOPICSTICKY));
$xoopsTpl->assign('img_digest', newbbDisplayImage('topic_digest', _MD_NEWBB_TOPICDIGEST));
$xoopsTpl->assign('img_poll', newbbDisplayImage('poll', _MD_NEWBB_TOPICHASPOLL));

$xoopsTpl->assign('mark_read', XOOPS_URL . "/modules/newbb/viewforum.php?mark=1&amp;{$page_query}");
$xoopsTpl->assign('mark_unread', XOOPS_URL . "/modules/newbb/viewforum.php?mark=2&amp;{$page_query}");

$xoopsTpl->assign('post_link', XOOPS_URL . '/modules/newbb/viewpost.php?forum=' . $forum_id);
$xoopsTpl->assign('newpost_link', XOOPS_URL . '/modules/newbb/viewpost.php?status=new&amp;forum=' . $forum_id);

$query_type = $query_array;
unset($query_type['type']);
$page_query_type = implode('&amp;', array_values($query_type));
unset($query_type);
///** @var Newbb\TypeHandler $typeHandler */
//$typeHandler = Newbb\Helper::getInstance()->getHandler('Type');
$typeOptions = null;
$types       = [];
if ($types = $typeHandler->getByForum($forum_id)) {
    $typeOptions[] = ['title' => _ALL, 'link' => XOOPS_URL . "/modules/newbb/viewforum.php?{$page_query_type}"];
    foreach ($types as $key => $item) {
        $typeOptions[] = [
            'title' => $item['type_name'],
            'link'  => XOOPS_URL . "/modules/newbb/viewforum.php?{$page_query_type}&amp;type={$key}"
        ];
    }
}
if ($type > 0) {
    require_once __DIR__ . '/include/functions.topic.php';
    $xoopsTpl->assign('forum_topictype', getTopicTitle('', $types[$type]['type_name'], $types[$type]['type_color']));
}
$xoopsTpl->assign_by_ref('typeOptions', $typeOptions);

$query_status = $query_array;
unset($query_status['status']);
$page_query_status = implode('&amp;', array_values($query_status));
unset($query_status);
$xoopsTpl->assign('newpost_link', XOOPS_URL . '/modules/newbb/viewpost.php?status=new&amp;forum=' . $forumObject->getVar('forum_id'));
$xoopsTpl->assign('all_link', XOOPS_URL . "/modules/newbb/viewforum.php?{$page_query_status}");
$xoopsTpl->assign('digest_link', XOOPS_URL . "/modules/newbb/viewforum.php?{$page_query_status}&amp;status=digest");
$xoopsTpl->assign('unreplied_link', XOOPS_URL . "/modules/newbb/viewforum.php?{$page_query_status}&amp;status=unreplied");
$xoopsTpl->assign('unread_link', XOOPS_URL . "/modules/newbb/viewforum.php?{$page_query_status}&amp;status=unread");
switch ($status) {
    case 'digest':
        $current_status = _MD_NEWBB_DIGEST;
        break;
    case 'unreplied':
        $current_status = _MD_NEWBB_UNREPLIED;
        break;
    case 'unread':
        $current_status = _MD_NEWBB_UNREAD;
        break;
    case 'active':
        $current_status = _MD_NEWBB_TYPE_ADMIN;
        break;
    case 'pending':
        $current_status = _MD_NEWBB_TYPE_PENDING;
        break;
    case 'deleted':
        $current_status = _MD_NEWBB_TYPE_DELETED;
        break;
    default:
        $current_status = '';
        break;
}
$xoopsTpl->assign('forum_topicstatus', $current_status);

$all_topics = $forumHandler->getTopicCount($forumObject, $startdate, $status);
if ($all_topics > $GLOBALS['xoopsModuleConfig']['topics_per_page']) {
    require_once $GLOBALS['xoops']->path('class/pagenav.php');
    $query_nav = $query_array;
    unset($query_nav['start']);
    $page_query_nav = implode('&amp;', array_values($query_nav));
    unset($query_nav);
    $nav = new \XoopsPageNav($all_topics, $GLOBALS['xoopsModuleConfig']['topics_per_page'], $start, 'start', $page_query_nav);
    if ('select' === $GLOBALS['xoopsModuleConfig']['pagenav_display']) {
        $navi = $nav->renderSelect();
    } elseif ('image' === $GLOBALS['xoopsModuleConfig']['pagenav_display']) {
        $navi = $nav->renderImageNav(4);
    } else {
        $navi = $nav->renderNav(4);
    }

    $xoopsTpl->assign('forum_pagenav', $navi);
} else {
    $xoopsTpl->assign('forum_pagenav', '');
}

if (!empty($GLOBALS['xoopsModuleConfig']['show_jump'])) {
    require_once __DIR__ . '/include/functions.forum.php';
    $xoopsTpl->assign('forum_jumpbox', newbbMakeJumpbox($forum_id));
}

if ($GLOBALS['xoopsModuleConfig']['show_permissiontable']) {
    //    /** var Newbb\PermissionHandler $permHandler */
    //    $permHandler      = Newbb\Helper::getInstance()->getHandler('Permission');
    $permission_table = $permHandler->getPermissionTable($forum_id, false, $isAdmin);
    $xoopsTpl->assign_by_ref('permission_table', $permission_table);
    unset($permission_table);
}

if (1 == $GLOBALS['xoopsModuleConfig']['rss_enable']) {
    $xoopsTpl->assign('rss_button', "<div align='right'><a href='" . XOOPS_URL . '/modules/' . $xoopsModule->dirname() . '/rss.php?f=' . $forum_id . "' title='RSS feed' target='_blank'>" . newbbDisplayImage('rss', 'RSS feed') . '</a></div>');
}
// irmtfan move to footer.php
require_once __DIR__ . '/footer.php';
include $GLOBALS['xoops']->path('footer.php');
