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
define("NEWBB_FUNCTIONS_CONFIG_LOADED", true);

if (!defined("NEWBB_FUNCTIONS_CONFIG")) {
    define("NEWBB_FUNCTIONS_CONFIG", 1);

    /**
     * @param string $category
     * @param string $dirname
     * @return bool
     */
    function newbbLoadConfig($category = "", $dirname = "newbb")
    {
//        global $xoopsModuleConfig;
        static $configs;

        if (isset($configs[""]) || isset($configs[$category])) {
            return true;
        }
        $configHandler = xoops_getmodulehandler("config", $dirname);
        if ($configs_data = $configHandler->getByCategory($category)) {
            $GLOBALS["xoopsModuleConfig"] = array_merge($GLOBALS["xoopsModuleConfig"], $configs_data);
        }
        $configs[$category] = 1;

        return true;
    }
}
