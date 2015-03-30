<?php
/**
 * CBB 4.0, or newbb, the forum module for XOOPS project
 *
 * @copyright    The XOOPS Project http://xoops.sf.net
 * @license        http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author        Taiwen Jiang (phppp or D.J.) <phppp@users.sourceforge.net>
 * @since        4.00
 * @version        $Id $
 * @package        module::newbb
 */

// defined('XOOPS_ROOT_PATH') || exit('XOOPS root path not defined');

defined("NEWBB_FUNCTIONS_INI") || include_once __DIR__ . "/functions.ini.php";
define("NEWBB_FUNCTIONS_LANGUAGE_LOADED", true);

if (!defined("NEWBB_FUNCTIONS_LANGUAGE")) {
    define("NEWBB_FUNCTIONS_LANGUAGE", 1);

    /**
     * @param $page
     * @param string $dirname
     * @return bool
     */
    function newbb_load_language($page, $dirname = "newbb")
    {
        $page = str_replace("..", "", $page);
        if (!@include_once $GLOBALS['xoops']->path("modules/{$dirname}/{$GLOBALS['xoopsConfig']['language']}/{$language}.php")) {
            if (!@include_once $GLOBALS['xoops']->path("modules/{$dirname}/language/{$language}.php")) {
                return false;
            }
        }

        return true;
    }
}
