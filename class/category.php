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
// defined('XOOPS_ROOT_PATH') || exit('Restricted access.');

class NewbbCategory extends XoopsObject
{
    /**
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->initVar('cat_id', XOBJ_DTYPE_INT);
        $this->initVar('cat_title', XOBJ_DTYPE_TXTBOX);
        $this->initVar('cat_image', XOBJ_DTYPE_SOURCE, 'blank.gif');
        $this->initVar('cat_description', XOBJ_DTYPE_TXTAREA);
        $this->initVar('cat_order', XOBJ_DTYPE_INT, 99);
        $this->initVar('cat_url', XOBJ_DTYPE_URL);
    }
}

/**
 * Class NewbbCategoryHandler
 */
class NewbbCategoryHandler extends XoopsPersistableObjectHandler
{
    /**
     * @param null|XoopsDatabase $db
     */
    public function __construct(XoopsDatabase $db)
    {
        parent::__construct($db, 'newbb_categories', 'NewbbCategory', 'cat_id', 'cat_title');
    }

    /**
     * @param  string $perm
     * @return mixed
     */
    public function getIdsByPermission($perm = 'access')
    {
        /** @var \NewbbPermissionHandler $permHandler */
        $permHandler = xoops_getModuleHandler('permission', 'newbb');
        return $permHandler->getCategories($perm);
    }

    /**
     * @param  string $permission
     * @param  null   $tags
     * @param  bool   $asObject
     * @return array
     */
    public function &getByPermission($permission = 'access', $tags = null, $asObject = true)
    {
        $categories = [];
        if (!$valid_ids = $this->getIdsByPermission($permission)) {
            return $categories;
        }
        $criteria = new Criteria('cat_id', '(' . implode(', ', $valid_ids) . ')', 'IN');
        $criteria->setSort('cat_order');
        $categories = $this->getAll($criteria, $tags, $asObject);

        return $categories;
    }

    /**
     * @param  XoopsObject $category
     * @param  bool        $force
     * @return mixed
     */
    public function insert(XoopsObject $category, $force = true)
    {
        $className = 'NewbbCategory';
        if (!($category instanceof $className)) {
            return false;
        }
        parent::insert($category, $force);
        if ($category->isNew()) {
            $this->applyPermissionTemplate($category);
        }

        return $category->getVar('cat_id');
    }

    /**
     * @param  XoopsObject $category
     * @param  bool        $force
     * @return bool|mixed
     * @internal param Category $category
     */
    public function delete(XoopsObject $category, $force = false)//delete(Category $category)
    {
        $className = 'NewbbCategory';
        if (!($category instanceof $className)) {
            return false;
        }
        /** @var \NewbbForumHandler $forumHandler */
        $forumHandler = xoops_getModuleHandler('forum', 'newbb');
        $forumHandler->deleteAll(new Criteria('cat_id', $category->getVar('cat_id')), true, true);
        if ($result = parent::delete($category)) {
            // Delete group permissions
            return $this->deletePermission($category);
        } else {
            $category->setErrors('delete category error');

            return false;
        }
    }

    /**
     * Check permission for a category
     *
     * @param  NewbbCategory|int $category object or id
     * @param  string            $perm     permission name
     *
     * @return bool
     */
    public function getPermission($category, $perm = 'access')
    {
        if ($GLOBALS['xoopsUserIsAdmin'] && 'newbb' === $GLOBALS['xoopsModule']->getVar('dirname')) {
            return true;
        }

        $cat_id = is_object($category) ? $category->getVar('cat_id') : (int)$category;
        /** @var NewbbPermissionHandler $permHandler */
        $permHandler = xoops_getModuleHandler('permission', 'newbb');
        return $permHandler->getPermission('category', $perm, $cat_id);
    }

    /**
     * @param NewbbCategory $category
     * @return mixed
     */
    public function deletePermission(NewbbCategory $category)
    {
        /** @var NewbbPermissionHandler $permHandler */
        $permHandler = xoops_getModuleHandler('permission', 'newbb');
        return $permHandler->deleteByCategory($category->getVar('cat_id'));
    }

    /**
     * @param NewbbCategory $category
     * @return mixed
     */
    public function applyPermissionTemplate(NewbbCategory $category)
    {
        /** @var NewbbPermissionHandler $permHandler */
        $permHandler = xoops_getModuleHandler('permission', 'newbb');
        return $permHandler->setCategoryPermission($category->getVar('cat_id'));
    }

    /**
     * @param  mixed $object
     * @return bool
     */
    public function synchronization($object = null)
    {
        return true;
    }
}
