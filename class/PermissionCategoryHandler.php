<?php declare(strict_types=1);

namespace XoopsModules\Newbb;

/**
 * NewBB 5.0x,  the forum module for XOOPS project
 *
 * @copyright      XOOPS Project (https://xoops.org)
 * @license        GNU GPL 2.0 or later (https://www.gnu.org/licenses/gpl-2.0.html)
 * @author         Taiwen Jiang (phppp or D.J.) <phppp@users.sourceforge.net>
 * @since          4.00
 */

use XoopsModules\Newbb;

//defined("NEWBB_HANDLER_PERMISSION") || require_once __DIR__  .'/permission.php';
//define("NEWBB_HANDLER_PERMISSION_CATEGORY", 1);

/**
 * Class PermissionCategoryHandler
 */
class PermissionCategoryHandler extends Newbb\PermissionHandler
{
    /**
     * @param \XoopsDatabase|null $db
     */
    public function __construct(\XoopsDatabase $db = null)
    {
        //        $this->PermissionHandler($db);
        parent::__construct($db);
    }

    /**
     * @param        $mid
     * @param int    $id
     * @return array
     */
    public function getValidItems($mid, $id = 0)
    {
        $full_items = [];
        if (empty($mid)) {
            return $full_items;
        }

        $full_items[] = "'category_access'";

        return $full_items;
    }

    /**
     * @param $cat_id
     * @return bool
     */
    public function deleteByCategory($cat_id)
    {
        $cat_id = (int)$cat_id;
        if (empty($cat_id)) {
            return false;
        }
        /** @var \XoopsGroupPermHandler $grouppermHandler */
        $grouppermHandler = \xoops_getHandler('groupperm');
        $criteria         = new \CriteriaCompo(new \Criteria('gperm_modid', $GLOBALS['xoopsModule']->getVar('mid')));
        $criteria->add(new \Criteria('gperm_name', 'category_access'));
        $criteria->add(new \Criteria('gperm_itemid', $cat_id));

        return $grouppermHandler->deleteAll($criteria);
    }

    /**
     * @param        $category
     * @param array  $groups
     * @return bool
     */
    public function setCategoryPermission($category, array $groups = [])
    {
        if (\is_object($GLOBALS['xoopsModule']) && 'newbb' === $GLOBALS['xoopsModule']->getVar('dirname')) {
            $mid = $GLOBALS['xoopsModule']->getVar('mid');
        } else {
            /** @var \XoopsModuleHandler $moduleHandler */
            $moduleHandler = \xoops_getHandler('module');
            $newbb         = $moduleHandler->getByDirname('newbb');
            $mid           = $newbb->getVar('mid');
        }
        if (empty($groups)) {
            /** @var \XoopsMemberHandler $memberHandler */
            $memberHandler = \xoops_getHandler('member');
            $glist         = $memberHandler->getGroupList();
            $groups        = \array_keys($glist);
        }
        $ids     = $this->getGroupIds('category_access', $category, $mid);
        $ids_add = \array_diff($groups, $ids);
        $ids_rmv = \array_diff($ids, $groups);
        foreach ($ids_add as $group) {
            $this->addRight('category_access', $category, $group, $mid);
        }
        foreach ($ids_rmv as $group) {
            $this->deleteRight('category_access', $category, $group, $mid);
        }

        return true;
    }
}
