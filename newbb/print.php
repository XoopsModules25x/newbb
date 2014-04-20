<?php
// $Id: print.php,v 1.3 2005/10/19 17:20:28 phppp Exp $
//  ------------------------------------------------------------------------ //
//                XOOPS - PHP Content Management System                      //
//                    Copyright (c) 2000 XOOPS.org                           //
//                       <http://www.xoops.org/>                             //
// ------------------------------------------------------------------------- //
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

/* 
 * Print contents of a post or a topic
 * currently only available for post print
 *
 * TODO: topic print; print with page splitting
 * 
 */
 
include_once dirname(__FILE__) . "/header.php";

error_reporting(0);
$xoopsLogger->activated = false;

if (empty($_POST["post_data"])) {
	
$forum = intval(@$_GET['forum']);
$topic_id = intval(@$_GET['topic_id']);
$post_id = intval(@$_GET['post_id']);

if ( empty($post_id) && empty($topic_id) ) {
	die(_MD_ERRORTOPIC);
}

if (!empty($post_id)) {
	$post_handler =& xoops_getmodulehandler('post', 'newbb');
	$post = & $post_handler->get($post_id);
	if (!$approved = $post->getVar('approved')) {
		die(_MD_NORIGHTTOVIEW);
	}
	$topic_id = $post->getVar("topic_id");
	$post_data = $post_handler->getPostForPrint($post);
	$isPost = 1;
	$post_data["url"] = XOOPS_URL."/modules/newbb/viewtopic.php?post_id=".$post_id;
	if(!empty($xoopsModuleConfig['do_rewrite']) ) {
		$post_data["url"] = seo_urls('<a href="'.$post_data["url"].'"></a>');
		$post_data["url"] = str_replace('<a href="','',$post_data["url"]);
		$post_data["url"] = str_replace('"></a>','',$post_data["url"]);
	}
}

$topic_handler =& xoops_getmodulehandler('topic', 'newbb');
$topic_obj =& $topic_handler->get($topic_id);
$topic_id = $topic_obj->getVar('topic_id');
$forum = $topic_obj->getVar('forum_id');
if (!$approved = $topic_obj->getVar('approved'))    {
	die(_MD_NORIGHTTOVIEW);
}

$isadmin = newbb_isAdmin($forum_obj);
if (!$isadmin && $topic_obj->getVar('approved')<0 ) {
    die(_MD_NORIGHTTOVIEW);
}

$forum_handler =& xoops_getmodulehandler('forum', 'newbb');
$forum = $topic_obj->getVar('forum_id');
$forum_obj =& $forum_handler->get($forum);
if (!$forum_handler->getPermission($forum_obj)) {
    die(_MD_NORIGHTTOVIEW);
}

if (!$topic_handler->getPermission($forum_obj, $topic_obj->getVar('topic_status'), "view")) {
	die(_MD_NORIGHTTOVIEW);
}
// irmtfan add print permission
if (!$topic_handler->getPermission($forum_obj, $topic_obj->getVar('topic_status'), "print")) {
	die(_MD_NORIGHTTOPRINT);
}

} else {
	$post_data = unserialize(base64_decode($_POST["post_data"]));
	$isPost = 1;
}

xoops_header(false);

if (empty($isPost)) {

	echo "</head><body style='background-color:#ffffff; color:#000000;' onload='window.print()'>
			<div style='width: 750px; border: 1px solid #000; padding: 20px;'>
			<div style='text-align: center; display: block; margin: 0 0 6px 0;'>
			<img src='" . XOOPS_URL . "/modules/newbb/images/xoopsbb_slogo.png' border='0' alt='' />
			<br /><br /> ";

    $postsArray = $topic_handler->getAllPosts($topic_obj);
    foreach ($postsArray as $post) {
		if (!$post->getVar('approved'))    continue;
		$post_data = $post_handler->getPostForPrint($post);
		echo "<h2 style='margin: 0;'>".$post_data['subject']."</h2>
 	          <div align='center'>" ._POSTEDBY. "&nbsp;".$post_data['author']."&nbsp;"._ON."&nbsp;".formatTimestamp($post_data['date'])."</div>
		      <div style='text-align: center; display: block; padding-bottom: 12px; margin: 0 0 6px 0; border-bottom: 2px solid #ccc;'></div>
		       <div>".$post_data['text']."</div>
		      <div style='padding-top: 12px; border-top: 2px solid #ccc;'></div><br />";
    }
	echo "<p>"._MD_COMEFROM . "&nbsp;".XOOPS_URL."/newbb/viewtopic.php?forum=".$forum_id."&amp;topic_id=".$topic_id."</p>";
	echo "</div></div>";
	echo "</body></html>";
	
} else {

	echo "</head><body style='background-color:#ffffff; color:#000000;' onload='window.print()'>
			<div style='width: 750px; border: 1px solid #000; padding: 20px;'>
			<div style='text-align: center; display: block; margin: 0 0 6px 0;'>
			<h2 style='margin: 0;'>".$post_data['subject']."</h2></div>
			<div align='center'>" ._POSTEDBY. "&nbsp;".$post_data['author']."&nbsp;"._ON."&nbsp;".formatTimestamp($post_data['date'])."</div>
			<div style='text-align: center; display: block; padding-bottom: 12px; margin: 0 0 6px 0; border-bottom: 2px solid #ccc;'></div>
			<div>".$post_data['text']."</div>
			<div style='padding-top: 12px; border-top: 2px solid #ccc;'></div>
			<p>"._MD_COMEFROM . "&nbsp;".$post_data["url"]."</p>
		    </div>
            <br /><br /></body></html>";
}
?>