<?php declare(strict_types=1);

/**
 * NewBB 5.0x,  the forum module for XOOPS project
 *
 * @copyright      XOOPS Project (https://xoops.org)
 * @license        GNU GPL 2.0 or later (https://www.gnu.org/licenses/gpl-2.0.html)
 * @author         Taiwen Jiang (phppp or D.J.) <phppp@users.sourceforge.net>
 * @since          4.00
 */

use XoopsModules\Newbb\{
    Helper,
    ReadHandler,
    ReadtopicHandler
};

/** @var Helper $helper */
/** @var ReadHandler $readHandler */
defined('NEWBB_FUNCTIONS_INI') || require __DIR__ . '/functions.ini.php';
define('NEWBB_FUNCTIONS_READ_LOADED', true);

if (!defined('NEWBB_FUNCTIONS_READ')) {
    define('NEWBB_FUNCTIONS_READ', 1);

    /**
     * @param        $type
     * @param        $item_id
     * @param        $post_id
     * @param null   $uid
     * @return mixed
     */
    function newbbSetRead($type, $item_id, $post_id, $uid = null)
    {
        $readHandler = Helper::getInstance()->getHandler('Read' . $type);

        return $readHandler->setRead($item_id, $post_id, $uid);
    }

    /**
     * @param        $type
     * @param        $item_id
     * @param null   $uid
     * @return mixed
     */
    function newbbGetRead($type, $item_id, $uid = null)
    {
        /** @var ReadHandler $readHandler */
        $readHandler = Helper::getInstance()->getHandler('Read' . $type);

        return $readHandler->getRead($item_id, $uid);
    }

    /**
     * @param int  $status
     * @param null $uid
     * @return mixed
     */
    function newbbSetReadforum($status = 0, $uid = null)
    {
        /** @var ReadHandler $readforumHandler */
        $readforumHandler = Helper::getInstance()->getHandler('Readforum');

        return $readforumHandler->setReadItems($status, $uid);
    }

    /**
     * @param int  $status
     * @param int  $forum_id
     * @param null $uid
     * @return mixed
     */
    function newbbSetReadTopic($status = 0, $forum_id = 0, $uid = null)
    {
        /** @var ReadHandler $readTopicHandler */
        $readTopicHandler = Helper::getInstance()->getHandler('Readtopic');

        return $readTopicHandler->setReadItems($status, $forum_id, $uid);
    }

    /**
     * @param        $type
     * @param        $items
     * @param null   $uid
     * @return mixed
     */
    function newbbIsRead($type, $items, $uid = null)
    {
        /** @var ReadHandler $readHandler */
        $readHandler = Helper::getInstance()->getHandler('Read' . $type);

        return $readHandler->isReadItems($items, $uid);
    }
}
