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

use XoopsTree;

require_once $GLOBALS['xoops']->path('class/xoopstree.php');

/**
 * Class Tree
 */
class Tree extends XoopsTree
{
    /** @var string */
    private $prefix = '&nbsp;&nbsp;';
    /** @var string */
    private $increment = '&nbsp;&nbsp;';
    /** @var array */
    private $postArray = [];

    /**
     * @param string $table_name
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
    public function setPrefix($val = ''): void
    {
        $this->prefix    = $val;
        $this->increment = $val;
    }

    /**
     * @param        $sel_id
     * @param string $order
     */
    public function getAllPostArray($sel_id, $order = ''): void
    {
        $this->postArray = $this->getAllChild($sel_id, $order);
    }

    /**
     * @param $postArray
     */
    public function setPostArray($postArray): void
    {
        $this->postArray = $postArray;
    }

    // returns an array of first child objects for a given id($sel_id)

    /**
     * @param mixed  $postTree_array
     * @param int    $pid
     * @param string $prefix
     * @return bool
     */
    public function getPostTree(&$postTree_array, $pid = 0, $prefix = '&nbsp;&nbsp;')
    {
        if (!\is_array($postTree_array)) {
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
                    'uid'         => $post->getVar('uid'),
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
