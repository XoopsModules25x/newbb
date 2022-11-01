<?php declare(strict_types=1);

/**
 * NewBB 5.0x,  the forum module for XOOPS project
 *
 * @copyright      XOOPS Project (https://xoops.org)
 * @license        GNU GPL 2.0 or later (https://www.gnu.org/licenses/gpl-2.0.html)
 * @author         Taiwen Jiang (phppp or D.J.) <phppp@users.sourceforge.net>
 * @since          4.00
 */

use XoopsModules\Newbb\{Helper,
    PostHandler,
    StatsHandler,
    TopicHandler
};

/** @var Helper $helper */
/** @var TopicHandler $topicHandler */
/** @var PostHandler $postHandler */
defined('NEWBB_FUNCTIONS_INI') || require __DIR__ . '/functions.ini.php';
define('NEWBB_FUNCTIONS_STATS_LOADED', true);

if (!defined('NEWBB_FUNCTIONS_STATS')) {
    define('NEWBB_FUNCTIONS_STATS', 1);

    /**
     * @return array
     */
    function newbbGetStats()
    {
        /** @var StatsHandler $statsHandler */
        $statsHandler = Helper::getInstance()->getHandler('Stats');
        $stats        = $statsHandler->getStats();

        return $stats;
    }

    /**
     * @param        $id
     * @param        $type
     * @param int    $increment
     * @return bool
     */
    function newbbUpdateStats($id, $type, $increment = 1)
    {
        /** @var StatsHandler $statsHandler */
        $statsHandler = Helper::getInstance()->getHandler('Stats');

        return $statsHandler->update($id, $type, $increment);
    }

    /*
    * Gets the total number of topics in a form
    */
    /**
     * @param string $forum_id
     * @return int
     */
    function getTotalTopics($forum_id = '')
    {
        $topicHandler = Helper::getInstance()->getHandler('Topic');
        $criteria     = new \CriteriaCompo(new \Criteria('approved', 0, '>'));
        if ($forum_id) {
            $criteria->add(new \Criteria('forum_id', (int)$forum_id));
        }

        return $topicHandler->getCount($criteria);
    }

    /*
    * Returns the total number of posts in the whole system, a forum, or a topic
    * Also can return the number of users on the system.
    */
    /**
     * @param int    $id
     * @param string $type
     * @return int
     */
    function getTotalPosts($id = 0, $type = 'all')
    {
        $postHandler = Helper::getInstance()->getHandler('Post');
        $criteria    = new \CriteriaCompo(new \Criteria('approved', 0, '>'));
        switch ($type) {
            case 'forum':
                if ($id > 0) {
                    $criteria->add(new \Criteria('forum_id', (int)$id));
                }
                break;
            case 'topic':
                if ($id > 0) {
                    $criteria->add(new \Criteria('topic_id', (int)$id));
                }
                break;
            case 'all':
            default:
                break;
        }

        return $postHandler->getCount($criteria);
    }

    /**
     * @return null
     */
    function getTotalViews()
    {
        $sql = 'SELECT sum(topic_views) FROM ' . $GLOBALS['xoopsDB']->prefix('newbb_topics') . ' ';
        $result = $GLOBALS['xoopsDB']->query($sql);
        if (!$GLOBALS['xoopsDB']->isResultSet($result)) {
            //            \trigger_error("Query Failed! SQL: $sql- Error: " . $xoopsDB->error(), E_USER_ERROR);
            return null;
        }
        
        [$total] = $GLOBALS['xoopsDB']->fetchRow($result);

        return $total;
    }
}
