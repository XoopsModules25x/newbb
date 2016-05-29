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

// defined('XOOPS_ROOT_PATH') || exit('XOOPS root path not defined');

/**
 * Function to a list of user names associated with their user IDs
 * @param        $uid
 * @param  int   $usereal
 * @param  bool  $linked
 * @return array
 */
function &newbb_getUnameFromIds($uid, $usereal = 0, $linked = false)
{
    xoops_load('xoopsuserutility');
    $ids = XoopsUserUtility::getUnameFromIds($uid, $usereal, $linked);

    return $ids;
}

/**
 * @param         $uid
 * @param  int    $usereal
 * @param  bool   $linked
 * @return string
 */
function newbb_getUnameFromId($uid, $usereal = 0, $linked = false)
{
    xoops_load('xoopsuserutility');

    return XoopsUserUtility::getUnameFromId($uid, $usereal, $linked);
}

// Adapted from PMA_getIp() [phpmyadmin project]
/**
 * @param  bool $asString
 * @return mixed
 */
function newbb_getIP($asString = false)
{
    xoops_load('xoopsuserutility');

    return XoopsUserUtility::getIP($asString);
    //    return '2001:cdba:0000:0000:0000:0000:3257:9652'; //for testing
}

/**
 * Function to check if a user is an administrator of the module
 *
 * @param  int $user
 * @param  int $mid
 * @return bool
 */
function newbb_isAdministrator($user = -1, $mid = 0)
{
    global $xoopsModule;

    if (is_numeric($user) && $user == -1) {
        $user = $GLOBALS['xoopsUser'];
    }
    if (!is_object($user) && (int)$user < 1) {
        return false;
    }
    $uid = is_object($user) ? $user->getVar('uid') : (int)$user;

    if (!$mid) {
        if (is_object($xoopsModule) && 'newbb' === $xoopsModule->getVar('dirname', 'n')) {
            $mid = $xoopsModule->getVar('mid', 'n');
        } else {
            $modhandler   = xoops_getHandler('module');
            $newbb_module = $modhandler->getByDirname('newbb');
            $mid          = $newbb_module->getVar('mid', 'n');
            unset($newbb_module);
        }
    }

    if (is_object($xoopsModule) && is_object($GLOBALS['xoopsUser']) && $mid == $xoopsModule->getVar('mid', 'n') && $uid == $GLOBALS['xoopsUser']->getVar('uid', 'n')) {
        return $GLOBALS['xoopsUserIsAdmin'];
    }

    $memberHandler = xoops_getHandler('member');
    $groups        = $memberHandler->getGroupsByUser($uid);

    $modulepermHandler = xoops_getHandler('groupperm');

    return $modulepermHandler->checkRight('module_admin', $mid, $groups);
}

/**
 * Function to check if a user is a moderator of a forum
 *
 * @param       $forum
 * @param  int  $user
 * @return bool
 */
function newbb_isModerator(&$forum, $user = -1)
{
    if (!is_object($forum)) {
        $forum_id = (int)$forum;
        if ($forum_id == 0) {
            return false;
        }
        $forumHandler = xoops_getModuleHandler('forum', 'newbb');
        $forum        = $forumHandler->get($forum_id);
    }

    if (is_numeric($user) && $user == -1) {
        $user = $GLOBALS['xoopsUser'];
    }
    if (!is_object($user) && (int)$user < 1) {
        return false;
    }
    $uid = is_object($user) ? $user->getVar('uid', 'n') : (int)$user;

    return in_array($uid, $forum->getVar('forum_moderator'), true);
}

/**
 * Function to check if a user has moderation permission over a forum
 *
 * @param  int $forum
 * @return bool
 */
function newbb_isAdmin($forum = 0)
{
    global $xoopsModule;
    static $_cachedModerators;

    if (empty($forum)) {
        return $GLOBALS['xoopsUserIsAdmin'];
    }

    if (!is_object($GLOBALS['xoopsUser'])) {
        return false;
    }

    if ($GLOBALS['xoopsUserIsAdmin'] && $xoopsModule->getVar('dirname') === 'newbb') {
        return true;
    }

    $cache_id = is_object($forum) ? $forum->getVar('forum_id', 'n') : (int)$forum;
    if (!isset($_cachedModerators[$cache_id])) {
        if (!is_object($forum)) {
            $forumHandler = xoops_getModuleHandler('forum', 'newbb');
            $forum        = $forumHandler->get((int)$forum);
        }
        $_cachedModerators[$cache_id] = $forum->getVar('forum_moderator');
    }

    return in_array($GLOBALS['xoopsUser']->getVar('uid'), $_cachedModerators[$cache_id]);
}

/* use hardcoded DB query to save queries */
/**
 * @param  array $uid
 * @return array
 */
function newbb_isModuleAdministrators(array $uid = array())
{
    global $xoopsModule;
    $module_administrators = array();

    if (!(bool)$uid) {
        return $module_administrators;
    }
    $mid = $xoopsModule->getVar('mid');

    $sql = 'SELECT COUNT(l.groupid) AS count, l.uid FROM ' .
           $GLOBALS['xoopsDB']->prefix('groups_users_link') .
           ' AS l' .
           ' LEFT JOIN ' .
           $GLOBALS['xoopsDB']->prefix('group_permission') .
           ' AS p ON p.gperm_groupid=l.groupid' .
           ' WHERE l.uid IN (' .
           implode(', ', array_map('intval', $uid)) .
           ')' .
           "    AND p.gperm_modid = '1' AND p.gperm_name = 'module_admin' AND p.gperm_itemid = '" .
           (int)$mid .
           "'" .
           ' GROUP BY l.uid';
    if ($result = $GLOBALS['xoopsDB']->query($sql)) {
        while ($myrow = $GLOBALS['xoopsDB']->fetchArray($result)) {
            if (!empty($myrow['count'])) {
                $module_administrators[] = $myrow['uid'];
            }
        }
    }

    return $module_administrators;
}

/* use hardcoded DB query to save queries */
/**
 * @param  array $uid
 * @param  int   $mid
 * @return array
 */
function newbb_isForumModerators(array $uid = array(), $mid = 0)
{
    $forum_moderators = array();

    if (!(bool)$uid) {
        return $forum_moderators;
    }

    $sql = 'SELECT forum_moderator FROM ' . $GLOBALS['xoopsDB']->prefix('bb_forums');
    if ($result = $GLOBALS['xoopsDB']->query($sql)) {
        while ($myrow = $GLOBALS['xoopsDB']->fetchArray($result)) {
            if (empty($myrow['forum_moderator'])) {
                continue;
            }
            $forum_moderators = array_merge($forum_moderators, unserialize($myrow['forum_moderator']));
        }
    }

    return array_unique($forum_moderators);
}
//ENDIF;
