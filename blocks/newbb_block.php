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
// defined('XOOPS_ROOT_PATH') || die('Restricted access');
// irmtfan use full path because block maybe used outside newbb

use XoopsModules\Newbb;

require_once $GLOBALS['xoops']->path('modules/newbb/include/functions.ini.php');

if (defined('NEWBB_BLOCK_DEFINED')) {
    return;
}
define('NEWBB_BLOCK_DEFINED', true);

/**
 * @param $var
 * @return bool
 */
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

/**
 * @param $options
 * @return array|bool
 */
function b_newbb_show($options)
{
    global $accessForums;
    global $xoopsLogger;

    require_once __DIR__ . '/../include/functions.config.php';
    require_once __DIR__ . '/../include/functions.time.php';

    $myts          = \MyTextSanitizer::getInstance();
    $block         = [];
    $i             = 0;
    $order         = '';
    $extraCriteria = '';
    if (!empty($options[2])) {
        //require_once __DIR__ . '/../include/functions.time.php';
        $extraCriteria .= ' AND p.post_time>' . (time() - newbbGetSinceTime($options[2]));
    }
    switch ($options[0]) {
        case 'time':
        default:
            $order = 't.topic_last_post_id';
            break;
    }

    if (!isset($accessForums)) {
        /** var Newbb\PermissionHandler $permHandler */
        $permHandler = Newbb\Helper::getInstance()->getHandler('Permission');
        if (!$accessForums = $permHandler->getForums()) {
            return $block;
        }
    }
    if (!empty($options[6])) {
        $myallowedForums = array_filter(array_slice($options, 6), 'b_newbb_array_filter'); // get allowed forums
        $allowedForums   = array_intersect($myallowedForums, $accessForums);
    } else {
        $allowedForums = $accessForums;
    }
    if (empty($allowedForums)) {
        return $block;
    }

    $forumCriteria   = ' AND t.forum_id IN (' . implode(',', $allowedForums) . ')';
    $approveCriteria = ' AND t.approved = 1';

    $newbbConfig = newbbLoadConfig();
    if (!empty($newbbConfig['do_rewrite'])) {
        require_once $GLOBALS['xoops']->path('modules/newbb/seo_url.php');
    } else {
        if (!defined('SEO_MODULE_NAME')) {
            define('SEO_MODULE_NAME', 'modules/newbb');
        }
    }

    $query = 'SELECT'
             . '    t.topic_id, t.topic_replies, t.forum_id, t.topic_title, t.topic_views, t.type_id,'
             . '    f.forum_name,t.topic_status,'
             . '    p.post_id, p.post_time, p.icon, p.uid, p.poster_name'
             . '    FROM '
             . $GLOBALS['xoopsDB']->prefix('newbb_topics')
             . ' AS t '
             . '    LEFT JOIN '
             . $GLOBALS['xoopsDB']->prefix('newbb_posts')
             . ' AS p ON t.topic_last_post_id=p.post_id'
             . '    LEFT JOIN '
             . $GLOBALS['xoopsDB']->prefix('newbb_forums')
             . ' AS f ON f.forum_id=t.forum_id'
             . '    WHERE 1=1 '
             . $forumCriteria
             . $approveCriteria
             . $extraCriteria
             . ' ORDER BY '
             . $order
             . ' DESC';

    $result = $GLOBALS['xoopsDB']->query($query, $options[1], 0);

    if (!$result) {
        //xoops_error($GLOBALS['xoopsDB']->error());
        return false;
    }
    $block['disp_mode'] = $options[3]; // 0 - full view; 1 - compact view; 2 - lite view;
    $rows               = [];
    $author             = [];
    $types              = [];

    while (false !== ($row = $GLOBALS['xoopsDB']->fetchArray($result))) {
        $rows[]              = $row;
        $author[$row['uid']] = 1;
        if ($row['type_id'] > 0) {
            $types[$row['type_id']] = 1;
        }
    }

    if (count($rows) < 1) {
        return $block;
    }

    require_once __DIR__ . '/../include/functions.user.php';
    $author_name = newbbGetUnameFromIds(array_keys($author), $newbbConfig['show_realname'], true);

    if (count($types) > 0) {
        /** @var Newbb\TypeHandler $typeHandler */
        $typeHandler = Newbb\Helper::getInstance()->getHandler('Type');
        $type_list   = $typeHandler->getList(new \Criteria('type_id', '(' . implode(', ', array_keys($types)) . ')', 'IN'));
    }

    foreach ($rows as $arr) {
        // irmtfan add lastposticon - load main lang
        xoops_loadLanguage('main', 'newbb');
        $topic                  = [];
        $topic_page_jump        = newbbDisplayImage('lastposticon', _MD_NEWBB_GOTOLASTPOST);
        $topic['topic_subject'] = empty($type_list[$arr['type_id']]) ? '' : '[' . $type_list[$arr['type_id']] . ']';

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
        $topic['time']    = newbbFormatTimestamp($arr['post_time']);
        if (!empty($author_name[$arr['uid']])) {
            $topic_poster = $author_name[$arr['uid']];
        } else {
            $topic_poster = $myts->htmlSpecialChars($arr['poster_name'] ?: $GLOBALS['xoopsConfig']['anonymous']);
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
    $block['seo_top_allforums'] = !empty($newbbConfig['do_rewrite']) ? seo_urls($seo_top_allforums) : $seo_top_allforums;
    $seo_top_allforums          = XOOPS_URL . '/' . SEO_MODULE_NAME . '/list.topic.php';
    $block['seo_top_alltopics'] = !empty($newbbConfig['do_rewrite']) ? seo_urls($seo_top_allforums) : $seo_top_allforums;
    $seo_top_allforums          = XOOPS_URL . '/' . SEO_MODULE_NAME . '/viewpost.php';
    $block['seo_top_allposts']  = !empty($newbbConfig['do_rewrite']) ? seo_urls($seo_top_allforums) : $seo_top_allforums;
    // END irmtfan remove hardcoded html in URLs
    $block['indexNav'] = (int)$options[4];

    return $block;
}

// options[0] - Citeria valid: time(by default), views, replies, digest, sticky
// options[1] - NumberToDisplay: any positive integer
// options[2] - TimeDuration: negative for hours, positive for days, for instance, -5 for 5 hours and 5 for 5 days
// options[3] - DisplayMode: 0-full view;1-compact view;2-lite view
// options[4] - Display Navigator: 1 (by default), 0 (No)
// options[5] - Title Length : 0 - no limit
// options[6] - SelectedForumIDs: null for all

/**
 * @param $options
 * @return array|bool
 */
function b_newbb_topic_show($options)
{
    global $accessForums;
    require_once __DIR__ . '/../include/functions.time.php';
    $myts          = \MyTextSanitizer::getInstance();
    $block         = [];
    $i             = 0;
    $order         = '';
    $extraCriteria = '';
    $time_criteria = null;
    if (!empty($options[2])) {
        $time_criteria = time() - newbbGetSinceTime($options[2]);
        $extraCriteria = ' AND t.topic_time>' . $time_criteria;
    }
    switch ($options[0]) {
        case 'views':
            $order = 't.topic_views';
            break;
        case 'replies':
            $order = 't.topic_replies';
            break;
        case 'digest':
            $order         = 't.digest_time';
            $extraCriteria = ' AND t.topic_digest=1';
            if (null !== $time_criteria) {
                $extraCriteria .= ' AND t.digest_time>' . $time_criteria;
            }
            break;
        case 'sticky':
            $order         = 't.topic_id';
            $extraCriteria .= ' AND t.topic_sticky=1';
            break;
        case 'time':
        default:
            $order = 't.topic_id';
            break;
    }

    $newbbConfig = newbbLoadConfig();
    if (!empty($newbbConfig['do_rewrite'])) {
        require_once $GLOBALS['xoops']->path('modules/newbb/seo_url.php');
    } else {
        if (!defined('SEO_MODULE_NAME')) {
            define('SEO_MODULE_NAME', 'modules/newbb');
        }
    }

    if (!isset($accessForums)) {
        /** var Newbb\PermissionHandler $permHandler */
        $permHandler = Newbb\Helper::getInstance()->getHandler('Permission');
        if (!$accessForums = $permHandler->getForums()) {
            return $block;
        }
    }

    if (!empty($options[6])) {
        $myallowedForums = array_filter(array_slice($options, 6), 'b_newbb_array_filter'); // get allowed forums
        $allowedForums   = array_intersect($myallowedForums, $accessForums);
    } else {
        $allowedForums = $accessForums;
    }
    if (empty($allowedForums)) {
        return false;
    }

    $forumCriteria   = ' AND t.forum_id IN (' . implode(',', $allowedForums) . ')';
    $approveCriteria = ' AND t.approved = 1';

    $query = 'SELECT'
             . '    t.topic_id, t.topic_replies, t.forum_id, t.topic_title, t.topic_views, t.type_id, t.topic_time, t.topic_poster, t.poster_name,'
             . '    f.forum_name'
             . '    FROM '
             . $GLOBALS['xoopsDB']->prefix('newbb_topics')
             . ' AS t '
             . '    LEFT JOIN '
             . $GLOBALS['xoopsDB']->prefix('newbb_forums')
             . ' AS f ON f.forum_id=t.forum_id'
             . '    WHERE 1=1 '
             . $forumCriteria
             . $approveCriteria
             . $extraCriteria
             . ' ORDER BY '
             . $order
             . ' DESC';

    $result = $GLOBALS['xoopsDB']->query($query, $options[1], 0);

    if (!$result) {
        //xoops_error($GLOBALS['xoopsDB']->error());
        return $block;
    }
    $block['disp_mode'] = $options[3]; // 0 - full view; 1 - compact view; 2 - lite view;
    $rows               = [];
    $author             = [];
    $types              = [];
    while (false !== ($row = $GLOBALS['xoopsDB']->fetchArray($result))) {
        $rows[]                       = $row;
        $author[$row['topic_poster']] = 1;
        if ($row['type_id'] > 0) {
            $types[$row['type_id']] = 1;
        }
    }
    if (count($rows) < 1) {
        return $block;
    }
    require_once __DIR__ . '/../include/functions.user.php';
    $author_name = newbbGetUnameFromIds(array_keys($author), $newbbConfig['show_realname'], true);
    if (count($types) > 0) {
        /** @var Newbb\TypeHandler $typeHandler */
        $typeHandler = Newbb\Helper::getInstance()->getHandler('Type');
        $type_list   = $typeHandler->getList(new \Criteria('type_id', '(' . implode(', ', array_keys($types)) . ')', 'IN'));
    }

    foreach ($rows as $arr) {
        // irmtfan remove $topic_page_jump because there is no last post
        //$topic_page_jump = '';
        $topic                  = [];
        $topic['topic_subject'] = empty($type_list[$arr['type_id']]) ? '' : '[' . $type_list[$arr['type_id']] . '] ';
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
        $topic['time']    = newbbFormatTimestamp($arr['topic_time']);
        if (!empty($author_name[$arr['topic_poster']])) {
            $topic_poster = $author_name[$arr['topic_poster']];
        } else {
            $topic_poster = $myts->htmlSpecialChars($arr['poster_name'] ?: $GLOBALS['xoopsConfig']['anonymous']);
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
    $block['seo_top_allforums'] = !empty($newbbConfig['do_rewrite']) ? seo_urls($seo_top_allforums) : $seo_top_allforums;
    $seo_top_allforums          = XOOPS_URL . '/' . SEO_MODULE_NAME . '/list.topic.php';
    $block['seo_top_alltopics'] = !empty($newbbConfig['do_rewrite']) ? seo_urls($seo_top_allforums) : $seo_top_allforums;
    $seo_top_allforums          = XOOPS_URL . '/' . SEO_MODULE_NAME . '/viewpost.php';
    $block['seo_top_allposts']  = !empty($newbbConfig['do_rewrite']) ? seo_urls($seo_top_allforums) : $seo_top_allforums;
    // END irmtfan remove hardcoded html in URLs
    $block['indexNav'] = (int)$options[4];

    return $block;
}

// options[0] - Citeria valid: title(by default), text
// options[1] - NumberToDisplay: any positive integer
// options[2] - TimeDuration: negative for hours, positive for days, for instance, -5 for 5 hours and 5 for 5 days
// options[3] - DisplayMode: 0-full view;1-compact view;2-lite view; Only valid for "time"
// options[4] - Display Navigator: 1 (by default), 0 (No)
// options[5] - Title/Text Length : 0 - no limit
// options[6] - SelectedForumIDs: null for all

/**
 * @param $options
 * @return array
 */
function b_newbb_post_show($options)
{
    global $accessForums;
    global $newbbConfig;

    require_once __DIR__ . '/../include/functions.time.php';
    $myts          = \MyTextSanitizer::getInstance();
    $block         = [];
    $i             = 0;
    $order         = '';
    $extraCriteria = '';
    $time_criteria = null;
    if (!empty($options[2])) {
        $time_criteria = time() - newbbGetSinceTime($options[2]);
        $extraCriteria = ' AND p.post_time>' . $time_criteria;
    }

    switch ($options[0]) {
        case 'text':
            if (!empty($newbbConfig['enable_karma'])) {
                $extraCriteria .= ' AND p.post_karma = 0';
            }
            if (!empty($newbbConfig['allow_require_reply'])) {
                $extraCriteria .= ' AND p.require_reply = 0';
            }
        // no break
        default:
            $order = 'p.post_id';
            break;
    }

    if (!isset($accessForums)) {
        /** var Newbb\PermissionHandler $permHandler */
        $permHandler = Newbb\Helper::getInstance()->getHandler('Permission');
        if (!$accessForums = $permHandler->getForums()) {
            return $block;
        }
    }

    $newbbConfig = newbbLoadConfig();
    if (!empty($newbbConfig['do_rewrite'])) {
        require_once $GLOBALS['xoops']->path('modules/newbb/seo_url.php');
    } else {
        if (!defined('SEO_MODULE_NAME')) {
            define('SEO_MODULE_NAME', 'modules/newbb');
        }
    }

    if (!empty($options[6])) {
        $myallowedForums = array_filter(array_slice($options, 6), 'b_newbb_array_filter'); // get allowed forums
        $allowedForums   = array_intersect($myallowedForums, $accessForums);
    } else {
        $allowedForums = $accessForums;
    }
    if (empty($allowedForums)) {
        return $block;
    }

    $forumCriteria   = ' AND p.forum_id IN (' . implode(',', $allowedForums) . ')';
    $approveCriteria = ' AND p.approved = 1';

    $query = 'SELECT';
    $query .= '    p.post_id, p.subject, p.post_time, p.icon, p.uid, p.poster_name,';
    if ('text' === $options[0]) {
        $query .= '    pt.dohtml, pt.dosmiley, pt.doxcode, pt.dobr, pt.post_text,';
    }
    $query .= '    f.forum_id, f.forum_name' . '    FROM ' . $GLOBALS['xoopsDB']->prefix('newbb_posts') . ' AS p ' . '    LEFT JOIN ' . $GLOBALS['xoopsDB']->prefix('newbb_forums') . ' AS f ON f.forum_id=p.forum_id';
    if ('text' === $options[0]) {
        $query .= '    LEFT JOIN ' . $GLOBALS['xoopsDB']->prefix('newbb_posts_text') . ' AS pt ON pt.post_id=p.post_id';
    }
    $query .= '    WHERE 1=1 ' . $forumCriteria . $approveCriteria . $extraCriteria . ' ORDER BY ' . $order . ' DESC';

    $result = $GLOBALS['xoopsDB']->query($query, $options[1], 0);
    if (!$result) {
        //xoops_error($GLOBALS['xoopsDB']->error());
        return $block;
    }
    $block['disp_mode'] = ('text' === $options[0]) ? 3 : $options[3]; // 0 - full view; 1 - compact view; 2 - lite view;
    $rows               = [];
    $author             = [];
    while (false !== ($row = $GLOBALS['xoopsDB']->fetchArray($result))) {
        $rows[]              = $row;
        $author[$row['uid']] = 1;
    }
    if (count($rows) < 1) {
        return $block;
    }
    require_once __DIR__ . '/../include/functions.user.php';
    $author_name = newbbGetUnameFromIds(array_keys($author), $newbbConfig['show_realname'], true);

    foreach ($rows as $arr) {
        //if ($arr['icon'] && is_file($GLOBALS['xoops']->path('images/subject/' . $arr['icon']))) {
        if (!empty($arr['icon'])) {
            $last_post_icon = '<img src="' . XOOPS_URL . '/images/subject/' . htmlspecialchars($arr['icon']) . '" alt="" />';
        } else {
            $last_post_icon = '<img src="' . XOOPS_URL . '/images/subject/icon1.gif" alt="" />';
        }
        //$topic['jump_post'] = "<a href='" . XOOPS_URL . "/modules/newbb/viewtopic.php?post_id=" . $arr['post_id'] ."#forumpost" . $arr['post_id'] . "'>" . $last_post_icon . '</a>';
        $topic               = [];
        $topic['forum_id']   = $arr['forum_id'];
        $topic['forum_name'] = $myts->htmlSpecialChars($arr['forum_name']);
        //$topic['id'] = $arr['topic_id'];

        $title = $myts->htmlSpecialChars($arr['subject']);
        if ('text' !== $options[0] && !empty($options[5])) {
            $title = xoops_substr($title, 0, $options[5]);
        }
        $topic['title']   = $title;
        $topic['post_id'] = $arr['post_id'];
        $topic['time']    = newbbFormatTimestamp($arr['post_time']);
        if (!empty($author_name[$arr['uid']])) {
            $topic_poster = $author_name[$arr['uid']];
        } else {
            $topic_poster = $myts->htmlSpecialChars($arr['poster_name'] ?: $GLOBALS['xoopsConfig']['anonymous']);
        }
        $topic['topic_poster'] = $topic_poster;

        if ('text' === $options[0]) {
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
    $block['seo_top_allforums'] = !empty($newbbConfig['do_rewrite']) ? seo_urls($seo_top_allforums) : $seo_top_allforums;
    $seo_top_allforums          = XOOPS_URL . '/' . SEO_MODULE_NAME . '/list.topic.php';
    $block['seo_top_alltopics'] = !empty($newbbConfig['do_rewrite']) ? seo_urls($seo_top_allforums) : $seo_top_allforums;
    $seo_top_allforums          = XOOPS_URL . '/' . SEO_MODULE_NAME . '/viewpost.php';
    $block['seo_top_allposts']  = !empty($newbbConfig['do_rewrite']) ? seo_urls($seo_top_allforums) : $seo_top_allforums;
    // END irmtfan remove hardcoded html in URLs

    $block['indexNav'] = (int)$options[4];

    return $block;
}

// options[0] - Citeria valid: post(by default), topic, digest, sticky
// options[1] - NumberToDisplay: any positive integer
// options[2] - TimeDuration: negative for hours, positive for days, for instance, -5 for 5 hours and 5 for 5 days
// options[3] - DisplayMode: 0-full view;1-compact view;
// options[4] - Display Navigator: 1 (by default), 0 (No)
// options[5] - Title Length : 0 - no limit
// options[6] - SelectedForumIDs: null for all

/**
 * @param $options
 * @return array
 */
function b_newbb_author_show($options)
{
    global $accessForums;
    global $newbbConfig;
    $myts  = \MyTextSanitizer::getInstance();
    $block = [];
    //    $i              = 0;
    $type          = 'topic';
    $order         = 'count';
    $extraCriteria = '';
    $time_criteria = null;
    if (!empty($options[2])) {
        require_once __DIR__ . '/../include/functions.time.php';
        $time_criteria = time() - newbbGetSinceTime($options[2]);
        $extraCriteria = ' AND topic_time > ' . $time_criteria;
    }
    switch ($options[0]) {
        case 'topic':
            break;
        case 'digest':
            $extraCriteria = ' AND topic_digest = 1';
            if (null !== $time_criteria) {
                $extraCriteria .= ' AND digest_time > ' . $time_criteria;
            }
            break;
        case 'sticky':
            $extraCriteria .= ' AND topic_sticky = 1';
            break;
        case 'post':
        default:
            $type = 'post';
            if (null !== $time_criteria) {
                $extraCriteria = ' AND post_time > ' . $time_criteria;
            }
            break;
    }

    if (!isset($accessForums)) {
        /** var Newbb\PermissionHandler $permHandler */
        $permHandler = Newbb\Helper::getInstance()->getHandler('Permission');
        if (!$accessForums = $permHandler->getForums()) {
            return $block;
        }
    }

    if (!empty($options[5])) {
        $myallowedForums = array_filter(array_slice($options, 5), 'b_newbb_array_filter'); // get allowed forums
        $allowedForums   = array_intersect($myallowedForums, $accessForums);
    } else {
        $allowedForums = $accessForums;
    }
    if (empty($allowedForums)) {
        return false;
    }

    if ('topic' === $type) {
        $forumCriteria   = ' AND forum_id IN (' . implode(',', $allowedForums) . ')';
        $approveCriteria = ' AND approved = 1';
        $query           = 'SELECT DISTINCT topic_poster AS author, COUNT(*) AS count
                    FROM ' . $GLOBALS['xoopsDB']->prefix('newbb_topics') . '
                    WHERE topic_poster>0 ' . $forumCriteria . $approveCriteria . $extraCriteria . ' GROUP BY topic_poster ORDER BY ' . $order . ' DESC';
    } else {
        $forumCriteria   = ' AND forum_id IN (' . implode(',', $allowedForums) . ')';
        $approveCriteria = ' AND approved = 1';
        $query           = 'SELECT DISTINCT uid AS author, COUNT(*) AS count
                    FROM ' . $GLOBALS['xoopsDB']->prefix('newbb_posts') . '
                    WHERE uid > 0 ' . $forumCriteria . $approveCriteria . $extraCriteria . ' GROUP BY uid ORDER BY ' . $order . ' DESC';
    }

    $result = $GLOBALS['xoopsDB']->query($query, $options[1], 0);
    if (!$result) {
        //xoops_error($GLOBALS['xoopsDB']->error());
        return $block;
    }
    $author = [];
    while (false !== ($row = $GLOBALS['xoopsDB']->fetchArray($result))) {
        $author[$row['author']]['count'] = $row['count'];
    }
    if (count($author) < 1) {
        return $block;
    }
    require_once __DIR__ . '/../include/functions.user.php';
    $author_name = newbbGetUnameFromIds(array_keys($author), $newbbConfig['show_realname']);
    foreach (array_keys($author) as $uid) {
        $author[$uid]['name'] = $myts->htmlSpecialChars($author_name[$uid]);
    }
    $block['authors']   =& $author;
    $block['disp_mode'] = $options[3]; // 0 - full view; 1 - lite view;
    $block['indexNav']  = (int)$options[4];

    return $block;
}

/**
 * @param $options
 * @return string
 */
function b_newbb_edit($options)
{
    require_once __DIR__ . '/../include/functions.forum.php';

    $form = _MB_NEWBB_CRITERIA . "<select name='options[0]'>";
    $form .= "<option value='time'";
    if ('time' === $options[0]) {
        $form .= " selected='selected' ";
    }
    $form .= '>' . _MB_NEWBB_CRITERIA_TIME . '</option>';
    $form .= '</select>';
    $form .= '<br>' . _MB_NEWBB_DISPLAY . "<input type='text' name='options[1]' value='" . $options[1] . "' />";
    $form .= '<br>' . _MB_NEWBB_TIME . "<input type='text' name='options[2]' value='" . $options[2] . "' />";
    $form .= '<br>&nbsp;&nbsp;&nbsp;&nbsp;<small>' . _MB_NEWBB_TIME_DESC . '</small>';
    $form .= '<br>' . _MB_NEWBB_DISPLAYMODE . "<input type='radio' name='options[3]' value='0'";
    if (0 == $options[3]) {
        $form .= ' checked';
    }
    $form .= ' />&nbsp;' . _MB_NEWBB_DISPLAYMODE_FULL . "<input type='radio' name='options[3]' value='1'";
    if (1 == $options[3]) {
        $form .= ' checked';
    }
    $form .= ' />&nbsp;' . _MB_NEWBB_DISPLAYMODE_COMPACT . "<input type='radio' name='options[3]' value='2'";
    if (2 == $options[3]) {
        $form .= ' checked';
    }
    $form .= ' />&nbsp;' . _MB_NEWBB_DISPLAYMODE_LITE;

    $form .= '<br>' . _MB_NEWBB_INDEXNAV . '<input type="radio" name="options[4]" value="1"';
    if (1 == $options[4]) {
        $form .= ' checked';
    }
    $form .= ' />' . _YES . '<input type="radio" name="options[4]" value="0"';
    if (0 == $options[4]) {
        $form .= ' checked';
    }
    $form .= ' />' . _NO;

    $form .= '<br>' . _MB_NEWBB_TITLE_LENGTH . "<input type='text' name='options[5]' value='" . $options[5] . "' />";

    $form .= '<br><br>' . _MB_NEWBB_FORUMLIST;

    $optionsForum = array_filter(array_slice($options, 6), 'b_newbb_array_filter'); // get allowed forums
    $isAll        = (0 === count($optionsForum) || empty($optionsForum[0]));
    $form         .= '<br>&nbsp;&nbsp;<select name="options[]" multiple="multiple">';
    $form         .= '<option value="0" ';
    if ($isAll) {
        $form .= ' selected';
    }
    $form .= '>' . _ALL . '</option>';
    $form .= newbbForumSelectBox($optionsForum);
    $form .= '</select><br>';

    return $form;
}

/**
 * @param $options
 * @return string
 */
function b_newbb_topic_edit($options)
{
    require_once __DIR__ . '/../include/functions.forum.php';
    $form = _MB_NEWBB_CRITERIA . "<select name='options[0]'>";
    $form .= "<option value='time'";
    if ('time' === $options[0]) {
        $form .= " selected='selected' ";
    }
    $form .= '>' . _MB_NEWBB_CRITERIA_TIME . '</option>';
    $form .= "<option value='views'";
    if ('views' === $options[0]) {
        $form .= " selected='selected' ";
    }
    $form .= '>' . _MB_NEWBB_CRITERIA_VIEWS . '</option>';
    $form .= "<option value='replies'";
    if ('replies' === $options[0]) {
        $form .= " selected='selected' ";
    }
    $form .= '>' . _MB_NEWBB_CRITERIA_REPLIES . '</option>';
    $form .= "<option value='digest'";
    if ('digest' === $options[0]) {
        $form .= " selected='selected' ";
    }
    $form .= '>' . _MB_NEWBB_CRITERIA_DIGEST . '</option>';
    $form .= "<option value='sticky'";
    if ('sticky' === $options[0]) {
        $form .= " selected='selected' ";
    }
    $form .= '>' . _MB_NEWBB_CRITERIA_STICKY . '</option>';
    $form .= '</select>';
    $form .= '<br>' . _MB_NEWBB_DISPLAY . "<input type='text' name='options[1]' value='" . $options[1] . "' />";
    $form .= '<br>' . _MB_NEWBB_TIME . "<input type='text' name='options[2]' value='" . $options[2] . "' />";
    $form .= '<br>&nbsp;&nbsp;&nbsp;&nbsp;<small>' . _MB_NEWBB_TIME_DESC . '</small>';
    $form .= '<br>' . _MB_NEWBB_DISPLAYMODE . "<input type='radio' name='options[3]' value='0'";
    if (0 == $options[3]) {
        $form .= ' checked';
    }
    $form .= ' />&nbsp;' . _MB_NEWBB_DISPLAYMODE_FULL . "<input type='radio' name='options[3]' value='1'";
    if (1 == $options[3]) {
        $form .= ' checked';
    }
    $form .= ' />&nbsp;' . _MB_NEWBB_DISPLAYMODE_COMPACT . "<input type='radio' name='options[3]' value='2'";
    if (2 == $options[3]) {
        $form .= ' checked';
    }
    $form .= ' />&nbsp;' . _MB_NEWBB_DISPLAYMODE_LITE;

    $form .= '<br>' . _MB_NEWBB_INDEXNAV . '<input type="radio" name="options[4]" value="1"';
    if (1 == $options[4]) {
        $form .= ' checked';
    }
    $form .= ' />' . _YES . '<input type="radio" name="options[4]" value="0"';
    if (0 == $options[4]) {
        $form .= ' checked';
    }
    $form .= ' />' . _NO;

    $form .= '<br>' . _MB_NEWBB_TITLE_LENGTH . "<input type='text' name='options[5]' value='" . $options[5] . "' />";

    $form .= '<br><br>' . _MB_NEWBB_FORUMLIST;

    $optionsForum = array_filter(array_slice($options, 6), 'b_newbb_array_filter'); // get allowed forums

    $isAll = (0 === count($optionsForum) || empty($optionsForum[0]));
    $form  .= '<br>&nbsp;&nbsp;<select name="options[]" multiple="multiple">';
    $form  .= '<option value="0" ';
    if ($isAll) {
        $form .= ' selected="selected"';
    }
    $form .= '>' . _ALL . '</option>';
    $form .= newbbForumSelectBox($optionsForum);
    $form .= '</select><br>';

    return $form;
}

/**
 * @param $options
 * @return string
 */
function b_newbb_post_edit($options)
{
    require_once __DIR__ . '/../include/functions.forum.php';
    $form = _MB_NEWBB_CRITERIA . "<select name='options[0]'>";
    $form .= "<option value='title'";
    if ('title' === $options[0]) {
        $form .= " selected='selected' ";
    }
    $form .= '>' . _MB_NEWBB_CRITERIA_TITLE . '</option>';
    $form .= "<option value='text'";
    if ('text' === $options[0]) {
        $form .= " selected='selected' ";
    }
    $form .= '>' . _MB_NEWBB_CRITERIA_TEXT . '</option>';
    $form .= '</select>';
    $form .= '<br>' . _MB_NEWBB_DISPLAY . "<input type='text' name='options[1]' value='" . $options[1] . "' />";
    $form .= '<br>' . _MB_NEWBB_TIME . "<input type='text' name='options[2]' value='" . $options[2] . "' />";
    $form .= '<br>&nbsp;&nbsp;&nbsp;&nbsp;<small>' . _MB_NEWBB_TIME_DESC . '</small>';
    $form .= '<br>' . _MB_NEWBB_DISPLAYMODE . "<input type='radio' name='options[3]' value='0'";
    if (0 == $options[3]) {
        $form .= ' checked';
    }
    $form .= ' />&nbsp;' . _MB_NEWBB_DISPLAYMODE_FULL . "<input type='radio' name='options[3]' value='1'";
    if (1 == $options[3]) {
        $form .= ' checked';
    }
    $form .= ' />&nbsp;' . _MB_NEWBB_DISPLAYMODE_COMPACT . "<input type='radio' name='options[3]' value='2'";
    if (2 == $options[3]) {
        $form .= ' checked';
    }
    $form .= ' />&nbsp;' . _MB_NEWBB_DISPLAYMODE_LITE;

    $form .= '<br>' . _MB_NEWBB_INDEXNAV . '<input type="radio" name="options[4]" value="1"';
    if (1 == $options[4]) {
        $form .= ' checked';
    }
    $form .= ' />' . _YES . '<input type="radio" name="options[4]" value="0"';
    if (0 == $options[4]) {
        $form .= ' checked';
    }
    $form .= ' />' . _NO;

    $form .= '<br>' . _MB_NEWBB_TITLE_LENGTH . "<input type='text' name='options[5]' value='" . $options[5] . "' />";

    $form .= '<br><br>' . _MB_NEWBB_FORUMLIST;

    $optionsForum = array_filter(array_slice($options, 6), 'b_newbb_array_filter'); // get allowed forums
    $isAll        = (0 === count($optionsForum) || empty($optionsForum[0]));
    $form         .= '<br>&nbsp;&nbsp;<select name="options[]" multiple="multiple">';
    $form         .= '<option value="0" ';
    if ($isAll) {
        $form .= ' selected="selected"';
    }
    $form .= '>' . _ALL . '</option>';
    $form .= newbbForumSelectBox($optionsForum);
    $form .= '</select><br>';

    return $form;
}

/**
 * @param $options
 * @return string
 */
function b_newbb_author_edit($options)
{
    require_once __DIR__ . '/../include/functions.forum.php';
    $form = _MB_NEWBB_CRITERIA . "<select name='options[0]'>";
    $form .= "<option value='post'";
    if ('post' === $options[0]) {
        $form .= " selected='selected' ";
    }
    $form .= '>' . _MB_NEWBB_CRITERIA_POST . '</option>';
    $form .= "<option value='topic'";
    if ('topic' === $options[0]) {
        $form .= " selected='selected' ";
    }
    $form .= '>' . _MB_NEWBB_CRITERIA_TOPIC . '</option>';
    $form .= "<option value='digest'";
    if ('digest' === $options[0]) {
        $form .= " selected='selected' ";
    }
    $form .= '>' . _MB_NEWBB_CRITERIA_DIGESTS . '</option>';
    $form .= "<option value='sticky'";
    if ('sticky' === $options[0]) {
        $form .= " selected='selected' ";
    }
    $form .= '>' . _MB_NEWBB_CRITERIA_STICKYS . '</option>';
    $form .= '</select>';
    $form .= '<br>' . _MB_NEWBB_DISPLAY . "<input type='text' name='options[1]' value='" . $options[1] . "' />";
    $form .= '<br>' . _MB_NEWBB_TIME . "<input type='text' name='options[2]' value='" . $options[2] . "' />";
    $form .= '<br>&nbsp;&nbsp;&nbsp;&nbsp;<small>' . _MB_NEWBB_TIME_DESC . '</small>';
    $form .= '<br>' . _MB_NEWBB_DISPLAYMODE . "<input type='radio' name='options[3]' value='0'";
    if (0 == $options[3]) {
        $form .= ' checked';
    }
    $form .= ' />&nbsp;' . _MB_NEWBB_DISPLAYMODE_COMPACT . "<input type='radio' name='options[3]' value='1'";
    if (1 == $options[3]) {
        $form .= ' checked';
    }
    $form .= ' />&nbsp;' . _MB_NEWBB_DISPLAYMODE_LITE;

    $form .= '<br>' . _MB_NEWBB_INDEXNAV . '<input type="radio" name="options[4]" value="1"';
    if (1 == $options[4]) {
        $form .= ' checked';
    }
    $form .= ' />' . _YES . '<input type="radio" name="options[4]" value="0"';
    if (0 == $options[4]) {
        $form .= ' checked';
    }
    $form .= ' />' . _NO;

    $form .= '<br><br>' . _MB_NEWBB_FORUMLIST;

    $optionsForum = array_filter(array_slice($options, 5), 'b_newbb_array_filter'); // get allowed forums
    $isAll        = (0 === count($optionsForum) || empty($optionsForum[0]));
    $form         .= '<br>&nbsp;&nbsp;<select name="options[]" multiple="multiple">';
    $form         .= '<option value="0" ';
    if ($isAll) {
        $form .= ' selected="selected"';
    }
    $form .= '>' . _ALL . '</option>';
    $form .= newbbForumSelectBox($optionsForum);
    $form .= '</select><br>';

    return $form;
}

/**
 * @param $options
 * @return bool
 */
function b_newbb_custom($options)
{
    // if no newbb module block set, we have to include the language file
    if (is_readable($GLOBALS['xoops']->path('modules/newbb/language/' . $GLOBALS['xoopsConfig']['language'] . '/blocks.php'))) {
        require_once $GLOBALS['xoops']->path('modules/newbb/language/' . $GLOBALS['xoopsConfig']['language'] . '/blocks.php');
    } else {
        require_once $GLOBALS['xoops']->path('modules/newbb/language/english/blocks.php');
    }

    $options = explode('|', $options);
    $block   = b_newbb_show($options);
    if (count($block['topics']) < 1) {
        return false;
    }

    $tpl = new \XoopsTpl();
    $tpl->assign('block', $block);
    $tpl->display('db:newbb_block.tpl');
}

/**
 * @param $options
 * @return bool
 */
function b_newbb_custom_topic($options)
{

    // if no newbb module block set, we have to include the language file
    if (is_readable($GLOBALS['xoops']->path('modules/newbb/language/' . $GLOBALS['xoopsConfig']['language'] . '/blocks.php'))) {
        require_once $GLOBALS['xoops']->path('modules/newbb/language/' . $GLOBALS['xoopsConfig']['language'] . '/blocks.php');
    } else {
        require_once $GLOBALS['xoops']->path('modules/newbb/language/english/blocks.php');
    }

    $options = explode('|', $options);
    $block   = b_newbb_topic_show($options);
    if (count($block['topics']) < 1) {
        return false;
    }

    $tpl = new \XoopsTpl();
    $tpl->assign('block', $block);
    $tpl->display('db:newbb_block_topic.tpl');
}

/**
 * @param $options
 * @return bool
 */
function b_newbb_custom_post($options)
{

    // if no newbb module block set, we have to include the language file
    if (is_readable($GLOBALS['xoops']->path('modules/newbb/language/' . $GLOBALS['xoopsConfig']['language'] . '/blocks.php'))) {
        require_once $GLOBALS['xoops']->path('modules/newbb/language/' . $GLOBALS['xoopsConfig']['language'] . '/blocks.php');
    } else {
        require_once $GLOBALS['xoops']->path('modules/newbb/language/english/blocks.php');
    }

    $options = explode('|', $options);
    $block   = b_newbb_post_show($options);
    if (count($block['topics']) < 1) {
        return false;
    }

    $tpl = new \XoopsTpl();
    $tpl->assign('block', $block);
    $tpl->display('db:newbb_block_post.tpl');
}

/**
 * @param $options
 * @return bool
 */
function b_newbb_custom_author($options)
{
    // if no newbb module block set, we have to include the language file
    if (is_readable($GLOBALS['xoops']->path('modules/newbb/language/' . $GLOBALS['xoopsConfig']['language'] . '/blocks.php'))) {
        require_once $GLOBALS['xoops']->path('modules/newbb/language/' . $GLOBALS['xoopsConfig']['language'] . '/blocks.php');
    } else {
        require_once $GLOBALS['xoops']->path('modules/newbb/language/english/blocks.php');
    }

    $options = explode('|', $options);
    $block   = b_newbb_author_show($options);
    if (count($block['authors']) < 1) {
        return false;
    }

    $tpl = new \XoopsTpl();
    $tpl->assign('block', $block);
    $tpl->display('db:newbb_block_author.tpl');
}

// irmtfan add local stylesheet and js footer.php
require_once $GLOBALS['xoops']->path('modules/newbb/footer.php');
