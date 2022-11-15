<?php declare(strict_types=1);
/**
 * NewBB 5.0x,  the forum module for XOOPS project
 *
 * @copyright      XOOPS Project (https://xoops.org)
 * @license        GNU GPL 2.0 or later (https://www.gnu.org/licenses/gpl-2.0.html)
 * @author         Taiwen Jiang (phppp or D.J.) <phppp@users.sourceforge.net>
 * @since          4.00
 */
defined('NEWBB_FUNCTIONS_INI') || require __DIR__ . '/functions.ini.php';
define('NEWBB_FUNCTIONS_LANGUAGE_LOADED', true);

if (!defined('NEWBB_FUNCTIONS_LANGUAGE')) {
    define('NEWBB_FUNCTIONS_LANGUAGE', 1);

    /**
     * @param         $page
     * @param string  $dirname
     * @return bool
     */
    function newbbLoadLanguage($page, $dirname = 'newbb')
    {
        $page = str_replace('..', '', $page);
        if (!@require_once $GLOBALS['xoops']->path("modules/{$dirname}/{$GLOBALS['xoopsConfig']['language']}/{$language}.php")) {
            if (!@require_once $GLOBALS['xoops']->path("modules/{$dirname}/language/{$language}.php")) {
                return false;
            }
        }

        return true;
    }
}
