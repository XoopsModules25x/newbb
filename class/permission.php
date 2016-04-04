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

defined('NEWBB_FUNCTIONS_INI') || include $GLOBALS['xoops']->path('modules/newbb/include/functions.ini.php');
define('NEWBB_HANDLER_PERMISSION', 1);
load_functions('cache');

// Initializing XoopsGroupPermHandler if not loaded yet
if (!class_exists('XoopsGroupPermHandler')) {
    require_once $GLOBALS['xoops']->path('kernel/groupperm.php');
}

/**
 * Class NewbbPermissionHandler
 */
class NewbbPermissionHandler extends XoopsGroupPermHandler
{
    public $_handler;

    /**
     * @param $db
     */
    public function __construct($db)
    {
        $this->db = $db;
        parent::__construct($db);
    }

    /**
     * @param $name
     * @return mixed
     */
    public function _loadHandler($name)
    {
        if (!isset($this->_handler[$name])) {
            require_once __DIR__ . "/permission.{$name}.php";
            $className             = 'NewbbPermission' . ucfirst($name) . 'Handler';
            $this->_handler[$name] = new $className($this->db);
        }

        return $this->_handler[$name];
    }

    /**
     * @param  bool $fullname
     * @return mixed
     */
    public function getValidForumPerms($fullname = false)
    {
        $handler = $this->_loadHandler('forum');

        return $handler->getValidPerms($fullname);
    }

    /**
     * @param  int  $forum
     * @param  bool $topic_locked
     * @param  bool $isadmin
     * @return mixed
     */
    public function &permission_table($forum = 0, $topic_locked = false, $isadmin = false)
    {
        $handler = $this->_loadHandler('forum');
        $perm    = $handler->permission_table($forum, $topic_locked, $isadmin);

        return $perm;
    }

    /**
     * @param $forum_id
     * @return mixed
     */
    public function deleteByForum($forum_id)
    {
        mod_clearCacheFile('permission_forum', 'newbb');
        $handler = $this->_loadHandler('forum');

        return $handler->deleteByForum($forum_id);
    }

    /**
     * @param $cat_id
     * @return mixed
     */
    public function deleteByCategory($cat_id)
    {
        mod_clearCacheFile('permission_category', 'newbb');
        $handler = $this->_loadHandler('category');

        return $handler->deleteByCategory($cat_id);
    }

    /**
     * @param        $category
     * @param  array $groups
     * @return mixed
     */
    public function setCategoryPermission($category, array $groups = array())
    {
        mod_clearCacheFile('permission_category', 'newbb');
        $handler = $this->_loadHandler('category');

        return $handler->setCategoryPermission($category, $groups);
    }

    /**
     * @param         $type
     * @param  string $gperm_name
     * @param  int    $id
     * @return bool
     */
    public function getPermission($type, $gperm_name = 'access', $id = 0)
    {
        global $xoopsModule;
        $ret = false;
        if ($GLOBALS['xoopsUserIsAdmin'] && $xoopsModule->getVar('dirname') === 'newbb') {
            $ret = true;
        }

        $groups = is_object($GLOBALS['xoopsUser']) ? $GLOBALS['xoopsUser']->getGroups() : array(XOOPS_GROUP_ANONYMOUS);
        if (!$groups) {
            $ret = false;
        }
        if (!$allowed_groups = $this->getGroups("{$type}_{$gperm_name}", $id)) {
            $ret = false;
        }

        if (count(array_intersect($allowed_groups, $groups)) > 0) {
            $ret = true;
        }

        return $ret;
    }

    /**
     * @param  string $perm_name
     * @return array
     */
    public function &getCategories($perm_name = 'access')
    {
        $ret = $this->getAllowedItems('category', "category_{$perm_name}");

        return $ret;
    }

    /**
     * @param  string $perm_name
     * @return array
     */
    public function getForums($perm_name = 'access')
    {
        $ret = $this->getAllowedItems('forum', "forum_{$perm_name}");

        return $ret;
    }

    /**
     * @param $type
     * @param $perm_name
     * @return array
     */
    public function getAllowedItems($type, $perm_name)
    {
        $ret = array();

        $groups = is_object($GLOBALS['xoopsUser']) ? $GLOBALS['xoopsUser']->getGroups() : array(XOOPS_GROUP_ANONYMOUS);
        if (count($groups) < 1) {
            return $ret;
        }

        if (!$_cachedPerms = $this->loadPermData($perm_name, $type)) {
            return $ret;
        }

        $allowed_items = array();
        foreach ($_cachedPerms as $id => $allowed_groups) {
            if ($id == 0 || empty($allowed_groups)) {
                continue;
            }

            if (array_intersect($groups, $allowed_groups)) {
                $allowed_items[$id] = 1;
            }
        }
        unset($_cachedPerms);
        $ret = array_keys($allowed_items);

        return $ret;
    }

    /**
     * @param        $gperm_name
     * @param  int   $id
     * @return array
     */
    public function getGroups($gperm_name, $id = 0)
    {
        $_cachedPerms = $this->loadPermData($gperm_name);
        $groups       = empty($_cachedPerms[$id]) ? array() : array_unique($_cachedPerms[$id]);
        unset($_cachedPerms);

        return $groups;
    }

    /**
     * @param  string $perm_name
     * @return array
     */
    public function createPermData($perm_name = 'forum_all')
    {
        global $xoopsModule;
        $perms = array();

        if (is_object($xoopsModule) && $xoopsModule->getVar('dirname') === 'newbb') {
            $modid = $xoopsModule->getVar('mid');
        } else {
            $module_handler = xoops_getHandler('module');
            $module         = $module_handler->getByDirname('newbb');
            $modid          = $module->getVar('mid');
            unset($module);
        }

        if (in_array($perm_name, array('forum_all', 'category_all'), true)) {
            $memberHandler = xoops_getHandler('member');
            $groups        = array_keys($memberHandler->getGroupList());

            $type           = ($perm_name === 'category_all') ? 'category' : 'forum';
            $object_handler = xoops_getModuleHandler($type, 'newbb');
            $object_ids     = $object_handler->getIds();
            foreach ($object_ids as $item_id) {
                $perms[$perm_name][$item_id] = $groups;
            }
        } else {
            $gpermHandler = xoops_getHandler('groupperm');
            $criteria     = new CriteriaCompo(new Criteria('gperm_modid', $modid));
            if (!empty($perm_name) && $perm_name !== 'forum_all' && $perm_name !== 'category_all') {
                $criteria->add(new Criteria('gperm_name', $perm_name));
            }
            $permissions = $this->getObjects($criteria);

            foreach ($permissions as $gperm) {
                $item_id                                         = $gperm->getVar('gperm_itemid');
                $group_id                                        = (int)$gperm->getVar('gperm_groupid');
                $perms[$gperm->getVar('gperm_name')][$item_id][] = $group_id;
            }
        }
        load_functions('cache');
        if (count($perms) > 0) {
            foreach (array_keys($perms) as $perm) {
                mod_createCacheFile($perms[$perm], 'permission_{$perm}', 'newbb');
            }
        }
        $ret = !empty($perm_name) ? @$perms[$perm_name] : $perms;

        return $ret;
    }

    /**
     * @param  string $perm_name
     * @return array|mixed|null
     */
    public function &loadPermData($perm_name = 'forum_access')
    {
        load_functions('cache');
        if (!$perms = mod_loadCacheFile('permission_{$perm_name}', 'newbb')) {
            $perms = $this->createPermData($perm_name);
        }

        return $perms;
    }

    /**
     * @param       $perm
     * @param       $itemid
     * @param       $groupid
     * @param  null $mid
     * @return bool
     */
    public function validateRight($perm, $itemid, $groupid, $mid = null)
    {
        if (empty($mid)) {
            if (is_object($GLOBALS['xoopsModule']) && $GLOBALS['xoopsModule']->getVar('dirname') === 'newbb') {
                $mid = $GLOBALS['xoopsModule']->getVar('mid');
            } else {
                $module_handler = xoops_getHandler('module');
                $mod            = $module_handler->getByDirname('newbb');
                $mid            = $mod->getVar('mid');
                unset($mod);
            }
        }
        if ($this->_checkRight($perm, $itemid, $groupid, $mid)) {
            return true;
        }
        load_functions('cache');
        mod_clearCacheFile('permission', 'newbb');
        $this->addRight($perm, $itemid, $groupid, $mid);

        return true;
    }

    /**
     * Check permission (directly)
     *
     * @param string $gperm_name   Name of permission
     * @param int    $gperm_itemid ID of an item
     * @param        int           /array $gperm_groupid A group ID or an array of group IDs
     * @param int    $gperm_modid  ID of a module
     *
     * @return bool TRUE if permission is enabled
     */
    public function _checkRight($gperm_name, $gperm_itemid, $gperm_groupid, $gperm_modid = 1)
    {
        $ret      = false;
        $criteria = new CriteriaCompo(new Criteria('gperm_modid', $gperm_modid));
        $criteria->add(new Criteria('gperm_name', $gperm_name));
        $gperm_itemid = (int)$gperm_itemid;
        if ($gperm_itemid > 0) {
            $criteria->add(new Criteria('gperm_itemid', $gperm_itemid));
        }
        if (is_array($gperm_groupid)) {
            $criteria2 = new CriteriaCompo();
            foreach ($gperm_groupid as $gid) {
                $criteria2->add(new Criteria('gperm_groupid', $gid), 'OR');
            }
            $criteria->add($criteria2);
        } else {
            $criteria->add(new Criteria('gperm_groupid', $gperm_groupid));
        }
        if ($this->getCount($criteria) > 0) {
            $ret = true;
        }

        return $ret;
    }

    /**
     * @param       $perm
     * @param       $itemid
     * @param       $groupid
     * @param  null $mid
     * @return bool
     */
    public function deleteRight($perm, $itemid, $groupid, $mid = null)
    {
        mod_clearCacheFile('permission', 'newbb');
        if (null === $mid) {
            if (is_object($GLOBALS['xoopsModule']) && $GLOBALS['xoopsModule']->getVar('dirname') === 'newbb') {
                $mid = $GLOBALS['xoopsModule']->getVar('mid');
            } else {
                $module_handler = xoops_getHandler('module');
                $mod            = $module_handler->getByDirname('newbb');
                $mid            = $mod->getVar('mid');
                unset($mod);
            }
        }
        if (is_callable(array(&$this->XoopsGroupPermHandler, 'deleteRight'))) {
            return parent::deleteRight($perm, $itemid, $groupid, $mid);
        } else {
            $criteria = new CriteriaCompo(new Criteria('gperm_name', $perm));
            $criteria->add(new Criteria('gperm_groupid', $groupid));
            $criteria->add(new Criteria('gperm_itemid', $itemid));
            $criteria->add(new Criteria('gperm_modid', $mid));
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

    /**
     * @param        $forum
     * @param  int   $mid
     * @return mixed
     */
    public function applyTemplate($forum, $mid = 0)
    {
        mod_clearCacheFile('permission_forum', 'newbb');
        $handler = $this->_loadHandler('forum');

        return $handler->applyTemplate($forum, $mid);
    }

    /**
     * @return mixed
     */
    public function getTemplate()
    {
        $handler  = $this->_loadHandler('forum');
        $template = $handler->getTemplate();

        return $template;
    }

    /**
     * @param $perms
     * @return mixed
     */
    public function setTemplate($perms)
    {
        $handler = $this->_loadHandler('forum');

        return $handler->setTemplate($perms);
    }
}
