<?php
// $Id: ratethread.php 62 2012-08-17 10:15:26Z alfred $
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

$ratinguser   = is_object($GLOBALS['xoopsUser']) ? $GLOBALS['xoopsUser']->getVar('uid') : 0;
$anonwaitdays = 1;
$ip           = newbb_getIP(true);
foreach (array("topic_id", "rate", "forum") as $var) {
    //    ${$var} = isset($_POST[$var]) ? intval($_POST[$var]) : (isset($_GET[$var])?intval($_GET[$var]):0);
    ${$var} = XoopsRequest::getInt($var, XoopsRequest::getInt($var, 0, 'POST'), 'GET');
}

$topicHandler =& xoops_getmodulehandler('topic', 'newbb');
$topic_obj     =& $topicHandler->get($topic_id);
if (!$topicHandler->getPermission($topic_obj->getVar("forum_id"), $topic_obj->getVar('topic_status'), "post")
    &&
    !$topicHandler->getPermission($topic_obj->getVar("forum_id"), $topic_obj->getVar('topic_status'), "reply")
) {
    // irmtfan - issue with javascript:history.go(-1)
    redirect_header($_SERVER['HTTP_REFERER'], 2, _NOPERM);
}

if (empty($rate)) {
    redirect_header("viewtopic.php?topic_id=" . $topic_id . "&amp;forum=" . $forum . "", 4, _MD_NOVOTERATE);
}
$rate_handler =& xoops_getmodulehandler("rate", $xoopsModule->getVar("dirname"));
if ($ratinguser != 0) {
    // Check if Topic POSTER is voting (UNLESS Anonymous users allowed to post)
    $crit_post =& new CriteriaCompo(new Criteria("topic_id", $topic_id));
    $crit_post->add(new Criteria("uid", $ratinguser));
    $postHandler =& xoops_getmodulehandler("post", $xoopsModule->getVar("dirname"));
    if ($postHandler->getCount($crit_post)) {
        redirect_header("viewtopic.php?topic_id=" . $topic_id . "&amp;forum=" . $forum . "", 4, _MD_CANTVOTEOWN);
    }
    // Check if REG user is trying to vote twice.
    $crit_rate =& new CriteriaCompo(new Criteria("topic_id", $topic_id));
    $crit_rate->add(new Criteria("ratinguser", $ratinguser));
    if ($rate_handler->getCount($crit_rate)) {
        redirect_header("viewtopic.php?topic_id=" . $topic_id . "&amp;forum=" . $forum . "", 4, _MD_VOTEONCE);
    }
} else {
    // Check if ANONYMOUS user is trying to vote more than once per day.
    $crit_rate =& new CriteriaCompo(new Criteria("topic_id", $topic_id));
    $crit_rate->add(new Criteria("ratinguser", $ratinguser));
    $crit_rate->add(new Criteria("ratinghostname", $ip));
    $crit_rate->add(new Criteria("ratingtimestamp", time() - (86400 * $anonwaitdays), ">"));
    if ($rate_handler->getCount($crit_rate)) {
        redirect_header("viewtopic.php?topic_id=" . $topic_id . "&amp;forum=" . $forum . "", 4, _MD_VOTEONCE);
    }
}
$rate_obj =& $rate_handler->create();
$rate_obj->setVar("rating", $rate * 2);
$rate_obj->setVar("topic_id", $topic_id);
$rate_obj->setVar("ratinguser", $ratinguser);
$rate_obj->setVar("ratinghostname", $ip);
$rate_obj->setVar("ratingtimestamp", time());

$ratingid = $rate_handler->insert($rate_obj);;

$query       = "select rating FROM " . $GLOBALS['xoopsDB']->prefix('bb_votedata') . " WHERE topic_id = " . $topic_id . "";
$voteresult  = $GLOBALS['xoopsDB']->query($query);
$votesDB     = $GLOBALS['xoopsDB']->getRowsNum($voteresult);
$totalrating = 0;
while (list($rating) = $GLOBALS['xoopsDB']->fetchRow($voteresult)) {
    $totalrating += $rating;
}
$finalrating = $totalrating / $votesDB;
$finalrating = number_format($finalrating, 4);
$sql         = sprintf("UPDATE %s SET rating = %u, votes = %u WHERE topic_id = %u", $GLOBALS['xoopsDB']->prefix('bb_topics'), $finalrating, $votesDB, $topic_id);
$GLOBALS['xoopsDB']->queryF($sql);

$ratemessage = _MD_VOTEAPPRE . "<br />" . sprintf(_MD_THANKYOU, $GLOBALS['xoopsConfig']['sitename']);
redirect_header("viewtopic.php?topic_id=" . $topic_id . "&amp;forum=" . $forum . "", 2, $ratemessage);
// irmtfan enhance include footer.php
include $GLOBALS['xoops']->path('footer.php');
