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

// defined('XOOPS_ROOT_PATH') || die('Restricted access');
require_once $GLOBALS['xoops']->path('modules/newbb/include/functions.ini.php');
require_once __DIR__ . '/functions.session.php';

// NewBB cookie structure
/* NewBB cookie storage
    Long term cookie: (configurable, generally one month)
        LV - Last Visit
        M - Menu mode
        V - View mode
        G - Toggle
    Short term cookie: (same as session life time)
        ST - Stored Topic IDs for mark
        LP - Last Post
        LF - Forum Last view
        LT - Topic Last read
        LVT - Last Visit Temp
*/

/* -- Cookie settings -- */
$forumCookie['domain'] = '';
$forumCookie['path']   = '/';
$forumCookie['secure'] = false;
$forumCookie['expire'] = time() + 3600 * 24 * 30; // one month
$forumCookie['prefix'] = 'newbb_' . (is_object($GLOBALS['xoopsUser']) ? $GLOBALS['xoopsUser']->getVar('uid') : '0IP' . \Xmf\IPAddress::fromRequest()->asReadable()); // irmtfan IP for anons - use $GLOBALS["xoopsUser"]

// set LastVisitTemp cookie, which only gets the time from the LastVisit cookie if it does not exist yet
// otherwise, it gets the time from the LastVisitTemp cookie
$last_visit = newbbGetSession('LV');
$last_visit = $last_visit ?: newbbGetCookie('LV');
$last_visit = $last_visit ?: time();

// update LastVisit cookie.
newbbSetCookie('LV', time(), $forumCookie['expire']); // set cookie life time to one month
newbbSetSession('LV', $last_visit);

// include customized variables
if (is_object($GLOBALS['xoopsModule']) && 'newbb' === $GLOBALS['xoopsModule']->getVar('dirname', 'n')) {
    $GLOBALS['xoopsModuleConfig'] = newbbLoadConfig();
}
