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

defined('NEWBB_FUNCTIONS_INI') || include __DIR__ . '/functions.ini.php';
define('NEWBB_FUNCTIONS_READ_LOADED', true);

if (!defined('NEWBB_FUNCTIONS_READ')) {
    define('NEWBB_FUNCTIONS_READ', 1);

    /**
     * @param        $type
     * @param        $item_id
     * @param        $post_id
     * @param  null  $uid
     * @return mixed
     */
    function newbbSetRead($type, $item_id, $post_id, $uid = null)
    {
        /** @var Newbb\ReadHandler $readHandler */
        $readHandler = Newbb\Helper::getInstance()->getHandler('Read'.$type);

        return $readHandler->setRead($item_id, $post_id, $uid);
    }

    /**
     * @param        $type
     * @param        $item_id
     * @param  null  $uid
     * @return mixed
     */
    function newbbGetRead($type, $item_id, $uid = null)
    {
        /** @var Newbb\ReadHandler $readHandler */
        $readHandler = Newbb\Helper::getInstance()->getHandler('Read'.$type);

        return $readHandler->getRead($item_id, $uid);
    }

    /**
     * @param  int  $status
     * @param  null $uid
     * @return mixed
     */
    function newbbSetReadForum($status = 0, $uid = null)
    {
        /** @var Newbb\ReadForumHandler $readHandler */
        $readForumHandler = Newbb\Helper::getInstance()->getHandler('Readforum');

        return $readForumHandler->setReadItems($status, $uid);
    }

    /**
     * @param  int  $status
     * @param  int  $forum_id
     * @param  null $uid
     * @return mixed
     */
    function newbbSetReadTopic($status = 0, $forum_id = 0, $uid = null)
    {
        /** @var Newbb\ReadtopicHandler $readHandler */
        $readTopicHandler = Newbb\Helper::getInstance()->getHandler('Readtopic');

        return $readTopicHandler->setReadItems($status, $forum_id, $uid);
    }

    /**
     * @param        $type
     * @param        $items
     * @param  null  $uid
     * @return mixed
     */
    function newbbIsRead($type, &$items, $uid = null)
    {
        /** @var Newbb\ReadHandler $readHandler */
        $readHandler = Newbb\Helper::getInstance()->getHandler('Read'.$type);

        return $readHandler->isReadItems($items, $uid);
    }
}
