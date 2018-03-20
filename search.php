<?php

use Xmf\Metagen;
use Xmf\Highlighter;
use Xmf\Request;

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

require_once __DIR__ . '/header.php';
xoops_loadLanguage('search');
/** @var \XoopsConfigHandler $configHandler */
$configHandler     = xoops_getHandler('config');
$xoopsConfigSearch = $configHandler->getConfigsByCat(XOOPS_CONF_SEARCH);
if (1 !== $xoopsConfigSearch['enable_search']) {
    redirect_header(XOOPS_URL . '/modules/newbb/index.php', 2, _MD_NEWBB_SEARCHDISABLED);
}

$GLOBALS['xoopsConfig']['module_cache'][$xoopsModule->getVar('mid')] = 0;
$xoopsOption['template_main']                                        = 'newbb_search.tpl';
require_once $GLOBALS['xoops']->path('header.php');

require_once __DIR__ . '/include/functions.render.php';
require_once __DIR__ . '/include/functions.forum.php';
require_once __DIR__ . '/include/functions.time.php';

require_once $GLOBALS['xoops']->path('modules/newbb/include/search.inc.php');
$limit = $GLOBALS['xoopsModuleConfig']['topics_per_page'];

$queries              = [];
$andor                = '';
$start                = 0;
$uid                  = 0;
$forum                = 0;
$sortby               = 'p.post_time'; // irmtfan remove DESC
$criteriaExtra        = new \CriteriaCompo(); // irmtfan new \Criteria
$searchin             = 'both';
$sort                 = '';
$since                = Request::getInt('since', null);
$next_search['since'] = $since;
$term                 = Request::getString('term', null);
$uname                = Request::getString('uname', null);
// irmtfan add select parameters
$selectlength = Request::getInt('selectlength', 200);

// irmtfan assign default values to variables
$show_search     = 'post_text';
$search_username = trim($uname);

if ($GLOBALS['xoopsModuleConfig']['wol_enabled']) {
    //    /** @var Newbb\OnlineHandler $onlineHandler */
    //    $onlineHandler = Newbb\Helper::getInstance()->getHandler('Online');
    $onlineHandler->init(0);
}

$xoopsTpl->assign('forumindex', sprintf(_MD_NEWBB_FORUMINDEX, htmlspecialchars($GLOBALS['xoopsConfig']['sitename'], ENT_QUOTES)));
//$xoopsTpl->assign("img_folder", newbbDisplayImage($forumImage['topic']));

if (!empty($uname) || Request::getString('submit', '') || !empty($term)) {
    // irmtfan filter positive numbers
    $selectlength = !empty($selectlength) ? abs($selectlength) : 200;
    // irmtfan add select parameters for next search
    $next_search['selectlength'] = $selectlength;

    $start = Request::getInt('start', 0);
    $forum = Request::getInt('forum', null);
    if (empty($forum) || 'all' === $forum || (is_array($forum) && in_array('all', $forum, true))) {
        $forum = [];
    } elseif (!is_array($forum)) {
        $forum = array_map('intval', explode('|', $forum));
    }
    $next_search['forum'] = implode('|', $forum);
    // START irmtfan topic search
    $topic                = Request::getString('topic', null);
    $next_search['topic'] = $topic;
    // END irmtfan topic search
    // START irmtfan add show search
    $show_search                = Request::getString('show_search', 'post_text');
    $next_search['show_search'] = $show_search;
    // START irmtfan add show search

    $addterms             = Request::getString('andor', 'AND');
    $next_search['andor'] = $addterms;
    $andor                = strtoupper($addterms);
    if (!in_array($addterms, ['OR', 'AND'], true)) {
        $andor = 'AND';
    }

    $uname_required       = false;
    $next_search['uname'] = $search_username;
    if (!empty($search_username)) {
        $uname_required  = true;
        $search_username = $GLOBALS['xoopsDB']->escape($search_username);
        if (!$result = $GLOBALS['xoopsDB']->query('SELECT uid FROM ' . $GLOBALS['xoopsDB']->prefix('users') . " WHERE uname LIKE '%$search_username%'")) {
            redirect_header(XOOPS_URL . '/search.php', 1, _MD_NEWBB_ERROROCCURED);
        }
        $uid = [];
        while (false !== ($row = $GLOBALS['xoopsDB']->fetchArray($result))) {
            $uid[] = $row['uid'];
        }
    } else {
        $uid = 0;
    }

    $next_search['term'] = htmlspecialchars($term, ENT_QUOTES);
    $query               = trim($term);

    if ('EXACT' !== $andor) {
        $ignored_queries = []; // holds keywords that are shorter than allowed minimum length
        $temp_queries    = str_getcsv($query, ' ', '"');
        foreach ($temp_queries as $q) {
            $q = trim($q);
            if (strlen($q) >= $xoopsConfigSearch['keyword_min']) {
                $queries[] = $q;
            } else {
                $ignored_queries[] = $q;
            }
        }
        if (!$uname_required && 0 === count($queries)) {
            redirect_header(XOOPS_URL . '/search.php', 2, sprintf(_SR_KEYTOOSHORT, $xoopsConfigSearch['keyword_min']));
        }
    } else {
        //$query = trim($query);
        if (!$uname_required && (strlen($query) < $xoopsConfigSearch['keyword_min'])) {
            redirect_header(XOOPS_URL . '/search.php', 2, sprintf(_SR_KEYTOOSHORT, $xoopsConfigSearch['keyword_min']));
        }
        $queries = [$query];
    }

    // entries must be lowercase
    $allowed = ['p.post_time', 'p.subject']; // irmtfan just post time and subject

    $sortby                = Request::getString('sortby', 'p.post_time');
    $next_search['sortby'] = $sortby;
    //$sortby = (in_array(strtolower($sortby), $allowed)) ? $sortby :  't.topic_last_post_id';
    $sortby                  = in_array(strtolower($sortby), $allowed) ? $sortby : 'p.post_time';
    $searchin                = Request::getString('searchin', 'both');
    $next_search['searchin'] = $searchin;
    // START irmtfan use criteria - add since and topic search
    if (!empty($since)) {
        $criteriaExtra->add(new \Criteria('p.post_time', time() - newbbGetSinceTime($since), '>='), 'OR');
    }
    if (is_numeric($topic) && !empty($topic)) {
        $criteriaExtra->add(new \Criteria('p.topic_id', $topic), 'OR');
    }
    // END irmtfan use criteria -  add since and topic search

    if ($uname_required && (!$uid || count($uid) < 1)) {
        $results = [];
    } // irmtfan bug fix array()
    else {
        $results = newbb_search($queries, $andor, $limit, $start, $uid, $forum, $sortby, $searchin, $criteriaExtra);
    } // irmtfan $criteriaExtra

    $search_info_keywords = Highlighter::apply($myts->htmlSpecialChars($term, ENT_QUOTES), implode(' ', $queries), '<mark>', '</mark>');
    $num_results          = count($results);
    if ($num_results < 1) {
        $xoopsTpl->assign('lang_nomatch', _SR_NOMATCH);
    } else {
        $skipresults = 0;
        foreach ($results as $row) {
            $post_text_select    = '';
            $post_subject_select = Highlighter::apply($queries, $row['title'], '<mark>', '</mark>');
            if ('post_text' === $show_search) {
                $post_text_select = Metagen::getSearchSummary($row['post_text'], $queries, $selectlength);
                $post_text_select = Highlighter::apply($queries, $post_text_select, '<mark>', '</mark>');
            }
            // if no text remained after select text continue
            if (empty($post_text_select) && empty($post_subject_select)) {
                ++$skipresults;
                continue;
            }
            $xoopsTpl->append('results', [
                'forum_name' => $row['forum_name'],
                'forum_link' => $row['forum_link'],
                'link'       => $row['link'],
                'title'      => $post_subject_select,
                'poster'     => $row['poster'],
                'post_time'  => formatTimestamp($row['time'], 'm'),
                'post_text'  => $post_text_select
            ]);
        }
        unset($results);

        if (count($next_search) > 0) {
            $items = [];
            foreach ($next_search as $para => $val) {
                $items[] = "{$para}=" . urlencode($val);
            }
            if (count($items) > 0) {
                $paras = implode('&', $items);
            }
            unset($next_search, $items);
        }
        $search_url = XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname') . '/search.php?' . $paras;
        // irmtfan remove to have just one query and appropriate next and prev links
        //$next_results = newbb_search($queries, $andor, 1, $start + $limit, $uid, $forum, $sortby, $searchin, $subquery);
        //$next_count = count($next_results);
        //$has_next = false;
        //if (is_array($next_results) && $next_count >0) {
        //$has_next = true;
        //}
        // irmtfan if $results < $limit => it is impossible to have next
        if ($num_results == $limit) {
            $next            = $start + $limit;
            $queries         = implode(',', $queries);
            $search_url_next = htmlspecialchars($search_url . "&direction=next&start={$next}");
            $search_next     = '<a href="' . $search_url_next . '">' . _SR_NEXT . '</a>';
            $xoopsTpl->assign('search_next', $search_next);
            $xoopsTpl->assign('search_next_url', $search_url_next);
        }
        if ($start > 0) {
            $prev            = $start - $limit;
            $search_url_prev = htmlspecialchars($search_url . "&direction=previous&start={$prev}");
            $search_prev     = '<a href="' . $search_url_prev . '">' . _SR_PREVIOUS . '</a>';
            $xoopsTpl->assign('search_prev', $search_prev);
            $xoopsTpl->assign('search_prev_url', $search_url_prev);
        }
        // irmtfan if all results skipped then redirect to the next/previous page
        if ($num_results == $skipresults) {
            $direction           = Request::getString('direction', 'next');
            $search_url_redirect = ('next' === strtolower($direction)) ? $search_url_next : $search_url_prev;
            redirect_header($search_url_redirect, 1, constant(strtoupper("_SR_{$direction}")));
        }
    }
    $search_info = _SR_KEYWORDS . ': ' . $search_info_keywords;
    if ($uname_required) {
        if ($search_info) {
            $search_info .= '<br>';
        }
        $search_info .= _MD_NEWBB_USERNAME . ': ' . $myts->htmlSpecialChars($search_username);
    }
    // add num_results
    $search_info .= '<br>' . sprintf(_SR_SHOWING, $start + 1, $start + $num_results);
    // if any result skip show the counter
    if (!empty($skipresults)) {
        $search_info .= ' - ' . sprintf(_SR_FOUND, $num_results - $skipresults);
    }
    $xoopsTpl->assign('search_info', $search_info);
}
// assign template vars for search
/* term */
$xoopsTpl->assign('search_term', htmlspecialchars($term, ENT_QUOTES));

/* andor */
$andor_select = '<select name="andor" id="andor" class="form-control">';
$andor_select .= '<option value="OR"';
if ('OR' === $andor) {
    $andor_select .= ' selected="selected"';
}
$andor_select .= '>' . _SR_ANY . '</option>';
$andor_select .= '<option value="AND"';
if ('AND' === $andor || empty($andor)) {
    $andor_select .= ' selected="selected"';
}
$andor_select .= '>' . _SR_ALL . '</option>';
$andor_select .= '</select>';
$xoopsTpl->assign('andor_selection_box', $andor_select);

/* forum */
$select_forum = '<select class="form-control" name="forum[]" id="forum" size="5" multiple="multiple">';
$select_forum .= '<option value="all">' . _MD_NEWBB_SEARCHALLFORUMS . '</option>';
$select_forum .= newbbForumSelectBox($forum);
$select_forum .= '</select>';
$xoopsTpl->assign_by_ref('forum_selection_box', $select_forum);

/* searchin */
$searchin_select = '';
$searchin_select .= '<label class="radio-inline"><input type="radio" name="searchin" value="title"';
if ('title' === $searchin) {
    $searchin_select .= ' checked';
}
$searchin_select .= ' />' . _MD_NEWBB_SUBJECT . ' </label>';
$searchin_select .= '<label class="radio-inline"><input type="radio" name="searchin" value="text"';
if ('text' === $searchin) {
    $searchin_select .= ' checked';
}
$searchin_select .= ' />' . _MD_NEWBB_BODY . ' </label>';
$searchin_select .= '<label class="radio-inline"><input type="radio" name="searchin" value="both"';
if ('both' === $searchin || empty($searchin)) {
    $searchin_select .= ' checked';
}
$searchin_select .= ' />' . _MD_NEWBB_SUBJECT . ' & ' . _MD_NEWBB_BODY . ' </label>';
$xoopsTpl->assign('searchin_radio', $searchin_select);

/* show_search */
$show_search_select = '';
$show_search_select .= '<label class="radio-inline"><input type="radio" name="show_search" value="post"';
if ('post' === $show_search) {
    $show_search_select .= ' checked';
}
$show_search_select .= ' />' . _MD_NEWBB_POSTS . ' </label>';
$show_search_select .= '<label class="radio-inline"><input type="radio" name="show_search" value="post_text"';
if ('post_text' === $show_search || empty($show_search)) {
    $show_search_select .= ' checked';
}
$show_search_select .= ' />' . _MD_NEWBB_SEARCHPOSTTEXT . ' </label>';
$xoopsTpl->assign('show_search_radio', $show_search_select);

/* author */
$xoopsTpl->assign('author_select', $search_username);

/* sortby */
$sortby_select = '<select name="sortby" id="sortby" class="form-control">';
$sortby_select .= '<option value=\'p.post_time\'';
if ('p.post_time' === $sortby || empty($sortby)) {
    $sortby_select .= ' selected=\'selected\'';
}
$sortby_select .= '>' . _MD_NEWBB_DATE . '</option>';
$sortby_select .= '<option value=\'p.subject\'';
if ('p.subject' === $sortby) {
    $sortby_select .= ' selected="selected"';
}
$sortby_select .= '>' . _MD_NEWBB_TOPIC . '</option>';
$sortby_select .= '</select>';
$xoopsTpl->assign('sortby_selection_box', $sortby_select);

/* selectlength */
$xoopsTpl->assign('selectlength_select', $selectlength);

// irmtfan get since from the user for selction box
$since        = Request::getInt('since', $GLOBALS['xoopsModuleConfig']['since_default']);
$select_since = newbbSinceSelectBox($since);
$xoopsTpl->assign_by_ref('since_selection_box', $select_since);

if ($xoopsConfigSearch['keyword_min'] > 0) {
    $xoopsTpl->assign('search_rule', sprintf(_SR_KEYIGNORE, $xoopsConfigSearch['keyword_min']));
}
// irmtfan move to footer.php
require_once __DIR__ . '/footer.php';
include $GLOBALS['xoops']->path('footer.php');
