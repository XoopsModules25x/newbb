CREATE TABLE `bb_moderates` (
  `mod_id`    INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `mod_start` INT(10)          NOT NULL DEFAULT '0',
  `mod_end`   INT(10)          NOT NULL DEFAULT '0',
  `mod_desc`  VARCHAR(255)     NOT NULL DEFAULT '',
  `uid`       INT(10)          NOT NULL DEFAULT '0',
  `ip`        VARCHAR(32)      NOT NULL DEFAULT '',
  `forum_id`  SMALLINT(4)      NOT NULL DEFAULT '0',
  PRIMARY KEY (`mod_id`),
  KEY `uid` (`uid`)
)
  ENGINE = MyISAM;
