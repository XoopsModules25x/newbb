<?php
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
 * @license         GPL 2.0 or later
 * @package         newbb
 * @since           4.33
 * @min_xoops       2.5.8
 * @author          XOOPS Development Team - Email:<name@site.com> - Website:<https://xoops.org>
 */

use XoopsModules\Newbb;

// defined('XOOPS_ROOT_PATH') || die('Restricted access');

$moduleDirName = basename(dirname(__DIR__));

// require_once __DIR__ . '/../class/Helper.php';
require_once __DIR__ . '/../class/Utility.php';

$db     = \XoopsDatabaseFactory::getDatabaseConnection();
$helper = \XoopsModules\Newbb\Helper::getInstance();

/** @var \XoopsModules\Newbb\Utility $utility */
$utility = new Newbb\Utility();

define('NEWBB_DIRNAME', basename(dirname(__DIR__)));
define('NEWBB_URL', XOOPS_URL . '/modules/' . NEWBB_DIRNAME);
define('NEWBB_PATH', XOOPS_ROOT_PATH . '/modules/' . NEWBB_DIRNAME);
define('NEWBB_IMAGES_URL', NEWBB_URL . '/assets/images');
define('NEWBB_ADMIN_URL', NEWBB_URL . '/admin');
define('NEWBB_ADMIN_PATH', NEWBB_PATH . '/admin/index.php');
define('NEWBB_ROOT_PATH', $GLOBALS['xoops']->path('modules/' . NEWBB_DIRNAME));
define('NEWBB_AUTHOR_LOGOIMG', NEWBB_URL . '/assets/images/logo_module.png');
define('NEWBB_UPLOAD_URL', XOOPS_UPLOAD_URL . '/' . NEWBB_DIRNAME); // WITHOUT Trailing slash
define('NEWBB_UPLOAD_PATH', XOOPS_UPLOAD_PATH . '/' . NEWBB_DIRNAME); // WITHOUT Trailing slash

// module information
$mod_copyright = "<a href='https://xoops.org' title='XOOPS Project' target='_blank'>
                     <img src='" . NEWBB_AUTHOR_LOGOIMG . "' alt='XOOPS Project' /></a>";

$helper->loadLanguage('common');

require_once NEWBB_ROOT_PATH . '/class/Helper.php';

//$debug     = false;
//$helper = Newbb\Helper::getInstance($debug);

//This is needed or it will not work in blocks.
global $newbbIsAdmin;

// Load only if module is installed
if (is_object($helper->getModule())) {
    // Find if the user is admin of the module
    $publisherIsAdmin = Newbb\Utility::userIsAdmin();
}

//$db = \XoopsDatabaseFactory::getDatabaseConnection();

/** @var Newbb\CategoryHandler $categoryHandler */
$categoryHandler = $helper->getHandler('category');
/** @var Newbb\DigestHandler $digestHandler */
$digestHandler = $helper->getHandler('digest');
/** @var Newbb\ForumHandler $forumHandler */
$forumHandler = $helper->getHandler('forum');
/** @var Newbb\IconHandler $iconHandler */
$iconHandler = $helper->getHandler('icon');
/** @var Newbb\KarmaHandler $karmaHandler */
$karmaHandler = $helper->getHandler('karma');
/** @var Newbb\ModerateHandler $moderateHandler */
$moderateHandler = $helper->getHandler('moderate');
/** @var Newbb\OnlineHandler $onlineHandler */
$onlineHandler = $helper->getHandler('online');
/** var Newbb\PermissionHandler $permHandler */
$permHandler = $helper->getHandler('permission');
/** @var Newbb\PostHandler $postHandler */
$postHandler = $helper->getHandler('post');
/** @var Newbb\RateHandler $rateHandler */
$rateHandler = $helper->getHandler('rate');
/** @var Newbb\ReadHandler $readHandler */
//$readHandler = $helper->getHandler('read' . $type);
/** @var Newbb\ReadForumHandler $readForumHandler */
$readForumHandler = $helper->getHandler('readforum');
/** @var Newbb\ReadtopicHandler $readTopicHandler */
$readTopicHandler = $helper->getHandler('readtopic');
/** @var Newbb\ReportHandler $reportHandler */
$reportHandler = $helper->getHandler('report');
/** @var Newbb\StatsHandler $statsHandler */
$statsHandler = $helper->getHandler('stats');
/** @var Newbb\TextHandler $textHandler */
$textHandler = $helper->getHandler('text');
/** @var Newbb\TopicHandler $topicHandler */
$topicHandler = $helper->getHandler('topic');
/** @var Newbb\TypeHandler $typeHandler */
$typeHandler = $helper->getHandler('type');
/** @var Newbb\UserstatsHandler $userstatsHandler */
$userstatsHandler = $helper->getHandler('userstats');
/** @var Newbb\XmlrssHandler $xmlrssHandler */
$xmlrssHandler = $helper->getHandler('xmlrss');
