<?php declare(strict_types=1);

/**
 * NewBB 5.0x,  the forum module for XOOPS project
 *
 * @copyright      XOOPS Project (https://xoops.org)
 * @license        GNU GPL 2.0 or later (https://www.gnu.org/licenses/gpl-2.0.html)
 * @author         Taiwen Jiang (phppp or D.J.) <phppp@users.sourceforge.net>
 * @since          4.00
 */

use XoopsModules\Newbb\Helper;

/** @var Helper $helper */
defined('NEWBB_FUNCTIONS_INI') || require __DIR__ . '/functions.ini.php';
define('NEWBB_FUNCTIONS_RECON_LOADED', true);

if (!defined('NEWBB_FUNCTIONS_RECON')) {
    define('NEWBB_FUNCTIONS_RECON', 1);

    /**
     * @param null $type
     * @return bool
     */
    function newbbSynchronization($type = null)
    {
        $allTypes = [
            'category',
            'forum',
            'topic',
            'post',
            'report',
            'rate',
            'moderate',
            'readtopic',
            'readforum',
            'stats',
        ];
        $type     = [];
        $type     = empty($type) ? $allTypes : (is_array($type) ? $type : [$type]);
        foreach ($type as $item) {
            /** @var \XoopsPersistableObjectHandler $handler */
            $handler = Helper::getInstance()->getHandler($item);
            if ('stats' !== $item) {
                $handler->synchronization();
            } else {
                $handler->reset();
            }

            if (method_exists($handler, 'cleanExpires')) {
                $handler->cleanExpires();
            }
            if (method_exists($handler, 'clearGarbage')) {
                $handler->clearGarbage();
            }
            unset($handler);
        }

        return true;
    }
}
