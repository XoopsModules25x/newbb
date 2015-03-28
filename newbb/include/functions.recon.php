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

defined("NEWBB_FUNCTIONS_INI") || include __DIR__ . "/functions.ini.php";
define("NEWBB_FUNCTIONS_RECON_LOADED", true);

if (!defined("NEWBB_FUNCTIONS_RECON")) {
    define("NEWBB_FUNCTIONS_RECON", 1);

    function newbb_synchronization($type = null)
    {
        $allTypes = array("category", "forum", "topic", "post", "report", "rate", "moderate", "readtopic", "readforum", "stats");
        $type     = empty($type) ? $allTypes : (is_array($type) ? $type : array($type));
        foreach ($type as $item) {
            $handler = xoops_getmodulehandler($item, "newbb");
            if ($item != "stats") {
                $handler->synchronization();
            } else {
                $handler->reset();
            }

            if (method_exists($handler, "cleanExpires")) {
                $handler->cleanExpires();
            }
            if (method_exists($handler, "clearGarbage")) {
                $handler->clearGarbage();
            }
            unset($handler);
        }

        return true;
    }

}
