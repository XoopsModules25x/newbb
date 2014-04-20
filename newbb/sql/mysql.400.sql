	
ALTER TABLE `bb_posts_text` 
  	ADD 		`dohtml` 			tinyint(1) 		unsigned NOT NULL default '0',
  	ADD 		`dosmiley` 			tinyint(1) 		unsigned NOT NULL default '1',
  	ADD 		`doxcode` 			tinyint(1) 		unsigned NOT NULL default '1',
  	ADD 		`doimage` 			tinyint(1) 		unsigned NOT NULL default '1',
  	ADD 		`dobr` 				tinyint(1) 		unsigned NOT NULL default '1';
	
ALTER TABLE `bb_posts` 
	ADD INDEX	`approved` (`approved`),
	CHANGE  	`attachment`  `attachment` TEXT NULL DEFAULT NULL;
	
ALTER TABLE `bb_topics` 
	CHANGE 		`topic_subject` `type_id`	smallint(4)         unsigned NOT NULL default '0',
  	ADD 		`topic_tags`				varchar(255)		NOT NULL default '' AFTER	`poll_id`,
	ADD INDEX	`topic_time` (`topic_time`),
	ADD INDEX	`approved` (`approved`),
	ADD INDEX	`type_id` (`type_id`);
	
ALTER TABLE `bb_forums` 
	DROP `forum_type`,
	DROP `allow_html`,
	DROP `allow_sig`,
	DROP `allow_polls`,
	DROP `allow_subject_prefix`;
	
CREATE TABLE `bb_type` (
  `type_id` 			smallint(4) 		unsigned NOT NULL auto_increment,
  `type_name` 			varchar(64) 		NOT NULL default '',
  `type_color` 			varchar(10) 		NOT NULL default '',
  `type_description` 	varchar(255) 		NOT NULL default '',
  
  PRIMARY KEY  			(`type_id`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `bb_type_forum`
-- 

CREATE TABLE `bb_type_forum` (
  `tf_id` 				mediumint(4) 		unsigned NOT NULL auto_increment,
  `type_id` 			smallint(4) 		unsigned NOT NULL default '0',
  `forum_id` 			smallint(4) 		unsigned NOT NULL default '0',
  `type_order` 			smallint(4) 		unsigned NOT NULL default '99',
  
  PRIMARY KEY  			(`tf_id`),
  KEY `forum_id`		(`forum_id`),
  KEY `type_order`		(`type_order`)
) ENGINE=MyISAM;

CREATE TABLE `bb_user_stats` (
  `uid` 				int(10) 		unsigned NOT NULL default '0',
  `user_topics` 		int(10) 		unsigned NOT NULL default '0',
  `user_digests` 		int(10) 		unsigned NOT NULL default '0',
  `user_posts` 			int(10) 		unsigned NOT NULL default '0',
  `user_lastpost` 		int(10) 		unsigned NOT NULL default '0',
  
  UNIQUE KEY  			(`uid`)
) ENGINE=MyISAM;


CREATE TABLE `bb_stats` (
  `stats_id` 			smallint(4) 	NOT NULL default '0',
  `stats_value` 		int(10) 		unsigned NOT NULL default '0',
  `stats_type` 			smallint(2) 	unsigned NOT NULL default '0',
  `stats_period` 		smallint(2) 	unsigned NOT NULL default '0',
  
  `time_update` 		date 			default NULL,
  `time_format` 		varchar(32) 	NOT NULL default '',
	
  KEY `stats_id`		(`stats_id`),
  KEY `stats_type`		(`stats_type`, `stats_period`)
) ENGINE=MyISAM;
