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

// Why the skip-DB-security check defined only for XMLRPC? We also need it!!! ~_*
if (!defined('XOOPS_XMLRPC')) {
    define('XOOPS_XMLRPC', 1);
}
ob_start();
require_once __DIR__ . '/header.php';
if (0 == $GLOBALS['xoopsModuleConfig']['email_digest']) {
    echo '<br>Not set';

    return false;
}
/** @var Newbb\DigestHandler $digestHandler */
$digestHandler = Newbb\Helper::getInstance()->getHandler('Digest');
$msg           = $digestHandler->process();
$msg           .= ob_get_contents();
ob_end_clean();
echo '<br>' . $msg;
