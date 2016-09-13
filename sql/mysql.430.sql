-- irmtfan remove ALTER TABLE `bb_posts` DROP INDEX `approved`;

ALTER TABLE `bb_posts`
  ADD INDEX `forumid_uid` (`forum_id`, `uid`);
ALTER TABLE `bb_posts`
  ADD INDEX `topicid_uid` (`topic_id`, `uid`);
ALTER TABLE `bb_posts`
  ADD INDEX `forumid_approved_postid` (`forum_id`, `approved`, `post_id`);
-- irmtfan add read_time indexes
ALTER TABLE `bb_reads_topic`
  ADD INDEX `read_item_uid` (`read_item`, `uid`);

ALTER TABLE `bb_reads_forum`
  ADD INDEX `read_item_uid` (`read_item`, `uid`);
