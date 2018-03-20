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

use Xmf\Request;

require_once __DIR__ . '/header.php';

if (!$forum = Request::getString('forum', '', 'GET')) {
    redirect_header(XOOPS_URL . '/index.php', 2, _MD_NEWBB_ERRORFORUM);
}

///** @var Newbb\ForumHandler $forumHandler */
//$forumHandler = Newbb\Helper::getInstance()->getHandler('Forum');
$forumObject = $forumHandler->get($forum);
if (!$forumHandler->getPermission($forumObject)) {
    redirect_header(XOOPS_URL . '/index.php', 2, _NOPERM);
}

///** @var Newbb\TopicHandler $topicHandler */
//$topicHandler = Newbb\Helper::getInstance()->getHandler('Topic');
$topicObject = $topicHandler->create();
$topicObject->setVar('forum_id', $forum);
if (!$topicHandler->getPermission($forumObject, 0, 'post')) {
    /*
     * Build the page query
     */
    $query_vars  = ['forum', 'order', 'mode', 'viewmode'];
    $query_array = [];
    foreach ($query_vars as $var) {
        if (Request::getString($var, '', 'GET')) {
            $query_array[$var] = "{$var}=" . Request::getString($var, '', 'GET');
        }
    }
    $page_query = htmlspecialchars(implode('&', array_values($query_array)));
    unset($query_array);
    redirect_header(XOOPS_URL . "/modules/newbb/viewforum.php?{$page_query}", 2, _MD_NEWBB_NORIGHTTOPOST);
}

if ($GLOBALS['xoopsModuleConfig']['wol_enabled']) {
    //    /** @var Newbb\OnlineHandler $onlineHandler */
    //    $onlineHandler = Newbb\Helper::getInstance()->getHandler('Online');
    $onlineHandler->init($forumObject);
}

$xoopsOption['template_main']                                        = 'newbb_edit_post.tpl';
$GLOBALS['xoopsConfig']['module_cache'][$xoopsModule->getVar('mid')] = 0; // Disable cache
require_once $GLOBALS['xoops']->path('header.php');

if (1 == $GLOBALS['xoopsModuleConfig']['disc_show'] || 3 == $GLOBALS['xoopsModuleConfig']['disc_show']) {
    $xoopsTpl->assign('disclaimer', $GLOBALS['xoopsModuleConfig']['disclaimer']);
}

$subject       = '';
$message       = '';
$dohtml        = 1;
$dosmiley      = 1;
$doxcode       = 1;
$dobr          = 1;
$icon          = '';
$post_karma    = 0;
$require_reply = 0;
$attachsig     = (is_object($GLOBALS['xoopsUser']) && $GLOBALS['xoopsUser']->getVar('attachsig')) ? 1 : 0;
$post_id       = 0;
$topic_id      = 0;
include __DIR__ . '/include/form.post.php';

require_once __DIR__ . '/footer.php';
include $GLOBALS['xoops']->path('footer.php');
