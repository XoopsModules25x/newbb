<?php
/**
 * CBB 4.0, or newbb, the forum module for XOOPS project
 *
 * @copyright    The XOOPS Project http://xoops.sf.net
 * @license        http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author        Taiwen Jiang (phppp or D.J.) <phppp@users.sourceforge.net>
 * @since        4.00
 * @version        $Id $
 * @package        module::newbb
 */

// defined("XOOPS_ROOT_PATH") || exit("XOOPS root path not defined");

defined("NEWBB_FUNCTIONS_INI") || include $GLOBALS['xoops']->path('modules/newbb/include/functions.ini.php');

/**
 * Class NewbbOnlineHandler
 */
class NewbbOnlineHandler
{
    public $db;
    public $forum_id;
    public $forum_object;
    public $topic_id;
    public $user_ids = array();

    /**
     * @param null $forum
     * @param null $forumtopic
     */
    public function init($forum = null, $forumtopic = null)
    {
        //$this->db = Database::getInstance();
        $this->db = $GLOBALS['xoopsDB'];
        if (is_object($forum)) {
            $this->forum_id     = $forum->getVar('forum_id');
            $this->forum_object = &$forum;
        } else {
            $this->forum_id     = intval($forum);
            $this->forum_object = $forum;
        }
        if (is_object($forumtopic)) {
            $this->topic_id = $forumtopic->getVar('topic_id');
            if (empty($this->forum_id)) {
                $this->forum_id = $forumtopic->getVar('forum_id');
            }
        } else {
            $this->topic_id = intval($forumtopic);
        }

        $this->update();
    }

    public function update()
    {
        global $xoopsModule;

        mt_srand((double)microtime() * 1000000);
        // set gc probabillity to 10% for now..
        if (mt_rand(1, 100) < 60) {
            $this->gc(150);
        }
        if (is_object($GLOBALS['xoopsUser'])) {
            $uid   = $GLOBALS['xoopsUser']->getVar('uid');
            $uname = $GLOBALS['xoopsUser']->getVar('uname');
            $name  = $GLOBALS['xoopsUser']->getVar('name');
        } else {
            $uid   = 0;
            $uname = '';
            $name  = '';
        }

        $xoops_online_handler =& xoops_gethandler('online');
        $xoopsupdate          = $xoops_online_handler->write($uid, $uname, time(), $xoopsModule->getVar('mid'), $_SERVER['REMOTE_ADDR']);
        if (!$xoopsupdate) {
            //xoops_error("newbb online upate error");
        }

        $uname = (empty($GLOBALS['xoopsModuleConfig']['show_realname']) || empty($name)) ? $uname : $name;
        $this->write($uid, $uname, time(), $this->forum_id, $_SERVER['REMOTE_ADDR'], $this->topic_id);
    }

    /**
     * @param $xoopsTpl
     */
    public function render(&$xoopsTpl)
    {
        mod_loadFunctions("render", "newbb");
        mod_loadFunctions("user", "newbb");
        if ($this->topic_id) {
            $criteria = new Criteria('online_topic', $this->topic_id);
        } elseif ($this->forum_id) {
            $criteria = new Criteria('online_forum', $this->forum_id);
        } else {
            $criteria = null;
        }
        $users     = $this->getAll($criteria);
        $num_total = count($users);

        $num_user     = 0;
        $users_id     = array();
        $users_online = array();
        for ($i = 0; $i < $num_total; ++$i) {
            if (empty($users[$i]['online_uid'])) {
                continue;
            }
            $users_id[]                             = $users[$i]['online_uid'];
            $users_online[$users[$i]['online_uid']] = array(
                "link"  => XOOPS_URL . "/userinfo.php?uid=" . $users[$i]['online_uid'],
                "uname" => $users[$i]['online_uname'],
            );
            ++$num_user;
        }
        $num_anonymous           = $num_total - $num_user;
        $online                  = array();
        $online['image']         = newbbDisplayImage('whosonline');
        $online['num_total']     = $num_total;
        $online['num_user']      = $num_user;
        $online['num_anonymous'] = $num_anonymous;
        $administrator_list      = newbb_isModuleAdministrators($users_id);
        $moderator_list          = array();
        if ($member_list = array_diff(array_keys($administrator_list), $users_id)) {
            if (is_object($this->forum_object)) {
                $moderator_list = $this->forum_object->getVar("forum_moderator");
            } else {
                $moderator_list = newbb_isForumModerators($member_list);
            }
        }
        foreach ($users_online as $uid => $user) {
            if (!empty($administrator_list[$uid])) {
                $user['level'] = 2;
            } elseif (!empty($moderator_list[$uid])) {
                $user['level'] = 1;
            } else {
                $user['level'] = 0;
            }
            $online["users"][] = $user;
        }

        $xoopsTpl->assign_by_ref("online", $online);
    }

    /**
     * Deprecated
     */
    public function &show_online()
    {
        mod_loadFunctions("render", "newbb");
        mod_loadFunctions("user", "newbb");
        if ($this->topic_id) {
            $criteria = new Criteria('online_topic', $this->topic_id);
        } elseif ($this->forum_id) {
            $criteria = new Criteria('online_forum', $this->forum_id);
        } else {
            $criteria = null;
        }
        $users     =& $this->getAll($criteria);
        $num_total = count($users);

        $num_user     = 0;
        $users_id     = array();
        $users_online = array();
        for ($i = 0; $i < $num_total; ++$i) {
            if (empty($users[$i]['online_uid'])) {
                continue;
            }
            $users_id[]                             = $users[$i]['online_uid'];
            $users_online[$users[$i]['online_uid']] = array(
                "link"  => XOOPS_URL . "/userinfo.php?uid=" . $users[$i]['online_uid'],
                "uname" => $users[$i]['online_uname'],
            );
            ++$num_user;
        }
        $num_anonymous           = $num_total - $num_user;
        $online                  = array();
        $online['image']         = newbbDisplayImage('whosonline');
        $online['statistik']     = newbbDisplayImage('statistik');
        $online['num_total']     = $num_total;
        $online['num_user']      = $num_user;
        $online['num_anonymous'] = $num_anonymous;
        $administrator_list      = newbb_isModuleAdministrators($users_id);
        $moderator_list          = array();
        if ($member_list = array_diff($users_id, array_keys($administrator_list))) {
            if (is_object($this->forum_object)) {
                $moderator_list = $this->forum_object->getVar("forum_moderator");
            } else {
                $moderator_list = newbb_isForumModerators($member_list);
            }
        }

        foreach ($users_online as $uid => $user) {
            if (in_array($uid, $administrator_list)) {
                $user['level'] = 2;
            } elseif (in_array($uid, $moderator_list)) {
                $user['level'] = 1;
            } else {
                $user['level'] = 0;
            }
            $online["users"][] = $user;
        }

        return $online;
    }

    /**
     * Write online information to the database
     *
     * @param  int $uid UID of the active user
     * @param  string $uname Username
     * @param $time
     * @param  string $forum_id Current forum_id
     * @param  string $ip User's IP adress
     * @param $topic_id
     * @return bool TRUE on success
     * @internal param string $timestamp
     */
    public function write($uid, $uname, $time, $forum_id, $ip, $topic_id)
    {
        global $xoopsModule;

        $uid = intval($uid);
        if ($uid > 0) {
            $sql = "SELECT COUNT(*) FROM " . $this->db->prefix('bb_online') . " WHERE online_uid=" . $uid;
        } else {
            $sql = "SELECT COUNT(*) FROM " . $this->db->prefix('bb_online') . " WHERE online_uid=" . $uid . " AND online_ip='" . $ip . "'";
        }
        list($count) = $this->db->fetchRow($this->db->queryF($sql));
        if ($count > 0) {
            $sql = "UPDATE " . $this->db->prefix('bb_online') . " SET online_updated= '" . $time . "', online_forum = '" . $forum_id . "', online_topic = '" . $topic_id . "' WHERE online_uid = " . $uid;
            if ($uid == 0) {
                $sql .= " AND online_ip='" . $ip . "'";
            }
        } else {
            $sql = sprintf("INSERT INTO %s (online_uid, online_uname, online_updated, online_ip, online_forum, online_topic) VALUES (%u, %s, %u, %s, %u, %u)", $this->db->prefix('bb_online'), $uid, $this->db->quoteString($uname), $time, $this->db->quoteString($ip), $forum_id, $topic_id);
        }
        if (!$this->db->queryF($sql)) {
            //xoops_error($this->db->error());
            return false;
        }

        $mysql_version = substr(trim(mysql_get_server_info()), 0, 3);
        /* for MySQL 4.1+ */
        if ($mysql_version >= "4.1") {
            $sql = "DELETE FROM " . $this->db->prefix('bb_online') .
                   " WHERE" .
                   " ( online_uid > 0 AND online_uid NOT IN ( SELECT online_uid FROM " . $this->db->prefix('online') . " WHERE online_module =" . $xoopsModule->getVar('mid') . " ) )" .
                   " OR ( online_uid = 0 AND online_ip NOT IN ( SELECT online_ip FROM " . $this->db->prefix('online') . " WHERE online_module =" . $xoopsModule->getVar('mid') . " AND online_uid = 0 ) )";

            if ($result = $this->db->queryF($sql)) {
                return true;
            } else {
                //xoops_error($this->db->error());
                return false;
            }
        } else {
            $sql    = "DELETE " . $this->db->prefix('bb_online') . " FROM " . $this->db->prefix('bb_online') .
                      " LEFT JOIN " . $this->db->prefix('online') . " AS aa " .
                      " ON " . $this->db->prefix('bb_online') . ".online_uid = aa.online_uid WHERE " . $this->db->prefix('bb_online') . ".online_uid > 0 AND aa.online_uid IS NULL";
            $result = $this->db->queryF($sql);
            $sql    = "DELETE " . $this->db->prefix('bb_online') . " FROM " . $this->db->prefix('bb_online') .
                      " LEFT JOIN " . $this->db->prefix('online') . " AS aa " .
                      " ON " . $this->db->prefix('bb_online') . ".online_ip = aa.online_ip WHERE " . $this->db->prefix('bb_online') . ".online_uid = 0 AND aa.online_ip IS NULL";
            $result = $this->db->queryF($sql);

            return true;
        }
    }

    /**
     * Garbage Collection
     *
     * Delete all online information that has not been updated for a certain time
     *
     * @param int $expire Expiration time in seconds
     */
    public function gc($expire)
    {
        global $xoopsModule;
        $sql = "DELETE FROM " . $this->db->prefix('bb_online') . " WHERE online_updated < " . (time() - intval($expire));
        $this->db->queryF($sql);

        $xoops_online_handler =& xoops_gethandler('online');
        $xoops_online_handler->gc($expire);
    }

    /**
     * Get an array of online information
     *
     * @param  object $criteria {@link CriteriaElement}
     * @return array  Array of associative arrays of online information
     */
    public function &getAll($criteria = null)
    {
        $ret   = array();
        $limit = $start = 0;
        $sql   = 'SELECT * FROM ' . $this->db->prefix('bb_online');
        if (is_object($criteria) && is_subclass_of($criteria, 'criteriaelement')) {
            $sql .= ' ' . $criteria->renderWhere();
            $limit = $criteria->getLimit();
            $start = $criteria->getStart();
        }
        $result = $this->db->query($sql, $limit, $start);
        if (!$result) {
            return $ret;
        }
        while ($myrow = $this->db->fetchArray($result)) {
            $ret[] = $myrow;
            if ($myrow["online_uid"] > 0) {
                $this->user_ids[] = $myrow["online_uid"];
            }
            unset($myrow);
        }
        $this->user_ids = array_unique($this->user_ids);

        return $ret;
    }

    /**
     * @param $uids
     * @return array
     */
    public function checkStatus($uids)
    {
        $online_users = array();
        $ret          = array();
        if (!empty($this->user_ids)) {
            $online_users =& $this->user_ids;
        } else {
            $sql = 'SELECT online_uid FROM ' . $this->db->prefix('bb_online');
            if (!empty($uids)) {
                $sql .= ' WHERE online_uid IN (' . implode(", ", array_map("intval", $uids)) . ')';
            }

            $result = $this->db->query($sql);
            if (!$result) {
                return $ret;
            }
            while (list($uid) = $this->db->fetchRow($result)) {
                $online_users[] = $uid;
            }
        }
        foreach ($uids as $uid) {
            if (in_array($uid, $online_users)) {
                $ret[$uid] = 1;
            }
        }

        return $ret;
    }

    /**
     * Count the number of online users
     *
     * @param object $criteria {@link CriteriaElement}
     * @return bool
     */
    public function getCount($criteria = null)
    {
        $sql = 'SELECT COUNT(*) FROM ' . $this->db->prefix('bb_online');
        if (is_object($criteria) && is_subclass_of($criteria, 'criteriaelement')) {
            $sql .= ' ' . $criteria->renderWhere();
        }
        if (!$result = $this->db->query($sql)) {
            return false;
        }
        list($ret) = $this->db->fetchRow($result);

        return $ret;
    }
}
