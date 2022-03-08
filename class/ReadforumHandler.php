<?php declare(strict_types=1);

namespace XoopsModules\Newbb;

/*
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 */
//  ------------------------------------------------------------------------ //
//  Author: phppp (D.J., infomax@gmail.com)                                  //
//  URL: https://xoops.org                                                    //
//  Project: Article Project                                                 //
//  ------------------------------------------------------------------------ //

use XoopsModules\Newbb;

require_once __DIR__ . '/Read.php';

/**
 * A handler for read/unread handling
 *
 *
 * @author        D.J. (phppp, https://xoopsforge.com)
 * @copyright     copyright (c) 2005 XOOPS.org
 */

/**
 * Class ReadforumHandler
 */
class ReadforumHandler extends Newbb\ReadHandler
{
    /**
     * @param \XoopsDatabase|null $db
     */
    public function __construct(\XoopsDatabase $db = null)
    {
        parent::__construct($db, 'forum');
    }

    /**
     * clean orphan items from database
     *
     * @param string $table_link
     * @param string $field_link
     * @param string $field_object
     * @return bool   true on success
     */
    public function cleanOrphan($table_link = '', $field_link = '', $field_object = '') //cleanOrphan()
    {
        parent::cleanOrphan($this->db->prefix('newbb_posts'), 'post_id');

        return parent::cleanOrphan($this->db->prefix('newbb_forums'), 'forum_id', 'read_item');
    }

    /**
     * @param int  $status
     * @param null $uid
     * @return bool
     */
    public function setReadItems($status = 0, $uid = null)
    {
        if (empty($this->mode)) {
            return true;
        }

        if (1 == $this->mode) {
            return $this->setReadItemsCookie($status);
        }

        return $this->setReadItemsDb($status, $uid);
    }

    /**
     * @param int        $status
     * @param array|null $items
     * @return bool
     */
    public function setReadItemsCookie($status, $items = null)
    {
        $cookie_name = 'LF';
        $items       = [];
        if (!empty($status)) {
            /** @var Newbb\ForumHandler $itemHandler */
            $itemHandler = Helper::getInstance()->getHandler('Forum');
            $items_id    = $itemHandler->getIds();
            foreach ($items_id as $key) {
                $items[$key] = \time();
            }
        }
        \newbbSetCookie($cookie_name, $items);

        return true;
    }

    /**
     * @param $status
     * @param $uid
     * @return bool
     */
    public function setReadItemsDb($status, $uid)
    {
        if (empty($uid)) {
            if (\is_object($GLOBALS['xoopsUser'])) {
                $uid = $GLOBALS['xoopsUser']->getVar('uid');
            } else {
                return false;
            }
        }
        if (empty($status)) {
            $this->deleteAll(new \Criteria('uid', $uid));

            return true;
        }

        /** @var Newbb\ForumHandler $itemHandler */
        $itemHandler = Helper::getInstance()->getHandler('Forum');
        $itemsObject = $itemHandler->getAll(null, ['forum_last_post_id']);
        foreach (\array_keys($itemsObject) as $key) {
            $this->setReadDb($key, $itemsObject[$key]->getVar('forum_last_post_id'), $uid);
        }
        unset($itemsObject);

        return true;
    }

    /**
     * @return void
     */
    public function synchronization(): void
    {
        //        return;
    }
}
