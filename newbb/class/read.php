<?php
// $Id: read.php 62 2012-08-17 10:15:26Z alfred $
//  ------------------------------------------------------------------------ //
//                XOOPS - PHP Content Management System                      //
//                    Copyright (c) 2000 XOOPS.org                           //
//                       <http://www.xoops.org/>                             //
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
//  URL: http://xoopsforge.com, http://xoops.org.cn                          //
//  Project: Article Project                                                 //
//  ------------------------------------------------------------------------ //

// defined("XOOPS_ROOT_PATH") || exit("XOOPS root path not defined");

defined("NEWBB_FUNCTIONS_INI") || include $GLOBALS['xoops']->path('modules/newbb/include/functions.ini.php');
newbb_load_object();

/**
 * A handler for read/unread handling
 *
 * @package     newbb/cbb
 *
 * @author        D.J. (phppp, http://xoopsforge.com)
 * @copyright    copyright (c) 2005 XOOPS.org
 */
class Read extends ArtObject
{
    /**
     * @param $type
     */
    public function Read($type)
    {
//        parent::__construct("bb_reads_" . $type);
        $this->ArtObject("bb_reads_" . $type);
        $this->initVar('read_id', XOBJ_DTYPE_INT);
        $this->initVar('uid', XOBJ_DTYPE_INT);
        $this->initVar('read_item', XOBJ_DTYPE_INT);
        $this->initVar('post_id', XOBJ_DTYPE_INT);
        $this->initVar('read_time', XOBJ_DTYPE_INT);
    }
}

/**
 * Class NewbbReadHandler
 */
class NewbbReadHandler extends ArtObjectHandler
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
     * @param $db
     * @param $type
     */
    public function NewbbReadHandler(&$db, $type)
    {
        $type = ("forum" === $type) ? "forum" : "topic";
        $this->ArtObjectHandler($db, 'bb_reads_' . $type, 'Read' . $type, 'read_id', 'post_id');
        $this->type  = $type;
        $newbbConfig = newbbLoadConfig();
        // irmtfan if read_expire = 0 dont clean
        $this->lifetime = isset($newbbConfig["read_expire"]) ? (int) ($newbbConfig["read_expire"]) * 24 * 3600 : 30 * 24 * 3600;
        $this->mode     = isset($newbbConfig["read_mode"]) ? $newbbConfig["read_mode"] : 2;
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

        /* for MySQL 4.1+ */
        if ($this->mysql_major_version() >= 4) {
            $sql = "DELETE bb FROM " . $this->table . " AS bb" .
                   " LEFT JOIN " . $this->table . " AS aa ON bb.read_item = aa.read_item " .
                   " WHERE aa.post_id > bb.post_id";
        } else {
            // for 4.0+
            $sql = "DELETE " . $this->table . " FROM " . $this->table .
                   " LEFT JOIN " . $this->table . " AS aa ON " . $this->table . ".read_item = aa.read_item " .
                   " WHERE aa.post_id > " . $this->table . ".post_id";
        }
        if (!$result = $this->db->queryF($sql)) {
            //xoops_error($this->db->error());
            return false;
        }
        // irmtfan if read_expire = 0 dont clean
        if (empty($this->lifetime)) {
            return true;
        }
        // irmtfan move here and rephrase
        $expire = time() - (int) ($this->lifetime);
        $sql    = "DELETE FROM " . $this->table . " WHERE read_time < " . $expire;
        if (!$result = $this->db->queryF($sql)) {
            //xoops_error($this->db->error());
            return false;
        }

        return true;
    }

    // END irmtfan rephrase function to 1- add clearDuplicate and 2- dont clean when read_expire = 0
    /**
     * @param $read_item
     * @param null $uid
     * @return bool|mixed|null
     */
    public function getRead($read_item, $uid = null)
    {
        if (empty($this->mode)) {
            return null;
        }
        if (1 === $this->mode) {
            return $this->getRead_cookie($read_item);
        } else {
            return $this->getRead_db($read_item, $uid);
        }
    }

    /**
     * @param $item_id
     * @return mixed
     */
    public function getRead_cookie($item_id)
    {
        $cookie_name = ($this->type === "forum") ? "LF" : "LT";
        $cookie_var  = $item_id;
        // irmtfan set true to return array
        $lastview = newbb_getcookie($cookie_name, true);

        return @$lastview[$cookie_var];
    }

    /**
     * @param $read_item
     * @param $uid
     * @return bool|null
     */
    public function getRead_db($read_item, $uid)
    {
        if (empty($uid)) {
            if (is_object($GLOBALS["xoopsUser"])) {
                $uid = $GLOBALS["xoopsUser"]->getVar("uid");
            } else {
                return false;
            }
        }
        $sql = "SELECT post_id " .
               " FROM " . $this->table .
               " WHERE read_item = " . (int) ($read_item) .
               "     AND uid = " . (int) ($uid);
        if (!$result = $this->db->queryF($sql, 1)) {
            return null;
        }
        list($post_id) = $this->db->fetchRow($result);

        return $post_id;
    }

    /**
     * @param $read_item
     * @param $post_id
     * @param null $uid
     * @return bool|mixed|void
     */
    public function setRead($read_item, $post_id, $uid = null)
    {
        if (empty($this->mode)) {
            return true;
        }
        if (1 === $this->mode) {
            return $this->setRead_cookie($read_item, $post_id);
        } else {
            return $this->setRead_db($read_item, $post_id, $uid);
        }
    }

    /**
     * @param $read_item
     * @param $post_id
     */
    public function setRead_cookie($read_item, $post_id)
    {
        $cookie_name          = ($this->type === "forum") ? "LF" : "LT";
        $lastview             = newbb_getcookie($cookie_name, true);
        $lastview[$read_item] = time();
        newbb_setcookie($cookie_name, $lastview);
    }

    /**
     * @param $read_item
     * @param $post_id
     * @param $uid
     * @return bool|mixed
     */
    public function setRead_db($read_item, $post_id, $uid)
    {
        if (empty($uid)) {
            if (is_object($GLOBALS["xoopsUser"])) {
                $uid = $GLOBALS["xoopsUser"]->getVar("uid");
            } else {
                return false;
            }
        }

        $sql = "UPDATE " . $this->table .
               " SET post_id = " . (int) ($post_id) . "," .
               "     read_time =" . time() .
               " WHERE read_item = " . (int) ($read_item) .
               "     AND uid = " . (int) ($uid);
        if ($this->db->queryF($sql) && $this->db->getAffectedRows()) {
            return true;
        }
        $object =& $this->create();
        $object->setVar("read_item", $read_item, true);
        $object->setVar("post_id", $post_id, true);
        $object->setVar("uid", $uid, true);
        $object->setVar("read_time", time(), true);

        return parent::insert($object);
    }

    /**
     * @param $items
     * @param null $uid
     * @return array|null
     */
    public function isRead_items(&$items, $uid = null)
    {
        $ret = null;
        if (empty($this->mode)) {
            return $ret;
        }

        if (1 === $this->mode) {
            $ret = $this->isRead_items_cookie($items);
        } else {
            $ret = $this->isRead_items_db($items, $uid);
        }

        return $ret;
    }

    /**
     * @param $items
     * @return array
     */
    public function isRead_items_cookie(&$items)
    {
        $cookie_name = ($this->type === "forum") ? "LF" : "LT";
        $cookie_vars = newbb_getcookie($cookie_name, true);

        $ret = array();
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
    public function isRead_items_db(&$items, $uid)
    {
        $ret = array();
        if (empty($items)) {
            return $ret;
        }

        if (empty($uid)) {
            if (is_object($GLOBALS["xoopsUser"])) {
                $uid = $GLOBALS["xoopsUser"]->getVar("uid");
            } else {
                return $ret;
            }
        }

        $criteria = new CriteriaCompo(new Criteria("uid", $uid));
        $criteria->add(new Criteria("read_item", "(" . implode(", ", array_map("intval", array_keys($items))) . ")", "IN"));
        $items_obj =& $this->getAll($criteria, array("read_item", "post_id"));

        $items_list = array();
        foreach (array_keys($items_obj) as $key) {
            $items_list[$items_obj[$key]->getVar("read_item")] = $items_obj[$key]->getVar("post_id");
        }
        unset($items_obj);

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
        $sql = "CREATE TABLE " . $this->table . "_duplicate like " . $this->table . "; ";
        if (!$result = $this->db->queryF($sql)) {
            xoops_error($this->db->error() . '<br />' . $sql);

            return false;
        }
        $sql = "INSERT " . $this->table . "_duplicate SELECT * FROM " . $this->table . " GROUP BY read_item, uid; ";
        if (!$result = $this->db->queryF($sql)) {
            xoops_error($this->db->error() . '<br />' . $sql);

            return false;
        }
        $sql = "RENAME TABLE " . $this->table . " TO " . $this->table . "_with_duplicate; ";
        if (!$result = $this->db->queryF($sql)) {
            xoops_error($this->db->error() . '<br />' . $sql);

            return false;
        }
        $sql = "RENAME TABLE " . $this->table . "_duplicate TO " . $this->table . "; ";
        if (!$result = $this->db->queryF($sql)) {
            xoops_error($this->db->error() . '<br />' . $sql);

            return false;
        }
        $sql    = "SHOW INDEX FROM " . $this->table . " WHERE KEY_NAME = 'read_item_uid'";
        $result = $this->db->queryF($sql);
        if (empty($result)) {
            $sql .= "ALTER TABLE " . $this->table . " ADD INDEX read_item_uid ( read_item, uid ); ";
            if (!$result = $this->db->queryF($sql)) {
                xoops_error($this->db->error() . '<br />' . $sql);

                return false;
            }
        }
        $sql = "DROP TABLE " . $this->table . "_with_duplicate; ";
        if (!$result = $this->db->queryF($sql)) {
            xoops_error($this->db->error() . '<br />' . $sql);

            return false;
        }

        return true;
    }
    // END irmtfan add clear duplicated rows function
}
