<?php
/**
 * NewBB 4.3x, the forum module for XOOPS project
 *
 * @copyright      XOOPS Project (http://xoops.org)
 * @license        http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author         Taiwen Jiang (phppp or D.J.) <phppp@users.sourceforge.net>
 * @since          4.00
 * @version        $Id $
 * @package        module::newbb
 */

// defined('XOOPS_ROOT_PATH') || exit('XOOPS root path not defined');

defined('NEWBB_FUNCTIONS_INI') || include_once __DIR__ . '/functions.ini.php';
define('NEWBB_FUNCTIONS_CONFIG_LOADED', true);

if (!defined('NEWBB_FUNCTIONS_CONFIG')) {
    define('NEWBB_FUNCTIONS_CONFIG', 1);

    /**
     * @param  string $category
     * @param  string $dirname
     * @return bool
     */
    function newbbLoadConfig($category = '', $dirname = 'newbb')
    {
        //        global $xoopsModuleConfig;
        static $configs;

        if (isset($configs['']) || isset($configs[$category])) {
            return true;
        }
        $configHandler = xoops_getModuleHandler('config', $dirname);
        if ($configsData = $configHandler->getByCategory($category)) {
            $GLOBALS['xoopsModuleConfig'] = array_merge($GLOBALS['xoopsModuleConfig'], $configsData);
        }
        $configs[$category] = 1;

        return true;
    }
}
