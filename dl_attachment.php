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
use XoopsModules\Newbb;

ob_start();
require_once __DIR__ . '/header.php';
include $GLOBALS['xoops']->path('header.php');

$attach_id = Request::getInt('attachid', 0, 'GET');
$post_id   = Request::getInt('post_id', 0, 'GET');

if (!$post_id || !$attach_id) {
    exit(_MD_NEWBB_NO_SUCH_FILE . ': post_id:' . $post_id . '; attachid' . $attachid);
}

///** @var Newbb\PostHandler $postHandler */
//$postHandler = Newbb\Helper::getInstance()->getHandler('Post');

/** @var Newbb\Post $forumpost */
$forumpost = $postHandler->get($post_id);
if (!$approved = $forumpost->getVar('approved')) {
    exit(_MD_NEWBB_NORIGHTTOVIEW);
}
///** @var TopicHandler $topicHandler */
//$topicHandler = Newbb\Helper::getInstance()->getHandler('Topic');
$topicObject = $topicHandler->getByPost($post_id);
$topic_id    = $topicObject->getVar('topic_id');
if (!$approved = $topicObject->getVar('approved')) {
    exit(_MD_NEWBB_NORIGHTTOVIEW);
}
///** @var NewbbForumHandler $forumHandler */
//$forumHandler = Newbb\Helper::getInstance()->getHandler('Forum');
$forumObject = $forumHandler->get($topicObject->getVar('forum_id'));
if (!$forumHandler->getPermission($forumObject)) {
    exit(_MD_NEWBB_NORIGHTTOACCESS);
}
if (!$topicHandler->getPermission($forumObject, $topicObject->getVar('topic_status'), 'view')) {
    exit(_MD_NEWBB_NORIGHTTOVIEW);
}

$attachments = $forumpost->getAttachment();
$attach      = $attachments[$attach_id];
if (!$attach) {
    exit(_MD_NEWBB_NO_SUCH_FILE);
}
$file_saved = $GLOBALS['xoops']->path($GLOBALS['xoopsModuleConfig']['dir_attachments'] . '/' . $attach['name_saved']);
if (!file_exists($file_saved)) {
    exit(_MD_NEWBB_NO_SUCH_FILE);
}
if ($down = $forumpost->incrementDownload($attach_id)) {
    $forumpost->saveAttachment();
}
unset($forumpost);
$msg = ob_get_contents();
ob_end_clean();

$xoopsLogger->activated = false;
if (!empty($GLOBALS['xoopsModuleConfig']['download_direct'])) {
    header('Cache-Control: no-store, no-cache, must-revalidate');
    header('Cache-Control: post-check=0, pre-check=0', false);
    header('Pragma: no-cache');
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
    header('location: ' . XOOPS_URL . '/' . $GLOBALS['xoopsModuleConfig']['dir_attachments'] . '/' . $attach['name_saved']);
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
    if (preg_match("/MSIE (\d\.\d{1,2})/", Request::getString('HTTP_USER_AGENT', '', 'SERVER'))) {
        header('Content-Disposition: attachment; filename="' . $file_display . '"');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
    } else {
        header('Content-Disposition: attachment; filename="' . $file_display . '"');
        header('Pragma: no-cache');
    }
    header('Content-Type: application/force-download');
    header('Content-Transfer-Encoding: binary');

    $handle = fopen($file_saved, 'rb');
    while (!feof($handle)) {
        $buffer = fread($handle, 4096);
        echo $buffer;
    }
    fclose($handle);
}
