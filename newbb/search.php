<?php
// $Id: search.php 62 2012-08-17 10:15:26Z alfred $
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
include_once __DIR__ . "/header.php";
xoops_loadLanguage("search");
$config_handler    =& xoops_gethandler('config');
$xoopsConfigSearch =& $config_handler->getConfigsByCat(XOOPS_CONF_SEARCH);
if ($xoopsConfigSearch['enable_search'] != 1) {
    redirect_header(XOOPS_URL . '/modules/newbb/index.php', 2, _MD_NEWBB_SEARCHDISABLED);
}

$xoopsConfig['module_cache'][$xoopsModule->getVar('mid')] = 0;
$xoopsOption['template_main']                             = 'newbb_search.tpl';
// irmtfan include header.php after defining $xoopsOption['template_main']
include_once $GLOBALS['xoops']->path('header.php');

mod_loadFunctions("render", "newbb");
mod_loadFunctions("forum", "newbb");
mod_loadFunctions("time", "newbb");
mod_loadFunctions("text", "newbb"); // irmtfan add text functions

include_once $GLOBALS['xoops']->path('modules/newbb/include/search.inc.php');
$limit = $xoopsModuleConfig['topics_per_page'];

$queries              = array();
$andor                = "";
$start                = 0;
$uid                  = 0;
$forum                = 0;
$sortby               = 'p.post_time'; // irmtfan remove DESC
$criteriaExtra        = new CriteriaCompo(); // irmtfan new criteria
$searchin             = "both";
$sort                 = "";
$since                = XoopsRequest::getInt('since', XoopsRequest::getInt('since', null, 'POST'), 'GET');
$next_search['since'] = $since;
$term                 = XoopsRequest::getString('term', XoopsRequest::getString('term', null, 'POST'), 'GET');
$uname                = XoopsRequest::getString('uname', XoopsRequest::getString('uname', null, 'POST'), 'GET');
// irmtfan add select parameters
$selectstartlag = XoopsRequest::getInt('selectstartlag', 100, 'GET');
$selectlength   = XoopsRequest::getInt('selectlength', 200, 'POST');
$selecthtml = XoopsRequest::getInt('selecthtml', '', 'GET') ? (XoopsRequest::getInt('selecthtml', '', 'GET') ? true : false) : true; // isset($_GET['selecthtml']) ? (!empty($_GET['selecthtml']) ? true : false) : true;

$selectexclude  = XoopsRequest::getString('selectexclude', '', 'GET');
$selectexclude  = newbb_str2array($selectexclude);
// irmtfan assign default values to variables
$show_search     = "post_text";
$search_username = trim($uname);

if ($xoopsModuleConfig['wol_enabled']) {
    $online_handler =& xoops_getmodulehandler('online', 'newbb');
    $online_handler->init(0);
}

$xoopsTpl->assign("forumindex", sprintf(_MD_FORUMINDEX, htmlspecialchars($xoopsConfig['sitename'], ENT_QUOTES)));
//$xoopsTpl->assign("img_folder", newbbDisplayImage($forumImage['topic']));

if (XoopsRequest::getString('submit', '') || !empty($uname) || !empty($term)) {
    // irmtfan filter positive numbers
    $selectstartlag = !empty($selectstartlag) ? abs($selectstartlag) : 100;
    $selectlength   = !empty($selectlength) ? abs($selectlength) : 200;
    // irmtfan add select parameters for next search
    $next_search['selectstartlag'] = $selectstartlag;
    $next_search['selectlength']   = $selectlength;
    $next_search['selecthtml']     = $selecthtml;
    $next_search['selectexclude']  = implode(", ", $selectexclude);

    $start = XoopsRequest::getInt('start', 0, 'GET');
    $forum = XoopsRequest::getInt('forum', XoopsRequest::getInt('forum', null, 'POST'), 'GET');
    if (empty($forum) || $forum == 'all' or (is_array($forum) and in_array('all', $forum))) {
        $forum = array();
    } elseif (!is_array($forum)) {
        $forum = array_map("intval", explode("|", $forum));
    }
    $next_search['forum'] = implode("|", $forum);
    // START irmtfan topic search
    $topic                = XoopsRequest::getString('topic', XoopsRequest::getString('topic', null, 'POST'), 'GET');
    $next_search['topic'] = $topic;
    // END irmtfan topic search
    // START irmtfan add show search
    $show_search                =  XoopsRequest::getString('show_search', XoopsRequest::getString('show_search', 'post_text', 'GET'), 'POST');
    $next_search['show_search'] = $show_search;
    // START irmtfan add show search

    $addterms             = XoopsRequest::getString('andor', XoopsRequest::getString('andor', '', 'GET'), 'POST');
    $next_search['andor'] = $addterms;
    if (!in_array(strtolower($addterms), array("or", "and", "exact"))) {
        // irmtfan change default to AND
        $andor = "AND";
    } else {
        $andor = strtoupper($addterms);
    }

    $uname_required       = false;
    $next_search['uname'] = $search_username;
    if (!empty($search_username)) {
        $uname_required  = true;
        $search_username = $myts->addSlashes($search_username);
        if (!$result = $xoopsDB->query("SELECT uid FROM " . $xoopsDB->prefix("users") . " WHERE uname LIKE '%$search_username%'")) {
            redirect_header('search.php', 1, _MD_ERROROCCURED);
        }
        $uid = array();
        while ($row = $xoopsDB->fetchArray($result)) {
            $uid[] = $row['uid'];
        }
    } else {
        $uid = 0;
    }

    $next_search['term'] = $term;
    $query               = trim($term);

    if ($andor != "EXACT") {
        $ignored_queries = array(); // holds kewords that are shorter than allowed minmum length
        $temp_queries    = preg_split('/[\s,]+/', $query);
        foreach ($temp_queries as $q) {
            $q = trim($q);
            if (strlen($q) >= $xoopsConfigSearch['keyword_min']) {
                $queries[] = $myts->addSlashes($q);
            } else {
                $ignored_queries[] = $myts->addSlashes($q);
            }
        }
        if (!$uname_required && count($queries) == 0) {
            redirect_header('search.php', 2, sprintf(_SR_KEYTOOSHORT, $xoopsConfigSearch['keyword_min']));
        }
    } else {
        //$query = trim($query);
        if (!$uname_required && (strlen($query) < $xoopsConfigSearch['keyword_min'])) {
            redirect_header('search.php', 2, sprintf(_SR_KEYTOOSHORT, $xoopsConfigSearch['keyword_min']));
        }
        $queries = array($myts->addSlashes($query));
    }

    // entries must be lowercase
    $allowed = array('p.post_time', 'p.subject'); // irmtfan just post time and subject

    $sortby                = XoopsRequest::getString('sortby', XoopsRequest::getString('sortby', null, 'POST'), 'GET');
    $next_search['sortby'] = $sortby;
    //$sortby = (in_array(strtolower($sortby), $allowed)) ? $sortby :  't.topic_last_post_id';
    $sortby                  = (in_array(strtolower($sortby), $allowed)) ? $sortby : 'p.post_time';
    $searchin                = XoopsRequest::getString('searchin', XoopsRequest::getString('searchin', 'both', 'GET'), 'POST');
    $next_search['searchin'] = $searchin;
    // START irmtfan use criteria - add since and topic search
    if (!empty($since)) {
        $criteriaExtra->add(new Criteria('p.post_time', (time() - newbb_getSinceTime($since)), '>='), 'OR');
    }
    if (is_numeric($topic) && !empty($topic)) {
        $criteriaExtra->add(new Criteria('p.topic_id', $topic), 'OR');
    }
    // END irmtfan use criteria -  add since and topic search

    if ($uname_required && (!$uid || count($uid) < 1)) {
        $results = array();
    } // irmtfan bug fix array()
    else {
        $results = newbb_search($queries, $andor, $limit, $start, $uid, $forum, $sortby, $searchin, $criteriaExtra);
    } // irmtfan $criteriaExtra

    // add newbb_highlightText function to keywords
    $search_info_keywords = newbb_highlightText($myts->htmlSpecialChars($term), $queries);
    // add number of results
    $num_results = count($results);
    if ($num_results < 1) {
        $xoopsTpl->assign("lang_nomatch", _SR_NOMATCH);
    } else {
        // START irmtfan add show search post_text, skip the result if both (post text) and (post subject) are empty
        $skipresults = 0;
        foreach ($results as $row) {
            $post_text           = "";
            $post_text_select    = "have text";
            $post_subject_select = "have text";
            if ($show_search == 'post_text') {
                $post_text        = newbb_selectText($row['post_text'], $queries, $selectstartlag, $selectlength, $selecthtml, implode("", $selectexclude)); // strip html tags = $selecthtml
                $post_text_select = $post_text;
                $post_text        = newbb_highlightText($post_text, $queries);
            } elseif ("title" != $searchin && !empty($selecthtml)) { // find if there is any query left after strip html tags
                $post_text_select = newbb_selectText($row['post_text'], $queries, 100, 30000, true, implode("", $selectexclude)); // strip html tags = true
            }
            if ("text" != $searchin) {
                $post_subject_select = newbb_selectText($row['title'], $queries, 100, 400, true);// strip html tags = true
            }
            // if no text remained after select text continue
            if (empty($post_text_select) && empty($post_subject_select)) {
                $skipresults = $skipresults + 1;
                continue;
            }
            // add newbb_highlightText function to subject - add post_text
            $xoopsTpl->append('results', array('forum_name' => $row['forum_name'], 'forum_link' => $row['forum_link'], 'link' => $row['link'], 'title' => newbb_highlightText($row['title'], $queries), 'poster' => $row['poster'], 'post_time' => formatTimestamp($row['time'], "m"), 'post_text' => $post_text));
        }
        // END irmtfan add show search post_text
        unset($results);

        if (count($next_search) > 0) {
            $items = array();
            foreach ($next_search as $para => $val) {
                if (!empty($val) || $para == "selecthtml") {
                    $items[] = "{$para}={$val}";
                }// irmtfan add { and } - add $para when selecthtml = 0 (no strip)
            }
            if (count($items) > 0) {
                $paras = implode("&", $items);
            }
            unset($next_search);
            unset($items);
        }
        $search_url = XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname') . "/search.php?" . $paras;
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
            $search_url_next = $search_url . "&direction=next&start={$next}"; // irmtfan add { and } direction=next
            $search_next     = '<a href="' . htmlspecialchars($search_url_next) . '">' . _SR_NEXT . '</a>';
            $xoopsTpl->assign("search_next", $search_next);
        }
        if ($start > 0) {
            $prev            = $start - $limit;
            $search_url_prev = $search_url . "&direction=previous&start={$prev}"; // irmtfan add { and } and direction=previous
            $search_prev     = '<a href="' . htmlspecialchars($search_url_prev) . '">' . _SR_PREVIOUS . '</a>';
            $xoopsTpl->assign("search_prev", $search_prev);
        }
        // irmtfan if all results skipped then redirect to the next/previous page
        if ($num_results == $skipresults) {
            $direction           = XoopsRequest::getString('direction', XoopsRequest::getString('direction', 'next', 'GET'), 'POST');
            $search_url_redirect = (strtolower($direction) == "next") ? $search_url_next : $search_url_prev;
            redirect_header($search_url_redirect, 1, constant(strtoupper("_SR_{$direction}")));
        }
    }
    // irmtfan add newbb_highlightText function
    $search_info = _SR_KEYWORDS . ": " . $search_info_keywords;
    if ($uname_required) {
        if ($search_info) {
            $search_info .= "<br />";
        }
        $search_info .= _MD_USERNAME . ": " . $myts->htmlSpecialChars($search_username);
    }
    // add num_results
    $search_info .= "<br />" . sprintf(_SR_SHOWING, $start + 1, $start + $num_results);
    // if any result skip show the counter
    if (!empty($skipresults)) {
        $search_info .= " - " . sprintf(_SR_FOUND, $num_results - $skipresults);
    }
    $xoopsTpl->assign("search_info", $search_info);
}
//  START irmtfan - assign template vars for search
/* term */
$xoopsTpl->assign("search_term", $term);

/* andor */
$andor_select = "<select name=\"andor\">";
$andor_select .= "<option value=\"OR\"";
if ("OR" == $andor) {
    $andor_select .= " selected=\"selected\"";
}
$andor_select .= ">" . _SR_ANY . "</option>";
$andor_select .= "<option value=\"AND\"";
if ("AND" == $andor || empty($andor)) {
    $andor_select .= " selected=\"selected\"";
}
$andor_select .= ">" . _SR_ALL . "</option>";
$andor_select .= "<option value=\"EXACT\"";
if ("EXACT" == $andor) {
    $andor_select .= " selected=\"selected\"";
}
$andor_select .= ">" . _SR_EXACT . "</option>";
$andor_select .= "</select>";
$xoopsTpl->assign("andor_selection_box", $andor_select);

/* forum */
$select_forum = '<select name="forum[]" size="5" multiple="multiple">';
$select_forum .= '<option value="all">' . _MD_SEARCHALLFORUMS . '</option>';
$select_forum .= newbb_forumSelectBox($forum);
$select_forum .= '</select>';
$xoopsTpl->assign_by_ref("forum_selection_box", $select_forum);

/* searchin */
$searchin_select = "";
$searchin_select .= "<input type=\"radio\" name=\"searchin\" value=\"title\"";
if ("title" == $searchin) {
    $searchin_select .= " checked";
}
$searchin_select .= " />" . _MD_SUBJECT . "&nbsp;&nbsp;";
$searchin_select .= "<input type=\"radio\" name=\"searchin\" value=\"text\"";
if ("text" == $searchin) {
    $searchin_select .= " checked";
}
$searchin_select .= " />" . _MD_BODY . "&nbsp;&nbsp;";
$searchin_select .= "<input type=\"radio\" name=\"searchin\" value=\"both\"";
if ("both" == $searchin || empty($searchin)) {
    $searchin_select .= " checked";
}
$searchin_select .= " />" . _MD_SUBJECT . " & " . _MD_BODY . "&nbsp;&nbsp;";
$xoopsTpl->assign("searchin_radio", $searchin_select);

/* show_search */
$show_search_select = "";
$show_search_select .= "<input type=\"radio\" name=\"show_search\" value=\"post\"";
if ("post" == $show_search) {
    $show_search_select .= " checked";
}
$show_search_select .= " />" . _MD_POSTS . "&nbsp;&nbsp;";
$show_search_select .= "<input type=\"radio\" name=\"show_search\" value=\"post_text\"";
if ("post_text" == $show_search || empty($show_search)) {
    $show_search_select .= " checked";
}
$show_search_select .= " />" . _MD_SEARCHPOSTTEXT . "&nbsp;&nbsp;";
$xoopsTpl->assign("show_search_radio", $show_search_select);

/* author */
$xoopsTpl->assign("author_select", $search_username);

/* sortby */
$sortby_select = "<select name=\"sortby\">";
$sortby_select .= "<option value=\"p.post_time\"";
if ("p.post_time" == $sortby || empty($sortby)) {
    $sortby_select .= " selected=\"selected\"";
}
$sortby_select .= ">" . _MD_DATE . "</option>";
$sortby_select .= "<option value=\"p.subject\"";
if ("p.subject" == $sortby) {
    $sortby_select .= " selected=\"selected\"";
}
$sortby_select .= ">" . _MD_TOPIC . "</option>";
$sortby_select .= "</select>";
$xoopsTpl->assign("sortby_selection_box", $sortby_select);

/* selectstartlag */
$xoopsTpl->assign("selectstartlag_select", $selectstartlag);

/* selectlength */
$xoopsTpl->assign("selectlength_select", $selectlength);

/* selecthtml */
$selecthtml_select = "";
$selecthtml_select .= "<input type=\"radio\" name=\"selecthtml\" value=\"1\" onclick=\"javascript: {document.Search.selectexcludeset.disabled=false;}\"";
if (!empty($selecthtml)) {
    $selecthtml_select .= " checked";
}
$selecthtml_select .= " />" . _YES . "&nbsp;&nbsp;";
$selecthtml_select .= "<input type=\"radio\" name=\"selecthtml\" value=\"0\" onclick=\"javascript: {document.Search.selectexcludeset.disabled=true;}\"";
if (empty($selecthtml)) {
    $selecthtml_select .= " checked";
}
$selecthtml_select .= " />" . _NO . "&nbsp;&nbsp;";
$xoopsTpl->assign("selecthtml_radio", $selecthtml_select);

/* selectexclude */
$selectexclude_select = "<fieldset name=\"selectexcludeset\"";
if (empty($selecthtml)) {
    $selectexclude_select .= " disabled";
}
$selectexclude_select .= " />";
$selectexclude_select .= "<input type=\"checkbox\" name=\"selectexclude[]\" value=\"<p>\"";
if (in_array("<p>", $selectexclude)) {
    $selectexclude_select .= " checked";
}
$selectexclude_select .= " /> " . _MD_SELECT_TAG . " p &nbsp;&nbsp;";
$selectexclude_select .= "<input type=\"checkbox\" name=\"selectexclude[]\" value=\"<br>\"";
if (in_array("<br>", $selectexclude)) {
    $selectexclude_select .= " checked";
}
$selectexclude_select .= " /> " . _MD_SELECT_TAG . " br &nbsp;&nbsp;";
$selectexclude_select .= "<input type=\"checkbox\" name=\"selectexclude[]\" value=\"<a>\"";
if (in_array("<a>", $selectexclude)) {
    $selectexclude_select .= " checked";
}
$selectexclude_select .= " /> " . _MD_SELECT_TAG . " a &nbsp;&nbsp;";
$selectexclude_select .= "<input type=\"checkbox\" name=\"selectexclude[]\" value=\"<div>\"";
if (in_array("<div>", $selectexclude)) {
    $selectexclude_select .= " checked";
}
$selectexclude_select .= " /> " . _MD_SELECT_TAG . " div &nbsp;&nbsp;";
$selectexclude_select .= "<input type=\"checkbox\" name=\"selectexclude[]\" value=\"<img>\"";
if (in_array("<img>", $selectexclude)) {
    $selectexclude_select .= " checked";
}
$selectexclude_select .= " /> " . _MD_SELECT_TAG . " img &nbsp;&nbsp;";
$selectexclude_select .= "<input type=\"checkbox\" name=\"selectexclude[]\" value=\"<span>\"";
if (in_array("<span>", $selectexclude)) {
    $selectexclude_select .= " checked";
}
$selectexclude_select .= " /> " . _MD_SELECT_TAG . " span &nbsp;&nbsp;";
$selectexclude_select .= "</fieldset>";
$xoopsTpl->assign("selectexclude_check_box", $selectexclude_select);
//  END irmtfan - assign template vars for search

// irmtfan get since from the user for selction box
$since        = XoopsRequest::getInt('since', $xoopsModuleConfig["since_default"], 'GET');
$select_since = newbb_sinceSelectBox($since);
$xoopsTpl->assign_by_ref("since_selection_box", $select_since);

if ($xoopsConfigSearch['keyword_min'] > 0) {
    $xoopsTpl->assign("search_rule", sprintf(_SR_KEYIGNORE, $xoopsConfigSearch['keyword_min']));
}
// irmtfan move to footer.php
include_once __DIR__ . "/footer.php";
include $GLOBALS['xoops']->path('footer.php');
