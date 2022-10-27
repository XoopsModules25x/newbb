<?php declare(strict_types=1);
/*
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 */

/**
 * @copyright    XOOPS Project (https://xoops.org)/
 * @license      GNU GPL 2.0 or later (https://www.gnu.org/licenses/gpl-2.0.html)
 * @author       Kazumi Ono (AKA onokazu) http://www.myweb.ne.jp/, https://xoops.org/, http://jp.xoops.org/
 * @author       XOOPS Development Team
 */

use Xmf\Module\Admin;
use XoopsModules\Newbb\{
    Common\Configurator,
    Helper
};

/** @var Helper $helper */
/** @var Admin $adminObject */

//require_once $GLOBALS['xoops']->path('include/cp_header.php');
require_once \dirname(__DIR__, 3) . '/include/cp_header.php';
require_once $GLOBALS['xoops']->path('modules/' . $xoopsModule->getVar('dirname') . '/include/vars.php');
require_once $GLOBALS['xoops']->path('modules/' . $xoopsModule->getVar('dirname') . '/include/functions.user.php');
require_once $GLOBALS['xoops']->path('modules/' . $xoopsModule->getVar('dirname') . '/include/functions.render.php');
//require_once $GLOBALS['xoops']->path('Frameworks/art/functions.php');
//require_once $GLOBALS['xoops']->path('Frameworks/art/functions.admin.php');

require_once \dirname(__DIR__) . '/include/common.php';

$helper = Helper::getInstance();

$adminObject = Admin::getInstance();

$configurator = new Configurator();

$myts = \MyTextSanitizer::getInstance();

if (!isset($GLOBALS['xoopsTpl']) || !($GLOBALS['xoopsTpl'] instanceof \XoopsTpl)) {
    require_once $GLOBALS['xoops']->path('class/template.php');
    $xoopsTpl = new \XoopsTpl();
}

$pathIcon16    = Admin::iconUrl('', '16');
$pathIcon32    = Admin::iconUrl('', '32');
$pathModIcon32 = $helper->getModule()->getInfo('modicons32');

// Local icons path
$xoopsTpl->assign('pathModIcon16', $pathIcon16);
$xoopsTpl->assign('pathModIcon32', $pathIcon32);

// Load language files
$helper->loadLanguage('admin');
$helper->loadLanguage('modinfo');
$helper->loadLanguage('main');
