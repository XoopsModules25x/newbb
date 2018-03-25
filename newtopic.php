<?php
/**
 * NewBB 4.3x, the forum module for XOOPS project
 *
 * @copyright      XOOPS Project (http://xoops.org)
 * @license        http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author         Taiwen Jiang (phppp or D.J.) <phppp@users.sourceforge.net>
 * @since          4.00
 * @package        module::newbb
 */

include_once __DIR__ . '/header.php';

if (!$forum = XoopsRequest::getString('forum', '', 'GET')) {
    redirect_header(XOOPS_URL . '/index.php', 2, _MD_ERRORFORUM);
}

$forumHandler = xoops_getModuleHandler('forum');
$forum_obj    = $forumHandler->get($forum);
if (!$forumHandler->getPermission($forum_obj)) {
    redirect_header(XOOPS_URL . '/index.php', 2, _NOPERM);
}

$topicHandler = xoops_getModuleHandler('topic');
$topic_obj    = $topicHandler->create();
$topic_obj->setVar('forum_id', $forum);
if (!$topicHandler->getPermission($forum_obj, 0, 'post')) {
    /*
     * Build the page query
     */
    $query_vars  = ['forum', 'order', 'mode', 'viewmode'];
    $query_array = [];
    foreach ($query_vars as $var) {
        if (XoopsRequest::getString($var, '', 'GET')) {
            $query_array[$var] = "{$var}=" . XoopsRequest::getString($var, '', 'GET');
        }
    }
    $page_query = htmlspecialchars(implode('&', array_values($query_array)));
    unset($query_array);
    redirect_header(XOOPS_URL . "/modules/newbb/viewforum.php?{$page_query}", 2, _MD_NORIGHTTOPOST);
}

if ($GLOBALS['xoopsModuleConfig']['wol_enabled']) {
    $onlineHandler = xoops_getModuleHandler('online');
    $onlineHandler->init($forum_obj);
}

$xoopsOption['template_main']                                        = 'newbb_edit_post.tpl';
$GLOBALS['xoopsConfig']['module_cache'][$xoopsModule->getVar('mid')] = 0; // Disable cache
// irmtfan remove and move to footer.php
//$xoopsOption['xoops_module_header']= $xoops_module_header;
// irmtfan include header.php after defining $xoopsOption['template_main']
include_once $GLOBALS['xoops']->path('header.php');
//$xoopsTpl->assign('xoops_module_header', $xoops_module_header);

/*
$xoopsTpl->assign('lang_forum_index', sprintf(_MD_FORUMINDEX, htmlspecialchars($GLOBALS['xoopsConfig']['sitename'], ENT_QUOTES)));

$categoryHandler = xoops_getModuleHandler("category");
$category_obj = $categoryHandler->get($forum_obj->getVar("cat_id"), array("cat_title"));
$xoopsTpl->assign('category', array("id" => $forum_obj->getVar("cat_id"), "title" => $category_obj->getVar('cat_title')));
$xoopsTpl->assign("parentforum", $forumHandler->getParents($forum_obj));
$xoopsTpl->assign(array(
    'forum_id'             => $forum_obj->getVar('forum_id'),
    'forum_name'         => $forum_obj->getVar('forum_name'),
    ));

$form_title = _MD_POSTNEW;
$xoopsTpl->assign("form_title", $form_title);
*/

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
// irmtfan move to footer.php
include_once __DIR__ . '/footer.php';
include $GLOBALS['xoops']->path('footer.php');
