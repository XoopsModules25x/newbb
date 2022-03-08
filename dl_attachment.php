<?php declare(strict_types=1);

/**
 * NewBB 5.0x,  the forum module for XOOPS project
 *
 * @copyright      XOOPS Project (https://xoops.org)
 * @license        GNU GPL 2.0 or later (https://www.gnu.org/licenses/gpl-2.0.html)
 * @author         Taiwen Jiang (phppp or D.J.) <phppp@users.sourceforge.net>
 * @since          4.00
 */

use Xmf\Request;
use XoopsModules\Newbb\{
    ForumHandler,
    Post,
    PostHandler,
    TopicHandler
};
/** @var Post $forumpost */
/** @var PostHandler $postHandler */
/** @var TopicHandler $topicHandler */
/** @var ForumHandler $forumHandler */
ob_start();
require_once __DIR__ . '/header.php';
require_once $GLOBALS['xoops']->path('header.php');

$attachId = Request::getInt('attachid', 0, 'GET');
$postId   = Request::getInt('post_id', 0, 'GET');

if (!$postId || !$attachId) {
    exit(_MD_NEWBB_NO_SUCH_FILE . ': post_id:' . $postId . '; attachid' . $attachId);
}

//$postHandler = \XoopsModules\Newbb\Helper::getInstance()->getHandler('Post');
$forumpost = $postHandler->get($postId);
if (!$approved = $forumpost->getVar('approved')) {
    exit(_MD_NEWBB_NORIGHTTOVIEW);
}
//$topicHandler = \XoopsModules\Newbb\Helper::getInstance()->getHandler('Topic');
$topicObject = $topicHandler->getByPost($postId);
$topic_id    = $topicObject->getVar('topic_id');
if (!$approved = $topicObject->getVar('approved')) {
    exit(_MD_NEWBB_NORIGHTTOVIEW);
}
//$forumHandler = \XoopsModules\Newbb\Helper::getInstance()->getHandler('Forum');
$forumObject = $forumHandler->get($topicObject->getVar('forum_id'));
if (!$forumHandler->getPermission($forumObject)) {
    exit(_MD_NEWBB_NORIGHTTOACCESS);
}
if (!$topicHandler->getPermission($forumObject, $topicObject->getVar('topic_status'), 'view')) {
    exit(_MD_NEWBB_NORIGHTTOVIEW);
}

$attachments = $forumpost->getAttachment();
$attach      = $attachments[$attachId];
if (!$attach) {
    exit(_MD_NEWBB_NO_SUCH_FILE);
}
$file_saved = $GLOBALS['xoops']->path($GLOBALS['xoopsModuleConfig']['dir_attachments'] . '/' . $attach['name_saved']);
if (!file_exists($file_saved)) {
    exit(_MD_NEWBB_NO_SUCH_FILE);
}
$down = $forumpost->incrementDownload($attachId);
if ($down) {
    $forumpost->saveAttachment();
}
unset($forumpost);
$msg = ob_get_clean();

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
        if (false === @ini_set('zlib.output_compression', 'Off')) {
            throw new \RuntimeException('Setting of zlib.output_compression failed.');
        }
    }

    if (function_exists('mb_http_output')) {
        mb_http_output('pass');
    }
    header('Expires: 0');
    //header('Content-Type: '.$mimetype);
    header('Content-Type: application/octet-stream');
    if (preg_match('/MSIE (\d\.\d{1,2})/', Request::getString('HTTP_USER_AGENT', '', 'SERVER'))) {
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
