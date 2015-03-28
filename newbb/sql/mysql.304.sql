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
CHANGE `topic_id` `topic_id` INT(8) UNSIGNED NOT NULL DEFAULT '0',
CHANGE `post_id` `post_id` INT(10) UNSIGNED NOT NULL DEFAULT '0';

ALTER TABLE `bb_attachments`
CHANGE `post_id` `post_id` INT(10) UNSIGNED NOT NULL DEFAULT '0',
CHANGE `online` `online` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
CHANGE `attach_time` `attach_time` INT(10) UNSIGNED NOT NULL DEFAULT '0',
CHANGE `download` `download` INT(10) UNSIGNED NOT NULL DEFAULT '0';

ALTER TABLE `bb_categories`
DROP `cat_state`,
CHANGE `cat_url` `cat_url` VARCHAR(255) NOT NULL DEFAULT '',
DROP `cat_showdescript`;
ALTER TABLE `bb_categories`
ADD INDEX `cat_order` (`cat_order`);

ALTER TABLE `bb_digest`
CHANGE `digest_time` `digest_time` INT(10) UNSIGNED NOT NULL DEFAULT '0';

ALTER TABLE `bb_forums`
CHANGE `parent_forum` `parent_forum` SMALLINT(4) UNSIGNED NOT NULL DEFAULT '0',
CHANGE `forum_moderator` `forum_moderator` VARCHAR(255) NOT NULL DEFAULT '',
CHANGE `forum_topics` `forum_topics` INT(8) UNSIGNED NOT NULL DEFAULT '0',
CHANGE `forum_posts` `forum_posts` INT(10) UNSIGNED NOT NULL DEFAULT '0',
CHANGE `forum_last_post_id` `forum_last_post_id` INT(10) UNSIGNED NOT NULL DEFAULT '0',
CHANGE `cat_id` `cat_id` SMALLINT(3) UNSIGNED NOT NULL DEFAULT '0',
CHANGE `forum_type` `forum_type` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
CHANGE `allow_html` `allow_html` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
CHANGE `allow_sig` `allow_sig` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
CHANGE `allow_subject_prefix` `allow_subject_prefix` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
CHANGE `forum_order` `forum_order` SMALLINT(4) UNSIGNED NOT NULL DEFAULT '0',
CHANGE `attach_maxkb` `attach_maxkb` SMALLINT(3) UNSIGNED NOT NULL DEFAULT '1000',
CHANGE `attach_ext` `attach_ext` VARCHAR(255) NOT NULL DEFAULT '',
CHANGE `allow_polls` `allow_polls` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
DROP `allow_attachments`,
DROP `subforum_count`;
ALTER TABLE `bb_forums`
ADD INDEX `forum_last_post_id` (`forum_last_post_id`),
ADD INDEX `cat_forum` (`cat_id`, `forum_order`),
ADD INDEX `forum_order` (`forum_order`),
ADD INDEX `cat_id` (`cat_id`);

ALTER TABLE `bb_votedata`
CHANGE `topic_id` `topic_id` INT(8) UNSIGNED NOT NULL DEFAULT '0',
CHANGE `ratinguser` `ratinguser` INT(10) UNSIGNED NOT NULL DEFAULT '0',
CHANGE `ratingtimestamp` `ratingtimestamp` INT(10) UNSIGNED NOT NULL DEFAULT '0';

ALTER TABLE `bb_online`
CHANGE `online_forum` `online_forum` INT(10) UNSIGNED NOT NULL DEFAULT '0',
CHANGE `online_topic` `online_topic` INT(8) UNSIGNED NOT NULL DEFAULT '0',
CHANGE `online_uid` `online_uid` INT(10) UNSIGNED NOT NULL DEFAULT '0',
CHANGE `online_updated` `online_updated` INT(10) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `bb_online`
ADD INDEX `online_forum` (`online_forum`),
ADD INDEX `online_topic` (`online_topic`),
ADD INDEX `online_updated` (`online_updated`);

ALTER TABLE `bb_report`
CHANGE `post_id` `post_id` INT(10) UNSIGNED NOT NULL DEFAULT '0',
CHANGE `reporter_uid` `reporter_uid` INT(10) UNSIGNED NOT NULL DEFAULT '0',
CHANGE `report_time` `report_time` INT(10) UNSIGNED NOT NULL DEFAULT '0',
CHANGE `report_result` `report_result` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0';

ALTER TABLE `bb_posts`
CHANGE `post_id` `post_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
CHANGE `pid` `pid` INT(10) UNSIGNED NOT NULL DEFAULT '0',
CHANGE `topic_id` `topic_id` INT(8) UNSIGNED NOT NULL DEFAULT '0',
CHANGE `forum_id` `forum_id` SMALLINT(4) UNSIGNED NOT NULL DEFAULT '0',
CHANGE `post_time` `post_time` INT(10) UNSIGNED NOT NULL DEFAULT '0',
CHANGE `dohtml` `dohtml` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
CHANGE `dosmiley` `dosmiley` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
CHANGE `doxcode` `doxcode` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
CHANGE `dobr` `dobr` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
CHANGE `doimage` `doimage` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
CHANGE `attachsig` `attachsig` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
CHANGE `approved` `approved` SMALLINT(2) NOT NULL DEFAULT '1',
CHANGE `post_karma` `post_karma` INT(10) UNSIGNED NOT NULL DEFAULT '0',
CHANGE `require_reply` `require_reply` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `bb_posts`
ADD INDEX `forum_id` (`forum_id`),
ADD INDEX `topic_id` (`topic_id`),
ADD INDEX `post_time` (`post_time`);

ALTER TABLE `bb_posts_text`
CHANGE `post_id` `post_id` INT(10) UNSIGNED NOT NULL DEFAULT '0';

ALTER TABLE `bb_topics`
CHANGE `topic_poster` `topic_poster` INT(10) UNSIGNED NOT NULL DEFAULT '0',
CHANGE `topic_time` `topic_time` INT(10) UNSIGNED NOT NULL DEFAULT '0',
CHANGE `topic_views` `topic_views` INT(10) UNSIGNED NOT NULL DEFAULT '0',
CHANGE `topic_replies` `topic_replies` MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
CHANGE `topic_last_post_id` `topic_last_post_id` INT(8) UNSIGNED NOT NULL DEFAULT '0',
CHANGE `forum_id` `forum_id` SMALLINT(4) UNSIGNED NOT NULL DEFAULT '0',
CHANGE `topic_status` `topic_status` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
CHANGE `topic_subject` `topic_subject` SMALLINT(3) UNSIGNED NOT NULL DEFAULT '0',
CHANGE `topic_sticky` `topic_sticky` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
CHANGE `topic_digest` `topic_digest` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
CHANGE `digest_time` `digest_time` INT(10) UNSIGNED NOT NULL DEFAULT '0',
CHANGE `approved` `approved` TINYINT(2) NOT NULL DEFAULT '1',
CHANGE `topic_haspoll` `topic_haspoll` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `bb_topics`
ADD INDEX `digest_time` (`digest_time`);

ALTER TABLE `bb_moderates`
CHANGE `mod_start` `mod_start` INT(10) UNSIGNED NOT NULL DEFAULT '0',
CHANGE `mod_end` `mod_end` INT(10) UNSIGNED NOT NULL DEFAULT '0',
CHANGE `uid` `uid` INT(10) UNSIGNED NOT NULL DEFAULT '0',
CHANGE `forum_id` `forum_id` SMALLINT(4) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `bb_moderates`
ADD INDEX `mod_end` (`mod_end`),
ADD INDEX `forum_id` (`forum_id`);

CREATE TABLE `bb_reads_topic` (
  `read_id`   INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `uid`       INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `read_time` INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `read_item` INT(8) UNSIGNED  NOT NULL DEFAULT '0',
  `post_id`   INT(10) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (`read_id`),
  KEY `uid` (`uid`),
  KEY `read_item` (`read_item`),
  KEY `post_id` (`post_id`)
)
  ENGINE = MyISAM;

CREATE TABLE `bb_reads_forum` (
  `read_id`   INT(10) UNSIGNED     NOT NULL AUTO_INCREMENT,
  `uid`       INT(10) UNSIGNED     NOT NULL DEFAULT '0',
  `read_time` INT(10) UNSIGNED     NOT NULL DEFAULT '0',
  `read_item` SMALLINT(4) UNSIGNED NOT NULL DEFAULT '0',
  `post_id`   INT(10) UNSIGNED     NOT NULL DEFAULT '0',
  PRIMARY KEY (`read_id`),
  KEY `uid` (`uid`),
  KEY `read_item` (`read_item`),
  KEY `post_id` (`post_id`)
)
  ENGINE = MyISAM;
