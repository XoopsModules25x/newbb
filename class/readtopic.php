<?php
// 
//  ------------------------------------------------------------------------ //
//                XOOPS - PHP Content Management System                      //
//                  Copyright (c) 2000-2016 XOOPS.org                        //
//                       <http://xoops.org/>                             //
//  ------------------------------------------------------------------------ //
//  This program is free software; you can redistribute it and/or modify     //
//  it under the terms of the GNU General Public License as published by     //
//  the Free Software Foundation; either version 2 of the License, or        //
//  (at your option) any later version.                                      //
//                                                                           //
//  You may not change or alter any portion of this comment or credits       //
//  of supporting developers from this source code or any supporting         //
//  source code which is considered copyrighted (c) material of the          //
//  original comment or credit authors.                                      //
//                                                                           //
//  This program is distributed in the hope that it will be useful,          //
//  but WITHOUT ANY WARRANTY; without even the implied warranty of           //
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            //
//  GNU General Public License for more details.                             //
//                                                                           //
//  You should have received a copy of the GNU General Public License        //
//  along with this program; if not, write to the Free Software              //
//  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA //
//  ------------------------------------------------------------------------ //
//  Author: phppp (D.J., infomax@gmail.com)                                  //
//  URL: http://xoops.org                                                    //
//  Project: Article Project                                                 //
//  ------------------------------------------------------------------------ //
include_once __DIR__ . '/read.php';

/**
 * A handler for read/unread handling
 *
 * @package       newbb
 *
 * @author        D.J. (phppp, http://xoopsforge.com)
 * @copyright     copyright (c) 2005 XOOPS.org
 */
class Readtopic extends Read
{
    /**
     *
     */
    public function __construct()
    {
        parent::__construct('topic');
        //$this->initVar('forum_id', XOBJ_DTYPE_INT);
    }
}

/**
 * Class NewbbReadtopicHandler
 */
class NewbbReadtopicHandler extends NewbbReadHandler
{
    /**
     * maximum records per forum for one user.
     * assigned from $GLOBALS['xoopsModuleConfig']["read_items"]
     *
     * @var integer
     */
    public $items_per_forum;

    /**
     * @param XoopsDatabase $db
     */
    public function __construct(XoopsDatabase $db)
    {
        parent::__construct($db, 'topic');
        $newbbConfig           = newbbLoadConfig();
        $this->items_per_forum = isset($newbbConfig['read_items']) ? (int)$newbbConfig['read_items'] : 100;
    }

    /**
     * clean orphan items from database
     *
     * @param  string $table_link
     * @param  string $field_link
     * @param  string $field_object
     * @return bool   true on success
     */
    public function cleanOrphan($table_link = '', $field_link = '', $field_object = '') //cleanOrphan()
    {
        parent::cleanOrphan($this->db->prefix('bb_posts'), 'post_id');

        return parent::cleanOrphan($this->db->prefix('bb_topics'), 'topic_id', 'read_item');
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
     * @param  int  $status
     * @param  int  $forum_id
     * @param  null $uid
     * @return bool
     */
    public function setReadItems($status = 0, $forum_id = 0, $uid = null)
    {
        if (empty($this->mode)) {
            return true;
        }

        if ($this->mode == 1) {
            return $this->setReadItemsCookie($status, $forum_id);
        } else {
            return $this->setReadItemsDb($status, $forum_id, $uid);
        }
    }

    /**
     * @param $status
     * @param $forum_id
     * @return bool
     */
    public function setReadItemsCookie($status, $forum_id)
    {
        $cookie_name = 'LT';
        $cookie_vars = newbb_getcookie($cookie_name, true);

        $itemHandler = xoops_getModuleHandler('topic', 'newbb');
        $criteria    = new CriteriaCompo(new Criteria('forum_id', $forum_id));
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
                $cookie_vars[$var] = time() /*$items[$var]*/
                ;
            }
        }
        newbb_setcookie($cookie_name, $cookie_vars);

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
            if (is_object($GLOBALS['xoopsUser'])) {
                $uid = $GLOBALS['xoopsUser']->getVar('uid');
            } else {
                return false;
            }
        }

        $itemHandler    = xoops_getModuleHandler('topic', 'newbb');
        $criteria_topic = new CriteriaCompo(new Criteria('forum_id', $forum_id));
        $criteria_topic->setSort('topic_last_post_id');
        $criteria_topic->setOrder('DESC');
        $criteria_topic->setLimit($this->items_per_forum);
        $criteria_sticky = new CriteriaCompo(new Criteria('forum_id', $forum_id));
        $criteria_sticky->add(new Criteria('topic_sticky', 1));

        if (empty($status)) {
            $items_id  = $itemHandler->getIds($criteria_topic);
            $sticky_id = $itemHandler->getIds($criteria_sticky);
            $items     = $items_id + $sticky_id;
            $criteria  = new CriteriaCompo(new Criteria('uid', $uid));
            $criteria->add(new Criteria('read_item', '(' . implode(', ', $items) . ')', 'IN'));
            $this->deleteAll($criteria, true);

            return true;
        }

        $items_obj  = $itemHandler->getAll($criteria_topic, ['topic_last_post_id']);
        $sticky_obj = $itemHandler->getAll($criteria_sticky, ['topic_last_post_id']);
        $items_obj += $sticky_obj;
        $items = [];
        foreach (array_keys($items_obj) as $key) {
            $items[$key] = $items_obj[$key]->getVar('topic_last_post_id');
        }
        unset($items_obj, $sticky_obj);
        foreach (array_keys($items) as $key) {
            $this->setRead_db($key, $items[$key], $uid);
        }

        return true;
    }

    /**
     *
     */
    public function synchronization()
    {
        //        return;
    }
}
