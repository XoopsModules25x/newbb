<?php
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
require_once $GLOBALS['xoops']->path('modules/newbb/include/functions.php');
if (!defined('NEWBB_NOTIFY_ITEMINFO')) {
    define('NEWBB_NOTIFY_ITEMINFO', 1);

    /**
     * @param $category
     * @param $item_id
     * @return mixed
     */
    function newbb_notify_iteminfo($category, $item_id)
    {
        /** @var \XoopsModuleHandler $moduleHandler */
        $moduleHandler = xoops_getHandler('module');
        $module        = $moduleHandler->getByDirname('newbb');

        if ('global' === $category) {
            $item['name'] = '';
            $item['url']  = '';

            return $item;
        }
        $item_id = (int)$item_id;

        if ('forum' === $category) {
            // Assume we have a valid forum id
            $sql = 'SELECT forum_name FROM ' . $GLOBALS['xoopsDB']->prefix('newbb_forums') . ' WHERE forum_id = ' . $item_id;
            if (!$result = $GLOBALS['xoopsDB']->query($sql)) {
                // irmtfan full URL
                redirect_header(XOOPS_URL . '/modules/' . $module->getVar('dirname') . 'index.php', 2, _MD_NEWBB_ERRORFORUM);
            }
            $result_array = $GLOBALS['xoopsDB']->fetchArray($result);
            $item['name'] = $result_array['forum_name'];
            $item['url']  = XOOPS_URL . '/modules/' . $module->getVar('dirname') . '/viewforum.php?forum=' . $item_id;

            return $item;
        }

        if ('thread' === $category) {
            // Assume we have a valid topid id
            $sql = 'SELECT t.topic_title,f.forum_id,f.forum_name FROM ' . $GLOBALS['xoopsDB']->prefix('newbb_topics') . ' t, ' . $GLOBALS['xoopsDB']->prefix('newbb_forums') . ' f WHERE t.forum_id = f.forum_id AND t.topic_id = ' . $item_id . ' LIMIT 1';
            if (!$result = $GLOBALS['xoopsDB']->query($sql)) {
                // irmtfan full URL
                redirect_header(XOOPS_URL . '/modules/' . $module->getVar('dirname') . 'index.php', 2, _MD_NEWBB_ERROROCCURED);
            }
            $result_array = $GLOBALS['xoopsDB']->fetchArray($result);
            $item['name'] = $result_array['topic_title'];
            $item['url']  = XOOPS_URL . '/modules/' . $module->getVar('dirname') . '/viewtopic.php?forum=' . $result_array['forum_id'] . '&topic_id=' . $item_id;

            return $item;
        }

        if ('post' === $category) {
            // Assume we have a valid post id
            $sql = 'SELECT subject,topic_id,forum_id FROM ' . $GLOBALS['xoopsDB']->prefix('newbb_posts') . ' WHERE post_id = ' . $item_id . ' LIMIT 1';
            if (!$result = $GLOBALS['xoopsDB']->query($sql)) {
                // irmtfan full URL
                redirect_header(XOOPS_URL . '/modules/' . $module->getVar('dirname') . 'index.php', 2, _MD_NEWBB_ERROROCCURED);
            }
            $result_array = $GLOBALS['xoopsDB']->fetchArray($result);
            $item['name'] = $result_array['subject'];
            $item['url']  = XOOPS_URL . '/modules/' . $module->getVar('dirname') . '/viewtopic.php?forum= ' . $result_array['forum_id'] . '&amp;topic_id=' . $result_array['topic_id'] . '#forumpost' . $item_id;

            return $item;
        }
    }
}
