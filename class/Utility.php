<?php declare(strict_types=1);

namespace XoopsModules\Newbb;

use Xmf\Module\Helper\Cache;

/** @var Helper $helper */

/**
 * Class Utility
 */
class Utility extends Common\SysUtility
{
    //--------------- Custom module methods -----------------------------

    public static function cleanCache(): void
    {
        $cacheHelper = new Cache('newbb');
        if (\method_exists($cacheHelper, 'clear')) {
            $cacheHelper->clear();

            return;
        }
        // for 2.5 systems, clear everything
        require_once XOOPS_ROOT_PATH . '/modules/system/class/maintenance.php';
        $maintenance = new \SystemMaintenance();
        $cacheList   = [
            3, // xoops_cache
        ];
        $maintenance->CleanCache($cacheList);
        \xoops_setActiveModules();
    }

    /**
     * Checks if a user is admin of NewBB
     *
     * @return bool
     */
    public static function userIsAdmin()
    {
        $helper = Helper::getInstance();

        static $newbbIsAdmin;

        if (isset($newbbIsAdmin)) {
            return $newbbIsAdmin;
        }

        if ($GLOBALS['xoopsUser']) {
            $newbbIsAdmin = $GLOBALS['xoopsUser']->isAdmin($helper->getModule()->getVar('mid'));
        } else {
            $newbbIsAdmin = false;
        }

        return $newbbIsAdmin;
    }
}
