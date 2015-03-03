<?php
/**
 * CBB 4.0, or newbb, the forum module for XOOPS project
 *
 * @copyright	The XOOPS Project http://xoops.sf.net
 * @license		http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author		Taiwen Jiang (phppp or D.J.) <phppp@users.sourceforge.net>
 * @since		4.00
 * @version		$Id: index.php 12504 2014-04-26 01:01:06Z beckmi $
 * @package		module::newbb
 */

include_once __DIR__ . "/header.php";

/* deal with marks */
if (isset($_GET['mark_read'])) {
    if (1 == intval($_GET['mark_read'])) { // marked as read
        $markvalue = 1;
        $markresult = _MD_MARK_READ;
    } else { // marked as unread
        $markvalue = 0;
        $markresult = _MD_MARK_UNREAD;
    }
    mod_loadFunctions("read", "newbb");
    newbb_setRead_forum($markvalue);
    $url = XOOPS_URL . '/modules/newbb/index.php';
    redirect_header($url, 2, _MD_ALL_FORUM_MARKED.' '.$markresult);
}

$viewcat = @intval($_GET['cat']);
$category_handler = xoops_getmodulehandler('category', 'newbb');

$categories = array();
if (!$viewcat) {
    $categories = $category_handler->getByPermission('access', null, false);
    $forum_index_title = "";
    $xoops_pagetitle = $xoopsModule->getVar('name');
} else {
    $category_obj = $category_handler->get($viewcat);
    if ($category_handler->getPermission($category_obj)) {
        $categories[$viewcat] = $category_obj->getValues();
    }
    $forum_index_title = sprintf(_MD_FORUMINDEX, htmlspecialchars($xoopsConfig['sitename'], ENT_QUOTES));
    $xoops_pagetitle = $category_obj->getVar('cat_title') . " [" .$xoopsModule->getVar('name')."]";
}

if (count($categories) == 0) {
    redirect_header(XOOPS_URL, 2, _MD_NORIGHTTOACCESS);
}

$xoopsOption['template_main'] = 'newbb_index.tpl';
$xoopsOption['xoops_pagetitle'] = $xoops_pagetitle;
// irmtfan remove and move to footer.php
//$xoopsOption['xoops_module_header'] = $xoops_module_header;
// irmtfan include header.php after defining $xoopsOption['template_main']
include_once $GLOBALS['xoops']->path('header.php');
mod_loadFunctions("render", "newbb");
/* rss feed */
// irmtfan new method
if (!empty($xoopsModuleConfig['rss_enable'])) {
    $xoopsTpl->assign("xoops_module_header",'
    <link rel="alternate" type="application/xml+rss" title="'.$xoopsModule->getVar('name').'" href="'.XOOPS_URL.'/modules/'.$xoopsModule->getVar('dirname', 'n').'/rss.php" />
    '. @$xoopsTpl->get_template_vars("xoops_module_header"));
}
$xoopsTpl->assign('xoops_pagetitle', $xoops_pagetitle);
// irmtfan remove and move to footer.php
//$xoopsTpl->assign('xoops_module_header', $xoops_module_header);
$xoopsTpl->assign('forum_index_title', $forum_index_title);
//if ($xoopsModuleConfig['wol_enabled']) {
if (!empty($xoopsModuleConfig['wol_enabled'])) {
    $online_handler = xoops_getmodulehandler('online', 'newbb');
    $online_handler->init();
    $xoopsTpl->assign('online', $online_handler->show_online());
}
$forum_handler = xoops_getmodulehandler('forum', 'newbb');
$post_handler = xoops_getmodulehandler('post', 'newbb');

/* Allowed forums */
$forums_allowed = $forum_handler->getIdsByPermission();

/* fetch top forums */
$forums_top = array();

if (!empty($forums_allowed)) {
    $crit_top = new CriteriaCompo(new Criteria("parent_forum", 0));
    $crit_top->add(new Criteria("cat_id", "(".implode(", ", array_keys($categories)).")", "IN"));
    $crit_top->add(new Criteria("forum_id", "(".implode(", ", $forums_allowed).")", "IN"));
    $forums_top = $forum_handler->getIds($crit_top);
}

/* fetch subforums if required to display */
if (empty($forums_top) || $xoopsModuleConfig['subforum_display'] == "hidden") {
    $forums_sub = array();
} else {
    $crit_sub = new CriteriaCompo(new Criteria("parent_forum", "(".implode(", ", $forums_top).")", "IN"));
    $crit_sub->add(new Criteria("forum_id", "(".implode(", ", $forums_allowed).")", "IN"));
    $forums_sub = $forum_handler->getIds($crit_sub);
}

/* Fetch forum data */
$forums_available = array_merge($forums_top, $forums_sub);
$forums_array = array();
$newtopics        = 0;
$deletetopics    = 0;
$newposts        = 0;
$deleteposts    = 0;
if (!empty($forums_available)) {
    $crit_forum = new Criteria("forum_id", "(".implode(", ", $forums_available).")", "IN");
    $crit_forum->setSort("cat_id ASC, parent_forum ASC, forum_order");
    $crit_forum->setOrder("ASC");
    $forums = $forum_handler->getAll($crit_forum, null, false);
    $newtopics = $forum_handler->getTopicCount($forums, 0, "pending");
    $deletetopics = $forum_handler->getTopicCount($forums, 0, "deleted");
    $forums_array = $forum_handler->display($forums, $xoopsModuleConfig["length_title_index"], $xoopsModuleConfig["count_subforum"]);
    $crit = new CriteriaCompo(new Criteria("forum_id", "(".implode(", ", $forums_available).")", "IN"));
    $crit->add(new Criteria('approved','-1'));
    $deleteposts = $post_handler->getCount($crit);
    $crit = new CriteriaCompo(new Criteria("forum_id", "(".implode(", ", $forums_available).")", "IN"));
    $crit->add(new Criteria('approved','0'));
    $newposts = $post_handler->getCount($crit);
}

if ($newtopics        > 0) $xoopsTpl->assign('wait_new_topic',$newtopics);
if ($deletetopics    > 0) $xoopsTpl->assign('delete_topic',$deletetopics);
if ($newposts        > 0) $xoopsTpl->assign('wait_new_post',$newposts);
if ($deleteposts    > 0) $xoopsTpl->assign('delete_post',$deleteposts);

$report_handler = xoops_getmodulehandler('report', 'newbb');
$reported = $report_handler->getCount(new Criteria("report_result", 0));
if ($reported > 0) $xoopsTpl->assign('report_post',sprintf(_MD_NEWBB_SEEWAITREPORT,$reported));

if (count($forums_array)>0) {
    foreach ($forums_array[0] as $parent => $forum) {
        if (isset($forums_array[$forum['forum_id']])) {
            $forum['subforum'] = $forums_array[$forum['forum_id']];
        }
        $forumsByCat[$forum['forum_cid']][] = $forum;
    }
}

$category_array = array();
$toggles = newbb_getcookie('G', true);
$icon_handler = newbb_getIconHandler();
$category_icon = array(
    "expand"    => $icon_handler->getImageSource("minus"),
    "collapse"    => $icon_handler->getImageSource("plus"))
    ;

foreach (array_keys($categories) as $id) {
    $forums = array();
    $onecat = $categories[$id];

    $cat_element_id = "cat_".$onecat['cat_id'];
    $expand = (count($toggles) > 0) ? ( (in_array($cat_element_id, $toggles)) ? false : true ) : true;
    // START irmtfan to improve newbb_displayImage
    if ($expand) {
        $cat_display = 'block';        //irmtfan move semicolon
        $cat_icon_display  = "minus";
        $cat_alt = _MD_NEWBB_HIDE;
    } else {
        $cat_display = 'none';        //irmtfan move semicolon
        $cat_icon_display  = "plus";
        $cat_alt = _MD_NEWBB_SEE;
    }
    $cat_displayImage = newbb_displayImage($cat_icon_display, $cat_alt);

    if (isset($forumsByCat[$onecat['cat_id']])) {
        $forums = $forumsByCat[$onecat['cat_id']];
    }

    $cat_sponsor = array();
    @list($url, $title) = array_map("trim", preg_split("/ /", $onecat['cat_url'], 2));
    if (empty($title)) $title = $url;
    $title = $myts->htmlSpecialChars($title);
    if (!empty($url)) $cat_sponsor = array("title" => $title, "link" => formatURL($url));
    $cat_image = $onecat['cat_image'];
    if ( !empty($cat_image) && $cat_image != "blank.gif") {
        $cat_image = XOOPS_URL."/modules/" . $xoopsModule->getVar("dirname", "n") . "/assets/images/category/" . $cat_image;
    } else {
        $cat_image = "";
    }
    $category_array[] = array(
        'cat_id'            => $onecat['cat_id'],
        'cat_title'            => $myts->displayTarea($onecat['cat_title'],1),
        'cat_image'            => $cat_image,
        'cat_sponsor'        => $cat_sponsor,
        'cat_description'    => $myts->displayTarea($onecat['cat_description'],1),
        'cat_element_id'    => $cat_element_id,
        'cat_display'        => $cat_display,
        'cat_displayImage'    => $cat_displayImage,
        'forums'            => $forums
        );
}

unset($categories, $forums_array, $forumsByCat);
$xoopsTpl->assign_by_ref("category_icon", $category_icon);
$xoopsTpl->assign_by_ref("categories", $category_array);
$xoopsTpl->assign("notifyicon", $category_icon);

$xoopsTpl->assign(array(
    "index_title"        => sprintf(_MD_WELCOME, htmlspecialchars($xoopsConfig['sitename'], ENT_QUOTES)),
    "index_desc"        => _MD_TOSTART,
    ));

/* display user stats */
if (!empty($xoopsModuleConfig['statistik_enabled'])) {
    $userstats = array();
    if (is_object($xoopsUser)) {
        $userstats_handler =& xoops_getmodulehandler('userstats');
        $userstats_row                = $userstats_handler->getStats($xoopsUser->getVar("uid"));
        $userstats["topics"]        = sprintf(_MD_USER_TOPICS, intval( @$userstats_row["user_topics"] ));
        $userstats["posts"]        = sprintf(_MD_USER_POSTS, intval( @$userstats_row["user_posts"] ));
        $userstats["digests"]        = sprintf(_MD_USER_DIGESTS, intval( @$userstats_row["user_digests"] ));
        $userstats["currenttime"]    = sprintf(_MD_TIMENOW, formatTimestamp(time(), "s")); // irmtfan should be removed because it is for anon users too
        $userstats["lastvisit"]    = sprintf(_MD_USER_LASTVISIT, formatTimestamp($last_visit, "s")); // irmtfan should be removed because it is for anon users too
        $userstats["lastpost"]        = empty($userstats_row["user_lastpost"]) ? _MD_USER_NOLASTPOST : sprintf(_MD_USER_LASTPOST, formatTimestamp($userstats_row["user_lastpost"], "s"));
    }
    $xoopsTpl->assign_by_ref("userstats", $userstats);
    // irmtfan add lastvisit smarty variable for all users
    $xoopsTpl->assign('lastvisit', sprintf(_MD_USER_LASTVISIT, formatTimestamp($last_visit, "l")));
    $xoopsTpl->assign('currenttime', sprintf(_MD_TIMENOW, formatTimestamp(time(), "m")) );
}

/* display forum stats */
$stats_handler = xoops_getmodulehandler('stats');
$stats = $stats_handler->getStats(array_merge(array(0), $forums_available));
$xoopsTpl->assign_by_ref("stats", $stats);
$xoopsTpl->assign("subforum_display", $xoopsModuleConfig['subforum_display']);
$xoopsTpl->assign('mark_read', XOOPS_URL."/modules/" . $xoopsModule->getVar("dirname", "n") . "/index.php?mark_read=1");
$xoopsTpl->assign('mark_unread', XOOPS_URL."/modules/" . $xoopsModule->getVar("dirname", "n") . "/index.php?mark_read=2");

$xoopsTpl->assign('all_link', XOOPS_URL."/modules/" . $xoopsModule->getVar("dirname", "n") ."/list.topic.php?status=all");
$xoopsTpl->assign('post_link', XOOPS_URL."/modules/" . $xoopsModule->getVar("dirname", "n") ."/viewpost.php?status=all");
$xoopsTpl->assign('newpost_link', XOOPS_URL."/modules/" . $xoopsModule->getVar("dirname", "n") ."/viewpost.php?status=new");
$xoopsTpl->assign('digest_link', XOOPS_URL."/modules/" . $xoopsModule->getVar("dirname", "n") ."/list.topic.php?status=digest");
$xoopsTpl->assign('unreplied_link', XOOPS_URL."/modules/" . $xoopsModule->getVar("dirname", "n") ."/list.topic.php?status=unreplied");
$xoopsTpl->assign('unread_link', XOOPS_URL."/modules/" . $xoopsModule->getVar("dirname", "n") ."/list.topic.php?status=unread");
$xoopsTpl->assign('menumode', $menumode);
$xoopsTpl->assign('menumode_other', $menumode_other);

$isadmin = $GLOBALS["xoopsUserIsAdmin"];
$xoopsTpl->assign('viewer_level',  ($isadmin) ? 2 : is_object($xoopsUser));
$mode = (!empty($_GET['mode'])) ? intval($_GET['mode']) : 0;
$xoopsTpl->assign('mode', $mode );

$xoopsTpl->assign('viewcat', $viewcat);
$xoopsTpl->assign('version', $xoopsModule->getVar("version"));

/* To be removed */
if ($isadmin) {
    $xoopsTpl->assign('forum_index_cpanel',array("link" => "admin/index.php", "name" => _MD_ADMINCP));
}

if ($xoopsModuleConfig['rss_enable'] == 1) {
    $xoopsTpl->assign("rss_enable", 1);
    $xoopsTpl->assign("rss_button", newbb_displayImage('rss', 'RSS feed'));
}
$xoopsTpl->assign(array(
    "img_forum_new" => newbb_displayImage('forum_new', _MD_NEWPOSTS),
    "img_forum" => newbb_displayImage('forum', _MD_NONEWPOSTS),
    'img_subforum' => newbb_displayImage('subforum')));

// irmtfan move to footer.php
include_once __DIR__ . "/footer.php";
include $GLOBALS['xoops']->path('footer.php');
