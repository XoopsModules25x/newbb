# phpMyAdmin SQL Dump
# version 2.5.6
# http://www.phpmyadmin.net
#
# Host: localhost
# Generation Time: Aug 17, 2004 at 10:28 AM
# Server version: 4.0.20
# PHP Version: 4.2.3
#
# Database : `xpsbeta_xoops`
#
ALTER TABLE `bb_archive` 
	CHANGE `topic_id` `topic_id` int(8) UNSIGNED NOT NULL default '0',
	CHANGE `post_id` `post_id` int(10) UNSIGNED NOT NULL default '0';

ALTER TABLE `bb_attachments` 
	CHANGE `post_id` `post_id` int(10) UNSIGNED NOT NULL default '0',
	CHANGE `online` `online` tinyint(1) UNSIGNED NOT NULL default '1',
	CHANGE `attach_time` `attach_time` int(10) UNSIGNED NOT NULL default '0',
	CHANGE `download` `download` int(10) UNSIGNED NOT NULL default '0';

ALTER TABLE `bb_categories`
	DROP `cat_state`,
	CHANGE `cat_url` `cat_url` varchar(255) NOT NULL default '',
	DROP `cat_showdescript`;
ALTER TABLE `bb_categories`
	ADD INDEX `cat_order` (`cat_order`);

ALTER TABLE `bb_digest`
	CHANGE `digest_time` `digest_time` int(10) UNSIGNED NOT NULL default '0';

ALTER TABLE `bb_forums`
	CHANGE `parent_forum` `parent_forum` smallint(4) UNSIGNED NOT NULL default '0',
	CHANGE `forum_moderator` `forum_moderator` varchar(255) NOT NULL default '',
	CHANGE `forum_topics` `forum_topics` int(8) UNSIGNED NOT NULL default '0',
	CHANGE `forum_posts` `forum_posts` int(10) UNSIGNED NOT NULL default '0',
	CHANGE `forum_last_post_id` `forum_last_post_id` int(10) unsigned NOT NULL default '0',
	CHANGE `cat_id` `cat_id` smallint(3) UNSIGNED NOT NULL default '0',
	CHANGE `forum_type` `forum_type` tinyint(1) UNSIGNED NOT NULL default '0',
	CHANGE `allow_html` `allow_html` tinyint(1) UNSIGNED NOT NULL default '1',
	CHANGE `allow_sig` `allow_sig` tinyint(1) UNSIGNED NOT NULL default '1',
	CHANGE `allow_subject_prefix` `allow_subject_prefix` tinyint(1) UNSIGNED NOT NULL default '0',
	CHANGE `forum_order` `forum_order` smallint(4) UNSIGNED NOT NULL default '0',
	CHANGE `attach_maxkb` `attach_maxkb` smallint(3) UNSIGNED NOT NULL default '1000',
	CHANGE `attach_ext` `attach_ext` varchar(255) NOT NULL default '',
	CHANGE `allow_polls` `allow_polls` tinyint(1) unsigned NOT NULL default '0',
	DROP `allow_attachments`,
	DROP `subforum_count`;
ALTER TABLE `bb_forums`
	ADD INDEX `forum_last_post_id` (`forum_last_post_id`),
	ADD INDEX `cat_forum` (`cat_id`, `forum_order`),
	ADD INDEX `forum_order` (`forum_order`),
	ADD INDEX `cat_id` (`cat_id`);

ALTER TABLE `bb_votedata`
	CHANGE `topic_id` `topic_id` int(8) unsigned NOT NULL default '0',
	CHANGE `ratinguser` `ratinguser` int(10) UNSIGNED NOT NULL default '0',
	CHANGE `ratingtimestamp` `ratingtimestamp` int(10) UNSIGNED NOT NULL default '0';

ALTER TABLE `bb_online`
	CHANGE `online_forum` `online_forum` int(10) UNSIGNED NOT NULL default '0',
	CHANGE `online_topic` `online_topic` int(8) UNSIGNED NOT NULL default '0',
	CHANGE `online_uid` `online_uid` int(10) UNSIGNED NOT NULL default '0',
	CHANGE `online_updated` `online_updated` int(10) UNSIGNED NOT NULL default '0';
ALTER TABLE `bb_online`
	ADD INDEX `online_forum` (`online_forum`),
	ADD INDEX `online_topic` (`online_topic`),
	ADD INDEX `online_updated` (`online_updated`);

ALTER TABLE `bb_report`
	CHANGE `post_id` `post_id` int(10) UNSIGNED NOT NULL default '0',
	CHANGE `reporter_uid` `reporter_uid` int(10) UNSIGNED NOT NULL default '0',
	CHANGE `report_time` `report_time` int(10) UNSIGNED NOT NULL default '0',
	CHANGE `report_result` `report_result` tinyint(1) UNSIGNED NOT NULL default '0';

ALTER TABLE `bb_posts`
	CHANGE `post_id` `post_id` int(10) unsigned NOT NULL auto_increment,
	CHANGE `pid` `pid` int(10) UNSIGNED NOT NULL default '0',
	CHANGE `topic_id` `topic_id` int(8) UNSIGNED NOT NULL default '0',
	CHANGE `forum_id` `forum_id` smallint(4) UNSIGNED NOT NULL default '0',
	CHANGE `post_time` `post_time` int(10) UNSIGNED NOT NULL default '0',
	CHANGE `dohtml` `dohtml` tinyint(1) UNSIGNED NOT NULL default '0',
	CHANGE `dosmiley` `dosmiley` tinyint(1) UNSIGNED NOT NULL default '1',
	CHANGE `doxcode` `doxcode` tinyint(1) UNSIGNED NOT NULL default '1',
	CHANGE `dobr` `dobr` tinyint(1) UNSIGNED NOT NULL default '1',
	CHANGE `doimage` `doimage` tinyint(1) UNSIGNED NOT NULL default '1',
	CHANGE `attachsig` `attachsig` tinyint(1) UNSIGNED NOT NULL default '0',
	CHANGE `approved` `approved` smallint(2) NOT NULL default '1',
	CHANGE `post_karma` `post_karma` int(10) UNSIGNED NOT NULL default '0',
	CHANGE `require_reply` `require_reply` tinyint(1) UNSIGNED NOT NULL default '0';
ALTER TABLE `bb_posts`
	ADD INDEX `forum_id` (`forum_id`),
	ADD INDEX `topic_id` (`topic_id`),
	ADD INDEX `post_time` (`post_time`);

ALTER TABLE `bb_posts_text`
	CHANGE `post_id` `post_id` int(10) unsigned NOT NULL default '0';

ALTER TABLE `bb_topics`
	CHANGE `topic_poster` `topic_poster` int(10) UNSIGNED NOT NULL default '0',
	CHANGE `topic_time` `topic_time` int(10) UNSIGNED NOT NULL default '0',
	CHANGE `topic_views` `topic_views` int(10) UNSIGNED NOT NULL default '0',
	CHANGE `topic_replies` `topic_replies` mediumint(8) UNSIGNED NOT NULL default '0',
	CHANGE `topic_last_post_id` `topic_last_post_id` int(8) UNSIGNED unsigned NOT NULL default '0',
	CHANGE `forum_id` `forum_id` smallint(4) UNSIGNED NOT NULL default '0',
	CHANGE `topic_status` `topic_status` tinyint(1) UNSIGNED NOT NULL default '0',
	CHANGE `topic_subject` `topic_subject` smallint(3) UNSIGNED NOT NULL default '0',
	CHANGE `topic_sticky` `topic_sticky` tinyint(1) UNSIGNED NOT NULL default '0',
	CHANGE `topic_digest` `topic_digest` tinyint(1) UNSIGNED NOT NULL default '0',
	CHANGE `digest_time` `digest_time` int(10) UNSIGNED NOT NULL default '0',
	CHANGE `approved` `approved` tinyint(2) NOT NULL default '1',
	CHANGE `topic_haspoll` `topic_haspoll` tinyint(1) UNSIGNED NOT NULL default '0';
ALTER TABLE `bb_topics`
	ADD INDEX `digest_time` (`digest_time`);

ALTER TABLE `bb_moderates`
	CHANGE `mod_start` `mod_start` int(10) UNSIGNED NOT NULL default '0',
	CHANGE `mod_end` `mod_end` int(10) UNSIGNED NOT NULL default '0',
	CHANGE `uid` `uid` int(10) UNSIGNED NOT NULL default '0',
	CHANGE `forum_id` `forum_id` smallint(4) UNSIGNED NOT NULL default '0';
ALTER TABLE `bb_moderates`
	ADD INDEX `mod_end` (`mod_end`),
	ADD INDEX `forum_id` (`forum_id`);

CREATE TABLE `bb_reads_topic` (
  `read_id` int(10) unsigned NOT NULL auto_increment,
  `uid` int(10) UNSIGNED NOT NULL default '0',
  `read_time` int(10) UNSIGNED NOT NULL default '0',
  `read_item` int(8) UNSIGNED NOT NULL default '0',
  `post_id` int(10) UNSIGNED NOT NULL default '0',
  PRIMARY KEY  (`read_id`),
  KEY `uid` (`uid`),
  KEY `read_item` (`read_item`),
  KEY `post_id` (`post_id`)
) ENGINE=MyISAM;

CREATE TABLE `bb_reads_forum` (
  `read_id` int(10) unsigned NOT NULL auto_increment,
  `uid` int(10) UNSIGNED NOT NULL default '0',
  `read_time` int(10) UNSIGNED NOT NULL default '0',
  `read_item` smallint(4) UNSIGNED NOT NULL default '0',
  `post_id` int(10) UNSIGNED NOT NULL default '0',
  PRIMARY KEY  (`read_id`),
  KEY `uid` (`uid`),
  KEY `read_item` (`read_item`),
  KEY `post_id` (`post_id`)
) ENGINE=MyISAM;