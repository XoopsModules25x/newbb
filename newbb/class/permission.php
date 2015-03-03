<?php
/**
 * CBB 4.0, or newbb, the forum module for XOOPS project
 *
 * @copyright	The XOOPS Project http://xoops.sf.net
 * @license		http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author		Taiwen Jiang (phppp or D.J.) <phppp@users.sourceforge.net>
 * @since		4.00
 * @version		$Id $
 * @package		module::newbb
 */

// defined("XOOPS_ROOT_PATH") || exit("XOOPS root path not defined");

defined("NEWBB_FUNCTIONS_INI") || include XOOPS_ROOT_PATH.'/modules/newbb/include/functions.ini.php';
define("NEWBB_HANDLER_PERMISSION", 1);
load_functions("cache");

// Initializing XoopsGroupPermHandler if not loaded yet
if (!class_exists("XoopsGroupPermHandler")) {
    require_once XOOPS_ROOT_PATH.'/kernel/groupperm.php';
}

class NewbbPermissionHandler extends XoopsGroupPermHandler
{
    var $_handler;

    function __construct($db)
    {
        $this->db = $db;
        parent::__construct($db);
    }

    function NewbbPermissionHandler(&$db)
    {
        $this->__construct($db);
    }

    function &_loadHandler($name) {
        if ( !isset($this->_handler[$name]) ) {
            require_once __DIR__."/permission.{$name}.php";
            $className = "NewbbPermission".ucfirst($name)."Handler";
            $this->_handler[$name] = new $className($this->db);
        }

        return $this->_handler[$name];
    }

    function getValidForumPerms($fullname = false)
    {
        $handler =& $this->_loadHandler("forum");

        return $handler->getValidPerms($fullname);
    }

    function &permission_table($forum = 0, $topic_locked = false, $isadmin = false)
    {
        $handler =& $this->_loadHandler("forum");
        $perm = $handler->permission_table($forum, $topic_locked, $isadmin);

        return $perm;
    }

    function deleteByForum($forum_id)
    {
        mod_clearCacheFile("permission_forum", "newbb");
        $handler =& $this->_loadHandler("forum");

        return $handler->deleteByForum($forum_id);
    }

    function deleteByCategory($cat_id)
    {
        mod_clearCacheFile("permission_category", "newbb");
        $handler =& $this->_loadHandler("category");

        return $handler->deleteByCategory($cat_id);
    }

    function setCategoryPermission($category, $groups = array())
    {
        mod_clearCacheFile("permission_category", "newbb");
        $handler =& $this->_loadHandler("category");

        return $handler->setCategoryPermission($category, $groups);
    }

    function getPermission($type, $gperm_name = "access", $id = 0)
    {
        global $xoopsUser, $xoopsModule;

        if ($GLOBALS["xoopsUserIsAdmin"] && $xoopsModule->getVar("dirname") == "newbb") {
            return true;
        }

        $groups = is_object($xoopsUser) ? $xoopsUser->getGroups() : array(XOOPS_GROUP_ANONYMOUS);
        if ( !$groups ) return false;
        if ( !$allowed_groups = $this->getGroups("{$type}_{$gperm_name}", $id) ) return false;

        if ( count(array_intersect($allowed_groups, $groups)) > 0) return true;

        return false;
    }

    function &getCategories($perm_name = "access")
    {
        $ret = $this->getAllowedItems("category", "category_{$perm_name}");

        return $ret;
    }

    function getForums($perm_name = "access")
    {
        $ret = $this->getAllowedItems("forum", "forum_{$perm_name}");

        return $ret;
    }

    function getAllowedItems($type, $perm_name)
    {
        global $xoopsUser;

        $ret = array();

        $groups = is_object($xoopsUser) ? $xoopsUser->getGroups() : array(XOOPS_GROUP_ANONYMOUS);
        if (count($groups) < 1)    return $ret;

        if (!$_cachedPerms = $this->loadPermData($perm_name, $type)) {
            return $ret;
        }

        $allowed_items = array();
        foreach ($_cachedPerms as $id => $allowed_groups) {
            if ($id == 0 || empty($allowed_groups)) continue;

            if (array_intersect($groups, $allowed_groups)) {
                $allowed_items[$id] = 1;
            }
        }
        unset($_cachedPerms);
        $ret = array_keys($allowed_items);

        return $ret;
    }

    function &getGroups($gperm_name, $id = 0)
    {
        $_cachedPerms = $this->loadPermData($gperm_name);
        $groups = empty($_cachedPerms[$id]) ? array() : array_unique($_cachedPerms[$id]);
        unset($_cachedPerms);

        return $groups;
    }

    function createPermData($perm_name = "forum_all")
    {
        global $xoopsModule;

        if (is_object($xoopsModule) && $xoopsModule->getVar("dirname") == "newbb") {
            $modid = $xoopsModule->getVar("mid") ;
        } else {
            $module_handler =& xoops_gethandler("module");
            $module =& $module_handler->getByDirname("newbb");
            $modid = $module->getVar("mid") ;
            unset($module);
        }

        if ( in_array($perm_name, array("forum_all", "category_all")) ) {
            $member_handler =& xoops_gethandler('member');
            $groups = array_keys( $member_handler->getGroupList() );

            $type = ($perm_name == "category_all") ? "category" : "forum";
            $object_handler =& xoops_getmodulehandler($type, 'newbb');
            $object_ids = $object_handler->getIds();
            foreach ($object_ids as $item_id) {
                $perms[$perm_name][$item_id] = $groups;
            }

        } else {
            $gperm_handler =& xoops_gethandler("groupperm");
            $criteria = new CriteriaCompo(new Criteria('gperm_modid', $modid));
            if (!empty($perm_name) && $perm_name != "forum_all" && $perm_name != "category_all") {
                $criteria->add(new Criteria('gperm_name', $perm_name));
            }
            $permissions =& $this->getObjects($criteria);

            $perms = array();
            foreach ($permissions as $gperm) {
                $item_id = $gperm->getVar('gperm_itemid');
                $group_id = intval( $gperm->getVar('gperm_groupid') );
                $perms[$gperm->getVar('gperm_name')][$item_id][] = $group_id;
            }
        }
        load_functions("cache");
        foreach (array_keys($perms) as $perm) {
            mod_createCacheFile($perms[$perm], "permission_{$perm}", "newbb");
        }

        $ret = !empty($perm_name) ? @$perms[$perm_name] : $perms;

        return $ret;
    }

    function &loadPermData($perm_name = "forum_access")
    {
        load_functions("cache");
        if (!$perms = mod_loadCacheFile("permission_{$perm_name}", "newbb")) {

            $perms = $this->createPermData($perm_name);
        }

        return $perms;
    }

    function validateRight($perm, $itemid, $groupid, $mid = null)
    {
        if (empty($mid)) {
            if (is_object($GLOBALS["xoopsModule"]) && $GLOBALS["xoopsModule"]->getVar("dirname") == "newbb") {
                $mid = $GLOBALS["xoopsModule"]->getVar("mid");
            } else {
                $module_handler =& xoops_gethandler("module");
                $mod = $module_handler->getByDirname("newbb");
                $mid = $mod->getVar("mid");
                unset($mod);
            }
        }
        if ($this->_checkRight($perm, $itemid, $groupid, $mid)) return true;
        load_functions("cache");
        mod_clearCacheFile("permission", "newbb");
        $this->addRight($perm, $itemid, $groupid, $mid);

        return true;
    }

    /**
     * Check permission (directly)
     *
     * @param string    $gperm_name    Name of permission
     * @param int       $gperm_itemid  ID of an item
     * @param int/array $gperm_groupid A group ID or an array of group IDs
     * @param int       $gperm_modid   ID of a module
     *
     * @return bool TRUE if permission is enabled
     */
    function _checkRight($gperm_name, $gperm_itemid, $gperm_groupid, $gperm_modid = 1)
    {
        $criteria = new CriteriaCompo(new Criteria("gperm_modid", $gperm_modid));
        $criteria->add(new Criteria("gperm_name", $gperm_name));
        $gperm_itemid = intval($gperm_itemid);
        if ($gperm_itemid > 0) {
            $criteria->add(new Criteria("gperm_itemid", $gperm_itemid));
        }
        if (is_array($gperm_groupid)) {
            $criteria2 = new CriteriaCompo();
            foreach ($gperm_groupid as $gid) {
                $criteria2->add(new Criteria("gperm_groupid", $gid), "OR");
            }
            $criteria->add($criteria2);
        } else {
            $criteria->add(new Criteria("gperm_groupid", $gperm_groupid));
        }
        if ($this->getCount($criteria) > 0) {
            return true;
        }

        return false;
    }

    function deleteRight($perm, $itemid, $groupid, $mid = null)
    {
        mod_clearCacheFile("permission", "newbb");
        if (empty($mid)) {
            if (is_object($GLOBALS["xoopsModule"]) && $GLOBALS["xoopsModule"]->getVar("dirname") == "newbb") {
                $mid = $GLOBALS["xoopsModule"]->getVar("mid");
            } else {
                $module_handler =& xoops_gethandler("module");
                $mod = $module_handler->getByDirname("newbb");
                $mid = $mod->getVar("mid");
                unset($mod);
            }
        }
        if (is_callable(array(&$this->XoopsGroupPermHandler, "deleteRight"))) {
            return parent::deleteRight($perm, $itemid, $groupid, $mid);
        } else {
            $criteria = new CriteriaCompo(new Criteria("gperm_name", $perm));
            $criteria->add(new Criteria("gperm_groupid", $groupid));
            $criteria->add(new Criteria("gperm_itemid", $itemid));
            $criteria->add(new Criteria("gperm_modid", $mid));
            $perms_obj = $this->getObjects($criteria);
            if (!empty($perms_obj)) {
                foreach ($perms_obj as $perm_obj) {
                    $this->delete($perm_obj);
                }
            }
            unset($criteria, $perms_obj);
        }

        return true;
    }

    function applyTemplate($forum, $mid = 0)
    {
        mod_clearCacheFile("permission_forum", "newbb");
        $handler = $this->_loadHandler("forum");

        return $handler->applyTemplate($forum, $mid);
    }

    function getTemplate()
    {
        $handler = $this->_loadHandler("forum");
        $template = $handler->getTemplate();

        return $template;
    }

    function setTemplate($perms)
    {
        $handler = $this->_loadHandler("forum");

        return $handler->setTemplate($perms);
    }
}
