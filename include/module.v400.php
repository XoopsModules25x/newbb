<?php declare(strict_types=1);
/**
 * NewBB 4.3x, the forum module for XOOPS project
 *
 * @copyright      XOOPS Project (https://xoops.org)
 * @license        GNU GPL 2.0 or later (https://www.gnu.org/licenses/gpl-2.0.html)
 * @author         Taiwen Jiang (phppp or D.J.) <phppp@users.sourceforge.net>
 * @since          4.00
 * @return bool
 */

use XoopsModules\Tag;

function xoops_module_update_newbb_v400(XoopsModule $module)
{
    $statsHandler = xoops_getModuleHandler('stats', 'newbb');

    $sql = 'SELECT `forum_id`, `forum_topics`, `forum_posts` FROM ' . $GLOBALS['xoopsDB']->prefix('bb_forums');
    $result = $GLOBALS['xoopsDB']->query($sql);
    if (!$GLOBALS['xoopsDB']->isResultSet($result)) {
        \trigger_error("Query Failed! SQL: $sql- Error: " . $GLOBALS['xoopsDB']->error(), E_USER_ERROR);
    }
    while (false !== ($row = $GLOBALS['xoopsDB']->fetchArray($result))) {
        $statsHandler->update($row['forum_id'], 'topic', $row['forum_topics']);
        $statsHandler->update($row['forum_id'], 'post', $row['forum_posts']);
    }

    $sql = 'SELECT `forum_id`, SUM(topic_views) AS views FROM ' . $GLOBALS['xoopsDB']->prefix('bb_topics') . ' GROUP BY `forum_id`';
    $result = $GLOBALS['xoopsDB']->query($sql);
    if (!$GLOBALS['xoopsDB']->isResultSet($result)) {
        \trigger_error("Query Failed! SQL: $sql- Error: " . $GLOBALS['xoopsDB']->error(), E_USER_ERROR);
    }
    while (false !== ($row = $GLOBALS['xoopsDB']->fetchArray($result))) {
        $statsHandler->update($row['forum_id'], 'view', $row['views']);
    }

    $sql = 'SELECT `forum_id`, COUNT(*) AS digests FROM ' . $GLOBALS['xoopsDB']->prefix('bb_topics') . ' WHERE topic_digest = 1 GROUP BY `forum_id`';
    $result = $GLOBALS['xoopsDB']->query($sql);
    if (!$GLOBALS['xoopsDB']->isResultSet($result)) {
        \trigger_error("Query Failed! SQL: $sql- Error: " . $GLOBALS['xoopsDB']->error(), E_USER_ERROR);
    }
    while (false !== ($row = $GLOBALS['xoopsDB']->fetchArray($result))) {
        $statsHandler->update($row['forum_id'], 'digest', $row['digests']);
    }

    $sql = 'SELECT SUM(forum_topics) AS topics, SUM(forum_posts) AS posts FROM ' . $GLOBALS['xoopsDB']->prefix('bb_forums');
    $result = $GLOBALS['xoopsDB']->query($sql);
    if (!$GLOBALS['xoopsDB']->isResultSet($result)) {
        \trigger_error("Query Failed! SQL: $sql- Error: " . $GLOBALS['xoopsDB']->error(), E_USER_ERROR);
    }
    while (false !== ($row = $GLOBALS['xoopsDB']->fetchArray($result))) {
        $statsHandler->update(-1, 'topic', $row['topics']);
        $statsHandler->update(-1, 'post', $row['posts']);
    }

    /*
    $GLOBALS['xoopsDB']->queryF(
            "    INSERT INTO ".$GLOBALS['xoopsDB']->prefix("bb_stats").
            "        (`id`, `value`, `type`, `period`, `time_update`, `time_format`)".
            "    SELECT `forum_id`, `forum_topics`, '".NEWBB_STATS_TYPE_TOPIC."', '".NEWBB_STATS_PERIOD_TOTAL."', NOW() + 0, ''".
            "         FROM ".$GLOBALS['xoopsDB']->prefix("bb_forums")
            );
    $GLOBALS['xoopsDB']->queryF(
            "    INSERT INTO ".$GLOBALS['xoopsDB']->prefix("bb_stats").
            "        (`id`, `value`, `type`, `period`, `time_update`, `time_format`)".
            "    SELECT `forum_id`, `forum_posts`, '".NEWBB_STATS_TYPE_POST."', '".NEWBB_STATS_PERIOD_TOTAL."', NOW() + 0, ''".
            "         FROM ".$GLOBALS['xoopsDB']->prefix("bb_forums")
            );
    $GLOBALS['xoopsDB']->queryF(
            "    INSERT INTO ".$GLOBALS['xoopsDB']->prefix("bb_stats").
            "        (`id`, `value`, `type`, `period`, `time_update`, `time_format`)".
            "    SELECT `forum_id`, count(*), '".NEWBB_STATS_TYPE_DIGEST."', '".NEWBB_STATS_PERIOD_TOTAL."', NOW() + 0, ''".
            "         FROM ".$GLOBALS['xoopsDB']->prefix("bb_topics").
            "        WHERE topic_digest = 1".
            "        GROUP BY `forum_id`".
            );
    $GLOBALS['xoopsDB']->queryF(
            "    INSERT INTO ".$GLOBALS['xoopsDB']->prefix("bb_stats").
            "        (`id`, `value`, `type`, `period`, `time_update`, `time_format`)".
            "    SELECT `forum_id`, SUM(topic_views), '".NEWBB_STATS_TYPE_VIEW."', '".NEWBB_STATS_PERIOD_TOTAL."', NOW() + 0, ''".
            "         FROM ".$GLOBALS['xoopsDB']->prefix("bb_topics").
            "        WHERE topic_digest = 1".
            "        GROUP BY `forum_id`".
            );
    */

    $sql = '    UPDATE '
           . $GLOBALS['xoopsDB']->prefix('bb_posts_text')
           . ' AS t, '
           . $GLOBALS['xoopsDB']->prefix('bb_posts')
           . ' AS p'
           . '    SET t.dohtml = p.dohtml, '
           . '        t.dosmiley = p.dosmiley, '
           . '        t.doxcode = p.doxcode, '
           . '        t.doimage = p.doimage, '
           . '        t.dobr = p.dobr'
           . '    WHERE p.post_id =t.post_id ';
    if ($GLOBALS['xoopsDB']->queryF($sql)) {
        $sql = '    ALTER TABLE ' . $GLOBALS['xoopsDB']->prefix('bb_posts') . '        DROP `dohtml`,' . '        DROP `dosmiley`,' . '        DROP `doxcode`,' . '        DROP `doimage`,' . '        DROP `dobr`';
        $GLOBALS['xoopsDB']->queryF($sql);
    } else {
        xoops_error($GLOBALS['xoopsDB']->error() . '<br>' . $sql);
    }

    if (\class_exists(\XoopsModules\Tag\TagHandler::class) && xoops_isActiveModule('tag')) {
        $tagHandler  = \XoopsModules\Tag\Helper::getInstance()
                                               ->getHandler('Tag');
        $table_topic = $GLOBALS['xoopsDB']->prefix('bb_topics');
        $sql    = '    SELECT topic_id, topic_tags' . "    FROM {$table_topic}";
        $result = $GLOBALS['xoopsDB']->query($sql);
        if (!$GLOBALS['xoopsDB']->isResultSet($result)) {
            xoops_error($GLOBALS['xoopsDB']->error());
            //            \trigger_error("Query Failed! SQL: $sql- Error: " . $xoopsDB->error(), E_USER_ERROR);

        } else {
            while (false !== ($myrow = $GLOBALS['xoopsDB']->fetchArray($result))) {
                if (empty($myrow['topic_tags'])) {
                    continue;
                }
                $tagHandler->updateByItem($myrow['topic_tags'], $myrow['topic_id'], $module->getVar('mid'));
            }
        }
    }

    $sql = ' SELECT COUNT(*)
            FROM ' . $GLOBALS['xoopsDB']->prefix('bb_type_tmp') . ' AS a, ' . $GLOBALS['xoopsDB']->prefix('bb_type_forum_tmp') . ' AS b
            WHERE a.type_id = b.type_id AND a.type_id >0;';
    $result = $GLOBALS['xoopsDB']->query($sql);
    if (!$result) {
        //xoops_error($GLOBALS['xoopsDB']->error());
        $GLOBALS['xoopsDB']->queryF('DROP TABLE ' . $GLOBALS['xoopsDB']->prefix('bb_type_tmp'));
        $GLOBALS['xoopsDB']->queryF('DROP TABLE ' . $GLOBALS['xoopsDB']->prefix('bb_type_forum_tmp'));

        return true;
    }

    $sql = ' INSERT INTO ' . $GLOBALS['xoopsDB']->prefix('bb_type') . '        (`type_id`, `type_name`, `type_color`)' . '    SELECT `type_id`, `type_name`, `type_color`' . '         FROM ' . $GLOBALS['xoopsDB']->prefix('bb_type_tmp');
    $result = $GLOBALS['xoopsDB']->queryF($sql);
    if (!$result){
    xoops_error($GLOBALS['xoopsDB']->error());
    }

    $sql = '    INSERT INTO ' . $GLOBALS['xoopsDB']->prefix('bb_type_forum') . '        (`type_id`, `forum_id`, `type_order`)' . '    SELECT `type_id`, `forum_id`, `type_order`' . '         FROM ' . $GLOBALS['xoopsDB']->prefix('bb_type_forum_tmp');
    $result = $GLOBALS['xoopsDB']->queryF($sql);
    if (!$result){
        xoops_error($GLOBALS['xoopsDB']->error());
    }

    $GLOBALS['xoopsDB']->queryF('DROP TABLE ' . $GLOBALS['xoopsDB']->prefix('bb_type_tmp'));
    $GLOBALS['xoopsDB']->queryF('DROP TABLE ' . $GLOBALS['xoopsDB']->prefix('bb_type_forum_tmp'));

    return true;
}
