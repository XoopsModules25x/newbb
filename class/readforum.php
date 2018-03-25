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
class ReadForum extends Read
{
    /**
     *
     */
    public function __construct()
    {
        parent::__construct('forum');
    }
}

/**
 * Class NewbbReadForumHandler
 */
class NewbbReadForumHandler extends NewbbReadHandler
{
    /**
     * @param XoopsDatabase $db
     */
    public function __construct(XoopsDatabase $db)
    {
        parent::__construct($db, 'forum');
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

        return parent::cleanOrphan($this->db->prefix('bb_forums'), 'forum_id', 'read_item');
    }

    /**
     * @param  int  $status
     * @param  null $uid
     * @return bool
     */
    public function setReadItems($status = 0, $uid = null)
    {
        if (empty($this->mode)) {
            return true;
        }

        if (1 == $this->mode) {
            return $this->setReadItemsCookie($status);
        } else {
            return $this->setReadItemsDb($status, $uid);
        }
    }

    /**
     * @param $status
     * @param $items
     * @return bool
     */
    public function setReadItemsCookie($status, $items)
    {
        $cookie_name = 'LF';
        $items       = [];
        if (!empty($status)) {
            $itemHandler = xoops_getModuleHandler('forum', 'newbb');
            $items_id    = $itemHandler->getIds();
            foreach ($items_id as $key) {
                $items[$key] = time();
            }
        }
        newbb_setcookie($cookie_name, $items);

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
            if (is_object($GLOBALS['xoopsUser'])) {
                $uid = $GLOBALS['xoopsUser']->getVar('uid');
            } else {
                return false;
            }
        }
        if (empty($status)) {
            $this->deleteAll(new Criteria('uid', $uid));

            return true;
        }

        $itemHandler = xoops_getModuleHandler('forum', 'newbb');
        $items_obj   = $itemHandler->getAll(null, ['forum_last_post_id']);
        foreach (array_keys($items_obj) as $key) {
            $this->setRead_db($key, $items_obj[$key]->getVar('forum_last_post_id'), $uid);
        }
        unset($items_obj);

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
