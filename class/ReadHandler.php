<?php namespace XoopsModules\Newbb;

//
//  ------------------------------------------------------------------------ //
//                XOOPS - PHP Content Management System                      //
//                  Copyright (c) 2000-2016 XOOPS.org                        //
//                       <https://xoops.org/>                             //
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
//  URL: https://xoops.org                                                    //
//  Project: Article Project                                                 //
//  ------------------------------------------------------------------------ //

// defined('XOOPS_ROOT_PATH') || die('Restricted access');

defined('NEWBB_FUNCTIONS_INI') || include $GLOBALS['xoops']->path('modules/newbb/include/functions.ini.php');

/**
 * A handler for read/unread handling
 *
 * @package       newbb
 *
 * @author        D.J. (phppp, http://xoopsforge.com)
 * @copyright     copyright (c) 2005 XOOPS.org
 */

/**
 * Class ReadHandler
 */
class ReadHandler extends \XoopsPersistableObjectHandler
{
    /**
     * Object type.
     * <ul>
     *  <li>forum</li>
     *  <li>topic</li>
     * </ul>
     *
     * @var string
     */
    public $type;

    /**
     * seconds records will persist.
     * assigned from $GLOBALS['xoopsModuleConfig']["read_expire"]
     * <ul>
     *  <li>positive days = delete all read records exist in the tables before expire time // irmtfan add comment</li>
     *  <li>0 = never expires // irmtfan change comment</li>
     *  <li>-1 or any negative days = never records // irmtfan change comment</li>
     * </ul>
     *
     * @var integer
     */
    public $lifetime;

    /**
     * storage mode for records.
     * assigned from $GLOBALS['xoopsModuleConfig']["read_mode"]
     * <ul>
     *  <li>0 = never records</li>
     *  <li>1 = uses cookie</li>
     *  <li>2 = stores in database</li>
     * </ul>
     *
     * @var integer
     */
    public $mode;

    /**
     * @param \XoopsDatabase     $db
     * @param                    $type
     */
    public function __construct(\XoopsDatabase $db, $type)
    {
        $type = ('forum' === $type) ? 'forum' : 'topic';
        parent::__construct($db, 'newbb_reads_' . $type, Read::class . $type, 'read_id', 'post_id');
        $this->type  = $type;
        $newbbConfig = newbbLoadConfig();
        // irmtfan if read_expire = 0 dont clean
        $this->lifetime = isset($newbbConfig['read_expire']) ? (int)$newbbConfig['read_expire'] * 24 * 3600 : 30 * 24 * 3600;
        $this->mode     = isset($newbbConfig['read_mode']) ? $newbbConfig['read_mode'] : 2;
    }

    /**
     * Clear garbage
     *
     * Delete all expired and duplicated records
     */
    // START irmtfan rephrase function to 1- add clearDuplicate and 2- dont clean when read_expire = 0
    public function clearGarbage()
    {
        // irmtfan clear duplicaed rows
        if (!$result = $this->clearDuplicate()) {
            return false;
        }

        $sql = 'DELETE bb FROM ' . $this->table . ' AS bb' . ' LEFT JOIN ' . $this->table . ' AS aa ON bb.read_item = aa.read_item ' . ' WHERE aa.post_id > bb.post_id';
        if (!$result = $this->db->queryF($sql)) {
            //xoops_error($this->db->error());
            return false;
        }
        // irmtfan if read_expire = 0 dont clean
        if (empty($this->lifetime)) {
            return true;
        }
        // irmtfan move here and rephrase
        $expire = time() - (int)$this->lifetime;
        $sql    = 'DELETE FROM ' . $this->table . ' WHERE read_time < ' . $expire;
        if (!$result = $this->db->queryF($sql)) {
            //xoops_error($this->db->error());
            return false;
        }

        return true;
    }

    // END irmtfan rephrase function to 1- add clearDuplicate and 2- dont clean when read_expire = 0

    /**
     * @param                  $read_item
     * @param  null            $uid
     * @return bool|mixed|null
     */
    public function getRead($read_item, $uid = null)
    {
        if (empty($this->mode)) {
            return null;
        }
        if (1 == $this->mode) {
            return $this->getReadCookie($read_item);
        } else {
            return $this->getReadDb($read_item, $uid);
        }
    }

    /**
     * @param $item_id
     * @return mixed
     */
    public function getReadCookie($item_id)
    {
        $cookie_name = ('forum' === $this->type) ? 'LF' : 'LT';
        $cookie_var  = $item_id;
        // irmtfan set true to return array
        $lastview = newbbGetCookie($cookie_name, true);

        return @$lastview[$cookie_var];
    }

    /**
     * @param $read_item
     * @param $uid
     * @return bool|null
     */
    public function getReadDb($read_item, $uid)
    {
        if (empty($uid)) {
            if (is_object($GLOBALS['xoopsUser'])) {
                $uid = $GLOBALS['xoopsUser']->getVar('uid');
            } else {
                return false;
            }
        }
        $sql = 'SELECT post_id ' . ' FROM ' . $this->table . ' WHERE read_item = ' . (int)$read_item . '     AND uid = ' . (int)$uid;
        if (!$result = $this->db->queryF($sql, 1)) {
            return null;
        }
        list($post_id) = $this->db->fetchRow($result);

        return $post_id;
    }

    /**
     * @param                  $read_item
     * @param                  $post_id
     * @param  null            $uid
     * @return bool|mixed|void
     */
    public function setRead($read_item, $post_id, $uid = null)
    {
        if (empty($this->mode)) {
            return true;
        }
        if (1 == $this->mode) {
            return $this->setReadCookie($read_item, $post_id);
        } else {
            return $this->setReadDb($read_item, $post_id, $uid);
        }
    }

    /**
     * @param $read_item
     * @param $post_id
     */
    public function setReadCookie($read_item, $post_id)
    {
        $cookie_name          = ('forum' === $this->type) ? 'LF' : 'LT';
        $lastview             = newbbGetCookie($cookie_name, true);
        $lastview[$read_item] = time();
        newbbSetCookie($cookie_name, $lastview);
    }

    /**
     * @param $read_item
     * @param $post_id
     * @param $uid
     * @return bool|mixed
     */
    public function setReadDb($read_item, $post_id, $uid)
    {
        if (empty($uid)) {
            if (is_object($GLOBALS['xoopsUser'])) {
                $uid = $GLOBALS['xoopsUser']->getVar('uid');
            } else {
                return false;
            }
        }

        $sql = 'UPDATE ' . $this->table . ' SET post_id = ' . (int)$post_id . ',' . '     read_time =' . time() . ' WHERE read_item = ' . (int)$read_item . '     AND uid = ' . (int)$uid;
        if ($this->db->queryF($sql) && $this->db->getAffectedRows()) {
            return true;
        }
        $object = $this->create();
        $object->setVar('read_item', $read_item);
        $object->setVar('post_id', $post_id);
        $object->setVar('uid', $uid);
        $object->setVar('read_time', time());

        return parent::insert($object);
    }

    /**
     * @param             $items
     * @param  null       $uid
     * @return array|null
     */
    public function isReadItems(&$items, $uid = null)
    {
        $ret = null;
        if (empty($this->mode)) {
            return $ret;
        }

        if (1 == $this->mode) {
            $ret = $this->isReadItemsCookie($items);
        } else {
            $ret = $this->isReadItemsDb($items, $uid);
        }

        return $ret;
    }

    /**
     * @param $items
     * @return array
     */
    public function isReadItemsCookie(&$items)
    {
        $cookie_name = ('forum' === $this->type) ? 'LF' : 'LT';
        $cookie_vars = newbbGetCookie($cookie_name, true);

        $ret = [];
        foreach ($items as $key => $last_update) {
            $ret[$key] = (max(@$GLOBALS['last_visit'], @$cookie_vars[$key]) >= $last_update);
        }

        return $ret;
    }

    /**
     * @param $items
     * @param $uid
     * @return array
     */
    public function isReadItemsDb(&$items, $uid)
    {
        $ret = [];
        if (empty($items)) {
            return $ret;
        }

        if (empty($uid)) {
            if (is_object($GLOBALS['xoopsUser'])) {
                $uid = $GLOBALS['xoopsUser']->getVar('uid');
            } else {
                return $ret;
            }
        }

        $criteria = new \CriteriaCompo(new \Criteria('uid', $uid));
        $criteria->add(new \Criteria('read_item', '(' . implode(', ', array_map('intval', array_keys($items))) . ')', 'IN'));
        $itemsObject = $this->getAll($criteria, ['read_item', 'post_id']);

        $items_list = [];
        foreach (array_keys($itemsObject) as $key) {
            $items_list[$itemsObject[$key]->getVar('read_item')] = $itemsObject[$key]->getVar('post_id');
        }
        unset($itemsObject);

        foreach ($items as $key => $last_update) {
            $ret[$key] = (@$items_list[$key] >= $last_update);
        }

        return $ret;
    }

    // START irmtfan add clear duplicated rows function

    /**
     * @return bool
     */
    public function clearDuplicate()
    {
        /**
         * This is needed for the following query GROUP BY clauses to work in MySQL 5.7.
         * This is a TEMPORARY fix. Needing this function is bad in the first place, but
         * needing sloppy SQL to make it work is worse.
         * @todo The schema itself should preclude the duplicates
         */
        $sql = "SET sql_mode=(SELECT REPLACE(@@sql_mode, 'ONLY_FULL_GROUP_BY', ''))";
        $this->db->queryF($sql);

        $sql = 'CREATE TABLE ' . $this->table . '_duplicate LIKE ' . $this->table . '; ';
        if (!$result = $this->db->queryF($sql)) {
            xoops_error($this->db->error() . '<br>' . $sql);

            return false;
        }
        $sql = 'INSERT ' . $this->table . '_duplicate SELECT * FROM ' . $this->table . ' GROUP BY read_item, uid; ';
        if (!$result = $this->db->queryF($sql)) {
            xoops_error($this->db->error() . '<br>' . $sql);

            return false;
        }
        $sql = 'RENAME TABLE ' . $this->table . ' TO ' . $this->table . '_with_duplicate; ';
        if (!$result = $this->db->queryF($sql)) {
            xoops_error($this->db->error() . '<br>' . $sql);

            return false;
        }
        $sql = 'RENAME TABLE ' . $this->table . '_duplicate TO ' . $this->table . '; ';
        if (!$result = $this->db->queryF($sql)) {
            xoops_error($this->db->error() . '<br>' . $sql);

            return false;
        }
        $sql    = 'SHOW INDEX FROM ' . $this->table . " WHERE KEY_NAME = 'read_item_uid'";
        $result = $this->db->queryF($sql);
        if (empty($result)) {
            $sql .= 'ALTER TABLE ' . $this->table . ' ADD INDEX read_item_uid ( read_item, uid ); ';
            if (!$result = $this->db->queryF($sql)) {
                xoops_error($this->db->error() . '<br>' . $sql);

                return false;
            }
        }
        $sql = 'DROP TABLE ' . $this->table . '_with_duplicate; ';
        if (!$result = $this->db->queryF($sql)) {
            xoops_error($this->db->error() . '<br>' . $sql);

            return false;
        }

        return true;
    }
    // END irmtfan add clear duplicated rows function
}
