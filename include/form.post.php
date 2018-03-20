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

// defined('XOOPS_ROOT_PATH') || die('Restricted access');

require_once $GLOBALS['xoops']->path('class/xoopsformloader.php');

$xoopsTpl->assign('lang_forum_index', sprintf(_MD_NEWBB_FORUMINDEX, htmlspecialchars($GLOBALS['xoopsConfig']['sitename'], ENT_QUOTES)));

$categoryHandler = Newbb\Helper::getInstance()->getHandler('Category');
$categoryObject  = $categoryHandler->get($forumObject->getVar('cat_id'), ['cat_title']);

//check banning
$moderated_id    = (is_object($GLOBALS['xoopsUser'])
                    && $GLOBALS['xoopsUser']->uid() > 0) ? $GLOBALS['xoopsUser']->uid() : 0;
$moderated_ip    = Request::getString('REMOTE_ADDR', '', 'SERVER');
$moderated_forum = $forumObject->getVar('forum_id');
/** @var Newbb\ModerateHandler $moderateHandler */
$moderateHandler = Newbb\Helper::getInstance()->getHandler('Moderate');
if (!$moderateHandler->verifyUser($moderated_id, '', $moderated_forum)) {
    $criteria = new \CriteriaCompo();
    $criteria->add(new \Criteria('uid', $moderated_id, '='));
    $criteria->setSort('mod_end');
    $criteria->setOrder('DESC');
    $mod  = $moderateHandler->getObjects($criteria, false, false);
    $tage = ($mod[0]['mod_end'] - $mod[0]['mod_start']) / 60 / 60 / 24;
    $msg  = $myts->displayTarea(sprintf(_MD_NEWBB_SUSPEND_TEXT, newbbGetUnameFromId($moderated_id), (int)$tage, $mod[0]['mod_desc'], formatTimestamp($mod[0]['mod_end'])), 1);
    xoops_error($msg, _MD_NEWBB_SUSPEND_NOACCESS);
    include $GLOBALS['xoops']->path('footer.php');
    exit();
}

$xoopsTpl->assign('category', ['id' => $forumObject->getVar('cat_id'), 'title' => $categoryObject->getVar('cat_title')]);
$xoopsTpl->assign('parentforum', $forumHandler->getParents($forumObject));
$xoopsTpl->assign([
                      'forum_id'   => $forumObject->getVar('forum_id'),
                      'forum_name' => $forumObject->getVar('forum_name')
                  ]);

if (!is_object($topicObject)) {
    $topicObject = $topicHandler->create();
}

$editby = false;
if ($topicObject->isNew()) {
    $form_title = _MD_NEWBB_POSTNEW;
} elseif ($postObject->isNew()) {
    if (empty($postParentObject)) {
        $postParentObject = $postHandler->get($pid);
    }
    $form_title = _MD_NEWBB_REPLY . ': <a href="' . XOOPS_URL . "/modules/newbb/viewtopic.php?topic_id={$topic_id}&amp;post_id={$pid}\" rel=\"external\">" . $postParentObject->getVar('subject') . '</a>';
} else {
    $form_title = _EDIT . ': <a href="' . XOOPS_URL . "/modules/newbb/viewtopic.php?post_id={$post_id}\" rel=\"external\">" . $postObject->getVar('subject') . '</a>';
    $editby     = true;
}
$xoopsTpl->assign('form_title', $form_title);

foreach ([
             'start',
             'topic_id',
             'post_id',
             'pid',
             'isreply',
             'isedit',
             'contents_preview'
         ] as $getint) {
    ${$getint} = Request::getInt($getint, (!empty(${$getint}) ? ${$getint} : 0), 'GET'); // isset($_GET[$getint]) ? (int)($_GET[$getint]) : ((!empty(${$getint})) ? ${$getint} : 0);
}
foreach ([
             'order',
             'viewmode',
             'hidden',
             'newbb_form',
             'icon',
             'op'
         ] as $getstr) {
    ${$getstr} = Request::getString($getstr, (!empty(${$getstr}) ? ${$getstr} : ''), 'GET'); //isset($_GET[$getstr]) ? $_GET[$getstr] : ((!empty(${$getstr})) ? ${$getstr} : '');
}

/** @var Newbb\TopicHandler $topicHandler */
$topicHandler = Newbb\Helper::getInstance()->getHandler('Topic');
$topic_status = $topicHandler->get(@$topic_id, 'topic_status');

//$filname = XOOPS_URL.$_SERVER['REQUEST_URI'];

$forum_form = new \XoopsThemeForm(htmlspecialchars(@$form_title), 'form_post', XOOPS_URL . '/modules/newbb/post.php', 'post', true);
$forum_form->setExtra('enctype="multipart/form-data"');

if ($editby) {
    $forum_form->addElement(new \XoopsFormText(_MD_NEWBB_EDITEDMSG, 'editwhy', 60, 100, ''));
}

$uid = is_object($GLOBALS['xoopsUser']) ? $GLOBALS['xoopsUser']->getVar('uid') : 0;
if (newbbIsAdmin($forumObject)
    || ($topicHandler->getPermission($forumObject, $topic_status, 'type')
        && (0 == $topic_id
            || $uid == $topicHandler->get(@$topic_id, 'topic_poster')))) {
    $type_id = $topicHandler->get(@$topic_id, 'type_id');
    /** @var Newbb\TypeHandler $typeHandler */
    $typeHandler = Newbb\Helper::getInstance()->getHandler('Type');
    $types       = $typeHandler->getByForum($forumObject->getVar('forum_id'));
    if (!empty($types)) {
        $type_element = new \XoopsFormSelect(_MD_NEWBB_TYPE, 'type_id', $type_id);
        //$type_element = new \XoopsFormRadio(_MD_NEWBB_TYPE, 'type_id', $type_id);
        $type_element->addOption(0, _NONE);
        foreach ($types as $key => $type) {
            //$value = empty($type["type_color"]) ? $type["type_name"] : "<em style=\"font-style: normal; color: " . $type["type_color"] . ";\">" . $type["type_name"] . "</em>";
            $type_element->addOption($key, $type['type_name']);
        }
        $forum_form->addElement($type_element);
    } else {
        $forum_form->addElement(new \XoopsFormHidden('type_id', 0));
    }
}

$subject_form = new \XoopsFormText(_MD_NEWBB_SUBJECTC, 'subject', 60, 100, $subject);
$subject_form->setExtra("tabindex='1'");
$forum_form->addElement($subject_form, true);

if (!is_object($GLOBALS['xoopsUser'])) {
    $required = !empty($GLOBALS['xoopsModuleConfig']['require_name']);
    $forum_form->addElement(new \XoopsFormText(_MD_NEWBB_NAMEMAIL, 'poster_name', 60, 255, (!empty($isedit) && !empty($poster_name)) ? $poster_name : ''), $required);
}

$icons_radio   = new \XoopsFormRadio(_MD_NEWBB_MESSAGEICON, 'icon', $icon);
$subject_icons = \XoopsLists::getSubjectsList();
foreach ($subject_icons as $iconfile) {
    $icons_radio->addOption($iconfile, '<img src="' . XOOPS_URL . '/images/subject/' . $iconfile . '" alt="" />');
}
$forum_form->addElement($icons_radio);

$nohtml = !$topicHandler->getPermission($forumObject, $topic_status, 'html');

if (Request::getString('editor', '', 'POST')) {
    $editor = trim(Request::getString('editor', '', 'POST'));
    newbbSetCookie('editor', $editor);
} elseif (!$editor = newbbGetCookie('editor')) {
    if (empty($editor)) {
        $editor = @ $GLOBALS['xoopsModuleConfig']['editor_default'];
    }
}
if (count(@$GLOBALS['xoopsModuleConfig']['editor_allowed']) > 0) {
    if (!in_array($editor, $GLOBALS['xoopsModuleConfig']['editor_allowed'])) {
        $editor = $GLOBALS['xoopsModuleConfig']['editor_allowed'][0];
        newbbSetCookie('editor', $editor);
    }
}

$forum_form->addElement(new \XoopsFormSelectEditor($forum_form, 'editor', $editor, $nohtml, @$GLOBALS['xoopsModuleConfig']['editor_allowed'][0]));

$editor_configs           = [];
$editor_configs['name']   = 'message';
$editor_configs['value']  = $message;
$editor_configs['rows']   = empty($GLOBALS['xoopsModuleConfig']['editor_rows']) ? 10 : $GLOBALS['xoopsModuleConfig']['editor_rows'];
$editor_configs['cols']   = empty($GLOBALS['xoopsModuleConfig']['editor_cols']) ? 30 : $GLOBALS['xoopsModuleConfig']['editor_cols'];
$editor_configs['width']  = empty($GLOBALS['xoopsModuleConfig']['editor_width']) ? '100%' : $GLOBALS['xoopsModuleConfig']['editor_width'];
$editor_configs['height'] = empty($GLOBALS['xoopsModuleConfig']['editor_height']) ? '400px' : $GLOBALS['xoopsModuleConfig']['editor_height'];

$_editor = new \XoopsFormEditor(_MD_NEWBB_MESSAGEC, $editor, $editor_configs, $nohtml, $onfailure = null);
$forum_form->addElement($_editor, true);

if (!empty($GLOBALS['xoopsModuleConfig']['do_tag']) && (empty($postObject) || $postObject->isTopic())) {
    $topic_tags = '';
    if (Request::getString('topic_tags', '', 'POST')) {
        $topic_tags = $myts->htmlSpecialChars(Request::getString('topic_tags', '', 'POST'));
    } elseif (!empty($topic_id)) {
        $topic_tags = $topicHandler->get($topic_id, 'topic_tags');
    }
    if (xoops_load('formtag', 'tag') && class_exists('TagFormTag')) {
        $forum_form->addElement(new TagFormTag('topic_tags', 60, 255, $topic_tags));
    }
}

$options_tray = new \XoopsFormElementTray(_MD_NEWBB_OPTIONS, '<br>');
if (is_object($GLOBALS['xoopsUser']) && 1 == $GLOBALS['xoopsModuleConfig']['allow_user_anonymous']) {
    $noname          = (!empty($isedit) && is_object($postObject) && 0 == $postObject->getVar('uid')) ? 1 : 0;
    $noname_checkbox = new \XoopsFormCheckBox('', 'noname', $noname);
    $noname_checkbox->addOption(1, _MD_NEWBB_POSTANONLY);
    $options_tray->addElement($noname_checkbox);
}

if (!$nohtml) {
    $html_checkbox = new \XoopsFormCheckBox('', 'dohtml', $dohtml);
    $html_checkbox->addOption(1, _MD_NEWBB_DOHTML);
    $options_tray->addElement($html_checkbox);
} else {
    $forum_form->addElement(new \XoopsFormHidden('dohtml', 0));
}

$smiley_checkbox = new \XoopsFormCheckBox('', 'dosmiley', $dosmiley);
$smiley_checkbox->addOption(1, _MD_NEWBB_DOSMILEY);
$options_tray->addElement($smiley_checkbox);

$xcode_checkbox = new \XoopsFormCheckBox('', 'doxcode', $doxcode);
$xcode_checkbox->addOption(1, _MD_NEWBB_DOXCODE);
$options_tray->addElement($xcode_checkbox);

if (!$nohtml) {
    $br_checkbox = new \XoopsFormCheckBox('', 'dobr', $dobr);
    $br_checkbox->addOption(1, _MD_NEWBB_DOBR);
    $options_tray->addElement($br_checkbox);
} else {
    $forum_form->addElement(new \XoopsFormHidden('dobr', 1));
}

if (is_object($GLOBALS['xoopsUser']) && $topicHandler->getPermission($forumObject, $topic_status, 'signature')) {
    $attachsig_checkbox = new \XoopsFormCheckBox('', 'attachsig', $attachsig);
    $attachsig_checkbox->addOption(1, _MD_NEWBB_ATTACHSIG);
    $options_tray->addElement($attachsig_checkbox);
}
$notify = 0;
if (is_object($GLOBALS['xoopsUser']) && $GLOBALS['xoopsModuleConfig']['notification_enabled']) {
    if (!empty($notify)) {
        // If 'notify' set, use that value (e.g. preview or upload)
        //$notify = 1;
    } else {
        // Otherwise, check previous subscribed status...
        /** @var \XoopsNotificationHandler $notificationHandler */
        $notificationHandler = xoops_getHandler('notification');
        if (!empty($topic_id)
            && $notificationHandler->isSubscribed('thread', $topic_id, 'new_post', $xoopsModule->getVar('mid'), $GLOBALS['xoopsUser']->getVar('uid'))) {
            $notify = 1;
        }
    }

    $notify_checkbox = new \XoopsFormCheckBox('', 'notify', $notify);
    $notify_checkbox->addOption(1, _MD_NEWBB_NEWPOSTNOTIFY);
    $options_tray->addElement($notify_checkbox);
}
$forum_form->addElement($options_tray);

if ($topicHandler->getPermission($forumObject, $topic_status, 'attach')) {
    $upload_tray = new \XoopsFormElementTray(_MD_NEWBB_ATTACHMENT);
    $upload_tray->addElement(new \XoopsFormFile('', 'userfile', $forumObject->getVar('attach_maxkb') * 1024));
    $upload_tray->addElement(new \XoopsFormButton('', 'contents_upload', _MD_NEWBB_UPLOAD, 'submit'));
    $upload_tray->addElement(new \XoopsFormLabel('<br><br>' . _MD_NEWBB_MAX_FILESIZE . ':', $forumObject->getVar('attach_maxkb') . 'Kb; '));
    $extensions = trim(str_replace('|', ' ', $forumObject->getVar('attach_ext')));
    $extensions = (empty($extensions) || '*' === $extensions) ? _ALL : $extensions;
    $upload_tray->addElement(new \XoopsFormLabel(_MD_NEWBB_ALLOWED_EXTENSIONS . ':', $extensions));
    $upload_tray->addElement(new \XoopsFormLabel('<br>' . sprintf(_MD_NEWBB_MAXPIC, $GLOBALS['xoopsModuleConfig']['max_img_height'], $GLOBALS['xoopsModuleConfig']['max_img_width'])));
    $forum_form->addElement($upload_tray);
}

if (!empty($attachments) && is_array($attachments) && count($attachments)) {
    $delete_attach_checkbox = new \XoopsFormCheckBox(_MD_NEWBB_THIS_FILE_WAS_ATTACHED_TO_THIS_POST, 'delete_attach[]');
    foreach ($attachments as $key => $attachment) {
        $attach = ' ' . _DELETE . ' <a href=' . XOOPS_URL . '/' . $GLOBALS['xoopsModuleConfig']['dir_attachments'] . '/' . $attachment['name_saved'] . ' rel="external">' . $attachment['nameDisplay'] . '</a><br>';
        $delete_attach_checkbox->addOption($key, $attach);
    }
    $forum_form->addElement($delete_attach_checkbox);
    unset($delete_attach_checkbox);
}

if (!empty($attachments_tmp) && is_array($attachments_tmp) && count($attachments_tmp)) {
    $delete_attach_checkbox = new \XoopsFormCheckBox(_MD_NEWBB_REMOVE, 'delete_tmp[]');
    $url_prefix             = str_replace(XOOPS_ROOT_PATH, XOOPS_URL, XOOPS_CACHE_PATH);
    foreach ($attachments_tmp as $key => $attachment) {
        $attach = ' <a href="' . $url_prefix . '/' . $attachment[0] . '" rel="external">' . $attachment[1] . '</a><br>';
        $delete_attach_checkbox->addOption($key, $attach);
    }
    $forum_form->addElement($delete_attach_checkbox);
    unset($delete_attach_checkbox);
    $attachments_tmp = base64_encode(serialize($attachments_tmp));
    $forum_form->addElement(new \XoopsFormHidden('attachments_tmp', $attachments_tmp));
}
$radiobox = null;
if ($GLOBALS['xoopsModuleConfig']['enable_karma'] || $GLOBALS['xoopsModuleConfig']['allow_require_reply']) {
    $view_require = $require_reply ? 'require_reply' : ($post_karma ? 'require_karma' : 'require_null');
    $radiobox     = new \XoopsFormRadio(_MD_NEWBB_VIEW_REQUIRE, 'view_require', $view_require);
    if ($GLOBALS['xoopsModuleConfig']['allow_require_reply']) {
        $radiobox->addOption('require_reply', _MD_NEWBB_REQUIRE_REPLY);
    }
    if ($GLOBALS['xoopsModuleConfig']['enable_karma']) {
        $karmas = array_map('trim', explode(',', $GLOBALS['xoopsModuleConfig']['karma_options']));
        if (count($karmas) > 1) {
            foreach ($karmas as $karma) {
                $karma_array[(string)$karma] = (int)$karma;
            }
            $karma_select = new \XoopsFormSelect('', 'post_karma', $post_karma);
            $karma_select->addOptionArray($karma_array);
            $radiobox->addOption('require_karma', _MD_NEWBB_REQUIRE_KARMA . $karma_select->render());
        }
    }
    $radiobox->addOption('require_null', _MD_NEWBB_REQUIRE_NULL);
}
if (null !== $radiobox) {
    $forum_form->addElement($radiobox);
}

if (empty($uid)) {
    $forum_form->addElement(new \XoopsFormCaptcha());
}

$forum_form->addElement(new \XoopsFormHidden('pid', @$pid));
$forum_form->addElement(new \XoopsFormHidden('post_id', @$post_id));
$forum_form->addElement(new \XoopsFormHidden('topic_id', @$topic_id));
$forum_form->addElement(new \XoopsFormHidden('forum', $forumObject->getVar('forum_id')));
$forum_form->addElement(new \XoopsFormHidden('viewmode', @$viewmode));
$forum_form->addElement(new \XoopsFormHidden('order', @$order));
$forum_form->addElement(new \XoopsFormHidden('start', @$start));
$forum_form->addElement(new \XoopsFormHidden('isreply', @$isreply));
$forum_form->addElement(new \XoopsFormHidden('isedit', @$isedit));
$forum_form->addElement(new \XoopsFormHidden('op', @$op));

$button_tray = new \XoopsFormElementTray('');

$submit_button = new \XoopsFormButton('', 'contents_submit', _SUBMIT, 'submit');
$submit_button->setExtra("tabindex='3'");

$cancel_button = new \XoopsFormButton('', 'cancel', _CANCEL, 'button');
if (!empty($topic_id)) {
    $extra = XOOPS_URL . '/modules/newbb/viewtopic.php?topic_id=' . (int)$topic_id;
} else {
    $extra = XOOPS_URL . '/modules/newbb/viewforum.php?forum=' . $forumObject->getVar('forum_id');
}
$cancel_button->setExtra("onclick='location=\"" . $extra . "\"'");
$cancel_button->setExtra("tabindex='6'");

if (!empty($isreply) && !empty($hidden)) {
    $forum_form->addElement(new \XoopsFormHidden('hidden', $hidden));

    $quote_button = new \XoopsFormButton('', 'quote', _MD_NEWBB_QUOTE, 'button');
    $quote_button->setExtra("onclick='xoopsGetElementById(\"message\").value=xoopsGetElementById(\"message\").value+ xoopsGetElementById(\"hidden\").value;xoopsGetElementById(\"hidden\").value=\"\";'");
    $quote_button->setExtra("tabindex='4'");
    $button_tray->addElement($quote_button);
}

$preview_button = new \XoopsFormButton('', 'btn_preview', _PREVIEW, 'button');
$preview_button->setExtra("tabindex='5'");
$preview_button->setExtra('onclick="window.document.forms.' . $forum_form->getName() . '.contents_preview.value=1; window.document.forms.' . $forum_form->getName() . '.submit() ;"');
$forum_form->addElement(new \XoopsFormHidden('contents_preview', 0));

$button_tray->addElement($preview_button);
$button_tray->addElement($submit_button);
$button_tray->addElement($cancel_button);
$forum_form->addElement($button_tray);

//$forum_form->display();
$forum_form->assign($xoopsTpl);
