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

use XoopsModules\Newbb;

// defined('XOOPS_ROOT_PATH') || die('Restricted access');

defined('NEWBB_FUNCTIONS_INI') || require_once __DIR__ . '/functions.ini.php';
define('NEWBB_FUNCTIONS_WELCOME_LOADED', true);

if (!defined('NEWBB_FUNCTIONS_WELCOME')) {
    define('NEWBB_FUNCTIONS_WELCOME', true);

    /**
     * @return bool
     */
    function newbbWelcome()
    {
        global $forumObject;
        $ret = '';

        $forumId = @$GLOBALS['xoopsModuleConfig']['welcome_forum'];
        if (!$forumId) {
            return false;
        }
        /** @var Newbb\ForumHandler $forumHandler */
        $forumHandler = Newbb\Helper::getInstance()->getHandler('Forum');
        $forumObject  = $forumHandler->get($forumId);
        if (!$forumObject || !$forumHandler->getPermission($forumObject)) {
            unset($forumObject);

            return false;
        }

        include __DIR__ . '/functions.welcome.inc.php';
        unset($forumObject);

        return $ret;
    }

    newbbWelcome();
}
