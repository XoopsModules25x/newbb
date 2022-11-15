<?php declare(strict_types=1);

namespace XoopsModules\Newbb;

/*
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 */

/**
 * @copyright    XOOPS Project (https://xoops.org)/
 * @license      GNU GPL 2.0 or later (https://www.gnu.org/licenses/gpl-2.0.html)
 * @author       phppp (D.J., infomax@gmail.com)
 * @author       XOOPS Development Team
 */
require_once $GLOBALS['xoops']->path('class/tree.php');

if (!\class_exists('ObjectTree')) {
    /**
     * Class ObjectTree
     */
    class ObjectTree extends \XoopsObjectTree
    {
        /**
         * @param      $objectArr
         * @param null $rootId
         */
        public function __construct($objectArr, $rootId = null)
        {
            parent::__construct($objectArr, 'forum_id', 'parent_forum', $rootId);
        }

        /**
         * Make options for a select box from
         *
         * @param int        $key           ID of the object to display as the root of select options
         * @param array      $ret           (reference to a string when called from outside) Result from previous recursions
         * @param string     $prefix_orig   String to indent items at deeper levels
         * @param string     $prefix_curr   String to indent the current item
         * @param null|array $tags
         * @internal  param string $fieldName Name of the member variable from the
         *                                  node objects that should be used as the title for the options.
         * @internal  param string $selected Value to display as selected
         * @access    private
         */
        protected function makeTreeItems($key, &$ret, $prefix_orig, $prefix_curr = '', $tags = null): void
        {
            if ($key > 0) {
                if (\count($tags) > 0) {
                    foreach ($tags as $tag) {
                        $ret[$key][$tag] = $this->tree[$key]['obj']->getVar($tag);
                    }
                } else {
                    $ret[$key]['forum_name'] = $this->tree[$key]['obj']->getVar('forum_name');
                }
                $ret[$key]['prefix'] = $prefix_curr;
                $prefix_curr         .= $prefix_orig;
            }
            if (isset($this->tree[$key]['child']) && !empty($this->tree[$key]['child'])) {
                foreach ($this->tree[$key]['child'] as $childkey) {
                    $this->makeTreeItems($childkey, $ret, $prefix_orig, $prefix_curr, $tags);
                }
            }
        }

        /**
         * Make a select box with options from the tree
         *
         * @param string $prefix           String to indent deeper levels
         * @param int    $key              ID of the object to display as the root of select options
         * @param null   $tags
         * @return array  HTML select box
         * @internal param string $name Name of the select box
         * @internal param string $fieldName Name of the member variable from the
         *                                 node objects that should be used as the title for the options.
         * @internal param string $selected Value to display as selected
         * @internal param bool $addEmptyOption Set TRUE to add an empty option with value "0" at the top of the hierarchy
         */
        public function &makeTree($prefix = '-', $key = 0, $tags = null)
        {
            $ret = [];
            $this->makeTreeItems($key, $ret, $prefix, '', $tags);

            return $ret;
        }

        /**
         * Make options for a select box from
         *
         * @param string $fieldName     Name of the member variable from the
         *                              node objects that should be used as the title for the options.
         * @param string $selected      Value to display as selected
         * @param int    $key           ID of the object to display as the root of select options
         * @param string $ret           (reference to a string when called from outside) Result from previous recursions
         * @param string $prefix_orig   String to indent items at deeper levels
         * @param string $prefix_curr   String to indent the current item
         * @access    private
         */
        public function _makeSelBoxOptions($fieldName, $selected, $key, &$ret, $prefix_orig, $prefix_curr = ''): void
        {
            if ($key > 0) {
                $value = $this->tree[$key]['obj']->getVar($this->myId);
                $ret   .= '<option value=\'' . $value . '\'';
                if ($value == $selected) {
                    $ret .= ' selected="selected"';
                }
                $ret         .= '>' . $prefix_curr . $this->tree[$key]['obj']->getVar($fieldName) . '</option>';
                $prefix_curr .= $prefix_orig;
            }
            if (isset($this->tree[$key]['child']) && !empty($this->tree[$key]['child'])) {
                foreach ($this->tree[$key]['child'] as $childkey) {
                    $this->_makeSelBoxOptions($fieldName, $selected, $childkey, $ret, $prefix_orig, $prefix_curr);
                }
            }
        }

        /**
         * Make a select box with options from the tree
         *
         * @param string $name             Name of the select box
         * @param string $fieldName        Name of the member variable from the
         *                                 node objects that should be used as the title for the options.
         * @param string $prefix           String to indent deeper levels
         * @param string $selected         Value to display as selected
         * @param bool   $addEmptyOption   Set TRUE to add an empty option with value "0" at the top of the hierarchy
         * @param int    $key              ID of the object to display as the root of select options
         * @param string $extra
         * @return string  HTML select box
         *
         * @deprecated since 2.5.9, please use makeSelectElement()
         */
        public function makeSelBox(
            $name,
            $fieldName,
            $prefix = '-',
            $selected = '',
            $addEmptyOption = false,
            $key = 0,
            $extra = ''
        ) { //makeSelBox($name, $prefix = '-', $selected = '', $EmptyOption = false, $key = 0)
            $trace = \debug_backtrace(\DEBUG_BACKTRACE_IGNORE_ARGS, 1);
            \trigger_error("makeSelBox() is deprecated since 2.5.9, please use makeSelectElement(), accessed from {$trace[0]['file']} line {$trace[0]['line']},");

            $ret = '<select name=' . $name . '>';
            if (!empty($addEmptyOption)) {
                $ret .= '<option value="0">' . (\is_string($addEmptyOption) ? $addEmptyOption : '') . '</option>';
            }
            $this->_makeSelBoxOptions('forum_name', $selected, $key, $ret, $prefix);
            $ret .= '</select>';

            return $ret;
        }

        /**
         * Make a tree for the array of a given category
         *
         * @param string $key   top key of the tree
         * @param array  $ret   the tree
         * @param int    $depth level of subcategories
         * @internal param array $tags fields to be used
         */
        public function getAllChildObject($key, &$ret, $depth = 0): void
        {
            if (0 == --$depth) {
                return;
            }

            if (isset($this->tree[$key]['child'])) {
                foreach ($this->tree[$key]['child'] as $childkey) {
                    if (isset($this->tree[$childkey]['obj'])) {
                        $ret['child'][$childkey] = $this->tree[$childkey]['obj'];
                    }
                    $this->getAllChildObject($childkey, $ret['child'][$childkey], $depth);
                }
            }
        }

        /**
         * Make a tree for the array
         *
         * @param int|string $key   top key of the tree
         * @param int        $depth level of subcategories
         * @return array
         * @internal param array $tags fields to be used
         */
        public function &makeObjectTree($key = 0, $depth = 0)
        {
            $ret = [];
            if ($depth > 0) {
                ++$depth;
            }
            $this->getAllChildObject($key, $ret, $depth);

            return $ret;
        }

        /**
         * Make a tree for the array of a given category
         *
         * @param string $key   top key of the tree
         * @param array  $ret   the tree
         * @param array  $tags  fields to be used
         * @param int    $depth level of subcategories
         */
        public function getAllChildArray($key, &$ret, array $tags = [], $depth = 0): void
        {
            if (0 == --$depth) {
                return;
            }

            if (isset($this->tree[$key]['child'])) {
                foreach ($this->tree[$key]['child'] as $childkey) {
                    if (isset($this->tree[$childkey]['obj'])) {
                        if (\count($tags) > 0) {
                            foreach ($tags as $tag) {
                                $ret['child'][$childkey][$tag] = $this->tree[$childkey]['obj']->getVar($tag);
                            }
                        } else {
                            $ret['child'][$childkey]['forum_name'] = $this->tree[$childkey]['obj']->getVar('forum_name');
                        }
                    }

                    $this->getAllChildArray($childkey, $ret['child'][$childkey], $tags, $depth);
                }
            }
        }

        /**
         * Make a tree for the array
         *
         * @param int|string $key   top key of the tree
         * @param array      $tags  fields to be used
         * @param int        $depth level of subcategories
         * @return array
         */
        public function &makeArrayTree($key = 0, $tags = null, $depth = 0)
        {
            $ret = [];
            if ($depth > 0) {
                ++$depth;
            }
            $this->getAllChildArray($key, $ret, $tags, $depth);

            return $ret;
        }

        /**#@+
         * get all parent forums
         *
         * @param string $key     ID of the child object
         * @param array  $ret     (empty when called from outside) Result from previous recursions
         * @param int    $uplevel (empty when called from outside) level of recursion
         * @return array  Array of parent nodes.
         */
        public function &myGetParentForums($key, array $ret = [], $uplevel = 0)
        {
            if (isset($this->tree[$key]['parent'], $this->tree[$this->tree[$key]['parent']]['obj'])) {
                $ret[$uplevel] = $this->tree[$this->tree[$key]['parent']]['obj'];
                if ($this->tree[$key]['parent'] !== $key) {
                    //$parents = $this->getParentForums($this->tree[$key]['parent'], $ret, $uplevel+1);
                    $parents = $this->getParentForums($this->tree[$key]['parent']);
                    foreach (\array_keys($parents) as $newkey) {
                        $ret[$newkey] = $parents[$newkey];
                    }
                }
            }

            return $ret;
        }

        /**
         * @param        $key
         * @param bool   $reverse
         * @return array
         */
        public function &getParentForums($key, $reverse = true)
        {
            $ret  = [];
            $pids = [];
            if (isset($this->tree[$key]['parent'], $this->tree[$this->tree[$key]['parent']]['obj'])) {
                $pids[]  = $this->tree[$this->tree[$key]['parent']]['obj']->getVar($this->myId);
                $parents = $this->myGetParentForums($this->tree[$key]['parent'], $ret);
                foreach (\array_keys($parents) as $newkey) {
                    if (!\is_object($newkey)) {
                        continue;
                    }
                    $ret[] = $parents[$newkey]->getVar($this->myId);
                }
            }
            if ($reverse) {
                $pids = \array_reverse($ret) + $pids;
            } else {
                $pids += $ret;
            }

            return $pids;
        }
        /**#@-*/
    }
}
