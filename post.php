<?php
/**
 * Newbb module
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright       XOOPS Project (https://xoops.org)
 * @license         GNU GPL 2 or later (http://www.gnu.org/licenses/gpl-2.0.html)
 * @package         newbb
 * @since           4.0
 * @author          Taiwen Jiang <phppp@users.sourceforge.net>
 */

use Xmf\Request;
use XoopsModules\Newbb;

require_once __DIR__ . '/header.php';

foreach ([
             'forum',
             'topic_id',
             'post_id',
             'order',
             'pid',
             'start',
             'isreply',
             'isedit'
         ] as $getint) {
    ${$getint} = Request::getInt($getint, 0, 'POST');
}

$op       = Request::getCmd('op', '', 'POST');
$viewmode = ('flat' !== Request::getString('viewmode', '', 'POST')) ? 'thread' : 'flat';
if (empty($forum)) {
    redirect_header('index.php', 2, _MD_NEWBB_ERRORFORUM);
}

///** @var Newbb\ForumHandler $forumHandler */
//$forumHandler = Newbb\Helper::getInstance()->getHandler('Forum');
///** @var Newbb\TopicHandler $topicHandler */
//$topicHandler = Newbb\Helper::getInstance()->getHandler('Topic');
///** @var Newbb\PostHandler $postHandler */
//$postHandler = Newbb\Helper::getInstance()->getHandler('Post');

if (!empty($isedit) && $post_id > 0) {
    /** @var Post $postObject */
    $postObject = $postHandler->get($post_id);
    $topic_id   = $postObject->getVar('topic_id');
} else {
    $postObject = $postHandler->create();
}
$topicObject = $topicHandler->get($topic_id);
$forum_id    = $topic_id ? $topicObject->getVar('forum_id') : $forum;
$forumObject = $forumHandler->get($forum_id);
if (!$forumHandler->getPermission($forumObject)) {
    redirect_header('index.php', 2, _NOPERM);
}

if ($GLOBALS['xoopsModuleConfig']['wol_enabled']) {
    //    /** @var Newbb\OnlineHandler $onlineHandler */
    //    $onlineHandler = Newbb\Helper::getInstance()->getHandler('Online');
    $onlineHandler->init($forumObject);
}

$error_message = [];

if (Request::getString('contents_submit', '', 'POST')) {
    $token_valid = false;
    $token_valid = $GLOBALS['xoopsSecurity']->check();

    $captcha_invalid = false;
    if (!is_object($GLOBALS['xoopsUser']) && Request::hasVar('uname', 'POST') && Request::hasVar('pass', 'POST')) {
        $uname = Request::getString('uname', '', 'POST');
        $pass  = Request::getString('pass', '', 'POST');
        /** @var \XoopsMemberHandler $memberHandler */
        $memberHandler = xoops_getHandler('member');
        $user          = $memberHandler->loginUser($uname, $pass);
        if (is_object($user) && 0 < $user->getVar('level')) {
            if (Request::getString('login', '', 'POST')) {
                $user->setVar('last_login', time());
                if (!$memberHandler->insertUser($user)) {
                }
                $_SESSION                    = [];
                $_SESSION['xoopsUserId']     = $user->getVar('uid');
                $_SESSION['xoopsUserGroups'] = $user->getGroups();
                if ($GLOBALS['xoopsConfig']['use_mysession'] && '' !== $GLOBALS['xoopsConfig']['session_name']) {
                    setcookie($GLOBALS['xoopsConfig']['session_name'], session_id(), time() + (60 * $GLOBALS['xoopsConfig']['session_expire']), '/', '', 0);
                }
                $user_theme = $user->getVar('theme');
                if (in_array($user_theme, $GLOBALS['xoopsConfig']['theme_set_allowed'])) {
                    $_SESSION['xoopsUserTheme'] = $user_theme;
                }
            }
            $GLOBALS['xoopsUser'] = $user;
            $xoopsUserIsAdmin     = $GLOBALS['xoopsUser']->isAdmin($xoopsModule->getVar('mid'));
        }
    }
    if (!is_object($GLOBALS['xoopsUser'])) {
        xoops_load('captcha');
        $xoopsCaptcha = \XoopsCaptcha::getInstance();
        if (!$xoopsCaptcha->verify()) {
            $captcha_invalid = true;
            $error_message[] = $xoopsCaptcha->getMessage();
        }
    }

    $isAdmin = newbbIsAdmin($forumObject);

    $time_valid = true;
    if (!$isAdmin && !empty($GLOBALS['xoopsModuleConfig']['post_timelimit'])) {
        $last_post = newbbGetSession('LP');
        if (time() - $last_post < $GLOBALS['xoopsModuleConfig']['post_timelimit']) {
            $time_valid = false;
        }
    }

    if ($captcha_invalid || !$token_valid || !$time_valid) {
        $_POST['contents_preview'] = 1;
        $_POST['contents_submit']  = null;
        $_POST['contents_upload']  = null;
        if (!$token_valid) {
            $error_message[] = _MD_NEWBB_INVALID_SUBMIT;
        }
        if (!$time_valid) {
            $error_message[] = sprintf(_MD_NEWBB_POSTING_LIMITED, $GLOBALS['xoopsModuleConfig']['post_timelimit']);
        }
    }
}

if (Request::getString('contents_submit', '', 'POST')) {
    $message = Request::getText('message', '', 'POST');
    if (empty($message)) {
        // irmtfan - issue with javascript:history.go(-1) - add error message
        redirect_header(Request::getString('HTTP_REFERER', '', 'SERVER'), 1, _MD_NEWBB_ERROR_BACK);
    }
    if (!empty($isedit) && $post_id > 0) {
        $uid = is_object($GLOBALS['xoopsUser']) ? $GLOBALS['xoopsUser']->getVar('uid') : 0;

        $topic_status = $topicObject->getVar('topic_status');
        if ($topicHandler->getPermission($forumObject, $topic_status, 'edit')
            && ($isAdmin
                || ($postObject->checkTimelimit('edit_timelimit')
                    && $postObject->checkIdentity()))) {
        } else {
            redirect_header(XOOPS_URL . "/modules/newbb/viewtopic.php?forum={$forum_id}&amp;topic_id={$topic_id}&amp;post_id={$post_id}&amp;order={$order}&amp;viewmode={$viewmode}", 2, _MD_NEWBB_NORIGHTTOEDIT);
        }

        $delete_attach = Request::getArray('delete_attach', [], 'POST');
        if (is_array($delete_attach) && count($delete_attach) > 0) {
            $postObject->deleteAttachment($delete_attach);
        }
    } else {
        if ($topic_id) {
            $topic_status = $topicObject->getVar('topic_status');
            if (!$topicHandler->getPermission($forumObject, $topic_status, 'reply')) {
                redirect_header(XOOPS_URL . "/modules/newbb/viewtopic.php?forum={$forum_id}&amp;topic_id={$topic_id}&amp;post_id={$post_id}&amp;order={$order}&amp;viewmode={$viewmode}", 2, _MD_NEWBB_NORIGHTTOREPLY);
            }
        } else {
            $topic_status = 0;
            if (!$topicHandler->getPermission($forumObject, $topic_status, 'post')) {
                redirect_header(XOOPS_URL . "/modules/newbb/viewtopic.php?forum={$forum_id}", 2, _MD_NEWBB_NORIGHTTOPOST);
            }
        }

        $isreply = 0;
        $isnew   = 1;
        if (!is_object($GLOBALS['xoopsUser'])
            || (Request::getString('noname', '', 'POST')
                && !empty($GLOBALS['xoopsModuleConfig']['allow_user_anonymous']))) {
            $uid = 0;
        } else {
            $uid = $GLOBALS['xoopsUser']->getVar('uid');
        }
        if (!empty($pid)) {
            $postObject->setVar('pid', $pid);
        }
        if (!empty($topic_id)) {
            $postObject->setVar('topic_id', $topic_id);
            $isreply = 1;
        }
        $postObject->setVar('poster_ip', Xmf\IPAddress::fromRequest()->asReadable());
        $postObject->setVar('uid', $uid);
        $postObject->setVar('post_time', time());
    }

    $approved = $topicHandler->getPermission($forumObject, $topic_status, 'noapprove');
    $postObject->setVar('approved', $approved);

    $postObject->setVar('forum_id', $forumObject->getVar('forum_id'));

    $subject       = xoops_trim(Request::getString('subject', '', 'POST'));
    $subject       = ('' === $subject) ? _NOTITLE : $subject;
    $poster_name   = xoops_trim(Request::getString('poster_name', '', 'POST'));
    $dohtml        = Request::getInt('dohtml', 0, 'POST')
                     && $topicHandler->getPermission($forumObject, $topic_status, 'html');
    $dosmiley      = Request::getInt('dosmiley', 0, 'POST');
    $doxcode       = Request::getInt('doxcode', 0, 'POST') ? 1 : 0;
    $dobr          = Request::getInt('dobr', 0, 'POST') ? 1 : 0;
    $icon          = (Request::getString('icon', '', 'POST')
                      && is_file($GLOBALS['xoops']->path('images/subject/' . Request::getString('icon', '', 'POST'))) ? Request::getString('icon', '', 'POST') : '');
    $attachsig     = Request::getBool('attachsig', false, 'POST')
                     && $topicHandler->getPermission($forumObject, $topic_status, 'signature');
    $view_require  = Request::getString('view_require', '', 'POST');
    $post_karma    = ('require_karma' === $view_require) ? Request::getInt('post_karma', 0, 'POST') : 0;
    $require_reply = ('require_reply' === $view_require);
    $postObject->setVar('subject', $subject);
    $editwhy = xoops_trim(Request::getString('editwhy', '', 'POST')); // !empty($_POST['editwhy'])) ? xoops_trim($_POST['editwhy']) : "";

    if ($dohtml && !newbbIsAdmin($forumObject)) {
        //$message=newbb_textFilter($message);
    }
    $postObject->setVar('post_text', $message);
    $postObject->setVar('post_karma', $post_karma);
    $postObject->setVar('require_reply', $require_reply);
    $postObject->setVar('poster_name', $poster_name);
    $postObject->setVar('dohtml', $dohtml);
    $postObject->setVar('dosmiley', $dosmiley);
    $postObject->setVar('doxcode', $doxcode);
    $postObject->setVar('dobr', $dobr);
    $postObject->setVar('icon', $icon);
    $postObject->setVar('attachsig', $attachsig);
    $postObject->setAttachment();
    if (!empty($post_id)) {
        $postObject->setPostEdit($poster_name, $editwhy);
    } // is reply

    //    $attachments_tmp = array();
    //    if (!empty($_POST["attachments_tmp"])) {
    if (Request::getString('attachments_tmp', '', 'POST')) {
        $attachments_tmp = unserialize(base64_decode(Request::getString('attachments_tmp', '', 'POST')));
        if (Request::getArray('delete_tmp', null, 'POST') && count(Request::getArray('delete_tmp', null, 'POST')) > 1) {
            foreach (Request::getArray('delete_tmp', null, 'POST') as $key) {
                unlink($GLOBALS['xoops']->path($GLOBALS['xoopsModuleConfig']['dir_attachments'] . '/' . $attachments_tmp[$key][0]));
                unset($attachments_tmp[$key]);
            }
        }
    }
    if (isset($attachments_tmp) && count($attachments_tmp)) {
        foreach ($attachments_tmp as $key => $attach) {
            if (rename(XOOPS_CACHE_PATH . '/' . $attachments_tmp[$key][0], $GLOBALS['xoops']->path($GLOBALS['xoopsModuleConfig']['dir_attachments'] . '/' . $attachments_tmp[$key][0]))) {
                $postObject->setAttachment($attach[0], $attach[1], $attach[2]);
            }
        }
    }
    $error_upload = '';

    if (isset($_FILES['userfile']['name']) && '' !== $_FILES['userfile']['name']
        && $topicHandler->getPermission($forumObject, $topic_status, 'attach')) {
        require_once $GLOBALS['xoops']->path('modules/' . $xoopsModule->getVar('dirname', 'n') . '/class/uploader.php');
        $maxfilesize = $forumObject->getVar('attach_maxkb') * 1024;
        $uploaddir   = XOOPS_CACHE_PATH;

        $uploader = new Newbb\Uploader($uploaddir, $forumObject->getVar('attach_ext'), (int)$maxfilesize, (int)$GLOBALS['xoopsModuleConfig']['max_img_width'], (int)$GLOBALS['xoopsModuleConfig']['max_img_height']);

        if ($_FILES['userfile']['error'] > 0) {
            switch ($_FILES['userfile']['error']) {
                case 1:
                    $error_message[] = _MD_NEWBB_MAXUPLOADFILEINI;
                    break;
                case 2:
                    $error_message[] = sprintf(_MD_NEWBB_MAXKB, $forumObject->getVar('attach_maxkb'));
                    break;
                default:
                    $error_message[] = _MD_NEWBB_UPLOAD_ERRNODEF;
                    break;
            }
        } else {
            $uploader->setCheckMediaTypeByExt();
            $temp = Request::getArray('xoops_upload_file', [], 'POST');
            if ($uploader->fetchMedia($temp[0])) {
                $prefix = is_object($GLOBALS['xoopsUser']) ? (string)$GLOBALS['xoopsUser']->uid() . '_' : 'newbb_';
                $uploader->setPrefix($prefix);
                if (!$uploader->upload()) {
                    $error_message[] = $error_upload = $uploader->getErrors();
                } else {
                    if (is_file($uploader->getSavedDestination())) {
                        if (rename(XOOPS_CACHE_PATH . '/' . $uploader->getSavedFileName(), $GLOBALS['xoops']->path($GLOBALS['xoopsModuleConfig']['dir_attachments'] . '/' . $uploader->getSavedFileName()))) {
                            $postObject->setAttachment($uploader->getSavedFileName(), $uploader->getMediaName(), $uploader->getMediaType());
                        }
                    }
                }
            } else {
                $error_message[] = $error_upload = $uploader->getErrors();
            }
        }
    }

    $postid = $postHandler->insert($postObject);

    if (!$postid) {
        require_once $GLOBALS['xoops']->path('header.php');
        xoops_error($postObject->getErrors());
        require_once $GLOBALS['xoops']->path('footer.php');
    }
    newbbSetSession('LP', time()); // Recording last post time
    $topicObject = $topicHandler->get($postObject->getVar('topic_id'));
    $uid         = is_object($GLOBALS['xoopsUser']) ? $GLOBALS['xoopsUser']->getVar('uid') : 0;
    if (newbbIsAdmin($forumObject)
        || ($topicHandler->getPermission($forumObject, $topic_status, 'type')
            && (0 == $topic_id
                || $uid == $topicObject->getVar('topic_poster')))) {
        $topicObject->setVar('type_id', Request::getInt('type_id', 0, 'POST'));
    }

    if (!empty($GLOBALS['xoopsModuleConfig']['do_tag']) && $postObject->isTopic()) {
        $topicObject->setVar('topic_tags', Request::getInt('topic_tags', 0, 'POST'));
    }
    $topicHandler->insert($topicObject);

    // Set read mark
    if (!empty($isnew)) {
        require_once $GLOBALS['xoops']->path('modules/newbb/include/functions.read.php');
        newbbSetRead('topic', $topicObject->getVar('topic_id'), $postid);
        if (!$postObject->getVar('pid')) {
            newbbSetRead('forum', $forumObject->getVar('forum_id'), $postid);
        }
    }

    //$postObject->loadFilters(empty($isnew) ? 'update' : 'insert');

    // Define tags for notification message
    if (!empty($isnew) && $approved && !empty($GLOBALS['xoopsModuleConfig']['notification_enabled'])) {
        $tags                = [];
        $tags['THREAD_NAME'] = Request::getString('subject', '', 'POST');
        $tags['THREAD_URL']  = XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname', 'n') . '/viewtopic.php?post_id=' . $postid;
        $tags['POST_URL']    = $tags['THREAD_URL']; // . '#forumpost' . $postid;
        require_once __DIR__ . '/include/notification.inc.php';
        $forum_info         = newbb_notify_iteminfo('forum', $forumObject->getVar('forum_id'));
        $tags['FORUM_NAME'] = $forum_info['name'];
        $tags['FORUM_URL']  = $forum_info['url'];
        /** @var \XoopsNotificationHandler $notificationHandler */
        $notificationHandler = xoops_getHandler('notification');
        if (empty($isreply)) {
            // Notify of new thread
            $notificationHandler->triggerEvent('forum', $forumObject->getVar('forum_id'), 'new_thread', $tags);
        } else {
            // Notify of new post
            $notificationHandler->triggerEvent('thread', $topic_id, 'new_post', $tags);
            $_tags['name'] = $tags['THREAD_NAME'];
            $_tags['url']  = $tags['POST_URL'];
            $_tags['uid']  = $uid;
            $notificationHandler->triggerEvent('thread', $topic_id, 'post', $_tags);
        }
        $notificationHandler->triggerEvent('global', 0, 'new_post', $tags);
        $notificationHandler->triggerEvent('forum', $forumObject->getVar('forum_id'), 'new_post', $tags);
        $tags['POST_CONTENT'] = Request::getString('message', '', 'POST');
        $tags['POST_NAME']    = Request::getString('subject', '', 'POST');
        $notificationHandler->triggerEvent('global', 0, 'new_fullpost', $tags);
        $notificationHandler->triggerEvent('forum', $forumObject->getVar('forum_id'), 'new_fullpost', $tags);
    }

    // If user checked notification box, subscribe them to the
    // appropriate event; if unchecked, then unsubscribe
    if (!empty($GLOBALS['xoopsUser']) && !empty($GLOBALS['xoopsModuleConfig']['notification_enabled'])) {
        $notificationHandler = xoops_getHandler('notification');
        if (!Request::getInt('notify', 0, 'POST')) {
            $notificationHandler->unsubscribe('thread', $postObject->getVar('topic_id'), 'new_post');
        } elseif (Request::getInt('notify', 0, 'POST') > 0) {
            $notificationHandler->subscribe('thread', $postObject->getVar('topic_id'), 'new_post');
        }
        // elseif ($_POST['notify']<0) keep it as it is
    }

    if ($approved) {
        if (!empty($GLOBALS['xoopsModuleConfig']['cache_enabled'])) {
            newbbSetSession('t' . $postObject->getVar('topic_id'), null);
        }
        // Update user
        if ($uid > 0) {
            $sql = 'SELECT count(*)' . '    FROM ' . $GLOBALS['xoopsDB']->prefix('newbb_topics') . '    WHERE approved=1 AND topic_poster =' . $uid;
            $ret = $GLOBALS['xoopsDB']->query($sql);
            list($topics) = $GLOBALS['xoopsDB']->fetchRow($ret);

            $sql = '    SELECT count(*)' . '    FROM ' . $GLOBALS['xoopsDB']->prefix('newbb_topics') . '    WHERE approved=1 AND topic_digest > 0 AND topic_poster =' . $uid;
            $ret = $GLOBALS['xoopsDB']->query($sql);
            list($digests) = $GLOBALS['xoopsDB']->fetchRow($ret);

            $sql = '    SELECT count(*), MAX(post_time)' . '    FROM ' . $GLOBALS['xoopsDB']->prefix('newbb_posts') . '    WHERE approved=1 AND uid =' . $uid;
            $ret = $GLOBALS['xoopsDB']->query($sql);
            list($posts, $lastpost) = $GLOBALS['xoopsDB']->fetchRow($ret);

            $GLOBALS['xoopsDB']->queryF('    REPLACE INTO ' . $GLOBALS['xoopsDB']->prefix('newbb_user_stats') . "     SET uid = '{$uid}', user_topics = '{$topics}', user_posts = '{$posts}', user_digests = '{$digests}', user_lastpost = '{$lastpost}'");
        }

        $redirect = XOOPS_URL . '/modules/newbb/viewtopic.php?post_id=' . $postid;
        $message  = _MD_NEWBB_THANKSSUBMIT . '<br>' . $error_upload;
    } else {
        $redirect = XOOPS_URL . '/modules/newbb/viewforum.php?forum=' . $postObject->getVar('forum_id');
        $message  = _MD_NEWBB_THANKSSUBMIT . '<br>' . _MD_NEWBB_WAITFORAPPROVAL . '<br>' . $error_upload;
    }

    if ('add' === $op) {
        redirect_header(XOOPS_URL . '/modules/newbb/polls.php?op=add&amp;forum=' . $postObject->getVar('forum_id') . '&amp;topic_id=' . $postObject->getVar('topic_id'), 1, _MD_NEWBB_ADDPOLL);
    } else {
        redirect_header($redirect, 2, $message);
    }
}

$xoopsOption['template_main']                                        = 'newbb_edit_post.tpl';
$GLOBALS['xoopsConfig']['module_cache'][$xoopsModule->getVar('mid')] = 0;
// irmtfan remove and move to footer.php
//$xoopsOption['xoops_module_header']= $xoops_module_header;
// irmtfan include header.php after defining $xoopsOption['template_main']
require_once $GLOBALS['xoops']->path('header.php');
//$xoopsTpl->assign('xoops_module_header', $xoops_module_header);

if (Request::getString('contents_upload', null, 'POST')) {
    $attachments_tmp = [];
    if (Request::getArray('attachments_tmp', null, 'POST')) {
        $attachments_tmp = unserialize(base64_decode(Request::getArray('attachments_tmp', [], 'POST')));
        if (Request::getArray('delete_tmp', null, 'POST') && count(Request::getArray('delete_tmp', null, 'POST'))) {
            foreach (Request::getArray('delete_tmp', '', 'POST') as $key) {
                unlink($uploaddir = $GLOBALS['xoops']->path($GLOBALS['xoopsModuleConfig']['dir_attachments'] . '/' . $attachments_tmp[$key][0]));
                unset($attachments_tmp[$key]);
            }
        }
    }

    $error_upload = '';
    if (isset($_FILES['userfile']['name']) && '' !== $_FILES['userfile']['name']) {
        require_once $GLOBALS['xoops']->path('modules/' . $xoopsModule->getVar('dirname', 'n') . '/class/uploader.php');
        $maxfilesize = $forumObject->getVar('attach_maxkb') * 1024;
        $uploaddir   = XOOPS_CACHE_PATH;

        $uploader = new Newbb\Uploader($uploaddir, $forumObject->getVar('attach_ext'), (int)$maxfilesize, (int)$GLOBALS['xoopsModuleConfig']['max_img_width'], (int)$GLOBALS['xoopsModuleConfig']['max_img_height']);
        if ($_FILES['userfile']['error'] > 0) {
            switch ($_FILES['userfile']['error']) {
                case 1:
                    $error_message[] = _MD_NEWBB_MAXUPLOADFILEINI;
                    break;
                case 2:
                    $error_message[] = sprintf(_MD_NEWBB_MAXKB, $forumObject->getVar('attach_maxkb'));
                    break;
                default:
                    $error_message[] = _MD_NEWBB_UPLOAD_ERRNODEF;
                    break;
            }
        } else {
            $uploader->setCheckMediaTypeByExt();
            $temp = Request::getArray('xoops_upload_file', [], 'POST');
            if ($uploader->fetchMedia($temp[0])) {
                $prefix = is_object($GLOBALS['xoopsUser']) ? (string)$GLOBALS['xoopsUser']->uid() . '_' : 'newbb_';
                $uploader->setPrefix($prefix);
                if (!$uploader->upload()) {
                    $error_message[] = $error_upload = $uploader->getErrors();
                } else {
                    if (is_file($uploader->getSavedDestination())) {
                        $attachments_tmp[(string)time()] = [
                            $uploader->getSavedFileName(),
                            $uploader->getMediaName(),
                            $uploader->getMediaType()
                        ];
                    }
                }
            } else {
                $error_message[] = $error_upload = $uploader->getErrors();
            }
        }
    }
}

if (Request::getString('contents_preview', Request::getString('contents_preview', '', 'POST'), 'GET')) {
    if (Request::getString('attachments_tmp', '', 'POST')) {
        $attachments_tmp = unserialize(base64_decode(Request::getString('attachments_tmp', '', 'POST')));
    }

    $p_subject = $myts->htmlSpecialChars(Request::getString('subject', '', 'POST'));
    $dosmiley  = Request::getInt('dosmiley', 0, 'POST');
    $dohtml    = Request::getInt('dohtml', 0, 'POST');
    $doxcode   = Request::getInt('doxcode', 0, 'POST');
    $dobr      = Request::getInt('dobr', 0, 'POST');
    $p_message = Request::getString('message', '', 'POST');
    $p_message = $myts->previewTarea($p_message, $dohtml, $dosmiley, $doxcode, 1, $dobr);
    $p_date    = formatTimestamp(time());
    if ($postObject->isNew()) {
        if (is_object($GLOBALS['xoopsUser'])) {
            $p_name = $GLOBALS['xoopsUser']->getVar('uname');
            if (!empty($GLOBALS['xoopsModuleConfig']['show_realname']) && $GLOBALS['xoopsUser']->getVar('name')) {
                $p_name = $GLOBALS['xoopsUser']->getVar('name');
            }
        }
    } elseif ($postObject->getVar('uid')) {
        $p_name = newbbGetUnameFromId($postObject->getVar('uid'), $GLOBALS['xoopsModuleConfig']['show_realname']);
    }
    if (empty($p_name)) {
        $p_name = Request::getString('poster_name', '', 'POST') ? htmlspecialchars(Request::getString('poster_name', '', 'POST')) : htmlspecialchars($GLOBALS['xoopsConfig']['anonymous']);
    }

    $post_preview = [
        'subject' => $p_subject,
        'meta'    => _MD_NEWBB_BY . ' ' . $p_name . ' ' . _MD_NEWBB_ON . ' ' . $p_date,
        'content' => $p_message
    ];
    $xoopsTpl->assign_by_ref('post_preview', $post_preview);
}

if (Request::getString('contents_upload', null, 'POST') || Request::getString('contents_preview', null, 'POST')
    || Request::getString('contents_preview', null, 'GET')
    || Request::getString('editor', '', 'POST')) {
    $editor        = Request::getString('editor', '', 'POST');
    $dosmiley      = Request::getInt('dosmiley', 0, 'POST');
    $dohtml        = Request::getInt('dohtml', 0, 'POST');
    $doxcode       = Request::getInt('doxcode', 0, 'POST');
    $dobr          = Request::getInt('dobr', 0, 'POST');
    $subject       = Request::getString('subject', '', 'POST');
    $message       = Request::getString('message', '', 'POST');
    $poster_name   = Request::getString('poster_name', '', 'POST');
    $hidden        = Request::getString('hidden', '', 'POST');
    $notify        = Request::getInt('notify', 0, 'POST');
    $attachsig     = Request::getInt('attachsig', 0, 'POST');//!empty($_POST['attachsig']) ? 1 : 0;
    $isreply       = Request::getInt('isreply', 0, 'POST'); //!empty($_POST['isreply']) ? 1 : 0;
    $isedit        = Request::getInt('isedit', 0, 'POST'); //!empty($_POST['isedit']) ? 1 : 0;
    $icon          = (Request::getString('icon', '', 'POST')
                      && is_file($GLOBALS['xoops']->path('images/subject/' . Request::getString('icon', '', 'POST'))) ? Request::getString('icon', '', 'POST') : '');
    $view_require  = Request::getString('view_require', '', 'POST');
    $post_karma    = (('require_karma' === $view_require)
                      && !Request::getInt('post_karma', 0, 'POST')) ? Request::getInt('post_karma', 0, 'POST') : 0;
    $require_reply = ('require_reply' === $view_require) ? 1 : 0;

    if (!Request::getString('contents_upload', '', 'POST')) {
        $contents_preview = 1;
    }
    $attachments = $postObject->getAttachment();
    $xoopsTpl->assign('error_message', implode('<br>', $error_message));

    include __DIR__ . '/include/form.post.php';
}
// irmtfan move to footer.php
require_once __DIR__ . '/footer.php';
include $GLOBALS['xoops']->path('footer.php');
