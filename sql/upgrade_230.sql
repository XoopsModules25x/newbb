CREATE TABLE `bb_moderates` (
  `mod_id` int(10) unsigned NOT NULL auto_increment,
  `mod_start` int(10) NOT NULL default '0',
  `mod_end` int(10) NOT NULL default '0',
  `mod_desc` varchar(255) NOT NULL default '',
  `uid` int(10) NOT NULL default '0',
  `ip` varchar(32) NOT NULL default '',
  `forum_id` smallint(4) NOT NULL default '0',
  PRIMARY KEY  (`mod_id`),
  KEY `uid` (`uid`)
) ENGINE=MyISAM;
