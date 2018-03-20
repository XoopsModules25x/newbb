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

defined('NEWBB_FUNCTIONS_INI') || include __DIR__ . '/functions.ini.php';
define('NEWBB_FUNCTIONS_LOADED', true);

if (!defined('NEWBB_FUNCTIONS')) {
    define('NEWBB_FUNCTIONS', 1);

    require_once __DIR__ . '/functions.image.php';
    require_once __DIR__ . '/functions.user.php';
    require_once __DIR__ . '/functions.render.php';
    require_once __DIR__ . '/functions.forum.php';
    require_once __DIR__ . '/functions.session.php';
    require_once __DIR__ . '/functions.stats.php';
}
