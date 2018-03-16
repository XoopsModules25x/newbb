<?php
/**
 * NewBB 5.0x,  the forum module for XOOPS project
 *
 * @copyright      XOOPS Project (https://xoops.org)
 * @license        GNU GPL 2 or later (http://www.gnu.org/licenses/gpl-2.0.html)
 * @author         Taiwen Jiang (phppp or D.J.) <phppp@users.sourceforge.net>
 * @since          4.00
 * @package        module::newbb
 */

xoops_loadLanguage('main', 'newbb');

// defined('XOOPS_ROOT_PATH') || die('Restricted access');
/* some static xoopsModuleConfig */
$customConfig = [];

// specification for custom time format
// default manner will be used if not specified
$customConfig['formatTimestamp_custom'] = ''; // Could be set as "Y-m-d H:i"

// requiring "name" field for anonymous users in edit form
$customConfig['require_name'] = true;

// display "register or login to post" for anonymous users
$customConfig['show_reg'] = true;

// perform forum/topic synchronization on module update
$customConfig['syncOnUpdate'] = false;

// time for pending/deleted topics/posts, expired one will be removed automatically, in days; 0 or no cleanup
$customConfig['pending_expire'] = 0;

// redirect to its URI of an attachment when requested
// Set to true if your attachment would be corrupted after download with normal way
$customConfig['download_direct'] = false;

// Set allowed editors
// Should set from module preferences?
$customConfig['editor_allowed'] = [];

// Set the default editor
$customConfig['editor_default'] = 'dhtmltextarea';

// Set the default editor for quick reply
$customConfig['editor_quick_default'] = 'textarea';

// default value for editor rows, coloumns
$customConfig['editor_rows'] = 15;
$customConfig['editor_cols'] = 40;

// default value for editor width, height (string)
$customConfig['editor_width']  = '100%';
$customConfig['editor_height'] = '400px';

// storage method for reading records: 0 - none; 1 - cookie; 2 - db
$customConfig['read_mode'] = 2;

// expire time for reading records, in days; irmtfan add feature: 0 or no cleanup
$customConfig['read_expire'] = 0;

// maximum records per forum for one user
$customConfig['read_items'] = 100;

// Enable tag system
$customConfig['do_tag'] = 1;

// Count posts counts of subfourms
$customConfig['count_subforum'] = 1;

// Length for post title on index page: 0 for not showing post title, 255 for not truncate
$customConfig['length_title_index'] = 40;

// MENU handler
/* You could remove anyone by commenting out in order to disable it */
$customConfig['valid_menumodes'] = [
    0 => _MD_NEWBB_MENU_SELECT,    // for selectbox
    //1 => _MD_NEWBB_MENU_CLICK,    // for "click to expand"
    //2 => _MD_NEWBB_MENU_HOVER        // for "mouse hover to expand"
];

// view latest edit
// 1 - all / 0-latest
$customConfig['do_latestedit'] = 1;

// START hacked by irmtfan
// Dispaly Text links instead of images and vice versa  => text links=true/images=false
// This is overall value.
// It means if you set $customConfig["display_text_links"] to true it will show all images in text links (and vice versa)
$customConfig['display_text_links'] = false;
// Display Text links instead of images and vice versa  => text links=true/images=false
// This is for each link.
// It means you can overwrite the above $customConfig["display_text_links"] overall value for each link one by one.
// go to /modules/newbb/include/display.php to set for each link
$customConfig['display_text_each_link'] = include $GLOBALS['xoops']->path('modules/newbb/include/display.php');
// jump to last post read in the topic
$customConfig['jump_to_topic_last_post_read_enabled'] = true;
// highlight keywords in search
$customConfig['highlight_search_enable'] = true;
// change the read_mode = 2 (db) to read_mode = 1 (cookie) for anonymous users
// Note: if set to true only change read_mode for anonymous users if read_mode = 2 (db), set to false to no action.
$customConfig['read_mode_db_to_cookie_for_anon'] = true;
// render topics with the specific title length. 0 = dont excerpt
$customConfig['topic_title_excerpt'] = 0;
// END hacked by irmtfan

return $customConfig;
