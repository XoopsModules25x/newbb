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

use XoopsModules\Newbb;

if (defined('XOOPS_MODULE_NEWBB_FUCTIONS')) {
    exit();
}
define('XOOPS_MODULE_NEWBB_FUCTIONS', 1);

require_once $GLOBALS['xoops']->path('modules/newbb/include/functions.ini.php');

/**
 * @param  XoopsModule $module
 * @param  null        $oldversion
 * @return bool
 */
function xoops_module_update_newbb(\XoopsModule $module, $oldversion = null)
{
    $cacheHelper = new \Xmf\Module\Helper\Cache('newbb');
    $cacheHelper->delete('config');

    $newbbConfig = newbbLoadConfig();

    // remove old html template files
    // create an array with all folders, and then run this once

    $templateDirectory = $GLOBALS['xoops']->path('modules/' . $module->getVar('dirname', 'n') . '/templates/');
    $template_list     = array_diff(scandir($templateDirectory, SCANDIR_SORT_NONE), ['..', '.']);
    foreach ($template_list as $k => $v) {
        $fileinfo = new \SplFileInfo($templateDirectory . $v);
        if ('html' === $fileinfo->getExtension() && 'index.html' !== $fileinfo->getFilename()) {
            @unlink($templateDirectory . $v);
        }
    }
    $templateDirectory = $GLOBALS['xoops']->path('modules/' . $module->getVar('dirname', 'n') . '/templates/blocks');
    $template_list     = array_diff(scandir($templateDirectory, SCANDIR_SORT_NONE), ['..', '.']);
    foreach ($template_list as $k => $v) {
        $fileinfo = new \SplFileInfo($templateDirectory . $v);
        if ('html' === $fileinfo->getExtension() && 'index.html' !== $fileinfo->getFilename()) {
            @unlink($templateDirectory . $v);
        }
    }
    // Load class XoopsFile
    xoops_load('xoopsfile');
    //remove /images directory
    $imagesDirectory = $GLOBALS['xoops']->path('modules/' . $module->getVar('dirname', 'n') . '/images/');
    $folderHandler   = \XoopsFile::getHandler('folder', $imagesDirectory);
    $folderHandler->delete($imagesDirectory);

    //remove old changelogs
    array_map('unlink', glob(dirname(__DIR__) . '/docs/changelog-rev*.txt'));

    if (!empty($newbbConfig['syncOnUpdate'])) {
        require_once __DIR__ . '/../include/functions.recon.php';
        newbbSynchronization();
    }

    return true;
}

/**
 * @param  XoopsModule $module
 * @return bool
 */
function xoops_module_pre_update_newbb(\XoopsModule $module)
{
    XoopsLoad::load('migrate', 'newbb');
    $newbbMigrate = new Newbb\Migrate();
    $newbbMigrate->synchronizeSchema();

    return true;
}

/**
 * @param  XoopsModule $module
 * @return bool
 */
function xoops_module_pre_install_newbb(\XoopsModule $module)
{
    $mod_tables =& $module->getInfo('tables');
    foreach ($mod_tables as $table) {
        $GLOBALS['xoopsDB']->queryF('DROP TABLE IF EXISTS ' . $GLOBALS['xoopsDB']->prefix($table) . ';');
    }

    return true;
}

/**
 * @param  XoopsModule $module
 * @return bool
 */
function xoops_module_install_newbb(\XoopsModule $module)
{
    /* Create a test category */
    /** @var Newbb\CategoryHandler $categoryHandler */
    $categoryHandler = Newbb\Helper::getInstance()->getHandler('Category');
    $category        = $categoryHandler->create();
    $category->setVar('cat_title', _MI_NEWBB_INSTALL_CAT_TITLE, true);
    $category->setVar('cat_image', '', true);
    $category->setVar('cat_description', _MI_NEWBB_INSTALL_CAT_DESC, true);
    $category->setVar('cat_url', 'https://xoops.org XOOPS Project', true);
    if (!$cat_id = $categoryHandler->insert($category)) {
        return true;
    }

    /* Create a forum for test */
    /** @var Newbb\ForumHandler $forumHandler */
    $forumHandler = Newbb\Helper::getInstance()->getHandler('Forum');
    $forum        = $forumHandler->create();
    $forum->setVar('forum_name', _MI_NEWBB_INSTALL_FORUM_NAME, true);
    $forum->setVar('forum_desc', _MI_NEWBB_INSTALL_FORUM_DESC, true);
    $forum->setVar('forum_moderator', []);
    $forum->setVar('parent_forum', 0);
    $forum->setVar('cat_id', $cat_id);
    $forum->setVar('attach_maxkb', 100);
    $forum->setVar('attach_ext', 'zip|jpg|gif|png');
    $forum->setVar('hot_threshold', 20);
    $forum_id = $forumHandler->insert($forum);

    /* Set corresponding permissions for the category and the forum */
    $module_id = $module->getVar('mid');
    /** @var XoopsGroupPermHandler $gpermHandler */
    $gpermHandler = xoops_getHandler('groupperm');
    $groups_view  = [XOOPS_GROUP_ADMIN, XOOPS_GROUP_USERS, XOOPS_GROUP_ANONYMOUS];
    $groups_post  = [XOOPS_GROUP_ADMIN, XOOPS_GROUP_USERS];
    // irmtfan bug fix: html and signature permissions, add: pdf and print permissions
    $post_items = [
        'post',
        'reply',
        'edit',
        'delete',
        'addpoll',
        'vote',
        'attach',
        'noapprove',
        'type',
        'html',
        'signature',
        'pdf',
        'print'
    ];
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
    require_once __DIR__ . '/functions.user.php';
    /** @var Newbb\PostHandler $postHandler */
    $postHandler = Newbb\Helper::getInstance()->getHandler('Post');
    /** @var  $forumpost */
    $forumpost = $postHandler->create();
    $forumpost->setVar('poster_ip', \Xmf\IPAddress::fromRequest()->asReadable());
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
