<?php
/**
 * NewBB 4.3x, the forum module for XOOPS project
 *
 * @copyright      XOOPS Project (http://xoops.org)
 * @license        http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author         Taiwen Jiang (phppp or D.J.) <phppp@users.sourceforge.net>
 * @since          4.00
 * @version        $Id $
 * @package        module::newbb
 */

// defined('XOOPS_ROOT_PATH') || exit('XOOPS root path not defined');

//defined("NEWBB_HANDLER_PERMISSION") || include __DIR__.'/permission.php';
//define("NEWBB_HANDLER_PERMISSION_CATEGORY", 1);

class NewbbPermissionCategoryHandler extends NewbbPermissionHandler
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
     * @param        $mid
     * @param  int   $id
     * @return array
     */
    public function getValidItems($mid, $id = 0)
    {
        $full_items = array();
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
        $cat_id = (int)($cat_id);
        if (empty($cat_id)) {
            return false;
        }
        $gpermHandler =& xoops_gethandler('groupperm');
        $criteria     = new CriteriaCompo(new Criteria('gperm_modid', $GLOBALS["xoopsModule"]->getVar('mid')));
        $criteria->add(new Criteria('gperm_name', 'category_access'));
        $criteria->add(new Criteria('gperm_itemid', $cat_id));

        return $gpermHandler->deleteAll($criteria);
    }

    /**
     * @param        $category
     * @param  array $groups
     * @return bool
     */
    public function setCategoryPermission($category, array $groups = array())
    {
        if (is_object($GLOBALS["xoopsModule"]) && $GLOBALS["xoopsModule"]->getVar("dirname") === "newbb") {
            $mid = $GLOBALS["xoopsModule"]->getVar("mid");
        } else {
            $module_handler =& xoops_gethandler('module');
            $newbb          =& $module_handler->getByDirname('newbb');
            $mid            = $newbb->getVar("mid");
        }
        if (empty($groups)) {
            $memberHandler =& xoops_gethandler('member');
            $glist         = $memberHandler->getGroupList();
            $groups        = array_keys($glist);
        }
        $ids     = $this->getGroupIds("category_access", $category, $mid);
        $ids_add = array_diff($groups, $ids);
        $ids_rmv = array_diff($ids, $groups);
        foreach ($ids_add as $group) {
            $this->addRight("category_access", $category, $group, $mid);
        }
        foreach ($ids_rmv as $group) {
            $this->deleteRight("category_access", $category, $group, $mid);
        }

        return true;
    }
}
