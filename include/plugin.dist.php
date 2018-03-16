<?php
/**
 * newbb plugin configurations
 *
 * To enable the plugin preferences, you must rename the file or copy it to 'plugin.php'
 *
 * @copyright      XOOPS Project (https://xoops.org)/
 * @license        GNU GPL 2 or later (http://www.gnu.org/licenses/gpl-2.0.html)
 * @author         Taiwen Jiang (phppp or D.J.) <php_pp@hotmail.com>
 * @since          4.00
 * @package        module::newbb
 */
xoops_loadLanguage('main', 'newbb');

// defined('XOOPS_ROOT_PATH') || die('Restricted access');
/* some static xoopsModuleConfig */
$customConfig = [];

// specification for custom time format
// default manner will be used if not specified
$customConfig['formatTimestamp_custom'] = ''; // Could be set as 'Y-m-d H:i'

// requiring 'name' field for anonymous users in edit form
$customConfig['require_name'] = true;

// display 'register or login to post' for anonymous users
$customConfig['show_reg'] = true;

// perform forum/topic synchronization on module update
$customConfig['syncOnUpdate'] = false;

// time for pending/deleted topics/posts, expired one will be removed automatically, in days; 0 or no cleanup
$customConfig['pending_expire'] = 7;

// redirect to its URI of an attachment when requested
// Set to true if your attachment would be corrupted after download with normal way
$customConfig['download_direct'] = false;

// Set allowed editors
// Should set from module preferences?
$customConfig['editor_allowed'] = [];

// Set the default editor
$customConfig['editor_default'] = '';

// storage method for reading records: 0 - none; 1 - cookie; 2 - db
$customConfig['read_mode'] = 2;

// expire time for reading records, in days
$customConfig['read_expire'] = 30;

// maximum records per forum for one user
$customConfig['read_items'] = 100;

// default value for editor rows, coloumns
$customConfig['editor_rows'] = 35;
$customConfig['editor_cols'] = 60;

// default value for editor width, height (string)
$customConfig['editor_width']  = '100%';
$customConfig['editor_height'] = '400px';

// Enable tag system
$customConfig['do_tag'] = 1;

// Count posts counts of subfourms
$customConfig['count_subforum'] = 1;

// Length for post title on index page: 0 for not showing post title, 255 for not truncate
$customConfig['length_title_index'] = 255;

// MENU handler
/* You could remove anyone by commenting out in order to disable it */
$valid_menumodes = [
    0 => _MD_NEWBB_MENU_SELECT,    // for selectbox
    1 => _MD_NEWBB_MENU_CLICK,    // for 'click to expand'
    2 => _MD_NEWBB_MENU_HOVER        // for 'mouse hover to expand'
];

return $customConfig;
