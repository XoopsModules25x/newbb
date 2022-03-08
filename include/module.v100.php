<?php declare(strict_types=1);
//
//  ------------------------------------------------------------------------ //
//                XOOPS - PHP Content Management System                      //
//                  Copyright (c) 2000-2020 XOOPS.org                        //
//                       <https://xoops.org>                             //
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

/**
 * @param $module
 * @return bool
 */
function xoops_module_update_newbb_v100(XoopsObject $module)
{
    $result = $GLOBALS['xoopsDB']->queryF(
        'CREATE TABLE ' . $GLOBALS['xoopsDB']->prefix('bb_archive') . "(
        `topic_id` tinyint(8) NOT NULL default '0',
        `post_id` tinyint(8) NOT NULL default '0',
        `post_text` text NOT NULL
        ) ENGINE=MyISAM"
    );
    if (!$result) {
        $module->setErrors('Could not create bb_archive');
    }

    $result = $GLOBALS['xoopsDB']->queryF(
        'CREATE TABLE ' . $GLOBALS['xoopsDB']->prefix('bb_attachments') . "(
        `attach_id` int(8) unsigned NOT NULL auto_increment,
        `post_id` int(10) default NULL,
        `name_saved` varchar(255) default NULL,
        `name_disp` varchar(255) default NULL,
        `mimetype` varchar(255) default NULL,
        `online` int(1) NOT NULL default '1',
        `attach_time` int(10) NOT NULL default '0',
        `download` int(10) NOT NULL default '0',
        PRIMARY KEY  (`attach_id`),
        KEY `post_id` (`post_id`)
        ) ENGINE=MyISAM"
    );
    if (!$result) {
        $module->setErrors('Could not create bb_attachments');
    }

    $result = $GLOBALS['xoopsDB']->queryF(
        'CREATE TABLE ' . $GLOBALS['xoopsDB']->prefix('bb_digest') . "(
        `digest_id` int(8) unsigned NOT NULL auto_increment,
        `digest_time` int(10) NOT NULL default '0',
        `digest_content` text,
        PRIMARY KEY  (`digest_id`),
        KEY `digest_time` (`digest_time`)
        ) ENGINE=MyISAM"
    );
    if (!$result) {
        $module->setErrors('Could not create bb_digest');
    }

    $result = $GLOBALS['xoopsDB']->queryF(
        'CREATE TABLE ' . $GLOBALS['xoopsDB']->prefix('bb_online') . "(
        `online_forum` int(10) NOT NULL default '0',
        `online_topic` int(10) NOT NULL default '0',
        `online_uid` int(10) default NULL,
        `online_uname` varchar(255) default NULL,
        `online_ip` varchar(32) default NULL,
        `online_updated` int(14) default NULL
        ) ENGINE=MyISAM"
    );
    if (!$result) {
        $module->setErrors('Could not create bb_online');
    }

    $result = $GLOBALS['xoopsDB']->queryF(
        'CREATE TABLE ' . $GLOBALS['xoopsDB']->prefix('bb_report') . "(
        `report_id` int(8) unsigned NOT NULL auto_increment,
        `post_id` int(10) default NULL,
        `reporter_uid` int(10) default NULL,
        `reporter_ip` int(11) NOT NULL default '0',
        `report_time` int(10) NOT NULL default '0',
        `report_text` varchar(255) default NULL,
        `report_result` tinyint(1) NOT NULL default '0',
        `report_memo` varchar(255) default NULL,
        PRIMARY KEY  (`report_id`),
        KEY `post_id` (`post_id`)
        ) ENGINE=MyISAM"
    );
    if (!$result) {
        $module->setErrors('Could not create bb_report');
    }

    $result = $GLOBALS['xoopsDB']->queryF(
        'CREATE TABLE ' . $GLOBALS['xoopsDB']->prefix('bb_votedata') . "(
        `ratingid` int(11) unsigned NOT NULL auto_increment,
        `topic_id` int(11) unsigned NOT NULL default '0',
        `ratinguser` int(11) NOT NULL default '0',
        `rating` tinyint(3) unsigned NOT NULL default '0',
        `ratinghostname` varchar(60) NOT NULL default '',
        `ratingtimestamp` int(10) NOT NULL default '0',
        PRIMARY KEY  (ratingid),
        KEY ratinguser (ratinguser),
        KEY ratinghostname (ratinghostname),
        KEY topic_id (topic_id)
        ) ENGINE=MyISAM"
    );
    if (!$result) {
        $module->setErrors('Could not create bb_votedata');
    }

    $result = $GLOBALS['xoopsDB']->queryF('ALTER TABLE ' . $GLOBALS['xoopsDB']->prefix('bb_categories') . ' ADD `cat_image` VARCHAR(50) DEFAULT NULL AFTER `cat_id`');
    if (!$result) {
        $module->setErrors('Could not add field in bb_categories');
    }

    $result = $GLOBALS['xoopsDB']->queryF('ALTER TABLE ' . $GLOBALS['xoopsDB']->prefix('bb_categories') . ' ADD `cat_description` TEXT NOT NULL AFTER `cat_title`');
    if (!$result) {
        $module->setErrors('Could not add field in bb_categories');
    }

    $result = $GLOBALS['xoopsDB']->queryF('ALTER TABLE ' . $GLOBALS['xoopsDB']->prefix('bb_categories') . " CHANGE `cat_order` `cat_order` SMALLINT(3) UNSIGNED NOT NULL DEFAULT '0'");
    if (!$result) {
        $module->setErrors('Could not change field in bb_categories');
    }

    $result = $GLOBALS['xoopsDB']->queryF('ALTER TABLE ' . $GLOBALS['xoopsDB']->prefix('bb_categories') . ' ADD `cat_url` VARCHAR(50) DEFAULT NULL AFTER `cat_state`');
    if (!$result) {
        $module->setErrors('Could not add field in bb_categories');
    }

    $result = $GLOBALS['xoopsDB']->queryF('ALTER TABLE ' . $GLOBALS['xoopsDB']->prefix('bb_forums') . ' DROP `forum_access`');
    if (!$result) {
        $module->setErrors('Could not drop field in bb_forums');
    }

    $result = $GLOBALS['xoopsDB']->queryF('ALTER TABLE ' . $GLOBALS['xoopsDB']->prefix('bb_forums') . ' DROP `posts_per_page`');
    if (!$result) {
        $module->setErrors('Could not drop field in bb_forums');
    }

    $result = $GLOBALS['xoopsDB']->queryF('ALTER TABLE ' . $GLOBALS['xoopsDB']->prefix('bb_forums') . ' DROP `topics_per_page`');
    if (!$result) {
        $module->setErrors('Could not drop field in bb_forums');
    }

    $result = $GLOBALS['xoopsDB']->queryF('ALTER TABLE ' . $GLOBALS['xoopsDB']->prefix('bb_forums') . " ADD `parent_forum` INT(10) NOT NULL DEFAULT '0' AFTER `forum_desc`");
    if (!$result) {
        $module->setErrors('Could not add field in bb_forums');
    }

    $result = $GLOBALS['xoopsDB']->queryF('ALTER TABLE ' . $GLOBALS['xoopsDB']->prefix('bb_forums') . ' CHANGE `forum_moderator` `forum_moderator` TEXT NOT NULL');
    if (!$result) {
        $module->setErrors('Could not change field in bb_forums');
    }

    $result = $GLOBALS['xoopsDB']->queryF('ALTER TABLE ' . $GLOBALS['xoopsDB']->prefix('bb_forums') . " CHANGE `forum_type` `forum_type` INT(1) NOT NULL DEFAULT '0'");
    if (!$result) {
        $module->setErrors('Could not change field in bb_forums');
    }

    $result = $GLOBALS['xoopsDB']->queryF('ALTER TABLE ' . $GLOBALS['xoopsDB']->prefix('bb_forums') . " CHANGE `allow_html` `allow_html` INT(1) NOT NULL DEFAULT '1'");
    if (!$result) {
        $module->setErrors('Could not change field in bb_forums');
    }

    $result = $GLOBALS['xoopsDB']->queryF('ALTER TABLE ' . $GLOBALS['xoopsDB']->prefix('bb_forums') . " CHANGE `allow_sig` `allow_sig` INT(1) NOT NULL DEFAULT '1'");
    if (!$result) {
        $module->setErrors('Could not change field in bb_forums');
    }

    $result = $GLOBALS['xoopsDB']->queryF('ALTER TABLE ' . $GLOBALS['xoopsDB']->prefix('bb_forums') . " ADD `allow_subject_prefix` INT(1) NOT NULL DEFAULT '0' AFTER `allow_sig`");
    if (!$result) {
        $module->setErrors('Could not add field in bb_forums');
    }

    $result = $GLOBALS['xoopsDB']->queryF('ALTER TABLE ' . $GLOBALS['xoopsDB']->prefix('bb_forums') . " ADD `forum_order` INT(8) NOT NULL DEFAULT '0' AFTER `hot_threshold`");
    if (!$result) {
        $module->setErrors('Could not add field in bb_forums');
    }

    $result = $GLOBALS['xoopsDB']->queryF('ALTER TABLE ' . $GLOBALS['xoopsDB']->prefix('bb_forums') . " ADD `allow_attachments` INT(1) NOT NULL DEFAULT '1' AFTER `forum_order`");
    if (!$result) {
        $module->setErrors('Could not add field in bb_forums');
    }

    $result = $GLOBALS['xoopsDB']->queryF('ALTER TABLE ' . $GLOBALS['xoopsDB']->prefix('bb_forums') . " ADD `attach_maxkb` INT(10) NOT NULL DEFAULT '1000' AFTER `allow_attachments`");
    if (!$result) {
        $module->setErrors('Could not add field in bb_forums');
    }

    $result = $GLOBALS['xoopsDB']->queryF('ALTER TABLE ' . $GLOBALS['xoopsDB']->prefix('bb_forums') . ' ADD `attach_ext` TEXT NOT NULL AFTER `attach_maxkb`');
    if (!$result) {
        $module->setErrors('Could not add field in bb_forums');
    }

    $result = $GLOBALS['xoopsDB']->queryF('ALTER TABLE ' . $GLOBALS['xoopsDB']->prefix('bb_forums') . " ADD `allow_polls` INT(1) NOT NULL DEFAULT '0' AFTER `attach_ext`");
    if (!$result) {
        $module->setErrors('Could not add field in bb_forums');
    }

    $result = $GLOBALS['xoopsDB']->queryF('ALTER TABLE ' . $GLOBALS['xoopsDB']->prefix('bb_posts') . ' ADD `poster_name` varchar(255) DEFAULT NULL AFTER `uid`');
    if (!$result) {
        $module->setErrors('Could not add field in bb_posts');
    }

    $result = $GLOBALS['xoopsDB']->queryF('ALTER TABLE ' . $GLOBALS['xoopsDB']->prefix('bb_posts') . " CHANGE `poster_ip` `poster_ip` INT(11) NOT NULL DEFAULT '0'");
    if (!$result) {
        $module->setErrors('Could not change field in bb_posts');
    }
    $result = $GLOBALS['xoopsDB']->queryF('ALTER TABLE ' . $GLOBALS['xoopsDB']->prefix('bb_posts') . ' DROP `nosmiley`');
    if (!$result) {
        $module->setErrors('Could not drop nosmiley in bb_posts');
    }

    $result = $GLOBALS['xoopsDB']->queryF('ALTER TABLE ' . $GLOBALS['xoopsDB']->prefix('bb_posts') . " CHANGE `nohtml` `dohtml` TINYINT(1) NOT NULL DEFAULT '0'");
    if (!$result) {
        $module->setErrors('Could not change field in bb_posts');
    }

    $result = $GLOBALS['xoopsDB']->queryF('ALTER TABLE ' . $GLOBALS['xoopsDB']->prefix('bb_posts') . " ADD `dosmiley` TINYINT(1) NOT NULL DEFAULT '1' AFTER `dohtml`");
    if (!$result) {
        $module->setErrors('Could not change field in bb_posts');
    }

    $result = $GLOBALS['xoopsDB']->queryF('ALTER TABLE ' . $GLOBALS['xoopsDB']->prefix('bb_posts') . " ADD `doxcode` TINYINT(1) NOT NULL DEFAULT '1' AFTER `dosmiley`");
    if (!$result) {
        $module->setErrors('Could not add field in bb_posts');
    }

    $result = $GLOBALS['xoopsDB']->queryF('ALTER TABLE ' . $GLOBALS['xoopsDB']->prefix('bb_posts') . " ADD `dobr` TINYINT(1) NOT NULL DEFAULT '1' AFTER `doxcode`");
    if (!$result) {
        $module->setErrors('OK exist just to be sure bb_posts');
    }

    $result = $GLOBALS['xoopsDB']->queryF('ALTER TABLE ' . $GLOBALS['xoopsDB']->prefix('bb_posts') . " ADD `doimage` TINYINT(1) NOT NULL DEFAULT '1' AFTER `dobr`");
    if (!$result) {
        $module->setErrors('OK exist just to be sure bb_posts');
    }

    $result = $GLOBALS['xoopsDB']->queryF('ALTER TABLE ' . $GLOBALS['xoopsDB']->prefix('bb_posts') . " ADD `approved` int(1) NOT NULL default '1' AFTER `attachsig`");
    if (!$result) {
        $module->setErrors('Could not add field in bb_posts');
    }

    $result = $GLOBALS['xoopsDB']->queryF('ALTER TABLE ' . $GLOBALS['xoopsDB']->prefix('bb_posts') . " ADD  `post_karma` int(10) NOT NULL default '0' AFTER `approved`");
    if (!$result) {
        $module->setErrors('Could not add field in bb_posts');
    }

    $result = $GLOBALS['xoopsDB']->queryF('ALTER TABLE ' . $GLOBALS['xoopsDB']->prefix('bb_posts') . ' ADD `attachment` text AFTER `post_karma`');
    if (!$result) {
        $module->setErrors('Could not add field in bb_posts');
    }

    $result = $GLOBALS['xoopsDB']->queryF('ALTER TABLE ' . $GLOBALS['xoopsDB']->prefix('bb_posts') . " ADD `require_reply` int(1) NOT NULL default '0' AFTER `attachment`");
    if (!$result) {
        $module->setErrors('Could not add field in bb_posts');
    }

    $result = $GLOBALS['xoopsDB']->queryF('ALTER TABLE ' . $GLOBALS['xoopsDB']->prefix('bb_posts_text') . ' ADD `post_edit` TEXT NOT NULL AFTER `post_text`');
    if (!$result) {
        $module->setErrors('Could not add field in bb_posts_text');
    }

    $result = $GLOBALS['xoopsDB']->queryF('ALTER TABLE ' . $GLOBALS['xoopsDB']->prefix('bb_topics') . " ADD `topic_subject` INT(3) NOT NULL default '0' AFTER `topic_status`");
    if (!$result) {
        $module->setErrors('Could not add field in bb_topics');
    }

    $result = $GLOBALS['xoopsDB']->queryF('ALTER TABLE ' . $GLOBALS['xoopsDB']->prefix('bb_topics') . " ADD `topic_digest` TINYINT(1) NOT NULL default '0' AFTER `topic_sticky`");
    if (!$result) {
        $module->setErrors('Could not add field in bb_topics');
    }

    $result = $GLOBALS['xoopsDB']->queryF('ALTER TABLE ' . $GLOBALS['xoopsDB']->prefix('bb_topics') . " ADD  `digest_time` int(10) NOT NULL default '0' AFTER `topic_digest`");
    if (!$result) {
        $module->setErrors('Could not add field in bb_topics');
    }

    $result = $GLOBALS['xoopsDB']->queryF('ALTER TABLE ' . $GLOBALS['xoopsDB']->prefix('bb_topics') . " ADD `approved` int(1) NOT NULL default '1' AFTER  `digest_time`");
    if (!$result) {
        $module->setErrors('Could not add field in bb_topics');
    }

    $result = $GLOBALS['xoopsDB']->queryF('ALTER TABLE ' . $GLOBALS['xoopsDB']->prefix('bb_topics') . ' ADD `poster_name` varchar(255) DEFAULT NULL AFTER `approved`');
    if (!$result) {
        $module->setErrors('Could not add field in bb_topics');
    }

    $result = $GLOBALS['xoopsDB']->queryF('ALTER TABLE ' . $GLOBALS['xoopsDB']->prefix('bb_topics') . " ADD `rating` double(6,4) NOT NULL default '0.0000' AFTER `poster_name`");
    if (!$result) {
        $module->setErrors('Could not add field in bb_topics');
    }

    $result = $GLOBALS['xoopsDB']->queryF('ALTER TABLE ' . $GLOBALS['xoopsDB']->prefix('bb_topics') . " ADD  `votes` int(11) unsigned NOT NULL default '0' AFTER `rating`");
    if (!$result) {
        $module->setErrors('Could not add field in bb_topics');
    }

    $result = $GLOBALS['xoopsDB']->queryF('ALTER TABLE ' . $GLOBALS['xoopsDB']->prefix('bb_topics') . " ADD `topic_haspoll` tinyint(1) NOT NULL default '0' AFTER `votes`");
    if (!$result) {
        $module->setErrors('Could not add field in bb_topics');
    }

    $result = $GLOBALS['xoopsDB']->queryF('ALTER TABLE ' . $GLOBALS['xoopsDB']->prefix('bb_topics') . " ADD  `poll_id` mediumint(8) unsigned NOT NULL default '0' AFTER `topic_haspoll`");
    if (!$result) {
        $module->setErrors('Could not add field in bb_topics');
    }

    $result = $GLOBALS['xoopsDB']->queryF('DROP TABLE ' . $GLOBALS['xoopsDB']->prefix('bb_forum_access'));
    if (!$result) {
        $module->setErrors('Could not drop bb_forum_access');
    }

    $result = $GLOBALS['xoopsDB']->queryF('DROP TABLE ' . $GLOBALS['xoopsDB']->prefix('bb_forum_mods'));
    if (!$result) {
        $module->setErrors('Could not drop bb_forum_mods');
    }

    return true;
}
