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
 * @version         $Id: functions.user.php 12504 2014-04-26 01:01:06Z beckmi $
 */

// defined('XOOPS_ROOT_PATH') || exit('XOOPS root path not defined');

/**
 * Function to a list of user names associated with their user IDs
 *
 */
function &newbb_getUnameFromIds( $uid, $usereal = 0, $linked = false )
{
    xoops_load("xoopsuserutility");
    $ids = XoopsUserUtility::getUnameFromIds($uid, $usereal, $linked);

    return $ids;
}

function newbb_getUnameFromId( $uid, $usereal = 0, $linked = false)
{
    xoops_load("xoopsuserutility");

    return XoopsUserUtility::getUnameFromId($uid, $usereal, $linked);
}

// Adapted from PMA_getIp() [phpmyadmin project]
function newbb_getIP($asString = false)
{
    xoops_load("xoopsuserutility");

    return XoopsUserUtility::getIP($asString);
}

/**
 * Function to check if a user is an administrator of the module
 *
 * @return bool
 */
function newbb_isAdministrator( $user = -1, $mid = 0 )
{
    global $xoopsUser, $xoopsModule;

    if ( is_numeric($user) && $user == -1 ) $user =& $xoopsUser;
    if ( !is_object($user) && intval($user) < 1 ) return false;
    $uid = (is_object($user)) ? $user->getVar("uid") : intval($user);

    if (!$mid) {
        if (is_object($xoopsModule) && "newbb" == $xoopsModule->getVar("dirname", "n")) {
            $mid = $xoopsModule->getVar("mid", "n");
        } else {
            $modhandler =& xoops_gethandler("module");
            $newbb_module =& $modhandler->getByDirname("newbb");
            $mid = $newbb_module->getVar("mid", "n");
            unset($newbb_module);
        }
    }

    if ( is_object($xoopsModule) && $mid == $xoopsModule->getVar("mid", "n") && is_object($xoopsUser) && $uid == $xoopsUser->getVar("uid", "n") ) {
        return $GLOBALS["xoopsUserIsAdmin"];
    }

    $member_handler =& xoops_gethandler('member');
    $groups = $member_handler->getGroupsByUser($uid);

    $moduleperm_handler =& xoops_gethandler('groupperm');

    return $moduleperm_handler->checkRight('module_admin', $mid, $groups);
}

/**
 * Function to check if a user is a moderator of a forum
 *
 * @return bool
 */
function newbb_isModerator( &$forum, $user = -1 )
{
    global $xoopsUser;

    if (!is_object($forum)) {
        $forum_id = intval($forum);
        if ( $forum_id == 0 ) return false;
        $forum_handler =& xoops_getmodulehandler("forum", "newbb");
        $forum =& $forum_handler->get($forum_id);
    }

    if (is_numeric($user) && $user == -1) $user =& $xoopsUser;
    if (!is_object($user) && intval($user) < 1) return false;
    $uid = (is_object($user)) ? $user->getVar("uid", "n") : intval($user);

    return in_array($uid, $forum->getVar("forum_moderator"));
}

/**
 * Function to check if a user has moderation permission over a forum
 *
 * @return bool
 */
function newbb_isAdmin($forum = 0)
{
    global $xoopsUser, $xoopsModule;
    static $_cachedModerators;

    if (empty($forum)) return $GLOBALS["xoopsUserIsAdmin"];

    if (!is_object($xoopsUser)) return false;

    if ($GLOBALS["xoopsUserIsAdmin"] && $xoopsModule->getVar("dirname") == "newbb") {
        return true;
    }

    $cache_id = (is_object($forum)) ? $forum->getVar('forum_id', "n") : intval($forum);
    if (!isset($_cachedModerators[$cache_id])) {
        if (!is_object($forum)) {
            $forum_handler =& xoops_getmodulehandler('forum', 'newbb');
            $forum = $forum_handler->get(intval($forum));
        }
        $_cachedModerators[$cache_id] = $forum->getVar("forum_moderator");
    }

    return in_array($xoopsUser->getVar("uid"), $_cachedModerators[$cache_id]);
}

/* use hardcoded DB query to save queries */
function newbb_isModuleAdministrators($uid = array())
{
    global $xoopsDB, $xoopsModule;
    $module_administrators = array();

    if (empty($uid)) return $module_administrators;
    $mid = $xoopsModule->getVar("mid");

    $sql = "SELECT COUNT(l.groupid) AS count, l.uid FROM " . $xoopsDB->prefix('groups_users_link') . " AS l" .
            " LEFT JOIN " . $xoopsDB->prefix('group_permission') . " AS p ON p.gperm_groupid=l.groupid" .
            " WHERE l.uid IN (" . implode(", ", array_map("intval", $uid)) . ")" .
            "    AND p.gperm_modid = '1' AND p.gperm_name = 'module_admin' AND p.gperm_itemid = '" . intval($mid) . "'" .
            " GROUP BY l.uid";
    if ($result = $xoopsDB->query($sql)) {
        while ($myrow = $xoopsDB->fetchArray($result)) {
            if (!empty($myrow["count"])) {
                $module_administrators[] = $myrow["uid"];
            }
        }
    }

    return $module_administrators;
}

/* use hardcoded DB query to save queries */
function newbb_isForumModerators($uid = array(), $mid = 0)
{
    global $xoopsDB;
    $forum_moderators = array();

    if (empty($uid)) return $forum_moderators;

    $sql = "SELECT forum_moderator FROM " . $xoopsDB->prefix('bb_forums');
    if ($result = $xoopsDB->query($sql)) {
        while ($myrow = $xoopsDB->fetchArray($result)) {
            if (empty($myrow["forum_moderator"])) continue;
            $forum_moderators = array_merge($forum_moderators, unserialize($myrow["forum_moderator"]));
        }
    }

    return array_unique($forum_moderators);
}
//ENDIF;
