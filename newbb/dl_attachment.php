<?php
/**
 * CBB 4.0, or newbb, the forum module for XOOPS project
 *
 * @copyright    The XOOPS Project http://xoops.sf.net
 * @license        http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author        Taiwen Jiang (phppp or D.J.) <phppp@users.sourceforge.net>
 * @since        4.00
 * @version        $Id $
 * @package        module::newbb
 */

ob_start();
include_once __DIR__ . "/header.php";
include $GLOBALS['xoops']->path('header.php');

$attach_id = XoopsRequest::getString('attachid', '', 'GET');
$post_id   = XoopsRequest::getInt('post_id', 0, 'GET');

if (!$post_id || !$attach_id) {
    die(_MD_NO_SUCH_FILE . ': post_id:' . $post_id . '; attachid' . $attachid);
}

$postHandler =& xoops_getmodulehandler('post', 'newbb');
$forumpost    =& $postHandler->get($post_id);
if (!$approved = $forumpost->getVar('approved')) {
    die(_MD_NORIGHTTOVIEW);
}
$topicHandler =& xoops_getmodulehandler('topic', 'newbb');
$topic_obj     =& $topicHandler->getByPost($post_id);
$topic_id      = $topic_obj->getVar('topic_id');
if (!$approved = $topic_obj->getVar('approved')) {
    die(_MD_NORIGHTTOVIEW);
}
$forumHandler =& xoops_getmodulehandler('forum', 'newbb');
$forum_obj     =& $forumHandler->get($topic_obj->getVar('forum_id'));
if (!$forumHandler->getPermission($forum_obj)) {
    die(_MD_NORIGHTTOACCESS);
}
if (!$topicHandler->getPermission($forum_obj, $topic_obj->getVar('topic_status'), "view")) {
    die(_MD_NORIGHTTOVIEW);
}

$attachments = $forumpost->getAttachment();
$attach      = $attachments[$attach_id];
if (!$attach) {
    die(_MD_NO_SUCH_FILE);
}
$file_saved = $GLOBALS['xoops']->path($GLOBALS['xoopsModuleConfig']['dir_attachments'] . '/' . $attach['name_saved']);
if (!file_exists($file_saved)) {
    die(_MD_NO_SUCH_FILE);
}
if ($down = $forumpost->incrementDownload($attach_id)) {
    $forumpost->saveAttachment();
}
unset($forumpost);
$msg = ob_get_contents();
ob_end_clean();

$xoopsLogger->activated = false;
if (!empty($GLOBALS["xoopsModuleConfig"]["download_direct"])) {
    header("Cache-Control: no-store, no-cache, must-revalidate");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");
    header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
    header("location: " . XOOPS_URL . '/' . $GLOBALS['xoopsModuleConfig']['dir_attachments'] . '/' . $attach['name_saved']);
} else {
    $file_display = $attach['nameDisplay'];
//$mimetype = $attach['mimetype'];

    if (ini_get('zlib.output_compression')) {
        @ini_set('zlib.output_compression', 'Off');
    }

    if (function_exists('mb_http_output')) {
        mb_http_output('pass');
    }
    header('Expires: 0');
//header('Content-Type: '.$mimetype);
    header('Content-Type: application/octet-stream');
    if (preg_match("/MSIE ([0-9]\.[0-9]{1,2})/", $_SERVER["HTTP_USER_AGENT"])) {
        header('Content-Disposition: attachment; filename="' . $file_display . '"');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
    } else {
        header('Content-Disposition: attachment; filename="' . $file_display . '"');
        header('Pragma: no-cache');
    }
    header("Content-Type: application/force-download");
    header("Content-Transfer-Encoding: binary");

    $handle = fopen($file_saved, "rb");
    while (!feof($handle)) {
        $buffer = fread($handle, 4096);
        echo $buffer;
    }
    fclose($handle);
}
