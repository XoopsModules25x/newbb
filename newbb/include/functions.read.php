<?php
/**
 * NewBB 4.3x, the forum module for XOOPS project
 *
 * @copyright    XOOPS Project (http://xoops.org)
 * @license        http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author        Taiwen Jiang (phppp or D.J.) <phppp@users.sourceforge.net>
 * @since        4.00
 * @version        $Id $
 * @package        module::newbb
 */

// defined('XOOPS_ROOT_PATH') || exit('XOOPS root path not defined');

defined("NEWBB_FUNCTIONS_INI") || include __DIR__ . "/functions.ini.php";
define("NEWBB_FUNCTIONS_READ_LOADED", true);

if (!defined("NEWBB_FUNCTIONS_READ")) {
    define("NEWBB_FUNCTIONS_READ", 1);

    /**
     * @param $type
     * @param $item_id
     * @param $post_id
     * @param null $uid
     * @return mixed
     */
    function newbb_setRead($type, $item_id, $post_id, $uid = null)
    {
        $read_handler = xoops_getmodulehandler("read" . $type, "newbb");

        return $read_handler->setRead($item_id, $post_id, $uid);
    }

    /**
     * @param $type
     * @param $item_id
     * @param null $uid
     * @return mixed
     */
    function newbb_getRead($type, $item_id, $uid = null)
    {
        $read_handler =& xoops_getmodulehandler("read" . $type, "newbb");

        return $read_handler->getRead($item_id, $uid);
    }

    /**
     * @param int $status
     * @param null $uid
     * @return mixed
     */
    function newbb_setRead_forum($status = 0, $uid = null)
    {
        $read_handler =& xoops_getmodulehandler("readforum", "newbb");

        return $read_handler->setRead_items($status, $uid);
    }

    /**
     * @param int $status
     * @param int $forum_id
     * @param null $uid
     * @return mixed
     */
    function newbb_setRead_topic($status = 0, $forum_id = 0, $uid = null)
    {
        $read_handler =& xoops_getmodulehandler("readtopic", "newbb");

        return $read_handler->setRead_items($status, $forum_id, $uid);
    }

    /**
     * @param $type
     * @param $items
     * @param null $uid
     * @return mixed
     */
    function newbb_isRead($type, &$items, $uid = null)
    {
        $read_handler =& xoops_getmodulehandler("read" . $type, "newbb");

        return $read_handler->isRead_items($items, $uid);
    }
}
