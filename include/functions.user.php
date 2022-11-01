<?php declare(strict_types=1);

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
 * @license         GNU GPL 2.0 or later (https://www.gnu.org/licenses/gpl-2.0.html)
 * @since           4.0
 * @author          Taiwen Jiang <phppp@users.sourceforge.net>
 */

use XoopsModules\Newbb\{
    Forum,
    Helper
};

/** @var Helper $helper */

/**
 * Function to a list of user names associated with their user IDs
 * @param int  $uid
 * @param int  $usereal
 * @param bool $linked
 * @return array
 */
function newbbGetUnameFromIds($uid, $usereal = 0, $linked = false)
{
    xoops_load('xoopsuserutility');
    $ids = \XoopsUserUtility::getUnameFromIds($uid, $usereal, $linked);

    return $ids;
}

/**
 * @param int  $uid
 * @param int  $usereal
 * @param bool $linked
 * @return string
 */
function newbbGetUnameFromId($uid, $usereal = 0, $linked = false)
{
    xoops_load('xoopsuserutility');

    return \XoopsUserUtility::getUnameFromId($uid, $usereal, $linked);
}

/**
 * Function to check if a user is an administrator of the module
 *
 * @param int|string|array|\XoopsUser $user
 * @param int                         $mid
 * @return bool
 */
function newbbIsAdministrator($user = -1, $mid = 0)
{
    global $xoopsModule;

    if (is_numeric($user) && -1 == $user) {
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
            /** @var \XoopsModuleHandler $moduleHandler */
            $moduleHandler = xoops_getHandler('module');
            $newbb_module  = $moduleHandler->getByDirname('newbb');
            $mid           = $newbb_module->getVar('mid', 'n');
            unset($newbb_module);
        }
    }

    if (is_object($xoopsModule) && is_object($GLOBALS['xoopsUser']) && $mid == $xoopsModule->getVar('mid', 'n')
        && $uid == $GLOBALS['xoopsUser']->getVar('uid', 'n')) {
        return $GLOBALS['xoopsUserIsAdmin'];
    }

    /** @var \XoopsMemberHandler $memberHandler */
    $memberHandler = xoops_getHandler('member');
    $groups        = $memberHandler->getGroupsByUser($uid);

    /** @var \XoopsGroupPermHandler $grouppermHandler */
    $grouppermHandler = xoops_getHandler('groupperm');

    return $grouppermHandler->checkRight('module_admin', $mid, $groups);
}

/**
 * Function to check if a user is a moderator of a forum
 *
 * @param mixed                        $forum
 * @param int|array |string|\XoopsUser $user
 * @return bool
 */
function newbbIsModerator(&$forum, $user = -1)
{
    if (!is_object($forum)) {
        $forum_id = (int)$forum;
        if (0 == $forum_id) {
            return false;
        }
        $forumHandler = Helper::getInstance()->getHandler('Forum');
        $forum        = $forumHandler->get($forum_id);
    }

    if (is_numeric($user) && -1 == $user) {
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
 * @param Forum|int $forum
 * @return bool
 */
function newbbIsAdmin($forum = 0)
{
    global $xoopsModule;
    static $_cachedModerators;

    if (empty($forum)) {
        return $GLOBALS['xoopsUserIsAdmin'];
    }

    if (!is_object($GLOBALS['xoopsUser'])) {
        return false;
    }

    if ($GLOBALS['xoopsUserIsAdmin'] && 'newbb' === $xoopsModule->getVar('dirname')) {
        return true;
    }

    $cache_id = is_object($forum) ? $forum->getVar('forum_id', 'n') : (int)$forum;
    if (!isset($_cachedModerators[$cache_id])) {
        if (!is_object($forum)) {
            $forumHandler = Helper::getInstance()->getHandler('Forum');
            $forum        = $forumHandler->get((int)$forum);
        }
        $_cachedModerators[$cache_id] = $forum->getVar('forum_moderator');
    }

    return in_array($GLOBALS['xoopsUser']->getVar('uid'), $_cachedModerators[$cache_id], true);
}

/* use hardcoded DB query to save queries */
/**
 * @param array $uid
 * @return array
 */
function newbbIsModuleAdministrators(array $uid = [])
{
    global $xoopsModule;
    $module_administrators = [];

    //    $xoopsMembershipHandler = xoops_getHandler('membership');
    //    $xoopsMembershipTable   = $xoopsMembershipHandler->table;

    /** @var \XoopsMembershipHandler $xoopsMembershipHandler */
    $xoopsMembershipHandler = xoops_getHandler('membership');
    $xoopsMembershipTable   = $xoopsMembershipHandler->table;
    /** @var \XoopsGroupPermHandler $xoopsGroupPermHandler */
    $xoopsGroupPermHandler = xoops_getHandler('groupperm');
    $xoopsGroupPermTable   = $xoopsGroupPermHandler->table;

    if (!(bool)$uid) {
        return $module_administrators;
    }
    $mid = $xoopsModule->getVar('mid');

    $sql = 'SELECT COUNT(l.groupid) AS count, l.uid FROM '
           . $xoopsMembershipTable
           . ' AS l'
           . ' LEFT JOIN '
           . $xoopsGroupPermTable
           . ' AS p ON p.gperm_groupid=l.groupid'
           . ' WHERE l.uid IN ('
           . implode(', ', array_map('\intval', $uid))
           . ')'
           . "    AND p.gperm_modid = '1' AND p.gperm_name = 'module_admin' AND p.gperm_itemid = '"
           . (int)$mid
           . "'"
           . ' GROUP BY l.uid';

    $result = $GLOBALS['xoopsDB']->query($sql);
    if ($GLOBALS['xoopsDB']->isResultSet($result)) {
        while (false !== ($myrow = $GLOBALS['xoopsDB']->fetchArray($result))) {
            if (!empty($myrow['count'])) {
                $module_administrators[] = $myrow['uid'];
            }
        }
    }

    return $module_administrators;
}

/* use hardcoded DB query to save queries */
/**
 * @param array $uid
 * @param int $mid
 * @return array
 */
function newbbIsForumModerators(array $uid = [], $mid = 0)
{
    $forum_moderators = [];

    if (empty($uid)) {
        return $forum_moderators;
    }

    $sql    = 'SELECT forum_moderator FROM ' . $GLOBALS['xoopsDB']->prefix('newbb_forums');
    $result = $GLOBALS['xoopsDB']->query($sql);
    if ($GLOBALS['xoopsDB']->isResultSet($result)) {
        while (false !== ($myrow = $GLOBALS['xoopsDB']->fetchArray($result))) {
            if (empty($myrow['forum_moderator'])) {
                continue;
            }
            $forum_moderators = array_merge($forum_moderators, (array)unserialize($myrow['forum_moderator']));
        }
    }

    return array_unique($forum_moderators??[]);
}
//ENDIF;
