<?php
/**
 * NewBB 4.3x, the forum module for XOOPS project
 *
 * @copyright      XOOPS Project (http://xoops.org)
 * @license        http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author         Taiwen Jiang (phppp or D.J.) <phppp@users.sourceforge.net>
 * @since          4.00
 * @version        $Id $
 * @package        module::newbb
 */

// defined('XOOPS_ROOT_PATH') || exit('XOOPS root path not defined');

defined('NEWBB_FUNCTIONS_INI') || include_once __DIR__ . '/functions.ini.php';
define('NEWBB_FUNCTIONS_STATS_LOADED', true);

if (!defined('NEWBB_FUNCTIONS_STATS')) {
    define('NEWBB_FUNCTIONS_STATS', 1);

    /**
     * @return mixed
     */
    function newbb_get_stats()
    {
        $statsHandler = xoops_getModuleHandler('stats', 'newbb');
        $stats        = $statsHandler->getStats();

        return $stats;
    }

    /**
     * @param        $id
     * @param        $type
     * @param  int $increment
     * @return mixed
     */
    function newbb_update_stats($id, $type, $increment = 1)
    {
        $statsHandler = xoops_getModuleHandler('stats', 'newbb');

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
        $topicHandler = xoops_getModuleHandler('topic', 'newbb');
        $criteria     = new CriteriaCompo(new Criteria('approved', 0, '>'));
        if ($forum_id) {
            $criteria->add(new Criteria('forum_id', (int)$forum_id));
        }

        return $topicHandler->getCount($criteria);
    }

    /*
    * Returns the total number of posts in the whole system, a forum, or a topic
    * Also can return the number of users on the system.
    */
    /**
     * @param  int $id
     * @param  string $type
     * @return mixed
     */
    function getTotalPosts($id = 0, $type = 'all')
    {
        $postHandler = xoops_getModuleHandler('post', 'newbb');
        $criteria    = new CriteriaCompo(new Criteria('approved', 0, '>'));
        switch ($type) {
            case 'forum':
                if ($id > 0) {
                    $criteria->add(new Criteria('forum_id', (int)$id));
                }
                break;
            case 'topic':
                if ($id > 0) {
                    $criteria->add(new Criteria('topic_id', (int)$id));
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
        $sql = 'SELECT sum(topic_views) FROM ' . $GLOBALS['xoopsDB']->prefix('bb_topics') . '';
        if (!$result = $GLOBALS['xoopsDB']->query($sql)) {
            return null;
        }
        list($total) = $GLOBALS['xoopsDB']->fetchRow($result);

        return $total;
    }
}
