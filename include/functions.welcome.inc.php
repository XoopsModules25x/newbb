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

global $xoopsModule, $myts, $xoopsUser, $forumObject;

if (!defined('XOOPS_ROOT_PATH') || !is_object($forumObject) || !is_object($GLOBALS['xoopsUser'])
    || !is_object($xoopsModule)) {
    return;
}

$forum_id    = $forumObject->getVar('forum_id');
$postHandler = Newbb\Helper::getInstance()->getHandler('Post');
$postObject  = $postHandler->create();
$postObject->setVar('poster_ip', \Xmf\IPAddress::fromRequest()->asReadable());
$postObject->setVar('uid', $GLOBALS['xoopsUser']->getVar('uid'));
$postObject->setVar('approved', 1);
$postObject->setVar('forum_id', $forum_id);

$subject = sprintf(_MD_NEWBB_WELCOME_SUBJECT, $GLOBALS['xoopsUser']->getVar('uname'));
$postObject->setVar('subject', $subject);
$postObject->setVar('dohtml', 1);
$postObject->setVar('dosmiley', 1);
$postObject->setVar('doxcode', 0);
$postObject->setVar('dobr', 1);
$postObject->setVar('icon', '');
$postObject->setVar('attachsig', 1);
$postObject->setVar('post_time', time());

$categories = [];

/** @var \XoopsModuleHandler $moduleHandler */
$moduleHandler = xoops_getHandler('module');
if ($mod = @$moduleHandler->getByDirname('profile', true)) {
    $gpermHandler = xoops_getHandler('groupperm');
    $groups       = [XOOPS_GROUP_ANONYMOUS, XOOPS_GROUP_USERS];

    if (!defined('_PROFILE_MA_ALLABOUT')) {
        $mod->loadLanguage();
    }
    /** var Newbb\PermissionHandler $permHandler */
    $permHandler = Newbb\Helper::getInstance()->getHandler('Permission');
    $show_ids    = $permHandler->getItemIds('profile_show', $groups, $mod->getVar('mid'));
    $visible_ids = $permHandler->getItemIds('profile_visible', $groups, $mod->getVar('mid'));
    unset($mod);
    $fieldids = array_intersect($show_ids, $visible_ids);
    /** @var \ProfileProfileHandler $profileHandler */
    $profileHandler = xoops_getModuleHandler('profile', 'profile');
    $fields         = $profileHandler->loadFields();
    /** @var \ProfileCategoryHandler $catHandler */
    $catHandler = xoops_getModuleHandler('category', 'profile');
    $categories = $catHandler->getObjects(null, true, false);
    /** @var \ProfileFieldHandler $fieldcatHandler */
    $fieldcatHandler = xoops_getModuleHandler('category', 'profile');
    $fieldcats       = $fieldcatHandler->getObjects(null, true, false);

    // Add core fields
    $categories[0]['cat_title'] = sprintf(_MD_NEWBB_AUTO_CREATE_ABOUT, $GLOBALS['xoopsUser']->getVar('uname'));
    $avatar                     = trim($GLOBALS['xoopsUser']->getVar('user_avatar'));
    if (!empty($avatar) && 'blank.gif' !== $avatar) {
        $categories[0]['fields'][] = [
            'title' => _MD_NEWBB_AUTO_CREATE_AVATARS,
            'value' => "<img src='" . XOOPS_UPLOAD_URL . '/' . $GLOBALS['xoopsUser']->getVar('user_avatar') . "' alt='" . $GLOBALS['xoopsUser']->getVar('uname') . "' />"
        ];
        $weights[0][]              = 0;
    }
    if (1 == $GLOBALS['xoopsUser']->getVar('user_viewemail')) {
        $email                     = $GLOBALS['xoopsUser']->getVar('email', 'E');
        $categories[0]['fields'][] = ['title' => _MD_NEWBB_AUTO_CREATE_EMAIL, 'value' => $email];
        $weights[0][]              = 0;
    }

    // Add dynamic fields
    foreach (array_keys($fields) as $i) {
        if (in_array($fields[$i]->getVar('fieldid'), $fieldids)) {
            $catid = isset($fieldcats[$fields[$i]->getVar('fieldid')]) ? $fieldcats[$fields[$i]->getVar('fieldid')]['catid'] : 0;
            $value = $fields[$i]->getOutputValue($GLOBALS['xoopsUser']);
            if (is_array($value)) {
                $value = implode('<br>', array_values($value));
            }

            if (empty($value)) {
                continue;
            }
            $categories[$catid]['fields'][] = ['title' => $fields[$i]->getVar('field_title'), 'value' => $value];
            $weights[$catid][]              = isset($fieldcats[$fields[$i]->getVar('fieldid')]) ? (int)$fieldcats[$fields[$i]->getVar('fieldid')]['field_weight'] : 1;
        }
    }

    foreach (array_keys($categories) as $i) {
        if (isset($categories[$i]['fields'])) {
            array_multisort($weights[$i], SORT_ASC, array_keys($categories[$i]['fields']), SORT_ASC, $categories[$i]['fields']);
        }
    }
    ksort($categories);
}

$message = sprintf(_MD_NEWBB_WELCOME_MESSAGE, $GLOBALS['xoopsUser']->getVar('uname')) . "\n\n";
//$message .= _PROFILE . ": <a href='" . XOOPS_URL . '/userinfo.php?uid=' . $GLOBALS['xoopsUser']->getVar('uid') . "'><strong>" . $GLOBALS['xoopsUser']->getVar('uname') . '</strong></a> ';
//$message .= " | <a target='_blank' href='".XOOPS_URL . '/pmlite.php?send2=1&amp;to_userid=' . $GLOBALS['xoopsUser']->getVar('uid') . "'>" . _MD_NEWBB_PM . "</a>\n";
$message .= sprintf($GLOBALS['xoopsModuleConfig']['welcome_forum_message']);
//foreach ($categories as $category) {
//    if (isset($category['fields'])) {
//        $message .= "\n\n" . $category['cat_title'] . ":\n\n";
//        foreach ($category['fields'] as $field) {
//            if (empty($field['value'])) {
//                continue;
//            }
//            $message .= $field['title'] . ': ' . $field['value'] . "\n";
//        }
//    }
//}
$postObject->setVar('post_text', $message);
$post_id = $postHandler->insert($postObject);

if (!empty($GLOBALS['xoopsModuleConfig']['notification_enabled'])) {
    $tags                = [];
    $tags['THREAD_NAME'] = $subject;
    $tags['THREAD_URL']  = XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname') . '/viewtopic.php?post_id=' . $post_id . '&amp;topic_id=' . $postObject->getVar('topic_id') . '&amp;forum=' . $forum_id;
    $tags['POST_URL']    = $tags['THREAD_URL'] . '#forumpost' . $post_id;
    require_once __DIR__ . '/notification.inc.php';
    $forum_info         = newbb_notify_iteminfo('forum', $forum_id);
    $tags['FORUM_NAME'] = $forum_info['name'];
    $tags['FORUM_URL']  = $forum_info['url'];
    /** @var \XoopsNotificationHandler $notificationHandler */
    $notificationHandler = xoops_getHandler('notification');
    $notificationHandler->triggerEvent('forum', $forum_id, 'new_thread', $tags);
    $notificationHandler->triggerEvent('global', 0, 'new_post', $tags);
    $notificationHandler->triggerEvent('forum', $forum_id, 'new_post', $tags);
    $tags['POST_CONTENT'] = $message;
    $tags['POST_NAME']    = $subject;
    $notificationHandler->triggerEvent('global', 0, 'new_fullpost', $tags);
}
