<?php namespace XoopsModules\Newbb;

//
// ------------------------------------------------------------------------ //
// XOOPS - PHP Content Management System                      //
// Copyright (c) 2000-2016 XOOPS.org                           //
// <https://xoops.org/>                             //
// ------------------------------------------------------------------------ //
// This program is free software; you can redistribute it and/or modify     //
// it under the terms of the GNU General Public License as published by     //
// the Free Software Foundation; either version 2 of the License, or        //
// (at your option) any later version.                                      //
// //
// You may not change or alter any portion of this comment or credits       //
// of supporting developers from this source code or any supporting         //
// source code which is considered copyrighted (c) material of the          //
// original comment or credit authors.                                      //
// //
// This program is distributed in the hope that it will be useful,          //
// but WITHOUT ANY WARRANTY; without even the implied warranty of           //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            //
// GNU General Public License for more details.                             //
// //
// You should have received a copy of the GNU General Public License        //
// along with this program; if not, write to the Free Software              //
// Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA //
// ------------------------------------------------------------------------ //
// Author: phppp (D.J., infomax@gmail.com)                                  //
//  URL: https://xoops.org                                                    //
// Project: Article Project                                                 //
// ------------------------------------------------------------------------ //

// defined('XOOPS_ROOT_PATH') || die('Restricted access');
require_once $GLOBALS['xoops']->path('class/xoopstree.php');

/**
 * Class Newbbtree
 */
class Tree extends \XoopsTree
{
    public $prefix    = '&nbsp;&nbsp;';
    public $increment = '&nbsp;&nbsp;';
    public $postArray = [];

    /**
     * @param        $table_name
     * @param string $id_name
     * @param string $pid_name
     */
    public function __construct($table_name, $id_name = 'post_id', $pid_name = 'pid')
    {
        parent::__construct($table_name, $id_name, $pid_name);
    }

    /**
     * @param string $val
     */
    public function setPrefix($val = '')
    {
        $this->prefix    = $val;
        $this->increment = $val;
    }

    /**
     * @param        $sel_id
     * @param string $order
     */
    public function getAllPostArray($sel_id, $order = '')
    {
        $this->postArray = $this->getAllChild($sel_id, $order);
    }

    /**
     * @param $postArray
     */
    public function setPostArray($postArray)
    {
        $this->postArray = $postArray;
    }

    // returns an array of first child objects for a given id($sel_id)

    /**
     * @param         $postTree_array
     * @param  int    $pid
     * @param  string $prefix
     * @return bool
     */
    public function getPostTree(&$postTree_array, $pid = 0, $prefix = '&nbsp;&nbsp;')
    {
        if (!is_array($postTree_array)) {
            $postTree_array = [];
        }

        $newPostArray = [];
        $prefix       .= $this->increment;
        foreach ($this->postArray as $post) {
            if ($post->getVar('pid') == $pid) {
                $postTree_array[] = [
                    'prefix'      => $prefix,
                    'icon'        => $post->getVar('icon'),
                    'post_time'   => $post->getVar('post_time'),
                    'post_id'     => $post->getVar('post_id'),
                    'forum_id'    => $post->getVar('forum_id'),
                    'subject'     => $post->getVar('subject'),
                    'poster_name' => $post->getVar('poster_name'),
                    'uid'         => $post->getVar('uid')
                ];
                $this->getPostTree($postTree_array, $post->getVar('post_id'), $prefix);
            } else {
                $newPostArray[] = $post;
            }
        }
        $this->postArray = $newPostArray;
        unset($newPostArray);

        return true;
    }
}
