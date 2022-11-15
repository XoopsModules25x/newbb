<?php declare(strict_types=1);
/*
 You may not change or alter any portion of this comment or credits
 of supporting developers from this source code or any supporting source code
 which is considered copyrighted (c) material of the original comment or credit authors.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
*/

/**
 * animal module for xoops
 *
 * @copyright       XOOPS Project (https://xoops.org)
 * @license         GNU GPL 2.0 or later (https://www.gnu.org/licenses/gpl-2.0.html)
 * @since           1.03
 * @author          XOOPS Development Team - ( https://xoops.org )
 */

use Xmf\Module\Admin;

$moduleDirName = \basename(\dirname(__DIR__));

/** @return object */
$moduleDirName      = \basename(\dirname(__DIR__));
$moduleDirNameUpper = \mb_strtoupper($moduleDirName);

return (object)[
    'name'           => $moduleDirNameUpper . ' Module Configurator',
    'paths'          => [
        'dirname'    => $moduleDirName,
        'admin'      => XOOPS_ROOT_PATH . '/modules/' . $moduleDirName . '/admin',
        'modPath'    => XOOPS_ROOT_PATH . '/modules/' . $moduleDirName,
        'modUrl'     => XOOPS_URL . '/modules/' . $moduleDirName,
        'uploadPath' => XOOPS_UPLOAD_PATH . '/' . $moduleDirName,
        'uploadUrl'  => XOOPS_UPLOAD_URL . '/' . $moduleDirName,
    ],
    'uploadFolders'  => [
        XOOPS_UPLOAD_PATH . '/' . $moduleDirName,
        XOOPS_UPLOAD_PATH . '/' . $moduleDirName . '/thumbs',

        //XOOPS_UPLOAD_PATH . '/flags'
    ],
    'copyBlankFiles' => [
        XOOPS_UPLOAD_PATH . '/' . $moduleDirName,
        XOOPS_UPLOAD_PATH . '/' . $moduleDirName . '/thumbs',
        //XOOPS_UPLOAD_PATH . '/flags'
    ],

    'copyTestFolders' => [
        //[
        //    constant($moduleDirNameUpper . '_PATH') . '/testdata/images',
        //    XOOPS_UPLOAD_PATH . '/' . $moduleDirName . '/images',
        //]
    ],

    'templateFolders' => [
        '/templates/',
        '/templates/blocks/',
        '/templates/admin/',
    ],
    'oldFiles'        => [
        '/class/request.php',
        '/class/registry.php',
        '/class/utilities.php',
        '/class/util.php',
        // '/include/constants.php',
        // '/include/functions.php',
        '/ajaxrating.txt',
    ],
    'oldFolders'      => [
        '/images',
        '/css',
        '/js',
        '/tcpdf',
        '/images',
    ],
    'renameTables'    => [
        'bb_archive'     => 'newbb_archive',
        'bb_attachments' => 'newbb_attachments',
        'bb_categories'  => 'newbb_categories',
        'bb_digest'      => 'newbb_digest',
        'bb_forums'      => 'newbb_forums',
        'bb_moderates'   => 'newbb_moderates',
        'bb_online'      => 'newbb_online',
        'bb_posts'       => 'newbb_posts',
        'bb_posts_text'  => 'newbb_posts_text',
        'bb_reads_forum' => 'newbb_reads_forum',
        'bb_reads_topic' => 'newbb_reads_topic',
        'bb_report'      => 'newbb_report',
        'bb_stats'       => 'newbb_stats',
        'bb_topics'      => 'newbb_topics',
        'bb_type'        => 'newbb_type',
        'bb_type_forum'  => 'newbb_type_forum',
        'bb_user_stats'  => 'newbb_user_stats',
        'bb_votedata'    => 'newbb_votedata',
    ],
    'moduleStats'     => [
        //            'totalcategories' => $helper->getHandler('Category')->getCategoriesCount(-1),
        //            'totalitems'      => $helper->getHandler('Item')->getItemsCount(),
        //            'totalsubmitted'  => $helper->getHandler('Item')->getItemsCount(-1, [Constants::PUBLISHER_STATUS_SUBMITTED]),
    ],
    'modCopyright'    => "<a href='https://xoops.org' title='XOOPS Project' target='_blank'>
                     <img src='" . Admin::iconUrl('xoopsmicrobutton.gif') . "' alt='XOOPS Project'></a>",
];
