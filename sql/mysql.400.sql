ALTER TABLE `bb_posts_text`
ADD `dohtml` TINYINT(1)    UNSIGNED NOT NULL DEFAULT '0',
ADD `dosmiley` TINYINT(1)    UNSIGNED NOT NULL DEFAULT '1',
ADD `doxcode` TINYINT(1)    UNSIGNED NOT NULL DEFAULT '1',
ADD `doimage` TINYINT(1)    UNSIGNED NOT NULL DEFAULT '1',
ADD `dobr` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1';


ALTER TABLE `bb_posts`
ADD INDEX `approved` (`approved`),
CHANGE `attachment`  `attachment` TEXT NULL;

ALTER TABLE `bb_topics`
CHANGE `topic_subject` `type_id` SMALLINT(4)         UNSIGNED NOT NULL DEFAULT '0',
ADD `topic_tags` VARCHAR(255) NOT NULL DEFAULT ''
AFTER `poll_id`,
ADD INDEX `topic_time` (`topic_time`),
ADD INDEX `approved` (`approved`),
ADD INDEX `type_id` (`type_id`);

ALTER TABLE `bb_forums`
DROP `forum_type`,
DROP `allow_html`,
DROP `allow_sig`,
DROP `allow_polls`,
DROP `allow_subject_prefix`;

CREATE TABLE `bb_type` (
  `type_id`          SMALLINT(4)    UNSIGNED NOT NULL AUTO_INCREMENT,
  `type_name`        VARCHAR(64)             NOT NULL DEFAULT '',
  `type_color`       VARCHAR(10)             NOT NULL DEFAULT '',
  `type_description` VARCHAR(255)            NOT NULL DEFAULT '',

  PRIMARY KEY (`type_id`)
)
  ENGINE = MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `bb_type_forum`
-- 

CREATE TABLE `bb_type_forum` (
  `tf_id`      MEDIUMINT(4)    UNSIGNED NOT NULL AUTO_INCREMENT,
  `type_id`    SMALLINT(4)    UNSIGNED  NOT NULL DEFAULT '0',
  `forum_id`   SMALLINT(4)    UNSIGNED  NOT NULL DEFAULT '0',
  `type_order` SMALLINT(4)    UNSIGNED  NOT NULL DEFAULT '99',

  PRIMARY KEY (`tf_id`),
  KEY `forum_id`    (`forum_id`),
  KEY `type_order`    (`type_order`)
)
  ENGINE = MyISAM;

CREATE TABLE `bb_user_stats` (
  `uid`           INT(10)    UNSIGNED NOT NULL DEFAULT '0',
  `user_topics`   INT(10)    UNSIGNED NOT NULL DEFAULT '0',
  `user_digests`  INT(10)    UNSIGNED NOT NULL DEFAULT '0',
  `user_posts`    INT(10)    UNSIGNED NOT NULL DEFAULT '0',
  `user_lastpost` INT(10)    UNSIGNED NOT NULL DEFAULT '0',

  UNIQUE KEY (`uid`)
)
  ENGINE = MyISAM;


CREATE TABLE `bb_stats` (
  `stats_id`     SMALLINT(4)           NOT NULL DEFAULT '0',
  `stats_value`  INT(10)    UNSIGNED   NOT NULL DEFAULT '0',
  `stats_type`   SMALLINT(2)  UNSIGNED NOT NULL DEFAULT '0',
  `stats_period` SMALLINT(2)  UNSIGNED NOT NULL DEFAULT '0',

  `time_update`  DATE                           DEFAULT NULL,
  `time_format`  VARCHAR(32)           NOT NULL DEFAULT '',

  KEY `stats_id`    (`stats_id`),
  KEY `stats_type`    (`stats_type`, `stats_period`)
)
  ENGINE = MyISAM;
