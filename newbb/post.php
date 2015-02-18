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
 * @copyright       The XOOPS Project http://sourceforge.net/projects/xoops/
 * @license         http://www.fsf.org/copyleft/gpl.html GNU public license
 * @package         newbb
 * @since           4.0
 * @author          Taiwen Jiang <phppp@users.sourceforge.net>
 * @version         $Id: post.php 62 2012-08-17 10:15:26Z alfred $
 */

include_once __DIR__ . "/header.php";

foreach (array(
            'forum',
            'topic_id',
            'post_id',
            'order',
            'pid',
            'start',
            'isreply',
            'isedit'
            ) as $getint) {
    ${$getint} = intval( @$_POST[$getint] );
}

$op = isset($_POST['op']) ? $_POST['op'] : '';
$viewmode = (isset($_POST['viewmode']) && $_POST['viewmode'] != 'flat') ? 'thread' : 'flat';
if ( empty($forum) ) {
    redirect_header("index.php", 2, _MD_ERRORFORUM);
}

$forum_handler = xoops_getmodulehandler('forum', 'newbb');
$topic_handler = xoops_getmodulehandler('topic', 'newbb');
$post_handler = xoops_getmodulehandler('post', 'newbb');

if ( !empty($isedit) && $post_id > 0 ) {
    $post_obj = $post_handler->get($post_id);
    $topic_id = $post_obj->getVar("topic_id");
} else {
    $post_obj = $post_handler->create();
}
$topic_obj = $topic_handler->get($topic_id);
$forum_id = ($topic_id) ? $topic_obj->getVar("forum_id") : $forum;
$forum_obj = $forum_handler->get($forum_id);
if (!$forum_handler->getPermission($forum_obj)) {
    redirect_header("index.php", 2, _NOPERM);
}

if ($xoopsModuleConfig['wol_enabled']) {
    $online_handler = xoops_getmodulehandler('online', 'newbb');
    $online_handler->init($forum_obj);
}

$error_message = array();

if ( !empty($_POST['contents_submit']) ) {

    $token_valid = false;
    $token_valid = $GLOBALS['xoopsSecurity']->check();

    $captcha_invalid = false;
    if (!is_object($xoopsUser) && !empty($_POST['uname']) && !empty($_POST['pass'])) {
        $uname = trim($_POST['uname']);
        $pass = trim($_POST['pass']);
        $member_handler = xoops_gethandler('member');
        $user = $member_handler->loginUser(addslashes($myts->stripSlashesGPC($uname)), addslashes($myts->stripSlashesGPC($pass)));
        if (is_object($user) && 0 < $user->getVar('level')) {
            if (!empty($_POST["login"])) {
                $user->setVar('last_login', time());
                if (!$member_handler->insertUser($user)) {
                }
                $_SESSION = array();
                $_SESSION['xoopsUserId'] = $user->getVar('uid');
                $_SESSION['xoopsUserGroups'] = $user->getGroups();
                if ($xoopsConfig['use_mysession'] && $xoopsConfig['session_name'] != '') {
                    setcookie($xoopsConfig['session_name'], session_id(), time() + (60 * $xoopsConfig['session_expire']), '/',  '', 0);
                }
                $user_theme = $user->getVar('theme');
                if (in_array($user_theme, $xoopsConfig['theme_set_allowed'])) {
                    $_SESSION['xoopsUserTheme'] = $user_theme;
                }
            }
            $xoopsUser =& $user;
            $xoopsUserIsAdmin = $xoopsUser->isAdmin($xoopsModule->getVar('mid'));
        }
    }
    if (!is_object($xoopsUser)) {
        xoops_load("captcha");
        $xoopsCaptcha = XoopsCaptcha::getInstance();
        if (! $xoopsCaptcha->verify() ) {
            $captcha_invalid = true;
            $error_message[] = $xoopsCaptcha->getMessage();
        }
    }

    $isadmin = newbb_isAdmin($forum_obj);

    $time_valid = true;
    if ( !$isadmin && !empty($xoopsModuleConfig['post_timelimit']) ) {
        $last_post = newbb_getsession('LP');
        if (time()-$last_post < $xoopsModuleConfig['post_timelimit']) {
            $time_valid = false;
        }
    }

    if ($captcha_invalid || !$token_valid || !$time_valid) {
        $_POST['contents_preview'] = 1;
        $_POST['contents_submit'] = null;
        $_POST['contents_upload'] = null;
        if (!$token_valid) {
            $error_message[] = _MD_INVALID_SUBMIT;
        }
        if (!$time_valid) {
            $error_message[] = sprintf(_MD_POSTING_LIMITED, $xoopsModuleConfig['post_timelimit']);
        }
    }
}

if ( !empty($_POST['contents_submit']) ) {
    $message =  $_POST['message'];
    if (empty($message)) {
        // irmtfan - issue with javascript:history.go(-1) - add error message
        redirect_header($_SERVER['HTTP_REFERER'], 1, _MD_ERROR_BACK);
    }
    if ( !empty($isedit) && $post_id > 0 ) {

        $uid = is_object($xoopsUser)? $xoopsUser->getVar('uid') : 0;

        $topic_status = $topic_obj->getVar('topic_status');
        if ( $topic_handler->getPermission($forum_obj, $topic_status, 'edit')
            && ( $isadmin || ( $post_obj->checkTimelimit('edit_timelimit') && $post_obj->checkIdentity() ))
            ) {} else {
            redirect_header(XOOPS_URL."/modules/newbb/viewtopic.php?forum={$forum_id}&amp;topic_id={$topic_id}&amp;post_id={$post_id}&amp;order={$order}&amp;viewmode={$viewmode}", 2, _MD_NORIGHTTOEDIT);
        }

        $delete_attach = isset($_POST['delete_attach']) ? $_POST['delete_attach'] : array();
        if (is_array($delete_attach) && count($delete_attach) > 0) {
            $post_obj->deleteAttachment($delete_attach);
        }
    } else {
        if ($topic_id) {
            $topic_status = $topic_obj->getVar('topic_status');
            if (!$topic_handler->getPermission($forum_obj, $topic_status, 'reply')) {
                redirect_header(XOOPS_URL."/modules/newbb/viewtopic.php?forum={$forum_id}&amp;topic_id={$topic_id}&amp;post_id={$post_id}&amp;order={$order}&amp;viewmode={$viewmode}", 2, _MD_NORIGHTTOREPLY);
            }
        } else {
            $topic_status = 0;
            if (!$topic_handler->getPermission($forum_obj, $topic_status, 'post')) {
                redirect_header(XOOPS_URL."/modules/newbb/viewtopic.php?forum={$forum_id}", 2, _MD_NORIGHTTOPOST);
            }
        }

        $isreply = 0;
        $isnew = 1;
        if ( !is_object($xoopsUser) || ( !empty($_POST['noname']) && !empty($xoopsModuleConfig['allow_user_anonymous']) ) ) {
            $uid = 0;
        } else {
            $uid = $xoopsUser->getVar("uid");
        }
        if (!empty($pid)) {
            $post_obj->setVar('pid', $pid);
        }
        if (!empty($topic_id)) {
            $post_obj->setVar('topic_id', $topic_id);
            $isreply = 1;
        }
        $post_obj->setVar('poster_ip', newbb_getIP());
        $post_obj->setVar('uid', $uid);
        $post_obj->setVar('post_time', time());
    }

    $approved = $topic_handler->getPermission($forum_obj, $topic_status, 'noapprove');
    $post_obj->setVar('approved', $approved);

    $post_obj->setVar('forum_id', $forum_obj->getVar('forum_id'));

    $subject = xoops_trim($_POST['subject']);
    $subject = ($subject == '') ? _NOTITLE : $subject;
    $poster_name = !empty($_POST['poster_name'])?xoops_trim($_POST['poster_name']):'';
    $dohtml = !empty($_POST['dohtml']) && $topic_handler->getPermission($forum_obj, $topic_status, 'html');
    $dosmiley = !empty($_POST['dosmiley']) ? 1 : 0;
    $doxcode = !empty($_POST['doxcode']) ? 1 : 0;
    $dobr = !empty($_POST['dobr']) ? 1 : 0;
    $icon = (!empty($_POST['icon']) && is_file(XOOPS_ROOT_PATH . "/images/subject/" . $_POST['icon']) ) ? $_POST['icon'] : '';
    $attachsig = !empty($_POST['attachsig']) && $topic_handler->getPermission($forum_obj, $topic_status, 'signature');
    $view_require = !empty($_POST['view_require']) ? $_POST['view_require'] : '';
    $post_karma = (($view_require == 'require_karma') && isset($_POST['post_karma'])) ? intval($_POST['post_karma']) : 0;
    $require_reply = ($view_require == 'require_reply');
    $post_obj->setVar('subject', $subject);
    $editwhy = (!empty($_POST['editwhy'])) ? xoops_trim($_POST['editwhy']) : "";

    if ($dohtml && !newbb_isAdmin($forum_obj) ) {
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
    if ( !empty($post_id) ) $post_obj->setPostEdit($poster_name,$editwhy); // is reply

    $attachments_tmp = array();
    if (!empty($_POST["attachments_tmp"])) {
        $attachments_tmp = unserialize(base64_decode($_POST["attachments_tmp"]));
        if (isset($_POST["delete_tmp"]) && count($_POST["delete_tmp"])) {
            foreach ($_POST["delete_tmp"] as $key) {
                unlink(XOOPS_ROOT_PATH . "/" . $xoopsModuleConfig['dir_attachments'] . "/" . $attachments_tmp[$key][0]);
                unset($attachments_tmp[$key]);
            }
        }
    }
    if (count($attachments_tmp)) {
        foreach ($attachments_tmp as $key => $attach) {
            if (rename(XOOPS_CACHE_PATH . "/" . $attachments_tmp[$key][0], XOOPS_ROOT_PATH . "/" . $xoopsModuleConfig['dir_attachments'] . "/" . $attachments_tmp[$key][0])) {
                $post_obj->setAttachment($attach[0], $attach[1], $attach[2]);
            }
        }
    }
    $error_upload = '';

    if (isset($_FILES['userfile']['name']) && $_FILES['userfile']['name'] != '' && $topic_handler->getPermission($forum_obj, $topic_status, 'attach') ) {
        require_once XOOPS_ROOT_PATH . '/modules/' . $xoopsModule->getVar("dirname", "n") . '/class/uploader.php';
        $maxfilesize = $forum_obj->getVar('attach_maxkb') * 1024;
        $uploaddir = XOOPS_CACHE_PATH;

        $uploader = new newbb_uploader(
            $uploaddir,
            $forum_obj->getVar('attach_ext'),
            intval($maxfilesize),
            intval($xoopsModuleConfig['max_img_width']),
            intval($xoopsModuleConfig['max_img_height'])
        );

        if ($_FILES['userfile']['error'] > 0) {
            switch ($_FILES['userfile']['error']) {
                case 1:
                    $error_message[] = _MD_NEWBB_MAXUPLOADFILEINI;
                    break;
                case 2:
                    $error_message[] = sprintf(_MD_NEWBB_MAXKB,$forum_obj->getVar('attach_maxkb'));
                    break;
                default:
                    $error_message[] = _MD_NEWBB_UPLOAD_ERRNODEF;
                    break;
            }
        } else {
            $uploader->setCheckMediaTypeByExt();

            if ( $uploader->fetchMedia( $_POST['xoops_upload_file'][0]) ) {
                $prefix = is_object($xoopsUser) ? strval($xoopsUser->uid()) . '_' : 'newbb_';
                $uploader->setPrefix($prefix);
                if ( !$uploader->upload() ) {
                    $error_message[] = $error_upload = $uploader->getErrors();
                } else {
                    if ( is_file( $uploader->getSavedDestination() )) {
                        if (rename(XOOPS_CACHE_PATH . "/" . $uploader->getSavedFileName(), XOOPS_ROOT_PATH . "/" . $xoopsModuleConfig['dir_attachments'] . "/" . $uploader->getSavedFileName())) {
                            $post_obj->setAttachment($uploader->getSavedFileName(), $uploader->getMediaName(), $uploader->getMediaType());
                        }
                    }
                }
            } else {
                $error_message[] = $error_upload = $uploader->getErrors();
            }
        }
    }

    $postid = $post_handler->insert($post_obj);

    if (!$postid) {
        include_once XOOPS_ROOT_PATH . '/header.php';
        xoops_error($post_obj->getErrors());
        include_once XOOPS_ROOT_PATH . '/footer.php';
        exit();
    }
    newbb_setsession("LP", time()); // Recording last post time
    $topic_obj =& $topic_handler->get( $post_obj->getVar("topic_id") );
    $uid = (is_object($xoopsUser)) ? $xoopsUser->getVar('uid') : 0;
    if ( newbb_isAdmin($forum_obj)
        ||
        ( $topic_handler->getPermission($forum_obj, $topic_status, 'type') && ($topic_id == 0 || $uid == $topic_obj->getVar('topic_poster'))
        )
    ) {
        $topic_obj->setVar("type_id", @$_POST["type_id"]);
    }

    if (!empty($xoopsModuleConfig['do_tag']) && $post_obj->isTopic()) {
        $topic_obj->setVar("topic_tags", @$_POST["topic_tags"]);
    }
    $topic_handler->insert($topic_obj);

    // Set read mark
    if (!empty($isnew)) {
        require_once XOOPS_ROOT_PATH . "/modules/newbb/include/functions.read.php";
        newbb_setRead("topic", $topic_obj->getVar("topic_id"), $postid);
        if (!$post_obj->getVar("pid")) {
            newbb_setRead("forum", $forum_obj->getVar('forum_id'), $postid);
        }
    }

    $post_obj->loadFilters(empty($isnew) ? "update" : "insert");

    // Define tags for notification message
    if ($approved && !empty($xoopsModuleConfig['notification_enabled']) && !empty($isnew)) {
        $tags = array();
        $tags['THREAD_NAME'] = $_POST['subject'];
        $tags['THREAD_URL'] = XOOPS_URL . '/modules/' . $xoopsModule->getVar("dirname", "n") . '/viewtopic.php?post_id=' . $postid ;
        $tags['POST_URL'] = $tags['THREAD_URL']; // . '#forumpost' . $postid;
        include_once 'include/notification.inc.php';
        $forum_info = newbb_notify_iteminfo ('forum', $forum_obj->getVar('forum_id'));
        $tags['FORUM_NAME'] = $forum_info['name'];
        $tags['FORUM_URL'] = $forum_info['url'];
        $notification_handler =& xoops_gethandler('notification');
        if (empty($isreply)) {
            // Notify of new thread
            $notification_handler->triggerEvent('forum', $forum_obj->getVar('forum_id'), 'new_thread', $tags);
        } else {
            // Notify of new post
            $notification_handler->triggerEvent('thread', $topic_id, 'new_post', $tags);
            $_tags["name"] = $tags['THREAD_NAME'];
            $_tags['url'] = $tags['POST_URL'];
            $_tags['uid'] = $uid;
            $notification_handler->triggerEvent('thread', $topic_id, 'post', $_tags);
        }
        $notification_handler->triggerEvent('global', 0, 'new_post', $tags);
        $notification_handler->triggerEvent('forum', $forum_obj->getVar('forum_id'), 'new_post', $tags);
        $tags['POST_CONTENT'] = $myts->stripSlashesGPC($_POST['message']);
        $tags['POST_NAME'] = $myts->stripSlashesGPC($_POST['subject']);
        $notification_handler->triggerEvent('global', 0, 'new_fullpost', $tags);
        $notification_handler->triggerEvent('forum', $forum_obj->getVar('forum_id'), 'new_fullpost', $tags);
    }

    // If user checked notification box, subscribe them to the
    // appropriate event; if unchecked, then unsubscribe
    if (!empty($xoopsUser) && !empty($xoopsModuleConfig['notification_enabled'])) {
        $notification_handler = xoops_gethandler('notification');
        if (empty($_POST['notify'])) {
            $notification_handler->unsubscribe('thread', $post_obj->getVar('topic_id'), 'new_post');
        } elseif ($_POST['notify'] > 0) {
            $notification_handler->subscribe('thread', $post_obj->getVar('topic_id'), 'new_post');
        }
        // elseif ($_POST['notify']<0) keep it as it is
    }

    if ($approved) {
        if (!empty($xoopsModuleConfig['cache_enabled'])) {
            newbb_setsession("t" . $post_obj->getVar("topic_id"), null);
        }
        // Update user
        if ($uid > 0) {
            $sql =	"SELECT count(*)".
                    "	FROM " . $xoopsDB->prefix("bb_topics") .
                    "	WHERE approved=1 AND topic_poster =" . $uid;
            $ret = $xoopsDB->query($sql);
            list($topics) = $xoopsDB->fetchRow($ret);

            $sql =	"	SELECT count(*)".
                    "	FROM " . $xoopsDB->prefix("bb_topics") .
                    "	WHERE approved=1 AND topic_digest > 0 AND topic_poster =" . $uid;
            $ret = $xoopsDB->query($sql);
            list($digests) = $xoopsDB->fetchRow($ret);

            $sql =	"	SELECT count(*), MAX(post_time)".
                    "	FROM " . $xoopsDB->prefix("bb_posts") .
                    "	WHERE approved=1 AND uid =" . $uid;
            $ret = $xoopsDB->query($sql);
            list($posts, $lastpost) = $xoopsDB->fetchRow($ret);

            $xoopsDB->queryF(
                    "	REPLACE INTO " . $xoopsDB->prefix("bb_user_stats") .
                    " 	SET uid = '{$uid}', user_topics = '{$topics}', user_posts = '{$posts}', user_digests = '{$digests}', user_lastpost = '{$lastpost}'"
                    );
        }

        $redirect = XOOPS_URL."/modules/newbb/viewtopic.php?post_id=" . $postid ;
        $message = _MD_THANKSSUBMIT . "<br />" . $error_upload;

    } else {
        $redirect = XOOPS_URL."/modules/newbb/viewforum.php?forum=" . $post_obj->getVar('forum_id');
        $message = _MD_THANKSSUBMIT . "<br />" . _MD_WAITFORAPPROVAL . "<br />" . $error_upload;
    }

    if ($op == "add") {
        redirect_header(XOOPS_URL."/modules/newbb/polls.php?op=add&amp;forum=" . $post_obj->getVar('forum_id') . "&amp;topic_id=" . $post_obj->getVar('topic_id'), 1, _MD_ADDPOLL);
    } else {
        redirect_header($redirect, 2, $message);
    }
}

$xoopsOption['template_main'] =  'newbb_edit_post.tpl';
$xoopsConfig["module_cache"][$xoopsModule->getVar("mid")] = 0;
// irmtfan remove and move to footer.php
//$xoopsOption['xoops_module_header']= $xoops_module_header;
// irmtfan include header.php after defining $xoopsOption['template_main']
include_once XOOPS_ROOT_PATH . "/header.php";
//$xoopsTpl->assign('xoops_module_header', $xoops_module_header);

if ( !empty($_POST['contents_upload']) ) {
    $attachments_tmp = array();
    if (!empty($_POST["attachments_tmp"])) {
        $attachments_tmp = unserialize(base64_decode($_POST["attachments_tmp"]));
        if (isset($_POST["delete_tmp"]) && count($_POST["delete_tmp"])) {
            foreach ($_POST["delete_tmp"] as $key) {
                unlink($uploaddir = XOOPS_ROOT_PATH . "/" . $xoopsModuleConfig['dir_attachments'] . "/" . $attachments_tmp[$key][0]);
                unset($attachments_tmp[$key]);
            }
        }
    }

    $error_upload = '';
    if ( isset($_FILES['userfile']['name']) && $_FILES['userfile']['name']!='' ) {
        require_once XOOPS_ROOT_PATH . '/modules/' . $xoopsModule->getVar("dirname", "n") . '/class/uploader.php';
        $maxfilesize = $forum_obj->getVar('attach_maxkb') * 1024;
        $uploaddir = XOOPS_CACHE_PATH;

        $uploader = new newbb_uploader(
            $uploaddir,
            $forum_obj->getVar('attach_ext'),
            intval($maxfilesize),
            intval($xoopsModuleConfig['max_img_width']),
            intval($xoopsModuleConfig['max_img_height'])
        );
        if ($_FILES['userfile']['error'] > 0) {
            switch ($_FILES['userfile']['error']) {
                case 1:
                    $error_message[] = _MD_NEWBB_MAXUPLOADFILEINI;
                    break;
                case 2:
                    $error_message[] = sprintf(_MD_NEWBB_MAXKB,$forum_obj->getVar('attach_maxkb'));
                    break;
                default:
                    $error_message[] = _MD_NEWBB_UPLOAD_ERRNODEF;
                    break;
            }
        } else {
            $uploader->setCheckMediaTypeByExt();
            if ( $uploader->fetchMedia( $_POST['xoops_upload_file'][0]) ) {
                $prefix = is_object($xoopsUser) ? strval($xoopsUser->uid()) . '_' : 'newbb_';
                $uploader->setPrefix($prefix);
                if ( !$uploader->upload() ) {
                    $error_message[] = $error_upload = $uploader->getErrors();
                } else {
                    if ( is_file( $uploader->getSavedDestination() )) {
                        $attachments_tmp[strval(time())]=array(
                            $uploader->getSavedFileName(),
                            $uploader->getMediaName(),
                            $uploader->getMediaType()
                        );
                    }
                }
            } else {
                $error_message[] = $error_upload = $uploader->getErrors();
            }
        }
   }
}

if ( !empty($_POST['contents_preview']) || !empty($_GET['contents_preview']) ) {
    if (!empty($_POST["attachments_tmp"])) {
        $attachments_tmp = unserialize(base64_decode($_POST["attachments_tmp"]));
    }

    $p_subject = $myts->htmlSpecialChars($myts->stripSlashesGPC($_POST['subject']));
    $dosmiley = empty($_POST['dosmiley']) ? 0 : 1;
    $dohtml = empty($_POST['dohtml']) ? 0 : 1;
    $doxcode = empty($_POST['doxcode']) ? 0 : 1;
    $dobr = empty($_POST['dobr']) ? 0 : 1;
    $p_message = $_POST['message'];
    $p_message = $myts->previewTarea($p_message, $dohtml, $dosmiley, $doxcode, 1, $dobr);
    $p_date = formatTimestamp(time());
    if ($post_obj->isNew()) {
        if (is_object($xoopsUser)) {
            $p_name = $xoopsUser->getVar("uname");
            if (!empty($xoopsModuleConfig['show_realname']) && $xoopsUser->getVar("name")) {
                $p_name = $xoopsUser->getVar("name");
            }
        }
    } elseif ($post_obj->getVar('uid')) {
        $p_name = newbb_getUnameFromId( $post_obj->getVar('uid'), $xoopsModuleConfig['show_realname'] );
    }
    if (empty($p_name)) {
        $p_name = empty($_POST['poster_name']) ? htmlspecialchars($xoopsConfig['anonymous']) : htmlSpecialChars($myts->stripSlashesGPC($_POST['poster_name']));
    }

    $post_preview = array(
                        "subject"   => $p_subject,
                        "meta"      => _MD_BY . " " . $p_name . " " . _MD_ON . " " . $p_date,
                        "content"   => $p_message,
                        );
    $xoopsTpl->assign_by_ref("post_preview", $post_preview);
}

if ( !empty($_POST['contents_upload']) || !empty($_POST['contents_preview']) || !empty($_GET['contents_preview']) || !empty($_POST['editor'])) {
    $editor = empty($_POST['editor']) ? "" : $_POST['editor'];
    $dosmiley = empty($_POST['dosmiley']) ? 0 : 1;
    $dohtml = empty($_POST['dohtml']) ? 0 : 1;
    $doxcode = empty($_POST['doxcode']) ? 0 : 1;
    $dobr = empty($_POST['dobr']) ? 0 : 1;
    $subject = $myts->htmlSpecialChars($myts->stripSlashesGPC($_POST['subject']));
    $message = $myts->htmlSpecialChars($myts->stripSlashesGPC($_POST['message']));
    $poster_name = isset($_POST['poster_name']) ? $myts->htmlSpecialChars($myts->stripSlashesGPC($_POST['poster_name'])) : '';
    $hidden = isset($_POST['hidden']) ? $myts->htmlSpecialChars($myts->stripSlashesGPC($_POST['hidden'])) : '';
    $notify = @intval($_POST['notify']);
    $attachsig = !empty($_POST['attachsig']) ? 1 : 0;
    $isreply = !empty($_POST['isreply']) ? 1 : 0;
    $isedit = !empty($_POST['isedit']) ? 1 : 0;
    $icon = (!empty($_POST['icon']) && is_file(XOOPS_ROOT_PATH . "/images/subject/" . $_POST['icon']) ) ? $_POST['icon'] : '';
    $view_require = isset($_POST['view_require']) ? $_POST['view_require'] : '';
    $post_karma = ( ($view_require == 'require_karma') && isset($_POST['post_karma']) )? intval($_POST['post_karma']) : 0 ;
    $require_reply = ($view_require == 'require_reply') ? 1 : 0;

    if (empty($_POST['contents_upload'])) $contents_preview = 1;
    $attachments = $post_obj->getAttachment();
    $xoopsTpl->assign("error_message", implode("<br />", $error_message));

    include __DIR__ . '/include/form.post.php';
}
// irmtfan move to footer.php
include_once __DIR__ . "/footer.php";
include XOOPS_ROOT_PATH . '/footer.php';
