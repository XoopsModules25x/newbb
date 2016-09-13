<?php
/**
 * NewBB 4.3x, the forum module for XOOPS project
 *
 * @copyright      XOOPS Project (http://xoops.org)
 * @license        http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author         Taiwen Jiang (phppp or D.J.) <phppp@users.sourceforge.net>
 * @since          4.00
 * @package        module::newbb
 */

// defined('XOOPS_ROOT_PATH') || exit('XOOPS root path not defined');

//defined("NEWBB_HANDLER_PERMISSION") || include __DIR__.'/permission.php';
//define("NEWBB_HANDLER_PERMISSION_FORUM", 1);

if (defined('FORUM_PERM_ITEMS') && class_exists('NewbbForumPermissionHandler')) {
    exit('access denied');
}
// irmtfan add pdf and print permissions.
define('FORUM_PERM_ITEMS', 'access,view,post,reply,edit,delete,addpoll,vote,attach,noapprove,type,html,signature,pdf,print');

/**
 * Class NewbbPermissionForumHandler
 */
class NewbbPermissionForumHandler extends NewbbPermissionHandler
{
    /**
     * @param XoopsDatabase $db
     */
    public function __construct(XoopsDatabase $db)
    {
        //        $this->NewbbPermissionHandler($db);
        parent::__construct($db);
    }

    /**
     * @param  bool $fullname
     * @return array
     */
    public function getValidPerms($fullname = false)
    {
        static $validPerms = array();
        if (isset($validPerms[(int)$fullname])) {
            return $validPerms[(int)$fullname];
        }
        $items = array_filter(array_map('trim', explode(',', FORUM_PERM_ITEMS)));
        if (!empty($fullname)) {
            foreach (array_keys($items) as $key) {
                $items[$key] = 'forum_' . $items[$key];
            }
        }
        $validPerms[(int)$fullname] = $items;

        return $items;
    }

    /**
     * @param        $mid
     * @param  int   $id
     * @return array
     */
    public function getValidItems($mid, $id = 0)
    {
        static $suspension = array();
        $full_items = array();
        if (empty($mid)) {
            return $full_items;
        }

        mod_loadFunctions('user', 'newbb');
        $uid = is_object($GLOBALS['xoopsUser']) ? $GLOBALS['xoopsUser']->getVar('uid') : 0;
        $ip  = newbb_getIP(true);
        if (!empty($GLOBALS['xoopsModuleConfig']['enable_usermoderate']) && !isset($suspension[$uid][$id])
            && !newbb_isAdmin($id)
        ) {
            $moderateHandler = xoops_getModuleHandler('moderate', 'newbb');
            if ($moderateHandler->verifyUser($uid, '', $id)) {
                $suspension[$uid][$ip][$id] = 1;
            } else {
                $suspension[$uid][$ip][$id] = 0;
            }
        }

        $items = $this->getValidPerms();
        foreach ($items as $item) {
            /* skip access for suspended users */
            //if ( !empty($suspension[$uid][$ip][$id]) && in_array($item, array("post", "reply", "edit", "delete", "addpoll", "vote", "attach", "noapprove", "type")) ) continue;
            if (!empty($suspension[$uid][$ip][$id])) {
                continue;
            }
            $full_items[] = "'forum_{$item}'";
        }

        return $full_items;
    }

    /*
    * Returns permissions for a certain type
    *
    * @param int $id id of the item (forum, topic or possibly post) to get permissions for
    *
    * @return array
    */
    /**
     * @param  int $id
     * @return bool
     */
    public function getPermissions($id = 0)
    {
        if (is_object($GLOBALS['xoopsModule']) && $GLOBALS['xoopsModule']->getVar('dirname') === 'newbb') {
            $modid = $GLOBALS['xoopsModule']->getVar('mid');
        } else {
            $moduleHandler = xoops_getHandler('module');
            $xoopsNewBB    = $moduleHandler->getByDirname('newbb');
            $modid         = $xoopsNewBB->getVar('mid');
            unset($xoopsNewBB);
        }

        // Get user's groups
        $groups = is_object($GLOBALS['xoopsUser']) ? $GLOBALS['xoopsUser']->getGroups() : array(XOOPS_GROUP_ANONYMOUS);
        // Create string of groupid's separated by commas, inserted in a set of brackets
        if (count($groups) < 1) {
            return false;
        }
        // Create criteria for getting only the permissions regarding this module and this user's groups
        $criteria = new CriteriaCompo(new Criteria('gperm_modid', $modid));
        $criteria->add(new Criteria('gperm_groupid', '(' . implode(',', $groups) . ')', 'IN'));
        if ($id) {
            if (is_array($id)) {
                $criteria->add(new Criteria('gperm_itemid', '(' . implode(',', $id) . ')', 'IN'));
            } else {
                $criteria->add(new Criteria('gperm_itemid', (int)$id));
            }
        }
        $gperm_names = implode(', ', $this->getValidItems($modid, $id));

        // Add criteria for gpermnames
        $criteria->add(new Criteria('gperm_name', '(' . $gperm_names . ')', 'IN'));
        // Get all permission objects in this module and for this user's groups
        $userpermissions = $this->getObjects($criteria, true);

        // Set the granted permissions to 1
        foreach ($userpermissions as $gperm_id => $gperm) {
            $permissions[$gperm->getVar('gperm_itemid')][$gperm->getVar('gperm_name')] = 1;
        }
        $userpermissions = null;
        unset($userpermissions);

        // Return the permission array
        return $permissions;
    }

    /**
     * @param  int  $forum
     * @param  bool $topic_locked
     * @param  bool $isadmin
     * @return array
     */
    public function &permission_table($forum = 0, $topic_locked = false, $isadmin = false)
    {
        $perm = array();

        $forum_id = $forum;
        if (is_object($forum)) {
            $forum_id = $forum->getVar('forum_id');
        }

        $permission_set = $this->getPermissions($forum_id);

        $perm_items = $this->getValidPerms();
        foreach ($perm_items as $item) {
            if ($item === 'access') {
                continue;
            }
            if ($isadmin
                || (isset($permission_set[$forum_id]['forum_' . $item])
                    && (!$topic_locked
                        || $item === 'view'))
            ) {
                $perm[] = constant('_MD_CAN_' . strtoupper($item));
            } else {
                $perm[] = constant('_MD_CANNOT_' . strtoupper($item));
            }
        }

        return $perm;
    }

    /**
     * @param $forum_id
     * @return bool
     */
    public function deleteByForum($forum_id)
    {
        $forum_id = (int)$forum_id;
        if (empty($forum_id)) {
            return false;
        }
        $gpermHandler = xoops_getHandler('groupperm');
        $criteria     = new CriteriaCompo(new Criteria('gperm_modid', $GLOBALS['xoopsModule']->getVar('mid')));
        $items        = $this->getValidPerms(true);
        $criteria->add(new Criteria('gperm_name', "('" . implode("', '", $items) . "')", 'IN'));
        $criteria->add(new Criteria('gperm_itemid', $forum_id));

        return $gpermHandler->deleteAll($criteria);
    }

    /**
     * @param       $forum
     * @param  int  $mid
     * @return bool
     */
    public function applyTemplate($forum, $mid = 0)
    {
        if (!$perm_template = $this->getTemplate()) {
            return false;
        }

        if (empty($mid)) {
            if (is_object($GLOBALS['xoopsModule']) && $GLOBALS['xoopsModule']->getVar('dirname') === 'newbb') {
                $mid = $GLOBALS['xoopsModule']->getVar('mid');
            } else {
                $moduleHandler = xoops_getHandler('module');
                $newbb         = $moduleHandler->getByDirname('newbb');
                $mid           = $newbb->getVar('mid');
                unset($newbb);
            }
        }

        $memberHandler = xoops_getHandler('member');
        $glist         = $memberHandler->getGroupList();
        $perms         = $this->getValidPerms(true);
        foreach (array_keys($glist) as $group) {
            foreach ($perms as $perm) {
                if (!empty($perm_template[$group][$perm])) {
                    $this->validateRight($perm, $forum, $group, $mid);
                } else {
                    $this->deleteRight($perm, $forum, $group, $mid);
                }
            }
        }

        return true;
    }

    /**
     * @return mixed|null
     */
    public function getTemplate()
    {
        $perms = mod_loadFile('perm_template', 'newbb');

        return $perms;
    }

    /**
     * @param $perms
     * @return bool
     */
    public function setTemplate($perms)
    {
        return mod_createFile($perms, 'perm_template', 'newbb');
    }
}
