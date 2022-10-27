<?php declare(strict_types=1);
/*
 You may not change or alter any portion of this comment or credits
 of supporting developers from this source code or any supporting source code
 which is considered copyrighted (c) material of the original comment or credit authors.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
*/

/**
 * NewBB module for xoops
 *
 * @copyright       XOOPS Project (https://xoops.org)
 * @license         GNU GPL 2.0 or later (https://www.gnu.org/licenses/gpl-2.0.html)
 * @since           4.33
 * @min_xoops       2.5.8
 * @author          XOOPS Development Team - Email:<name@site.com> - Website:<https://xoops.org>
 */

use Xmf\Module\Admin;
use XoopsModules\Newbb\{
    Helper,
    Utility
};

/** @var Helper $helper */
/** @var Utility $utility */
/** @var Admin $adminObject */
require_once \dirname(__DIR__) . '/preloads/autoloader.php';

$moduleDirName      = \basename(\dirname(__DIR__));
$moduleDirNameUpper = \mb_strtoupper($moduleDirName);

$db      = \XoopsDatabaseFactory::getDatabaseConnection();
$helper  = Helper::getInstance();
$utility = new Utility();

$helper->loadLanguage('common');

//define('NEWBB_DIRNAME', basename(dirname(__DIR__)));
//define('NEWBB_URL', XOOPS_URL . '/modules/' . NEWBB_DIRNAME);
//define('NEWBB_PATH', XOOPS_ROOT_PATH . '/modules/' . NEWBB_DIRNAME);
//define('NEWBB_IMAGES_URL', NEWBB_URL . '/assets/images');
//define('NEWBB_ADMIN_URL', NEWBB_URL . '/admin');
//define('NEWBB_ADMIN_PATH', NEWBB_PATH . '/admin/index.php');
//define('NEWBB_ROOT_PATH', $GLOBALS['xoops']->path('modules/' . NEWBB_DIRNAME));
//define('NEWBB_AUTHOR_LOGOIMG', NEWBB_URL . '/assets/images/logo_module.png');
//define('NEWBB_UPLOAD_URL', XOOPS_UPLOAD_URL . '/' . NEWBB_DIRNAME); // WITHOUT Trailing slash
//define('NEWBB_UPLOAD_PATH', XOOPS_UPLOAD_PATH . '/' . NEWBB_DIRNAME); // WITHOUT Trailing slash

if (!defined($moduleDirNameUpper . '_CONSTANTS_DEFINED')) {
    define($moduleDirNameUpper . '_DIRNAME', basename(dirname(__DIR__)));
    define($moduleDirNameUpper . '_ROOT_PATH', XOOPS_ROOT_PATH . '/modules/' . $moduleDirName . '/');
    define($moduleDirNameUpper . '_PATH', XOOPS_ROOT_PATH . '/modules/' . $moduleDirName . '/');
    define($moduleDirNameUpper . '_URL', XOOPS_URL . '/modules/' . $moduleDirName . '/');
    define($moduleDirNameUpper . '_IMAGE_URL', constant($moduleDirNameUpper . '_URL') . '/assets/images/');
    define($moduleDirNameUpper . '_IMAGE_PATH', constant($moduleDirNameUpper . '_ROOT_PATH') . '/assets/images');
    define($moduleDirNameUpper . '_ADMIN_URL', constant($moduleDirNameUpper . '_URL') . '/admin/');
    define($moduleDirNameUpper . '_ADMIN_PATH', constant($moduleDirNameUpper . '_ROOT_PATH') . '/admin/');
    define($moduleDirNameUpper . '_ADMIN', constant($moduleDirNameUpper . '_URL') . '/admin/index.php');
    define($moduleDirNameUpper . '_AUTHOR_LOGOIMG', constant($moduleDirNameUpper . '_URL') . '/assets/images/logoModule.png');
    define($moduleDirNameUpper . '_UPLOAD_URL', XOOPS_UPLOAD_URL . '/' . $moduleDirName); // WITHOUT Trailing slash
    define($moduleDirNameUpper . '_UPLOAD_PATH', XOOPS_UPLOAD_PATH . '/' . $moduleDirName); // WITHOUT Trailing slash
    define($moduleDirNameUpper . '_CONSTANTS_DEFINED', 1);
}

// module information
//$mod_copyright = "<a href='https://xoops.org' title='XOOPS Project' target='_blank'>
//                     <img src='" . NEWBB_AUTHOR_LOGOIMG . "' alt='XOOPS Project' ></a>";
//

//This is needed or it will not work in blocks.
//global $newbbIsAdmin;

// Load only if module is installed
//if (is_object($helper->getModule())) {
//    // Find if the user is admin of the module
//    $publisherIsAdmin = Utility::userIsAdmin();
//}

//$db = \XoopsDatabaseFactory::getDatabaseConnection();

/** @var Newbb\CategoryHandler $categoryHandler */
$categoryHandler = $helper->getHandler('Category');
/** @var Newbb\DigestHandler $digestHandler */
$digestHandler = $helper->getHandler('Digest');
/** @var Newbb\ForumHandler $forumHandler */
$forumHandler = $helper->getHandler('Forum');
/** @var Newbb\IconHandler $iconHandler */
$iconHandler = $helper->getHandler('Icon');
/** @var Newbb\KarmaHandler $karmaHandler */
$karmaHandler = $helper->getHandler('Karma');
/** @var Newbb\ModerateHandler $moderateHandler */
$moderateHandler = $helper->getHandler('Moderate');
/** @var Newbb\OnlineHandler $onlineHandler */
$onlineHandler = $helper->getHandler('Online');
/** var Newbb\PermissionHandler $permHandler */
$permHandler = $helper->getHandler('Permission');
/** @var Newbb\PostHandler $postHandler */
$postHandler = $helper->getHandler('Post');
/** @var Newbb\RateHandler $rateHandler */
$rateHandler = $helper->getHandler('Rate');
/** @var Newbb\ReadHandler $readHandler */
//$readHandler = $helper->getHandler('Read' . $type);
/** @var Newbb\ReadforumHandler $ReadforumHandler */
$ReadforumHandler = $helper->getHandler('Readforum');
/** @var Newbb\ReadtopicHandler $readTopicHandler */
$readTopicHandler = $helper->getHandler('Readtopic');
/** @var Newbb\ReportHandler $reportHandler */
$reportHandler = $helper->getHandler('Report');
/** @var Newbb\StatsHandler $statsHandler */
$statsHandler = $helper->getHandler('Stats');
/** @var Newbb\TextHandler $textHandler */
$textHandler = $helper->getHandler('Text');
/** @var Newbb\TopicHandler $topicHandler */
$topicHandler = $helper->getHandler('Topic');
/** @var Newbb\TypeHandler $typeHandler */
$typeHandler = $helper->getHandler('Type');
/** @var Newbb\UserstatsHandler $userstatsHandler */
$userstatsHandler = $helper->getHandler('Userstats');
/** @var Newbb\XmlrssHandler $xmlrssHandler */
$xmlrssHandler = $helper->getHandler('Xmlrss');

$pathIcon16 = Admin::iconUrl('', '16');
$pathIcon32 = Admin::iconUrl('', '32');
//$pathModIcon16 = $helper->getModule()->getInfo('modicons16');
//$pathModIcon32 = $helper->getModule()->getInfo('modicons32');

$icons = [
    'edit'    => "<img src='" . $pathIcon16 . "/edit.png'  alt=" . _EDIT . "' align='middle'>",
    'delete'  => "<img src='" . $pathIcon16 . "/delete.png' alt='" . _DELETE . "' align='middle'>",
    'clone'   => "<img src='" . $pathIcon16 . "/editcopy.png' alt='" . _CLONE . "' align='middle'>",
    'preview' => "<img src='" . $pathIcon16 . "/view.png' alt='" . _PREVIEW . "' align='middle'>",
    'print'   => "<img src='" . $pathIcon16 . "/printer.png' alt='" . _CLONE . "' align='middle'>",
    'pdf'     => "<img src='" . $pathIcon16 . "/pdf.png' alt='" . _CLONE . "' align='middle'>",
    'add'     => "<img src='" . $pathIcon16 . "/add.png' alt='" . _ADD . "' align='middle'>",
    '0'       => "<img src='" . $pathIcon16 . "/0.png' alt='" . 0 . "' align='middle'>",
    '1'       => "<img src='" . $pathIcon16 . "/1.png' alt='" . 1 . "' align='middle'>",
];

//when debugging, change to true
$debug = false;
//$debug = true;

// MyTextSanitizer object
$myts = \MyTextSanitizer::getInstance();

if (!isset($GLOBALS['xoopsTpl']) || !($GLOBALS['xoopsTpl'] instanceof \XoopsTpl)) {
    require_once $GLOBALS['xoops']->path('class/template.php');
    $GLOBALS['xoopsTpl'] = new \XoopsTpl();
}

$GLOBALS['xoopsTpl']->assign('mod_url', $helper->url());
// Local icons path
if (is_object($helper->getModule())) {
    $pathModIcon16 = $helper->getModule()->getInfo('modicons16');
    $pathModIcon32 = $helper->getModule()->getInfo('modicons32');

    $GLOBALS['xoopsTpl']->assign('pathModIcon16', XOOPS_URL . '/modules/' . $moduleDirName . '/' . $pathModIcon16);
    $GLOBALS['xoopsTpl']->assign('pathModIcon32', $pathModIcon32);
}

xoops_loadLanguage('main', $moduleDirName);
if (class_exists('D3LanguageManager')) {
    require_once XOOPS_TRUST_PATH . "/libs/altsys/class/D3LanguageManager.class.php";
    $langman = D3LanguageManager::getInstance();
    $langman->read('main.php', $moduleDirName);
}

