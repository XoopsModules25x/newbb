<?php
// $Id: newbb_block.php 62 2012-08-17 10:15:26Z alfred $
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
// defined('XOOPS_ROOT_PATH') || exit('XOOPS root path not defined');
// irmtfan use full path because block maybe used outside newbb
include_once $GLOBALS['xoops']->path('modules/newbb/include/functions.ini.php');

if (defined('NEWBB_BLOCK_DEFINED')) return;
define('NEWBB_BLOCK_DEFINED', true);

function b_newbb_array_filter($var)
{
    return $var > 0;
}

// options[0] - Citeria valid: time(by default)
// options[1] - NumberToDisplay: any positive integer
// options[2] - TimeDuration: negative for hours, positive for days, for instance, -5 for 5 hours and 5 for 5 days
// options[3] - DisplayMode: 0-full view;1-compact view;2-lite view
// options[4] - Display Navigator: 1 (by default), 0 (No)
// options[5] - Title Length : 0 - no limit
// options[6] - SelectedForumIDs: null for all

function b_newbb_show($options)
{
    global $xoopsConfig;
    global $access_forums;
    global $xoopsLogger;

    mod_loadFunctions("time", "newbb");

    $myts           =& MyTextSanitizer::getInstance();
    $block          = array();
    $i              = 0;
    $order          = "";
    $extra_criteria = "";
    if (!empty($options[2])) {
        mod_loadFunctions("time", "newbb");
        $extra_criteria .= " AND p.post_time>" . (time() - newbb_getSinceTime($options[2]));
    }
    switch ($options[0]) {
        case 'time':
        default:
            $order = 't.topic_last_post_id';
            break;
    }

    if (!isset($access_forums)) {
        $perm_handler =& xoops_getmodulehandler('permission', 'newbb');
        if (!$access_forums = $perm_handler->getForums()) {
            return $block;
        }
    }
    if (!empty($options[6])) {
        $allowedforums  = array_filter(array_slice($options, 6), "b_newbb_array_filter"); // get allowed forums
        $allowed_forums = array_intersect($allowedforums, $access_forums);
    } else {
        $allowed_forums = $access_forums;
    }
    if (empty($allowed_forums)) return $block;

    $forum_criteria   = ' AND t.forum_id IN (' . implode(',', $allowed_forums) . ')';
    $approve_criteria = ' AND t.approved = 1';

    $newbbConfig = newbb_load_config();
    if (!empty($newbbConfig['do_rewrite'])) {
        include_once $GLOBALS['xoops']->path('modules/newbb/seo_url.php');
    } else {
        if (!defined('SEO_MODULE_NAME')) define('SEO_MODULE_NAME', 'modules/newbb');
    }

    $query = 'SELECT' .
             '	t.topic_id, t.topic_replies, t.forum_id, t.topic_title, t.topic_views, t.type_id,' .
             '	f.forum_name,t.topic_status,' .
             '	p.post_id, p.post_time, p.icon, p.uid, p.poster_name' .
             '	FROM ' . $GLOBALS["xoopsDB"]->prefix('bb_topics') . ' AS t ' .
             '	LEFT JOIN ' . $GLOBALS["xoopsDB"]->prefix('bb_posts') . ' AS p ON t.topic_last_post_id=p.post_id' .
             '	LEFT JOIN ' . $GLOBALS["xoopsDB"]->prefix('bb_forums') . ' AS f ON f.forum_id=t.forum_id' .
             '	WHERE 1=1 ' .
             $forum_criteria .
             $approve_criteria .
             $extra_criteria .
             ' ORDER BY ' . $order . ' DESC';

    $result = $GLOBALS["xoopsDB"]->query($query, $options[1], 0);

    if (!$result) {
        //xoops_error($GLOBALS["xoopsDB"]->error());
        return false;
    }
    $block['disp_mode'] = $options[3]; // 0 - full view; 1 - compact view; 2 - lite view;
    $rows               = array();
    $author             = array();
    $types              = array();

    while ($row = $GLOBALS["xoopsDB"]->fetchArray($result)) {
        $rows[]              = $row;
        $author[$row["uid"]] = 1;
        if ($row['type_id'] > 0) {
            $types[$row['type_id']] = 1;
        }
    }

    if (count($rows) < 1) return $block;

    mod_loadFunctions("user", "newbb");
    $author_name = newbb_getUnameFromIds(array_keys($author), $newbbConfig['show_realname'], true);

    if (count($types) > 0) {
        $type_handler =& xoops_getmodulehandler('type', 'newbb');
        $type_list    = $type_handler->getList(new Criteria("type_id", "(" . implode(", ", array_keys($types)) . ")", "IN"));
    }

    foreach ($rows as $arr) {
        // irmtfan add lastposticon - load main lang
        xoops_loadLanguage("main", "newbb");
        $topic_page_jump        = newbb_displayImage('lastposticon', _MD_NEWBB_GOTOLASTPOST);
        $topic['topic_subject'] = empty($type_list[$arr["type_id"]]) ? "" : "[" . $type_list[$arr["type_id"]] . "]";

        $topic['post_id']      = $arr['post_id'];
        $topic['topic_status'] = $arr['topic_status'];
        $topic['forum_id']     = $arr['forum_id'];
        $topic['forum_name']   = $myts->htmlSpecialChars($arr['forum_name']);
        $topic['id']           = $arr['topic_id'];

        $title = $myts->htmlSpecialChars($arr['topic_title']);
        if (!empty($options[5])) {
            $title = xoops_substr($title, 0, $options[5]);
        }
        $topic['title']   = $topic['topic_subject'] . ' ' . $title;
        $topic['replies'] = $arr['topic_replies'];
        $topic['views']   = $arr['topic_views'];
        $topic['time']    = newbb_formatTimestamp($arr['post_time']);
        if (!empty($author_name[$arr['uid']])) {
            $topic_poster = $author_name[$arr['uid']];
        } else {
            $topic_poster = $myts->htmlSpecialChars(($arr['poster_name']) ? $arr['poster_name'] : $GLOBALS["xoopsConfig"]["anonymous"]);
        }
        $topic['topic_poster']    = $topic_poster;
        $topic['topic_page_jump'] = $topic_page_jump;
        // START irmtfan remove hardcoded html in URLs - add $seo_topic_url
        $seo_url       = XOOPS_URL . '/' . SEO_MODULE_NAME . '/viewtopic.php?post_id=' . $topic['post_id'];
        $seo_topic_url = XOOPS_URL . '/' . SEO_MODULE_NAME . '/viewtopic.php?topic_id=' . $topic['id'];
        $seo_forum_url = XOOPS_URL . '/' . SEO_MODULE_NAME . '/viewforum.php?forum=' . $topic['forum_id'];
        if (!empty($newbbConfig['do_rewrite'])) {
            $topic['seo_url']       = seo_urls($seo_url);
            $topic['seo_topic_url'] = seo_urls($seo_topic_url);
            $topic['seo_forum_url'] = seo_urls($seo_forum_url);
        } else {
            $topic['seo_url']       = $seo_url;
            $topic['seo_topic_url'] = $seo_topic_url;
            $topic['seo_forum_url'] = $seo_forum_url;
        }
        // END irmtfan remove hardcoded html in URLs - add $seo_topic_url
        $block['topics'][] = $topic;
        unset($topic);
    }
    // START irmtfan remove hardcoded html in URLs
    $seo_top_allforums          = XOOPS_URL . '/' . SEO_MODULE_NAME;
    $block['seo_top_allforums'] = (!empty($newbbConfig['do_rewrite'])) ? seo_urls($seo_top_allforums) : $seo_top_allforums;
    $seo_top_allforums          = XOOPS_URL . '/' . SEO_MODULE_NAME . '/list.topic.php';
    $block['seo_top_alltopics'] = (!empty($newbbConfig['do_rewrite'])) ? seo_urls($seo_top_allforums) : $seo_top_allforums;
    $seo_top_allforums          = XOOPS_URL . '/' . SEO_MODULE_NAME . '/viewpost.php';
    $block['seo_top_allposts']  = (!empty($newbbConfig['do_rewrite'])) ? seo_urls($seo_top_allforums) : $seo_top_allforums;
    // END irmtfan remove hardcoded html in URLs
    $block['indexNav'] = intval($options[4]);

    return $block;
}

// options[0] - Citeria valid: time(by default), views, replies, digest, sticky
// options[1] - NumberToDisplay: any positive integer
// options[2] - TimeDuration: negative for hours, positive for days, for instance, -5 for 5 hours and 5 for 5 days
// options[3] - DisplayMode: 0-full view;1-compact view;2-lite view
// options[4] - Display Navigator: 1 (by default), 0 (No)
// options[5] - Title Length : 0 - no limit
// options[6] - SelectedForumIDs: null for all

function b_newbb_topic_show($options)
{
    global $xoopsConfig;
    global $access_forums;
    mod_loadFunctions("time", "newbb");
    $myts           = MyTextSanitizer::getInstance();
    $block          = array();
    $i              = 0;
    $order          = "";
    $extra_criteria = "";
    $time_criteria  = null;
    if (!empty($options[2])) {
        $time_criteria  = time() - newbb_getSinceTime($options[2]);
        $extra_criteria = " AND t.topic_time>" . $time_criteria;
    }
    switch ($options[0]) {
        case 'views':
            $order = 't.topic_views';
            break;
        case 'replies':
            $order = 't.topic_replies';
            break;
        case 'digest':
            $order          = 't.digest_time';
            $extra_criteria = " AND t.topic_digest=1";
            if ($time_criteria) {
                $extra_criteria .= " AND t.digest_time>" . $time_criteria;
            }
            break;
        case 'sticky':
            $order = 't.topic_id';
            $extra_criteria .= " AND t.topic_sticky=1";
            break;
        case 'time':
        default:
            $order = 't.topic_id';
            break;
    }

    $newbbConfig = newbb_load_config();
    if (!empty($newbbConfig['do_rewrite'])) {
        include_once $GLOBALS['xoops']->path('modules/newbb/seo_url.php');
    } else {
        if (!defined('SEO_MODULE_NAME')) define('SEO_MODULE_NAME', 'modules/newbb');
    }

    if (!isset($access_forums)) {
        $perm_handler =& xoops_getmodulehandler('permission', 'newbb');
        if (!$access_forums = $perm_handler->getForums()) {
            return $block;
        }
    }

    if (!empty($options[6])) {
        $allowedforums  = array_filter(array_slice($options, 6), "b_newbb_array_filter"); // get allowed forums
        $allowed_forums = array_intersect($allowedforums, $access_forums);
    } else {
        $allowed_forums = $access_forums;
    }
    if (empty($allowed_forums)) return false;

    $forum_criteria   = ' AND t.forum_id IN (' . implode(',', $allowed_forums) . ')';
    $approve_criteria = ' AND t.approved = 1';

    $query = 'SELECT' .
             '	t.topic_id, t.topic_replies, t.forum_id, t.topic_title, t.topic_views, t.type_id, t.topic_time, t.topic_poster, t.poster_name,' .
             '	f.forum_name' .
             '	FROM ' . $GLOBALS["xoopsDB"]->prefix('bb_topics') . ' AS t ' .
             '	LEFT JOIN ' . $GLOBALS["xoopsDB"]->prefix('bb_forums') . ' AS f ON f.forum_id=t.forum_id' .
             '	WHERE 1=1 ' .
             $forum_criteria .
             $approve_criteria .
             $extra_criteria .
             ' ORDER BY ' . $order . ' DESC';

    $result = $GLOBALS["xoopsDB"]->query($query, $options[1], 0);

    if (!$result) {
        //xoops_error($GLOBALS["xoopsDB"]->error());
        return $block;
    }
    $block['disp_mode'] = $options[3]; // 0 - full view; 1 - compact view; 2 - lite view;
    $rows               = array();
    $author             = array();
    $types              = array();
    while ($row = $GLOBALS["xoopsDB"]->fetchArray($result)) {
        $rows[]                       = $row;
        $author[$row["topic_poster"]] = 1;
        if ($row['type_id'] > 0) {
            $types[$row['type_id']] = 1;
        }
    }
    if (count($rows) < 1) return $block;
    mod_loadFunctions("user", "newbb");
    $author_name = newbb_getUnameFromIds(array_keys($author), $newbbConfig['show_realname'], true);
    if (count($types) > 0) {
        $type_handler = xoops_getmodulehandler('type', 'newbb');
        $type_list    = $type_handler->getList(new Criteria("type_id", "(" . implode(", ", array_keys($types)) . ")", "IN"));
    }

    foreach ($rows as $arr) {
        // irmtfan remove $topic_page_jump because there is no last post
        //$topic_page_jump = '';
        $topic['topic_subject'] = empty($type_list[$arr["type_id"]]) ? "" : "[" . $type_list[$arr["type_id"]] . "] ";
        $topic['forum_id']      = $arr['forum_id'];
        $topic['forum_name']    = $myts->htmlSpecialChars($arr['forum_name']);
        $topic['id']            = $arr['topic_id'];

        $title = $myts->htmlSpecialChars($arr['topic_title']);
        if (!empty($options[5])) {
            $title = xoops_substr($title, 0, $options[5]);
        }
        $topic['title']   = $topic['topic_subject'] . $title;
        $topic['replies'] = $arr['topic_replies'];
        $topic['views']   = $arr['topic_views'];
        $topic['time']    = newbb_formatTimestamp($arr['topic_time']);
        if (!empty($author_name[$arr['topic_poster']])) {
            $topic_poster = $author_name[$arr['topic_poster']];
        } else {
            $topic_poster = $myts->htmlSpecialChars(($arr['poster_name']) ? $arr['poster_name'] : $GLOBALS["xoopsConfig"]["anonymous"]);
        }
        $topic['topic_poster'] = $topic_poster;
        // irmtfan remove $topic_page_jump because there is no last post
        //$topic['topic_page_jump'] = $topic_page_jump;
        // START irmtfan remove hardcoded html in URLs - add $seo_topic_url
        $seo_topic_url = XOOPS_URL . '/' . SEO_MODULE_NAME . '/viewtopic.php?topic_id=' . $topic['id'];
        $seo_forum_url = XOOPS_URL . '/' . SEO_MODULE_NAME . '/viewforum.php?forum=' . $topic['forum_id'];

        if (!empty($newbbConfig['do_rewrite'])) {
            $topic['seo_topic_url'] = seo_urls($seo_topic_url);
            $topic['seo_forum_url'] = seo_urls($seo_forum_url);

        } else {
            $topic['seo_topic_url'] = $seo_topic_url;
            $topic['seo_forum_url'] = $seo_forum_url;
        }
        // END irmtfan remove hardcoded html in URLs - add $seo_topic_url
        $block['topics'][] = $topic;
        unset($topic);
    }
    // START irmtfan remove hardcoded html in URLs
    $seo_top_allforums          = XOOPS_URL . '/' . SEO_MODULE_NAME;
    $block['seo_top_allforums'] = (!empty($newbbConfig['do_rewrite'])) ? seo_urls($seo_top_allforums) : $seo_top_allforums;
    $seo_top_allforums          = XOOPS_URL . '/' . SEO_MODULE_NAME . '/list.topic.php';
    $block['seo_top_alltopics'] = (!empty($newbbConfig['do_rewrite'])) ? seo_urls($seo_top_allforums) : $seo_top_allforums;
    $seo_top_allforums          = XOOPS_URL . '/' . SEO_MODULE_NAME . '/viewpost.php';
    $block['seo_top_allposts']  = (!empty($newbbConfig['do_rewrite'])) ? seo_urls($seo_top_allforums) : $seo_top_allforums;
    // END irmtfan remove hardcoded html in URLs
    $block['indexNav'] = intval($options[4]);

    return $block;
}

// options[0] - Citeria valid: title(by default), text
// options[1] - NumberToDisplay: any positive integer
// options[2] - TimeDuration: negative for hours, positive for days, for instance, -5 for 5 hours and 5 for 5 days
// options[3] - DisplayMode: 0-full view;1-compact view;2-lite view; Only valid for "time"
// options[4] - Display Navigator: 1 (by default), 0 (No)
// options[5] - Title/Text Length : 0 - no limit
// options[6] - SelectedForumIDs: null for all

function b_newbb_post_show($options)
{
    global $xoopsConfig;
    global $access_forums;

    mod_loadFunctions("time", "newbb");
    $myts           = MyTextSanitizer::getInstance();
    $block          = array();
    $i              = 0;
    $order          = "";
    $extra_criteria = "";
    $time_criteria  = null;
    if (!empty($options[2])) {
        $time_criteria  = time() - newbb_getSinceTime($options[2]);
        $extra_criteria = " AND p.post_time>" . $time_criteria;
    }

    switch ($options[0]) {
        case "text":
            if (!empty($newbbConfig['enable_karma']))
                $extra_criteria .= " AND p.post_karma = 0";
            if (!empty($newbbConfig['allow_require_reply']))
                $extra_criteria .= " AND p.require_reply = 0";
        default:
            $order = 'p.post_id';
            break;
    }

    if (!isset($access_forums)) {
        $perm_handler = xoops_getmodulehandler('permission', 'newbb');
        if (!$access_forums = $perm_handler->getForums()) {
            return $block;
        }
    }

    $newbbConfig = newbb_load_config();
    if (!empty($newbbConfig['do_rewrite'])) {
        include_once $GLOBALS['xoops']->path('modules/newbb/seo_url.php');
    } else {
        if (!defined('SEO_MODULE_NAME')) define('SEO_MODULE_NAME', 'modules/newbb');
    }

    if (!empty($options[6])) {
        $allowedforums  = array_filter(array_slice($options, 6), "b_newbb_array_filter"); // get allowed forums
        $allowed_forums = array_intersect($allowedforums, $access_forums);
    } else {
        $allowed_forums = $access_forums;
    }
    if (empty($allowed_forums)) return $block;

    $forum_criteria   = ' AND p.forum_id IN (' . implode(',', $allowed_forums) . ')';
    $approve_criteria = ' AND p.approved = 1';

    $query = 'SELECT';
    $query .= '	p.post_id, p.subject, p.post_time, p.icon, p.uid, p.poster_name,';
    if ($options[0] == "text") {
        $query .= '	pt.dohtml, pt.dosmiley, pt.doxcode, pt.dobr, pt.post_text,';
    }
    $query .= '	f.forum_id, f.forum_name' .
              '	FROM ' . $GLOBALS["xoopsDB"]->prefix('bb_posts') . ' AS p ' .
              '	LEFT JOIN ' . $GLOBALS["xoopsDB"]->prefix('bb_forums') . ' AS f ON f.forum_id=p.forum_id';
    if ($options[0] == "text") {
        $query .= '	LEFT JOIN ' . $GLOBALS["xoopsDB"]->prefix('bb_posts_text') . ' AS pt ON pt.post_id=p.post_id';
    }
    $query .= '	WHERE 1=1 ' .
              $forum_criteria .
              $approve_criteria .
              $extra_criteria .
              ' ORDER BY ' . $order . ' DESC';

    $result = $GLOBALS["xoopsDB"]->query($query, $options[1], 0);
    if (!$result) {
        //xoops_error($GLOBALS["xoopsDB"]->error());
        return $block;
    }
    $block['disp_mode'] = ($options[0] == "text") ? 3 : $options[3]; // 0 - full view; 1 - compact view; 2 - lite view;
    $rows               = array();
    $author             = array();
    while ($row = $GLOBALS["xoopsDB"]->fetchArray($result)) {
        $rows[]              = $row;
        $author[$row["uid"]] = 1;
    }
    if (count($rows) < 1) return $block;
    mod_loadFunctions("user", "newbb");
    $author_name = newbb_getUnameFromIds(array_keys($author), $newbbConfig['show_realname'], true);

    foreach ($rows as $arr) {
        //if ($arr['icon'] && is_file($GLOBALS['xoops']->path('images/subject/' . $arr['icon']))) {
        if (!empty($arr['icon'])) {
            $last_post_icon = '<img src="' . XOOPS_URL . '/images/subject/' . htmlspecialchars($arr['icon']) . '" alt="" />';
        } else {
            $last_post_icon = '<img src="' . XOOPS_URL . '/images/subject/icon1.gif" alt="" />';
        }
        //$topic['jump_post'] = "<a href='" . XOOPS_URL . "/modules/newbb/viewtopic.php?post_id=" . $arr['post_id'] ."#forumpost" . $arr['post_id'] . "'>" . $last_post_icon . "</a>";
        $topic['forum_id']   = $arr['forum_id'];
        $topic['forum_name'] = $myts->htmlSpecialChars($arr['forum_name']);
        //$topic['id'] = $arr['topic_id'];

        $title = $myts->htmlSpecialChars($arr['subject']);
        if ($options[0] != "text" && !empty($options[5])) {
            $title = xoops_substr($title, 0, $options[5]);
        }
        $topic['title']   = $title;
        $topic['post_id'] = $arr['post_id'];
        $topic['time']    = newbb_formatTimestamp($arr['post_time']);
        if (!empty($author_name[$arr['uid']])) {
            $topic_poster = $author_name[$arr['uid']];
        } else {
            $topic_poster = $myts->htmlSpecialChars(($arr['poster_name']) ? $arr['poster_name'] : $GLOBALS["xoopsConfig"]["anonymous"]);
        }
        $topic['topic_poster'] = $topic_poster;

        if ($options[0] == "text") {
            $post_text = $myts->displayTarea($arr['post_text'], $arr['dohtml'], $arr['dosmiley'], $arr['doxcode'], 1, $arr['dobr']);
            if (!empty($options[5])) {
                $post_text = xoops_substr(strip_tags($post_text), 0, $options[5]);
            }
            $topic['post_text'] = $post_text;
        }
        // START irmtfan remove hardcoded html in URLs
        $seo_url       = XOOPS_URL . '/' . SEO_MODULE_NAME . '/viewtopic.php?post_id=' . $topic['post_id'];
        $seo_forum_url = XOOPS_URL . '/' . SEO_MODULE_NAME . '/viewforum.php?forum=' . $topic['forum_id'];
        // END irmtfan remove hardcoded html in URLs
        if (!empty($newbbConfig['do_rewrite'])) {
            $topic['seo_url']       = seo_urls($seo_url);
            $topic['seo_forum_url'] = seo_urls($seo_forum_url);

        } else {
            $topic['seo_url']       = $seo_url;
            $topic['seo_forum_url'] = $seo_forum_url;
        }

        $block['topics'][] = $topic;
        unset($topic);
    }
    // START irmtfan remove hardcoded html in URLs
    $seo_top_allforums          = XOOPS_URL . '/' . SEO_MODULE_NAME;
    $block['seo_top_allforums'] = (!empty($newbbConfig['do_rewrite'])) ? seo_urls($seo_top_allforums) : $seo_top_allforums;
    $seo_top_allforums          = XOOPS_URL . '/' . SEO_MODULE_NAME . '/list.topic.php';
    $block['seo_top_alltopics'] = (!empty($newbbConfig['do_rewrite'])) ? seo_urls($seo_top_allforums) : $seo_top_allforums;
    $seo_top_allforums          = XOOPS_URL . '/' . SEO_MODULE_NAME . '/viewpost.php';
    $block['seo_top_allposts']  = (!empty($newbbConfig['do_rewrite'])) ? seo_urls($seo_top_allforums) : $seo_top_allforums;
    // END irmtfan remove hardcoded html in URLs

    $block['indexNav'] = intval($options[4]);

    return $block;
}

// options[0] - Citeria valid: post(by default), topic, digest, sticky
// options[1] - NumberToDisplay: any positive integer
// options[2] - TimeDuration: negative for hours, positive for days, for instance, -5 for 5 hours and 5 for 5 days
// options[3] - DisplayMode: 0-full view;1-compact view;
// options[4] - Display Navigator: 1 (by default), 0 (No)
// options[5] - Title Length : 0 - no limit
// options[6] - SelectedForumIDs: null for all

function b_newbb_author_show($options)
{
    global $xoopsConfig;
    global $access_forums;
    global $newbbConfig;

    $myts           =& MyTextSanitizer::getInstance();
    $block          = array();
    $i              = 0;
    $type           = "topic";
    $order          = "count";
    $extra_criteria = "";
    $time_criteria  = null;
    if (!empty($options[2])) {
        mod_loadFunctions("time", "newbb");
        $time_criteria  = time() - newbb_getSinceTime($options[2]);
        $extra_criteria = " AND topic_time > " . $time_criteria;
    }
    switch ($options[0]) {
        case 'topic':
            break;
        case 'digest':
            $extra_criteria = " AND topic_digest = 1";
            if ($time_criteria) {
                $extra_criteria .= " AND digest_time > " . $time_criteria;
            }
            break;
        case 'sticky':
            $extra_criteria .= " AND topic_sticky = 1";
            break;
        case 'post':
        default:
            $type = "post";
            if ($time_criteria) {
                $extra_criteria = " AND post_time > " . $time_criteria;
            }
            break;
    }

    if (!isset($access_forums)) {
        $perm_handler =& xoops_getmodulehandler('permission', 'newbb');
        if (!$access_forums = $perm_handler->getForums()) {
            return $block;
        }
    }

    if (!empty($options[5])) {
        $allowedforums  = array_filter(array_slice($options, 5), "b_newbb_array_filter"); // get allowed forums
        $allowed_forums = array_intersect($allowedforums, $access_forums);
    } else {
        $allowed_forums = $access_forums;
    }
    if (empty($allowed_forums)) return false;

    if ($type == "topic") {
        $forum_criteria   = ' AND forum_id IN (' . implode(',', $allowed_forums) . ')';
        $approve_criteria = ' AND approved = 1';
        $query            = 'SELECT DISTINCT topic_poster AS author, COUNT(*) AS count
                    FROM ' . $GLOBALS["xoopsDB"]->prefix('bb_topics') . '
                    WHERE topic_poster>0 ' .
                            $forum_criteria .
                            $approve_criteria .
                            $extra_criteria .
                            ' GROUP BY topic_poster ORDER BY ' . $order . ' DESC';
    } else {
        $forum_criteria   = ' AND forum_id IN (' . implode(',', $allowed_forums) . ')';
        $approve_criteria = ' AND approved = 1';
        $query            = 'SELECT DISTINCT uid AS author, COUNT(*) AS count
                    FROM ' . $GLOBALS["xoopsDB"]->prefix('bb_posts') . '
                    WHERE uid > 0 ' .
                            $forum_criteria .
                            $approve_criteria .
                            $extra_criteria .
                            ' GROUP BY uid ORDER BY ' . $order . ' DESC';
    }

    $result = $GLOBALS["xoopsDB"]->query($query, $options[1], 0);
    if (!$result) {
        //xoops_error($GLOBALS["xoopsDB"]->error());
        return $block;
    }
    $author = array();
    while ($row = $GLOBALS["xoopsDB"]->fetchArray($result)) {
        $author[$row["author"]]["count"] = $row["count"];
    }
    if (count($author) < 1) return $block;
    mod_loadFunctions("user", "newbb");
    $author_name = newbb_getUnameFromIds(array_keys($author), $newbbConfig['show_realname']);
    foreach (array_keys($author) as $uid) {
        $author[$uid]["name"] = $myts->htmlSpecialChars($author_name[$uid]);
    }
    $block['authors']   =& $author;
    $block['disp_mode'] = $options[3]; // 0 - full view; 1 - lite view;
    $block['indexNav']  = intval($options[4]);

    return $block;
}

function b_newbb_edit($options)
{
    mod_loadFunctions("forum", "newbb");

    $form = _MB_NEWBB_CRITERIA . "<select name='options[0]'>";
    $form .= "<option value='time'";
    if ($options[0] == "time") $form .= " selected='selected' ";
    $form .= ">" . _MB_NEWBB_CRITERIA_TIME . "</option>";
    $form .= "</select>";
    $form .= "<br />" . _MB_NEWBB_DISPLAY . "<input type='text' name='options[1]' value='" . $options[1] . "' />";
    $form .= "<br />" . _MB_NEWBB_TIME . "<input type='text' name='options[2]' value='" . $options[2] . "' />";
    $form .= "<br />&nbsp;&nbsp;&nbsp;&nbsp;<small>" . _MB_NEWBB_TIME_DESC . "</small>";
    $form .= "<br />" . _MB_NEWBB_DISPLAYMODE . "<input type='radio' name='options[3]' value='0'";
    if ($options[3] == 0) {
        $form .= " checked='checked'";
    }
    $form .= " />&nbsp;" . _MB_NEWBB_DISPLAYMODE_FULL . "<input type='radio' name='options[3]' value='1'";
    if ($options[3] == 1) {
        $form .= " checked='checked'";
    }
    $form .= " />&nbsp;" . _MB_NEWBB_DISPLAYMODE_COMPACT . "<input type='radio' name='options[3]' value='2'";
    if ($options[3] == 2) {
        $form .= " checked='checked'";
    }
    $form .= " />&nbsp;" . _MB_NEWBB_DISPLAYMODE_LITE;

    $form .= "<br />" . _MB_NEWBB_INDEXNAV . "<input type=\"radio\" name=\"options[4]\" value=\"1\"";
    if ($options[4] == 1) $form .= " checked=\"checked\"";
    $form .= " />" . _YES . "<input type=\"radio\" name=\"options[4]\" value=\"0\"";
    if ($options[4] == 0) $form .= " checked=\"checked\"";
    $form .= " />" . _NO;

    $form .= "<br />" . _MB_NEWBB_TITLE_LENGTH . "<input type='text' name='options[5]' value='" . $options[5] . "' />";

    $form .= "<br /><br />" . _MB_NEWBB_FORUMLIST;

    $options_forum = array_filter(array_slice($options, 6), "b_newbb_array_filter"); // get allowed forums
    $isAll         = (count($options_forum) == 0 || empty($options_forum[0]));
    $form .= "<br />&nbsp;&nbsp;<select name=\"options[]\" multiple=\"multiple\">";
    $form .= "<option value=\"0\" ";
    if ($isAll) $form .= " selected";
    $form .= ">" . _ALL . "</option>";
    $form .= newbb_forumSelectBox($options_forum);
    $form .= "</select><br />";

    return $form;
}

function b_newbb_topic_edit($options)
{
    mod_loadFunctions("forum", "newbb");
    $form = _MB_NEWBB_CRITERIA . "<select name='options[0]'>";
    $form .= "<option value='time'";
    if ($options[0] == "time") $form .= " selected='selected' ";
    $form .= ">" . _MB_NEWBB_CRITERIA_TIME . "</option>";
    $form .= "<option value='views'";
    if ($options[0] == "views") $form .= " selected='selected' ";
    $form .= ">" . _MB_NEWBB_CRITERIA_VIEWS . "</option>";
    $form .= "<option value='replies'";
    if ($options[0] == "replies") $form .= " selected='selected' ";
    $form .= ">" . _MB_NEWBB_CRITERIA_REPLIES . "</option>";
    $form .= "<option value='digest'";
    if ($options[0] == "digest") $form .= " selected='selected' ";
    $form .= ">" . _MB_NEWBB_CRITERIA_DIGEST . "</option>";
    $form .= "<option value='sticky'";
    if ($options[0] == "sticky") $form .= " selected='selected' ";
    $form .= ">" . _MB_NEWBB_CRITERIA_STICKY . "</option>";
    $form .= "</select>";
    $form .= "<br />" . _MB_NEWBB_DISPLAY . "<input type='text' name='options[1]' value='" . $options[1] . "' />";
    $form .= "<br />" . _MB_NEWBB_TIME . "<input type='text' name='options[2]' value='" . $options[2] . "' />";
    $form .= "<br />&nbsp;&nbsp;&nbsp;&nbsp;<small>" . _MB_NEWBB_TIME_DESC . "</small>";
    $form .= "<br />" . _MB_NEWBB_DISPLAYMODE . "<input type='radio' name='options[3]' value='0'";
    if ($options[3] == 0) {
        $form .= " checked='checked'";
    }
    $form .= " />&nbsp;" . _MB_NEWBB_DISPLAYMODE_FULL . "<input type='radio' name='options[3]' value='1'";
    if ($options[3] == 1) {
        $form .= " checked='checked'";
    }
    $form .= " />&nbsp;" . _MB_NEWBB_DISPLAYMODE_COMPACT . "<input type='radio' name='options[3]' value='2'";
    if ($options[3] == 2) {
        $form .= " checked='checked'";
    }
    $form .= " />&nbsp;" . _MB_NEWBB_DISPLAYMODE_LITE;

    $form .= "<br />" . _MB_NEWBB_INDEXNAV . "<input type=\"radio\" name=\"options[4]\" value=\"1\"";
    if ($options[4] == 1) $form .= " checked=\"checked\"";
    $form .= " />" . _YES . "<input type=\"radio\" name=\"options[4]\" value=\"0\"";
    if ($options[4] == 0) $form .= " checked=\"checked\"";
    $form .= " />" . _NO;

    $form .= "<br />" . _MB_NEWBB_TITLE_LENGTH . "<input type='text' name='options[5]' value='" . $options[5] . "' />";

    $form .= "<br /><br />" . _MB_NEWBB_FORUMLIST;

    $options_forum = array_filter(array_slice($options, 6), "b_newbb_array_filter"); // get allowed forums

    $isAll = (count($options_forum) == 0 || empty($options_forum[0])) ? true : false;
    $form .= "<br />&nbsp;&nbsp;<select name=\"options[]\" multiple=\"multiple\">";
    $form .= "<option value=\"0\" ";
    if ($isAll) $form .= " selected=\"selected\"";
    $form .= ">" . _ALL . "</option>";
    $form .= newbb_forumSelectBox($options_forum);
    $form .= "</select><br />";

    return $form;
}

function b_newbb_post_edit($options)
{
    mod_loadFunctions("forum", "newbb");
    $form = _MB_NEWBB_CRITERIA . "<select name='options[0]'>";
    $form .= "<option value='title'";
    if ($options[0] == "title") $form .= " selected='selected' ";
    $form .= ">" . _MB_NEWBB_CRITERIA_TITLE . "</option>";
    $form .= "<option value='text'";
    if ($options[0] == "text") $form .= " selected='selected' ";
    $form .= ">" . _MB_NEWBB_CRITERIA_TEXT . "</option>";
    $form .= "</select>";
    $form .= "<br />" . _MB_NEWBB_DISPLAY . "<input type='text' name='options[1]' value='" . $options[1] . "' />";
    $form .= "<br />" . _MB_NEWBB_TIME . "<input type='text' name='options[2]' value='" . $options[2] . "' />";
    $form .= "<br />&nbsp;&nbsp;&nbsp;&nbsp;<small>" . _MB_NEWBB_TIME_DESC . "</small>";
    $form .= "<br />" . _MB_NEWBB_DISPLAYMODE . "<input type='radio' name='options[3]' value='0'";
    if ($options[3] == 0) {
        $form .= " checked='checked'";
    }
    $form .= " />&nbsp;" . _MB_NEWBB_DISPLAYMODE_FULL . "<input type='radio' name='options[3]' value='1'";
    if ($options[3] == 1) {
        $form .= " checked='checked'";
    }
    $form .= " />&nbsp;" . _MB_NEWBB_DISPLAYMODE_COMPACT . "<input type='radio' name='options[3]' value='2'";
    if ($options[3] == 2) {
        $form .= " checked='checked'";
    }
    $form .= " />&nbsp;" . _MB_NEWBB_DISPLAYMODE_LITE;

    $form .= "<br />" . _MB_NEWBB_INDEXNAV . "<input type=\"radio\" name=\"options[4]\" value=\"1\"";
    if ($options[4] == 1) $form .= " checked=\"checked\"";
    $form .= " />" . _YES . "<input type=\"radio\" name=\"options[4]\" value=\"0\"";
    if ($options[4] == 0) $form .= " checked=\"checked\"";
    $form .= " />" . _NO;

    $form .= "<br />" . _MB_NEWBB_TITLE_LENGTH . "<input type='text' name='options[5]' value='" . $options[5] . "' />";

    $form .= "<br /><br />" . _MB_NEWBB_FORUMLIST;

    $options_forum = array_filter(array_slice($options, 6), "b_newbb_array_filter"); // get allowed forums
    $isAll         = (count($options_forum) == 0 || empty($options_forum[0])) ? true : false;
    $form .= "<br />&nbsp;&nbsp;<select name=\"options[]\" multiple=\"multiple\">";
    $form .= "<option value=\"0\" ";
    if ($isAll) $form .= " selected=\"selected\"";
    $form .= ">" . _ALL . "</option>";
    $form .= newbb_forumSelectBox($options_forum);
    $form .= "</select><br />";

    return $form;
}

function b_newbb_author_edit($options)
{
    mod_loadFunctions("forum", "newbb");
    $form = _MB_NEWBB_CRITERIA . "<select name='options[0]'>";
    $form .= "<option value='post'";
    if ($options[0] == "post") $form .= " selected='selected' ";
    $form .= ">" . _MB_NEWBB_CRITERIA_POST . "</option>";
    $form .= "<option value='topic'";
    if ($options[0] == "topic") $form .= " selected='selected' ";
    $form .= ">" . _MB_NEWBB_CRITERIA_TOPIC . "</option>";
    $form .= "<option value='digest'";
    if ($options[0] == "digest") $form .= " selected='selected' ";
    $form .= ">" . _MB_NEWBB_CRITERIA_DIGESTS . "</option>";
    $form .= "<option value='sticky'";
    if ($options[0] == "sticky") $form .= " selected='selected' ";
    $form .= ">" . _MB_NEWBB_CRITERIA_STICKYS . "</option>";
    $form .= "</select>";
    $form .= "<br />" . _MB_NEWBB_DISPLAY . "<input type='text' name='options[1]' value='" . $options[1] . "' />";
    $form .= "<br />" . _MB_NEWBB_TIME . "<input type='text' name='options[2]' value='" . $options[2] . "' />";
    $form .= "<br />&nbsp;&nbsp;&nbsp;&nbsp;<small>" . _MB_NEWBB_TIME_DESC . "</small>";
    $form .= "<br />" . _MB_NEWBB_DISPLAYMODE . "<input type='radio' name='options[3]' value='0'";
    if ($options[3] == 0) {
        $form .= " checked='checked'";
    }
    $form .= " />&nbsp;" . _MB_NEWBB_DISPLAYMODE_COMPACT . "<input type='radio' name='options[3]' value='1'";
    if ($options[3] == 1) {
        $form .= " checked='checked'";
    }
    $form .= " />&nbsp;" . _MB_NEWBB_DISPLAYMODE_LITE;

    $form .= "<br />" . _MB_NEWBB_INDEXNAV . "<input type=\"radio\" name=\"options[4]\" value=\"1\"";
    if ($options[4] == 1) $form .= " checked=\"checked\"";
    $form .= " />" . _YES . "<input type=\"radio\" name=\"options[4]\" value=\"0\"";
    if ($options[4] == 0) $form .= " checked=\"checked\"";
    $form .= " />" . _NO;

    $form .= "<br /><br />" . _MB_NEWBB_FORUMLIST;

    $options_forum = array_filter(array_slice($options, 5), "b_newbb_array_filter"); // get allowed forums
    $isAll         = (count($options_forum) == 0 || empty($options_forum[0])) ? true : false;
    $form .= "<br />&nbsp;&nbsp;<select name=\"options[]\" multiple=\"multiple\">";
    $form .= "<option value=\"0\" ";
    if ($isAll) $form .= " selected=\"selected\"";
    $form .= ">" . _ALL . "</option>";
    $form .= newbb_forumSelectBox($options_forum);
    $form .= "</select><br />";

    return $form;
}

function b_newbb_custom($options)
{
    global $xoopsConfig;
    // if no newbb module block set, we have to include the language file
    if (is_readable($GLOBALS['xoops']->path('modules/newbb/language/' . $xoopsConfig['language'] . '/blocks.php')))
        include_once($GLOBALS['xoops']->path('modules/newbb/language/' . $xoopsConfig['language'] . '/blocks.php'));
    else
        include_once($GLOBALS['xoops']->path('modules/newbb/language/english/blocks.php'));

    $options = explode('|', $options);
    $block   = &b_newbb_show($options);
    if (count($block["topics"]) < 1) return false;

    $tpl = new XoopsTpl();
    $tpl->assign('block', $block);
    $tpl->display('db:newbb_block.tpl');
}

function b_newbb_custom_topic($options)
{
    global $xoopsConfig;
    // if no newbb module block set, we have to include the language file
    if (is_readable($GLOBALS['xoops']->path('modules/newbb/language/' . $xoopsConfig['language'] . '/blocks.php')))
        include_once($GLOBALS['xoops']->path('modules/newbb/language/' . $xoopsConfig['language'] . '/blocks.php'));
    else
        include_once($GLOBALS['xoops']->path('modules/newbb/language/english/blocks.php'));

    $options = explode('|', $options);
    $block   = &b_newbb_topic_show($options);
    if (count($block["topics"]) < 1) return false;

    $tpl = new XoopsTpl();
    $tpl->assign('block', $block);
    $tpl->display('db:newbb_block_topic.tpl');
}

function b_newbb_custom_post($options)
{
    global $xoopsConfig;
    // if no newbb module block set, we have to include the language file
    if (is_readable($GLOBALS['xoops']->path('modules/newbb/language/' . $xoopsConfig['language'] . '/blocks.php')))
        include_once($GLOBALS['xoops']->path('modules/newbb/language/' . $xoopsConfig['language'] . '/blocks.php'));
    else
        include_once($GLOBALS['xoops']->path('modules/newbb/language/english/blocks.php'));

    $options = explode('|', $options);
    $block   = &b_newbb_post_show($options);
    if (count($block["topics"]) < 1) return false;

    $tpl = new XoopsTpl();
    $tpl->assign('block', $block);
    $tpl->display('db:newbb_block_post.tpl');
}

function b_newbb_custom_author($options)
{
    global $xoopsConfig;
    // if no newbb module block set, we have to include the language file
    if (is_readable($GLOBALS['xoops']->path('modules/newbb/language/' . $xoopsConfig['language'] . '/blocks.php')))
        include_once($GLOBALS['xoops']->path('modules/newbb/language/' . $xoopsConfig['language'] . '/blocks.php'));
    else
        include_once($GLOBALS['xoops']->path('modules/newbb/language/english/blocks.php'));

    $options = explode('|', $options);
    $block   = &b_newbb_author_show($options);
    if (count($block["authors"]) < 1) return false;

    $tpl = new XoopsTpl();
    $tpl->assign('block', $block);
    $tpl->display('db:newbb_block_author.tpl');
}

// irmtfan add local stylesheet and js footer.php
include_once $GLOBALS['xoops']->path('modules/newbb/footer.php');
