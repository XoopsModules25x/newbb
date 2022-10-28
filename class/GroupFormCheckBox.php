<?php declare(strict_types=1);

namespace XoopsModules\Newbb;

/**
 * TODO: synchronize cascade permissions for multi-level
 */

/**
 * Add category navigation to forum casscade structure
 * <ol>Special points:
 *    <li> Use negative values for category IDs to avoid conflict between category and forum
 *    <li> Disabled checkbox for categories to avoid unnecessary permission items for categories in forum permission table
 * </ol>
 *
 * Note: this is a __patchy__ solution. We should have a more extensible and flexible group permission management: not only for data architecture but also for management interface
 */

/**
 * Class GroupFormCheckBox
 * @package XoopsModules\Newbb
 */
class GroupFormCheckBox extends \XoopsGroupFormCheckBox
{
    /**
     * @param      $caption
     * @param      $name
     * @param      $groupId
     * @param null $values
     */
    public function __construct($caption, $name, $groupId, $values = null)
    {
        parent::__construct($caption, $name, $groupId, $values);
    }

    /**
     * Renders checkbox options for an item tree
     *
     * @param string $tree
     * @param array  $option
     * @param string $prefix
     * @param array  $parentIds
     */
    public function _renderOptionTree(&$tree, $option, $prefix, $parentIds = []): void
    {
        if ($option['id'] > 0) {
            $tree .= $prefix . '<input type="checkbox" name="' . $this->getName() . '[groups][' . $this->_groupId . '][' . $option['id'] . ']" id="' . $this->getName() . '[groups][' . $this->_groupId . '][' . $option['id'] . ']" onclick="';
            foreach ($parentIds as $pid) {
                if ($pid <= 0) {
                    continue;
                }
                $parent_ele = $this->getName() . '[groups][' . $this->_groupId . '][' . $pid . ']';
                $tree       .= "var ele = xoopsGetElementById('" . $parent_ele . "'); if (ele.checked !== true) {ele.checked = this.checked;}";
            }
            foreach ($option['allchild'] as $cid) {
                $child_ele = $this->getName() . '[groups][' . $this->_groupId . '][' . $cid . ']';
                $tree      .= "var ele = xoopsGetElementById('" . $child_ele . "'); if (this.checked !== true) {ele.checked = false;}";
            }
            $tree .= '" value="1"';
            if (\in_array($option['id'], $this->_value, true)) {
                $tree .= ' checked';
            }
            $tree .= ' >' . $option['name'] . '<input type="hidden" name="' . $this->getName() . '[parents][' . $option['id'] . ']" value="' . \implode(':', $parentIds) . '" ><input type="hidden" name="' . $this->getName() . '[itemname][' . $option['id'] . ']" value="' . \htmlspecialchars(
                    (string)$option['name'],
                    \ENT_QUOTES | \ENT_HTML5
                ) . "\" ><br>\n";
        } else {
            $tree .= $prefix . $option['name'] . '<input type="hidden" id="' . $this->getName() . '[groups][' . $this->_groupId . '][' . $option['id'] . "]\" ><br>\n";
        }
        if (isset($option['children'])) {
            foreach ($option['children'] as $child) {
                if ($option['id'] > 0) {
                    //                  array_push($parentIds, $option['id']);
                    $parentIds[] = $option['id'];
                }
                $this->_renderOptionTree($tree, $this->_optionTree[$child], $prefix . '&nbsp;-', $parentIds);
            }
        }
    }
}
