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

defined("NEWBB_FUNCTIONS_INI") || include __DIR__ . "/functions.ini.php";
define("NEWBB_FUNCTIONS_LOADED", true);

if (!defined("NEWBB_FUNCTIONS")) {
    define("NEWBB_FUNCTIONS", 1);

    load_functions();
    mod_loadFunctions("image", "newbb");
    mod_loadFunctions("user", "newbb");
    mod_loadFunctions("render", "newbb");
    mod_loadFunctions("forum", "newbb");
    mod_loadFunctions("session", "newbb");
    mod_loadFunctions("stats", "newbb");
}
