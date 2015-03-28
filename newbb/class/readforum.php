<?php
// $Id: readforum.php 62 2012-08-17 10:15:26Z alfred $
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
include_once __DIR__ . '/read.php';

/**
 * A handler for read/unread handling
 *
 * @package     newbb/cbb
 *
 * @author        D.J. (phppp, http://xoopsforge.com)
 * @copyright    copyright (c) 2005 XOOPS.org
 */
class Readforum extends Read
{
    function Readforum()
    {
        $this->Read("forum");
    }
}

class NewbbReadforumHandler extends NewbbReadHandler
{
    function NewbbReadforumHandler(&$db)
    {
        $this->NewbbReadHandler($db, "forum");
    }

    /**
     * clean orphan items from database
     *
     * @return bool true on success
     */
    function cleanOrphan()
    {
        parent::cleanOrphan($this->db->prefix("bb_posts"), "post_id");

        return parent::cleanOrphan($this->db->prefix("bb_forums"), "forum_id", "read_item");
    }

    function setRead_items($status = 0, $uid = null)
    {
        if (empty($this->mode)) return true;

        if ($this->mode == 1) return $this->setRead_items_cookie($status);
        else return $this->setRead_items_db($status, $uid);
    }

    function setRead_items_cookie($status, $items)
    {
        $cookie_name = "LF";
        $items       = array();
        if (!empty($status)) {
            $item_handler =& xoops_getmodulehandler('forum', 'newbb');
            $items_id     = $item_handler->getIds();
            foreach ($items_id as $key) {
                $items[$key] = time();
            }
        }
        newbb_setcookie($cookie_name, $items);

        return true;
    }

    function setRead_items_db($status, $uid)
    {
        if (empty($uid)) {
            if (is_object($GLOBALS["xoopsUser"])) {
                $uid = $GLOBALS["xoopsUser"]->getVar("uid");
            } else {
                return false;
            }
        }
        if (empty($status)) {
            $this->deleteAll(new Criteria("uid", $uid));

            return true;
        }

        $item_handler =& xoops_getmodulehandler('forum', 'newbb');
        $items_obj    =& $item_handler->getAll(null, array("forum_last_post_id"));
        foreach (array_keys($items_obj) as $key) {
            $this->setRead_db($key, $items_obj[$key]->getVar("forum_last_post_id"), $uid);
        }
        unset($items_obj);

        return true;
    }

    function synchronization()
    {
        return;
    }
}
