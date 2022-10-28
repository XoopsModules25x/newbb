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

/**
 * @copyright    XOOPS Project (https://xoops.org)/
 * @license      GNU GPL 2.0 or later (https://www.gnu.org/licenses/gpl-2.0.html)
 * @author       Kazumi Ono (AKA onokazu) http://www.myweb.ne.jp/, https://xoops.org/, http://jp.xoops.org/
 * @author       XOOPS Development Team
 */

use Xmf\Module\Admin;
use XoopsModules\Newbb\Helper;

$moduleDirName      = \basename(\dirname(__DIR__));
$moduleDirNameUpper = \mb_strtoupper($moduleDirName);

$helper = Helper::getInstance();
$helper->loadLanguage('common');
$helper->loadLanguage('feedback');

$pathIcon32    = Admin::menuIconPath('');
$pathModIcon32 = XOOPS_URL . '/modules/' . $moduleDirName . '/assets/images/icons/32/';
if (is_object($helper->getModule()) && false !== $helper->getModule()->getInfo('modicons32')) {
    $pathModIcon32 = $helper->url($helper->getModule()->getInfo('modicons32'));
}

$adminmenu[] = [
    'title' => _MI_NEWBB_ADMENU_INDEX,
    'link'  => 'admin/index.php',
    'icon'  => $pathIcon32 . 'home.png',
];

$adminmenu[] = [
    'title' => _MI_NEWBB_ADMENU_CATEGORY,
    'link'  => 'admin/admin_cat_manager.php',
    'icon'  => $pathIcon32 . 'category.png',
];

$adminmenu[] = [
    'title' => _MI_NEWBB_ADMENU_FORUM,
    'link'  => 'admin/admin_forum_manager.php',
    'icon'  => $pathIcon32 . 'forums.png',
];

$adminmenu[] = [
    'title' => _MI_NEWBB_ADMENU_PERMISSION,
    'link'  => 'admin/admin_permissions.php',
    'icon'  => $pathIcon32 . 'permissions.png',
];

$adminmenu[] = [
    'title' => _MI_NEWBB_ADMENU_ORDER,
    'link'  => 'admin/admin_forum_reorder.php',
    'icon'  => $pathIcon32 . 'compfile.png',
];

$adminmenu[] = [
    'title' => _MI_NEWBB_ADMENU_PRUNE,
    'link'  => 'admin/admin_forum_prune.php',
    'icon'  => $pathIcon32 . 'update.png',
];

$adminmenu[] = [
    'title' => _MI_NEWBB_ADMENU_REPORT,
    'link'  => 'admin/admin_report.php',
    'icon'  => $pathIcon32 . 'content.png',
];

$adminmenu[] = [
    'title' => _MI_NEWBB_ADMENU_DIGEST,
    'link'  => 'admin/admin_digest.php',
    'icon'  => $pathIcon32 . 'digest.png',
];

$adminmenu[] = [
    'title' => _MI_NEWBB_ADMENU_VOTE,
    'link'  => 'admin/admin_votedata.php',
    'icon'  => $pathIcon32 . 'button_ok.png',
];

$adminmenu[] = [
    'title' => _MI_NEWBB_ADMENU_TYPE,
    'link'  => 'admin/admin_type_manager.php',
    'icon'  => $pathIcon32 . 'type.png',
];

$adminmenu[] = [
    'title' => _MI_NEWBB_ADMENU_GROUPMOD,
    'link'  => 'admin/admin_groupmod.php',
    'icon'  => $pathIcon32 . 'groupmod.png',
];

$adminmenu[] = [
    'title' => _MI_NEWBB_ADMENU_SYNC,
    'link'  => 'admin/admin_synchronization.php',
    'icon'  => $pathIcon32 . 'synchronized.png',
];

// Blocks Admin
$adminmenu[] = [
    'title' => constant('CO_' . $moduleDirNameUpper . '_' . 'BLOCKS'),
    'link' => 'admin/blocksadmin.php',
    'icon' => $pathIcon32 . '/block.png',
];

if (is_object($helper->getModule()) && $helper->getConfig('displayDeveloperTools')) {
    $adminmenu[] = [
        'title' => _MI_NEWBB_ADMENU_MIGRATE,
        'link'  => 'admin/migrate.php',
        'icon'  => $pathIcon32 . 'database_go.png',
    ];
}
$adminmenu[] = [
    'title' => _MI_NEWBB_ADMENU_ABOUT,
    'link'  => 'admin/about.php',
    'icon'  => $pathIcon32 . 'about.png',
];
