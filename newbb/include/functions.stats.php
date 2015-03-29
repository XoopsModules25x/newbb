<?php
/**
 * CBB 4.0, or newbb, the forum module for XOOPS project
 *
 * @copyright    The XOOPS Project http://xoops.sf.net
 * @license        http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author        Taiwen Jiang (phppp or D.J.) <phppp@users.sourceforge.net>
 * @since        4.00
 * @version        $Id $
 * @package        module::newbb
 */

// defined('XOOPS_ROOT_PATH') || exit('XOOPS root path not defined');

defined("NEWBB_FUNCTIONS_INI") || include_once __DIR__ . "/functions.ini.php";
define("NEWBB_FUNCTIONS_STATS_LOADED", true);

if (!defined("NEWBB_FUNCTIONS_STATS")) {
    define("NEWBB_FUNCTIONS_STATS", 1);

    /**
     * @return mixed
     */
    function newbb_get_stats()
    {
        $stats_handler =& xoops_getmodulehandler('stats', 'newbb');
        $stats         = $stats_handler->getStats();

        return $stats;
    }

    /**
     * @param $id
     * @param $type
     * @param int $increment
     * @return mixed
     */
    function newbb_update_stats($id, $type, $increment = 1)
    {
        $stats_handler =& xoops_getmodulehandler('stats', 'newbb');

        return $stats_handler->update($id, $type, $increment);
    }

    /*
    * Gets the total number of topics in a form
    */
    /**
     * @param string $forum_id
     * @return mixed
     */
    function get_total_topics($forum_id = "")
    {
        $topic_handler =& xoops_getmodulehandler('topic', 'newbb');
        $criteria      = new CriteriaCompo(new Criteria("approved", 0, ">"));
        if ($forum_id) {
            $criteria->add(new Criteria("forum_id", intval($forum_id)));
        }

        return $topic_handler->getCount($criteria);
    }

    /*
    * Returns the total number of posts in the whole system, a forum, or a topic
    * Also can return the number of users on the system.
    */
    /**
     * @param int $id
     * @param string $type
     * @return mixed
     */
    function get_total_posts($id = 0, $type = "all")
    {
        $post_handler =& xoops_getmodulehandler('post', 'newbb');
        $criteria     = new CriteriaCompo(new Criteria("approved", 0, ">"));
        switch ($type) {
            case 'forum':
                if ($id > 0) {
                    $criteria->add(new Criteria("forum_id", intval($id)));
                }
                break;
            case 'topic':
                if ($id > 0) {
                    $criteria->add(new Criteria("topic_id", intval($id)));
                }
                break;
            case 'all':
            default:
                break;
        }

        return $post_handler->getCount($criteria);
    }

    /**
     * @return null
     */
    function get_total_views()
    {
        global $xoopsDB;
        $sql = "SELECT sum(topic_views) FROM " . $xoopsDB->prefix("bb_topics") . "";
        if (!$result = $xoopsDB->query($sql)) {
            return null;
        }
        list($total) = $xoopsDB->fetchRow($result);

        return $total;
    }
}
