<?php declare(strict_types=1);

/**
 * NewBB, the forum module for XOOPS project
 *
 * @copyright      XOOPS Project (https://xoops.org)
 * @license        GNU GPL 2.0 or later (https://www.gnu.org/licenses/gpl-2.0.html)
 * @author         Taiwen Jiang (phppp or D.J.) <phppp@users.sourceforge.net>
 * @since          4.00
 */
require_once __DIR__ . '/preloads/autoloader.php';

$moduleDirName = basename(__DIR__);
$moduleDirNameUpper = \mb_strtoupper($moduleDirName);

$modversion = [
    'version'             => '5.1.0',
    'module_status'       => 'Beta 7',
    'release_date'        => '2022/10/26',
    'name'                => _MI_NEWBB_NAME,
    'description'         => _MI_NEWBB_DESC,
    'help'                => 'page=help',
    'credits'             => 'NewBB 2 developed by Marko Schmuck (predator) / D.J. (phppp) / Alfred(dhcst)',
    'author'              => 'Marko Schmuck (predator) / D.J. (phppp) / Alfred(dhcst) / (irmtfan) / (Geekwright) / (Mamba) / (Aerograf)',
    'license'             => 'GNU GPL 2.0',
    'license_url'         => 'www.gnu.org/licenses/gpl-2.0.html/',
    'image'               => 'assets/images/logoModule.png',
    'dirname'             => basename(__DIR__),
    'author_realname'     => 'NewBB Dev Team',
    'author_email'        => '',
    'module_website_url'  => 'www.xoops.org/',
    'module_website_name' => 'XOOPS',
    'min_php'             => '7.4',
    'min_xoops'           => '2.5.10',
    'min_admin'           => '1.2',
    'min_db'              => ['mysql' => '5.5'],
    'modicons16'          => 'assets/images/icons/16',
    'modicons32'          => 'assets/images/icons/32',
    'demo_site_url'       => 'https://xoops.org/newbb/',
    'demo_site_name'      => 'XOOPS Project',
    'support_site_url'    => 'https://xoops.org/newbb/',
    'support_site_name'   => 'XOOPS Project',
    'submit_feature'      => 'https://xoops.org/modules/newbb/viewforum.php?forum=30',
    'submit_bug'          => 'https://xoops.org/modules/newbb/viewforum.php?forum=28',
    // ------------------- Mysql -----------------------------
    'sqlfile'             => ['mysql' => 'sql/mysql.sql'],
    // ------------------- Tables ----------------------------
    'tables'              => [
        $moduleDirName . '_' . 'archive',
        $moduleDirName . '_' . 'categories',
        $moduleDirName . '_' . 'votedata',
        $moduleDirName . '_' . 'forums',
        $moduleDirName . '_' . 'posts',
        $moduleDirName . '_' . 'posts_text',
        $moduleDirName . '_' . 'topics',
        $moduleDirName . '_' . 'online',
        $moduleDirName . '_' . 'digest',
        $moduleDirName . '_' . 'report',
        $moduleDirName . '_' . 'attachments',
        $moduleDirName . '_' . 'moderates',
        $moduleDirName . '_' . 'reads_forum',
        $moduleDirName . '_' . 'reads_topic',
        $moduleDirName . '_' . 'type',
        $moduleDirName . '_' . 'type_forum',
        $moduleDirName . '_' . 'stats',
        $moduleDirName . '_' . 'user_stats',
    ],
    // ------------------- Admin Menu -------------------
    'system_menu'         => 1,
    'hasAdmin'            => 1,
    'adminindex'          => 'admin/index.php',
    'adminmenu'           => 'admin/menu.php',
    // ------------------- Main Menu -------------------
    'hasMain'             => 1,
    // ------------------- Search ---------------------------
    'hasSearch'           => 1,
    'search'              => [
        'file' => 'include/search.inc.php',
        'func' => 'newbb_search',
    ],
    // ------------------- Install/Update -------------------
    'onInstall'           => 'include/module.php',
    'onUpdate'            => 'include/module.php',
    //    'onUpdate'            => 'include/onupdate.php',
    //  'onUninstall'         => 'include/onuninstall.php',
];
// ------------------- Help files ------------------- //
$modversion['helpsection'] = [
    ['name' => _MI_NEWBB_OVERVIEW, 'link' => 'page=help'],
    ['name' => _MI_NEWBB_HELP_DISCLAIMER, 'link' => 'page=disclaimer'],
    ['name' => _MI_NEWBB_LICENSE, 'link' => 'page=license'],
    ['name' => _MI_NEWBB_SUPPORT, 'link' => 'page=support'],
    //    array('name' => _MI_NEWBB_HOWTO, 'link' => 'page=__howto'),
    //    array('name' => _MI_NEWBB_REQUIREMENTS, 'link' => 'page=__requirements'),
    //    array('name' => _MI_NEWBB_CREDITS, 'link' => 'page=__credits'),
];
// ------------------- Templates ------------------- //
$modversion['templates'] = [
    ['file' => 'newbb_index_menu.tpl', 'description' => ''],
    ['file' => 'newbb_index.tpl', 'description' => ''],
    ['file' => 'newbb_viewforum_subforum.tpl', 'description' => ''],
    ['file' => 'newbb_viewforum_menu.tpl', 'description' => ''],
    ['file' => 'newbb_viewforum.tpl', 'description' => ''],
    ['file' => 'newbb_viewtopic.tpl', 'description' => ''],
    ['file' => 'newbb_thread.tpl', 'description' => ''],
    ['file' => 'newbb_edit_post.tpl', 'description' => ''],
    ['file' => 'newbb_poll_results.tpl', 'description' => ''],
    ['file' => 'newbb_poll_view.tpl', 'description' => ''],
    ['file' => 'newbb_searchresults.tpl', 'description' => ''],
    ['file' => 'newbb_search.tpl', 'description' => ''],
    ['file' => 'newbb_viewall.tpl', 'description' => ''],
    ['file' => 'newbb_viewpost.tpl', 'description' => ''],
    ['file' => 'newbb_online.tpl', 'description' => ''],
    ['file' => 'newbb_rss.tpl', 'description' => ''],
    ['file' => 'newbb_notification_select.tpl', 'description' => ''],
    ['file' => 'newbb_moderate.tpl', 'description' => ''],
];
// ------------------- Blocks ------------------- //
// options[0] - Citeria valid: time(by default)
// options[1] - NumberToDisplay: any positive integer
// options[2] - TimeDuration: negative for hours, positive for days, for instance, -5 for 5 hours and 5 for 5 days
// options[3] - DisplayMode: 0-full view;1-compact view;2-lite view
// options[4] - Display Navigator: 1 (by default), 0 (No)
// options[5] - SelectedForumIDs: null for all
$modversion['blocks'][1] = [
    'file'        => 'newbb_block.php',
    'name'        => _MI_NEWBB_BLOCK_TOPIC_POST,
    'description' => 'It Will drop (use advance topic renderer block)',
    // irmtfan
    'show_func'   => 'b_newbb_show',
    'options'     => 'time|5|360|0|1|0',
    'edit_func'   => 'b_newbb_edit',
    'template'    => 'newbb_block.tpl',
];
// options[0] - Citeria valid: time(by default), views, replies, digest, sticky
// options[1] - NumberToDisplay: any positive integer
// options[2] - TimeDuration: negative for hours, positive for days, for instance, -5 for 5 hours and 5 for 5 days
// options[3] - DisplayMode: 0-full view;1-compact view;2-lite view
// options[4] - Display Navigator: 1 (by default), 0 (No)
// options[5] - Title Length : 0 - no limit
// options[6] - SelectedForumIDs: null for all
$modversion['blocks'][] = [
    'file'        => 'newbb_block.php',
    'name'        => _MI_NEWBB_BLOCK_TOPIC,
    'description' => 'It Will drop (use advance topic renderer block)',
    // irmtfan
    'show_func'   => 'b_newbb_topic_show',
    'options'     => 'time|5|0|0|1|0|0',
    'edit_func'   => 'b_newbb_topic_edit',
    'template'    => 'newbb_block_topic.tpl',
];
// options[0] - Citeria valid: title(by default), text
// options[1] - NumberToDisplay: any positive integer
// options[2] - TimeDuration: negative for hours, positive for days, for instance, -5 for 5 hours and 5 for 5 days
// options[3] - DisplayMode: 0-full view;1-compact view;2-lite view; Only valid for 'time'
// options[4] - Display Navigator: 1 (by default), 0 (No)
// options[5] - Title/Text Length : 0 - no limit
// options[6] - SelectedForumIDs: null for all
$modversion['blocks'][] = [
    'file'        => 'newbb_block.php',
    'name'        => _MI_NEWBB_BLOCK_POST,
    'description' => 'Shows recent posts in the forums',
    'show_func'   => 'b_newbb_post_show',
    'options'     => 'title|10|0|0|1|0|0',
    'edit_func'   => 'b_newbb_post_edit',
    'template'    => 'newbb_block_post.tpl',
];
// options[0] - Citeria valid: post(by default), topic, digest, sticky
// options[1] - NumberToDisplay: any positive integer
// options[2] - TimeDuration: negative for hours, positive for days, for instance, -5 for 5 hours and 5 for 5 days
// options[3] - DisplayMode: 0-full view;1-compact view;
// options[4] - Display Navigator: 1 (by default), 0 (No)
// options[5] - SelectedForumIDs: null for all
$modversion['blocks'][] = [
    'file'        => 'newbb_block.php',
    'name'        => _MI_NEWBB_BLOCK_AUTHOR,
    'description' => 'Shows authors stats',
    'show_func'   => 'b_newbb_author_show',
    'options'     => 'topic|5|0|0|1|0',
    'edit_func'   => 'b_newbb_author_edit',
    'template'    => 'newbb_block_author.tpl',
];
/*
 * $options:
 *                    $options[0] - number of tags to display
 *                    $options[1] - time duration, in days, 0 for all the time
 *                    $options[2] - max font size (px or %)
 *                    $options[3] - min font size (px or %)
 */
$modversion['blocks'][] = [
    'file'        => 'newbb_block_tag.php',
    'name'        => _MI_NEWBB_BLOCK_TAG_CLOUD,
    'description' => 'Show tag cloud',
    'show_func'   => 'newbb_tag_block_cloud_show',
    'edit_func'   => 'newbb_tag_block_cloud_edit',
    'options'     => '100|0|150|80',
    'template'    => 'newbb_tag_block_cloud.tpl',
];
/*
 * $options:
 *                    $options[0] - number of tags to display
 *                    $options[1] - time duration, in days, 0 for all the time
 *                    $options[2] - sort: a - alphabet; c - count; t - time
 */
$modversion['blocks'][] = [
    'file'        => 'newbb_block_tag.php',
    'name'        => _MI_NEWBB_BLOCK_TAG_TOP,
    'description' => 'Show top tags',
    'show_func'   => 'newbb_tag_block_top_show',
    'edit_func'   => 'newbb_tag_block_top_edit',
    'options'     => '50|0|c',
    'template'    => 'newbb_tag_block_top.tpl',
];
// options[0] - Status in WHERE claus: all(by default), sticky, digest,lock, poll, voted, viewed, replied, read, (UN_) , active, pending, deleted (admin) (It is  multi-select)
// options[1] - Uid in WHERE claus: uid of the topic poster : -1 - all users (by default)
// options[2] - Lastposter in WHERE claus: uid of the lastposter in topic : -1 - all users (by default)
// options[3] - Type in WHERE claus: topic type in the forum : 0 - none (by default)
// options[4] - Sort in ORDER claus: topic, forum, poster, replies, views, lastpost(by default), lastposttime, lastposter, lastpostmsgicon, ratings, votes, publish, digest, sticky, lock, poll, type (if exist), approve(admin mode)
// options[5] - Order in ORDER claus: Descending 0(by default), Ascending 1
// options[6] - NumberToDisplay: any positive integer - 5 by default
// options[7] - TimeDuration: negative for hours, positive for days, for instance, -5 for 5 hours and 5 for 5 days - 360 by default
// options[8] - DisplayMode: all fields in sort PLUS attachment, read, pagenav
// options[9] - Display Navigator: 1 (by default), 0 (No)
// options[10] - Title Length : 0 by default - no limit and show complete title
// options[11] - Post text Length: 0 - dont show post text - 200 by default
// options[12] - SelectedForumIDs: multi-select ngative values for categories and positive values for forums: null for all(by default)
$modversion['blocks'][] = [
    'file'        => 'list_topic.php',
    'name'        => _MI_NEWBB_BLOCK_LIST_TOPIC,
    'description' => 'Shows a list of topics (advance renderer)',
    'show_func'   => 'newbb_list_topic_show',
    'options'     => 'all|-1|-1|0|lastpost|0|5|360|topic,forum,replies,lastpost,lastposttime,lastposter,lastpostmsgicon,publish|1|0|200|0',
    'edit_func'   => 'newbb_list_topic_edit',
    'template'    => 'newbb_block_list_topic.tpl',
];
// Smarty
$modversion['use_smarty'] = 1;
// Configs
$modversion['config']   = [];
$modversion['config'][] = [
    'name'        => 'do_rewrite',
    'title'       => '_MI_NEWBB_DO_REWRITE',
    'description' => '_MI_NEWBB_DO_REWRITE_DESC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 0,
];
$modversion['config'][] = [
    'name'        => 'subforum_display',
    'title'       => '_MI_NEWBB_SUBFORUM_DISPLAY',
    'description' => '_MI_NEWBB_SUBFORUM_DISPLAY_DESC',
    'formtype'    => 'select',
    'valuetype'   => 'text',
    'options'     => [
        _MI_NEWBB_SUBFORUM_EXPAND   => 'expand',
        _MI_NEWBB_SUBFORUM_COLLAPSE => 'collapse',
        _MI_NEWBB_SUBFORUM_HIDDEN   => 'hidden',
    ],
    'default'     => 'collapse',
];
$modversion['config'][] = [
    'name'        => 'post_excerpt',
    'title'       => '_MI_NEWBB_POST_EXCERPT',
    'description' => '_MI_NEWBB_POST_EXCERPT_DESC',
    'formtype'    => 'textbox',
    'valuetype'   => 'int',
    'default'     => 100,
];
$modversion['config'][] = [
    'name'        => 'topics_per_page',
    'title'       => '_MI_NEWBB_TOPICSPERPAGE',
    'description' => '_MI_NEWBB_TOPICSPERPAGE_DESC',
    'formtype'    => 'textbox',
    'valuetype'   => 'int',
    'default'     => 20,
];
$modversion['config'][] = [
    'name'        => 'posts_per_page',
    'title'       => '_MI_NEWBB_POSTSPERPAGE',
    'description' => '_MI_NEWBB_POSTSPERPAGE_DESC',
    'formtype'    => 'textbox',
    'valuetype'   => 'int',
    'default'     => 10,
];
$modversion['config'][] = [
    'name'        => 'pagenav_display',
    'title'       => '_MI_NEWBB_PAGENAV_DISPLAY',
    'description' => '_MI_NEWBB_PAGENAV_DISPLAY_DESC',
    'formtype'    => 'select',
    'valuetype'   => 'text',
    'options'     => [
        _MI_NEWBB_PAGENAV_NUMBER => 'number',
        _MI_NEWBB_PAGENAV_IMAGE  => 'image',
        _MI_NEWBB_PAGENAV_SELECT => 'select',
    ],
    'default'     => 'number',
];
$modversion['config'][] = [
    'name'        => 'cache_enabled',
    'title'       => '_MI_NEWBB_CACHE_ENABLE',
    'description' => '_MI_NEWBB_CACHE_ENABLE_DESC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 0,
];
$modversion['config'][] = [
    'name'        => 'statistik_enabled',
    'title'       => '_MI_NEWBB_STATISTIK_ENABLE',
    'description' => '_MI_NEWBB_STATISTIK_ENABLE_DESC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 1,
];
$modversion['config'][] = [
    'name'        => 'dir_attachments',
    'title'       => '_MI_NEWBB_DIR_ATTACHMENT',
    'description' => '_MI_NEWBB_DIR_ATTACHMENT_DESC',
    'formtype'    => 'textbox',
    'valuetype'   => 'text',
    'default'     => 'uploads/newbb',
];
$modversion['config'][] = [
    'name'        => 'media_allowed',
    'title'       => '_MI_NEWBB_MEDIA_ENABLE',
    'description' => '_MI_NEWBB_MEDIA_ENABLE_DESC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 1,
];
$modversion['config'][] = [
    'name'        => 'path_magick',
    'title'       => '_MI_NEWBB_PATH_MAGICK',
    'description' => '_MI_NEWBB_PATH_MAGICK_DESC',
    'formtype'    => 'textbox',
    'valuetype'   => 'text',
    'default'     => '/usr/bin/X11',
];
$modversion['config'][] = [
    'name'        => 'path_netpbm',
    'title'       => '_MI_NEWBB_PATH_NETPBM',
    'description' => '_MI_NEWBB_PATH_NETPBM_DESC',
    'formtype'    => 'textbox',
    'valuetype'   => 'text',
    'default'     => '/usr/bin',
];
$modversion['config'][] = [
    'name'        => 'image_lib',
    'title'       => '_MI_NEWBB_IMAGELIB',
    'description' => '_MI_NEWBB_IMAGELIB_DESC',
    'formtype'    => 'select',
    'valuetype'   => 'int',
    'default'     => 0,
    'options'     => [
        _MI_NEWBB_AUTO   => 0,
        _MI_NEWBB_MAGICK => 1,
        _MI_NEWBB_NETPBM => 2,
        _MI_NEWBB_GD1    => 3,
        _MI_NEWBB_GD2    => 4,
    ],
];
$modversion['config'][] = [
    'name'        => 'show_userattach',
    'title'       => '_MI_NEWBB_USERATTACH_ENABLE',
    'description' => '_MI_NEWBB_USERATTACH_ENABLE_DESC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 1,
];
$modversion['config'][] = [
    'name'        => 'max_img_width',
    'title'       => '_MI_NEWBB_MAX_IMG_WIDTH',
    'description' => '_MI_NEWBB_MAX_IMG_WIDTH_DESC',
    'formtype'    => 'textbox',
    'valuetype'   => 'int',
    'default'     => 800,
];
$modversion['config'][] = [
    'name'        => 'max_img_height',
    'title'       => '_MI_NEWBB_MAX_IMG_HEIGHT',
    'description' => '_MI_NEWBB_MAX_IMG_HEIGHT_DESC',
    'formtype'    => 'textbox',
    'valuetype'   => 'int',
    'default'     => 640,
];
$modversion['config'][] = [
    'name'        => 'max_image_width',
    'title'       => '_MI_NEWBB_MAX_IMAGE_WIDTH',
    'description' => '_MI_NEWBB_MAX_IMAGE_WIDTH_DESC',
    'formtype'    => 'textbox',
    'valuetype'   => 'int',
    'default'     => 150,
];
$modversion['config'][] = [
    'name'        => 'max_image_height',
    'title'       => '_MI_NEWBB_MAX_IMAGE_HEIGHT',
    'description' => '_MI_NEWBB_MAX_IMAGE_HEIGHT_DESC',
    'formtype'    => 'textbox',
    'valuetype'   => 'int',
    'default'     => 150,
];
$modversion['config'][] = [
    'name'        => 'wol_enabled',
    'title'       => '_MI_NEWBB_WOL_ENABLE',
    'description' => '_MI_NEWBB_WOL_ENABLE_DESC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 1,
];
$modversion['config'][] = [
    'name'        => 'user_level',
    'title'       => '_MI_NEWBB_USERLEVEL',
    'description' => '_MI_NEWBB_USERLEVEL_DESC',
    'formtype'    => 'select',
    'valuetype'   => 'int',
    'default'     => 2,
    'options'     => [
        _MI_NEWBB_NULL    => 0,
        _MI_NEWBB_TEXT    => 1,
        _MI_NEWBB_GRAPHIC => 2,
    ],
];
$modversion['config'][] = [
    'name'        => 'show_realname',
    'title'       => '_MI_NEWBB_SHOW_REALNAME',
    'description' => '_MI_NEWBB_SHOW_REALNAME_DESC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 0,
];
$modversion['config'][] = [
    'name'        => 'groupbar_enabled',
    'title'       => '_MI_NEWBB_GROUPBAR_ENABLE',
    'description' => '_MI_NEWBB_GROUPBAR_ENABLE_DESC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 1,
];
$modversion['config'][] = [
    'name'        => 'rating_enabled',
    'title'       => '_MI_NEWBB_RATING_ENABLE',
    'description' => '_MI_NEWBB_RATING_ENABLE_DESC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 0,
];
$modversion['config'][] = [
    'name'        => 'reportmod_enabled',
    'title'       => '_MI_NEWBB_REPORTMOD_ENABLE',
    'description' => '_MI_NEWBB_REPORTMOD_ENABLE_DESC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 0,
];
$modversion['config'][] = [
    'name'        => 'quickreply_enabled',
    'title'       => '_MI_NEWBB_QUICKREPLY_ENABLE',
    'description' => '_MI_NEWBB_QUICKREPLY_ENABLE_DESC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 1,
];
$modversion['config'][] = [
    'name'        => 'rss_enable',
    'title'       => '_MI_NEWBB_RSS_ENABLE',
    'description' => '_MI_NEWBB_RSS_ENABLE_DESC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 1,
];
$modversion['config'][] = [
    'name'        => 'rss_maxitems',
    'title'       => '_MI_NEWBB_RSS_MAX_ITEMS',
    'description' => '',
    'formtype'    => 'textbox',
    'valuetype'   => 'int',
    'default'     => 10,
];
$modversion['config'][] = [
    'name'        => 'rss_maxdescription',
    'title'       => '_MI_NEWBB_RSS_MAX_DESCRIPTION',
    'description' => '',
    'formtype'    => 'textbox',
    'valuetype'   => 'int',
    'default'     => 0,
];
$modversion['config'][] = [
    'name'        => 'rss_cachetime',
    'title'       => '_MI_NEWBB_RSS_CACHETIME',
    'description' => '_MI_NEWBB_RSS_CACHETIME_DESCRIPTION',
    'formtype'    => 'textbox',
    'valuetype'   => 'int',
    'default'     => 30,
];
// 4.05
$modversion['config'][] = [
    'name'        => 'show_infobox',
    'title'       => '_MI_NEWBB_SHOW_INFOBOX',
    'description' => '_MI_NEWBB_SHOW_INFOBOX_DESC',
    'formtype'    => 'select',
    'valuetype'   => 'int',
    'default'     => 1,
    'options'     => [
        _MI_NEWBB_INFOBOX_NONE   => 0,
        _MI_NEWBB_INFOBOX_HIDDEN => 1,
        _MI_NEWBB_INFOBOX_SHOW   => 2,
    ],
];
$modversion['config'][] = [
    'name'        => 'show_sociallinks',
    'title'       => '_MI_NEWBB_SHOW_SOCIALLINKS',
    'description' => '_MI_NEWBB_SHOW_SOCIALLINKS_DESC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 1,
];
$modversion['config'][] = [
    'name'        => 'show_advertising',
    'title'       => '_MI_NEWBB_ADVERTISING',
    'description' => '_MI_NEWBB_ADVERTISING_DESC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 0,
];
$modversion['config'][] = [
    'name'        => 'show_jump',
    'title'       => '_MI_NEWBB_SHOW_JUMPBOX',
    'description' => '_MI_NEWBB_SHOW_JUMPBOX_DESC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 1,
];
$modversion['config'][] = [
    'name'        => 'show_permissiontable',
    'title'       => '_MI_NEWBB_SHOW_PERMISSIONTABLE',
    'description' => '_MI_NEWBB_SHOW_PERMISSIONTABLE_DESC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 1,
];
$modversion['config'][] = [
    'name'        => 'email_digest',
    'title'       => '_MI_NEWBB_EMAIL_DIGEST',
    'description' => '_MI_NEWBB_EMAIL_DIGEST_DESC',
    'formtype'    => 'select',
    'valuetype'   => 'int',
    'default'     => 0,
    'options'     => [
        _MI_NEWBB_EMAIL_NONE   => 0,
        _MI_NEWBB_EMAIL_DAILY  => 1,
        _MI_NEWBB_EMAIL_WEEKLY => 2,
    ],
];
$modversion['config'][] = [
    'name'        => 'show_ip',
    'title'       => '_MI_NEWBB_SHOW_IP',
    'description' => '_MI_NEWBB_SHOW_IP_DESC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 1,
];
$modversion['config'][] = [
    'name'        => 'enable_karma',
    'title'       => '_MI_NEWBB_ENABLE_KARMA',
    'description' => '_MI_NEWBB_ENABLE_KARMA_DESC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 1,
];
$modversion['config'][] = [
    'name'        => 'karma_options',
    'title'       => '_MI_NEWBB_KARMA_OPTIONS',
    'description' => '_MI_NEWBB_KARMA_OPTIONS_DESC',
    'formtype'    => 'textbox',
    'valuetype'   => 'text',
    'default'     => '0, 10, 50, 100, 500, 1000, 5000, 10000',
];
// irmtfan - add 365 = one year
$modversion['config'][] = [
    'name'        => 'since_options',
    'title'       => '_MI_NEWBB_SINCE_OPTIONS',
    'description' => '_MI_NEWBB_SINCE_OPTIONS_DESC',
    'formtype'    => 'textbox',
    'valuetype'   => 'text',
    'default'     => '-1, -2, -6, -12, 0, 1, 2, 5, 10, 20, 30, 60, 100, 365',
];
$modversion['config'][] = [
    'name'        => 'since_default',
    'title'       => '_MI_NEWBB_SINCE_DEFAULT',
    'description' => '_MI_NEWBB_SINCE_DEFAULT_DESC',
    'formtype'    => 'textbox',
    'valuetype'   => 'int',
    'default'     => 0,
];
$modversion['config'][] = [
    'name'        => 'allow_user_anonymous',
    'title'       => '_MI_NEWBB_USER_ANONYMOUS',
    'description' => '_MI_NEWBB_USER_ANONYMOUS_DESC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 0,
];
$modversion['config'][] = [
    'name'        => 'anonymous_prefix',
    'title'       => '_MI_NEWBB_ANONYMOUS_PRE',
    'description' => '_MI_NEWBB_ANONYMOUS_PRE_DESC',
    'formtype'    => 'textbox',
    'valuetype'   => 'text',
    'default'     => $GLOBALS['xoopsConfig']['anonymous'] . '-',
];
$modversion['config'][] = [
    'name'        => 'allow_require_reply',
    'title'       => '_MI_NEWBB_REQUIRE_REPLY',
    'description' => '_MI_NEWBB_REQUIRE_REPLY_DESC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 0,
];
$modversion['config'][] = [
    'name'        => 'edit_timelimit',
    'title'       => '_MI_NEWBB_EDIT_TIMELIMIT',
    'description' => '_MI_NEWBB_EDIT_TIMELIMIT_DESC',
    'formtype'    => 'textbox',
    'valuetype'   => 'int',
    'default'     => 60,
];
$modversion['config'][] = [
    'name'        => 'recordedit_timelimit',
    'title'       => '_MI_RECORDEDIT_TIMELIMIT',
    'description' => '_MI_RECORDEDIT_TIMELIMIT_DESC',
    'formtype'    => 'textbox',
    'valuetype'   => 'int',
    'default'     => 15,
];
$modversion['config'][] = [
    'name'        => 'delete_timelimit',
    'title'       => '_MI_NEWBB_DELETE_TIMELIMIT',
    'description' => '_MI_NEWBB_DELETE_TIMELIMIT_DESC',
    'formtype'    => 'textbox',
    'valuetype'   => 'int',
    'default'     => 60,
];
$modversion['config'][] = [
    'name'        => 'post_timelimit',
    'title'       => '_MI_NEWBB_POST_TIMELIMIT',
    'description' => '_MI_NEWBB_POST_TIMELIMIT_DESC',
    'formtype'    => 'textbox',
    'valuetype'   => 'int',
    'default'     => 30,
];
$modversion['config'][] = [
    'name'        => 'enable_permcheck',
    'title'       => '_MI_NEWBB_PERMCHECK_ONDISPLAY',
    'description' => '_MI_NEWBB_PERMCHECK_ONDISPLAY_DESC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 1,
];
$modversion['config'][] = [
    'name'        => 'enable_usermoderate',
    'title'       => '_MI_NEWBB_USERMODERATE',
    'description' => '_MI_NEWBB_USERMODERATE_DESC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 0,
];
$modversion['config'][] = [
    'name'        => 'disc_show',
    'title'       => '_MI_NEWBB_SHOW_DIS',
    'description' => '',
    'formtype'    => 'select',
    'valuetype'   => 'int',
    'default'     => 0,
    'options'     => [
        _MI_NEWBB_NONE    => 0,
        _MI_NEWBB_POST    => 1,
        _MI_NEWBB_REPLY   => 2,
        _MI_NEWBB_OP_BOTH => 3,
    ],
];
$modversion['config'][] = [
    'name'        => 'disclaimer',
    'title'       => '_MI_NEWBB_DISCLAIMER',
    'description' => '_MI_NEWBB_DISCLAIMER_DESC',
    'formtype'    => 'textarea',
    'valuetype'   => 'text',
    'default'     => _MI_NEWBB_DISCLAIMER_TEXT,
];
$modversion['config'][] = [
    'name'        => 'welcome_forum',
    'title'       => '_MI_NEWBB_WELCOMEFORUM',
    'description' => '_MI_NEWBB_WELCOMEFORUM_DESC',
    'formtype'    => 'textbox',
    'valuetype'   => 'int',
    'default'     => 0,
    //    'options'     => $forum_options
];
$modversion['config'][] = [
    'name'        => 'welcome_forum_message',
    'title'       => '_MI_NEWBB_WELCOMEFORUM_MESSAGE',
    'description' => '_MI_NEWBB_WELCOMEFORUM_MESSAGE_DESC',
    'formtype'    => 'textarea',
    'valuetype'   => 'text',
    'default'     => _MI_NEWBB_WELCOMEFORUM_DESC_MESSAGE,
];
$modversion['config'][] = [
    'name'        => 'poll_module',
    'title'       => '_MI_NEWBB_POLL_MODULE',
    'description' => '_MI_NEWBB_POLL_MODULE_DESC',
    'valuetype'   => 'text',
    'formtype'    => 'textbox',
    'default'     => 'xoopspoll',
];

$modversion['config'][] = [
    'name'        => 'forum_desc_length',
    'title'       => '_MI_NEWBB_FORUM_DESC_LENGTH',
    'description' => '_MI_NEWBB_FORUM_DESC_LENGTH_DESC',
    'formtype'    => 'textbox',
    'valuetype'   => 'int',
    'default'     => 440,
];
/**
 * Make Sample button visible?
 */
$modversion['config'][] = [
    'name'        => 'displaySampleButton',
    'title'       => 'CO_' . $moduleDirNameUpper . '_' . 'SHOW_SAMPLE_BUTTON',
    'description' => 'CO_' . $moduleDirNameUpper . '_' . 'SHOW_SAMPLE_BUTTON_DESC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 1,
];

/**
 * Show Developer Tools?
 */
$modversion['config'][] = [
    'name'        => 'displayDeveloperTools',
    'title'       => '_MI_NEWBB_SHOW_DEV_TOOLS',
    'description' => '_MI_NEWBB_SHOW_DEV_TOOLS_DESC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 0,
];

$modversion['config'][] = [
    'name'        => 'facebookstyle',
    'title'       => '_MI_NEWBB_FACEBOOK_STYLE_RATING',
    'description' => '_MI_NEWBB_FACEBOOK_STYLE_RATING_DESC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 0,
];

// ------------------- Notification ---------------------- //
$modversion['notification']                = [];
$modversion['hasNotification']             = 1;
$modversion['notification']['lookup_file'] = 'include/notification.inc.php';
$modversion['notification']['lookup_func'] = 'newbb_notify_iteminfo';
$modversion['notification']['category'][]  = [
    'name'           => 'thread',
    'title'          => _MI_NEWBB_THREAD_NOTIFY,
    'description'    => _MI_NEWBB_THREAD_NOTIFYDSC,
    'subscribe_from' => 'viewtopic.php',
    'item_name'      => 'topic_id',
    'allow_bookmark' => 1,
];
$modversion['notification']['category'][]  = [
    'name'           => 'forum',
    'title'          => _MI_NEWBB_FORUM_NOTIFY,
    'description'    => _MI_NEWBB_FORUM_NOTIFYDSC,
    'subscribe_from' => 'viewforum.php',
    'item_name'      => 'forum',
    'allow_bookmark' => 1,
];
$modversion['notification']['category'][]  = [
    'name'           => 'global',
    'title'          => _MI_NEWBB_GLOBAL_NOTIFY,
    'description'    => _MI_NEWBB_GLOBAL_NOTIFYDSC,
    'subscribe_from' => 'index.php',
];
$modversion['notification']['event'][]     = [
    'name'          => 'new_post',
    'category'      => 'thread',
    'title'         => _MI_NEWBB_THREAD_NEWPOST_NOTIFY,
    'caption'       => _MI_NEWBB_THREAD_NEWPOST_NOTIFYCAP,
    'description'   => _MI_NEWBB_THREAD_NEWPOST_NOTIFYDSC,
    'mail_template' => 'thread_newpost_notify',
    'mail_subject'  => _MI_NEWBB_THREAD_NEWPOST_NOTIFYSBJ,
];
$modversion['notification']['event'][]     = [
    'name'          => 'new_thread',
    'category'      => 'forum',
    'title'         => _MI_NEWBB_FORUM_NEWTHREAD_NOTIFY,
    'caption'       => _MI_NEWBB_FORUM_NEWTHREAD_NOTIFYCAP,
    'description'   => _MI_NEWBB_FORUM_NEWTHREAD_NOTIFYDSC,
    'mail_template' => 'forum_newthread_notify',
    'mail_subject'  => _MI_NEWBB_FORUM_NEWTHREAD_NOTIFYSBJ,
];
$modversion['notification']['event'][]     = [
    'name'          => 'new_forum',
    'category'      => 'global',
    'title'         => _MI_NEWBB_GLOBAL_NEWFORUM_NOTIFY,
    'caption'       => _MI_NEWBB_GLOBAL_NEWFORUM_NOTIFYCAP,
    'description'   => _MI_NEWBB_GLOBAL_NEWFORUM_NOTIFYDSC,
    'mail_template' => 'global_newforum_notify',
    'mail_subject'  => _MI_NEWBB_GLOBAL_NEWFORUM_NOTIFYSBJ,
];
$modversion['notification']['event'][]     = [
    'name'          => 'new_post',
    'category'      => 'global',
    'title'         => _MI_NEWBB_GLOBAL_NEWPOST_NOTIFY,
    'caption'       => _MI_NEWBB_GLOBAL_NEWPOST_NOTIFYCAP,
    'description'   => _MI_NEWBB_GLOBAL_NEWPOST_NOTIFYDSC,
    'mail_template' => 'global_newpost_notify',
    'mail_subject'  => _MI_NEWBB_GLOBAL_NEWPOST_NOTIFYSBJ,
];
$modversion['notification']['event'][]     = [
    'name'          => 'new_post',
    'category'      => 'forum',
    'title'         => _MI_NEWBB_FORUM_NEWPOST_NOTIFY,
    'caption'       => _MI_NEWBB_FORUM_NEWPOST_NOTIFYCAP,
    'description'   => _MI_NEWBB_FORUM_NEWPOST_NOTIFYDSC,
    'mail_template' => 'forum_newpost_notify',
    'mail_subject'  => _MI_NEWBB_FORUM_NEWPOST_NOTIFYSBJ,
];
$modversion['notification']['event'][]     = [
    'name'          => 'new_fullpost',
    'category'      => 'global',
    'admin_only'    => 1,
    'title'         => _MI_NEWBB_GLOBAL_NEWFULLPOST_NOTIFY,
    'caption'       => _MI_NEWBB_GLOBAL_NEWFULLPOST_NOTIFYCAP,
    'description'   => _MI_NEWBB_GLOBAL_NEWFULLPOST_NOTIFYDSC,
    'mail_template' => 'global_newfullpost_notify',
    'mail_subject'  => _MI_NEWBB_GLOBAL_NEWFULLPOST_NOTIFYSBJ,
];
$modversion['notification']['event'][]     = [
    'name'          => 'digest',
    'category'      => 'global',
    'title'         => _MI_NEWBB_GLOBAL_DIGEST_NOTIFY,
    'caption'       => _MI_NEWBB_GLOBAL_DIGEST_NOTIFYCAP,
    'description'   => _MI_NEWBB_GLOBAL_DIGEST_NOTIFYDSC,
    'mail_template' => 'global_digest_notify',
    'mail_subject'  => _MI_NEWBB_GLOBAL_DIGEST_NOTIFYSBJ,
];
$modversion['notification']['event'][]     = [
    'name'          => 'new_fullpost',
    'category'      => 'forum',
    'admin_only'    => 1,
    'title'         => _MI_NEWBB_GLOBAL_NEWFULLPOST_NOTIFY,
    'caption'       => _MI_NEWBB_GLOBAL_NEWFULLPOST_NOTIFYCAP,
    'description'   => _MI_NEWBB_GLOBAL_NEWFULLPOST_NOTIFYDSC,
    'mail_template' => 'global_newfullpost_notify',
    'mail_subject'  => _MI_NEWBB_GLOBAL_NEWFULLPOST_NOTIFYSBJ,
];
