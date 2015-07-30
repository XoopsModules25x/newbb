<?php
/**
 * NewBB 4.3x, the forum module for XOOPS project
 *
 * @copyright    XOOPS Project (http://xoops.org)
 * @license        http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author        Taiwen Jiang (phppp or D.J.) <phppp@users.sourceforge.net>
 * @since        4.00
 * @version        $Id $
 * @package        module::newbb
 */

// defined('XOOPS_ROOT_PATH') || exit('XOOPS root path not defined');

defined("NEWBB_FUNCTIONS_INI") || include_once __DIR__ . "/functions.ini.php";
define("NEWBB_FUNCTIONS_WELCOME_LOADED", true);

if (!defined("NEWBB_FUNCTIONS_WELCOME")) {
    define("NEWBB_FUNCTIONS_WELCOME", true);

    /**
     * @return bool
     */
    function newbb_welcome()
    {
        global $forum_obj;
        //$GLOBALS['xoopsModuleConfig']["welcome_forum"] = 1;
        $forumHandler =& xoops_getmodulehandler('forum', 'newbb');
        $forum_obj     = $forumHandler->get($GLOBALS['xoopsModuleConfig']["welcome_forum"]);
        if (!$forumHandler->getPermission($forum_obj)) {
            unset($forum_obj);

            return false;
        }

        include __DIR__ . "/functions.welcome.inc.php";
        unset($forum_obj);

        return $ret;
    }

    newbb_welcome();
}
