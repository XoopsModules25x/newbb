<?php
/**
 * NewBB 4.3x, the forum module for XOOPS project
 *
 * @copyright    XOOPS Project (http://xoops.org)
 * @license        http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author        Taiwen Jiang (phppp or D.J.) <phppp@users.sourceforge.net>
 * @since        4.00
 * @version        $Id $
 * @package        module::newbb
 */

global $xoopsModule, $myts, $xoopsUser, $forum_obj;

if (!defined('XOOPS_ROOT_PATH') || !is_object($forum_obj) || !is_object($GLOBALS['xoopsUser']) || !is_object($xoopsModule)) {
    return;
}

$forum_id     = $forum_obj->getVar("forum_id");
$postHandler =& xoops_getmodulehandler('post', 'newbb');
$post_obj     =& $postHandler->create();
$post_obj->setVar('poster_ip', newbb_getIP());
$post_obj->setVar('uid', $GLOBALS['xoopsUser']->getVar("uid"));
$post_obj->setVar('approved', 1);
$post_obj->setVar('forum_id', $forum_id);

$subject = sprintf(_MD_WELCOME_SUBJECT, $GLOBALS['xoopsUser']->getVar('uname'));
$post_obj->setVar('subject', $subject);
$post_obj->setVar('dohtml', 1);
$post_obj->setVar('dosmiley', 1);
$post_obj->setVar('doxcode', 0);
$post_obj->setVar('dobr', 1);
$post_obj->setVar('icon', "");
$post_obj->setVar('attachsig', 1);
$post_obj->setVar('post_time', time());

$categories = array();

$module_handler =& xoops_gethandler('module');
if ($mod = @$module_handler->getByDirname('profile', true)) {
    $gpermHandler = &xoops_gethandler('groupperm');
    $groups        = array(XOOPS_GROUP_ANONYMOUS, XOOPS_GROUP_USERS);

    if (!defined("_PROFILE_MA_ALLABOUT")) {
        $mod->loadLanguage();
    }
    $grouppermHandler =& xoops_getmodulehandler('permission', 'newbb');
    $show_ids          = $grouppermHandler->getItemIds('profile_show', $groups, $mod->getVar('mid'));
    $visible_ids       = $grouppermHandler->getItemIds('profile_visible', $groups, $mod->getVar('mid'));
    unset($mod);
    $fieldids         = array_intersect($show_ids, $visible_ids);
    $profile_handler  =& xoops_gethandler('profile');
    $fields           = $profile_handler->loadFields();
    $cat_handler      =& xoops_getmodulehandler('category', 'profile');
    $categories       = $cat_handler->getObjects(null, true, false);
    $fieldcat_handler =& xoops_getmodulehandler('fieldcategory', 'profile');
    $fieldcats        = $fieldcat_handler->getObjects(null, true, false);

    // Add core fields
    $categories[0]['cat_title'] = sprintf(_PROFILE_MA_ALLABOUT, $GLOBALS['xoopsUser']->getVar('uname'));
    $avatar                     = trim($GLOBALS['xoopsUser']->getVar('user_avatar'));
    if (!empty($avatar) && $avatar !== "blank.gif") {
        $categories[0]['fields'][] = array('title' => _PROFILE_MA_AVATAR, 'value' => "<img src='" . XOOPS_UPLOAD_URL . "/" . $GLOBALS['xoopsUser']->getVar('user_avatar') . "' alt='" . $GLOBALS['xoopsUser']->getVar('uname') . "' />");
        $weights[0][]              = 0;
    }
    if ($GLOBALS['xoopsUser']->getVar('user_viewemail') === 1) {
        $email                     = $GLOBALS['xoopsUser']->getVar('email', 'E');
        $categories[0]['fields'][] = array('title' => _PROFILE_AM_EMAIL, 'value' => $email);
        $weights[0][]              = 0;
    }

    // Add dynamic fields
    foreach (array_keys($fields) as $i) {
        if (in_array($fields[$i]->getVar('fieldid'), $fieldids)) {
            $catid = isset($fieldcats[$fields[$i]->getVar('fieldid')]) ? $fieldcats[$fields[$i]->getVar('fieldid')]['catid'] : 0;
            $value = $fields[$i]->getOutputValue($GLOBALS['xoopsUser']);
            if (is_array($value)) {
                $value = implode('<br />', array_values($value));
            }

            if (empty($value)) {
                continue;
            }
            $categories[$catid]['fields'][] = array('title' => $fields[$i]->getVar('field_title'), 'value' => $value);
            $weights[$catid][]              = isset($fieldcats[$fields[$i]->getVar('fieldid')]) ? (int)($fieldcats[$fields[$i]->getVar('fieldid')]['field_weight']) : 1;
        }
    }

    foreach (array_keys($categories) as $i) {
        if (isset($categories[$i]['fields'])) {
            array_multisort($weights[$i], SORT_ASC, array_keys($categories[$i]['fields']), SORT_ASC, $categories[$i]['fields']);
        }
    }
    ksort($categories);
}

$message = sprintf(_MD_WELCOME_MESSAGE, $GLOBALS['xoopsUser']->getVar('uname')) . "\n\n";
$message .= _PROFILE . ": <a href='" . XOOPS_URL . "/userinfo.php?uid=" . $GLOBALS['xoopsUser']->getVar('uid') . "'><strong>" . $GLOBALS['xoopsUser']->getVar('uname') . "</strong></a> ";
//$message .= " | <a href='".XOOPS_URL . "/pmlite.php?send2=1&amp;to_userid=" . $GLOBALS['xoopsUser']->getVar('uid')."'>"._MD_PM."</a>\n";
foreach ($categories as $category) {
    if (isset($category["fields"])) {
        $message .= "\n\n" . $category["cat_title"] . ":\n\n";
        foreach ($category["fields"] as $field) {
            if (empty($field["value"])) {
                continue;
            }
            $message .= $field["title"] . ": " . $field["value"] . "\n";
        }
    }
}
$post_obj->setVar('post_text', $message);
$post_id = $postHandler->insert($post_obj);

if (!empty($GLOBALS['xoopsModuleConfig']['notification_enabled'])) {
    $tags                = array();
    $tags['THREAD_NAME'] = $subject;
    $tags['THREAD_URL']  = XOOPS_URL . '/modules/' . $xoopsModule->getVar("dirname") . '/viewtopic.php?post_id=' . $post_id . '&amp;topic_id=' . $post_obj->getVar('topic_id') . '&amp;forum=' . $forum_id;
    $tags['POST_URL']    = $tags['THREAD_URL'] . '#forumpost' . $post_id;
    include_once 'include/notification.inc.php';
    $forum_info           = newbb_notify_iteminfo('forum', $forum_id);
    $tags['FORUM_NAME']   = $forum_info['name'];
    $tags['FORUM_URL']    = $forum_info['url'];
    $notificationHandler =& xoops_gethandler('notification');
    $notificationHandler->triggerEvent('forum', $forum_id, 'new_thread', $tags);
    $notificationHandler->triggerEvent('global', 0, 'new_post', $tags);
    $notificationHandler->triggerEvent('forum', $forum_id, 'new_post', $tags);
    $tags['POST_CONTENT'] = $myts->stripSlashesGPC($message);
    $tags['POST_NAME']    = $myts->stripSlashesGPC($subject);
    $notificationHandler->triggerEvent('global', 0, 'new_fullpost', $tags);
}
