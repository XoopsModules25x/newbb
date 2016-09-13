<?php
//
// ------------------------------------------------------------------------ //
// This program is free software; you can redistribute it and/or modify     //
// it under the terms of the GNU General Public License as published by     //
// the Free Software Foundation; either version 2 of the License, or        //
// (at your option) any later version.                                      //
//                                                                          //
// You may not change or alter any portion of this comment or credits       //
// of supporting developers from this source code or any supporting         //
// source code which is considered copyrighted (c) material of the          //
// original comment or credit authors.                                      //
//                                                                          //
// This program is distributed in the hope that it will be useful,          //
// but WITHOUT ANY WARRANTY; without even the implied warranty of           //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            //
// GNU General Public License for more details.                             //
//                                                                          //
// You should have received a copy of the GNU General Public License        //
// along with this program; if not, write to the Free Software              //
// Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA //
// ------------------------------------------------------------------------ //
// Author: phppp (D.J., infomax@gmail.com)                                  //
//  URL: http://xoops.org                                                    //
// Project: Article Project                                                 //
// ------------------------------------------------------------------------ //

// defined('XOOPS_ROOT_PATH') || exit('XOOPS root path not defined');
require_once $GLOBALS['xoops']->path('class/tree.php');

if (!class_exists('NewbbObjectTree')) {
    /**
     * Class NewbbObjectTree
     */
    class NewbbObjectTree extends XoopsObjectTree
    {
        /**
         * @param      $objectArr
         * @param null $rootId
         */
        public function __construct(&$objectArr, $rootId = null)
        {
            parent::__construct($objectArr, 'forum_id', 'parent_forum', $rootId);
        }

        /**
         * Make options for a select box from
         *
         * @param int    $key         ID of the object to display as the root of select options
         * @param string $ret         (reference to a string when called from outside) Result from previous recursions
         * @param string $prefix_orig String to indent items at deeper levels
         * @param string $prefix_curr String to indent the current item
         * @param null   $tags
         * @internal  param string $fieldName Name of the member variable from the
         *                            node objects that should be used as the title for the options.
         * @internal  param string $selected Value to display as selected
         * @access    private
         */
        protected function _makeTreeItems($key, &$ret, $prefix_orig, $prefix_curr = '', $tags = null)
        {
            if ($key > 0) {
                if (count($tags) > 0) {
                    foreach ($tags as $tag) {
                        $ret[$key][$tag] = $this->_tree[$key]['obj']->getVar($tag);
                    }
                } else {
                    $ret[$key]['forum_name'] = $this->_tree[$key]['obj']->getVar('forum_name');
                }
                $ret[$key]['prefix'] = $prefix_curr;
                $prefix_curr .= $prefix_orig;
            }
            if (isset($this->_tree[$key]['child']) && !empty($this->_tree[$key]['child'])) {
                foreach ($this->_tree[$key]['child'] as $childkey) {
                    $this->_makeTreeItems($childkey, $ret, $prefix_orig, $prefix_curr, $tags);
                }
            }
        }

        /**
         * Make a select box with options from the tree
         *
         * @param  string  $prefix         String to indent deeper levels
         * @param  integer $key            ID of the object to display as the root of select options
         * @param  null    $tags
         * @return string  HTML select box
         * @internal param string $name Name of the select box
         * @internal param string $fieldName Name of the member variable from the
         *                                 node objects that should be used as the title for the options.
         * @internal param string $selected Value to display as selected
         * @internal param bool $addEmptyOption Set TRUE to add an empty option with value "0" at the top of the hierarchy
         */
        public function &makeTree($prefix = '-', $key = 0, $tags = null)
        {
            $ret = array();
            $this->_makeTreeItems($key, $ret, $prefix, '', $tags);

            return $ret;
        }

        /**
         * Make a select box with options from the tree
         *
         * @param  string  $name           Name of the select box
         * @param  string  $fieldName
         * @param  string  $prefix         String to indent deeper levels
         * @param  string  $selected       Value to display as selected
         * @param  bool    $addEmptyOption
         * @param  integer $key            ID of the object to display as the root of select options
         * @param  string  $extra
         * @return string  HTML select box
         * @internal param bool $EmptyOption
         * @internal param string $fieldName Name of the member variable from the
         *                                 node objects that should be used as the title for the options.
         * @internal param bool $addEmptyOption Set TRUE to add an empty option with value "0" at the top of the hierarchy
         */
        public function makeSelBox(
            $name,
            $fieldName,
            $prefix = '-',
            $selected = '',
            $addEmptyOption = false,
            $key = 0,
            $extra = ''
        ) //makeSelBox($name, $prefix = '-', $selected = '', $EmptyOption = false, $key = 0)
        {
            $ret = '<select name=' . $name . '>';
            if (!empty($addEmptyOption)) {
                $ret .= '<option value="0">' . (is_string($EmptyOption) ? $EmptyOption : '') . '</option>';
            }
            $this->_makeSelBoxOptions('forum_name', $selected, $key, $ret, $prefix);
            $ret .= '</select>';

            return $ret;
        }

        /**
         * Make a tree for the array of a given category
         *
         * @param  string  $key   top key of the tree
         * @param  array   $ret   the tree
         * @param  integer $depth level of subcategories
         * @return array
         * @internal param array $tags fields to be used
         */
        public function getAllChild_object($key, &$ret, $depth = 0)
        {
            if (--$depth == 0) {
                return;
            }

            if (isset($this->_tree[$key]['child'])) {
                foreach ($this->_tree[$key]['child'] as $childkey) {
                    if (isset($this->_tree[$childkey]['obj'])) {
                        $ret['child'][$childkey] = $this->_tree[$childkey]['obj'];
                    }
                    $this->getAllChild_object($childkey, $ret['child'][$childkey], $depth);
                }
            }
        }

        /**
         * Make a tree for the array
         *
         * @param  int|string $key   top key of the tree
         * @param  integer    $depth level of subcategories
         * @return array
         * @internal param array $tags fields to be used
         */
        public function &makeObjectTree($key = 0, $depth = 0)
        {
            $ret = array();
            if ($depth > 0) {
                ++$depth;
            }
            $this->getAllChild_object($key, $ret, $depth);

            return $ret;
        }

        /**
         * Make a tree for the array of a given category
         *
         * @param  string  $key   top key of the tree
         * @param  array   $ret   the tree
         * @param  array   $tags  fields to be used
         * @param  integer $depth level of subcategories
         * @return array
         **/
        public function getAllChild_array($key, &$ret, array $tags = array(), $depth = 0)
        {
            if (--$depth == 0) {
                return;
            }

            if (isset($this->_tree[$key]['child'])) {
                foreach ($this->_tree[$key]['child'] as $childkey) {
                    if (isset($this->_tree[$childkey]['obj'])) {
                        if (count($tags) > 0) {
                            foreach ($tags as $tag) {
                                $ret['child'][$childkey][$tag] = $this->_tree[$childkey]['obj']->getVar($tag);
                            }
                        } else {
                            $ret['child'][$childkey]['forum_name'] = $this->_tree[$childkey]['obj']->getVar('forum_name');
                        }
                    }

                    $this->getAllChild_array($childkey, $ret['child'][$childkey], $tags, $depth);
                }
            }
        }

        /**
         * Make a tree for the array
         *
         * @param  int|string $key   top key of the tree
         * @param  array      $tags  fields to be used
         * @param  integer    $depth level of subcategories
         * @return array
         */
        public function &makeArrayTree($key = 0, $tags = null, $depth = 0)
        {
            $ret = array();
            if ($depth > 0) {
                ++$depth;
            }
            $this->getAllChild_array($key, $ret, $tags, $depth);

            return $ret;
        }

        /**#@+
         * get all parent forums
         *
         * @param  string $key     ID of the child object
         * @param  array  $ret     (empty when called from outside) Result from previous recursions
         * @param  int    $uplevel (empty when called from outside) level of recursion
         * @return array  Array of parent nodes.
         */
        public function &_getParentForums($key, array $ret = array(), $uplevel = 0)
        {
            if (isset($this->_tree[$key]['parent']) && isset($this->_tree[$this->_tree[$key]['parent']]['obj'])) {
                $ret[$uplevel] = $this->_tree[$this->_tree[$key]['parent']]['obj'];
                if ($this->_tree[$key]['parent'] !== $key) {
                    //$parents = $this->getParentForums($this->_tree[$key]['parent'], $ret, $uplevel+1);
                    $parents = $this->getParentForums($this->_tree[$key]['parent']);
                    foreach (array_keys($parents) as $newkey) {
                        $ret[$newkey] = $parents[$newkey];
                    }
                }
            }

            return $ret;
        }

        /**
         * @param        $key
         * @param  bool  $reverse
         * @return array
         */
        public function &getParentForums($key, $reverse = true)
        {
            $ret  = array();
            $pids = array();
            if (isset($this->_tree[$key]['parent']) && isset($this->_tree[$this->_tree[$key]['parent']]['obj'])) {
                $pids[]  = $this->_tree[$this->_tree[$key]['parent']]['obj']->getVar($this->_myId);
                $parents = $this->_getParentForums($this->_tree[$key]['parent'], $ret);
                foreach (array_keys($parents) as $newkey) {
                    if (!is_object($newkey)) {
                        continue;
                    }
                    $ret[] = $parents[$newkey]->getVar($this->_myId);
                }
            }
            if ($reverse) {
                $pids = array_reverse($ret) + $pids;
            } else {
                $pids += $ret;
            }

            return $pids;
        }
        /**#@-*/
    }
}
