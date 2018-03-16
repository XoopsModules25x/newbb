<?php namespace XoopsModules\Newbb;

/**
 * NewBB 5.0x,  the forum module for XOOPS project
 *
 * @copyright      XOOPS Project (https://xoops.org)
 * @license        GNU GPL 2 or later (http://www.gnu.org/licenses/gpl-2.0.html)
 * @author         Taiwen Jiang (phppp or D.J.) <phppp@users.sourceforge.net>
 * @since          4.00
 * @package        module::newbb
 */

use XoopsModules\Newbb;

// defined('XOOPS_ROOT_PATH') || die('Restricted access');

defined('NEWBB_FUNCTIONS_INI') || include $GLOBALS['xoops']->path('modules/newbb/include/functions.ini.php');
define('NEWBB_HANDLER_PERMISSION', 1);

// Initializing XoopsGroupPermHandler if not loaded yet
if (!class_exists('XoopsGroupPermHandler')) {
    require_once $GLOBALS['xoops']->path('kernel/groupperm.php');
}

/**
 * Class PermissionHandler
 */
class PermissionHandler extends \XoopsGroupPermHandler
{
    public $_handler;
    /** @var \Xmf\Module\Helper\Cache */
    protected $cacheHelper;

    /**
     * @param $db
     */
    public function __construct(\XoopsDatabase $db)
    {
        $this->cacheHelper = new \Xmf\Module\Helper\Cache('newbb');

        $this->db = $db;
        parent::__construct($db);
    }

    /**
     * @param $name
     * @return mixed
     */
    public function loadHandler($name)
    {
        if (!isset($this->_handler[$name])) {
//            require_once __DIR__ . "/permission.{$name}.php";
            $className             = '\\XoopsModules\\Newbb\\Permission' . ucfirst($name) . 'Handler';
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
        $handler = $this->loadHandler('forum');

        return $handler->getValidPerms($fullname);
    }

    /**
     * @param  int  $forum
     * @param  bool $topic_locked
     * @param  bool $isAdmin
     * @return mixed
     */
    public function getPermissionTable($forum = 0, $topic_locked = false, $isAdmin = false)
    {
        $handler = $this->loadHandler('forum');
        $perm    = $handler->getPermissionTable($forum, $topic_locked, $isAdmin);

        return $perm;
    }

    /**
     * @param $forum_id
     * @return mixed
     */
    public function deleteByForum($forum_id)
    {
        $this->cacheHelper->delete('permission_forum');
        $handler = $this->loadHandler('forum');

        return $handler->deleteByForum($forum_id);
    }

    /**
     * @param $cat_id
     * @return mixed
     */
    public function deleteByCategory($cat_id)
    {
        $this->cacheHelper->delete('permission_category');
        $handler = $this->loadHandler('category');

        return $handler->deleteByCategory($cat_id);
    }

    /**
     * @param        $category
     * @param  array $groups
     * @return mixed
     */
    public function setCategoryPermission($category, array $groups = [])
    {
        $this->cacheHelper->delete('permission_category');
        $handler = $this->loadHandler('category');

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
        if ($GLOBALS['xoopsUserIsAdmin'] && 'newbb' === $xoopsModule->getVar('dirname')) {
            $ret = true;
        }

        $groups = is_object($GLOBALS['xoopsUser']) ? $GLOBALS['xoopsUser']->getGroups() : [XOOPS_GROUP_ANONYMOUS];
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
        $ret = [];

        $groups = is_object($GLOBALS['xoopsUser']) ? $GLOBALS['xoopsUser']->getGroups() : [XOOPS_GROUP_ANONYMOUS];
        if (count($groups) < 1) {
            return $ret;
        }

        if (!$_cachedPerms = $this->loadPermData($perm_name, $type)) {
            return $ret;
        }

        $allowed_items = [];
        foreach ($_cachedPerms as $id => $allowed_groups) {
            if (0 == $id || empty($allowed_groups)) {
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
        $groups       = empty($_cachedPerms[$id]) ? [] : array_unique($_cachedPerms[$id]);
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
        /** @var \XoopsModuleHandler $moduleHandler */
        $perms = [];

        if (is_object($xoopsModule) && 'newbb' === $xoopsModule->getVar('dirname')) {
            $modid = $xoopsModule->getVar('mid');
        } else {
            $moduleHandler = xoops_getHandler('module');
            $module        = $moduleHandler->getByDirname('newbb');
            $modid         = $module->getVar('mid');
            unset($module);
        }

        if (in_array($perm_name, ['forum_all', 'category_all'], true)) {
            /** @var \XoopsMemberHandler $memberHandler */
            $memberHandler = xoops_getHandler('member');
            $groups        = array_keys($memberHandler->getGroupList());

            $type          = ('category_all' === $perm_name) ? 'category' : 'forum';
            $objectHandler = Newbb\Helper::getInstance()->getHandler($type);
            $object_ids    = $objectHandler->getIds();
            foreach ($object_ids as $item_id) {
                $perms[$perm_name][$item_id] = $groups;
            }
        } else {
            $gpermHandler = xoops_getHandler('groupperm');
            $criteria     = new \CriteriaCompo(new \Criteria('gperm_modid', $modid));
            if (!empty($perm_name) && 'forum_all' !== $perm_name && 'category_all' !== $perm_name) {
                $criteria->add(new \Criteria('gperm_name', $perm_name));
            }
            $permissions = $this->getObjects($criteria);

            foreach ($permissions as $gperm) {
                $item_id                                         = $gperm->getVar('gperm_itemid');
                $group_id                                        = (int)$gperm->getVar('gperm_groupid');
                $perms[$gperm->getVar('gperm_name')][$item_id][] = $group_id;
            }
        }
        if (count($perms) > 0) {
            foreach (array_keys($perms) as $perm) {
                $this->cacheHelper->write("permission_{$perm}", $perms[$perm]);
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
        if (!$perms = $this->cacheHelper->read("permission_{$perm_name}")) {
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
            if (is_object($GLOBALS['xoopsModule']) && 'newbb' === $GLOBALS['xoopsModule']->getVar('dirname')) {
                $mid = $GLOBALS['xoopsModule']->getVar('mid');
            } else {
                /** @var \XoopsModuleHandler $moduleHandler */
                $moduleHandler = xoops_getHandler('module');
                $mod           = $moduleHandler->getByDirname('newbb');
                $mid           = $mod->getVar('mid');
                unset($mod);
            }
        }
        if ($this->myCheckRight($perm, $itemid, $groupid, $mid)) {
            return true;
        }
        $this->cacheHelper->delete('permission');
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
    public function myCheckRight($gperm_name, $gperm_itemid, $gperm_groupid, $gperm_modid = 1)
    {
        $ret      = false;
        $criteria = new \CriteriaCompo(new \Criteria('gperm_modid', $gperm_modid));
        $criteria->add(new \Criteria('gperm_name', $gperm_name));
        $gperm_itemid = (int)$gperm_itemid;
        if ($gperm_itemid > 0) {
            $criteria->add(new \Criteria('gperm_itemid', $gperm_itemid));
        }
        if (is_array($gperm_groupid)) {
            $criteria2 = new \CriteriaCompo();
            foreach ($gperm_groupid as $gid) {
                $criteria2->add(new \Criteria('gperm_groupid', $gid), 'OR');
            }
            $criteria->add($criteria2);
        } else {
            $criteria->add(new \Criteria('gperm_groupid', $gperm_groupid));
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
        $this->cacheHelper->delete('permission');
        if (null === $mid) {
            if (is_object($GLOBALS['xoopsModule']) && 'newbb' === $GLOBALS['xoopsModule']->getVar('dirname')) {
                $mid = $GLOBALS['xoopsModule']->getVar('mid');
            } else {
                /** @var \XoopsModuleHandler $moduleHandler */
                $moduleHandler = xoops_getHandler('module');
                $mod           = $moduleHandler->getByDirname('newbb');
                $mid           = $mod->getVar('mid');
                unset($mod);
            }
        }
        if (is_callable([&$this->XoopsGroupPermHandler, 'deleteRight'])) {
            return parent::deleteRight($perm, $itemid, $groupid, $mid);
        } else {
            $criteria = new \CriteriaCompo(new \Criteria('gperm_name', $perm));
            $criteria->add(new \Criteria('gperm_groupid', $groupid));
            $criteria->add(new \Criteria('gperm_itemid', $itemid));
            $criteria->add(new \Criteria('gperm_modid', $mid));
            $permsObject =& $this->getObjects($criteria);
            if (!empty($permsObject)) {
                foreach ($permsObject as $permObject) {
                    $this->delete($permObject);
                }
            }
            unset($criteria, $permsObject);
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
        $this->cacheHelper->delete('permission_forum');
        $handler = $this->loadHandler('forum');

        return $handler->applyTemplate($forum, $mid);
    }

    /**
     * @return mixed
     */
    public function getTemplate()
    {
        $handler  = $this->loadHandler('forum');
        $template = $handler->getTemplate();

        return $template;
    }

    /**
     * @param $perms
     * @return mixed
     */
    public function setTemplate($perms)
    {
        $handler = $this->loadHandler('forum');

        return $handler->setTemplate($perms);
    }
}
