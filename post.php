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
 * @copyright       XOOPS Project (http://xoops.org)
 * @license         http://www.fsf.org/copyleft/gpl.html GNU public license
 * @package         newbb
 * @since           4.0
 * @author          Taiwen Jiang <phppp@users.sourceforge.net>
 */

include_once __DIR__ . '/header.php';

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
    ${$getint} = XoopsRequest::getInt($getint, 0, 'POST');
}

$op       = XoopsRequest::getCmd('op', '', 'POST');
$viewmode = ('flat' !== XoopsRequest::getString('viewmode', '', 'POST')) ? 'thread' : 'flat';
if (empty($forum)) {
    redirect_header('index.php', 2, _MD_ERRORFORUM);
}

$forumHandler = xoops_getModuleHandler('forum', 'newbb');
$topicHandler = xoops_getModuleHandler('topic', 'newbb');
$postHandler  = xoops_getModuleHandler('post', 'newbb');

if (!empty($isedit) && $post_id > 0) {
    $post_obj = $postHandler->get($post_id);
    $topic_id = $post_obj->getVar('topic_id');
} else {
    $post_obj = $postHandler->create();
}
$topic_obj = $topicHandler->get($topic_id);
$forum_id  = $topic_id ? $topic_obj->getVar('forum_id') : $forum;
$forum_obj = $forumHandler->get($forum_id);
if (!$forumHandler->getPermission($forum_obj)) {
    redirect_header('index.php', 2, _NOPERM);
}

if ($GLOBALS['xoopsModuleConfig']['wol_enabled']) {
    $onlineHandler = xoops_getModuleHandler('online', 'newbb');
    $onlineHandler->init($forum_obj);
}

$error_message = [];

if (XoopsRequest::getString('contents_submit', '', 'POST')) {
    $token_valid = false;
    $token_valid = $GLOBALS['xoopsSecurity']->check();

    $captcha_invalid = false;
    if (!is_object($GLOBALS['xoopsUser']) && XoopsRequest::getString('uname', '', 'POST')
        && XoopsRequest::getString('pass', '', 'POST')
    ) {
        $uname         = XoopsRequest::getString('uname', '', 'POST');
        $pass          = XoopsRequest::getString('pass', '', 'POST');
        $memberHandler = xoops_getHandler('member');
        $user          = $memberHandler->loginUser(addslashes($myts->stripSlashesGPC($uname)), addslashes($myts->stripSlashesGPC($pass)));
        if (is_object($user) && 0 < $user->getVar('level')) {
            if (XoopsRequest::getString('login', '', 'POST')) {
                $user->setVar('last_login', time());
                if (!$memberHandler->insertUser($user)) {
                }
                $_SESSION                    = [];
                $_SESSION['xoopsUserId']     = $user->getVar('uid');
                $_SESSION['xoopsUserGroups'] = $user->getGroups();
                if ($GLOBALS['xoopsConfig']['use_mysession'] && $GLOBALS['xoopsConfig']['session_name'] !== '') {
                    setcookie($GLOBALS['xoopsConfig']['session_name'], session_id(), time() + (60 * $GLOBALS['xoopsConfig']['session_expire']), '/', '', 0);
                }
                $user_theme = $user->getVar('theme');
                if (in_array($user_theme, $GLOBALS['xoopsConfig']['theme_set_allowed'])) {
                    $_SESSION['xoopsUserTheme'] = $user_theme;
                }
            }
            $GLOBALS['xoopsUser'] =& $user;
            $xoopsUserIsAdmin     = $GLOBALS['xoopsUser']->isAdmin($xoopsModule->getVar('mid'));
        }
    }
    if (!is_object($GLOBALS['xoopsUser'])) {
        xoops_load('captcha');
        $xoopsCaptcha = XoopsCaptcha::getInstance();
        if (!$xoopsCaptcha->verify()) {
            $captcha_invalid = true;
            $error_message[] = $xoopsCaptcha->getMessage();
        }
    }

    $isadmin = newbb_isAdmin($forum_obj);

    $time_valid = true;
    if (!$isadmin && !empty($GLOBALS['xoopsModuleConfig']['post_timelimit'])) {
        $last_post = newbb_getsession('LP');
        if (time() - $last_post < $GLOBALS['xoopsModuleConfig']['post_timelimit']) {
            $time_valid = false;
        }
    }

    if ($captcha_invalid || !$token_valid || !$time_valid) {
        $_POST['contents_preview'] = 1;
        $_POST['contents_submit']  = null;
        $_POST['contents_upload']  = null;
        if (!$token_valid) {
            $error_message[] = _MD_INVALID_SUBMIT;
        }
        if (!$time_valid) {
            $error_message[] = sprintf(_MD_POSTING_LIMITED, $GLOBALS['xoopsModuleConfig']['post_timelimit']);
        }
    }
}

if (XoopsRequest::getString('contents_submit', '', 'POST')) {
    $message = XoopsRequest::getText('message', '', 'POST');
    if (empty($message)) {
        // irmtfan - issue with javascript:history.go(-1) - add error message
        redirect_header($_SERVER['HTTP_REFERER'], 1, _MD_ERROR_BACK);
    }
    if (!empty($isedit) && $post_id > 0) {
        $uid = is_object($GLOBALS['xoopsUser']) ? $GLOBALS['xoopsUser']->getVar('uid') : 0;

        $topic_status = $topic_obj->getVar('topic_status');
        if ($topicHandler->getPermission($forum_obj, $topic_status, 'edit')
            && ($isadmin
                || ($post_obj->checkTimelimit('edit_timelimit')
                    && $post_obj->checkIdentity()))
        ) {
        } else {
            redirect_header(XOOPS_URL . "/modules/newbb/viewtopic.php?forum={$forum_id}&amp;topic_id={$topic_id}&amp;post_id={$post_id}&amp;order={$order}&amp;viewmode={$viewmode}", 2,
                            _MD_NORIGHTTOEDIT);
        }

        $delete_attach = XoopsRequest::getArray('delete_attach', [], 'POST');
        if (is_array($delete_attach) && count($delete_attach) > 0) {
            $post_obj->deleteAttachment($delete_attach);
        }
    } else {
        if ($topic_id) {
            $topic_status = $topic_obj->getVar('topic_status');
            if (!$topicHandler->getPermission($forum_obj, $topic_status, 'reply')) {
                redirect_header(XOOPS_URL . "/modules/newbb/viewtopic.php?forum={$forum_id}&amp;topic_id={$topic_id}&amp;post_id={$post_id}&amp;order={$order}&amp;viewmode={$viewmode}", 2,
                                _MD_NORIGHTTOREPLY);
            }
        } else {
            $topic_status = 0;
            if (!$topicHandler->getPermission($forum_obj, $topic_status, 'post')) {
                redirect_header(XOOPS_URL . "/modules/newbb/viewtopic.php?forum={$forum_id}", 2, _MD_NORIGHTTOPOST);
            }
        }

        $isreply = 0;
        $isnew   = 1;
        if (!is_object($GLOBALS['xoopsUser'])
            || (XoopsRequest::getString('noname', '', 'POST')
                && !empty($GLOBALS['xoopsModuleConfig']['allow_user_anonymous']))
        ) {
            $uid = 0;
        } else {
            $uid = $GLOBALS['xoopsUser']->getVar('uid');
        }
        if (!empty($pid)) {
            $post_obj->setVar('pid', $pid);
        }
        if (!empty($topic_id)) {
            $post_obj->setVar('topic_id', $topic_id);
            $isreply = 1;
        }
        $post_obj->setVar('poster_ip', newbb_getIP(true));//using "true" to force the IP as a string
        $post_obj->setVar('uid', $uid);
        $post_obj->setVar('post_time', time());
    }

    $approved = $topicHandler->getPermission($forum_obj, $topic_status, 'noapprove');
    $post_obj->setVar('approved', $approved);

    $post_obj->setVar('forum_id', $forum_obj->getVar('forum_id'));

    $subject       = xoops_trim(XoopsRequest::getString('subject', '', 'POST'));
    $subject       = ($subject === '') ? _NOTITLE : $subject;
    $poster_name   = xoops_trim(XoopsRequest::getString('poster_name', '', 'POST'));
    $dohtml        = XoopsRequest::getInt('dohtml', 0, 'POST')
                     && $topicHandler->getPermission($forum_obj, $topic_status, 'html');
    $dosmiley      = XoopsRequest::getInt('dosmiley', 0, 'POST');
    $doxcode       = XoopsRequest::getInt('doxcode', 0, 'POST') ? 1 : 0;
    $dobr          = XoopsRequest::getInt('dobr', 0, 'POST') ? 1 : 0;
    $icon          = (XoopsRequest::getString('icon', '', 'POST')
                      && is_file($GLOBALS['xoops']->path('images/subject/' . XoopsRequest::getString('icon', '', 'POST'))) ? XoopsRequest::getString('icon', '', 'POST') : '');
    $attachsig     = XoopsRequest::getBool('attachsig', false, 'POST')
                     && $topicHandler->getPermission($forum_obj, $topic_status, 'signature');
    $view_require  = XoopsRequest::getString('view_require', '', 'POST');
    $post_karma    = ($view_require === 'require_karma') ? XoopsRequest::getInt('post_karma', 0, 'POST') : 0;
    $require_reply = ($view_require === 'require_reply');
    $post_obj->setVar('subject', $subject);
    $editwhy = xoops_trim(XoopsRequest::getString('editwhy', '', 'POST')); // !empty($_POST['editwhy'])) ? xoops_trim($_POST['editwhy']) : "";

    if ($dohtml && !newbb_isAdmin($forum_obj)) {
        //$message=newbb_textFilter($message);
    }
    $post_obj->setVar('post_text', $message);
    $post_obj->setVar('post_karma', $post_karma);
    $post_obj->setVar('require_reply', $require_reply);
    $post_obj->setVar('poster_name', $poster_name);
    $post_obj->setVar('dohtml', $dohtml);
    $post_obj->setVar('dosmiley', $dosmiley);
    $post_obj->setVar('doxcode', $doxcode);
    $post_obj->setVar('dobr', $dobr);
    $post_obj->setVar('icon', $icon);
    $post_obj->setVar('attachsig', $attachsig);
    $post_obj->setAttachment();
    if (!empty($post_id)) {
        $post_obj->setPostEdit($poster_name, $editwhy);
    } // is reply

    //    $attachments_tmp = array();
    //    if (!empty($_POST["attachments_tmp"])) {
    if (XoopsRequest::getString('attachments_tmp', '', 'POST')) {
        $attachments_tmp = unserialize(base64_decode(XoopsRequest::getString('attachments_tmp', '', 'POST')));
        if (XoopsRequest::getArray('delete_tmp', null, 'POST')
            && count(XoopsRequest::getArray('delete_tmp', null, 'POST')) > 1
        ) {
            foreach (XoopsRequest::getArray('delete_tmp', null, 'POST') as $key) {
                unlink($GLOBALS['xoops']->path($GLOBALS['xoopsModuleConfig']['dir_attachments'] . '/' . $attachments_tmp[$key][0]));
                unset($attachments_tmp[$key]);
            }
        }
    }
    if (isset($attachments_tmp) && count($attachments_tmp)) {
        foreach ($attachments_tmp as $key => $attach) {
            if (rename(XOOPS_CACHE_PATH . '/' . $attachments_tmp[$key][0], $GLOBALS['xoops']->path($GLOBALS['xoopsModuleConfig']['dir_attachments'] . '/' . $attachments_tmp[$key][0]))) {
                $post_obj->setAttachment($attach[0], $attach[1], $attach[2]);
            }
        }
    }
    $error_upload = '';

    if (isset($_FILES['userfile']['name']) && $_FILES['userfile']['name'] !== ''
        && $topicHandler->getPermission($forum_obj, $topic_status, 'attach')
    ) {
        require_once $GLOBALS['xoops']->path('modules/' . $xoopsModule->getVar('dirname', 'n') . '/class/uploader.php');
        $maxfilesize = $forum_obj->getVar('attach_maxkb') * 1024;
        $uploaddir   = XOOPS_CACHE_PATH;

        $uploader = new NewbbUploader($uploaddir, $forum_obj->getVar('attach_ext'), (int)$maxfilesize, (int)$GLOBALS['xoopsModuleConfig']['max_img_width'],
                                      (int)$GLOBALS['xoopsModuleConfig']['max_img_height']);

        if ($_FILES['userfile']['error'] > 0) {
            switch ($_FILES['userfile']['error']) {
                case 1:
                    $error_message[] = _MD_NEWBB_MAXUPLOADFILEINI;
                    break;
                case 2:
                    $error_message[] = sprintf(_MD_NEWBB_MAXKB, $forum_obj->getVar('attach_maxkb'));
                    break;
                default:
                    $error_message[] = _MD_NEWBB_UPLOAD_ERRNODEF;
                    break;
            }
        } else {
            $uploader->setCheckMediaTypeByExt();
            $temp = XoopsRequest::getArray('xoops_upload_file', [], 'POST');
            if ($uploader->fetchMedia($temp[0])) {
                $prefix = is_object($GLOBALS['xoopsUser']) ? (string)$GLOBALS['xoopsUser']->uid() . '_' : 'newbb_';
                $uploader->setPrefix($prefix);
                if (!$uploader->upload()) {
                    $error_message[] = $error_upload = $uploader->getErrors();
                } else {
                    if (is_file($uploader->getSavedDestination())) {
                        if (rename(XOOPS_CACHE_PATH . '/' . $uploader->getSavedFileName(),
                                   $GLOBALS['xoops']->path($GLOBALS['xoopsModuleConfig']['dir_attachments'] . '/' . $uploader->getSavedFileName()))) {
                            $post_obj->setAttachment($uploader->getSavedFileName(), $uploader->getMediaName(), $uploader->getMediaType());
                        }
                    }
                }
            } else {
                $error_message[] = $error_upload = $uploader->getErrors();
            }
        }
    }

    $postid = $postHandler->insert($post_obj);

    if (!$postid) {
        include_once $GLOBALS['xoops']->path('header.php');
        xoops_error($post_obj->getErrors());
        include_once $GLOBALS['xoops']->path('footer.php');
    }
    newbb_setsession('LP', time()); // Recording last post time
    $topic_obj = $topicHandler->get($post_obj->getVar('topic_id'));
    $uid       = is_object($GLOBALS['xoopsUser']) ? $GLOBALS['xoopsUser']->getVar('uid') : 0;
    if (newbb_isAdmin($forum_obj)
        || ($topicHandler->getPermission($forum_obj, $topic_status, 'type')
            && ($topic_id == 0
                || $uid == $topic_obj->getVar('topic_poster')))
    ) {
        $topic_obj->setVar('type_id', XoopsRequest::getInt('type_id', 0, 'POST'));
    }

    if (!empty($GLOBALS['xoopsModuleConfig']['do_tag']) && $post_obj->isTopic()) {
        $topic_obj->setVar('topic_tags', XoopsRequest::getInt('topic_tags', 0, 'POST'));
    }
    $topicHandler->insert($topic_obj);

    // Set read mark
    if (!empty($isnew)) {
        require_once $GLOBALS['xoops']->path('modules/newbb/include/functions.read.php');
        newbb_setRead('topic', $topic_obj->getVar('topic_id'), $postid);
        if (!$post_obj->getVar('pid')) {
            newbb_setRead('forum', $forum_obj->getVar('forum_id'), $postid);
        }
    }

    $post_obj->loadFilters(empty($isnew) ? 'update' : 'insert');

    // Define tags for notification message
    if (!empty($isnew) && $approved && !empty($GLOBALS['xoopsModuleConfig']['notification_enabled'])) {
        $tags                = [];
        $tags['THREAD_NAME'] = XoopsRequest::getString('subject', '', 'POST');
        $tags['THREAD_URL']  = XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname', 'n') . '/viewtopic.php?post_id=' . $postid;
        $tags['POST_URL']    = $tags['THREAD_URL']; // . '#forumpost' . $postid;
        include_once __DIR__ . '/include/notification.inc.php';
        $forum_info          = newbb_notify_iteminfo('forum', $forum_obj->getVar('forum_id'));
        $tags['FORUM_NAME']  = $forum_info['name'];
        $tags['FORUM_URL']   = $forum_info['url'];
        $notificationHandler = xoops_getHandler('notification');
        if (empty($isreply)) {
            // Notify of new thread
            $notificationHandler->triggerEvent('forum', $forum_obj->getVar('forum_id'), 'new_thread', $tags);
        } else {
            // Notify of new post
            $notificationHandler->triggerEvent('thread', $topic_id, 'new_post', $tags);
            $_tags['name'] = $tags['THREAD_NAME'];
            $_tags['url']  = $tags['POST_URL'];
            $_tags['uid']  = $uid;
            $notificationHandler->triggerEvent('thread', $topic_id, 'post', $_tags);
        }
        $notificationHandler->triggerEvent('global', 0, 'new_post', $tags);
        $notificationHandler->triggerEvent('forum', $forum_obj->getVar('forum_id'), 'new_post', $tags);
        $tags['POST_CONTENT'] = $myts->stripSlashesGPC(XoopsRequest::getString('message', '', 'POST'));
        $tags['POST_NAME']    = $myts->stripSlashesGPC(XoopsRequest::getString('subject', '', 'POST'));
        $notificationHandler->triggerEvent('global', 0, 'new_fullpost', $tags);
        $notificationHandler->triggerEvent('forum', $forum_obj->getVar('forum_id'), 'new_fullpost', $tags);
    }

    // If user checked notification box, subscribe them to the
    // appropriate event; if unchecked, then unsubscribe
    if (!empty($GLOBALS['xoopsUser']) && !empty($GLOBALS['xoopsModuleConfig']['notification_enabled'])) {
        $notificationHandler = xoops_getHandler('notification');
        if (!XoopsRequest::getInt('notify', 0, 'POST')) {
            $notificationHandler->unsubscribe('thread', $post_obj->getVar('topic_id'), 'new_post');
        } elseif (XoopsRequest::getInt('notify', 0, 'POST') > 0) {
            $notificationHandler->subscribe('thread', $post_obj->getVar('topic_id'), 'new_post');
        }
        // elseif ($_POST['notify']<0) keep it as it is
    }

    if ($approved) {
        if (!empty($GLOBALS['xoopsModuleConfig']['cache_enabled'])) {
            newbb_setsession('t' . $post_obj->getVar('topic_id'), null);
        }
        // Update user
        if ($uid > 0) {
            $sql = 'SELECT count(*)' . '    FROM ' . $GLOBALS['xoopsDB']->prefix('bb_topics') . '    WHERE approved=1 AND topic_poster =' . $uid;
            $ret = $GLOBALS['xoopsDB']->query($sql);
            list($topics) = $GLOBALS['xoopsDB']->fetchRow($ret);

            $sql = '    SELECT count(*)' . '    FROM ' . $GLOBALS['xoopsDB']->prefix('bb_topics') . '    WHERE approved=1 AND topic_digest > 0 AND topic_poster =' . $uid;
            $ret = $GLOBALS['xoopsDB']->query($sql);
            list($digests) = $GLOBALS['xoopsDB']->fetchRow($ret);

            $sql = '    SELECT count(*), MAX(post_time)' . '    FROM ' . $GLOBALS['xoopsDB']->prefix('bb_posts') . '    WHERE approved=1 AND uid =' . $uid;
            $ret = $GLOBALS['xoopsDB']->query($sql);
            list($posts, $lastpost) = $GLOBALS['xoopsDB']->fetchRow($ret);

            $GLOBALS['xoopsDB']->queryF('    REPLACE INTO '
                                        . $GLOBALS['xoopsDB']->prefix('bb_user_stats')
                                        . "     SET uid = '{$uid}', user_topics = '{$topics}', user_posts = '{$posts}', user_digests = '{$digests}', user_lastpost = '{$lastpost}'");
        }

        $redirect = XOOPS_URL . '/modules/newbb/viewtopic.php?post_id=' . $postid;
        $message  = _MD_THANKSSUBMIT . '<br>' . $error_upload;
    } else {
        $redirect = XOOPS_URL . '/modules/newbb/viewforum.php?forum=' . $post_obj->getVar('forum_id');
        $message  = _MD_THANKSSUBMIT . '<br>' . _MD_WAITFORAPPROVAL . '<br>' . $error_upload;
    }

    if ($op === 'add') {
        redirect_header(XOOPS_URL . '/modules/newbb/polls.php?op=add&amp;forum=' . $post_obj->getVar('forum_id') . '&amp;topic_id=' . $post_obj->getVar('topic_id'), 1, _MD_ADDPOLL);
    } else {
        redirect_header($redirect, 2, $message);
    }
}

$xoopsOption['template_main']                                        = 'newbb_edit_post.tpl';
$GLOBALS['xoopsConfig']['module_cache'][$xoopsModule->getVar('mid')] = 0;
// irmtfan remove and move to footer.php
//$xoopsOption['xoops_module_header']= $xoops_module_header;
// irmtfan include header.php after defining $xoopsOption['template_main']
include_once $GLOBALS['xoops']->path('header.php');
//$xoopsTpl->assign('xoops_module_header', $xoops_module_header);

if (XoopsRequest::getString('contents_upload', null, 'POST')) {
    $attachments_tmp = [];
    if (XoopsRequest::getArray('attachments_tmp', null, 'POST')) {
        $attachments_tmp = unserialize(base64_decode(XoopsRequest::getArray('attachments_tmp', [], 'POST')));
        if (XoopsRequest::getArray('delete_tmp', null, 'POST')
            && count(XoopsRequest::getArray('delete_tmp', null, 'POST'))
        ) {
            foreach (XoopsRequest::getArray('delete_tmp', '', 'POST') as $key) {
                unlink($uploaddir = $GLOBALS['xoops']->path($GLOBALS['xoopsModuleConfig']['dir_attachments'] . '/' . $attachments_tmp[$key][0]));
                unset($attachments_tmp[$key]);
            }
        }
    }

    $error_upload = '';
    if (isset($_FILES['userfile']['name']) && $_FILES['userfile']['name'] !== '') {
        require_once $GLOBALS['xoops']->path('modules/' . $xoopsModule->getVar('dirname', 'n') . '/class/uploader.php');
        $maxfilesize = $forum_obj->getVar('attach_maxkb') * 1024;
        $uploaddir   = XOOPS_CACHE_PATH;

        $uploader = new NewbbUploader($uploaddir, $forum_obj->getVar('attach_ext'), (int)$maxfilesize, (int)$GLOBALS['xoopsModuleConfig']['max_img_width'],
                                      (int)$GLOBALS['xoopsModuleConfig']['max_img_height']);
        if ($_FILES['userfile']['error'] > 0) {
            switch ($_FILES['userfile']['error']) {
                case 1:
                    $error_message[] = _MD_NEWBB_MAXUPLOADFILEINI;
                    break;
                case 2:
                    $error_message[] = sprintf(_MD_NEWBB_MAXKB, $forum_obj->getVar('attach_maxkb'));
                    break;
                default:
                    $error_message[] = _MD_NEWBB_UPLOAD_ERRNODEF;
                    break;
            }
        } else {
            $uploader->setCheckMediaTypeByExt();
            $temp = XoopsRequest::getArray('xoops_upload_file', [], 'POST');
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

if (XoopsRequest::getString('contents_preview', XoopsRequest::getString('contents_preview', '', 'POST'), 'GET')) {
    if (XoopsRequest::getString('attachments_tmp', '', 'POST')) {
        $attachments_tmp = unserialize(base64_decode(XoopsRequest::getString('attachments_tmp', '', 'POST')));
    }

    $p_subject = $myts->htmlSpecialChars($myts->stripSlashesGPC(XoopsRequest::getString('subject', '', 'POST')));
    $dosmiley  = XoopsRequest::getInt('dosmiley', 0, 'POST');
    $dohtml    = XoopsRequest::getInt('dohtml', 0, 'POST');
    $doxcode   = XoopsRequest::getInt('doxcode', 0, 'POST');
    $dobr      = XoopsRequest::getInt('dobr', 0, 'POST');
    $p_message = XoopsRequest::getString('message', '', 'POST');
    $p_message = $myts->previewTarea($p_message, $dohtml, $dosmiley, $doxcode, 1, $dobr);
    $p_date    = formatTimestamp(time());
    if ($post_obj->isNew()) {
        if (is_object($GLOBALS['xoopsUser'])) {
            $p_name = $GLOBALS['xoopsUser']->getVar('uname');
            if (!empty($GLOBALS['xoopsModuleConfig']['show_realname']) && $GLOBALS['xoopsUser']->getVar('name')) {
                $p_name = $GLOBALS['xoopsUser']->getVar('name');
            }
        }
    } elseif ($post_obj->getVar('uid')) {
        $p_name = newbb_getUnameFromId($post_obj->getVar('uid'), $GLOBALS['xoopsModuleConfig']['show_realname']);
    }
    if (empty($p_name)) {
        $p_name = XoopsRequest::getString('poster_name', '', 'POST') ? htmlspecialchars(XoopsRequest::getString('poster_name', '', 'POST')) : htmlspecialchars($GLOBALS['xoopsConfig']['anonymous']);
    }

    $post_preview = [
        'subject' => $p_subject,
        'meta'    => _MD_BY . ' ' . $p_name . ' ' . _MD_ON . ' ' . $p_date,
        'content' => $p_message
    ];
    $xoopsTpl->assign_by_ref('post_preview', $post_preview);
}

if (XoopsRequest::getString('contents_upload', null, 'POST')
    || XoopsRequest::getString('contents_preview', null, 'POST')
    || XoopsRequest::getString('contents_preview', null, 'GET')
    || XoopsRequest::getString('editor', '', 'POST')
) {
    $editor        = XoopsRequest::getString('editor', '', 'POST');
    $dosmiley      = XoopsRequest::getInt('dosmiley', 0, 'POST');
    $dohtml        = XoopsRequest::getInt('dohtml', 0, 'POST');
    $doxcode       = XoopsRequest::getInt('doxcode', 0, 'POST');
    $dobr          = XoopsRequest::getInt('dobr', 0, 'POST');
    $subject       = XoopsRequest::getString('subject', '', 'POST');
    $message       = XoopsRequest::getString('message', '', 'POST');
    $poster_name   = XoopsRequest::getString('poster_name', '', 'POST');
    $hidden        = XoopsRequest::getString('hidden', '', 'POST');
    $notify        = XoopsRequest::getInt('notify', 0, 'POST');
    $attachsig     = XoopsRequest::getInt('attachsig', 0, 'POST');//!empty($_POST['attachsig']) ? 1 : 0;
    $isreply       = XoopsRequest::getInt('isreply', 0, 'POST'); //!empty($_POST['isreply']) ? 1 : 0;
    $isedit        = XoopsRequest::getInt('isedit', 0, 'POST'); //!empty($_POST['isedit']) ? 1 : 0;
    $icon          = (XoopsRequest::getString('icon', '', 'POST')
                      && is_file($GLOBALS['xoops']->path('images/subject/' . XoopsRequest::getString('icon', '', 'POST'))) ? XoopsRequest::getString('icon', '', 'POST') : '');
    $view_require  = XoopsRequest::getString('view_require', '', 'POST');
    $post_karma    = (($view_require === 'require_karma')
                      && !XoopsRequest::getInt('post_karma', 0, 'POST')) ? XoopsRequest::getInt('post_karma', 0, 'POST') : 0;
    $require_reply = ($view_require === 'require_reply') ? 1 : 0;

    if (!XoopsRequest::getString('contents_upload', '', 'POST')) {
        $contents_preview = 1;
    }
    $attachments = $post_obj->getAttachment();
    $xoopsTpl->assign('error_message', implode('<br>', $error_message));

    include __DIR__ . '/include/form.post.php';
}
// irmtfan move to footer.php
include_once __DIR__ . '/footer.php';
include $GLOBALS['xoops']->path('footer.php');
