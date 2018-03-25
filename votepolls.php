<?php
//
//  ------------------------------------------------------------------------ //
//                XOOPS - PHP Content Management System                      //
//                  Copyright (c) 2000-2016 XOOPS.org                        //
//                       <http://xoops.org/>                             //
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

include_once __DIR__ . '/header.php';
$poll_id  = XoopsRequest::getInt('poll_id', XoopsRequest::getInt('poll_id', 0, 'POST'), 'GET');
$topic_id = XoopsRequest::getInt('topic_id', XoopsRequest::getInt('topic_id', 0, 'POST'), 'GET');
$forum    = XoopsRequest::getInt('forum', XoopsRequest::getInt('forum', 0, 'POST'), 'GET');

$topicHandler = xoops_getModuleHandler('topic', 'newbb');
$topic_obj    = $topicHandler->get($topic_id);
if (!$topicHandler->getPermission($topic_obj->getVar('forum_id'), $topic_obj->getVar('topic_status'), 'vote')) {
    redirect_header($_SERVER['HTTP_REFERER'], 2, _NOPERM);
}

$testVars = XoopsRequest::get('POST');
if (!isset($testVars['option_id'])) {
    redirect_header(XOOPS_URL . "/modules/newbb/viewtopic.php?topic_id={$topic_id}", 1, _MD_POLL_NOOPTION);
}
// poll module
/** @var XoopsModuleHandler $moduleHandler */
$moduleHandler = xoops_getHandler('module');
$pollModuleHandler = $moduleHandler->getByDirname($GLOBALS['xoopsModuleConfig']['poll_module']);
if (is_object($pollModuleHandler) && $pollModuleHandler->getVar('isactive')) {
    // new xoopspoll module
    if ($pollModuleHandler->getVar('version') >= 140) {
        xoops_load('constants', $GLOBALS['xoopsModuleConfig']['poll_module']);
        xoops_loadLanguage('main', $GLOBALS['xoopsModuleConfig']['poll_module']);
        $xpPollHandler = xoops_getModuleHandler('poll', $GLOBALS['xoopsModuleConfig']['poll_module']);
        $xpLogHandler  = xoops_getModuleHandler('log', $GLOBALS['xoopsModuleConfig']['poll_module']);
        $poll_obj      = $xpPollHandler->get($poll_id); // will create poll if poll_id = 0 exist
        // old xoopspoll or umfrage or any clone from them
    } else {
        include $GLOBALS['xoops']->path('modules/' . $GLOBALS['xoopsModuleConfig']['poll_module'] . '/include/constants.php');
        $classPoll = $topic_obj->loadOldPoll();
        $poll_obj  = new $classPoll($poll_id); // will create poll if poll_id = 0 exist
    }
} else {
    redirect_header($_SERVER['HTTP_REFERER'], 2, _MD_POLLMODULE_ERROR);
}

$mail_author = false;
// new xoopspoll module
if ($pollModuleHandler->getVar('version') >= 140) {
    $classConstants = ucfirst($GLOBALS['xoopsModuleConfig']['poll_module']) . 'Constants';
    if (is_object($poll_obj)) {
        if ($poll_obj->getVar('multiple')) {
            $optionId = XoopsRequest::getArray('option_id', 0, 'POST');
            $optionId = $optionId; // type cast to make sure it's an array
            $optionId = array_map('intval', $optionId); // make sure values are integers
        } else {
            $optionId = XoopsRequest::getInt('option_id', 0, 'POST');
        }
        if (!$poll_obj->hasExpired()) {
            $msg = constant('_MD_' . strtoupper($GLOBALS['xoopsModuleConfig']['poll_module']) . '_MUSTLOGIN');
            //@todo:: add $url to all redirects
            //            $url = $GLOBALS['xoops']->buildUrl("index.php", array('poll_id' => $poll_id));
            if ($poll_obj->isAllowedToVote()) {
                $thisVoter     = is_object($GLOBALS['xoopsUser']) ? $GLOBALS['xoopsUser']->getVar('uid') : null;
                $votedThisPoll = $xpLogHandler->hasVoted($poll_id, xoops_getenv('REMOTE_ADDR'), $thisVoter);
                if (!$votedThisPoll) {
                    /* user that hasn't voted before in this poll or module preferences allow it */
                    $voteTime = time();
                    if ($poll_obj->vote($optionId, xoops_getenv('REMOTE_ADDR'), $voteTime)) {
                        if (!$xpPollHandler->updateCount($poll_obj)) { // update the count and save in db
                            echo $poll_obj->getHtmlErrors();
                            exit();
                        }
                        $msg = constant('_MD_' . strtoupper($GLOBALS['xoopsModuleConfig']['poll_module']) . '_THANKSFORVOTE');
                    } else {
                        /* there was a problem registering the vote */
                        redirect_header($GLOBALS['xoops']->buildUrl('index.php', ['poll_id' => $poll_id]), $classConstants::REDIRECT_DELAY_MEDIUM,
                                        constant('_MD_' . strtoupper($GLOBALS['xoopsModuleConfig']['poll_module']) . '_VOTE_ERROR'));
                    }
                } else {
                    $msg = constant('_MD_' . strtoupper($GLOBALS['xoopsModuleConfig']['poll_module']) . '_ALREADYVOTED');
                }
                /* set anon user vote (and the time they voted) */
                if (!is_object($GLOBALS['xoopsUser'])) {
                    xoops_load('pollUtility', $GLOBALS['xoopsModuleConfig']['poll_module']);
                    $classPollUtility = ucfirst($GLOBALS['xoopsModuleConfig']['poll_module']) . 'PollUtility';
                    $classPollUtility::setVoteCookie($poll_id, $voteTime, 0);
                }
            } else {
                $msg = constant('_MD_' . strtoupper($GLOBALS['xoopsModuleConfig']['poll_module']) . '_CANNOTVOTE');
            }
        } else {
            /* poll has expired so just show the results */
            $msg = constant('_MD_' . strtoupper($GLOBALS['xoopsModuleConfig']['poll_module']) . 'SORRYEXPIRED');
        }
    } else {
        $msg = constant('_MD_' . strtoupper($GLOBALS['xoopsModuleConfig']['poll_module']) . '_ERROR_INVALID_POLLID');
    }
    if (null !== $url) {
        redirect_header($url, $classConstants::REDIRECT_DELAY_MEDIUM, $msg);
    } else {
        redirect_header($GLOBALS['xoops']->buildUrl('viewtopic.php', ['topic_id' => $topic_id]), $classConstants::REDIRECT_DELAY_MEDIUM, $msg);
    }
    // old xoopspoll or umfrage or any clone from them
} else {
    $classLog = $classPoll . 'Log';
    if (is_object($GLOBALS['xoopsUser'])) {
        if ($classLog::hasVoted($poll_id, $_SERVER['REMOTE_ADDR'], $GLOBALS['xoopsUser']->getVar('uid'))) {
            $msg = _PL_ALREADYVOTED;
            setcookie("bb_polls[{$poll_id}]", 1);
        } else {
            // irmtfan save ip to db
            $poll_obj->vote(XoopsRequest::getInt('option_id', 0, 'POST'), $_SERVER['REMOTE_ADDR'], $GLOBALS['xoopsUser']->getVar('uid'));
            $poll_obj->updateCount();
            $msg = _PL_THANKSFORVOTE;
            setcookie("bb_polls[{$poll_id}]", 1);
        }
    } else {
        if ($classLog::hasVoted($poll_id, $_SERVER['REMOTE_ADDR'])) {
            $msg = _PL_ALREADYVOTED;
            setcookie("bb_polls[{$poll_id}]", 1);
        } else {
            $poll_obj->vote(XoopsRequest::getInt('option_id', 0, 'POST'), $_SERVER['REMOTE_ADDR']);
            $poll_obj->updateCount();
            $msg = _PL_THANKSFORVOTE;
            setcookie("bb_polls[{$poll_id}]", 1);
        }
    }
}
// irmtfan - simple url
redirect_header("viewtopic.php?topic_id={$topic_id}", 1, $msg);
