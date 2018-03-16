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

use XoopsModules\Newbb;

// defined('XOOPS_ROOT_PATH') || die('Restricted access');

defined('NEWBB_FUNCTIONS_INI') || include __DIR__ . '/functions.ini.php';
define('NEWBB_FUNCTIONS_FORUM_LOADED', true);

if (!defined('NEWBB_FUNCTIONS_FORUM')) {
    define('NEWBB_FUNCTIONS_FORUM', 1);

    /**
     * @param  null|array $value             selected forum id
     * @param  string     $permission        permission (access, all, etc.)
     * @param  bool       $categoryDelimiter show delimiter between categories
     * @param  bool       $see
     * @return string
     */
    function newbbForumSelectBox($value = null, $permission = 'access', $categoryDelimiter = true, $see = false)
    {
        global $xoopsUser;
        /** @var Newbb\CategoryHandler $categoryHandler */
        $categoryHandler = Newbb\Helper::getInstance()->getHandler('Category');
        $categories      = $categoryHandler->getByPermission($permission, ['cat_id', 'cat_order', 'cat_title'], false);

        $cacheHelper = new \Xmf\Module\Helper\Cache('newbb');

        $groups = [XOOPS_GROUP_ANONYMOUS];
        if (is_object($xoopsUser)) {
            $groups = $xoopsUser->getGroups();
        }
        sort($groups);
        $groupKey = 'forumselect_' . $permission . '_' . md5(implode(',', $groups));
        $forums   = $cacheHelper->cacheRead($groupKey, function () use ($categories, $permission) {
            /** @var Newbb\CategoryHandler $categoryHandler */
            $categoryHandler = Newbb\Helper::getInstance()->getHandler('Category');
            $categories      = $categoryHandler->getByPermission($permission, ['cat_id', 'cat_order', 'cat_title'], false);

            /** @var Newbb\ForumHandler $forumHandler */
            $forumHandler = Newbb\Helper::getInstance()->getHandler('Forum');
            $forums       = $forumHandler->getTree(array_keys($categories), 0, 'all');

            return $forums;
        }, 300);

        $value = is_array($value) ? $value : [$value];
        //$see = is_array($see) ? $see : array($see);
        $box = '';
        if (count($forums) > 0) {
            foreach (array_keys($categories) as $key) {
                if ($categoryDelimiter) {
                    $box .= "<option value=0>&nbsp;</option>\n";
                }
                $box .= "<option value='" . (-1 * $key) . "'>[" . $categories[$key]['cat_title'] . "]</option>\n";
                if (empty($forums[$key])) {
                    continue;
                }
                foreach ($forums[$key] as $f => $forum) {
                    if ($see && in_array($f, $value)) {
                        continue;
                    }
                    $box .= "<option value='{$f}' " . (in_array($f, $value) ? ' selected' : '') . '>' . $forum['prefix'] . $forum['forum_name'] . "</option>\n";
                }
            }
        } else {
            $box .= '<option value=0>' . _MD_NEWBB_NOFORUMINDB . "</option>\n";
        }
        unset($forums, $categories);

        return $box;
    }

    /**
     * @param  int $forum_id
     * @return string
     */
    function newbbMakeJumpbox($forum_id = 0)
    {
        $box = '<form name="forum_jumpbox" method="get" action="' . XOOPS_URL . '/modules/newbb/viewforum.php" onsubmit="javascript: if (document.forum_jumpbox.forum.value &lt; 1) {return false;}">';
        $box .= '<select class="select" name="forum" onchange="if (this.options[this.selectedIndex].value >0) { document.forms.forum_jumpbox.submit();}">';
        $box .= '<option value=0>-- ' . _MD_NEWBB_SELFORUM . ' --</option>';
        $box .= newbbForumSelectBox($forum_id);
        $box .= "</select> <input type='submit' class='button' value='" . _GO . "' /></form>";
        unset($forums, $categories);

        return $box;
    }

    /**
     * Get structured forums
     *
     * This is a temporary solution
     * To be substituted with a new tree handler
     *
     * @int integer    $pid    parent forum ID
     *
     * @param  int  $pid
     * @param  bool $refresh
     * @return array
     */
    function newbbGetSubForum($pid = 0, $refresh = false)
    {
        static $list;
        if (!isset($list)) {
            $cacheHelper = new \Xmf\Module\Helper\Cache('newbb');
            $list        = $cacheHelper->read('forum_sub');
        }

        if (!is_array($list) || $refresh) {
            $list = newbbCreateSubForumList();
        }
        if (0 == $pid) {
            return $list;
        } else {
            return @$list[$pid];
        }
    }

    /**
     * @return array
     */
    function newbbCreateSubForumList()
    {
        /** @var Newbb\ForumHandler $forumHandler */
//        $forumHandler = Newbb\Helper::getInstance()->getHandler('Forum');
        $forumHandler = Newbb\Helper::getInstance()->getHandler('Forum');
        $criteria     = new \CriteriaCompo(null, 1);
        $criteria->setSort('cat_id ASC, parent_forum ASC, forum_order');
        $criteria->setOrder('ASC');
        $forumsObject = $forumHandler->getObjects($criteria);
        require_once $GLOBALS['xoops']->path('modules/newbb/class/tree.php');
        $tree        = new Newbb\ObjectTree($forumsObject, 'forum_id', 'parent_forum');
        $forum_array = [];
        foreach (array_keys($forumsObject) as $key) {
            if (!$child = array_keys($tree->getAllChild($forumsObject[$key]->getVar('forum_id')))) {
                continue;
            }
            $forum_array[$forumsObject[$key]->getVar('forum_id')] = $child;
        }
        unset($forumsObject, $tree, $criteria);

        $cacheHelper = new \Xmf\Module\Helper\Cache('newbb');
        $cacheHelper->write('forum_sub', $forum_array);

        return $forum_array;
    }

    /**
     * @param  int  $forum_id
     * @param  bool $refresh
     * @return array|mixed|null
     */
    function newbbGetParentForum($forum_id = 0, $refresh = false)
    {
        static $list = null;

        if (!isset($list)) {
            $cacheHelper = new \Xmf\Module\Helper\Cache('newbb');
            $list        = $cacheHelper->read('forum_parent');
        }
        if (!is_array($list) || $refresh) {
            $list = newbbCreateParentForumList();
        }
        if (0 == $forum_id) {
            return $list;
        } else {
            return @$list[$forum_id];
        }
    }

    /**
     * @return array
     */
    function newbbCreateParentForumList()
    {
        /** @var Newbb\ForumHandler $forumHandler */
        $forumHandler = Newbb\Helper::getInstance()->getHandler('Forum');
        $criteria     = new \Criteria('1', 1);
        $criteria->setSort('parent_forum');
        $criteria->setOrder('ASC');
        $forumsObject = $forumHandler->getObjects($criteria);
        require_once $GLOBALS['xoops']->path('modules/newbb/class/tree.php');
        $tree        = new Newbb\ObjectTree($forumsObject, 'forum_id', 'parent_forum');
        $forum_array = [];
        foreach (array_keys($forumsObject) as $key) {
            $parent_forum = $forumsObject[$key]->getVar('parent_forum');
            if (!$parent_forum) {
                continue;
            }
            if (isset($forum_array[$parent_forum])) {
                $forum_array[$forumsObject[$key]->getVar('forum_id')]   = $forum_array[$parent_forum];
                $forum_array[$forumsObject[$key]->getVar('forum_id')][] = $parent_forum;
            } else {
                $forum_array[$forumsObject[$key]->getVar('forum_id')] = $tree->getParentForums($forumsObject[$key]->getVar('forum_id'));
            }
        }
        unset($forumsObject, $tree, $criteria);

        $cacheHelper = new \Xmf\Module\Helper\Cache('newbb');
        $cacheHelper->write('forum_parent', $forum_array);

        return $forum_array;
    }
}
