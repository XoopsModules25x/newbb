<?php
/**
 * NewBB 4.3x, the forum module for XOOPS project
 *
 * @copyright      XOOPS Project (http://xoops.org)
 * @license        http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author         Taiwen Jiang (phppp or D.J.) <phppp@users.sourceforge.net>
 * @since          4.00
 * @package        module::newbb
 */

include_once __DIR__ . '/header.php';

/* deal with marks */
if (XoopsRequest::getInt('mark_read', 0)) { //TODO mb check if this is GET or POST?
    if (1 === XoopsRequest::getInt('mark_read', 0)) { // marked as read
        $markvalue  = 1;
        $markresult = _MD_MARK_READ;
    } else { // marked as unread
        $markvalue  = 0;
        $markresult = _MD_MARK_UNREAD;
    }
    mod_loadFunctions('read', 'newbb');
    newbb_setRead_forum($markvalue);
    $url = XOOPS_URL . '/modules/newbb/index.php';
    redirect_header($url, 2, _MD_ALL_FORUM_MARKED . ' ' . $markresult);
}

$viewcat         = XoopsRequest::getInt('cat', 0, 'GET');//TODO mb check if this is GET or POST?
$categoryHandler = xoops_getModuleHandler('category', 'newbb');

$categories = [];
if (!$viewcat) {
    $categories        = $categoryHandler->getByPermission('access', null, false);
    $forum_index_title = '';
    $xoops_pagetitle   = $xoopsModule->getVar('name');
} else {
    $category_obj = $categoryHandler->get($viewcat);
    if ($categoryHandler->getPermission($category_obj)) {
        $categories[$viewcat] = $category_obj->getValues();
    }
    $forum_index_title = sprintf(_MD_FORUMINDEX, htmlspecialchars($GLOBALS['xoopsConfig']['sitename'], ENT_QUOTES));
    $xoops_pagetitle   = $category_obj->getVar('cat_title') . ' [' . $xoopsModule->getVar('name') . ']';
}

if (count($categories) === 0) {
    redirect_header(XOOPS_URL, 2, _MD_NORIGHTTOACCESS);
}

$xoopsOption['template_main']   = 'newbb_index.tpl';
$xoopsOption['xoops_pagetitle'] = $xoops_pagetitle;
// irmtfan remove and move to footer.php
//$xoopsOption['xoops_module_header'] = $xoops_module_header;
// irmtfan include header.php after defining $xoopsOption['template_main']
include_once $GLOBALS['xoops']->path('header.php');
mod_loadFunctions('render', 'newbb');
/* rss feed */
// irmtfan new method
if (!empty($GLOBALS['xoopsModuleConfig']['rss_enable'])) {
    $xoopsTpl->assign('xoops_module_header', '
    <link rel="alternate" type="application/xml+rss" title="' . $xoopsModule->getVar('name') . '" href="' . XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname', 'n') . '/rss.php" />
    ' . @$xoopsTpl->get_template_vars('xoops_module_header'));
}
$xoopsTpl->assign('xoops_pagetitle', $xoops_pagetitle);
// irmtfan remove and move to footer.php
//$xoopsTpl->assign('xoops_module_header', $xoops_module_header);
$xoopsTpl->assign('forum_index_title', $forum_index_title);
//if ($GLOBALS['xoopsModuleConfig']['wol_enabled']) {
if (!empty($GLOBALS['xoopsModuleConfig']['wol_enabled'])) {
    $onlineHandler = xoops_getModuleHandler('online', 'newbb');
    $onlineHandler->init();
    $xoopsTpl->assign('online', $onlineHandler->show_online());
}
$forumHandler = xoops_getModuleHandler('forum', 'newbb');
$postHandler  = xoops_getModuleHandler('post', 'newbb');

/* Allowed forums */
$forums_allowed = $forumHandler->getIdsByPermission();

/* fetch top forums */
$forums_top = [];

if (!empty($forums_allowed)) {
    $crit_top = new CriteriaCompo(new Criteria('parent_forum', 0));
    $crit_top->add(new Criteria('cat_id', '(' . implode(', ', array_keys($categories)) . ')', 'IN'));
    $crit_top->add(new Criteria('forum_id', '(' . implode(', ', $forums_allowed) . ')', 'IN'));
    $forums_top = $forumHandler->getIds($crit_top);
}

/* fetch subforums if required to display */
if ('hidden' === $GLOBALS['xoopsModuleConfig']['subforum_display'] || 0 === count($forums_top)) {
    $forums_sub = [];
} else {
    $crit_sub = new CriteriaCompo(new Criteria('parent_forum', '(' . implode(', ', $forums_top) . ')', 'IN'));
    $crit_sub->add(new Criteria('forum_id', '(' . implode(', ', $forums_allowed) . ')', 'IN'));
    $forums_sub = $forumHandler->getIds($crit_sub);
}

/* Fetch forum data */
$forums_available = array_merge($forums_top, $forums_sub);
$forums_array     = [];
$newtopics        = 0;
$deletetopics     = 0;
$newposts         = 0;
$deleteposts      = 0;
if (0 !== count($forums_available)) {
    $crit_forum = new Criteria('forum_id', '(' . implode(', ', $forums_available) . ')', 'IN');
    $crit_forum->setSort('cat_id ASC, parent_forum ASC, forum_order');
    $crit_forum->setOrder('ASC');
    $forums       = $forumHandler->getAll($crit_forum, null, false);
    $newtopics    = $forumHandler->getTopicCount($forums, 0, 'pending');
    $deletetopics = $forumHandler->getTopicCount($forums, 0, 'deleted');
    $forums_array = $forumHandler->display($forums, $GLOBALS['xoopsModuleConfig']['length_title_index'], $GLOBALS['xoopsModuleConfig']['count_subforum']);
    $crit         = new CriteriaCompo(new Criteria('forum_id', '(' . implode(', ', $forums_available) . ')', 'IN'));
    $crit->add(new Criteria('approved', '-1'));
    $deleteposts = $postHandler->getCount($crit);
    $crit        = new CriteriaCompo(new Criteria('forum_id', '(' . implode(', ', $forums_available) . ')', 'IN'));
    $crit->add(new Criteria('approved', '0'));
    $newposts = $postHandler->getCount($crit);
}

if ($newtopics > 0) {
    $xoopsTpl->assign('wait_new_topic', $newtopics);
}
if ($deletetopics > 0) {
    $xoopsTpl->assign('delete_topic', $deletetopics);
}
if ($newposts > 0) {
    $xoopsTpl->assign('wait_new_post', $newposts);
}
if ($deleteposts > 0) {
    $xoopsTpl->assign('delete_post', $deleteposts);
}

$reportHandler = xoops_getModuleHandler('report', 'newbb');
$reported      = $reportHandler->getCount(new Criteria('report_result', 0));
if ($reported > 0) {
    $xoopsTpl->assign('report_post', sprintf(_MD_NEWBB_SEEWAITREPORT, $reported));
}

if (count($forums_array) > 0) {
    foreach ($forums_array[0] as $parent => $forum) {
        if (isset($forums_array[$forum['forum_id']])) {
            $forum['subforum'] = $forums_array[$forum['forum_id']];
        }
        $forumsByCat[$forum['forum_cid']][] = $forum;
    }
}

$category_array = [];
$toggles        = newbb_getcookie('G', true);
$iconHandler    = newbbGetIconHandler();
$category_icon  = [
    'expand'   => $iconHandler->getImageSource('minus'),
    'collapse' => $iconHandler->getImageSource('plus')
];

foreach (array_keys($categories) as $id) {
    $forums = [];
    $onecat = $categories[$id];

    $cat_element_id = 'cat_' . $onecat['cat_id'];
    $expand         = (count($toggles) > 0) ? (in_array($cat_element_id, $toggles) ? false : true) : true;
    // START irmtfan to improve newbbDisplayImage
    if ($expand) {
        $cat_display      = 'block';        //irmtfan move semicolon
        $cat_icon_display = 'minus';
        $cat_alt          = _MD_NEWBB_HIDE;
    } else {
        $cat_display      = 'none';        //irmtfan move semicolon
        $cat_icon_display = 'plus';
        $cat_alt          = _MD_NEWBB_SEE;
    }
    $cat_displayImage = newbbDisplayImage($cat_icon_display, $cat_alt);

    if (isset($forumsByCat[$onecat['cat_id']])) {
        $forums = $forumsByCat[$onecat['cat_id']];
    }

    $cat_sponsor = [];
    @list($url, $title) = array_map('trim', preg_split('/ /', $onecat['cat_url'], 2));
    if ('' === $title) {
        $title = $url;
    }
    $title = $myts->htmlSpecialChars($title);
    if ('' !== $url) {
        $cat_sponsor = ['title' => $title, 'link' => formatURL($url)];
    }
    $cat_image = $onecat['cat_image'];
    $cat_image = '';
    if ('' !== $cat_image && 'blank.gif' !== $cat_image && 0 !== (int)$cat_image) {
        $cat_image = XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname', 'n') . '/assets/images/category/' . $cat_image;
    }
    $category_array[] = [
        'cat_id'           => $onecat['cat_id'],
        'cat_title'        => $myts->displayTarea($onecat['cat_title'], 1),
        'cat_image'        => $cat_image,
        'cat_sponsor'      => $cat_sponsor,
        'cat_description'  => $myts->displayTarea($onecat['cat_description'], 1),
        'cat_element_id'   => $cat_element_id,
        'cat_display'      => $cat_display,
        'cat_displayImage' => $cat_displayImage,
        'forums'           => $forums
    ];
}

unset($categories, $forums_array, $forumsByCat);
$xoopsTpl->assign_by_ref('category_icon', $category_icon);
$xoopsTpl->assign_by_ref('categories', $category_array);
$xoopsTpl->assign('notifyicon', $category_icon);

$xoopsTpl->assign([
                      'index_title' => sprintf(_MD_WELCOME, htmlspecialchars($GLOBALS['xoopsConfig']['sitename'], ENT_QUOTES)),
                      'index_desc'  => _MD_TOSTART
                  ]);

/* display user stats */
if (!empty($GLOBALS['xoopsModuleConfig']['statistik_enabled'])) {
    $userstats = [];
    if (is_object($GLOBALS['xoopsUser'])) {
        $userstatsHandler         = xoops_getModuleHandler('userstats');
        $userstats_row            = $userstatsHandler->getStats($GLOBALS['xoopsUser']->getVar('uid'));
        $userstats['topics']      = sprintf(_MD_USER_TOPICS, (int)(@$userstats_row['user_topics']));
        $userstats['posts']       = sprintf(_MD_USER_POSTS, (int)(@$userstats_row['user_posts']));
        $userstats['digests']     = sprintf(_MD_USER_DIGESTS, (int)(@$userstats_row['user_digests']));
        $userstats['currenttime'] = sprintf(_MD_TIMENOW, formatTimestamp(time(), 's')); // irmtfan should be removed because it is for anon users too
        $userstats['lastvisit']   = sprintf(_MD_USER_LASTVISIT, formatTimestamp($last_visit, 's')); // irmtfan should be removed because it is for anon users too
        $userstats['lastpost']    = empty($userstats_row['user_lastpost']) ? _MD_USER_NOLASTPOST : sprintf(_MD_USER_LASTPOST, formatTimestamp($userstats_row['user_lastpost'], 's'));
    }
    $xoopsTpl->assign_by_ref('userstats', $userstats);
    // irmtfan add lastvisit smarty variable for all users
    $xoopsTpl->assign('lastvisit', sprintf(_MD_USER_LASTVISIT, formatTimestamp($last_visit, 'l')));
    $xoopsTpl->assign('currenttime', sprintf(_MD_TIMENOW, formatTimestamp(time(), 'm')));
}

/* display forum stats */
$statsHandler = xoops_getModuleHandler('stats');
$stats        = $statsHandler->getStats(array_merge([0], $forums_available));
$xoopsTpl->assign_by_ref('stats', $stats);
$xoopsTpl->assign('subforum_display', $GLOBALS['xoopsModuleConfig']['subforum_display']);
$xoopsTpl->assign('mark_read', XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname', 'n') . '/index.php?mark_read=1');
$xoopsTpl->assign('mark_unread', XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname', 'n') . '/index.php?mark_read=2');

$xoopsTpl->assign('all_link', XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname', 'n') . '/list.topic.php?status=all');
$xoopsTpl->assign('post_link', XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname', 'n') . '/viewpost.php?status=all');
$xoopsTpl->assign('newpost_link', XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname', 'n') . '/viewpost.php?status=new');
$xoopsTpl->assign('digest_link', XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname', 'n') . '/list.topic.php?status=digest');
$xoopsTpl->assign('unreplied_link', XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname', 'n') . '/list.topic.php?status=unreplied');
$xoopsTpl->assign('unread_link', XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname', 'n') . '/list.topic.php?status=unread');
$xoopsTpl->assign('menumode', $menumode);
$xoopsTpl->assign('menumode_other', $menumode_other);

$isadmin = $GLOBALS['xoopsUserIsAdmin'];
$xoopsTpl->assign('viewer_level', $isadmin ? 2 : is_object($GLOBALS['xoopsUser']));
$mode = XoopsRequest::getInt('mode', 0, 'GET');
$xoopsTpl->assign('mode', $mode);

$xoopsTpl->assign('viewcat', $viewcat);
$xoopsTpl->assign('version', $xoopsModule->getVar('version'));

/* To be removed */
if ($isadmin) {
    $xoopsTpl->assign('forum_index_cpanel', ['link' => 'admin/index.php', 'name' => _MD_ADMINCP]);
}

if ($GLOBALS['xoopsModuleConfig']['rss_enable'] == 1) {
    $xoopsTpl->assign('rss_enable', 1);
    $xoopsTpl->assign('rss_button', newbbDisplayImage('rss', 'RSS feed'));
}
$xoopsTpl->assign([
                      'img_forum_new' => newbbDisplayImage('forum_new', _MD_NEWPOSTS),
                      'img_forum'     => newbbDisplayImage('forum', _MD_NONEWPOSTS),
                      'img_subforum'  => newbbDisplayImage('subforum')
                  ]);

// irmtfan move to footer.php
include_once __DIR__ . '/footer.php';
include $GLOBALS['xoops']->path('footer.php');
