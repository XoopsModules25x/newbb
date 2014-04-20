<?php
// $Id: report.php 62 2012-08-17 10:15:26Z alfred $
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
include_once dirname(__FILE__) . "/header.php";

if ( isset($_POST['submit']) ) {
	$GPC = "_POST";
} else {
	$GPC = "_GET";
}

foreach (array('post_id', 'order','forum','topic_id') as $getint) {
    ${$getint} = intval( @${$GPC}[$getint] );
}
$viewmode = (isset(${$GPC}['viewmode']) && ${$GPC}['viewmode'] != 'flat') ? 'thread' : 'flat';

if ( empty($post_id) ) {
    redirect_header("index.php", 2, _MD_ERRORPOST);
    exit();
}

if ($xoopsModuleConfig['wol_enabled']) {
	$online_handler =& xoops_getmodulehandler('online', 'newbb');
	$online_handler->init($forum);
}

$myts = MyTextSanitizer::getInstance();
// Disable cache
$xoopsConfig["module_cache"][$xoopsModule->getVar("mid")] = 0;
include XOOPS_ROOT_PATH.'/header.php';
include XOOPS_ROOT_PATH."/class/xoopsformloader.php";

if ( !empty($_POST['submit']) ) {
    $error_message = '';
    if (!is_object($xoopsUser)) {
        xoops_load("xoopscaptcha");
        $xoopsCaptcha = XoopsCaptcha::getInstance();
        if (! $xoopsCaptcha->verify() ) {
            $captcha_invalid = true;
            $error_message = $xoopsCaptcha->getMessage();
        }
    }
    if ($error_message!='') {
       xoops_error($error_message);
    
    } else {
        $report_handler =& xoops_getmodulehandler('report', 'newbb');
        $report =& $report_handler->create();
        $report->setVar('report_text', $_POST['report_text']);
        $report->setVar('post_id', $post_id);
        $report->setVar('report_time', time());
        $report->setVar('reporter_uid', is_object($xoopsUser) ? $xoopsUser->getVar('uid') : 0);
        $report->setVar('reporter_ip', newbb_getIP());
        $report->setVar('report_result', 0);
        $report->setVar('report_memo', "");

        if ($report_id = $report_handler->insert($report)) {
            $forum_handler =& xoops_getmodulehandler('forum', 'newbb');
            if (empty($forum)) {
            
            }
            $forum_obj =& $forum_handler->get($forum);
            
            if (is_object($forum_obj)) {
                $mods = $forum_obj->getVar('forum_moderator');
                $emails=array();
                $member_handler =& xoops_gethandler('member');
                foreach ($mods as $mod) {
                     $thisUser =& $member_handler->getUser($mod);
                     if (is_object($thisUser)) {
                        $emails[]=$thisUser->getVar('email');
                        unset($thisUser);
                     }
                }
                $xoopsMailer =& xoops_getMailer();
                $xoopsMailer->reset();
                $xoopsMailer->setTemplateDir();
                $xoopsMailer->useMail();
                $xoopsMailer->setTemplate('forum_report.tpl');
                $xoopsMailer->setToEmails($emails);
                $xoopsMailer->assign('MESSAGE', $_POST['report_text']);
                $xoopsMailer->setSubject(_MD_NEWBB_REPORTSUBJECT);
                $xoopsMailer->send();
            }
            $message = _MD_REPORTED;
        } else {
            $message = _MD_REPORT_ERROR;
        }
        redirect_header("viewtopic.php?forum=$forum&amp;topic_id=$topic_id&amp;post_id=$post_id&amp;order=$order&amp;viewmode=$viewmode",2,$message);
        exit();
    }   
} 

$report_form = new XoopsThemeForm('', 'reportform', 'report.php');
$report_form->addElement(new XoopsFormText(_MD_REPORT_TEXT, 'report_text', 80, 255,@$_POST['report_text']), true); 
if (!is_object($xoopsUser)) {
    $report_form->addElement( new XoopsFormCaptcha() );
}

$post_handler =& xoops_getmodulehandler('post', 'newbb');
$post_obj =& $post_handler->get($post_id);
$forum = $post_obj->getVar('forum_id');

//$report_form->addElement(new XoopsFormHidden('pid', $pid));
$report_form->addElement(new XoopsFormHidden('post_id', $post_id));
$report_form->addElement(new XoopsFormHidden('topic_id', $topic_id));
$report_form->addElement(new XoopsFormHidden('forum', $forum));
$report_form->addElement(new XoopsFormHidden('viewmode', $viewmode));
$report_form->addElement(new XoopsFormHidden('order', $order));

$button_tray = new XoopsFormElementTray('');
$submit_button = new XoopsFormButton('', 'submit', _SUBMIT, "submit");
$cancel_button = new XoopsFormButton('', 'cancel', _MD_CANCELPOST, 'button');
$extra = "viewtopic.php?forum=$forum&amp;topic_id=$topic_id&amp;post_id=$post_id&amp;order=$order&amp;viewmode=$viewmode";
$cancel_button->setExtra("onclick='location=\"".$extra."\"'");
$button_tray->addElement($submit_button);
$button_tray->addElement($cancel_button);
$report_form->addElement($button_tray);
$report_form->display();


$r_subject=$post_obj->getVar('subject', "E");
if ( $xoopsModuleConfig['enable_karma'] && $post_obj->getVar('post_karma') > 0 ) {
    $r_message = sprintf(_MD_KARMA_REQUIREMENT, "***", $post_obj->getVar('post_karma'))."</div>";
} elseif ( $xoopsModuleConfig['allow_require_reply'] && $post_obj->getVar('require_reply') ) {
    $r_message = _MD_REPLY_REQUIREMENT;
} else {
    $r_message = $post_obj->getVar('post_text');
}

$r_date = formatTimestamp($post_obj->getVar('post_time'));
if ($post_obj->getVar('uid')) {
    $r_name =newbb_getUnameFromId( $post_obj->getVar('uid'), $xoopsModuleConfig['show_realname']);
} else {
    $poster_name = $post_obj->getVar('poster_name');
   	$r_name = (empty($poster_name))?$xoopsConfig['anonymous']:$myts->htmlSpecialChars($poster_name);
}
$r_content = _MD_SUBJECTC." ".$r_subject."<br />";
$r_content .= _MD_BY." ".$r_name." "._MD_ON." ".$r_date."<br /><br />";
$r_content .= $r_message;

echo "<br /><table cellpadding='4' cellspacing='1' width='98%' class='outer'><tr><td class='head'>".$r_subject."</td></tr>";
echo "<tr><td><br />".$r_content."<br /></td></tr></table>";

include XOOPS_ROOT_PATH.'/footer.php';
?>