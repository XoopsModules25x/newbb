<?php declare(strict_types=1);
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

use Xmf\IPAddress;
use Xmf\Module\Helper\Cache;
use XoopsModules\Newbb\{Common\Configurator,
    Common\Migrate,
    CategoryHandler,
    ForumHandler,
    Helper,
    PostHandler
};

/** @var PostHandler $postHandler */
/** @var ForumHandler $forumHandler */
/** @var CategoryHandler $categoryHandler */
if (defined('XOOPS_MODULE_NEWBB_FUCTIONS')) {
    exit();
}
define('XOOPS_MODULE_NEWBB_FUCTIONS', 1);

require_once $GLOBALS['xoops']->path('modules/newbb/include/functions.ini.php');

/**
 * @param XoopsModule $module
 * @param null        $oldversion
 * @return bool
 */
function xoops_module_update_newbb(\XoopsModule $module, $oldversion = null)
{
    $cacheHelper = new Cache('newbb');
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
    array_map('\unlink', glob(dirname(__DIR__) . '/docs/changelog-rev*.txt', GLOB_NOSORT));

    if (!empty($newbbConfig['syncOnUpdate'])) {
        require_once \dirname(__DIR__) . '/include/functions.recon.php';
        newbbSynchronization();
    }

    return true;
}

/**
 * @param XoopsModule $module
 * @return bool
 */
function xoops_module_pre_update_newbb(\XoopsModule $module)
{
    //    XoopsLoad::load('migrate', 'newbb');
    $configurator = new Configurator();

    $migrator = new Migrate($configurator);
    $migrator->synchronizeSchema();

    return true;
}

/**
 * @param XoopsModule $module
 * @return bool
 */
function xoops_module_pre_install_newbb(\XoopsModule $module)
{
    $mod_tables = &$module->getInfo('tables');
    foreach ($mod_tables as $table) {
        $GLOBALS['xoopsDB']->queryF('DROP TABLE IF EXISTS ' . $GLOBALS['xoopsDB']->prefix($table) . ';');
    }

    return true;
}

/**
 * @param XoopsModule $module
 * @return bool
 */
function xoops_module_install_newbb(\XoopsModule $module)
{
    /* Create a test category */
    $categoryHandler = Helper::getInstance()->getHandler('Category');
    $category        = $categoryHandler->create();
    $category->setVar('cat_title', _MI_NEWBB_INSTALL_CAT_TITLE, true);
    $category->setVar('cat_image', '', true);
    $category->setVar('cat_description', _MI_NEWBB_INSTALL_CAT_DESC, true);
    $category->setVar('cat_url', 'https://xoops.org XOOPS Project', true);
    if (!$cat_id = $categoryHandler->insert($category)) {
        return true;
    }

    /* Create a forum for test */
    $forumHandler = Helper::getInstance()->getHandler('Forum');
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
    /** @var \XoopsGroupPermHandler $grouppermHandler */
    $grouppermHandler = xoops_getHandler('groupperm');
    $groups_view      = [XOOPS_GROUP_ADMIN, XOOPS_GROUP_USERS, XOOPS_GROUP_ANONYMOUS];
    $groups_post      = [XOOPS_GROUP_ADMIN, XOOPS_GROUP_USERS];
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
        'print',
    ];
    foreach ($groups_view as $group_id) {
        $grouppermHandler->addRight('category_access', $cat_id, $group_id, $module_id);
        $grouppermHandler->addRight('forum_access', $forum_id, $group_id, $module_id);
        $grouppermHandler->addRight('forum_view', $forum_id, $group_id, $module_id);
    }
    foreach ($groups_post as $group_id) {
        foreach ($post_items as $item) {
            $grouppermHandler->addRight('forum_' . $item, $forum_id, $group_id, $module_id);
        }
    }

    /* Create a test post */
    require_once __DIR__ . '/functions.user.php';
    $postHandler = Helper::getInstance()->getHandler('Post');
    /** @var $forumpost */
    $forumpost = $postHandler->create();
    $forumpost->setVar('poster_ip', IPAddress::fromRequest()->asReadable());
    $forumpost->setVar('uid', $GLOBALS['xoopsUser']->getVar('uid'));
    $forumpost->setVar('approved', 1);
    $forumpost->setVar('forum_id', $forum_id);
    $forumpost->setVar('subject', _MI_NEWBB_INSTALL_POST_SUBJECT, true);
    $forumpost->setVar('dohtml', 0);
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
