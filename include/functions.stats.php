<?php
/**
 * NewBB 5.0x,  the forum module for XOOPS project
 *
 * @copyright      XOOPS Project (https://xoops.org)
 * @license        GNU GPL 2 or later (http://www.gnu.org/licenses/gpl-2.0.html)
 * @author         Taiwen Jiang (phppp or D.J.) <phppp@users.sourceforge.net>
 * @since          4.00
 * @package        module::newbb
 */

use XoopsModules\Newbb;

// defined('XOOPS_ROOT_PATH') || die('Restricted access');

defined('NEWBB_FUNCTIONS_INI') || require_once __DIR__ . '/functions.ini.php';
define('NEWBB_FUNCTIONS_STATS_LOADED', true);

if (!defined('NEWBB_FUNCTIONS_STATS')) {
    define('NEWBB_FUNCTIONS_STATS', 1);

    /**
     * @return mixed
     */
    function newbbGetStats()
    {
        /** @var Newbb\StatsHandler $statsHandler */
        $statsHandler = Newbb\Helper::getInstance()->getHandler('Stats');
        $stats        = $statsHandler->getStats();

        return $stats;
    }

    /**
     * @param        $id
     * @param        $type
     * @param  int   $increment
     * @return mixed
     */
    function newbbUpdateStats($id, $type, $increment = 1)
    {
        /** @var Newbb\StatsHandler $statsHandler */
        $statsHandler = Newbb\Helper::getInstance()->getHandler('Stats');

        return $statsHandler->update($id, $type, $increment);
    }

    /*
    * Gets the total number of topics in a form
    */
    /**
     * @param  string $forum_id
     * @return mixed
     */
    function getTotalTopics($forum_id = '')
    {
        /** @var Newbb\TopicHandler $topicHandler */
        $topicHandler = Newbb\Helper::getInstance()->getHandler('Topic');
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
     * @param  int    $id
     * @param  string $type
     * @return mixed
     */
    function getTotalPosts($id = 0, $type = 'all')
    {
        /** @var Newbb\PostHandler $postHandler */
        $postHandler = Newbb\Helper::getInstance()->getHandler('Post');
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
        if (!$result = $GLOBALS['xoopsDB']->query($sql)) {
            return null;
        }
        list($total) = $GLOBALS['xoopsDB']->fetchRow($result);

        return $total;
    }
}
