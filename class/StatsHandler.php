<?php namespace XoopsModules\Newbb;

/**
 * NewBB 5.0x,  the forum module for XOOPS project
 *
 * @copyright      XOOPS Project (https://xoops.org)
 * @license        GNU GPL 2 or later (http://www.gnu.org/licenses/gpl-2.0.html)
 * @author         Taiwen Jiang (phppp or D.J.) <phppp@users.sourceforge.net>
 * @since          4.00
 * @package        module::newbb
 */

// defined('XOOPS_ROOT_PATH') || die('Restricted access');

defined('NEWBB_FUNCTIONS_INI') || require_once __DIR__ . '/functions.ini.php';

define('NEWBB_STATS_TYPE_TOPIC', 1);
define('NEWBB_STATS_TYPE_POST', 2);
define('NEWBB_STATS_TYPE_DIGEST', 3);
define('NEWBB_STATS_TYPE_VIEW', 4);

define('NEWBB_STATS_PERIOD_TOTAL', 1);
define('NEWBB_STATS_PERIOD_DAY', 2);
define('NEWBB_STATS_PERIOD_WEEK', 3);
define('NEWBB_STATS_PERIOD_MONTH', 4);

/**
 * Stats for forum
 *
 */
class StatsHandler
{
    public $db;
    public $table;
    public $param = [
        'type'   => ['topic', 'post', 'digest', 'view'],
        'period' => ['total', 'day', 'week', 'month']
    ];

    /**
     * @param null|\XoopsDatabase $db
     */
    public function __construct(\XoopsDatabase $db = null)
    {
        //$this->db = $db;
        //if (!$db || !($db instanceof XoopsDatabase)) {
        $this->db = $GLOBALS['xoopsDB'];
        //}
        $this->table = $this->db->prefix('newbb_stats');
    }

    /**
     * @param  null|\XoopsDatabase $db
     * @return StatsHandler
     */
    public static function getInstance(\XoopsDatabase $db = null)
    {
        static $instance;
        if (null === $instance) {
            $instance = new static($db);
        }

        return $instance;
    }

    /**
     * @param       $id
     * @param       $type
     * @param  int  $increment
     * @return bool
     */
    public function update($id, $type, $increment = 1)
    {
        $id        = (int)$id;
        $increment = (int)$increment;

        if (empty($increment) || false === ($type = array_search($type, $this->param['type']))) {
            return false;
        }

        $sql    = "    UPDATE {$this->table}"
                  . '    SET stats_value = CASE '
                  . "                    WHEN time_format = '' OR DATE_FORMAT(time_update, time_format) = DATE_FORMAT(NOW(), time_format)  THEN stats_value + '{$increment}' "
                  . "                    ELSE '{$increment}' "
                  . '                END, '
                  . '        time_update = NOW()'
                  . '    WHERE '
                  . "        (stats_id = '0' OR stats_id = '{$id}') "
                  . "        AND stats_type='{$type}' ";
        $result = $this->db->queryF($sql);
        $rows   = $this->db->getAffectedRows();
        if (0 == $rows) {
            $sql    = "    INSERT INTO {$this->table}"
                      . '        (`stats_id`, `stats_value`, `stats_type`, `stats_period`, `time_update`, `time_format`) '
                      . '    VALUES '
                      . "        ('0', '{$increment}', '{$type}', '"
                      . array_search('total', $this->param['period'], true)
                      . "', NOW(), ''), "
                      . "        ('0', '{$increment}', '{$type}', '"
                      . array_search('day', $this->param['period'], true)
                      . "', NOW(), '%Y%j'), "
                      . "        ('0', '{$increment}', '{$type}', '"
                      . array_search('week', $this->param['period'], true)
                      . "', NOW(), '%Y%u'), "
                      . "        ('0', '{$increment}', '{$type}', '"
                      . array_search('month', $this->param['period'], true)
                      . "', NOW(), '%Y%m')";
            $result = $this->db->queryF($sql);
        }
        if ($rows < 2 * count($this->param['period']) && !empty($id)) {
            $sql    = "    INSERT INTO {$this->table}"
                      . '        (`stats_id`, `stats_value`, `stats_type`, `stats_period`, `time_update`, `time_format`) '
                      . '    VALUES '
                      . "        ('{$id}', '{$increment}', '{$type}', '"
                      . array_search('total', $this->param['period'], true)
                      . "', NOW(), ''), "
                      . "        ('{$id}', '{$increment}', '{$type}', '"
                      . array_search('day', $this->param['period'], true)
                      . "', NOW(), '%Y%j'), "
                      . "        ('{$id}', '{$increment}', '{$type}', '"
                      . array_search('week', $this->param['period'], true)
                      . "', NOW(), '%Y%u'), "
                      . "        ('{$id}', '{$increment}', '{$type}', '"
                      . array_search('month', $this->param['period'], true)
                      . "', NOW(), '%Y%m')";
            $result = $this->db->queryF($sql);
        }
    }

    /**
     * Get stats of "Today"
     *
     * @param  array $ids     ID of forum: > 0, forum; 0 - global; empty - all
     * @param  array $types   type of stats items: 1 - topic; 2 - post; 3 - digest; 4 - click; empty - all
     * @param  array $periods time period: 1 - all time; 2 - today; 3 - this week; 4 - this month; empty - all
     * @return array
     */
    public function getStats(array $ids, array $types = [], array $periods = [])
    {
        $ret = [];

        $_types = [];
        foreach ($types as $type) {
            $_types[] = array_search($type, $this->param['type']);
        }
        $_periods = [];
        foreach ($periods as $period) {
            $_periods[] = array_search($period, $this->param['period']);
        }
        $sql    = '    SELECT stats_id, stats_value, stats_type, stats_period '
                  . "    FROM {$this->table} "
                  . '    WHERE '
                  . "        ( time_format = '' OR DATE_FORMAT(time_update, time_format) = DATE_FORMAT(NOW(), time_format) ) "
                  . '        '
                  . (empty($ids) ? '' : 'AND stats_id IN ('
                                        . implode(', ', array_map('intval', $ids))
                                        . ')')
                  . '        '
                  . (empty($_types) ? '' : 'AND stats_type IN (' . implode(', ', $_types) . ')')
                  . '        '
                  . (empty($_periods) ? '' : 'AND stats_period IN (' . implode(', ', $_periods) . ')');
        $result = $this->db->query($sql);

        while (false !== ($row = $this->db->fetchArray($result))) {
            $ret[(string)$row['stats_id']][$this->param['type'][$row['stats_type']]][$this->param['period'][$row['stats_period']]] = $row['stats_value'];
        }

        return $ret;
    }

    public function reset()
    {
        $this->db->queryF('TRUNCATE TABLE ' . $this->table);
        $now        = time();
        $time_start = [
            'day'   => '%Y%j',
            'week'  => '%Y%u',
            'month' => '%Y%m'
        ];
        $counts     = [];

        $sql = '    SELECT forum_id' . '    FROM ' . $this->db->prefix('newbb_forums');
        $ret = $this->db->query($sql);
        while (false !== (list($forum_id) = $this->db->fetchRow($ret))) {
            $sql    = '    SELECT COUNT(*), SUM(topic_views)' . '    FROM ' . $this->db->prefix('newbb_topics') . "    WHERE approved=1 AND forum_id = {$forum_id}";
            $result = $this->db->query($sql);
            list($topics, $views) = $this->db->fetchRow($result);
            $this->update($forum_id, 'topic', $topics);
            $this->update($forum_id, 'view', $views);

            $sql    = '    SELECT COUNT(*)' . '    FROM ' . $this->db->prefix('newbb_topics') . "    WHERE approved=1 AND topic_digest >0 AND forum_id = {$forum_id}";
            $result = $this->db->query($sql);
            list($digests) = $this->db->fetchRow($result);
            $this->update($forum_id, 'digest', $digests);

            $sql    = '    SELECT COUNT(*)' . '    FROM ' . $this->db->prefix('newbb_posts') . "    WHERE approved=1 AND forum_id = {$forum_id}";
            $result = $this->db->query($sql);
            list($posts) = $this->db->fetchRow($result);
            $this->update($forum_id, 'post', $posts);

            foreach ($time_start as $period => $format) {
                $sql    = '    SELECT COUNT(*), SUM(topic_views)' . '    FROM ' . $this->db->prefix('newbb_topics') . "    WHERE approved=1 AND forum_id = {$forum_id}" . "        AND FROM_UNIXTIME(topic_time, '{$format}') >= FROM_UNIXTIME({$now}, '{$format}')";
                $result = $this->db->query($sql);
                list($topics, $views) = $this->db->fetchRow($result);
                $views = empty($views) ? 0 : $views; // null check
                $this->db->queryF("    INSERT INTO {$this->table}"
                                  . '        (`stats_id`, `stats_value`, `stats_type`, `stats_period`, `time_update`, `time_format`) '
                                  . '    VALUES '
                                  . "        ('{$forum_id}', '{$topics}', '"
                                  . array_search('topic', $this->param['type'], true)
                                  . "', '"
                                  . array_search($period, $this->param['period'])
                                  . "', NOW(), '{$format}')");
                $this->db->queryF("    INSERT INTO {$this->table}"
                                  . '        (`stats_id`, `stats_value`, `stats_type`, `stats_period`, `time_update`, `time_format`) '
                                  . '    VALUES '
                                  . "        ('{$forum_id}', '{$views}', '"
                                  . array_search('view', $this->param['type'], true)
                                  . "', '"
                                  . array_search($period, $this->param['period'])
                                  . "', NOW(), '{$format}')");
                @$counts['topic'][$period] += $topics;
                @$counts['view'][$period] += $views;

                $sql    = '    SELECT COUNT(*)' . '    FROM ' . $this->db->prefix('newbb_topics') . "    WHERE approved=1 AND topic_digest >0 AND forum_id = {$forum_id}" . "        AND FROM_UNIXTIME(digest_time, '{$format}') >= FROM_UNIXTIME({$now}, '{$format}')";
                $result = $this->db->query($sql);
                list($digests) = $this->db->fetchRow($result);
                $this->db->queryF("    INSERT INTO {$this->table}"
                                  . '        (`stats_id`, `stats_value`, `stats_type`, `stats_period`, `time_update`, `time_format`) '
                                  . '    VALUES '
                                  . "        ('{$forum_id}', '{$digests}', '"
                                  . array_search('digest', $this->param['type'], true)
                                  . "', '"
                                  . array_search($period, $this->param['period'])
                                  . "', NOW(), '{$format}')");
                @$counts['digest'][$period] += $digests;

                $sql    = '    SELECT COUNT(*)' . '    FROM ' . $this->db->prefix('newbb_posts') . "    WHERE approved=1 AND forum_id = {$forum_id}" . "        AND FROM_UNIXTIME(post_time, '{$format}') >= FROM_UNIXTIME({$now}, '{$format}')";
                $result = $this->db->query($sql);
                list($posts) = $this->db->fetchRow($result);
                $this->db->queryF("    INSERT INTO {$this->table}"
                                  . '        (`stats_id`, `stats_value`, `stats_type`, `stats_period`, `time_update`, `time_format`) '
                                  . '    VALUES '
                                  . "        ('{$forum_id}', '{$posts}', '"
                                  . array_search('post', $this->param['type'])
                                  . "', '"
                                  . array_search($period, $this->param['period'], true)
                                  . "', NOW(), '{$format}')");
                @$counts['post'][$period] += $posts;
            }
        }

        $this->db->queryF("    DELETE FROM {$this->table}" . "    WHERE stats_id = '0' AND stats_period <> " . array_search('total', $this->param['period'], true));
        foreach ($time_start as $period => $format) {
            foreach (array_keys($counts) as $type) {
                $this->db->queryF("    INSERT INTO {$this->table}"
                                  . '        (`stats_id`, `stats_value`, `stats_type`, `stats_period`, `time_update`, `time_format`) '
                                  . '    VALUES '
                                  . "        ('0', '{$counts[$type][$period]}', '"
                                  . array_search($type, $this->param['type'], true)
                                  . "', '"
                                  . array_search($period, $this->param['period'], true)
                                  . "', NOW(), '{$format}')");
            }
        }
    }
}
