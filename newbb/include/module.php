<?php
// $Id: module.php 62 2012-08-17 10:15:26Z alfred $
//  ------------------------------------------------------------------------ //
//                XOOPS - PHP Content Management System                      //
//                    Copyright (c) 2000 XOOPS.org                           //
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
// defined('XOOPS_ROOT_PATH') || exit('XOOPS root path not defined');
if (defined('XOOPS_MODULE_NEWBB_FUCTIONS')) {
    exit();
}
define('XOOPS_MODULE_NEWBB_FUCTIONS', 1);

include_once $GLOBALS['xoops']->path('modules/newbb/include/functions.ini.php');

newbb_load_object();

/**
 * @param XoopsModule $module
 * @param null        $oldversion
 * @return bool
 */
function xoops_module_update_newbb(XoopsModule $module, $oldversion = null)
{
    //  START irmtfan to not run update script if user has the latest version.
    if ($oldversion === round($module->getInfo('version') * 100, 2)) {
        $module->setErrors('You have the latest ' . $module->getInfo('name') . ' module (' . $module->getInfo('dirname') . ' version ' . $module->getInfo('version') . ') and update is not necessary');
        //        print_r($module->getErrors());
        echo($module->getErrors());

        return true;
    }
    //  END irmtfan to dont run update script if user has the latest version.

    load_functions('config');
    mod_clearConfg($module->getVar('dirname', 'n'));

    $newbbConfig = newbbLoadConfig();

    //$oldversion = $module->getVar('version');
    //$oldconfig = $module->getVar('hasconfig');
    // NewBB 1.0 -- no config
    //if (empty($oldconfig)) {
    if ($oldversion === 100) {
        include_once __DIR__ . '/module.v100.php';
        xoops_module_update_newbb_v100($module);
    }

    // NewBB 2.* and CBB 1.*
    // change group permission name
    // change forum moderators
    if ($oldversion < 220) {
        include_once __DIR__ . '/module.v220.php';
        xoops_module_update_newbb_v220($module);
    }

    if ($oldversion < 230) {
        $GLOBALS['xoopsDB']->queryFromFile($GLOBALS['xoops']->path('modules/' . $module->getVar('dirname', 'n') . '/sql/upgrade_230.sql'));
        //$module->setErrors("bb_moderates table inserted");
    }

    if ($oldversion < 304) {
        $GLOBALS['xoopsDB']->queryFromFile($GLOBALS['xoops']->path('modules/' . $module->getVar('dirname', 'n') . '/sql/mysql.304.sql'));
    }

    if ($oldversion < 400) {
        $GLOBALS['xoopsDB']->queryFromFile($GLOBALS['xoops']->path('modules/' . $module->getVar('dirname', 'n') . '/sql/mysql.400.sql'));
        include __DIR__ . '/module.v400.php';
        xoops_module_update_newbb_v400($module);
    }

    if ($oldversion < 403) {
        $sql = "    ALTER TABLE " . $GLOBALS['xoopsDB']->prefix('bb_posts') . " CHANGE `poster_ip` `poster_ip` varchar(15) NOT NULL default '0.0.0.0'";
        $GLOBALS['xoopsDB']->queryF($sql);
    }

    if ($oldversion < 431) {
        $GLOBALS['xoopsDB']->queryFromFile($GLOBALS['xoops']->path('modules/' . $module->getVar('dirname', 'n') . '/sql/mysql.430.sql'));
    }

    if ($oldversion < 434) {
        // remove old html template files

        // create an array with all folders, and then run this once

        $templateDirectory = $GLOBALS['xoops']->path('modules/' . $module->getVar('dirname', 'n') . '/templates/');
        $template_list     = array_diff(scandir($templateDirectory), array('..', '.'));
        foreach ($template_list as $k => $v) {
            $fileinfo = new SplFileInfo($templateDirectory . $v);
            if ($fileinfo->getExtension() === 'html' && $fileinfo->getFilename() !== 'index.html') {
                @unlink($templateDirectory . $v);
            }
        }
        $templateDirectory = $GLOBALS['xoops']->path('modules/' . $module->getVar('dirname', 'n') . '/templates/blocks');
        $template_list     = array_diff(scandir($templateDirectory), array('..', '.'));
        foreach ($template_list as $k => $v) {
            $fileinfo = new SplFileInfo($templateDirectory . $v);
            if ($fileinfo->getExtension() === 'html' && $fileinfo->getFilename() !== 'index.html') {
                @unlink($templateDirectory . $v);
            }
        }
        // Load class XoopsFile
        xoops_load('xoopsfile');
        //remove /images directory
        $imagesDirectory = $GLOBALS['xoops']->path('modules/' . $module->getVar('dirname', 'n') . '/images/');
        $folderHandler   = XoopsFile::getHandler('folder', $imagesDirectory);
        $folderHandler->delete($imagesDirectory);

        //remove old changelogs

        array_map('unlink', glob('some/dir/*.txt'));

        $file = dirname(__DIR__) . '/docs/changelog-rev9883.txt';
        $file = dirname(__DIR__) . '/docs/changelog-rev10095.txt';
        $file = dirname(__DIR__) . '/docs/changelog-rev10109.txt';

        if (is_file("$file")) {
            unlink("$file");
        }
    }

    if (!empty($newbbConfig["syncOnUpdate"])) {
        mod_loadFunctions('recon', 'newbb');
        newbb_synchronization();
    }

    return true;
}

/**
 * @param XoopsModule $module
 * @return bool
 */
function xoops_module_pre_update_newbb(XoopsModule $module)
{
    return newbb_setModuleConfig($module, true);
}

/**
 * @param XoopsModule $module
 * @return bool
 */
function xoops_module_pre_install_newbb(XoopsModule $module)
{
    $mod_tables = $module->getInfo('tables');
    foreach ($mod_tables as $table) {
        $GLOBALS['xoopsDB']->queryF('DROP TABLE IF EXISTS ' . $GLOBALS['xoopsDB']->prefix($table) . ';');
    }

    return newbb_setModuleConfig($module);
}

/**
 * @param XoopsModule $module
 * @return bool
 */
function xoops_module_install_newbb(XoopsModule $module)
{
    /* Create a test category */
    $categoryHandler = &xoops_getmodulehandler('category', $module->getVar('dirname'));
    $category        = $categoryHandler->create();
    $category->setVar('cat_title', _MI_NEWBB_INSTALL_CAT_TITLE, true);
    $category->setVar('cat_image', '', true);
    $category->setVar('cat_description', _MI_NEWBB_INSTALL_CAT_DESC, true);
    $category->setVar('cat_url', 'http://xoops.org XOOPS Project', true);
    if (!$cat_id = $categoryHandler->insert($category)) {
        return true;
    }

    /* Create a forum for test */
    $forumHandler = &xoops_getmodulehandler('forum', $module->getVar('dirname'));
    $forum        = $forumHandler->create();
    $forum->setVar('forum_name', _MI_NEWBB_INSTALL_FORUM_NAME, true);
    $forum->setVar('forum_desc', _MI_NEWBB_INSTALL_FORUM_DESC, true);
    $forum->setVar('forum_moderator', array());
    $forum->setVar('parent_forum', 0);
    $forum->setVar('cat_id', $cat_id);
    $forum->setVar('attach_maxkb', 100);
    $forum->setVar('attach_ext', 'zip|jpg|gif|png');
    $forum->setVar('hot_threshold', 20);
    $forum_id = $forumHandler->insert($forum);

    /* Set corresponding permissions for the category and the forum */
    $module_id    = $module->getVar('mid');
    $gpermHandler = &xoops_gethandler('groupperm');
    $groups_view  = array(XOOPS_GROUP_ADMIN, XOOPS_GROUP_USERS, XOOPS_GROUP_ANONYMOUS);
    $groups_post  = array(XOOPS_GROUP_ADMIN, XOOPS_GROUP_USERS);
    // irmtfan bug fix: html and signature permissions, add: pdf and print permissions
    $post_items = array('post', 'reply', 'edit', 'delete', 'addpoll', 'vote', 'attach', 'noapprove', 'type', 'html', 'signature', 'pdf', 'print');
    foreach ($groups_view as $group_id) {
        $gpermHandler->addRight('category_access', $cat_id, $group_id, $module_id);
        $gpermHandler->addRight('forum_access', $forum_id, $group_id, $module_id);
        $gpermHandler->addRight('forum_view', $forum_id, $group_id, $module_id);
    }
    foreach ($groups_post as $group_id) {
        foreach ($post_items as $item) {
            $gpermHandler->addRight('forum_' . $item, $forum_id, $group_id, $module_id);
        }
    }

    /* Create a test post */
    mod_loadFunctions('user', 'newbb');
    $postHandler = &xoops_getmodulehandler('post', $module->getVar('dirname'));
    $forumpost   = $postHandler->create();
    $forumpost->setVar('poster_ip', newbb_getIP());
    $forumpost->setVar('uid', $GLOBALS['xoopsUser']->getVar('uid'));
    $forumpost->setVar('approved', 1);
    $forumpost->setVar('forum_id', $forum_id);
    $forumpost->setVar('subject', _MI_NEWBB_INSTALL_POST_SUBJECT, true);
    $forumpost->setVar('dohtml', 1);
    $forumpost->setVar('dosmiley', 1);
    $forumpost->setVar('doxcode', 1);
    $forumpost->setVar('dobr', 1);
    $forumpost->setVar('icon', '', true);
    $forumpost->setVar('attachsig', 1);
    $forumpost->setVar('post_time', time());
    $forumpost->setVar('post_text', _MI_NEWBB_INSTALL_POST_TEXT, true);
    $postid = $postHandler->insert($forumpost);

    return true;
}

/**
 * @param      $module
 * @param bool $isUpdate
 * @return bool
 */
function newbb_setModuleConfig(&$module, $isUpdate = false)
{
    return true;
}
