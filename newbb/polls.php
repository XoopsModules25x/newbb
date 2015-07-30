<?php
/**
 * Newbb module
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright       XOOPS Project (http://xoops.org)
 * @license         http://www.fsf.org/copyleft/gpl.html GNU public license
 * @package         newbb
 * @since           4.0
 * @author          Taiwen Jiang <phppp@users.sourceforge.net>
 * @version         $Id: polls.php 2175 2008-09-23 14:07:03Z phppp $
 */
// rewrite by irmtfan and zyspec to accept xoopspoll 1.4 and all old xoopspoll and umfrage versions and all clones

include_once __DIR__ . '/header.php';
include_once $GLOBALS['xoops']->path('class/xoopsformloader.php');
include_once $GLOBALS['xoops']->path('class/xoopslists.php');
include_once $GLOBALS['xoops']->path('class/xoopsblock.php');
xoops_load('XoopsLocal');
$op      = 'add';
$goodOps = array('add', 'save', 'edit', 'update', 'addmore', 'savemore',
                 'delete', 'delete_ok', 'restart', 'restart_ok', 'log');
$op      = (isset($_REQUEST['op'])) ? $_REQUEST['op'] : 'add';
$op      = (!in_array($op, $goodOps)) ? 'add' : $op;

$poll_id  = XoopsRequest::getInt('poll_id', XoopsRequest::getInt('poll_id', 0, 'GET'), 'POST');
$topic_id = XoopsRequest::getInt('topic_id', XoopsRequest::getInt('topic_id', 0, 'GET'), 'POST');

// deal with permissions
$topicHandler =& xoops_getmodulehandler('topic', 'newbb');
$topic_obj     =& $topicHandler->get($topic_id);
// topic exist
if (is_object($topic_obj)) {
    $forum_id = $topic_obj->getVar('forum_id');
} else {
    redirect_header(XOOPS_URL . '/index.php', 2, _MD_POLLMODULE_ERROR . ': ' . _MD_FORUMNOEXIST);
}
// forum access permission
$forumHandler =& xoops_getmodulehandler('forum', 'newbb');
$forum_obj     =& $forumHandler->get($forum_id);
if (!$forumHandler->getPermission($forum_obj)) {
    redirect_header(XOOPS_URL . '/index.php', 2, _MD_NORIGHTTOACCESS);
}
// topic view permission
if (!$topicHandler->getPermission($forum_obj, $topic_obj->getVar('topic_status'), 'view')) {
    redirect_header(XOOPS_URL . '/viewforum.php?forum=' . $forum_id, 2, _MD_NORIGHTTOVIEW);
}
// poll module
$pollModuleHandler =& $module_handler->getByDirname($GLOBALS['xoopsModuleConfig']['poll_module']);
if (is_object($pollModuleHandler) && $pollModuleHandler->getVar('isactive')) {
    // new xoopspoll module
    if ($pollModuleHandler->getVar('version') >= 140) {
        xoops_load('constants', $GLOBALS['xoopsModuleConfig']['poll_module']);
        xoops_load('pollUtility', $GLOBALS['xoopsModuleConfig']['poll_module']);
        xoops_load('request', $GLOBALS['xoopsModuleConfig']['poll_module']);
        xoops_loadLanguage('admin', $GLOBALS['xoopsModuleConfig']['poll_module']);
        $xpPollHandler =& xoops_getmodulehandler('poll', $GLOBALS['xoopsModuleConfig']['poll_module']);
        $poll_obj      = $xpPollHandler->get($poll_id); // will create poll if poll_id = 0 exist
        // old xoopspoll or umfrage or any clone from them
    } else {
        include $GLOBALS['xoops']->path('modules/' . $GLOBALS['xoopsModuleConfig']['poll_module'] . '/include/constants.php');
        $classPoll = $topic_obj->loadOldPoll();
        $poll_obj  = new $classPoll($poll_id); // will create poll if poll_id = 0 exist
    }
} else {
    // irmtfan - issue with javascript:history.go(-1)
    redirect_header($_SERVER['HTTP_REFERER'], 2, _MD_POLLMODULE_ERROR);
}
// include header
include $GLOBALS['xoops']->path('header.php');

// no admin user permission
if (is_object($GLOBALS['xoopsUser']) && !newbb_isAdmin($forum_obj)) {
    $perm = false;
    if ($topicHandler->getPermission($forum_obj, $topic_obj->getVar('topic_status'), 'addpoll')
    ) {
        if (('add' === $op || 'save' === $op || 'update' === $op)
            && !$topic_obj->getVar('topic_haspoll')
            && ($GLOBALS['xoopsUser']->getVar('uid') === $topic_obj->getVar('topic_poster'))
        ) {
            $perm = true;
        } elseif (!empty($poll_id) && ($GLOBALS['xoopsUser']->getVar('uid') === $poll_obj->getVar('user_id'))) {
            $perm = true;
        }
    }
    if (!$perm) {
        redirect_header(XOOPS_URL . "/viewtopic.php?topic_id={$topic_id}", 2, _NOPERM);
    }
}
switch ($op) {
    case 'add':
        // new xoopspoll module
        if ($pollModuleHandler->getVar('version') >= 140) {
            echo "<h4>" . _MD_POLL_CREATNEWPOLL . "</h4>\n";
            $poll_obj->renderForm($_SERVER['PHP_SELF'], 'post', array('topic_id' => $topic_id));
            // old xoopspoll or umfrage or any clone from them
        } else {
            $classOption  = $classPoll . 'Option';
            $poll_form    = new XoopsThemeForm(_MD_POLL_CREATNEWPOLL, 'poll_form', 'polls.php', 'post', true);
            $author_label = new XoopsFormLabel(_MD_POLL_AUTHOR, (is_object($GLOBALS['xoopsUser'])) ? ("<a href='" . XOOPS_URL . '/userinfo.php?uid=' . $GLOBALS['xoopsUser']->getVar('uid') . "'>" . newbb_getUnameFromId($GLOBALS['xoopsUser']->getVar('uid'), $GLOBALS['xoopsModuleConfig']['show_realname']) . '</a>')
                : $GLOBALS['xoopsConfig']['anonymous']);
            $poll_form->addElement($author_label);
            $question_text = new XoopsFormText(_MD_POLL_POLLQUESTION, 'question', 50, 255);
            $poll_form->addElement($question_text);
            $desc_tarea = new XoopsFormTextarea(_MD_POLL_POLLDESC, 'description');
            $poll_form->addElement($desc_tarea);
            $currenttime = formatTimestamp(time(), 'Y-m-d H:i:s');
            $endtime     = formatTimestamp(time() + 604800, 'Y-m-d H:i:s');
            $expire_text = new XoopsFormText(_MD_POLL_EXPIRATION . '<br /><small>' . _MD_POLL_FORMAT . '<br />' . sprintf(_MD_POLL_CURRENTTIME, $currenttime) . '</small>', 'end_time', 30, 19, $endtime);
            $poll_form->addElement($expire_text);

            $weight_text = new XoopsFormText(_MD_POLL_DISPLAYORDER, 'weight', 6, 5, 0);
            $poll_form->addElement($weight_text);

            $multi_yn = new XoopsFormRadioYN(_MD_POLL_ALLOWMULTI, 'multiple', 0);
            $poll_form->addElement($multi_yn);

            $notify_yn = new XoopsFormRadioYN(_MD_POLL_NOTIFY, 'notify', 1);
            $poll_form->addElement($notify_yn);

            $option_tray    = new XoopsFormElementTray(_MD_POLL_POLLOPTIONS, '');
            $barcolor_array = XoopsLists::getImgListAsArray($GLOBALS['xoops']->path('modules/' . $GLOBALS['xoopsModuleConfig']['poll_module'] . '/assets/images/colorbars/'));
            for ($i = 0; $i < 10; ++$i) {
                $current_bar = (current($barcolor_array) !== 'blank.gif') ? current($barcolor_array) : next($barcolor_array);
                $option_text = new XoopsFormText('', 'option_text[]', 50, 255);
                $option_tray->addElement($option_text);
                $color_select = new XoopsFormSelect('', "option_color[{$i}]", $current_bar);
                $color_select->addOptionArray($barcolor_array);
                $color_select->setExtra("onchange='showImgSelected(\"option_color_image[{$i}]\", \"option_color[{$i}]\", \"modules/" . $GLOBALS['xoopsModuleConfig']["poll_module"] . "/assets/images/colorbars\", \"\", \"" . XOOPS_URL . "\")'");
                $color_label = new XoopsFormLabel('', "<img src='" . XOOPS_URL . '/modules/' . $GLOBALS['xoopsModuleConfig']['poll_module'] . "/assets/images/colorbars/" . $current_bar . "' name='option_color_image[{$i}]' id='option_color_image[{$i}]' width='30' align='bottom' height='15' alt='' /><br />");
                $option_tray->addElement($color_select);
                $option_tray->addElement($color_label);
                if (!next($barcolor_array)) {
                    reset($barcolor_array);
                }
                unset($color_select, $color_label);
            }
            $poll_form->addElement($option_tray);

            $poll_form->addElement(new XoopsFormHidden('op', 'save'));
            $poll_form->addElement(new XoopsFormHidden('topic_id', $topic_id));
            $poll_form->addElement(new XoopsFormHidden('poll_id', $poll_id));
            $poll_form->addElement(new XoopsFormHidden('user_id', (is_object($GLOBALS['xoopsUser'])) ? $GLOBALS['xoopsUser']->getVar('uid') : 0));
            $poll_form->addElement(new XoopsFormButtonTray('poll_submit', _SUBMIT, 'submit'));
            echo '<h4>' . _MD_POLL_POLLCONF . '</h4>';
            $poll_form->display();
        }
        break; // op: add

    case 'edit':
        // new xoopspoll module
        if ($pollModuleHandler->getVar('version') >= 140) {
            echo '<h4>' . _MD_POLL_EDITPOLL . "</h4>\n";
            $poll_obj->renderForm($_SERVER['PHP_SELF'], 'post', array('topic_id' => $topic_id));
            // old xoopspoll or umfrage or any clone from them
        } else {
            $classOption  = $classPoll . 'Option';
            $poll_form    = new XoopsThemeForm(_MD_POLL_EDITPOLL, 'poll_form', 'polls.php', 'post', true);
            $author_label = new XoopsFormLabel(_MD_POLL_AUTHOR, "<a href='" . XOOPS_URL . '/userinfo.php?uid=' . $poll_obj->getVar('user_id') . "'>" . newbb_getUnameFromId($poll_obj->getVar('user_id'), $GLOBALS['xoopsModuleConfig']['show_realname']) . '</a>');
            $poll_form->addElement($author_label);
            $question_text = new XoopsFormText(_MD_POLL_POLLQUESTION, 'question', 50, 255, $poll_obj->getVar('question', 'E'));
            $poll_form->addElement($question_text);
            $desc_tarea = new XoopsFormTextarea(_MD_POLL_POLLDESC, 'description', $poll_obj->getVar('description', 'E'));
            $poll_form->addElement($desc_tarea);
            $date = formatTimestamp($poll_obj->getVar('end_time'), 'Y-m-d H:i:s'); // important "Y-m-d H:i:s" use in jdf function
            if (!$poll_obj->hasExpired()) {
                $expire_text = new XoopsFormText(_MD_POLL_EXPIRATION . '<br /><small>' . _MD_POLL_FORMAT . '<br />' . sprintf(_MD_POLL_CURRENTTIME, formatTimestamp(time(), 'Y-m-d H:i:s')) . '</small>', 'end_time', 20, 19, $date);
                $poll_form->addElement($expire_text);
            } else {
                // irmtfan full URL - add topic_id
                $restart_label = new XoopsFormLabel(_MD_POLL_EXPIRATION, sprintf(_MD_POLL_EXPIREDAT, $date) . "<br /><a href='" . XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname') . "/polls.php?op=restart&amp;poll_id={$poll_id}&amp;topic_id={$topic_id}'>" . _MD_POLL_RESTART . '</a>');
                $poll_form->addElement($restart_label);
            }
            $weight_text = new XoopsFormText(_MD_POLL_DISPLAYORDER, 'weight', 6, 5, $poll_obj->getVar('weight'));
            $poll_form->addElement($weight_text);
            $multi_yn = new XoopsFormRadioYN(_MD_POLL_ALLOWMULTI, 'multiple', $poll_obj->getVar('multiple'));
            $poll_form->addElement($multi_yn);
            $options_arr  =& $classOption::getAllByPollId($poll_id);
            $notify_value = 1;
            if (0 !== $poll_obj->getVar('mail_status')) {
                $notify_value = 0;
            }
            $notify_yn = new XoopsFormRadioYN(_MD_POLL_NOTIFY, 'notify', $notify_value);
            $poll_form->addElement($notify_yn);
            $option_tray    = new XoopsFormElementTray(_MD_POLL_POLLOPTIONS, '');
            $barcolor_array = XoopsLists::getImgListAsArray($GLOBALS['xoops']->path("modules/{$GLOBALS['xoopsModuleConfig']['poll_module']}/assets/images/colorbars/"));
            $i              = 0;
            foreach ($options_arr as $option) {
                $option_tray->addElement(new XoopsFormText('', 'option_text[]', 50, 255, $option->getVar('option_text')));
                $option_tray->addElement(new XoopsFormHidden('option_id[]', $option->getVar('option_id')));
                $color_select = new XoopsFormSelect('', 'option_color[{$i}]', $option->getVar('option_color'));
                $color_select->addOptionArray($barcolor_array);
                $color_select->setExtra("onchange='showImgSelected(\"option_color_image[{$i}]\", \"option_color[" . $i . "]\", \"modules/{$GLOBALS['xoopsModuleConfig']['poll_module']}/assets/images/colorbars\", \"\", \"" . XOOPS_URL . "\")'");
                $color_label = new XoopsFormLabel("", "<img src='" . $GLOBALS['xoops']->url("modules/{$GLOBALS['xoopsModuleConfig']['poll_module']}/assets/images/colorbars/" . $option->getVar("option_color", "E")) . "' name='option_color_image[{$i}]' id='option_color_image[{$i}]' class='alignbottom' width='30' height='15' alt='' /><br />");
                $option_tray->addElement($color_select);
                $option_tray->addElement($color_label);
                unset($color_select, $color_label);
                ++$i;
            }
            // irmtfan full URL
            $more_label = new XoopsFormLabel('', "<br /><a href='" . XOOPS_URL . '/modules/' . $xoopsModule->getVar("dirname") . "/polls.php?op=addmore&amp;poll_id={$poll_id}&amp;topic_id={$topic_id}'>" . _MD_POLL_ADDMORE . "</a>");
            $option_tray->addElement($more_label);
            $poll_form->addElement($option_tray);
            $poll_form->addElement(new XoopsFormHidden('op', 'update'));
            $poll_form->addElement(new XoopsFormHidden('topic_id', $topic_id));
            $poll_form->addElement(new XoopsFormHidden('poll_id', $poll_id));
            $poll_form->addElement(new XoopsFormButtonTray('poll_submit', _SUBMIT, 'submit'));

            echo '<h4>' . _MD_POLL_POLLCONF . "</h4>\n";
            $poll_form->display();
        }
        break; // op: edit

    case 'save':
        // old xoopspoll or umfrage or any clone from them
        if ($pollModuleHandler->getVar('version') < 140) {
            // check security token
            if (!$GLOBALS['xoopsSecurity']->check()) {
                redirect_header($_SERVER['PHP_SELF'], 2, implode('<br />', $GLOBALS['xoopsSecurity']->getErrors()));
            }
            /*
             * The option check should be done before submitting
             */
            $option_empty = true;
            if (!XoopsRequest::getString('option_text', '', 'POST')) {
                // irmtfan - issue with javascript:history.go(-1)
                redirect_header($_SERVER['HTTP_REFERER'], 2, _MD_ERROROCCURED . ': ' . _MD_POLL_POLLOPTIONS . ' !');
            }
            $option_text = XoopsRequest::getArray('option_text', '', 'POST');
            foreach ($option_text as $optxt) {
                if (trim($optxt) !== '') {
                    $option_empty = false;
                    break;
                }
            }
            if ($option_empty) {
                // irmtfan - issue with javascript:history.go(-1)
                redirect_header($_SERVER['HTTP_REFERER'], 2, _MD_ERROROCCURED . ': ' . _MD_POLL_POLLOPTIONS . ' !');
            }
            $poll_obj->setVar('question', XoopsRequest::getString('question', '', 'POST'));
            $poll_obj->setVar('description', XoopsRequest::getString('description', '', 'POST'));
            $end_time = XoopsRequest::getString('end_time', '', 'POST'); // (empty($_POST['end_time'])) ? "" : $_POST['end_time'];
            if ('' !== $end_time) {
                $timezone = (is_object($GLOBALS['xoopsUser'])) ? $GLOBALS['xoopsUser']->getVar('timezone') : null;
                $poll_obj->setVar('end_time', userTimeToServerTime(method_exists('XoopsLocal', 'strtotime') ? XoopsLocal::strtotime($end_time) : strtotime($end_time), $timezone));
            } else {
                // if expiration date is not set, set it to 10 days from now
                $poll_obj->setVar('end_time', time() + (86400 * 10));
            }

            $poll_obj->setVar('display', 0);
            $poll_obj->setVar('weight', XoopsRequest::getInt('weight', 0, 'POST'));
            $poll_obj->setVar('multiple', XoopsRequest::getInt('multiple', 0, 'POST'));
            $poll_obj->setVar('user_id', XoopsRequest::getInt('user_id', 0, 'POST'));
            if (XoopsRequest::getInt('notify', 0, 'POST') && $end_time > time()) {
                // if notify, set mail status to "not mailed"
                $poll_obj->setVar('mail_status', POLL_NOTMAILED);
            } else {
                // if not notify, set mail status to already "mailed"
                $poll_obj->setVar('mail_status', POLL_MAILED);
            }
            $new_poll_id = $poll_obj->store();
            if (empty($new_poll_id)) {
                xoops_error($poll_obj->getHtmlErrors);
                break;
            }
            $i            = 0;
            $option_color = XoopsRequest::getArray('option_color', null, 'POST');;
            $classOption = $classPoll . 'Option';
            foreach ($option_text as $optxt) {
                $optxt      = trim($optxt);
                $option_obj = new $classOption();
                if ($optxt !== '') {
                    $option_obj->setVar('option_text', $optxt);
                    $option_obj->setVar('option_color', $option_color[$i]);
                    $option_obj->setVar('poll_id', $new_poll_id);
                    $option_obj->store();
                }
                ++$i;
            }
            // clear the template cache so changes take effect immediately
            include_once $GLOBALS['xoops']->path('class/template.php');
            xoops_template_clear_module_cache($GLOBALS['xoopsModule']->getVar('mid'));
            xoops_template_clear_module_cache($pollModuleHandler->getVar('mid'));

            // update topic to indicate it has a poll
            $topic_obj->setVar('topic_haspoll', 1);
            $topic_obj->setVar('poll_id', $new_poll_id);
            $success = $topicHandler->insert($topic_obj);
            if (!$success) {
                xoops_error($topicHandler->getHtmlErrors());
            } else {
                redirect_header("viewtopic.php?topic_id={$topic_id}", 2, _MD_POLL_DBUPDATED);
            }
            break;// op: save
        }
    case 'update':
        // check security token
        if (!$GLOBALS['xoopsSecurity']->check()) {
            redirect_header($_SERVER['PHP_SELF'], 2, implode('<br />', $GLOBALS['xoopsSecurity']->getErrors()));
        }
        /* make sure there's at least one option */
        $option_text   = XoopsRequest::getString('option_text', '', 'POST');
        $option_string = is_array($option_text) ? implode('', $option_text) : $option_text;
        $option_string = trim($option_string);
        if ('' ===$option_string) {
            redirect_header($_SERVER['HTTP_REFERER'], 2, _MD_ERROROCCURED . ': ' . _MD_POLL_POLLOPTIONS . ' !');
        }

        // new xoopspoll module
        if ($pollModuleHandler->getVar('version') >= 140) {
            $xpOptHandler =& xoops_getmodulehandler('option', $GLOBALS['xoopsModuleConfig']['poll_module']);
            $xpLogHandler =& xoops_getmodulehandler('log', $GLOBALS['xoopsModuleConfig']['poll_module']);
//            $classRequest = ucfirst($GLOBALS['xoopsModuleConfig']["poll_module"]) . "Request";
            $classConstants   = ucfirst($GLOBALS['xoopsModuleConfig']['poll_module']) . 'Constants';
            $notify           = XoopsRequest::getInt('notify', $classConstants::NOTIFICATION_ENABLED, 'POST');
            $currentTimestamp = time();
            //$xuEndTimestamp   = method_exists('XoopsLocal', 'strtotime') ? XoopsLocal::strtotime(XoopsRequest::getString('xu_end_time', null, 'POST'))
            //                                                             : strtotime(XoopsRequest::getString('xu_end_time', null, 'POST'));
            $xuEndTimestamp = strtotime(XoopsRequest::getString('xu_end_time', null, 'POST'));
            $endTimestamp   = (!XoopsRequest::getString('xu_end_time', null, 'POST')) ? ($currentTimestamp + $classConstants::DEFAULT_POLL_DURATION) : userTimeToServerTime($xuEndTimestamp);
            //$xuStartTimestamp = method_exists('XoopsLocal', 'strtotime') ? XoopsLocal::strtotime(XoopsRequest::getString('xu_start_time', null, 'POST'))
            //                                                             : strtotime(XoopsRequest::getString('xu_start_time', null, 'POST'));
            $xuStartTimestamp = strtotime(XoopsRequest::getString('xu_start_time', null, 'POST'));
            $startTimestamp   = (!XoopsRequest::getString('xu_start_time', null, 'POST')) ? ($endTimestamp - $classConstants::DEFAULT_POLL_DURATION) : userTimeToServerTime($xuStartTimestamp);

            //  don't allow changing start time if there are votes in the log
            if (($startTimestamp < $poll_obj->getVar('start_time'))
                && ($xpLogHandler->getTotalVotesByPollId($poll_id) > 0)
            ) {
                $startTimestamp = $poll_obj->getVar('start_time'); //don't change start time
            }

            $poll_vars = array(
                'user_id'     => XoopsRequest::getInt('user_id', $GLOBALS['xoopsUser']->uid(), 'POST'),
                'question'    => XoopsRequest::getString('question', null, 'POST'),
                'description' => XoopsRequest::getText('description', null, 'POST'),
                'mail_status' => ($classConstants::NOTIFICATION_ENABLED === $notify) ? $classConstants::POLL_NOT_MAILED : $classConstants::POLL_MAILED,
                'mail_voter'  => XoopsRequest::getInt('mail_voter', $classConstants::NOT_MAIL_POLL_TO_VOTER, 'POST'),
                'start_time'  => $startTimestamp,
                'end_time'    => $endTimestamp,
                'display'     => XoopsRequest::getInt('display', $classConstants::DO_NOT_DISPLAY_POLL_IN_BLOCK, 'POST'),
                'visibility'  => XoopsRequest::getInt('visibility', $classConstants::HIDE_NEVER, 'POST'),
                'weight'      => XoopsRequest::getInt('weight', $classConstants::DEFAULT_WEIGHT, 'POST'),
                'multiple'    => XoopsRequest::getInt('multiple', $classConstants::NOT_MULTIPLE_SELECT_POLL, 'POST'),
                'multilimit'  => XoopsRequest::getInt('multilimit', $classConstants::MULTIPLE_SELECT_LIMITLESS, 'POST'),
                'anonymous'   => XoopsRequest::getInt('anonymous', $classConstants::ANONYMOUS_VOTING_DISALLOWED, 'POST')
            );
            $poll_obj->setVars($poll_vars);
            $poll_id = $xpPollHandler->insert($poll_obj);
            if (!$poll_id) {
                $err = $poll_obj->getHtmlErrors();
                exit($err);
            }

            // now get the options
            $optionIdArray    = XoopsRequest::getArray('option_id', array(), 'POST');
            $optionIdArray    = array_map('intval', $optionIdArray);
            $optionTextArray  = XoopsRequest::getArray('option_text', array(), 'POST');
            $optionColorArray = XoopsRequest::getArray('option_color', array(), 'POST');

            foreach ($optionIdArray as $key => $oId) {
                if (!empty($oId) && ($option_obj = $xpOptHandler->get($oId))) {
                    // existing option object so need to update it
                    $optionTextArray[$key] = trim($optionTextArray[$key]);
                    if ('' === $optionTextArray[$key]) {
                        // want to delete this option
                        if (false !== $xpOptHandler->delete($option_obj)) {
                            // now remove it from the log
                            $xpLogHandler->deleteByOptionId($option_obj->getVar('option_id'));
                            //update vote count in poll
                            $xpPollHandler->updateCount($poll_obj);
                        } else {
                            xoops_error($xpLogHandler->getHtmlErrors());
                            break;
                        }
                    } else {
                        $option_obj->setVar('option_text', $optionTextArray[$key]);
                        $option_obj->setVar('option_color', $optionColorArray[$key]);
                        $option_obj->setVar('poll_id', $poll_id);
                        $xpOptHandler->insert($option_obj);
                    }
                } else {
                    // new option object
                    $option_obj            = $xpOptHandler->create();
                    $optionTextArray[$key] = trim($optionTextArray[$key]);
                    if ('' !== $optionTextArray[$key]) { // ignore if text is empty
                        $option_obj->setVar('option_text', $optionTextArray[$key]);
                        $option_obj->setVar('option_color', $optionColorArray[$key]);
                        $option_obj->setVar('poll_id', $poll_id);
                        $xpOptHandler->insert($option_obj);
                    }
                    unset($option_obj);
                }
            }
            // old xoopspoll or umfrage or any clone from them
        } else {
            $poll_obj->setVar('question', XoopsRequest::getString('question', '', 'POST'));
            $poll_obj->setVar('description', XoopsRequest::getString('description', '', 'POST'));

            $end_time = XoopsRequest::getString('end_time', '', 'POST');
            if ('' !== $end_time) {
                $timezone = (is_object($GLOBALS['xoopsUser'])) ? $GLOBALS['xoopsUser']->getVar('timezone') : null;
                $poll_obj->setVar('end_time', userTimeToServerTime(method_exists('XoopsLocal', 'strtotime') ? XoopsLocal::strtotime($end_time) : strtotime($end_time), $timezone));
            }
            $poll_obj->setVar('display', 0);
            $poll_obj->setVar('weight', XoopsRequest::getInt('weight', 0, 'POST'));
            $poll_obj->setVar('multiple', XoopsRequest::getInt('multiple', 0, 'POST'));
            $poll_obj->setVar('user_id', XoopsRequest::getInt('user_id', 0, 'POST'));
            if (XoopsRequest::getInt('notify', 0, 'POST') && $end_time > time()) {
                // if notify, set mail status to "not mailed"
                $poll_obj->setVar('mail_status', POLL_NOTMAILED);
            } else {
                // if not notify, set mail status to already "mailed"
                $poll_obj->setVar('mail_status', POLL_MAILED);
            }

            if (!$poll_obj->store()) {
                xoops_error($poll_obj->getHtmlErrors);
                break;
            }
            $i            = 0;
            $option_id    = XoopsRequest::getArray('option_id', null, 'POST');
            $option_color = XoopsRequest::getArray('option_color', null, 'POST');
            $classOption  = $classPoll . 'Option';
            $classLog     = $classPoll . 'Log';
            foreach ($option_id as $opid) {
                $option_obj      = new $classOption($opid);
                $option_text[$i] = trim($option_text[$i]);
                if ($option_text[$i] !== '') {
                    $option_obj->setVar('option_text', $option_text[$i]);
                    $option_obj->setVar('option_color', $option_color[$i]);
                    $option_obj->store();
                } else {
                    if ($option_obj->delete() !== false) {
                        $classLog::deleteByOptionId($option->getVar('option_id'));
                    }
                }
                ++$i;
            }
            $poll_obj->updateCount();
        }
        // clear the template cache so changes take effect immediately
        include_once $GLOBALS['xoops']->path('class/template.php');
        xoops_template_clear_module_cache($GLOBALS['xoopsModule']->getVar('mid'));
        xoops_template_clear_module_cache($pollModuleHandler->getVar('mid'));

        // update topic to indicate it has a poll
        $topic_obj->setVar('topic_haspoll', 1);
        $topic_obj->setVar('poll_id', $poll_obj->getVar('poll_id'));
        $success = $topicHandler->insert($topic_obj);
        if (!$success) {
            xoops_error($topicHandler->getHtmlErrors());
        } else {
            redirect_header("viewtopic.php?topic_id={$topic_id}", 2, _MD_POLL_DBUPDATED);
        }
        break;// op: save | update

    case 'addmore':
        $question = $poll_obj->getVar('question');
        unset($poll_obj);
        $poll_form = new XoopsThemeForm(_MD_POLL_ADDMORE, 'poll_form', 'polls.php', 'post', true);
        $poll_form->addElement(new XoopsFormLabel(_MD_POLL_POLLQUESTION, $question));
        // new xoopspoll module
        if ($pollModuleHandler->getVar('version') >= 140) {
            $xpOptHandler =& xoops_getmodulehandler('option', $GLOBALS['xoopsModuleConfig']['poll_module']);
            $option_tray  = $xpOptHandler->renderOptionFormTray($poll_id);
            // old xoopspoll or umfrage or any clone from them
        } else {
            $option_tray    = new XoopsFormElementTray(_MD_POLL_POLLOPTIONS, '');
            $barcolor_array = XoopsLists::getImgListAsArray($GLOBALS['xoops']->path('modules/' . $GLOBALS['xoopsModuleConfig']['poll_module'] . '/assets/images/colorbars/'));
            for ($i = 0; $i < 10; ++$i) {
                $current_bar = (current($barcolor_array) !== 'blank.gif') ? current($barcolor_array) : next($barcolor_array);
                $option_text = new XoopsFormText('', 'option_text[]', 50, 255);
                $option_tray->addElement($option_text);
                $color_select = new XoopsFormSelect('', "option_color[{$i}]", $current_bar);
                $color_select->addOptionArray($barcolor_array);
                $color_select->setExtra("onchange='showImgSelected(\"option_color_image[{$i}]\", \"option_color[{$i}]\", \"modules/{$GLOBALS['xoopsModuleConfig']['poll_module']}/assets/images/colorbars\", \"\", \"" . XOOPS_URL . "\")'");
                $color_label = new XoopsFormLabel('', "<img src='" . $GLOBALS['xoops']->url("modules/{$GLOBALS['xoopsModuleConfig']['poll_module']}/assets/images/colorbars/{$current_bar}") . "' name='option_color_image[{$i}]' id='option_color_image[{$i}]' class='alignbottom' width='30' height='15' alt='' /><br />");
                $option_tray->addElement($color_select);
                $option_tray->addElement($color_label);
                unset($color_select, $color_label, $option_text);
                if (!next($barcolor_array)) {
                    reset($barcolor_array);
                }
            }
        }
        $poll_form->addElement($option_tray);
        $poll_form->addElement(new XoopsFormButtonTray('poll_submit', _SUBMIT, 'submit'));
        $poll_form->addElement(new XoopsFormHidden('op', 'savemore'));
        $poll_form->addElement(new XoopsFormHidden('topic_id', $topic_id));
        $poll_form->addElement(new XoopsFormHidden('poll_id', $poll_id));

        echo '<h4>' . _MD_POLL_POLLCONF . "</h4>\n";
        $poll_form->display();
        break;

    case 'savemore':
        // check security token
        if (!$GLOBALS['xoopsSecurity']->check()) {
            redirect_header($_SERVER['PHP_SELF'], 2, implode('<br />', $GLOBALS['xoopsSecurity']->getErrors()));
        }

        $option_text   = XoopsRequest::getString('option_text', '', 'POST');
        $option_string = is_array($option_text) ? implode('', $option_text) : $option_text;
        $option_string = trim($option_string);
        if ('' === $option_string) {
            // irmtfan - issue with javascript:history.go(-1)
            redirect_header($_SERVER['HTTP_REFERER'], 2, _MD_ERROROCCURED . ': ' . _MD_POLL_POLLOPTIONS . ' !');
        }
        $i            = 0;
        $option_color = XoopsRequest::getArray('option_color', null, 'POST');
        foreach ($option_text as $optxt) {
            $optxt = trim($optxt);
            if ('' !== $optxt) {
                // new xoopspoll module
                if ($pollModuleHandler->getVar('version') >= 140) {
                    $xpOptHandler =& xoops_getmodulehandler('option', $GLOBALS['xoopsModuleConfig']['poll_module']);
                    $option_obj   = $xpOptHandler->create();
                    $option_obj->setVar('option_text', $optxt);
                    $option_obj->setVar('poll_id', $poll_id);
                    $option_obj->setVar('option_color', $option_color[$i]);
                    $xpOptHandler->insert($option_obj);
                    // old xoopspoll or umfrage or any clone from them
                } else {
                    $classOption = $classPoll . 'Option';
                    $option_obj  = new $classOption();
                    $option_obj->setVar('option_text', $optxt);
                    $option_obj->setVar('poll_id', $poll_id);
                    $option_obj->setVar('option_color', $option_color[$i]);
                    $option_obj->store();
                }
                unset($option_obj);
            }
            ++$i;
        }
        include_once $GLOBALS['xoops']->path('class/template.php');
        xoops_template_clear_module_cache($GLOBALS['xoopsModule']->getVar('mid'));
        xoops_template_clear_module_cache($pollModuleHandler->getVar('mid'));
        redirect_header("polls.php?op=edit&amp;poll_id={$poll_id}&amp;topic_id={$topic_id}", 2, _MD_POLL_DBUPDATED);
        break;

    case 'delete':
        echo '<h4>' . _MD_POLL_POLLCONF . "</h4>\n";
        xoops_confirm(array('op' => 'delete_ok', 'topic_id' => $topic_id, 'poll_id' => $poll_id), 'polls.php', sprintf(_MD_POLL_RUSUREDEL, $poll_obj->getVar('question')));
        break;

    case 'delete_ok':
        // check security token
        if (!$GLOBALS['xoopsSecurity']->check()) {
            redirect_header($_SERVER['PHP_SELF'], 2, implode('<br />', $GLOBALS['xoopsSecurity']->getErrors()));
        }
        //try and delete the poll
        // new xoopspoll module
        if ($pollModuleHandler->getVar('version') >= 140) {
            $status = $xpPollHandler->delete($poll_obj);
            if (false !== $status) {
                $xpOptHandler =& xoops_getmodulehandler('option', $GLOBALS['xoopsModuleConfig']['poll_module']);
                $xpLogHandler =& xoops_getmodulehandler('log', $GLOBALS['xoopsModuleConfig']['poll_module']);
                $xpOptHandler->deleteByPollId($poll_id);
                $xpLogHandler->deleteByPollId($poll_id);
            } else {
                $msg = $xpPollHandler->getHtmlErrors();
            }
            // old xoopspoll or umfrage or any clone from them
        } else {
            $status      = $poll_obj->delete();
            $classOption = $classPoll . 'Option';
            $classLog    = $classPoll . 'Log';
            if (false !== $status) {
                $classOption::deleteByPollId($poll_id);
                $classLog::deleteByPollId($poll_id);
            } else {
                $msg = $poll_obj->getHtmlErrors();
            }
        }
        if (false !== $status) {
            include_once $GLOBALS['xoops']->path('class/template.php');
            xoops_template_clear_module_cache($xoopsModule->getVar('mid'));
            xoops_template_clear_module_cache($pollModuleHandler->getVar('mid'));
            // delete comments for this poll
            xoops_comment_delete($xoopsModule->getVar('mid'), $poll_id);

            $topic_obj->setVar('votes', 0); // not sure why we want to clear votes too... but I left it alone
            $topic_obj->setVar('topic_haspoll', 0);
            $topic_obj->setVar('poll_id', 0);
            $success = $topicHandler->insert($topic_obj);
            if (!$success) {
                xoops_error($topicHandler->getHtmlErrors());
                break;
            }
        } else {
            xoops_error($msg);
            break;
        }
        redirect_header("viewtopic.php?topic_id={$topic_id}", 1, _MD_POLL_DBUPDATED);
        break;

    case 'restart':
        // new xoopspoll module
        if ($pollModuleHandler->getVar('version') >= 140) {
            $classConstants        = ucfirst($GLOBALS['xoopsModuleConfig']['poll_module']) . 'Constants';
            $default_poll_duration = $classConstants::DEFAULT_POLL_DURATION;
            // old xoopspoll or umfrage or any clone from them
        } else {
            $default_poll_duration = (86400 * 10);
        }
        $poll_form   = new XoopsThemeForm(_MD_POLL_RESTARTPOLL, 'poll_form', 'polls.php', 'post', true);
        $expire_text = new XoopsFormText(_MD_POLL_EXPIRATION . "<br /><small>" . _MD_POLL_FORMAT . "<br />" . sprintf(_MD_POLL_CURRENTTIME, formatTimestamp(time(), "Y-m-d H:i:s")) . "<br />" . sprintf(_MD_POLL_EXPIREDAT, formatTimestamp($poll_obj->getVar("end_time"), "Y-m-d H:i:s")) . "</small>", "end_time", 20, 19, formatTimestamp(time() + $default_poll_duration, "Y-m-d H:i:s"));
        $poll_form->addElement($expire_text);
        $poll_form->addElement(new XoopsFormRadioYN(_MD_POLL_NOTIFY, 'notify', 1));
        $poll_form->addElement(new XoopsFormRadioYN(_MD_POLL_RESET, 'reset', 0));
        $poll_form->addElement(new XoopsFormHidden('op', 'restart_ok'));
        $poll_form->addElement(new XoopsFormHidden('topic_id', $topic_id));
        $poll_form->addElement(new XoopsFormHidden('poll_id', $poll_id));
        $poll_form->addElement(new XoopsFormButton('', 'poll_submit', _MD_POLL_RESTART, 'submit'));

        echo '<h4>' . _MD_POLL_POLLCONF . "</h4>\n";
        $poll_form->display();
        break;

    case 'restart_ok':
        // check security token
        if (!$GLOBALS['xoopsSecurity']->check()) {
            redirect_header($_SERVER['PHP_SELF'], 2, implode('<br />', $GLOBALS['xoopsSecurity']->getErrors()));
        }

        // new xoopspoll module
        if ($pollModuleHandler->getVar('version') >= 140) {
            $classConstants        = ucfirst($GLOBALS['xoopsModuleConfig']['poll_module']) . 'Constants';
            $default_poll_duration = $classConstants::DEFAULT_POLL_DURATION;
            $poll_not_mailed       = $classConstants::POLL_NOT_MAILED;
            $poll_mailed           = $classConstants::POLL_MAILED;
            // old xoopspoll or umfrage or any clone from them
        } else {
            $default_poll_duration = (86400 * 10);
            $poll_not_mailed       = POLL_NOTMAILED;
            $poll_mailed           = POLL_MAILED;
        }

        $end_time = !XoopsRequest::getInt('end_time', 0, 'POST');
        if (0 !==$end_time) {
            $timezone = (is_object($GLOBALS['xoopsUser'])) ? $GLOBALS['xoopsUser']->getVar("timezone") : null;
            $poll_obj->setVar('end_time', userTimeToServerTime(method_exists('XoopsLocal', 'strtotime') ? XoopsLocal::strtotime($end_time) : strtotime($end_time), $timezone));
        } else {
            $poll_obj->setVar('end_time', time() + $default_poll_duration);
        }

        $isNotify = XoopsRequest::getInt('notify', 0, 'POST');
        if (!empty($isNotify) && ($end_time > time())) {
            // if notify, set mail status to "not mailed"
            $poll_obj->setVar('mail_status', $poll_not_mailed);
        } else {
            // if not notify, set mail status to already "mailed"
            $poll_obj->setVar('mail_status', $poll_mailed);
        }

        // new xoopspoll module
        if ($pollModuleHandler->getVar('version') >= 140) {
            if (!$xpPollHandler->insert($poll_obj)) {  // update the poll
                xoops_error($poll_obj->getHtmlErrors());
                exit();
            }
            if (XoopsRequest::getInt('reset', 0, 'POST')) { // reset all vote/voter counters
                $xpOptHandler =& xoops_getmodulehandler('option', $GLOBALS['xoopsModuleConfig']['poll_module']);
                $xpLogHandler =& xoops_getmodulehandler('log', $GLOBALS['xoopsModuleConfig']['poll_module']);
                $xpLogHandler->deleteByPollId($poll_id);
                $xpOptHandler->resetCountByPollId($poll_id);
                $xpPollHandler->updateCount($poll_obj);
            }
            // old xoopspoll or umfrage or any clone from them
        } else {
            if (!$poll_obj->store()) { // update the poll
                xoops_error($poll_obj->getHtmlErrors());
                exit();
            }
            if (XoopsRequest::getInt('reset', 0, 'POST')) { // reset all logs
                $classOption = $classPoll . 'Option';
                $classLog    = $classPoll . 'Log';
                $classLog::deleteByPollId($poll_id);
                $classOption::resetCountByPollId($poll_id);
                $poll_obj->updateCount();
            }
        }
        include_once $GLOBALS['xoops']->path('class/template.php');
        xoops_template_clear_module_cache($xoopsModule->getVar('mid'));
        xoops_template_clear_module_cache($pollModuleHandler->getVar('mid'));
        redirect_header(XOOPS_URL . "/viewtopic.php?topic_id={$topic_id}", 1, _MD_POLL_DBUPDATED);
        break;

    case 'log':
        // new xoopspoll module
        if ($pollModuleHandler->getVar('version') >= 140) {
            redirect_header($GLOBALS['xoops']->url("modules/{$GLOBALS['xoopsModuleConfig']['poll_module']}/admin/main.php?op=log&amp;poll_id={$poll_id}"), 2, _MD_POLL_VIEWLOG);
            // old xoopspoll or umfrage or any clone from them
        } else {
            redirect_header($GLOBALS['xoops']->url("modules/{$GLOBALS['xoopsModuleConfig']['poll_module']}/admin/index.php?op=log&amp;poll_id={$poll_id}"), 2, _MD_POLL_VIEWLOG);
        }
        break;
} // switch

// irmtfan move to footer.php
include_once __DIR__ . '/footer.php';
include $GLOBALS['xoops']->path('footer.php');
