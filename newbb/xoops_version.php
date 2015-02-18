<?php
/**
 * CBB 4.0, or newbb, the forum module for XOOPS project
 *
 * @copyright	The XOOPS Project http://xoops.sf.net
 * @license		http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author		Taiwen Jiang (phppp or D.J.) <phppp@users.sourceforge.net>
 * @since		4.00
 * @version		$Id $
 * @package		module::newbb
 */

$modversion['name'] 				= _MI_NEWBB_NAME;
$modversion['version'] 				= 4.33;
$modversion['description'] 			= _MI_NEWBB_DESC;
$modversion['credits'] 				= "NewBB 2 developed by Marko Schmuck (predator) / D.J. (phppp) / Alfred(dhcst)";
$modversion['author'] 				= "Marko Schmuck (predator) / D.J. (phppp) / Alfred(dhcst) / xoops.org (irmtfan)";
$modversion['license']     			= 'GNU GPL 2.0';
$modversion['license_url'] 			= "www.gnu.org/licenses/gpl-2.0.html/";
$modversion['image'] 				= "assets/images/xoopsbb_slogo.png";
$modversion['dirname'] 				= "newbb";

$modversion['author_realname'] 		= "NewBB Dev Team";
$modversion['author_email'] 		= "";
$modversion['status_version'] 		= "4.33";

//about
$modversion["module_status"] 		= "RC8";
$modversion['release_date']     	= '2015/02/18';
$modversion["module_website_url"] 	= "www.xoops.org/";
$modversion["module_website_name"] 	= "XOOPS";
$modversion['min_php']				= "5.3.7";
$modversion['min_xoops']			= "2.5.7";
$modversion['min_admin']			= "1.1";
$modversion['min_db']				= array('mysql'=>'5.0', 'mysqli'=>'5.0');
$modversion['system_menu'] 			= 1;

$modversion['dirmoduleadmin'] 		= 'Frameworks/moduleclasses';
$modversion['icons16'] 				= 'Frameworks/moduleclasses/icons/16';
$modversion['icons32'] 				= 'Frameworks/moduleclasses/icons/32';

$modversion['warning'] 				= "Only For XOOPS >= 2.5.0 ";

$modversion['demo_site_url'] 		= "http://www.xoops.org/newbb/";
$modversion['demo_site_name'] 		= "XOOPS Project";
$modversion['support_site_url'] 	= "http://www.xoops.org/newbb/";
$modversion['support_site_name'] 	= "XOOPS Project";
$modversion['submit_feature'] 		= "http://xoops.org/modules/newbb/viewforum.php?forum=30";
$modversion['submit_bug'] 			= "http://xoops.org/modules/newbb/viewforum.php?forum=28";

include_once XOOPS_ROOT_PATH."/Frameworks/art/functions.ini.php";
// Is performing module install/update?
$isModuleAction = mod_isModuleAction($modversion['dirname']);

// Sql file
$modversion['sqlfile']['mysql'] = "sql/mysql.sql";

// Tables created by sql file (without prefix!)
$modversion['tables'] = array(
    "bb_archive",
    "bb_categories",
    "bb_votedata",
    "bb_forums",
    "bb_posts",
    "bb_posts_text",
    "bb_topics",
    "bb_online",
    "bb_digest",
    "bb_report",
    "bb_attachments", // reserved table for next version
    "bb_moderates", // For suspension
    "bb_reads_forum",
    "bb_reads_topic",
    "bb_type",
    "bb_type_forum",
    "bb_stats",
    "bb_user_stats",
);

// Admin things
$modversion['hasAdmin'] = 1;
$modversion['adminindex'] = "admin/index.php";
$modversion['adminmenu'] = "admin/menu.php";

// Menu
$modversion['hasMain'] = 1;

//install
$modversion['onInstall'] = 'include/module.php';

//update things
$modversion['onUpdate'] = 'include/module.php';

// Templates
$modversion['templates'] = array(

    array('file' => 'newbb_index_menu.tpl',	    'description' => ''),
    array('file' => 'newbb_index.tpl', 			'description' => ''),

    array('file' => 'newbb_viewforum_subforum.tpl','description' => ''),
    array('file' => 'newbb_viewforum_menu.tpl',	'description' => ''),
    array('file' => 'newbb_viewforum.tpl',			'description' => ''),

    array('file' => 'newbb_viewtopic.tpl',	        'description' => ''),
    array('file' => 'newbb_thread.tpl',			'description' => ''),
    array('file' => 'newbb_edit_post.tpl',			'description' => ''),
    array('file' => 'newbb_poll_results.tpl',		'description' => ''),
    array('file' => 'newbb_poll_view.tpl',			'description' => ''),
    array('file' => 'newbb_searchresults.tpl',		'description' => ''),
    array('file' => 'newbb_search.tpl',			'description' => ''),

    array('file' => 'newbb_viewall.tpl',			'description' => ''),
    array('file' => 'newbb_viewpost.tpl',			'description' => ''),
    array('file' => 'newbb_online.tpl',			'description' => ''),
    array('file' => 'newbb_rss.tpl',				'description' => ''),

    array('file' => 'newbb_notification_select.tpl','description' => ''),
    );

// Blocks

// options[0] - Citeria valid: time(by default)
// options[1] - NumberToDisplay: any positive integer
// options[2] - TimeDuration: negative for hours, positive for days, for instance, -5 for 5 hours and 5 for 5 days
// options[3] - DisplayMode: 0-full view;1-compact view;2-lite view
// options[4] - Display Navigator: 1 (by default), 0 (No)
// options[5] - SelectedForumIDs: null for all

$modversion['blocks'][1] = array(
    'file'			=> "newbb_block.php",
    'name'			=> _MI_NEWBB_BLOCK_TOPIC_POST,
    'description'	=> "It Will drop (use advance topic renderer block)", // irmtfan
    'show_func'		=> "b_newbb_show",
    'options'		=> "time|5|360|0|1|0",
    'edit_func'		=> "b_newbb_edit",
    'template'		=> 'newbb_block.tpl');

// options[0] - Citeria valid: time(by default), views, replies, digest, sticky
// options[1] - NumberToDisplay: any positive integer
// options[2] - TimeDuration: negative for hours, positive for days, for instance, -5 for 5 hours and 5 for 5 days
// options[3] - DisplayMode: 0-full view;1-compact view;2-lite view
// options[4] - Display Navigator: 1 (by default), 0 (No)
// options[5] - Title Length : 0 - no limit
// options[6] - SelectedForumIDs: null for all

$modversion['blocks'][] = array(
    'file' 			=> "newbb_block.php",
    'name'			=> _MI_NEWBB_BLOCK_TOPIC,
    'description'	=> "It Will drop (use advance topic renderer block)", // irmtfan
    'show_func'		=> "b_newbb_topic_show",
    'options'		=> "time|5|0|0|1|0|0",
    'edit_func'		=> "b_newbb_topic_edit",
    'template'		=> 'newbb_block_topic.tpl');

// options[0] - Citeria valid: title(by default), text
// options[1] - NumberToDisplay: any positive integer
// options[2] - TimeDuration: negative for hours, positive for days, for instance, -5 for 5 hours and 5 for 5 days
// options[3] - DisplayMode: 0-full view;1-compact view;2-lite view; Only valid for "time"
// options[4] - Display Navigator: 1 (by default), 0 (No)
// options[5] - Title/Text Length : 0 - no limit
// options[6] - SelectedForumIDs: null for all

$modversion['blocks'][] = array(
    'file'			=> "newbb_block.php",
    'name'			=> _MI_NEWBB_BLOCK_POST,
    'description'	=> "Shows recent posts in the forums",
    'show_func'		=> "b_newbb_post_show",
    'options'		=> "title|10|0|0|1|0|0",
    'edit_func'		=> "b_newbb_post_edit",
    'template'		=> 'newbb_block_post.tpl');

// options[0] - Citeria valid: post(by default), topic, digest, sticky
// options[1] - NumberToDisplay: any positive integer
// options[2] - TimeDuration: negative for hours, positive for days, for instance, -5 for 5 hours and 5 for 5 days
// options[3] - DisplayMode: 0-full view;1-compact view;
// options[4] - Display Navigator: 1 (by default), 0 (No)
// options[5] - SelectedForumIDs: null for all

$modversion['blocks'][] = array(
    'file'			=> "newbb_block.php",
    'name'			=> _MI_NEWBB_BLOCK_AUTHOR,
    'description'	=> "Shows authors stats",
    'show_func'		=> "b_newbb_author_show",
    'options'		=> "topic|5|0|0|1|0",
    'edit_func'		=> "b_newbb_author_edit",
    'template'		=> 'newbb_block_author.tpl');

/*
 * $options:
 *					$options[0] - number of tags to display
 *					$options[1] - time duration, in days, 0 for all the time
 *					$options[2] - max font size (px or %)
 *					$options[3] - min font size (px or %)
 */
$modversion["blocks"][]	= array(
    "file"			=> "newbb_block_tag.php",
    "name"			=> _MI_NEWBB_BLOCK_TAG_CLOUD,
    "description"	=> "Show tag cloud",
    "show_func"		=> "newbb_tag_block_cloud_show",
    "edit_func"		=> "newbb_tag_block_cloud_edit",
    "options"		=> "100|0|150|80",
    "template"		=> "newbb_tag_block_cloud.tpl",
    );

/*
 * $options:
 *					$options[0] - number of tags to display
 *					$options[1] - time duration, in days, 0 for all the time
 *					$options[2] - sort: a - alphabet; c - count; t - time
 */
$modversion["blocks"][]	= array(
    "file"			=> "newbb_block_tag.php",
    "name"			=> _MI_NEWBB_BLOCK_TAG_TOP,
    "description"	=> "Show top tags",
    "show_func"		=> "newbb_tag_block_top_show",
    "edit_func"		=> "newbb_tag_block_top_edit",
    "options"		=> "50|0|c",
    "template"		=> "newbb_tag_block_top.tpl",
    );
// irmtfan START add list topic block
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

$modversion['blocks'][] = array(
    'file' 			=> "list_topic.php",
    'name'			=> _MI_NEWBB_BLOCK_LIST_TOPIC,
    'description'	=> "Shows a list of topics (advance renderer)",
    'show_func'		=> "newbb_list_topic_show",
    'options'		=> "all|-1|-1|0|lastpost|0|5|360|topic,forum,replies,lastpost,lastposttime,lastposter,lastpostmsgicon,publish|1|0|200|0",
    'edit_func'		=> "newbb_list_topic_edit",
    'template'		=> 'newbb_block_list_topic.tpl');
// irmtfan END add list topic block

// Search
$modversion['hasSearch'] = 1;
$modversion['search']['file'] = "include/search.inc.php";
$modversion['search']['func'] = "newbb_search";

// Smarty
$modversion['use_smarty'] = 1;

// Configs
$modversion['config'] = array();

$modversion['config'][] = array(
    'name' 			=> 'do_rewrite',
    'title' 		=> '_MI_DO_REWRITE',
    'description' 	=> '_MI_DO_REWRITE_DESC',
    'formtype' 		=> 'yesno',
    'valuetype' 	=> 'int',
    'default' 		=> 0);

$modversion['config'][] = array(
    'name' 			=> 'pngforie_enabled',
    'title' 		=> '_MI_PNGFORIE_ENABLE',
    'description' 	=> '_MI_PNGFORIE_ENABLE_DESC',
    'formtype' 		=> 'yesno',
    'valuetype' 	=> 'int',
    'default' 		=> 0);

$modversion['config'][] = array(
    'name'			=> 'subforum_display',
    'title'			=> '_MI_SUBFORUM_DISPLAY',
    'description'	=> '_MI_SUBFORUM_DISPLAY_DESC',
    'formtype'		=> 'select',
    'valuetype'		=> 'text',
    'options'		=> array(
                        _MI_SUBFORUM_EXPAND		=> 'expand',
                        _MI_SUBFORUM_COLLAPSE	=> 'collapse',
                        _MI_SUBFORUM_HIDDEN		=> 'hidden'),
    'default'		=> "collapse");

$modversion['config'][] = array(
    'name'			=> 'post_excerpt',
    'title'			=> '_MI_POST_EXCERPT',
    'description'	=> '_MI_POST_EXCERPT_DESC',
    'formtype'		=> 'textbox',
    'valuetype'		=> 'int',
    'default'		=> 100);

$modversion['config'][] = array(
    'name'			=> 'topics_per_page',
    'title'			=> '_MI_TOPICSPERPAGE',
    'description'	=> '_MI_TOPICSPERPAGE_DESC',
    'formtype'		=> 'textbox',
    'valuetype'		=> 'int',
    'default'		=> 20);

$modversion['config'][] = array(
    'name'			=> 'posts_per_page',
    'title'			=> '_MI_POSTSPERPAGE',
    'description'	=> '_MI_POSTSPERPAGE_DESC',
    'formtype'		=> 'textbox',
    'valuetype'		=> 'int',
    'default'		=> 10);

$modversion['config'][] = array(
    'name'			=> 'pagenav_display',
    'title'			=> '_MI_PAGENAV_DISPLAY',
    'description'	=> '_MI_PAGENAV_DISPLAY_DESC',
    'formtype'		=> 'select',
    'valuetype'		=> 'text',
    'options'		=> array(
                        _MI_PAGENAV_ZAHL		=> 'zahl',
                        _MI_PAGENAV_BILD		=> 'bild',
                        _MI_PAGENAV_SELECT		=> 'select'),
    'default'		=> "zahl");

$modversion['config'][] = array(
    'name'			=> 'cache_enabled',
    'title'			=> '_MI_CACHE_ENABLE',
    'description'	=> '_MI_CACHE_ENABLE_DESC',
    'formtype'		=> 'yesno',
    'valuetype'		=> 'int',
    'default'		=> 0);

$modversion['config'][] = array(
    'name'			=> 'statistik_enabled',
    'title'			=> '_MI_STATISTIK_ENABLE',
    'description'	=> '_MI_STATISTIK_ENABLE_DESC',
    'formtype'		=> 'yesno',
    'valuetype'		=> 'int',
    'default'		=> 1);

$modversion['config'][] = array(
    'name'			=> 'dir_attachments',
    'title'			=> '_MI_DIR_ATTACHMENT',
    'description'	=> '_MI_DIR_ATTACHMENT_DESC',
    'formtype'		=> 'textbox',
    'valuetype'		=> 'text',
    'default'		=> 'uploads/newbb');

$modversion['config'][] = array(
    'name'			=> 'media_allowed',
    'title'			=> '_MI_MEDIA_ENABLE',
    'description'	=> '_MI_MEDIA_ENABLE_DESC',
    'formtype'		=> 'yesno',
    'valuetype'		=> 'int',
    'default'		=> 1);

$modversion['config'][] = array(
    'name'			=> 'path_magick',
    'title'			=> '_MI_PATH_MAGICK',
    'description'	=> '_MI_PATH_MAGICK_DESC',
    'formtype'		=> 'textbox',
    'valuetype'		=> 'text',
    'default'		=> '/usr/bin/X11');

$modversion['config'][] = array(
    'name'			=> 'path_netpbm',
    'title'			=> '_MI_PATH_NETPBM',
    'description'	=> '_MI_PATH_NETPBM_DESC',
    'formtype'		=> 'textbox',
    'valuetype'		=> 'text',
    'default'		=> '/usr/bin');

$modversion['config'][] = array(
    'name'			=> 'image_lib',
    'title'			=> '_MI_IMAGELIB',
    'description'	=> '_MI_IMAGELIB_DESC',
    'formtype'		=> 'select',
    'valuetype'		=> 'int',
    'default'		=> 0,
    'options'		=> array(
                        _MI_AUTO	=> 0,
                        _MI_MAGICK	=> 1,
                        _MI_NETPBM	=> 2,
                        _MI_GD1		=> 3,
                        _MI_GD2		=> 4 )
                        );

$modversion['config'][] = array(
    'name'			=> 'show_userattach',
    'title'			=> '_MI_USERATTACH_ENABLE',
    'description'	=> '_MI_USERATTACH_DESC',
    'formtype'		=> 'yesno',
    'valuetype'		=> 'int',
    'default'		=> 1);

$modversion['config'][] = array(
    'name'			=> 'max_img_width',
    'title'			=> '_MI_MAX_IMG_WIDTH',
    'description'	=> '_MI_MAX_IMG_WIDTH_DESC',
    'formtype'		=> 'textbox',
    'valuetype'		=> 'int',
    'default'		=> 800);

$modversion['config'][] = array(
    'name'			=> 'max_img_height',
    'title'			=> '_MI_MAX_IMG_HEIGHT',
    'description'	=> '_MI_MAX_IMG_HEIGHT_DESC',
    'formtype'		=> 'textbox',
    'valuetype'		=> 'int',
    'default'		=> 640);

$modversion['config'][] = array(
    'name'			=> 'max_image_width',
    'title'			=> '_MI_MAX_IMAGE_WIDTH',
    'description'	=> '_MI_MAX_IMAGE_WIDTH_DESC',
    'formtype'		=> 'textbox',
    'valuetype'		=> 'int',
    'default'		=> 150);

$modversion['config'][] = array(
    'name'			=> 'max_image_height',
    'title'			=> '_MI_MAX_IMAGE_HEIGHT',
    'description'	=> '_MI_MAX_IMAGE_HEIGHT_DESC',
    'formtype'		=> 'textbox',
    'valuetype'		=> 'int',
    'default'		=> 150);

$modversion['config'][] = array(
    'name'			=> 'wol_enabled',
    'title'			=> '_MI_WOL_ENABLE',
    'description'	=> '_MI_WOL_ENABLE_DESC',
    'formtype'		=> 'yesno',
    'valuetype'		=> 'int',
    'default'		=> 1);

$modversion['config'][] = array(
    'name'			=> 'user_level',
    'title'			=> '_MI_USERLEVEL',
    'description'	=> '_MI_USERLEVEL_DESC',
    'formtype'		=> 'select',
    'valuetype'		=> 'int',
    'default'		=> 2,
    'options'		=> array(
                        _MI_NULL	=> 0,
                        _MI_TEXT	=> 1,
                        _MI_GRAPHIC	=> 2)
                    );

$modversion['config'][] = array(
    'name'			=> 'show_realname',
    'title'			=> '_MI_SHOW_REALNAME',
    'description'	=> '_MI_SHOW_REALNAME_DESC',
    'formtype'		=> 'yesno',
    'valuetype'		=> 'int',
    'default'		=> 0);

$modversion['config'][] = array(
    'name'			=> 'groupbar_enabled',
    'title'			=> '_MI_GROUPBAR_ENABLE',
    'description'	=> '_MI_GROUPBAR_ENABLE_DESC',
    'formtype'		=> 'yesno',
    'valuetype'		=> 'int',
    'default'		=> 1);

$modversion['config'][] = array(
    'name'			=> 'rating_enabled',
    'title'			=> '_MI_RATING_ENABLE',
    'description'	=> '_MI_RATING_ENABLE_DESC',
    'formtype'		=> 'yesno',
    'valuetype'		=> 'int',
    'default'		=> 0);

$modversion['config'][] = array(
    'name'			=> 'reportmod_enabled',
    'title'			=> '_MI_REPORTMOD_ENABLE',
    'description'	=> '_MI_REPORTMOD_ENABLE_DESC',
    'formtype'		=> 'yesno',
    'valuetype'		=> 'int',
    'default'		=> 0);

$modversion['config'][] = array(
    'name'			=> 'quickreply_enabled',
    'title'			=> '_MI_QUICKREPLY_ENABLE',
    'description'	=> '_MI_QUICKREPLY_ENABLE_DESC',
    'formtype'		=> 'yesno',
    'valuetype'		=> 'int',
    'default'		=> 1);

$modversion['config'][] = array(
    'name'			=> 'rss_enable',
    'title'			=> '_MI_RSS_ENABLE',
    'description'	=> '_MI_RSS_ENABLE_DESC',
    'formtype'		=> 'yesno',
    'valuetype'		=> 'int',
    'default'		=> 1);

$modversion['config'][] = array(
    'name'			=> 'rss_maxitems',
    'title'			=> '_MI_RSS_MAX_ITEMS',
    'description'	=> '',
    'formtype'		=> 'textbox',
    'valuetype'		=> 'int',
    'default'		=> 10);

$modversion['config'][] = array(
    'name'			=> 'rss_maxdescription',
    'title'			=> '_MI_RSS_MAX_DESCRIPTION',
    'description'	=> '',
    'formtype'		=> 'textbox',
    'valuetype'		=> 'int',
    'default'		=> 0);

$modversion['config'][] = array(
    'name'			=> 'rss_cachetime',
    'title'			=> '_MI_RSS_CACHETIME',
    'description'	=> '_MI_RSS_CACHETIME_DESCRIPTION',
    'formtype'		=> 'textbox',
    'valuetype'		=> 'int',
    'default'		=> 30);

// 4.05
$modversion['config'][] = array(
    'name'      	=> 'show_infobox',
    'title'         => '_MI_SHOW_INFOBOX',
    'description'	=> '_MI_SHOW_INFOBOX_DESC',
    'formtype'		=> 'select',
    'valuetype'		=> 'int',
    'default'   	=> 1,
    'options'   	=> array(
                            _MI_NEWBB_INFOBOX_NONE =>0,
                            _MI_NEWBB_INFOBOX_HIDDEN => 1,
                            _MI_NEWBB_INFOBOX_SHOW => 2 )
    );

$modversion['config'][] = array(
    'name'          => 'show_sociallinks',
    'title'         => '_MI_SHOW_SOCIALLINKS',
    'description' 	=> '_MI_SHOW_SOCIALLINKS_DESC',
    'formtype'		=> 'yesno',
    'valuetype'		=> 'int',
    'default'		=> 1);

$modversion['config'][] = array(
    'name'          => 'show_advertising',
    'title'         => '_MI_ADVERTISING',
    'description' 	=> '_MI_ADVERTISING_DESC',
    'formtype'		=> 'yesno',
    'valuetype'		=> 'int',
    'default'		=> 0);

$modversion['config'][] = array(
    'name'			=> 'show_jump',
    'title'			=> '_MI_SHOW_JUMPBOX',
    'description'	=> '_MI_JUMPBOXDESC',
    'formtype'		=> 'yesno',
    'valuetype'		=> 'int',
    'default'		=> 1);

$modversion['config'][] = array(
    'name'			=> 'show_permissiontable',
    'title'			=> '_MI_SHOW_PERMISSIONTABLE',
    'description'	=> '_MI_SHOW_PERMISSIONTABLE_DESC',
    'formtype'		=> 'yesno',
    'valuetype'		=> 'int',
    'default'		=> 1);

$modversion['config'][] = array(
    'name'			=> 'email_digest',
    'title'			=> '_MI_EMAIL_DIGEST',
    'description'	=> '_MI_EMAIL_DIGEST_DESC',
    'formtype'		=> 'select',
    'valuetype'		=> 'int',
    'default'		=> 0,
    'options'		=> array(
                        _MI_NEWBB_EMAIL_NONE	=> 0,
                        _MI_NEWBB_EMAIL_DAILY	=> 1,
                        _MI_NEWBB_EMAIL_WEEKLY	=> 2)
                        );

$modversion['config'][] = array(
    'name'			=> 'show_ip',
    'title'			=> '_MI_SHOW_IP',
    'description'	=> '_MI_SHOW_IP_DESC',
    'formtype'		=> 'yesno',
    'valuetype'		=> 'int',
    'default'		=> 1);

$modversion['config'][] = array(
    'name'			=> 'enable_karma',
    'title'			=> '_MI_ENABLE_KARMA',
    'description'	=> '_MI_ENABLE_KARMA_DESC',
    'formtype'		=> 'yesno',
    'valuetype'		=> 'int',
    'default'		=> 1);

$modversion['config'][] = array(
    'name'			=> 'karma_options',
    'title'			=> '_MI_KARMA_OPTIONS',
    'description'	=> '_MI_KARMA_OPTIONS_DESC',
    'formtype'		=> 'textbox',
    'valuetype'		=> 'text',
    'default'		=> '0, 10, 50, 100, 500, 1000, 5000, 10000');
// irmtfan - add 365 = one year
$modversion['config'][] = array(
    'name'			=> 'since_options',
    'title'			=> '_MI_SINCE_OPTIONS',
    'description'	=> '_MI_SINCE_OPTIONS_DESC',
    'formtype'		=> 'textbox',
    'valuetype'		=> 'text',
    'default'		=> "-1, -2, -6, -12, 0, 1, 2, 5, 10, 20, 30, 60, 100, 365");

$modversion['config'][] = array(
    'name'			=> 'since_default',
    'title'			=> '_MI_SINCE_DEFAULT',
    'description'	=> '_MI_SINCE_DEFAULT_DESC',
    'formtype'		=> 'textbox',
    'valuetype'		=> 'int',
    'default'		=> 0);

$modversion['config'][] = array(
    'name'			=> 'allow_user_anonymous',
    'title'			=> '_MI_USER_ANONYMOUS',
    'description'	=> '_MI_USER_ANONYMOUS_DESC',
    'formtype'		=> 'yesno',
    'valuetype'		=> 'int',
    'default'		=> 0);

$modversion['config'][] = array(
    'name'			=> 'anonymous_prefix',
    'title'			=> '_MI_ANONYMOUS_PRE',
    'description'	=> '_MI_ANONYMOUS_PRE_DESC',
    'formtype'		=> 'textbox',
    'valuetype'		=> 'text',
    'default'		=> $GLOBALS['xoopsConfig']['anonymous']."-");

$modversion['config'][] = array(
    'name'			=> 'allow_require_reply',
    'title'			=> '_MI_REQUIRE_REPLY',
    'description'	=> '_MI_REQUIRE_REPLY_DESC',
    'formtype'		=> 'yesno',
    'valuetype'		=> 'int',
    'default'		=> 0);

$modversion['config'][] = array(
    'name'			=> 'edit_timelimit',
    'title'			=> '_MI_EDIT_TIMELIMIT',
    'description'	=> '_MI_EDIT_TIMELIMIT_DESC',
    'formtype'		=> 'textbox',
    'valuetype'		=> 'int',
    'default'		=> 60);

$modversion['config'][] = array(
    'name'			=> 'recordedit_timelimit',
    'title'			=> '_MI_RECORDEDIT_TIMELIMIT',
    'description'	=> '_MI_RECORDEDIT_TIMELIMIT_DESC',
    'formtype'		=> 'textbox',
    'valuetype'		=> 'int',
    'default'		=> 15);

$modversion['config'][] = array(
    'name'			=> 'delete_timelimit',
    'title'			=> '_MI_DELETE_TIMELIMIT',
    'description'	=> '_MI_DELETE_TIMELIMIT_DESC',
    'formtype'		=> 'textbox',
    'valuetype'		=> 'int',
    'default'		=> 60);

$modversion['config'][] = array(
    'name'			=> 'post_timelimit',
    'title'			=> '_MI_POST_TIMELIMIT',
    'description'	=> '_MI_POST_TIMELIMIT_DESC',
    'formtype'		=> 'textbox',
    'valuetype'		=> 'int',
    'default'		=> 30);

$modversion['config'][] = array(
    'name'			=> 'enable_permcheck',
    'title'			=> '_MI_PERMCHECK_ONDISPLAY',
    'description'	=> '_MI_PERMCHECK_ONDISPLAY_DESC',
    'formtype'		=> 'yesno',
    'valuetype'		=> 'int',
    'default'		=> 1);

$modversion['config'][] = array(
    'name'			=> 'enable_usermoderate',
    'title'			=> '_MI_USERMODERATE',
    'description'	=> '_MI_USERMODERATE_DESC',
    'formtype'		=> 'yesno',
    'valuetype'		=> 'int',
    'default'		=> 0);

$modversion['config'][] = array(
    'name'			=> 'disc_show',
    'title'			=> '_MI_SHOW_DIS',
    'description'	=> '_MI_SHOW_DIS_DESC',
    'formtype'		=> 'select',
    'valuetype'		=> 'int',
    'default'		=> 0,
    'options'		=> array(
                        _NONE		=> 0,
                        _MI_POST	=> 1,
                        _MI_REPLY	=> 2,
                        _MI_OP_BOTH	=> 3)
                        );

$modversion['config'][] = array(
    'name'			=> 'disclaimer',
    'title'			=> '_MI_DISCLAIMER',
    'description'	=> '_MI_DISCLAIMER_DESC',
    'formtype'		=> 'textarea',
    'valuetype'		=> 'text',
    'default'		=> _MI_DISCLAIMER_TEXT);

$forum_options = array(_NONE => 0);
if ($isModuleAction && "update_ok" == $_POST["op"]) {
    $forum_handler =& xoops_getmodulehandler('forum', 'newbb', true);
    if ( $forums = $forum_handler->getForumsByCategory(0, 'access', false, array("parent_forum", "cat_id", "forum_name")) ):
    foreach (array_keys($forums) as $c) {
        foreach (array_keys($forums[$c]) as $f) {
            $forum_options[$forums[$c][$f]["title"]] = $f;
            if (!isset($forums[$c][$f]["sub"])) continue;
            foreach (array_keys($forums[$c][$f]["sub"]) as $s) {
                $forum_options["-- ".$forums[$c][$f]["sub"][$s]["title"]] = $s;
            }
        }
    }
    unset($forums);
    endif;
}
$modversion['config'][] = array(
    'name'			=> 'welcome_forum',
    'title'			=> '_MI_WELCOMEFORUM',
    'description'	=> '_MI_WELCOMEFORUM_DESC',
    'formtype'		=> 'select',
    'valuetype'		=> 'int',
    'default'		=> 0,
    'options'		=> $forum_options);
// START irmtfan add a poll_module config
$pollDirs = array();
$dir_def = 0;
$formtype = "select";
// if in install, update
if ($isModuleAction) {
    $topic_handler = xoops_getmodulehandler('topic', $modversion['dirname']);
    $pollDirs = $topic_handler->getActivePolls();
    // priorities for default poll module : 1- xoopspoll 2- last element in array 3- if no poll module => 0
    $dir_def = !empty($pollDirs) ? (!empty($pollDirs["xoopspoll"]) ? $pollDirs["xoopspoll"] : end($pollDirs))
                                 : 0;
    //Now check all topics and try to find the poll module
    if ("update_ok" == $_POST["op"]) {
        $dir_in_update = $topic_handler->findPollModule($pollDirs);
        if (!is_bool($dir_in_update)) {
            $dir_def = $dir_in_update;
            // if change 'formtype' to hidden the default value will be changed too!!!
            // see xoops255/modules/system/admin/modulesadmin/main.php line 829
            $formtype = "hidden";
        } else {
            $formtype = "select";
        }
    }
}

$isPref = (
    // action module "system"
    is_object($GLOBALS["xoopsModule"]) && "system" == $GLOBALS["xoopsModule"]->getVar("dirname", "n")
    &&
    // current action
    !empty($_REQUEST['fct']) && $_REQUEST['fct'] == "preferences"
    );
xoops_loadLanguage('admin', $modversion['dirname']);
// if in pref AND click on save AND 'poll_module' != 0
if ($isPref && !empty($_POST['poll_module'])) {
    $hModConfig = xoops_gethandler('config');
    $criteria = new CriteriaCompo();
    $criteria->add(new Criteria('conf_name', "poll_module", "="), "AND");
    $criteria->add(new Criteria('conf_formtype', "select", "="), "AND"); // not hidden
    $criteria->add(new Criteria('conf_id', "(" . implode(", ",$_POST['conf_ids']). ")", "IN"), "AND");
    $pollOptions = $hModConfig->getConfigs($criteria);
    $pollOptions = end($pollOptions);
    if (is_object($pollOptions) && $pollOptions->getVar("conf_value") != "0") {
        $topic_handler = xoops_getmodulehandler('topic', $modversion['dirname']);
        $topicPolls = $topic_handler->getCount(new Criteria("topic_haspoll", 1));
        if ($topicPolls > 0) {
            $poll_module_in_use = $topic_handler->findPollModule();
            if (is_string($poll_module_in_use)) {
                $pollOptions->setVar("conf_value", $poll_module_in_use);
                $pollOptions->setVar("conf_formtype", "hidden");
                $result = $hModConfig->insertConfig($pollOptions);
                if (!$result) {
                    //echo "error: poll_module is in danger!!!";
                }
                // I have to redirect back to prevent system module to save bad $_POST['poll_module'] setting!!!
                redirect_header($_SERVER['HTTP_REFERER'], 2, _AM_SYSTEM_DBUPDATED . "<br/>" .
                            _AM_NEWBB_POLLMODULE ." ". _AM_NEWBB_POLL_OK . " :(" . $poll_module_in_use . ")");
            }
        }
    }
}
$i = count($modversion['config']); // temporary until change the whole xoops_version config
++$i;
$modversion['config'][$i]['name'] = 'poll_module';
$modversion['config'][$i]['title'] = '_AM_NEWBB_POLLMODULE';
$modversion['config'][$i]['description'] = '_AM_NEWBB_POLLMODULE';
$modversion['config'][$i]['valuetype'] = 'text';
$modversion['config'][$i]['default'] = $dir_def;
$modversion['config'][$i]['formtype'] = $formtype;
$modversion['config'][$i]['options'] = $pollDirs;

// END irmtfan add a poll_module config
// Notification
$modversion["notification"] = array();
$modversion['hasNotification'] = 1;
$modversion['notification']['lookup_file'] = 'include/notification.inc.php';
$modversion['notification']['lookup_func'] = 'newbb_notify_iteminfo';

$modversion['notification']['category'][1]['name'] = 'thread';
$modversion['notification']['category'][1]['title'] = _MI_NEWBB_THREAD_NOTIFY;
$modversion['notification']['category'][1]['description'] = _MI_NEWBB_THREAD_NOTIFYDSC;
$modversion['notification']['category'][1]['subscribe_from'] = 'viewtopic.php';
$modversion['notification']['category'][1]['item_name'] = 'topic_id';
$modversion['notification']['category'][1]['allow_bookmark'] = 1;

$modversion['notification']['category'][2]['name'] = 'forum';
$modversion['notification']['category'][2]['title'] = _MI_NEWBB_FORUM_NOTIFY;
$modversion['notification']['category'][2]['description'] = _MI_NEWBB_FORUM_NOTIFYDSC;
$modversion['notification']['category'][2]['subscribe_from'] = 'viewforum.php';
$modversion['notification']['category'][2]['item_name'] = 'forum';
$modversion['notification']['category'][2]['allow_bookmark'] = 1;

$modversion['notification']['category'][3]['name'] = 'global';
$modversion['notification']['category'][3]['title'] = _MI_NEWBB_GLOBAL_NOTIFY;
$modversion['notification']['category'][3]['description'] = _MI_NEWBB_GLOBAL_NOTIFYDSC;
$modversion['notification']['category'][3]['subscribe_from'] = 'index.php';

$modversion['notification']['event'][1]['name'] = 'new_post';
$modversion['notification']['event'][1]['category'] = 'thread';
$modversion['notification']['event'][1]['title'] = _MI_NEWBB_THREAD_NEWPOST_NOTIFY;
$modversion['notification']['event'][1]['caption'] = _MI_NEWBB_THREAD_NEWPOST_NOTIFYCAP;
$modversion['notification']['event'][1]['description'] = _MI_NEWBB_THREAD_NEWPOST_NOTIFYDSC;
$modversion['notification']['event'][1]['mail_template'] = 'thread_newpost_notify';
$modversion['notification']['event'][1]['mail_subject'] = _MI_NEWBB_THREAD_NEWPOST_NOTIFYSBJ;

$modversion['notification']['event'][2]['name'] = 'new_thread';
$modversion['notification']['event'][2]['category'] = 'forum';
$modversion['notification']['event'][2]['title'] = _MI_NEWBB_FORUM_NEWTHREAD_NOTIFY;
$modversion['notification']['event'][2]['caption'] = _MI_NEWBB_FORUM_NEWTHREAD_NOTIFYCAP;
$modversion['notification']['event'][2]['description'] = _MI_NEWBB_FORUM_NEWTHREAD_NOTIFYDSC;
$modversion['notification']['event'][2]['mail_template'] = 'forum_newthread_notify';
$modversion['notification']['event'][2]['mail_subject'] = _MI_NEWBB_FORUM_NEWTHREAD_NOTIFYSBJ;

$modversion['notification']['event'][3]['name'] = 'new_forum';
$modversion['notification']['event'][3]['category'] = 'global';
$modversion['notification']['event'][3]['title'] = _MI_NEWBB_GLOBAL_NEWFORUM_NOTIFY;
$modversion['notification']['event'][3]['caption'] = _MI_NEWBB_GLOBAL_NEWFORUM_NOTIFYCAP;
$modversion['notification']['event'][3]['description'] = _MI_NEWBB_GLOBAL_NEWFORUM_NOTIFYDSC;
$modversion['notification']['event'][3]['mail_template'] = 'global_newforum_notify';
$modversion['notification']['event'][3]['mail_subject'] = _MI_NEWBB_GLOBAL_NEWFORUM_NOTIFYSBJ;

$modversion['notification']['event'][4]['name'] = 'new_post';
$modversion['notification']['event'][4]['category'] = 'global';
$modversion['notification']['event'][4]['title'] = _MI_NEWBB_GLOBAL_NEWPOST_NOTIFY;
$modversion['notification']['event'][4]['caption'] = _MI_NEWBB_GLOBAL_NEWPOST_NOTIFYCAP;
$modversion['notification']['event'][4]['description'] = _MI_NEWBB_GLOBAL_NEWPOST_NOTIFYDSC;
$modversion['notification']['event'][4]['mail_template'] = 'global_newpost_notify';
$modversion['notification']['event'][4]['mail_subject'] = _MI_NEWBB_GLOBAL_NEWPOST_NOTIFYSBJ;

$modversion['notification']['event'][5]['name'] = 'new_post';
$modversion['notification']['event'][5]['category'] = 'forum';
$modversion['notification']['event'][5]['title'] = _MI_NEWBB_FORUM_NEWPOST_NOTIFY;
$modversion['notification']['event'][5]['caption'] = _MI_NEWBB_FORUM_NEWPOST_NOTIFYCAP;
$modversion['notification']['event'][5]['description'] = _MI_NEWBB_FORUM_NEWPOST_NOTIFYDSC;
$modversion['notification']['event'][5]['mail_template'] = 'forum_newpost_notify';
$modversion['notification']['event'][5]['mail_subject'] = _MI_NEWBB_FORUM_NEWPOST_NOTIFYSBJ;

$modversion['notification']['event'][6]['name'] = 'new_fullpost';
$modversion['notification']['event'][6]['category'] = 'global';
$modversion['notification']['event'][6]['admin_only'] = 1;
$modversion['notification']['event'][6]['title'] = _MI_NEWBB_GLOBAL_NEWFULLPOST_NOTIFY;
$modversion['notification']['event'][6]['caption'] = _MI_NEWBB_GLOBAL_NEWFULLPOST_NOTIFYCAP;
$modversion['notification']['event'][6]['description'] = _MI_NEWBB_GLOBAL_NEWFULLPOST_NOTIFYDSC;
$modversion['notification']['event'][6]['mail_template'] = 'global_newfullpost_notify';
$modversion['notification']['event'][6]['mail_subject'] = _MI_NEWBB_GLOBAL_NEWFULLPOST_NOTIFYSBJ;

$modversion['notification']['event'][7]['name'] = 'digest';
$modversion['notification']['event'][7]['category'] = 'global';
$modversion['notification']['event'][7]['title'] = _MI_NEWBB_GLOBAL_DIGEST_NOTIFY;
$modversion['notification']['event'][7]['caption'] = _MI_NEWBB_GLOBAL_DIGEST_NOTIFYCAP;
$modversion['notification']['event'][7]['description'] = _MI_NEWBB_GLOBAL_DIGEST_NOTIFYDSC;
$modversion['notification']['event'][7]['mail_template'] = 'global_digest_notify';
$modversion['notification']['event'][7]['mail_subject'] = _MI_NEWBB_GLOBAL_DIGEST_NOTIFYSBJ;

$modversion['notification']['event'][8]['name'] = 'new_fullpost';
$modversion['notification']['event'][8]['category'] = 'forum';
$modversion['notification']['event'][8]['admin_only'] = 1;
$modversion['notification']['event'][8]['title'] = _MI_NEWBB_GLOBAL_NEWFULLPOST_NOTIFY;
$modversion['notification']['event'][8]['caption'] = _MI_NEWBB_GLOBAL_NEWFULLPOST_NOTIFYCAP;
$modversion['notification']['event'][8]['description'] = _MI_NEWBB_GLOBAL_NEWFULLPOST_NOTIFYDSC;
$modversion['notification']['event'][8]['mail_template'] = 'global_newfullpost_notify';
$modversion['notification']['event'][8]['mail_subject'] = _MI_NEWBB_GLOBAL_NEWFULLPOST_NOTIFYSBJ;
