<?php declare(strict_types=1);
/*
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 */
//  ------------------------------------------------------------------------ //
//  Author: phppp (D.J., infomax@gmail.com)                                  //
//  URL: https://xoops.org                                                    //
//  Project: Article Project                                                 //
//  ------------------------------------------------------------------------ //

use Xmf\IPAddress;
use Xmf\Request;
use XoopsModules\Newbb\{
    Helper,
    ForumHandler,
    OnlineHandler,
    Post,
};

/** @var Helper $helper */
/** @var ForumHandler $forumHandler */
/** @var Post $post */
/** @var OnlineHandler $onlineHandler */
require_once __DIR__ . '/header.php';

$GPC = '_GET';
if (Request::getString('submit', '', 'POST')) {
    $GPC = '_POST';
}

foreach (['post_id', 'order', 'forum', 'topic_id'] as $getint) {
    ${$getint} = (int)(@${$GPC}[$getint]);
}
$viewmode = (isset(${$GPC}['viewmode']) && 'flat' !== ${$GPC}['viewmode']) ? 'thread' : 'flat';

if (empty($post_id)) {
    redirect_header(XOOPS_URL . '/index.php', 2, _MD_NEWBB_ERRORPOST);
}

if ($GLOBALS['xoopsModuleConfig']['wol_enabled']) {
    //    $onlineHandler = \XoopsModules\Newbb\Helper::getInstance()->getHandler('Online');
    $onlineHandler->init($forum);
}

$myts = \MyTextSanitizer::getInstance();
// Disable cache
$GLOBALS['xoopsConfig']['module_cache'][$xoopsModule->getVar('mid')] = 0;
require_once $GLOBALS['xoops']->path('header.php');
require_once $GLOBALS['xoops']->path('class/xoopsformloader.php');

if (Request::hasVar('submit', 'POST')) {
    $error_message = '';
    if (!is_object($GLOBALS['xoopsUser'])) {
        xoops_load('xoopscaptcha');
        $xoopsCaptcha = \XoopsCaptcha::getInstance();
        if (!$xoopsCaptcha->verify()) {
            $captcha_invalid = true;
            $error_message   = $xoopsCaptcha->getMessage();
        }
    }
    if ('' !== $error_message) {
        xoops_error($error_message);
    } else {
        //        $reportHandler = \XoopsModules\Newbb\Helper::getInstance()->getHandler('Report');
        $report = $reportHandler->create();
        $report->setVar('report_text', Request::getString('report_text', '', 'POST'));
        $report->setVar('post_id', Request::getInt('post_id', 0, 'POST'));
        $report->setVar('report_time', time());
        $report->setVar('reporter_uid', is_object($GLOBALS['xoopsUser']) ? $GLOBALS['xoopsUser']->getVar('uid') : 0);
        $report->setVar('reporter_ip', IPAddress::fromRequest()->asReadable());
        $report->setVar('report_result', 0);
        $report->setVar('report_memo', '');

        $reportId = $reportHandler->insert($report);
        if ($reportId) {
            //            $forumHandler = \XoopsModules\Newbb\Helper::getInstance()->getHandler('Forum');
            if (empty($forum)) {
            }
            $forumObject = $forumHandler->get($forum);

            if (is_object($forumObject)) {
                $mods   = $forumObject->getVar('forum_moderator');
                $emails = [];
                /** @var \XoopsMemberHandler $memberHandler */
                $memberHandler = xoops_getHandler('member');
                foreach ($mods as $mod) {
                    $thisUser = $memberHandler->getUser($mod);
                    if (is_object($thisUser)) {
                        $emails[] = $thisUser->getVar('email');
                        unset($thisUser);
                    }
                }
                $xoopsMailer = xoops_getMailer();
                $xoopsMailer->reset();
                $xoopsMailer->setTemplateDir();
                $xoopsMailer->useMail();
                $xoopsMailer->setTemplate('forum_report.tpl');
                $xoopsMailer->setToEmails($emails);
                $xoopsMailer->assign('MESSAGE', Request::getString('report_text', '', 'POST'));
                $xoopsMailer->setSubject(_MD_NEWBB_REPORTSUBJECT);
                $xoopsMailer->send();
            }
            $message = _MD_NEWBB_REPORTED;
        } else {
            $message = _MD_NEWBB_REPORT_ERROR;
        }
        redirect_header("viewtopic.php?forum=$forum&amp;topic_id=$topic_id&amp;post_id=$post_id&amp;order=$order&amp;viewmode=$viewmode", 2, $message);
    }
}

$report_form = new \XoopsThemeForm('', 'reportform', 'report.php');
$report_form->addElement(new \XoopsFormText(_MD_NEWBB_REPORT_TEXT, 'report_text', 80, 255, Request::getString('report_text', '', 'POST')), true);
if (!is_object($GLOBALS['xoopsUser'])) {
    $report_form->addElement(new \XoopsFormCaptcha());
}

//$postHandler = \XoopsModules\Newbb\Helper::getInstance()->getHandler('Post');
$postObject = $postHandler->get($post_id);
$forum      = $postObject->getVar('forum_id');

//$report_form->addElement(new \XoopsFormHidden('pid', $pid));
$report_form->addElement(new \XoopsFormHidden('post_id', $post_id));
$report_form->addElement(new \XoopsFormHidden('topic_id', $topic_id));
$report_form->addElement(new \XoopsFormHidden('forum', $forum));
$report_form->addElement(new \XoopsFormHidden('viewmode', $viewmode));
$report_form->addElement(new \XoopsFormHidden('order', $order));

$buttonTray    = new \XoopsFormElementTray('');
$submit_button = new \XoopsFormButton('', 'submit', _SUBMIT, 'submit');
$cancel_button = new \XoopsFormButton('', 'cancel', _MD_NEWBB_CANCELPOST, 'button');
$extra         = "viewtopic.php?forum=$forum&amp;topic_id=$topic_id&amp;post_id=$post_id&amp;order=$order&amp;viewmode=$viewmode";
$cancel_button->setExtra("onclick='location=\"" . $extra . "\"'");
$buttonTray->addElement($submit_button);
$buttonTray->addElement($cancel_button);
$report_form->addElement($buttonTray);
$report_form->display();

$r_subject = $postObject->getVar('subject', 'E');
if ($GLOBALS['xoopsModuleConfig']['enable_karma'] && $postObject->getVar('post_karma') > 0) {
    $r_message = sprintf(_MD_NEWBB_KARMA_REQUIREMENT, '***', $postObject->getVar('post_karma')) . '</div>';
} elseif ($GLOBALS['xoopsModuleConfig']['allow_require_reply'] && $postObject->getVar('require_reply')) {
    $r_message = _MD_NEWBB_REPLY_REQUIREMENT;
} else {
    $r_message = $postObject->getVar('post_text');
}

$r_date = formatTimestamp($postObject->getVar('post_time'));
if ($postObject->getVar('uid')) {
    $r_name = newbbGetUnameFromId($postObject->getVar('uid'), $GLOBALS['xoopsModuleConfig']['show_realname']);
} else {
    $poster_name = $postObject->getVar('poster_name');
    $r_name      = empty($poster_name) ? $GLOBALS['xoopsConfig']['anonymous'] : htmlspecialchars($poster_name, ENT_QUOTES | ENT_HTML5);
}
$r_content = _MD_NEWBB_SUBJECTC . ' ' . $r_subject . '<br>';
$r_content .= _MD_NEWBB_BY . ' ' . $r_name . ' ' . _MD_NEWBB_ON . ' ' . $r_date . '<br><br>';
$r_content .= $r_message;

echo "<br><table cellpadding='4' cellspacing='1' width='98%' class='outer'><tr><td class='head'>" . $r_subject . '</td></tr>';
echo '<tr><td><br>' . $r_content . '<br></td></tr></table>';

require_once $GLOBALS['xoops']->path('footer.php');
