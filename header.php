<?php
/**
 * NewBB 5.0x,  the forum module for XOOPS project
 *
 * @copyright      XOOPS Project (http://xoops.org)
 * @license        GNU GPL 2 or later (http://www.gnu.org/licenses/gpl-2.0.html)
 * @author         Taiwen Jiang (phppp or D.J.) <phppp@users.sourceforge.net>
 * @since          4.00
 * @package        module::newbb
 */

use Xmf\Request;

include dirname(dirname(__DIR__)) . '/mainfile.php';
// defined('XOOPS_ROOT_PATH') || exit('XOOPS root path not defined');
/** @var \XoopsLogger $xoopsLogger */
$xoopsLogger->startTime('newBB_Header');
// irmtfan assign newbb dirname then replace all. include xoops header.php (now commented and removed)
//$dirname = $xoopsModule->getVar('dirname');
$moduleDirName = basename(__DIR__);
//include_once $GLOBALS['xoops']->path('header.php');

if (!empty($GLOBALS['xoopsModuleConfig']['do_rewrite'])) {
    include_once __DIR__ . '/seo_url.php';
    /* for seo */
    $toseo_url = ['index.php', 'viewforum.php', 'viewtopic.php', 'rss.php'];

    if (!empty($GLOBALS['xoopsModuleConfig']['do_rewrite']) && (!isset($_POST) || count($_POST) <= 0)
        && (strpos(getenv('REQUEST_URI'), '.html') === false)) {
        $redir = false;
        if (strpos(getenv('REQUEST_URI'), 'mark_read=') === true || strpos(getenv('REQUEST_URI'), 'mark=') === true) {
            // Mark Forums
        } else {
            if (in_array(basename(getenv('SCRIPT_NAME')), $toseo_url)) {
                //rewrite only for files

                if (trim(getenv('SCRIPT_NAME')) !== '') {
                    if (strpos(getenv('REQUEST_URI'), '/' . SEO_MODULE_NAME . '/') === false) {
                        $redir = true;
                    } elseif (getenv('QUERY_STRING')) {
                        $redir = true;
                    }
                }
            }
        }

        if ($redir === true) {
            $s      = 'http://' . getenv('HTTP_HOST') . getenv('REQUEST_URI');
            $s      = str_replace('/' . REAL_MODULE_NAME . '/', '/' . SEO_MODULE_NAME . '/', $s);
            $newurl = seo_urls('<a href="' . $s . '"></a>');
            $newurl = str_replace('<a href="', '', $newurl);
            $newurl = str_replace('"></a>', '', $newurl);
            if (!headers_sent()) {
                header('HTTP/1.1 301 Moved Permanently');
                header("Location: $newurl");
                exit();
            }
        }
    }
}

include_once $GLOBALS['xoops']->path('modules/' . $moduleDirName . '/include/vars.php');

include_once __DIR__ . '/include/functions.user.php';
include_once __DIR__ . '/include/functions.topic.php';

require_once $GLOBALS['xoops']->path('class/xoopsformloader.php');
require_once $GLOBALS['xoops']->path('class/module.textsanitizer.php');
$myts = MyTextSanitizer::getInstance();

$menumode       = 0;
$menumode_other = [];
$menu_url       = htmlspecialchars(preg_replace('/&menumode=[^&]/', '', Request::getString('REQUEST_URI','','SERVER')));
$menu_url       .= (false === strpos($menu_url, '?')) ? '?menumode=' : '&amp;menumode=';
//foreach ($GLOBALS['xoopsModuleConfig']['valid_menumodes'] as $key => $val) {
//    if ($key !== $menumode) {
//        $menumode_other[] = array('title' => $val, 'link' => $menu_url . $key);
//    }
//}

if (is_object($GLOBALS['xoopsUser']) && !empty($GLOBALS['xoopsModuleConfig']['welcome_forum'])
    && !$GLOBALS['xoopsUser']->getVar('posts')) {
    include_once __DIR__ . '/include/functions.welcome.php';
}
// irmtfan for backward compatibility
$pollmodules = $GLOBALS['xoopsModuleConfig']['poll_module'];

/** @var \XoopsModuleHandler $moduleHandler */
$moduleHandler = xoops_getHandler('module');
$xoopspoll     = $moduleHandler->getByDirname($pollmodules);

$xoopsLogger->stopTime('newBB_Header');
