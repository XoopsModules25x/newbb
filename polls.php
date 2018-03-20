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
 * @copyright       XOOPS Project (https://xoops.org)
 * @license         GNU GPL 2 or later (http://www.gnu.org/licenses/gpl-2.0.html)
 * @package         newbb
 * @since           4.0
 * @author          Taiwen Jiang <phppp@users.sourceforge.net>
 */

use Xmf\Request;
use XoopsModules\Newbb;

// rewrite by irmtfan and zyspec to accept xoopspoll 1.4 and all old xoopspoll and umfrage versions and all clones

require_once __DIR__ . '/header.php';
require_once $GLOBALS['xoops']->path('class/xoopsformloader.php');
require_once $GLOBALS['xoops']->path('class/xoopslists.php');
require_once $GLOBALS['xoops']->path('class/xoopsblock.php');
xoops_load('XoopsLocal');
$op      = 'add';
$goodOps = [
    'add',
    'save',
    'edit',
    'update',
    'addmore',
    'savemore',
    'delete',
    'delete_ok',
    'restart',
    'restart_ok',
    'log'
];
$op      = isset($_REQUEST['op']) ? $_REQUEST['op'] : 'add';
$op      = (!in_array($op, $goodOps)) ? 'add' : $op;

$poll_id  = Request::getInt('poll_id', Request::getInt('poll_id', 0, 'GET'), 'POST');
$topic_id = Request::getInt('topic_id', Request::getInt('topic_id', 0, 'GET'), 'POST');

// deal with permissions
/** @var Newbb\TopicHandler $topicHandler */
$topicHandler = Newbb\Helper::getInstance()->getHandler('Topic');
$topicObject  = $topicHandler->get($topic_id);
// topic exist
if (is_object($topicObject)) {
    $forum_id = $topicObject->getVar('forum_id');
} else {
    redirect_header(XOOPS_URL . '/index.php', 2, _MD_NEWBB_POLLMODULE_ERROR . ': ' . _MD_NEWBB_FORUMNOEXIST);
}
// forum access permission
/** @var Newbb\ForumHandler $forumHandler */
$forumHandler = Newbb\Helper::getInstance()->getHandler('Forum');
$forumObject  = $forumHandler->get($forum_id);
if (!$forumHandler->getPermission($forumObject)) {
    redirect_header(XOOPS_URL . '/index.php', 2, _MD_NEWBB_NORIGHTTOACCESS);
}
// topic view permission
if (!$topicHandler->getPermission($forumObject, $topicObject->getVar('topic_status'), 'view')) {
    redirect_header('viewforum.php?forum=' . $forum_id, 2, _MD_NEWBB_NORIGHTTOVIEW);
}
// poll module
$pollModuleHandler = $moduleHandler->getByDirname($GLOBALS['xoopsModuleConfig']['poll_module']);
if (is_object($pollModuleHandler) && $pollModuleHandler->getVar('isactive')) {
    // new xoopspoll module
    if ($pollModuleHandler->getVar('version') >= 140) {
        xoops_load('constants', $GLOBALS['xoopsModuleConfig']['poll_module']);
        xoops_load('pollUtility', $GLOBALS['xoopsModuleConfig']['poll_module']);
        xoops_load('request', $GLOBALS['xoopsModuleConfig']['poll_module']);
        xoops_loadLanguage('admin', $GLOBALS['xoopsModuleConfig']['poll_module']);
        /** @var \XoopspollPollHandler $xpPollHandler */
        $xpPollHandler = Xoopspoll\Helper::getInstance()->getHandler('Poll');
        /** @var \XoopsPoll $pollObject */
        $pollObject = $xpPollHandler->get($poll_id); // will create poll if poll_id = 0 exist
        // old xoopspoll or umfrage or any clone from them
    } else {
        include $GLOBALS['xoops']->path('modules/' . $GLOBALS['xoopsModuleConfig']['poll_module'] . '/include/constants.php');
        $classPoll  = $topicObject->loadOldPoll();
        $pollObject = new $classPoll($poll_id); // will create poll if poll_id = 0 exist
    }
} else {
    // irmtfan - issue with javascript:history.go(-1)
    redirect_header(Request::getString('HTTP_REFERER', '', 'SERVER'), 2, _MD_NEWBB_POLLMODULE_ERROR);
}
// include header
include $GLOBALS['xoops']->path('header.php');

// no admin user permission
if (is_object($GLOBALS['xoopsUser']) && !newbbIsAdmin($forumObject)) {
    $perm = false;
    if ($topicHandler->getPermission($forumObject, $topicObject->getVar('topic_status'), 'addpoll')) {
        if (('add' === $op || 'save' === $op || 'update' === $op) && !$topicObject->getVar('topic_haspoll')
            && ($GLOBALS['xoopsUser']->getVar('uid') == $topicObject->getVar('topic_poster'))) {
            $perm = true;
        } elseif (!empty($poll_id) && ($GLOBALS['xoopsUser']->getVar('uid') == $pollObject->getVar('user_id'))) {
            $perm = true;
        }
    }
    if (!$perm) {
        redirect_header("viewtopic.php?topic_id={$topic_id}", 2, _NOPERM);
    }
}
switch ($op) {
    case 'add':
        // new xoopspoll module
        if ($pollModuleHandler->getVar('version') >= 140) {
            echo '<h4>' . _MD_NEWBB_POLL_CREATNEWPOLL . "</h4>\n";
            $pollObject->renderForm(Request::getString('PHP_SELF', '', 'SERVER'), 'post', ['topic_id' => $topic_id]);
        // old xoopspoll or umfrage or any clone from them
        } else {
            $classOption  = $classPoll . 'Option';
            $poll_form    = new \XoopsThemeForm(_MD_NEWBB_POLL_CREATNEWPOLL, 'poll_form', 'polls.php', 'post', true);
            $author_label = new \XoopsFormLabel(_MD_NEWBB_POLL_AUTHOR, is_object($GLOBALS['xoopsUser']) ? ("<a href='"
                                                                                                          . XOOPS_URL
                                                                                                          . '/userinfo.php?uid='
                                                                                                          . $GLOBALS['xoopsUser']->getVar('uid')
                                                                                                          . "'>"
                                                                                                          . newbbGetUnameFromId($GLOBALS['xoopsUser']->getVar('uid'), $GLOBALS['xoopsModuleConfig']['show_realname'])
                                                                                                          . '</a>') : $GLOBALS['xoopsConfig']['anonymous']);
            $poll_form->addElement($author_label);
            $question_text = new \XoopsFormText(_MD_NEWBB_POLL_POLLQUESTION, 'question', 50, 255);
            $poll_form->addElement($question_text);
            $desc_tarea = new \XoopsFormTextarea(_MD_NEWBB_POLL_POLLDESC, 'description');
            $poll_form->addElement($desc_tarea);
            $currenttime = formatTimestamp(time(), 'Y-m-d H:i:s');
            $endtime     = formatTimestamp(time() + 604800, 'Y-m-d H:i:s');
            $expire_text = new \XoopsFormText(_MD_NEWBB_POLL_EXPIRATION . '<br><small>' . _MD_NEWBB_POLL_FORMAT . '<br>' . sprintf(_MD_NEWBB_POLL_CURRENTTIME, $currenttime) . '</small>', 'end_time', 30, 19, $endtime);
            $poll_form->addElement($expire_text);

            $weight_text = new \XoopsFormText(_MD_NEWBB_POLL_DISPLAYORDER, 'weight', 6, 5, 0);
            $poll_form->addElement($weight_text);

            $multi_yn = new \XoopsFormRadioYN(_MD_NEWBB_POLL_ALLOWMULTI, 'multiple', 0);
            $poll_form->addElement($multi_yn);

            $notify_yn = new \XoopsFormRadioYN(_MD_NEWBB_POLL_NOTIFY, 'notify', 1);
            $poll_form->addElement($notify_yn);

            $option_tray    = new \XoopsFormElementTray(_MD_NEWBB_POLL_POLLOPTIONS, '');
            $barcolor_array = \XoopsLists::getImgListAsArray($GLOBALS['xoops']->path('modules/' . $GLOBALS['xoopsModuleConfig']['poll_module'] . '/assets/images/colorbars/'));
            for ($i = 0; $i < 10; ++$i) {
                $current_bar = ('blank.gif' !== current($barcolor_array)) ? current($barcolor_array) : next($barcolor_array);
                $option_text = new \XoopsFormText('', 'option_text[]', 50, 255);
                $option_tray->addElement($option_text);
                $color_select = new \XoopsFormSelect('', "option_color[{$i}]", $current_bar);
                $color_select->addOptionArray($barcolor_array);
                $color_select->setExtra("onchange='showImgSelected(\"option_color_image[{$i}]\", \"option_color[{$i}]\", \"modules/" . $GLOBALS['xoopsModuleConfig']['poll_module'] . '/assets/images/colorbars", "", "' . XOOPS_URL . "\")'");
                $color_label = new \XoopsFormLabel('', "<img src='"
                                                      . XOOPS_URL
                                                      . '/modules/'
                                                      . $GLOBALS['xoopsModuleConfig']['poll_module']
                                                      . '/assets/images/colorbars/'
                                                      . $current_bar
                                                      . "' name='option_color_image[{$i}]' id='option_color_image[{$i}]' width='30' align='bottom' height='15' alt='' /><br>");
                $option_tray->addElement($color_select);
                $option_tray->addElement($color_label);
                if (!next($barcolor_array)) {
                    reset($barcolor_array);
                }
                unset($color_select, $color_label);
            }
            $poll_form->addElement($option_tray);

            $poll_form->addElement(new \XoopsFormHidden('op', 'save'));
            $poll_form->addElement(new \XoopsFormHidden('topic_id', $topic_id));
            $poll_form->addElement(new \XoopsFormHidden('poll_id', $poll_id));
            $poll_form->addElement(new \XoopsFormHidden('user_id', is_object($GLOBALS['xoopsUser']) ? $GLOBALS['xoopsUser']->getVar('uid') : 0));
            $poll_form->addElement(new \XoopsFormButtonTray('poll_submit', _SUBMIT, 'submit'));
            echo '<h4>' . _MD_NEWBB_POLL_POLLCONF . '</h4>';
            $poll_form->display();
        }
        break; // op: add

    case 'edit':
        // new xoopspoll module
        if ($pollModuleHandler->getVar('version') >= 140) {
            echo '<h4>' . _MD_NEWBB_POLL_EDITPOLL . "</h4>\n";
            $pollObject->renderForm(Request::getString('PHP_SELF', '', 'SERVER'), 'post', ['topic_id' => $topic_id]);
        // old xoopspoll or umfrage or any clone from them
        } else {
            $classOption  = $classPoll . 'Option';
            $poll_form    = new \XoopsThemeForm(_MD_NEWBB_POLL_EDITPOLL, 'poll_form', 'polls.php', 'post', true);
            $author_label = new \XoopsFormLabel(_MD_NEWBB_POLL_AUTHOR, "<a href='" . XOOPS_URL . '/userinfo.php?uid=' . $pollObject->getVar('user_id') . "'>" . newbbGetUnameFromId($pollObject->getVar('user_id'), $GLOBALS['xoopsModuleConfig']['show_realname']) . '</a>');
            $poll_form->addElement($author_label);
            $question_text = new \XoopsFormText(_MD_NEWBB_POLL_POLLQUESTION, 'question', 50, 255, $pollObject->getVar('question', 'E'));
            $poll_form->addElement($question_text);
            $desc_tarea = new \XoopsFormTextarea(_MD_NEWBB_POLL_POLLDESC, 'description', $pollObject->getVar('description', 'E'));
            $poll_form->addElement($desc_tarea);
            $date = formatTimestamp($pollObject->getVar('end_time'), 'Y-m-d H:i:s'); // important "Y-m-d H:i:s" use in jdf function
            if (!$pollObject->hasExpired()) {
                $expire_text = new \XoopsFormText(_MD_NEWBB_POLL_EXPIRATION . '<br><small>' . _MD_NEWBB_POLL_FORMAT . '<br>' . sprintf(_MD_NEWBB_POLL_CURRENTTIME, formatTimestamp(time(), 'Y-m-d H:i:s')) . '</small>', 'end_time', 20, 19, $date);
                $poll_form->addElement($expire_text);
            } else {
                // irmtfan full URL - add topic_id
                $restart_label = new \XoopsFormLabel(
                    _MD_NEWBB_POLL_EXPIRATION,
                                                    sprintf(_MD_NEWBB_POLL_EXPIREDAT, $date) . "<br><a href='" . XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname') . "/polls.php?op=restart&amp;poll_id={$poll_id}&amp;topic_id={$topic_id}'>" . _MD_NEWBB_POLL_RESTART . '</a>'
                );
                $poll_form->addElement($restart_label);
            }
            $weight_text = new \XoopsFormText(_MD_NEWBB_POLL_DISPLAYORDER, 'weight', 6, 5, $pollObject->getVar('weight'));
            $poll_form->addElement($weight_text);
            $multi_yn = new \XoopsFormRadioYN(_MD_NEWBB_POLL_ALLOWMULTI, 'multiple', $pollObject->getVar('multiple'));
            $poll_form->addElement($multi_yn);
            $options_arr  =& $classOption::getAllByPollId($poll_id);
            $notify_value = 1;
            if (0 !== $pollObject->getVar('mail_status')) {
                $notify_value = 0;
            }
            $notify_yn = new \XoopsFormRadioYN(_MD_NEWBB_POLL_NOTIFY, 'notify', $notify_value);
            $poll_form->addElement($notify_yn);
            $option_tray    = new \XoopsFormElementTray(_MD_NEWBB_POLL_POLLOPTIONS, '');
            $barcolor_array = \XoopsLists::getImgListAsArray($GLOBALS['xoops']->path("modules/{$GLOBALS['xoopsModuleConfig']['poll_module']}/assets/images/colorbars/"));
            $i              = 0;
            foreach ($options_arr as $option) {
                /** @var \XoopsPoll $option */
                $option_tray->addElement(new \XoopsFormText('', 'option_text[]', 50, 255, $option->getVar('option_text')));
                $option_tray->addElement(new \XoopsFormHidden('option_id[]', $option->getVar('option_id')));
                $color_select = new \XoopsFormSelect('', 'option_color[{$i}]', $option->getVar('option_color'));
                $color_select->addOptionArray($barcolor_array);
                $color_select->setExtra("onchange='showImgSelected(\"option_color_image[{$i}]\", \"option_color[" . $i . "]\", \"modules/{$GLOBALS['xoopsModuleConfig']['poll_module']}/assets/images/colorbars\", \"\", \"" . XOOPS_URL . "\")'");
                $color_label = new \XoopsFormLabel('', "<img src='"
                                                      . $GLOBALS['xoops']->url("modules/{$GLOBALS['xoopsModuleConfig']['poll_module']}/assets/images/colorbars/" . $option->getVar('option_color', 'E'))
                                                      . "' name='option_color_image[{$i}]' id='option_color_image[{$i}]' class='alignbottom' width='30' height='15' alt='' /><br>");
                $option_tray->addElement($color_select);
                $option_tray->addElement($color_label);
                unset($color_select, $color_label);
                ++$i;
            }
            // irmtfan full URL
            $more_label = new \XoopsFormLabel('', "<br><a href='" . XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname') . "/polls.php?op=addmore&amp;poll_id={$poll_id}&amp;topic_id={$topic_id}'>" . _MD_NEWBB_POLL_ADDMORE . '</a>');
            $option_tray->addElement($more_label);
            $poll_form->addElement($option_tray);
            $poll_form->addElement(new \XoopsFormHidden('op', 'update'));
            $poll_form->addElement(new \XoopsFormHidden('topic_id', $topic_id));
            $poll_form->addElement(new \XoopsFormHidden('poll_id', $poll_id));
            $poll_form->addElement(new \XoopsFormButtonTray('poll_submit', _SUBMIT, 'submit'));

            echo '<h4>' . _MD_NEWBB_POLL_POLLCONF . "</h4>\n";
            $poll_form->display();
        }
        break; // op: edit

    case 'save':
        // old xoopspoll or umfrage or any clone from them
        if ($pollModuleHandler->getVar('version') < 140) {
            // check security token
            if (!$GLOBALS['xoopsSecurity']->check()) {
                redirect_header(Request::getString('PHP_SELF', '', 'SERVER'), 2, implode('<br>', $GLOBALS['xoopsSecurity']->getErrors()));
            }
            /*
             * The option check should be done before submitting
             */
            $option_empty = true;
            if (!Request::getString('option_text', '', 'POST')) {
                // irmtfan - issue with javascript:history.go(-1)
                redirect_header(Request::getString('HTTP_REFERER', '', 'SERVER'), 2, _MD_NEWBB_ERROROCCURED . ': ' . _MD_NEWBB_POLL_POLLOPTIONS . ' !');
            }
            $option_text = Request::getArray('option_text', '', 'POST');
            foreach ($option_text as $optxt) {
                if ('' !== trim($optxt)) {
                    $option_empty = false;
                    break;
                }
            }
            if ($option_empty) {
                // irmtfan - issue with javascript:history.go(-1)
                redirect_header(Request::getString('HTTP_REFERER', '', 'SERVER'), 2, _MD_NEWBB_ERROROCCURED . ': ' . _MD_NEWBB_POLL_POLLOPTIONS . ' !');
            }
            $pollObject->setVar('question', Request::getString('question', '', 'POST'));
            $pollObject->setVar('description', Request::getString('description', '', 'POST'));
            $end_time = Request::getString('end_time', '', 'POST'); // (empty($_POST['end_time'])) ? "" : $_POST['end_time'];
            if ('' !== $end_time) {
                $timezone = is_object($GLOBALS['xoopsUser']) ? $GLOBALS['xoopsUser']->getVar('timezone') : null;
                $pollObject->setVar('end_time', userTimeToServerTime(method_exists('XoopsLocal', 'strtotime') ? XoopsLocal::strtotime($end_time) : strtotime($end_time), $timezone));
            } else {
                // if expiration date is not set, set it to 10 days from now
                $pollObject->setVar('end_time', time() + (86400 * 10));
            }

            $pollObject->setVar('display', 0);
            $pollObject->setVar('weight', Request::getInt('weight', 0, 'POST'));
            $pollObject->setVar('multiple', Request::getInt('multiple', 0, 'POST'));
            $pollObject->setVar('user_id', Request::getInt('user_id', 0, 'POST'));
            if (Request::getInt('notify', 0, 'POST') && $end_time > time()) {
                // if notify, set mail status to "not mailed"
                $pollObject->setVar('mail_status', POLL_NOTMAILED);
            } else {
                // if not notify, set mail status to already "mailed"
                $pollObject->setVar('mail_status', POLL_MAILED);
            }
            $new_poll_id = $pollObject->store();
            if (empty($new_poll_id)) {
                xoops_error($pollObject->getHtmlErrors);
                break;
            }
            $i            = 0;
            $option_color = Request::getArray('option_color', null, 'POST');
            $classOption  = $classPoll . 'Option';
            foreach ($option_text as $optxt) {
                $optxt = trim($optxt);
                /** @var XoopspollOption $optionObject */
                $optionObject = new $classOption();
                if ('' !== $optxt) {
                    $optionObject->setVar('option_text', $optxt);
                    $optionObject->setVar('option_color', $option_color[$i]);
                    $optionObject->setVar('poll_id', $new_poll_id);
                    $optionObject->store();
                }
                ++$i;
            }
            // clear the template cache so changes take effect immediately
            require_once $GLOBALS['xoops']->path('class/template.php');
            xoops_template_clear_module_cache($GLOBALS['xoopsModule']->getVar('mid'));
            xoops_template_clear_module_cache($pollModuleHandler->getVar('mid'));

            // update topic to indicate it has a poll
            $topicObject->setVar('topic_haspoll', 1);
            $topicObject->setVar('poll_id', $new_poll_id);
            $success = $topicHandler->insert($topicObject);
            if (!$success) {
                xoops_error($topicHandler->getHtmlErrors());
            } else {
                redirect_header("viewtopic.php?topic_id={$topic_id}", 2, _MD_NEWBB_POLL_DBUPDATED);
            }
            break;// op: save
        }
    // no break
    case 'update':
        // check security token
        if (!$GLOBALS['xoopsSecurity']->check()) {
            redirect_header(Request::getString('PHP_SELF', '', 'SERVER'), 2, implode('<br>', $GLOBALS['xoopsSecurity']->getErrors()));
        }
        /* make sure there's at least one option */
        $option_text   = Request::getString('option_text', '', 'POST');
        $option_string = is_array($option_text) ? implode('', $option_text) : $option_text;
        $option_string = trim($option_string);
        if ('' === $option_string) {
            redirect_header(Request::getString('HTTP_REFERER', '', 'SERVER'), 2, _MD_NEWBB_ERROROCCURED . ': ' . _MD_NEWBB_POLL_POLLOPTIONS . ' !');
        }

        // new xoopspoll module
        if ($pollModuleHandler->getVar('version') >= 140) {
            /** @var \XoopspollOptionHandler $xpOptHandler */
            $xpOptHandler = Xoopspoll\Helper::getInstance()->getHandler('Option');
            /** @var \XoopspollLogHandler $xpLogHandler */
            $xpLogHandler = Xoopspoll\Helper::getInstance()->getHandler('Log');
            //            $classRequest = ucfirst($GLOBALS['xoopsModuleConfig']["poll_module"]) . "Request";
            $classConstants   = ucfirst($GLOBALS['xoopsModuleConfig']['poll_module']) . 'Constants';
            $notify           = Request::getInt('notify', $classConstants::NOTIFICATION_ENABLED, 'POST');
            $currentTimestamp = time();
            //$xuEndTimestamp   = method_exists('XoopsLocal', 'strtotime') ? XoopsLocal::strtotime(Request::getString('xu_end_time', null, 'POST'))
            //                                                             : strtotime(Request::getString('xu_end_time', null, 'POST'));
            $xuEndTimestamp = strtotime(Request::getString('xu_end_time', null, 'POST'));
            $endTimestamp   = (!Request::getString('xu_end_time', null, 'POST')) ? ($currentTimestamp + $classConstants::DEFAULT_POLL_DURATION) : userTimeToServerTime($xuEndTimestamp);
            //$xuStartTimestamp = method_exists('XoopsLocal', 'strtotime') ? XoopsLocal::strtotime(Request::getString('xu_start_time', null, 'POST'))
            //                                                             : strtotime(Request::getString('xu_start_time', null, 'POST'));
            $xuStartTimestamp = strtotime(Request::getString('xu_start_time', null, 'POST'));
            $startTimestamp   = (!Request::getString('xu_start_time', null, 'POST')) ? ($endTimestamp - $classConstants::DEFAULT_POLL_DURATION) : userTimeToServerTime($xuStartTimestamp);

            //  don't allow changing start time if there are votes in the log
            if (($startTimestamp < $pollObject->getVar('start_time'))
                && ($xpLogHandler->getTotalVotesByPollId($poll_id) > 0)) {
                $startTimestamp = $pollObject->getVar('start_time'); //don't change start time
            }

            $poll_vars = [
                'user_id'     => Request::getInt('user_id', $GLOBALS['xoopsUser']->uid(), 'POST'),
                'question'    => Request::getString('question', null, 'POST'),
                'description' => Request::getText('description', null, 'POST'),
                'mail_status' => ($classConstants::NOTIFICATION_ENABLED == $notify) ? $classConstants::POLL_NOT_MAILED : $classConstants::POLL_MAILED,
                'mail_voter'  => Request::getInt('mail_voter', $classConstants::NOT_MAIL_POLL_TO_VOTER, 'POST'),
                'start_time'  => $startTimestamp,
                'end_time'    => $endTimestamp,
                'display'     => Request::getInt('display', $classConstants::DO_NOT_DISPLAY_POLL_IN_BLOCK, 'POST'),
                'visibility'  => Request::getInt('visibility', $classConstants::HIDE_NEVER, 'POST'),
                'weight'      => Request::getInt('weight', $classConstants::DEFAULT_WEIGHT, 'POST'),
                'multiple'    => Request::getInt('multiple', $classConstants::NOT_MULTIPLE_SELECT_POLL, 'POST'),
                'multilimit'  => Request::getInt('multilimit', $classConstants::MULTIPLE_SELECT_LIMITLESS, 'POST'),
                'anonymous'   => Request::getInt('anonymous', $classConstants::ANONYMOUS_VOTING_DISALLOWED, 'POST')
            ];
            $pollObject->setVars($poll_vars);
            $poll_id = $xpPollHandler->insert($pollObject);
            if (!$poll_id) {
                $err = $pollObject->getHtmlErrors();
                exit($err);
            }

            // now get the options
            $optionIdArray    = Request::getArray('option_id', [], 'POST');
            $optionIdArray    = array_map('intval', $optionIdArray);
            $optionTextArray  = Request::getArray('option_text', [], 'POST');
            $optionColorArray = Request::getArray('option_color', [], 'POST');

            foreach ($optionIdArray as $key => $oId) {
                if (!empty($oId) && ($optionObject = $xpOptHandler->get($oId))) {
                    // existing option object so need to update it
                    $optionTextArray[$key] = trim($optionTextArray[$key]);
                    if ('' === $optionTextArray[$key]) {
                        // want to delete this option
                        if (false !== $xpOptHandler->delete($optionObject)) {
                            // now remove it from the log
                            $xpLogHandler->deleteByOptionId($optionObject->getVar('option_id'));
                            //update vote count in poll
                            $xpPollHandler->updateCount($pollObject);
                        } else {
                            xoops_error($xpLogHandler->getHtmlErrors());
                            break;
                        }
                    } else {
                        $optionObject->setVar('option_text', $optionTextArray[$key]);
                        $optionObject->setVar('option_color', $optionColorArray[$key]);
                        $optionObject->setVar('poll_id', $poll_id);
                        $xpOptHandler->insert($optionObject);
                    }
                } else {
                    // new option object
                    $optionObject          = $xpOptHandler->create();
                    $optionTextArray[$key] = trim($optionTextArray[$key]);
                    if ('' !== $optionTextArray[$key]) { // ignore if text is empty
                        $optionObject->setVar('option_text', $optionTextArray[$key]);
                        $optionObject->setVar('option_color', $optionColorArray[$key]);
                        $optionObject->setVar('poll_id', $poll_id);
                        $xpOptHandler->insert($optionObject);
                    }
                    unset($optionObject);
                }
            }
            // old xoopspoll or umfrage or any clone from them
        } else {
            $pollObject->setVar('question', Request::getString('question', '', 'POST'));
            $pollObject->setVar('description', Request::getString('description', '', 'POST'));

            $end_time = Request::getString('end_time', '', 'POST');
            if ('' !== $end_time) {
                $timezone = is_object($GLOBALS['xoopsUser']) ? $GLOBALS['xoopsUser']->getVar('timezone') : null;
                $pollObject->setVar('end_time', userTimeToServerTime(method_exists('XoopsLocal', 'strtotime') ? XoopsLocal::strtotime($end_time) : strtotime($end_time), $timezone));
            }
            $pollObject->setVar('display', 0);
            $pollObject->setVar('weight', Request::getInt('weight', 0, 'POST'));
            $pollObject->setVar('multiple', Request::getInt('multiple', 0, 'POST'));
            $pollObject->setVar('user_id', Request::getInt('user_id', 0, 'POST'));
            if (Request::getInt('notify', 0, 'POST') && $end_time > time()) {
                // if notify, set mail status to "not mailed"
                $pollObject->setVar('mail_status', POLL_NOTMAILED);
            } else {
                // if not notify, set mail status to already "mailed"
                $pollObject->setVar('mail_status', POLL_MAILED);
            }

            if (!$pollObject->store()) {
                xoops_error($pollObject->getHtmlErrors);
                break;
            }
            $i            = 0;
            $option_id    = Request::getArray('option_id', null, 'POST');
            $option_color = Request::getArray('option_color', null, 'POST');
            $classOption  = $classPoll . 'Option';
            $classLog     = $classPoll . 'Log';
            foreach ($option_id as $opid) {
                $optionObject    = new $classOption($opid);
                $option_text[$i] = trim($option_text[$i]);
                if ('' !== $option_text[$i]) {
                    $optionObject->setVar('option_text', $option_text[$i]);
                    $optionObject->setVar('option_color', $option_color[$i]);
                    $optionObject->store();
                } else {
                    if (false !== $optionObject->delete()) {
                        $classLog::deleteByOptionId($option->getVar('option_id'));
                    }
                }
                ++$i;
            }
            $pollObject->updateCount();
        }
        // clear the template cache so changes take effect immediately
        require_once $GLOBALS['xoops']->path('class/template.php');
        xoops_template_clear_module_cache($GLOBALS['xoopsModule']->getVar('mid'));
        xoops_template_clear_module_cache($pollModuleHandler->getVar('mid'));

        // update topic to indicate it has a poll
        $topicObject->setVar('topic_haspoll', 1);
        $topicObject->setVar('poll_id', $pollObject->getVar('poll_id'));
        $success = $topicHandler->insert($topicObject);
        if (!$success) {
            xoops_error($topicHandler->getHtmlErrors());
        } else {
            redirect_header("viewtopic.php?topic_id={$topic_id}", 2, _MD_NEWBB_POLL_DBUPDATED);
        }
        break;// op: save | update

    case 'addmore':
        $question = $pollObject->getVar('question');
        unset($pollObject);
        $poll_form = new \XoopsThemeForm(_MD_NEWBB_POLL_ADDMORE, 'poll_form', 'polls.php', 'post', true);
        $poll_form->addElement(new \XoopsFormLabel(_MD_NEWBB_POLL_POLLQUESTION, $question));
        // new xoopspoll module
        if ($pollModuleHandler->getVar('version') >= 140) {
            $xpOptHandler = Xoopspoll\Helper::getInstance()->getHandler('Option');
            $option_tray  = $xpOptHandler->renderOptionFormTray($poll_id);
        // old xoopspoll or umfrage or any clone from them
        } else {
            $option_tray    = new \XoopsFormElementTray(_MD_NEWBB_POLL_POLLOPTIONS, '');
            $barcolor_array = \XoopsLists::getImgListAsArray($GLOBALS['xoops']->path('modules/' . $GLOBALS['xoopsModuleConfig']['poll_module'] . '/assets/images/colorbars/'));
            for ($i = 0; $i < 10; ++$i) {
                $current_bar = ('blank.gif' !== current($barcolor_array)) ? current($barcolor_array) : next($barcolor_array);
                $option_text = new \XoopsFormText('', 'option_text[]', 50, 255);
                $option_tray->addElement($option_text);
                $color_select = new \XoopsFormSelect('', "option_color[{$i}]", $current_bar);
                $color_select->addOptionArray($barcolor_array);
                $color_select->setExtra("onchange='showImgSelected(\"option_color_image[{$i}]\", \"option_color[{$i}]\", \"modules/{$GLOBALS['xoopsModuleConfig']['poll_module']}/assets/images/colorbars\", \"\", \"" . XOOPS_URL . "\")'");
                $color_label = new \XoopsFormLabel('', "<img src='"
                                                      . $GLOBALS['xoops']->url("modules/{$GLOBALS['xoopsModuleConfig']['poll_module']}/assets/images/colorbars/{$current_bar}")
                                                      . "' name='option_color_image[{$i}]' id='option_color_image[{$i}]' class='alignbottom' width='30' height='15' alt='' /><br>");
                $option_tray->addElement($color_select);
                $option_tray->addElement($color_label);
                unset($color_select, $color_label, $option_text);
                if (!next($barcolor_array)) {
                    reset($barcolor_array);
                }
            }
        }
        $poll_form->addElement($option_tray);
        $poll_form->addElement(new \XoopsFormButtonTray('poll_submit', _SUBMIT, 'submit'));
        $poll_form->addElement(new \XoopsFormHidden('op', 'savemore'));
        $poll_form->addElement(new \XoopsFormHidden('topic_id', $topic_id));
        $poll_form->addElement(new \XoopsFormHidden('poll_id', $poll_id));

        echo '<h4>' . _MD_NEWBB_POLL_POLLCONF . "</h4>\n";
        $poll_form->display();
        break;

    case 'savemore':
        // check security token
        if (!$GLOBALS['xoopsSecurity']->check()) {
            redirect_header(Request::getString('PHP_SELF', '', 'SERVER'), 2, implode('<br>', $GLOBALS['xoopsSecurity']->getErrors()));
        }

        $option_text   = Request::getString('option_text', '', 'POST');
        $option_string = is_array($option_text) ? implode('', $option_text) : $option_text;
        $option_string = trim($option_string);
        if ('' === $option_string) {
            // irmtfan - issue with javascript:history.go(-1)
            redirect_header(Request::getString('HTTP_REFERER', '', 'SERVER'), 2, _MD_NEWBB_ERROROCCURED . ': ' . _MD_NEWBB_POLL_POLLOPTIONS . ' !');
        }
        $i            = 0;
        $option_color = Request::getArray('option_color', null, 'POST');
        foreach ($option_text as $optxt) {
            $optxt = trim($optxt);
            if ('' !== $optxt) {
                // new xoopspoll module
                if ($pollModuleHandler->getVar('version') >= 140) {
                    $xpOptHandler = Xoopspoll\Helper::getInstance()->getHandler('Option');
                    $optionObject = $xpOptHandler->create();
                    $optionObject->setVar('option_text', $optxt);
                    $optionObject->setVar('poll_id', $poll_id);
                    $optionObject->setVar('option_color', $option_color[$i]);
                    $xpOptHandler->insert($optionObject);
                // old xoopspoll or umfrage or any clone from them
                } else {
                    $classOption  = $classPoll . 'Option';
                    $optionObject = new $classOption();
                    $optionObject->setVar('option_text', $optxt);
                    $optionObject->setVar('poll_id', $poll_id);
                    $optionObject->setVar('option_color', $option_color[$i]);
                    $optionObject->store();
                }
                unset($optionObject);
            }
            ++$i;
        }
        require_once $GLOBALS['xoops']->path('class/template.php');
        xoops_template_clear_module_cache($GLOBALS['xoopsModule']->getVar('mid'));
        xoops_template_clear_module_cache($pollModuleHandler->getVar('mid'));
        redirect_header("polls.php?op=edit&amp;poll_id={$poll_id}&amp;topic_id={$topic_id}", 2, _MD_NEWBB_POLL_DBUPDATED);
        break;

    case 'delete':
        echo '<h4>' . _MD_NEWBB_POLL_POLLCONF . "</h4>\n";
        xoops_confirm(['op' => 'delete_ok', 'topic_id' => $topic_id, 'poll_id' => $poll_id], 'polls.php', sprintf(_MD_NEWBB_POLL_RUSUREDEL, $pollObject->getVar('question')));
        break;

    case 'delete_ok':
        // check security token
        if (!$GLOBALS['xoopsSecurity']->check()) {
            redirect_header(Request::getString('PHP_SELF', '', 'SERVER'), 2, implode('<br>', $GLOBALS['xoopsSecurity']->getErrors()));
        }
        //try and delete the poll
        // new xoopspoll module
        if ($pollModuleHandler->getVar('version') >= 140) {
            $status = $xpPollHandler->delete($pollObject);
            if (false !== $status) {
                $xpOptHandler = Xoopspoll\Helper::getInstance()->getHandler('Option');
                $xpLogHandler = Xoopspoll\Helper::getInstance()->getHandler('Log');
                $xpOptHandler->deleteByPollId($poll_id);
                $xpLogHandler->deleteByPollId($poll_id);
            } else {
                $msg = $xpPollHandler->getHtmlErrors();
            }
            // old xoopspoll or umfrage or any clone from them
        } else {
            $status      = $pollObject->delete();
            $classOption = $classPoll . 'Option';
            $classLog    = $classPoll . 'Log';
            if (false !== $status) {
                $classOption::deleteByPollId($poll_id);
                $classLog::deleteByPollId($poll_id);
            } else {
                $msg = $pollObject->getHtmlErrors();
            }
        }
        if (false !== $status) {
            require_once $GLOBALS['xoops']->path('class/template.php');
            xoops_template_clear_module_cache($xoopsModule->getVar('mid'));
            xoops_template_clear_module_cache($pollModuleHandler->getVar('mid'));
            // delete comments for this poll
            xoops_comment_delete($xoopsModule->getVar('mid'), $poll_id);

            $topicObject->setVar('votes', 0); // not sure why we want to clear votes too... but I left it alone
            $topicObject->setVar('topic_haspoll', 0);
            $topicObject->setVar('poll_id', 0);
            $success = $topicHandler->insert($topicObject);
            if (!$success) {
                xoops_error($topicHandler->getHtmlErrors());
                break;
            }
        } else {
            xoops_error($msg);
            break;
        }
        redirect_header(XOOPS_URL . "/modules/newbb/viewtopic.php?topic_id={$topic_id}", 1, _MD_NEWBB_POLL_DBUPDATED);
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
        $poll_form   = new \XoopsThemeForm(_MD_NEWBB_POLL_RESTARTPOLL, 'poll_form', 'polls.php', 'post', true);
        $expire_text = new \XoopsFormText(
            _MD_NEWBB_POLL_EXPIRATION . '<br><small>' . _MD_NEWBB_POLL_FORMAT . '<br>' . sprintf(_MD_NEWBB_POLL_CURRENTTIME, formatTimestamp(time(), 'Y-m-d H:i:s')) . '<br>' . sprintf(
            _MD_NEWBB_POLL_EXPIREDAT,
                                                                                                                                                                                                                     formatTimestamp($pollObject->getVar('end_time'), 'Y-m-d H:i:s')
        ) . '</small>',
                                         'end_time',
            20,
            19,
            formatTimestamp(time() + $default_poll_duration, 'Y-m-d H:i:s')
        );
        $poll_form->addElement($expire_text);
        $poll_form->addElement(new \XoopsFormRadioYN(_MD_NEWBB_POLL_NOTIFY, 'notify', 1));
        $poll_form->addElement(new \XoopsFormRadioYN(_MD_NEWBB_POLL_RESET, 'reset', 0));
        $poll_form->addElement(new \XoopsFormHidden('op', 'restart_ok'));
        $poll_form->addElement(new \XoopsFormHidden('topic_id', $topic_id));
        $poll_form->addElement(new \XoopsFormHidden('poll_id', $poll_id));
        $poll_form->addElement(new \XoopsFormButton('', 'poll_submit', _MD_NEWBB_POLL_RESTART, 'submit'));

        echo '<h4>' . _MD_NEWBB_POLL_POLLCONF . "</h4>\n";
        $poll_form->display();
        break;

    case 'restart_ok':
        // check security token
        if (!$GLOBALS['xoopsSecurity']->check()) {
            redirect_header(Request::getString('PHP_SELF', '', 'SERVER'), 2, implode('<br>', $GLOBALS['xoopsSecurity']->getErrors()));
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

        $end_time = !Request::getInt('end_time', 0, 'POST');
        if (0 !== $end_time) {
            $timezone = is_object($GLOBALS['xoopsUser']) ? $GLOBALS['xoopsUser']->getVar('timezone') : null;
            $pollObject->setVar('end_time', userTimeToServerTime(method_exists('XoopsLocal', 'strtotime') ? XoopsLocal::strtotime($end_time) : strtotime($end_time), $timezone));
        } else {
            $pollObject->setVar('end_time', time() + $default_poll_duration);
        }

        $isNotify = Request::getInt('notify', 0, 'POST');
        if (!empty($isNotify) && ($end_time > time())) {
            // if notify, set mail status to "not mailed"
            $pollObject->setVar('mail_status', $poll_not_mailed);
        } else {
            // if not notify, set mail status to already "mailed"
            $pollObject->setVar('mail_status', $poll_mailed);
        }

        // new xoopspoll module
        if ($pollModuleHandler->getVar('version') >= 140) {
            if (!$xpPollHandler->insert($pollObject)) {  // update the poll
                xoops_error($pollObject->getHtmlErrors());
                exit();
            }
            if (Request::getInt('reset', 0, 'POST')) { // reset all vote/voter counters
                /** @var \XoopspollOptionHandler $xpOptHandler */
                $xpOptHandler = Xoopspoll\Helper::getInstance()->getHandler('Option');
                /** @var \XoopspollLogHandler $xpLogHandler */
                $xpLogHandler = Xoopspoll\Helper::getInstance()->getHandler('Log');
                $xpLogHandler->deleteByPollId($poll_id);
                $xpOptHandler->resetCountByPollId($poll_id);
                $xpPollHandler->updateCount($pollObject);
            }
            // old xoopspoll or umfrage or any clone from them
        } else {
            if (!$pollObject->store()) { // update the poll
                xoops_error($pollObject->getHtmlErrors());
                exit();
            }
            if (Request::getInt('reset', 0, 'POST')) { // reset all logs
                $classOption = $classPoll . 'Option';
                $classLog    = $classPoll . 'Log';
                $classLog::deleteByPollId($poll_id);
                $classOption::resetCountByPollId($poll_id);
                $pollObject->updateCount();
            }
        }
        require_once $GLOBALS['xoops']->path('class/template.php');
        xoops_template_clear_module_cache($xoopsModule->getVar('mid'));
        xoops_template_clear_module_cache($pollModuleHandler->getVar('mid'));
        redirect_header(XOOPS_URL . "/modules/newbb/viewtopic.php?topic_id={$topic_id}", 1, _MD_NEWBB_POLL_DBUPDATED);
        break;

    case 'log':
        // new xoopspoll module
        if ($pollModuleHandler->getVar('version') >= 140) {
            redirect_header($GLOBALS['xoops']->url("modules/{$GLOBALS['xoopsModuleConfig']['poll_module']}/admin/main.php?op=log&amp;poll_id={$poll_id}"), 2, _MD_NEWBB_POLL_VIEWLOG);
        // old xoopspoll or umfrage or any clone from them
        } else {
            redirect_header($GLOBALS['xoops']->url("modules/{$GLOBALS['xoopsModuleConfig']['poll_module']}/admin/index.php?op=log&amp;poll_id={$poll_id}"), 2, _MD_NEWBB_POLL_VIEWLOG);
        }
        break;
} // switch

// irmtfan move to footer.php
require_once __DIR__ . '/footer.php';
include $GLOBALS['xoops']->path('footer.php');
