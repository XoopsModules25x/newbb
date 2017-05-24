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
//  Author: phppp (D.J., infomax@gmail.com)                                  //
//  URL: http://xoops.org                                                    //
//  Project: Article Project                                                 //
//  ------------------------------------------------------------------------ //

// a complete rewrite by irmtfan to enhance: 1- RTL 2- Multilanguage (EMLH and Xlanguage)
error_reporting(0);

include_once __DIR__ . '/header.php';

$attach_id = XoopsRequest::getString('attachid', '', 'GET');

$forum    = XoopsRequest::getInt('forum', 0, 'GET');
$topic_id = XoopsRequest::getInt('topic_id', 0, 'GET');
$post_id  = XoopsRequest::getInt('post_id', 0, 'GET');

if (!is_file(XOOPS_PATH . '/vendor/tcpdf/tcpdf.php')) {
    redirect_header(XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname') . '/viewtopic.php?topic_id=' . $topic_id, 3, 'TCPF for Xoops not installed');
}

if (empty($post_id)) {
    exit(_MD_ERRORTOPIC);
}

$postHandler = xoops_getModuleHandler('post', 'newbb');
$post        = $postHandler->get($post_id);
if (!$approved = $post->getVar('approved')) {
    exit(_MD_NORIGHTTOVIEW);
}

$post_data = $postHandler->getPostForPDF($post);
//$post_edit = $post->displayPostEdit();  //reserve for future versions to display edit records
$topicHandler = xoops_getModuleHandler('topic', 'newbb');
$forumtopic   = $topicHandler->getByPost($post_id);
$topic_id     = $forumtopic->getVar('topic_id');
if (!$approved = $forumtopic->getVar('approved')) {
    exit(_MD_NORIGHTTOVIEW);
}

$forumHandler    = xoops_getModuleHandler('forum', 'newbb');
$forum           = $forum ?: $forumtopic->getVar('forum_id');
$viewtopic_forum = $forumHandler->get($forum);
$parent_forums   = array();
$parent_forums   = $forumHandler->getParents($viewtopic_forum);
$pf_title        = '';
if ($parent_forums) {
    foreach ($parent_forums as $p_f) {
        $pf_title .= $p_f['forum_name'] . ' - ';
    }
}

if (!$forumHandler->getPermission($viewtopic_forum)) {
    exit(_MD_NORIGHTTOACCESS);
}
if (!$topicHandler->getPermission($viewtopic_forum, $forumtopic->getVar('topic_status'), 'view')) {
    exit(_MD_NORIGHTTOVIEW);
}
// irmtfan add pdf permission
if (!$topicHandler->getPermission($viewtopic_forum, $forumtopic->getVar('topic_status'), 'pdf')) {
    exit(_MD_NORIGHTTOPDF);
}

$categoryHandler = xoops_getModuleHandler('category', 'newbb');
$cat             = $viewtopic_forum->getVar('cat_id');
$viewtopic_cat   = $categoryHandler->get($cat);

$GLOBALS['xoopsOption']['pdf_cache'] = 0;

$pdf_data['author'] = $myts->undoHtmlSpecialChars($post_data['author']);
$pdf_data['title']  = $myts->undoHtmlSpecialChars($post_data['subject']);
$content            = '';
$content .= '<b>' . $pdf_data['title'] . '</b><br><br>';
$content .= _MD_AUTHORC . ' ' . $pdf_data['author'] . '<br>';
$content .= _MD_NEWBB_POSTEDON . ' ' . formatTimestamp($post_data['date']) . '<br><br><br>';
$content .= $myts->undoHtmlSpecialChars($post_data['text']) . '<br>';
//$content .= $post_edit . '<br>'; //reserve for future versions to display edit records
$pdf_data['content']        = str_replace('[pagebreak]', '<br>', $content);
$pdf_data['topic_title']    = $forumtopic->getVar('topic_title');
$pdf_data['forum_title']    = $pf_title . $viewtopic_forum->getVar('forum_name');
$pdf_data['cat_title']      = $viewtopic_cat->getVar('cat_title');
$pdf_data['subject']        = NEWBB_PDF_SUBJECT . ': ' . $pdf_data['topic_title'];
$pdf_data['keywords']       = XOOPS_URL . ', ' . 'XOOPS Project, ' . $pdf_data['topic_title'];
$pdf_data['HeadFirstLine']  = $GLOBALS['xoopsConfig']['sitename'] . ' - ' . $GLOBALS['xoopsConfig']['slogan'];
$pdf_data['HeadSecondLine'] = _MD_FORUMHOME . ' - ' . $pdf_data['cat_title'] . ' - ' . $pdf_data['forum_title'] . ' - ' . $pdf_data['topic_title'];

// START irmtfan to implement EMLH by GIJ
if (function_exists('easiestml')) {
    $pdf_data = easiestml($pdf_data);
    // END irmtfan to implement EMLH by GIJ
    // START irmtfan to implement Xlanguage by phppp(DJ)
} elseif (function_exists('xlanguage_ml')) {
    $pdf_data = xlanguage_ml($pdf_data);
}
// END irmtfan to implement Xlanguage by phppp(DJ)

require_once XOOPS_PATH . '/vendor/tcpdf/tcpdf.php';
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, _CHARSET, false);
// load $localLanguageOptions array with language specific definitions and apply
if (is_file(XOOPS_PATH . '/vendor/tcpdf/config/lang/' . $GLOBALS['xoopsConfig']['language'] . '.php')) {
    require_once XOOPS_PATH . '/vendor/tcpdf/config/lang/' . $GLOBALS['xoopsConfig']['language'] . '.php';
} else {
    require_once XOOPS_PATH . '/vendor/tcpdf/config/lang/english.php';
}
$pdf->setLanguageArray($localLanguageOptions);

// START irmtfan hack to add RTL-LTR local
// until _RTL added to core 2.6.0
if (!defined('_RTL')) {
    define('_RTL', false);
}
$pdf->setRTL(_RTL);
// END irmtfan hack to add RTL-LTR local

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor(PDF_AUTHOR);
$pdf->SetTitle($pdf_data['forum_title'] . ' - ' . $pdf_data['subject']);
$pdf->SetSubject($pdf_data['subject']);
$pdf->SetKeywords($pdf_data['keywords']);

//$pdf->SetHeaderData('', '5', $pdf_data['HeadFirstLine'], $pdf_data['HeadSecondLine']);
$pdf->setHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, $pdf_data['HeadFirstLine'], $pdf_data['HeadSecondLine'], array(0, 64, 255), array(0, 64, 128));

//set margins
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->setHeaderMargin(PDF_MARGIN_HEADER);
$pdf->setFooterMargin(PDF_MARGIN_FOOTER);
//set auto page breaks
$pdf->SetAutoPageBreak(true, 25);

$pdf->setHeaderFont(array(PDF_FONT_NAME_SUB, '', PDF_FONT_SIZE_SUB));
$pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
$pdf->setFooterData($tc = array(0, 64, 0), $lc = array(0, 64, 128));

$pdf->Open();
$pdf->AddPage();
$pdf->SetFont(PDF_FONT_NAME_MAIN, PDF_FONT_STYLE_MAIN, PDF_FONT_SIZE_MAIN);
$pdf->writeHTML($pdf_data['content'], true, 0);
$pdf->Output($pdf_data['topic_title'] . '_' . $post_id . '.pdf', 'I');
