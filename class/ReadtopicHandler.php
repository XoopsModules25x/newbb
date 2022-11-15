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
 * Class ReadtopicHandler
 */
class ReadtopicHandler extends Newbb\ReadHandler
{
    /**
     * maximum records per forum for one user.
     * assigned from $GLOBALS['xoopsModuleConfig']["read_items"]
     *
     * @var int
     */
    private $items_per_forum;

    /**
     * @param \XoopsDatabase|null $db
     */
    public function __construct(\XoopsDatabase $db = null)
    {
        parent::__construct($db, 'topic');
        $newbbConfig           = \newbbLoadConfig();
        $this->items_per_forum = isset($newbbConfig['read_items']) ? (int)$newbbConfig['read_items'] : 100;
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

        return parent::cleanOrphan($this->db->prefix('newbb_topics'), 'topic_id', 'read_item');
    }

    /**
     * Clear garbage
     *
     * Delete all expired and duplicated records
     */
    public function clearGarbage()
    {
        parent::clearGarbage();

        // TODO: clearItemsExceedMaximumItemsPerForum
        return true;
    }

    /**
     * @param int  $status
     * @param int  $forum_id
     * @param null $uid
     * @return bool
     */
    public function setReadItems($status = 0, $forum_id = 0, $uid = null)
    {
        if (empty($this->mode)) {
            return true;
        }

        if (1 == $this->mode) {
            return $this->setReadItemsCookie($status, $forum_id);
        }

        return $this->setReadItemsDb($status, $forum_id, $uid);
    }

    /**
     * @param $status
     * @param $forum_id
     * @return bool
     */
    public function setReadItemsCookie($status, $forum_id)
    {
        $cookie_name = 'LT';
        $cookie_vars = \newbbGetCookie($cookie_name, true);

        /** @var Newbb\TopicHandler $itemHandler */
        $itemHandler = Helper::getInstance()->getHandler('Topic');
        $criteria    = new \CriteriaCompo(new \Criteria('forum_id', $forum_id));
        $criteria->setSort('topic_last_post_id');
        $criteria->setOrder('DESC');
        $criteria->setLimit($this->items_per_forum);
        $items = $itemHandler->getIds($criteria);

        foreach ($items as $var) {
            if (empty($status)) {
                if (isset($cookie_vars[$var])) {
                    unset($cookie_vars[$var]);
                }
            } else {
                $cookie_vars[$var] = \time(); /*$items[$var]*/
            }
        }
        \newbbSetCookie($cookie_name, $cookie_vars);

        return true;
    }

    /**
     * @param $status
     * @param $forum_id
     * @param $uid
     * @return bool
     */
    public function setReadItemsDb($status, $forum_id, $uid)
    {
        if (empty($uid)) {
            if (\is_object($GLOBALS['xoopsUser'])) {
                $uid = $GLOBALS['xoopsUser']->getVar('uid');
            } else {
                return false;
            }
        }

        /** @var Newbb\TopicHandler $itemHandler */
        $itemHandler    = Helper::getInstance()->getHandler('Topic');
        $criteria_topic = new \CriteriaCompo(new \Criteria('forum_id', $forum_id));
        $criteria_topic->setSort('topic_last_post_id');
        $criteria_topic->setOrder('DESC');
        $criteria_topic->setLimit($this->items_per_forum);
        $criteria_sticky = new \CriteriaCompo(new \Criteria('forum_id', $forum_id));
        $criteria_sticky->add(new \Criteria('topic_sticky', 1));

        if (empty($status)) {
            $items_id  = $itemHandler->getIds($criteria_topic);
            $sticky_id = $itemHandler->getIds($criteria_sticky);
            $items     = $items_id + $sticky_id;
            $criteria  = new \CriteriaCompo(new \Criteria('uid', $uid));
            $criteria->add(new \Criteria('read_item', '(' . \implode(', ', $items) . ')', 'IN'));
            $this->deleteAll($criteria, true);

            return true;
        }

        $itemsObject  = $itemHandler->getAll($criteria_topic, ['topic_last_post_id']);
        $stickyObject = $itemHandler->getAll($criteria_sticky, ['topic_last_post_id']);
        $itemsObject  += $stickyObject;
        $items        = [];
        foreach (\array_keys($itemsObject) as $key) {
            $items[$key] = $itemsObject[$key]->getVar('topic_last_post_id');
        }
        unset($itemsObject, $stickyObject);

        foreach (\array_keys($items) as $key) {
            $this->setReadDb($key, $items[$key], $uid);
        }

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
