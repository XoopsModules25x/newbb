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

use Xmf\Request;

require_once __DIR__ . '/header.php';

$ratinguser   = is_object($GLOBALS['xoopsUser']) ? $GLOBALS['xoopsUser']->getVar('uid') : 0;
$anonwaitdays = 1;
$ip           = \Xmf\IPAddress::fromRequest()->asReadable();
foreach (['topic_id', 'rate', 'forum'] as $var) {
    //    ${$var} = isset($_POST[$var]) ? (int)($_POST[$var]) : (isset($_GET[$var])?(int)($_GET[$var]):0);
    ${$var} = Request::getInt($var, Request::getInt($var, 0, 'POST'), 'GET');
}

///** @var Newbb\TopicHandler $topicHandler */
//$topicHandler = Newbb\Helper::getInstance()->getHandler('Topic');
$topicObject = $topicHandler->get($topic_id);
if (!$topicHandler->getPermission($topicObject->getVar('forum_id'), $topicObject->getVar('topic_status'), 'post')
    && !$topicHandler->getPermission($topicObject->getVar('forum_id'), $topicObject->getVar('topic_status'), 'reply')) {
    // irmtfan - issue with javascript:history.go(-1)
    redirect_header(Request::getString('HTTP_REFERER', '', 'SERVER'), 2, _NOPERM);
}

if (empty($rate)) {
    redirect_header('viewtopic.php?topic_id=' . $topic_id . '&amp;forum=' . $forum . '', 4, _MD_NEWBB_NOVOTERATE);
}
///** @var Newbb\RateHandler $rateHandler */
//$rateHandler = Newbb\Helper::getInstance()->getHandler('Rate');
if (0 !== $ratinguser) {
    // Check if Topic POSTER is voting (UNLESS Anonymous users allowed to post)
    $crit_post = new \CriteriaCompo(new \Criteria('topic_id', $topic_id));
    $crit_post->add(new \Criteria('uid', $ratinguser));
    //    /** @var Newbb\PostHandler $postHandler */
    //    $postHandler = Newbb\Helper::getInstance()->getHandler('Post');
    if ($postHandler->getCount($crit_post)) {
        redirect_header('viewtopic.php?topic_id=' . $topic_id . '&amp;forum=' . $forum . '', 4, _MD_NEWBB_CANTVOTEOWN);
    }
    // Check if REG user is trying to vote twice.
    $crit_rate = new \CriteriaCompo(new \Criteria('topic_id', $topic_id));
    $crit_rate->add(new \Criteria('ratinguser', $ratinguser));
    if ($rateHandler->getCount($crit_rate)) {
        redirect_header('viewtopic.php?topic_id=' . $topic_id . '&amp;forum=' . $forum . '', 4, _MD_NEWBB_VOTEONCE);
    }
} else {
    // Check if ANONYMOUS user is trying to vote more than once per day.
    $crit_rate = new \CriteriaCompo(new \Criteria('topic_id', $topic_id));
    $crit_rate->add(new \Criteria('ratinguser', $ratinguser));
    $crit_rate->add(new \Criteria('ratinghostname', $ip));
    $crit_rate->add(new \Criteria('ratingtimestamp', time() - (86400 * $anonwaitdays), '>'));
    if ($rateHandler->getCount($crit_rate)) {
        redirect_header('viewtopic.php?topic_id=' . $topic_id . '&amp;forum=' . $forum . '', 4, _MD_NEWBB_VOTEONCE);
    }
}
$rateObject = $rateHandler->create();
$rateObject->setVar('rating', $rate * 2);
$rateObject->setVar('topic_id', $topic_id);
$rateObject->setVar('ratinguser', $ratinguser);
$rateObject->setVar('ratinghostname', $ip);
$rateObject->setVar('ratingtimestamp', time());

$ratingid = $rateHandler->insert($rateObject);

$query       = 'SELECT rating FROM ' . $GLOBALS['xoopsDB']->prefix('newbb_votedata') . ' WHERE topic_id = ' . $topic_id . ' ';
$voteresult  = $GLOBALS['xoopsDB']->query($query);
$votesDB     = $GLOBALS['xoopsDB']->getRowsNum($voteresult);
$totalrating = 0;
while (false !== (list($rating) = $GLOBALS['xoopsDB']->fetchRow($voteresult))) {
    $totalrating += $rating;
}
$finalrating = $totalrating / $votesDB;
$finalrating = number_format($finalrating, 4);
$sql         = sprintf('UPDATE "%s" SET rating = "%u", votes = "%u" WHERE topic_id = "%u"', $GLOBALS['xoopsDB']->prefix('newbb_topics'), $finalrating, $votesDB, $topic_id);
$GLOBALS['xoopsDB']->queryF($sql);

$ratemessage = _MD_NEWBB_VOTEAPPRE . '<br>' . sprintf(_MD_NEWBB_THANKYOU, $GLOBALS['xoopsConfig']['sitename']);
redirect_header('viewtopic.php?topic_id=' . $topic_id . '&amp;forum=' . $forum . '', 2, $ratemessage);
// irmtfan enhance include footer.php
include $GLOBALS['xoops']->path('footer.php');
